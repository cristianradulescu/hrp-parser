<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="default")
     *
     * @return Response
     */
    public function index()
    {
        return $this->render(
            'index.html.twig',
            [
                'title' => 'page.title_index',
                'label_user' => 'form.label_user',
                'label_password' => 'form.label_password',
                'label_company' => 'form.label_company',
                'label_year' => 'form.label_year',
                'label_month' => 'form.label_month',
                'label_submit' => 'form.label_submit',
                'form_info_required_fields' => 'form.info_required_fields'
            ]
        );
    }

    /**
     * @Route("/export-confirm", name="export-confirm", methods={"POST"})
     *
     * @param Request $request
     * @param TranslatorInterface $translator
     * @return RedirectResponse|Response
     * @throws \Exception
     */
    public function exportConfirm(Request $request, TranslatorInterface $translator)
    {
        $params = $request->request->all();
        $params[] = $this->getParameter('hrp_domain');

        try {
            $extractedContent = $this->get('App\Service\PhantomjsService')
                ->setUsername($params['username'])
                ->setPassword($params['password'])
                ->setCompanyId($params['company'])
                ->setYear($params['year'])
                ->setMonth($params['month'])
                ->setDomain($this->getParameter('hrp_domain'))
                ->run($_SERVER['HRP_SIMULATE_REQUEST']);

        } catch (\Exception $e) {
            return $this->returnError(
                $translator->trans('page.error').': '.$e->getMessage());
        }

        $timekeepingService = $this->get('App\Service\TimekeepingService');
        $tableDateHeader = $timekeepingService->createListOfIntlDatesInMonth(
            $params['year'],
            $params['month'],
            $_SERVER['LOCALE']
        );
        $content = [];
        foreach ($extractedContent as $workedHours) {
            $name = $workedHours[0];

            // remove Name and compute the rest of daily details
            array_shift($workedHours);
            foreach ($workedHours as $key => $nbOfHours) {
                foreach($timekeepingService->computeDailyDetails($nbOfHours) as $workedHoursDetails) {
                    if (!array_key_exists($key+1, $tableDateHeader)) continue;
                    $content[$name][$tableDateHeader[$key+1]][] = $workedHoursDetails;
                }
            }
        }

        $allowDataReset = $_SERVER['ALLOW_DATA_RESET'];
        if ($allowDataReset) {
            $this->get('snc_redis.default')->set('content-' . $params['username'], json_encode($content));
        }

        return $this->render(
            'export-confirm.html.twig',
            [
                'title' => 'page.title_export_confirm',
                'content' => $content,
                'column_name' => 'spreadsheet.column_name',
                'subcolumn_in' => 'spreadsheet.column_in',
                'subcolumn_out' => 'spreadsheet.column_out',
                'subcolumn_break' => 'spreadsheet.column_break',
                'subcolumn_total' => 'spreadsheet.column_total',
                'export_button' => 'export_to_excel',
                'export_filename' => $params['username'].'-'.$params['year'].'-'.$params['month'].'_'
                    .(new \DateTime())->format('YmdHis').'.xlsx',
                'allow_data_reset' => $allowDataReset
            ]
        );
    }

    /**
     * @Route("/export-demo", name="export-demo")
     *
     * @param Request $request
     * @return Response
     */
    public function exportDemo(Request $request)
    {
        $request->setMethod(Request::METHOD_POST);
        $request->request->set('username', 'cristian');
        $request->request->set('password', 'notthepasswordhaha');
        $request->request->set('company', '123-123');
        $request->request->set('year', 2017);
        $request->request->set('month', 11);

        return $this->forward('App\Controller\DefaultController:exportConfirm');
    }

    /**
     * @param string $message
     * @return RedirectResponse
     */
    protected function returnError(string $message = '')
    {
        $this->addFlash('error', $message);
        return $this->redirectToRoute('default');
    }
}

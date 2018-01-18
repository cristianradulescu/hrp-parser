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
            ]
        );
    }

    /**
     * @Route("/export", name="export", methods={"POST"})
     *
     * @param Request $request
     * @param TranslatorInterface $translator
     * @return RedirectResponse|Response
     */
    public function export(Request $request, TranslatorInterface $translator)
    {
        $params = $request->request->all();

        try {
            $spreadsheetService = $this->get('App\Service\SpreadsheetService')
                ->setCreator($params['username'])
                ->setTitle(
                    $translator->trans('spreadsheet.title').' '.$params['year'].'-'.$params['month']
                )
                ->setCategory('HR')
                ->setYear($params['year'])
                ->setMonth($params['month']);

            // prepare content
            $content = [];
            foreach ($params['employee'] as $employeeName => $employeeWorkDetails) {
                $content[] = array_merge(
                    [str_replace('_', ' ', $employeeName)],
                    $employeeWorkDetails
                );
            }
            $spreadsheetService->setContent($content);
            $spreadsheetService->generateSpreadsheet();
            $tmpFile = $spreadsheetService->writeSpreadsheetFile();
        } catch (\Exception $e) {
            return $this->returnError(
                $translator->trans('page.error').': '.$e->getMessage());
        }

        return new Response(
            file_get_contents($tmpFile),
            200,
            [
                'Content-Type' => 'application/vnd.ms-excel',
                'Content-Disposition' => 'attachment; filename="'.$spreadsheetService->getFilename().'"'
            ]
        );
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

<?php

namespace App\Controller;

use App\Service\PhantomjsService;
use App\Service\SpreadsheetService;
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
                'title' => 'page.title',
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
     * @Route("/export", name="export", methods={"POST"})
     *
     * @param Request $request
     * @param TranslatorInterface $translator
     * @return RedirectResponse|Response
     */
    public function export(Request $request, TranslatorInterface $translator)
    {
        $params = $request->request->all();
        $params[] = $this->getParameter('hrp_domain');

        try {
            $phantomjsService = new PhantomjsService($params);
            $output = $phantomjsService->run($_SERVER['HRP_SIMULATE_REQUEST']);

            $spreadsheetService = (new SpreadsheetService($translator))
                ->setCreator($params['username'])
                ->setTitle(
                    $translator->trans('spreadsheet.title').' '.$params['year'].'-'.$params['month']
                )
                ->setCategory('HR')
                ->setYear($params['year'])
                ->setMonth($params['month'])
                ->setContent($output);
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
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function returnError(string $message = '')
    {
        $this->addFlash('error', $message);
        return $this->redirectToRoute('default');
    }
}

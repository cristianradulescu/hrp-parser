<?php

namespace App\Controller;

use App\Service\PhantomjsService;
use App\Service\SpreadsheetService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
                'title' => 'HRP parser',
                'label_user' => 'User',
                'label_password' => 'Password',
                'label_company' => 'Company',
                'label_year' => 'Year',
                'label_month' => 'Month',
                'label_submit' => 'Export'
            ]
        );
    }

    /**
     * @Route("/export", name="export", methods={"POST"})
     *
     * @param Request $request
     * @return Response
     */
    public function export(Request $request)
    {
        $params = $request->request->all();
        $params[] = $this->getParameter('hrp_domain');

        try {
            $phantomjsService = new PhantomjsService($params);
            $output = $phantomjsService->run($_SERVER['HRP_SIMULATE_REQUEST']);

            $spreadsheetService = (new SpreadsheetService())
                ->setCreator($params['username'])
                ->setTitle('Employees report '.$params['year'].'-'.$params['month'])
                ->setCategory('HR')
                ->setYear($params['year'])
                ->setMonth($params['month'])
                ->setContent($output);
            $spreadsheetService->generateSpreadsheet();
            $tmpFile = $spreadsheetService->writeSpreadsheetFile();
        } catch (\Exception $e) {
            return $this->returnError('Error: '.$e->getMessage());
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

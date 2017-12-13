<?php

namespace App\Controller;

use App\Service\PhantomjsService;
use App\Service\SpreadsheetService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Exception\ProcessFailedException;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="default")
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
        } catch (ProcessFailedException $pe) {
            return new Response('An error occurred while trying to get data from external domain. '.$pe->getMessage());
        } catch (\Exception $e) {
            return new Response('An error occurred during th export process. '.$e->getMessage());
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
}

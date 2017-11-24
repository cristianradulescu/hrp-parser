<?php

namespace App\Controller;

use App\Service\PhantomjsService;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\RichText;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
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
            $output = $phantomjsService->run();
        } catch (ProcessFailedException $pe) {
            return new Response($pe->getMessage());
        }

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator($params['username'])
            ->setLastModifiedBy($params['username'])
            ->setTitle('Employees report '.$params['year'].'-'.$params['month'])
            ->setCategory('HR');

        $spreadsheet->setActiveSheetIndex(0)
            ->mergeCells('A1:A2')
            ->setCellValue('A1', $this->formatHeaderText('Name'))
            ->setCellValue('B1', $this->formatHeaderText('Day 1'))
            ->setCellValue('C1', $this->formatHeaderText('Day 2'));
        $spreadsheet->getActiveSheet()->setTitle('Report');

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $filename = $params['month'].'_pontaj_g'.(new \DateTime())->format('YmdHis').'.xlsx';
        $tmpFile =  '/tmp/'.$filename;
        $writer->save($tmpFile);

        return new Response(
            file_get_contents($tmpFile),
            200,
            [
                'Content-Type' => 'application/vnd.ms-excel',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"'
            ]
        );
    }

    /**
     * @param string $text
     * @return RichText
     */
    protected function formatHeaderText(string $text)
    {
        $richText = new RichText();
        $richText->createText('');
        $headerText = $richText->createTextRun($text);
        $headerText->getFont()->setBold(true);

        return $richText;
    }
}

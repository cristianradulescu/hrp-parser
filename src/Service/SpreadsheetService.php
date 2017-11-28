<?php

namespace App\Service;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\RichText;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Exception as WriterException;

/**
 * Class SpreadsheetService
 * @package App\Service
 */
class SpreadsheetService
{
    /**
     * @var Spreadsheet
     */
    protected $spreadsheet;

    protected $filename = '';

    protected $creator = '';
    protected $title = '';
    protected $category = '';

    protected $year = 2017;
    protected $month = 1;

    /**
     * @param string $creator
     * @return SpreadsheetService
     */
    public function setCreator(string $creator): SpreadsheetService
    {
        $this->creator = $creator;
        return $this;
    }

    /**
     * @param string $title
     * @return SpreadsheetService
     */
    public function setTitle(string $title): SpreadsheetService
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @param string $category
     * @return SpreadsheetService
     */
    public function setCategory(string $category): SpreadsheetService
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @param int $year
     * @return SpreadsheetService
     */
    public function setYear(int $year): SpreadsheetService
    {
        $this->year = $year;
        return $this;
    }

    /**
     * @param int $month
     * @return SpreadsheetService
     */
    public function setMonth(int $month): SpreadsheetService
    {
        $this->month = $month;
        return $this;
    }

    public function generateSpreadsheet()
    {
        $this->spreadsheet = new Spreadsheet();
        $this->spreadsheet->getProperties()
            ->setCreator($this->creator)
            ->setLastModifiedBy($this->creator)
            ->setTitle($this->title)
            ->setCategory($this->category);
        $this->spreadsheet->setActiveSheetIndex(0)
            ->mergeCells('A1:A2')
            ->setCellValue('A1', $this->formatHeaderText('Name'))
            ->setCellValue('B1', $this->formatHeaderText('Day 1'))
            ->setCellValue('C1', $this->formatHeaderText('Day 2'));
        $this->spreadsheet->getActiveSheet()->setTitle('Report');

        return $this->spreadsheet;
    }

    /**
     * @return string
     */
    public function writeSpreadsheetFile()
    {
        $writer = IOFactory::createWriter($this->spreadsheet, 'Xlsx');
        $tmpFile = '/tmp/' . $this->getFilename();
        $writer->save($tmpFile);

        return $tmpFile;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        if ('' === $this->filename) {
            $this->filename = 'pontaj_g' . (new \DateTime())->format('YmdHis') . '.xlsx';
        }

        return $this->filename;
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
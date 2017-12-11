<?php

namespace App\Service;

use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\RichText;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
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

    /**
     * @var array
     */
    protected $content = array();

    /**
     * @var string
     */
    protected $filename = '';

    /**
     * @var string
     */
    protected $creator = '';
    protected $title = '';
    protected $category = '';

    /**
     * @var int
     */
    protected $year = 2017;
    protected $month = 1;

    /**
     * @param array $content
     * @return SpreadsheetService
     */
    public function setContent(array $content): SpreadsheetService
    {
        $this->content = $content;
        return $this;
    }

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

        try {
            $this->spreadsheet->setActiveSheetIndex(0);
            $activeSheet = $this->spreadsheet->getActiveSheet();
            $activeSheet->setTitle('Report');

            /**
             * "Name" column
             * --------------
             * - Merge A1:A2
             * - Center vertically and horizontally
             * - AutoFill column width
             * - Apply "Header text" style
             */
            $activeSheet->mergeCells('A1:A2')
                ->getStyle('A1')
                ->getAlignment()
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $activeSheet->getColumnDimension('A')->setAutoSize(true);
            $activeSheet->setCellValue('A1', $this->formatHeaderText('Name'));

            // A1 and A2 are merged, start from A3
            $cellStartIndex = 3;
            foreach ($this->content as $userRow) {
                $activeSheet->setCellValue('A'.$cellStartIndex, $userRow[0]);
                $cellStartIndex++;
            }

            /**
             * @todo: "Day" column
             * --------------
             * - Row #1: Merge B1:E2 / [Monday, 11-Dec]
             * - Row #2: [In][Out][Break][Total]
             * - Center vertically and horizontally
             * - Apply "Header text" style
             */

        } catch (Exception $e) {
        }

        return $this->spreadsheet;
    }

    /**
     * @return string
     * @throws WriterException
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
     * @throws Exception
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
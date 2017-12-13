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

    protected $dateColumnGroups = [
        1 => ['B', 'C', 'D', 'E'],
        2 => ['F', 'G', 'H', 'I'],
        3 => ['J', 'K', 'L', 'M'],
        4 => ['N', 'O', 'P', 'Q'],
        5 => ['R', 'S', 'T', 'U'],
        6 => ['V', 'W', 'X', 'Y'],
        7 => ['Z', 'AA', 'AB', 'AC'],
        8 => ['AD', 'AE', 'AF', 'AG'],
        9 => ['AH', 'AI', 'AJ', 'AK'],
        10 =>['AL', 'AM', 'AN', 'AO'],
        11 =>['AP', 'AQ', 'AR', 'AS'],
        12 =>['AT', 'AU', 'AV', 'AW'],
        13 => ['AX', 'AY', 'AZ', 'BA'],
        14 => ['BB', 'BC', 'BD', 'BE'],
        15 => ['BF', 'BG', 'BH', 'BI'],
        16 => ['BJ', 'BK', 'BL', 'BM'],
        17 => ['BN', 'BO', 'BP', 'BQ'],
        18 => ['BR', 'BS', 'BT', 'BU'],
        19 => ['BV', 'BW', 'BX', 'BY'],
        20 => ['BZ', 'CA', 'CB', 'CC'],
        21 => ['CD', 'CE', 'CF', 'CG'],
        22 => ['CH', 'CI', 'CJ', 'CK'],
        23 => ['CL', 'CM', 'CN', 'CO'],
        24 => ['CP', 'CQ', 'CR', 'CS'],
        25 => ['CT', 'CU', 'CV', 'CW'],
        26 => ['CX', 'CY', 'CZ', 'DA'],
        27 => ['DB', 'DC', 'DD', 'DE'],
        28 => ['DF', 'DG', 'DH', 'DI'],
        29 => ['DJ', 'DK', 'DL', 'DM'],
        30 => ['DN', 'DO', 'DP', 'DQ'],
        31 => ['DR', 'DS', 'DT', 'DU'],
    ];

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

    /**
     * @return Spreadsheet
     * @throws Exception
     */
    public function generateSpreadsheet()
    {
        $this->spreadsheet = new Spreadsheet();
        $this->spreadsheet->getProperties()
            ->setCreator($this->creator)
            ->setLastModifiedBy($this->creator)
            ->setTitle($this->title)
            ->setCategory($this->category);

        $this->spreadsheet->setActiveSheetIndex(0);
        $activeSheet = $this->spreadsheet->getActiveSheet();
        $activeSheet->setTitle('Report');

        /**
         * "Name" header
         * --------------
         * - Merge A1:A2
         * - Center vertically and horizontally
         * - AutoFill column width
         * - Apply "Header text" style
         * - TODO: Freeze column
         */
        $activeSheet->mergeCells('A1:A2')
            ->getStyle('A1')
            ->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $activeSheet->getColumnDimension('A')->setAutoSize(true);
        $activeSheet->setCellValue('A1', $this->formatHeaderText('Name'));

        /**
         * "Day" header
         * --------------
         * - Row #1: Merge B1:E1 / [Friday, 1-Dec]
         * - Row #2: [In][Out][Break][Total]
         * - Center vertically and horizontally
         * - Apply "Header text" style
         */
        $nbOfDaysInMonth = (new \DateTime($this->year.'-'.$this->month.'-01'))->format('t');
        for ($index = 1; $index <= $nbOfDaysInMonth; $index++) {
            $activeSheet->mergeCells($this->dateColumnGroups[$index][0].'1:'.$this->dateColumnGroups[$index][3].'1')
                ->getStyle($this->dateColumnGroups[$index][0].'1')
                ->getAlignment()
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $activeSheet->setCellValue(
                $this->dateColumnGroups[$index][0].'1',
                $this->formatHeaderText(
                    (new \DateTime($this->year.'-'.$this->month.'-'.$index))->format('D, d-M')
                )
            );
            $activeSheet->setCellValue($this->dateColumnGroups[$index][0].'2', $this->formatHeaderText('In'));
            $activeSheet->setCellValue($this->dateColumnGroups[$index][1].'2', $this->formatHeaderText('Out'));
            $activeSheet->setCellValue($this->dateColumnGroups[$index][2].'2', $this->formatHeaderText('Break'));
            $activeSheet->setCellValue($this->dateColumnGroups[$index][3].'2', $this->formatHeaderText('Total'));
        }

        /**
         * Data
         */
        // Rows #1 and #2 are used as header, start from row #3
        $cellStartIndex = 3;
        foreach ($this->content as $userRow) {
            // Name
            $name = array_shift($userRow);
            $activeSheet->setCellValue('A'.$cellStartIndex, $name);

            // day entries
            foreach ($userRow as $key => $value) {
                $activeSheet->setCellValue($this->dateColumnGroups[$key+1][3].$cellStartIndex, $value);

            }

            $cellStartIndex++;
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
        $tmpFile = '/tmp/'.$this->getFilename();
        $writer->save($tmpFile);

        return $tmpFile;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        if ('' === $this->filename) {
            $this->filename = $this->creator
                .'-'.$this->year.'-'.$this->month.'_'
                .(new \DateTime())->format('YmdHis').'.xlsx';
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
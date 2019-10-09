<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader;

/********************************************************
 * * *    		Excel - Import, export excel 	 	* * *
 * * *    				Version 1.0    				* * *
 * * *    	Author: dung.huynh@southtelecom.vn    	* * *
 ********************************************************/

/**
 * CLASS Excel
 * @use: Set instance of Class.
 * @vendor: phpoffice/phpspreadsheet
 * @composer: composer require phpoffice/phpspreadsheet
 * @doc: https://phpspreadsheet.readthedocs.io/ 
 * @github: https://github.com/PHPOffice/PhpSpreadsheet/
 * @example: 
 * @return: 
 */

Class MyReadFilter implements \PhpOffice\PhpSpreadsheet\Reader\IReadFilter {

	public function __construct() {
		$fromColumn = 0; $toColumn = 20;
        $this->columns = array();
        $toColumn++;
        while ($fromColumn !== $toColumn) {
            $this->columns[] = $fromColumn++;
        }
        $fromRow = 0; $toRow = 1000;
        $toRow++;
        while ($fromRow !== $toRow) {
            $this->rows[] = $fromRow++;
        }
    }

	public function setColumns($fromColumn, $toColumn) {
        $this->columns = array();
        while ($fromColumn !== $toColumn) {
            $this->columns[] = $fromColumn++;
        }
    }

    public function setRows($fromRow, $toRow) {
        $this->rows = array();
        $toRow++;
        while ($fromRow !== $toRow) {
            $this->rows[] = $fromRow++;
        }
    }

    public function readCell($column, $row, $worksheetName = '') {
        // Read column in columns, row in rows
        if (in_array($column, $this->columns) && in_array($row, $this->rows)) {
            return true;
        }
        return false;
    }
}

Class Excel {

	function __construct() 
	{
		$this->WFF =& get_instance();
		$this->WFF->load->library("mongo_db");
	}

    function write($data, $model = [], $filename = "export.xlsx")
    {
    	$spreadsheet = new Spreadsheet();
    	$spreadsheet->getProperties()
	    ->setCreator("South Telecom")
	    ->setLastModifiedBy("Tri Dung")
	    ->setTitle("Report")
	    ->setSubject("Report")
	    ->setDescription("Office 2007 XLSX, generated using PHP classes.")
	    ->setKeywords("office 2007 openxml php")
	    ->setCategory("Report");

	    $worksheet = $spreadsheet->getActiveSheet();

	    $fieldToCol = array();
	    // Title row
	    $col = "A";
	    $row = 1;
	    if($model) {
		    foreach ($model as $field => $prop) {
		    	$fieldToCol[ $field ] = $col;
		    	$title = isset($prop["title"]) ? $prop["title"] : $field;
		    	$worksheet->setCellValue($col . $row, $title);
		    	$worksheet->getColumnDimension($col)->setAutoSize(true);
		    	$col++;
		    }
		} else {
			foreach ($data[0] as $field => $value) {
				$fieldToCol[ $field ] = $col;
		    	$worksheet->setCellValue($col . $row, $field);
		    	$worksheet->getColumnDimension($col)->setAutoSize(true);
		    	$col++;
			}
		}
		--$col;
		$maxCol = $col;
		$worksheet->getStyle("A1:{$maxCol}1")->getFill()
	    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
	    ->getStartColor()->setARGB('FFFF0000');
	    // Data row
		if($data) {
		    $row = 2;
		    foreach ($data as $doc) {
		    	foreach ($doc as $field => $value) {
		    		if(isset($fieldToCol[ $field ], $model[$field])) {
			    		$col = $fieldToCol[ $field ];
			    		switch ($model[$field]["type"]) {
			    			case 'array': case 'arrayPhone': case 'arrayEmail':
			    				$val = implode(",", $value);
			    				$worksheet->setCellValueExplicit($col . $row, $val, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
			    				break;
			    			
			    			case 'string': case 'name': case 'phone': 
			    			case 'email':
			    				$worksheet->setCellValueExplicit($col . $row, $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
			    				break;

			    			case 'boolean':
			    				$worksheet->setCellValueExplicit($col . $row, $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_BOOLEAN);
			    				break;


			    			case 'int': case 'double':
			    				$worksheet->setCellValueExplicit($col . $row, $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
			    				break;

			    			default:
			    				break;
			    		}
		    		}
		    	}
		    	if($row % 2 == 1) {
			    	$worksheet->getStyle("A{$row}:{$maxCol}{$row}")->getFill()
				    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				    ->getStartColor()->setARGB('F0F6DA');
				}
		    	$row++;
		    }
	    }
	    
    	$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    	$file_path = UPLOAD_PATH . "excel/" . $filename;
		$writer->save($file_path);
		return $file_path;
    }

    function read($file_path, $pageSize = 5, $titleRow = 1, $limit_column = null)
    {
    	$total = $this->getTotal($file_path);
    	if(empty($pageSize)) {
            $pageSize = $total;
        }
    	$data = $this->getData($file_path, $titleRow, $pageSize + 1, $limit_column);
    	$show_data = array();
    	for ($i = 0; $i < $pageSize; $i++) { 
    		if(isset($data[$i]))
    			$show_data[] = $data[$i];
    	}
    	return array("data" => $show_data, "total" => $total);
    }

    function convert($file_path, $convert, $from_row = 0, $to_row = 1000000, $limit_column = null, $titleRow = 1)
    {
    	$excel_data = $this->getValueData($file_path, $from_row, $to_row, $titleRow, $limit_column);
    	$data = array();
    	foreach ($excel_data as $row) {
			$doc = array();
			foreach ($row as $key => $value) {
				if(isset($convert[$key]))
					$doc[$convert[$key]] = $value;
			}
			$data[] = $doc;
		}
		return $data;
    }

    function getValueData($file_path, $from_row = 0, $to_row = 1000000, $titleRow = 1, $limit_column)
    {
    	$collection = $file_path;
    	$csv_data = $this->WFF->mongo_db->limit($to_row)->select([], ["_id"])->get($collection);
    	if($csv_data) {
    		$data = $csv_data;
    	} else {
	    	$excel_data = $this->getData($file_path, $from_row, $to_row, $limit_column);
	    	$data = array();
	    	foreach ($excel_data as $index => $value) {
	    		if($index > $titleRow)
	    			$data[] = $value;
	    	}
    	}
    	return $data;
    }

    function insertDataCsv($file_path, $delimeter = ",") 
    {
    	$collection = $file_path;
		$file = @fopen($file_path,"r");
		if(!$file) return FALSE;
		$i = 0;
		if($this->WFF->mongo_db->getOne($collection))
			$this->WFF->mongo_db->drop_collection($collection);
		
		while(! feof($file))
		{
			$col = "A";
			try {
				$doc = @fgetcsv($file, 0, $delimeter);
				if(is_array($doc) && $i) {
					$row = array();
					foreach ($doc as $key => $value) {
						$row[$col] = $value;
						$col++;
					}
					$this->WFF->mongo_db->insert($collection, $row);
				}
				$i++;
			} catch(Exception $e) {
				continue;
			}
		}
		fclose($file);
		return TRUE;
    }

    function getData($file_path, $from_row = 0, $to_row = 1000, $limit_column = null)
    {
    	if(!isset($this->reader)) {
	    	/**  Identify the type of $file_path  **/
			$inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($file_path);
			/**  Create a new Reader of the type that has been identified  **/
			$this->inputFileType = $inputFileType;
			$this->reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
		}
		if($this->inputFileType == "csv") {
			$collection = $file_path;
			$sheetData = $this->WFF->mongo_db->limit($to_row)->select([], ["_id"])->get($collection);
		} else {
			/**  Advise the Reader that we only want to load cell data  **/
			$this->reader->setReadDataOnly(true);
			// Filter
			$filter = new MyReadFilter();
			$filter->setRows($from_row, $to_row);
            print_r("TEST");
            exit();
			if($limit_column) {
                $filter->setColumns("A", $limit_column);
            }
//			else {
//                $filter->setColumns("A", $limit_column);
//            }

			$this->reader->setReadFilter( $filter );

			/**  Load $file_path to a Spreadsheet Object  **/
			$spreadsheet = $this->reader->load($file_path);

			$worksheet = $spreadsheet->getActiveSheet();

			$maxCell = $worksheet->getHighestRowAndColumn();

            $sheetData = $worksheet->rangeToArray('A1:' . $maxCell['column'] . $maxCell['row']);

			// $sheetData = $worksheet->rangeToArray();
		}
		return $sheetData;
    }

    function getTotal($file_path)
    {
    	$collection = $file_path;
	    $total = $this->WFF->mongo_db->count($collection);
	    if(!$total) {
	    	/**  Identify the type of $file_path  **/
			$inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($file_path);
			/**  Create a new Reader of the type that has been identified  **/
			$this->inputFileType = $inputFileType;
			$this->reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
			/**  Advise the Reader that we only want to load cell data  **/
			$this->reader->setReadDataOnly(true);
			/**  Load $file_path to a Spreadsheet Object  **/
			$spreadsheet = $this->reader->load($file_path);
			$worksheet = $spreadsheet->getActiveSheet();
			$total = $worksheet->getHighestDataRow();
		}
		return $total;
    }

    public function getActiveSheet($file_path)
    {
		$spreadsheet = $this->reader->load($file_path);
		$worksheet = $spreadsheet->getActiveSheet();
		return $worksheet;
    }
    
    public function stringFromColumnIndex($columnIndex)
    {
		$value = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex);
		return $value;
    }
    function toFormattedString($value,$format)
    {
    	$cell_value = \PhpOffice\PhpSpreadsheet\Style\NumberFormat::toFormattedString($value, $format);
    	return $cell_value;
    }
}
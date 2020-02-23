<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader;

/********************************************************
 * * *    		Csv - Import, export csv 	 		* * *
 * * *    				Version 1.0    				* * *
 * * *    	Author: dung.huynh@southtelecom.vn    	* * *
 ********************************************************/

Class Csv {

	function __construct() 
	{
	}

	function split($inputFile)
	{
		$inputFile = 'input.csv';
		$outputFile = 'output';

//		$splitSize = 10000;
		$splitSize = 10;

		$in = fopen($inputFile, 'r');

		$rowCount = 0;
		$fileCount = 1;
		while (!feof($in)) {
		    if (($rowCount % $splitSize) == 0) {
		        if ($rowCount > 0) {
		            fclose($out);
		        }
		        $out = fopen($outputFile . $fileCount++ . '.csv', 'w');
		    }
		    $data = fgetcsv($in);
		    if ($data)
		        fputcsv($out, $data);
		    $rowCount++;
		}

		fclose($out);
	}

	function read($inputFileName, $length = 0, $delimiter = ',', $hasTitle = true) {
	    $data = array();
	    $header = array();
        $file = fopen($inputFileName,"r");

        while(!feof($file))
        {
            $temp = fgetcsv($file, $length, $delimiter);
            if(!empty($temp)) {
                if(empty($data)) {
                    $header = array_values(array_filter($temp));
                }
                $editedValue = array_slice($temp, 0, 8);
                array_push($data, $editedValue);
            }
        }

        fclose($file);

        $return  = array(
            'data'  => $data,
            'total' => ($hasTitle) ? count($data) - 1 : count($data)
        );

        return $return;
    }

    function convert($file_path, $convert, $from_row = 0, $to_row = 1000000, $limit_column = null, $titleRow = 1)
    {
        $csv_data = $this->getValueData($file_path, $from_row, $to_row, $titleRow, $limit_column);
        $data = array();
        foreach ($csv_data as $row) {
            $doc = array();
            foreach ($row as $key => $value) {
                if(isset($convert[$key]))
                    $doc[$convert[$key]] = $value;
            }
            $data[] = $doc;
        }
        return $data;
    }

    function getValueData($inputFileName, $length = 0, $delimiter = ',', $hasTitle = true) {

    }
}
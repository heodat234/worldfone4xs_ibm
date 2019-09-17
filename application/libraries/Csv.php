<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

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

		$splitSize = 10000;

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
}
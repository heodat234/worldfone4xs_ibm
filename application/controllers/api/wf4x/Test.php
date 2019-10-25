<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Test extends CI_Controller {

	function __construct()
	{
		parent::__construct();
	}

	function z()
	{
		$this->load->library("imap");
		$uids = $this->imap->search('SINCE "' . date(DATE_RFC2822) . '"');
		$emails = array();
		foreach ($uids as $uid) {
			$emails[] = $this->imap->get_message($uid);
		}
		pre($emails);
		/*foreach ($emails as $email) {
			if
		}*/
	}
	function n()
	{
		$this->load->model("navitaire_model");
		$result = $this->navitaire_model->getBooking("123");
		pre($result);
	}
   function unique_columns( $columns){
       $values = [];

       foreach ($columns as $value) {
           $count = 0;
           $value = $original = trim($value);

           while (in_array($value, $values)) {
               $value = $original . '-' . ++$count;
           }

           $values[] = $value;
       }

       return $values;
   }
   function read_csv( $file,  $length = 1000,  $delimiter = ',') {
       $handle = fopen($file, 'r');
       $hashes = [];
       $values = [];
       $header = null;
       // $headerUnique = null;

       if (!$handle) {
           return $values;
       }
       $header = fgetcsv($handle, $length, $delimiter);

       if (!$header) {
           return $values;
       }
       // return $header;
       $headerUnique = unique_columns($header);
       while (false !== ($data = fgetcsv($handle, $length, $delimiter))) {
           $hash = md5(serialize($data));

           if (!isset($hashes[$hash])) {
               $hashes[$hash] = true;
               // $values[] = $data;
               $values[] = array_combine($headerUnique, $data);
               // break;
           }
       }

       fclose($handle);

       return $value;
   }


   function testFile()
   {
      ini_set('max_execution_time', '300');
      $collection = '2_Telesalelist';
      $idImport = "5db268f51ef2b442430451e0";
      $extension = '999';

      // $output = exec('PYTHONIOENCODING=utf-8 python3.6 /var/www/html/python/importCSV.py ' . $idImport . " ". $collection ." ". $extension ." > /dev/null &");
      $output = shell_exec('PYTHONIOENCODING=utf-8 python3.6 /var/www/html/python/importTelesaleCSV.py ' . $idImport . " ". $collection ." ". $extension ." 2>&1");

      echo $output;
   }

   function testAssign()
   {
      $random = '100';
      $idImport = "5db268f51ef2b442430451e0";
      $extension = '999';

      // $output = exec('PYTHONIOENCODING=utf-8 python3.6 /var/www/html/python/importCSV.py ' . $idImport . " ". $collection ." ". $extension ." > /dev/null &");
      $output = shell_exec('PYTHONIOENCODING=utf-8 python3.6 /var/www/html/python/assign.py ' . $idImport . " ". $random ." ". $extension ." 2>&1");

      echo $output;
   }


    function convertCSVToJson() {
        $this->load->library('excel');
        $this->load->library('mongo_db');
        $filePath="/var/www/html/worldfone4xs_ibm/upload/web/ZACCF-20.xlsx";
        $rowDataRaw = $this->excel->read($filePath, 50, 1);
        if(!empty($rowDataRaw['data'])) {
            $rowDataRaw = $rowDataRaw['data'];
        }
        $insertData = array();
        foreach ($rowDataRaw as $key => $value) {
            if($key === 0) {
                continue;
            }
            $rowData = array();
            foreach ($rowDataRaw[0] as $titleKey => $titleValue) {
                $rowData[$titleValue] = $value[$titleKey];
            }
            array_push($insertData, $rowData);
        }
        echo "<pre>";
        print_r($insertData);
        echo "</pre>";
        $this->mongo_db->batch_insert('ZACCF', $insertData);
    }
}
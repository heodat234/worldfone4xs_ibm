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
   function testExcel()
   {
      $this->load->library("crud");
      $filePath ="/var/www/html/worldfone4xs_ibm/upload/users/import/Data_thu_vien_chung.csv";

      $request = array (
          'take' => 30,
          'skip' => 0,
          'page' => 1,
          'pageSize' => 30,
          "sort" => array(array("field" => "index", "dir" => "asc"))
        );
      $this->crud->select_db($this->config->item("_mongo_db"));
      $match = array( "collection" => '2_Datalibrary' );
      $response = $this->crud->read("Model", $request, ["index","field", "title", "type"], $match);
      if(!empty($response['data'])) {
         $titleData = $response['data'];
      }
      $result = $this->read_csv($filePath,10000,',');
      // $rows = array_chunk($result, 100);
      $insertData = array();
      // $k = 0;
      // for ($r=1; $r < count($rows); $r++) {
      //     $rowData = array();
      //     foreach ($titleData as $titleKey => $titleValue) {
      //         $value  = $rows[$r][$titleKey];
      //         var_dump($value);exit;

      //         if ( !is_numeric($value) && ($titleValue['type'] =='int' || $titleValue['type'] == 'double')) {
      //             $error[$k] = array('cell' =>$r.$titleKey,'type' =>'number');
      //             $k++;
      //             continue;
      //         }
      //         // if ($titleValue['type'] =='boolean' ) {
      //         //     $error[$k] = array('cell' =>$column.$i,'type' =>'boolean');
      //         //     $k++;
      //         //     continue;
      //         // }
      //         if (isset($value) && $titleValue['type'] == 'timestamp') {
      //             $value = str_replace('/', '-', $value);
      //             // $value = $this->excel->toFormattedString($cell->getValue(), 'dd/mm/yyyy');
      //             // var_dump(strtotime($value));exit;
      //             if(strtotime($value) ) {
      //                 $value = strtotime($value);
      //             }else{
      //                 $error[$k] =  array('cell' =>$r.$titleKey,'type' =>'date');
      //                 $k++;
      //                 continue;
      //             }
      //         }

      //         switch ($titleValue['type']) {
      //             case 'string':
      //                 $value = (string)$value;
      //                 break;
      //             case 'int':
      //                 $value = (int)$value;
      //                 break;
      //             case 'double':
      //                 $value = (double)$value;
      //                 break;
      //             default:
      //                $value = (string)$value;
      //         }
      //         $rowData[$titleValue['field']] = isset($value) ? $value : '';
      //     }
      //     $rowData['createdAt']        = time();
      //     // $rowData['id_import']        = $idImport;
      //     if ($rowData['assign'] != '') {
      //         $rowData['assigned_by']  = 'Byfixed-Import';
      //     }else{
      //         $rowData['assigned_by']  = '';
      //     }

      //     array_push($insertData, $rowData);
      // }
      echo "<pre>";
      print_r($result);


   }

   function testFile()
   {
      $collection = '2_Datalibrary';
      $idImport = "5da9f9301ef2b41d9248de02";
      $output = shell_exec('python3.6 /var/www/html/python/excel_1.py ' . $idImport . " ". $collection ." 2>&1");

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
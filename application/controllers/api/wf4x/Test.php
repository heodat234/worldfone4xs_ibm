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
    function check_your_datetime($x) {
        return (date('d/m/Y', strtotime($x)) == $x);
    }
    function filter_mydate($s) {
        if (preg_match('@^(\d\d)/(\d\d)/(\d\d\d\d)$@', $s) == false) {
            return false;
        }
        if (checkdate($m[2], $m[3], $m[1]) == false || $m[4] >= 24 || $m[5] >= 60 || $m[6] >= 60) {
            return false;
        }
        return $s;
    }
    public function test1()
    {
        $date = "1230220000";
        if (!strtotime($date)) {
            echo "sai";
        }else{
            echo "đúng";
        }
        var_dump(date('d/m/Y', strtotime($date)));
        if(preg_match('@^(\d\d)-(\d\d)-(\d\d\d\d)$@', $date)){
            $arr = explode('/', $date);
            $new = $arr[1].'/'.$arr[0].'/'.$arr[2];
            var_dump(date('d/m/Y', strtotime($new)));
        }else{
            echo "sai";
        }
        // if (!empty($errors['warning_count'])) {
        //     echo "Strictly speaking, that date was invalid!\n";
        // }else{
        //     echo "phải";
        // }
    }
    function convertCSVToJson() {
        $this->load->library("mongo_db");
        $filePath="C:/xampp/htdocs/worldfone4xs_ibm/upload/users/import/Data_thu_vien_chung.xlsx";
        $duoifile = 'xlsx';
        $insertData = array();
        if ($duoifile == 'xlsx') {
            $this->load->library('excel_PHP');
            $objPHPExcel = PHPExcel_IOFactory::load($filePath);

            // $objWorksheet  = $objPHPExcel->setActiveSheetIndex(0);
            // $highestRow    = $objWorksheet->getHighestRow();
            // $highestColumn = $objWorksheet->getHighestColumn();
            // $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
            // $array = array();
            // $data = array();
            // $arr_candidate = array();
            // $i = 0;
            // for ($row = 3; $row <= $highestRow;$row++)
            // {
            //     for ($j=0; $j < $highestColumnIndex; $j++) { 
            //         $data[$i][$j] = $objWorksheet->getCellByColumnAndRow($j,$row)->getCalculatedValue();
            //     }
            //     $i++;
                
            // }
 
            $maxCell = $objPHPExcel->getActiveSheet()->getHighestRowAndColumn();
            $data = $objPHPExcel->getActiveSheet()->rangeToArray('A1:' . $maxCell['column'] . $maxCell['row']);
            $data = array_map('array_filter', $data);
            $rowDataRaw = array_filter($data);

            foreach ($rowDataRaw as $key => $value) {
                if($key === 0) {
                    continue;
                }
                $rowData = array();
                foreach ($rowDataRaw[0] as $titleKey => $titleValue) {
                    if ($titleValue == '') {
                        continue;
                    }
                    $titleValue = str_replace(".", "", $titleValue);
                    $titleValue = str_replace(" ", "_", $titleValue);
                    $titleValue = str_replace("\n", "", $titleValue);
                    if(isset($value[$titleKey]) && strtotime($value[$titleKey])) {
                        $value[$titleKey] = strtotime($value[$titleKey]);
                    }
                    $rowData[$titleValue] = isset($value[$titleKey]) ? $value[$titleKey] : '';
                }
                array_push($insertData, $rowData);
            }
        }else if ($duoifile == 'csv') {
            $headerData = array();
            if (($h = fopen($filePath, "r")) !== FALSE) 
            {
              while (($rowData = fgetcsv($h, 1000, ",")) !== FALSE) 
              {     
                array_push($headerData, $rowData);
                break;
              }
                $i = 0;
                while (($row = fgetcsv($h, 1000, ",")) !== FALSE) 
                {   
                    if ($i == 0) {
                        $i++;
                       continue;
                    }
                    $rowData = array();
                    foreach ($headerData[0] as $titleKey => $titleValue) {
                        $titleValue = str_replace(".", "", $titleValue);
                        $titleValue = str_replace(" ", "_", $titleValue);
                        if (isset($row[$titleKey])) {
                           $rowData[$titleValue] = $row[$titleKey];
                        }else{
                            $rowData[$titleValue] = '';
                        }                        
                    }
                    array_push($insertData, $rowData);
                    $i++;
                }
                
              fclose($h);
            }
        }
        echo "<pre>";
        print_r($insertData);
        echo "</pre>";
        // $this->mongo_db->batch_insert('ZACCF', $insertData);
    }
}
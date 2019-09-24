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
    
    public function test1()
    {
        $path = FCPATH.'upload\users\import\\';

        $items = array_diff(scandir($path), array('..', '.'));
        foreach ($items as $name) {
                $file_path = $path . $name;
                $file_info = new SplFileInfo($file_path);
                $file_name = $file_info->getFilename();
                $ext = $file_info->getExtension();
            echo "<pre>";
            print_r($file_path);
            echo "</pre>";
        }
        exit;
    }
    function convertCSVToJson() {
        $this->load->library("mongo_db");
        $filePath="C:/xampp/htdocs/worldfone4xs_ibm/upload/users/import/Data_thu_vien_chung.xlsx";
        $duoifile = 'xlsx';
        $insertData = array();
        if ($duoifile == 'xlsx') {
            $this->load->library('Excel');

            $rowDataRaw = $this->excel->read($filePath, 50, 1);
            if(!empty($rowDataRaw['data'])) {
                $rowDataRaw = $rowDataRaw['data'];
            }

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
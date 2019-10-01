<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Test extends CI_Controller {

    private $collection = "Import";
	function __construct()
	{
		parent::__construct();
        $this->sub = set_sub_collection();
        $this->collection = $this->sub . $this->collection;
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
        $this->load->library('mongo_db');
        $this->load->library("crud");
        $this->crud->select_db($this->config->item("_mongo_db"));
        $request = array (
          'take' => 30,
          'skip' => 0,
          'page' => 1,
          'pageSize' => 30,
          "sort" => array(array("field" => "index", "dir" => "asc"))
        );
        $match = array( "collection" => '2_Telesalelist' );
        $response = $this->crud->read("Model", $request, ["index","field", "title", "type"], $match);
        if(!empty($response['data'])) {
            $titleData = $response['data'];
        }

        $filePath="C:/xampp/htdocs/worldfone4xs_ibm/upload/users/import/Data_thu_vien_chung.xlsx";
        $duoifile = 'xlsx';
        $insertData = array();
        $insertData = $error = array();
        if ($duoifile == 'xlsx') {
            $this->load->library('Excel');

            $rowDataRaw = $this->excel->read($filePath, 50, 1);
            if(!empty($rowDataRaw['data'])) {
                $rowDataRaw = $rowDataRaw['data'];
            }

            $objWorksheet   = $this->excel->getActiveSheet($filePath);
            $highestRow     = $objWorksheet->getHighestRow();
            // $highestColumn  = $this->excel->getHighestColumn($objWorksheet);
            $k = 0;
            for ($i=2; $i <= $highestRow; $i++) { 
                $rowData = array();
                foreach ($titleData as $titleKey => $titleValue) {
                    $cell   = $objWorksheet->getCellByColumnAndRow($titleKey + 1,$i);
                    $type   = $cell->getDataType();
                    $column = $this->excel->stringFromColumnIndex($titleKey + 1);
                    $value  = $cell->getValue();

                    if ($type != 'n' && ($titleValue['type'] =='int' || $titleValue['type'] == 'double')) {
                        $error[$k] = array('cell' =>$column.$i,'type' =>'number');
                        $k++;
                        continue;
                    }
                    if ($type != 'b' && $titleValue['type'] =='boolean' ) {
                        $error[$k] = array('cell' =>$column.$i,'type' =>'boolean');
                        $k++;
                        continue;
                    }
                    if (isset($value) && $titleValue['type'] == 'timestamp') {
                        $value = str_replace('/', '-', $value);
                        // $value = $this->excel->toFormattedString($cell->getValue(), 'dd/mm/yyyy');
                        // var_dump(strtotime($value));exit;
                        if(strtotime($value) ) {
                            $value = strtotime($value);
                        }else{
                            $error[$k] =  array('cell' =>$column.$i,'type' =>'date');
                            $k++;
                            continue;
                        }
                    }
                    
                    switch ($titleValue['type']) {
                        case 'string':
                            $value = (string)$value;
                            break;
                        case 'int':
                            $value = (int)$value;
                            break;
                        case 'double':
                            $value = (double)$value;
                            break;
                        default:
                           
                    }
                    $rowData[$titleValue['field']] = isset($value) ? $value : '';
                }
                $rowData['createdAt']        = time();
                $rowData['last_modified']    = 0;
                $rowData['id_import']        = 1;
                $rowData['assigned_by']      = 'By Admin';
                array_push($insertData, $rowData);
            }
            
        }else if ($duoifile == 'csv') {
            // $titleData = array();
            if (($h = fopen($filePath, "r")) !== FALSE) 
            {
                $i = 0;
                while (($row = fgetcsv($h, 1000, ",")) !== FALSE) 
                {   
                    if ($i == 0) {
                        $i++;
                       continue;
                    }
                    $rowData = array();
                    foreach ($titleData as $titleKey => $titleValue) {
                        if ($titleValue['field'] == '') {
                            continue;
                        }
                        if(isset($value[$titleKey]) && strtotime($value[$titleKey])) {
                            $value[$titleKey] = strtotime($value[$titleKey]);
                        }
                        $rowData[$titleValue['field']] = isset($value[$titleKey]) ? $value[$titleKey] : '';                       
                    }
                    $rowData['createdAt']        = time();
                    $rowData['last_modified']    = 0;
                    $rowData['id_import']        = 1;
                    $rowData['assigned_by']      = 'By Admin';
                    array_push($insertData, $rowData);
                    $i++;
                }
              fclose($h);
            }
        }
        $this->mongo_db->switch_db();
        var_dump(($error));exit;
        echo "<pre>";
        print_r($insertData);
        echo "</pre>";
        // $this->mongo_db->batch_insert('ZACCF', $insertData);
    }
}
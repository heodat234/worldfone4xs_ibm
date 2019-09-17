<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Import_model extends CI_Model {

    private $collection = "Import";
    

    function __construct() {
        parent::__construct();
        $this->load->library('mongo_db');
        $this->load->library("session");
        $this->sub = set_sub_collection();
        $this->collection = $this->sub . $this->collection;

    }

    function importData($filePath,$duoifile,$collection)
    {
        $collection = $this->sub . $collection;

        $insertData = array();
        if ($duoifile == 'xlsx') {
            $this->load->library('excel_PHP');
            $objPHPExcel = PHPExcel_IOFactory::load($filePath);
            $maxCell = $objPHPExcel->getActiveSheet()->getHighestRowAndColumn();
            $data = $objPHPExcel->getActiveSheet()->rangeToArray('A1:'. $maxCell['column'] . $maxCell['row']);
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
                    $titleValue = trim(str_replace(".", "", $titleValue));
                    $titleValue = str_replace(" ", "_", $titleValue);
                    $titleValue = str_replace("\n", "", $titleValue);
                    if(isset($value[$titleKey]) && strtotime($value[$titleKey])) {
                        $value[$titleKey] = strtotime($value[$titleKey]);
                    }
                    $rowData[$titleValue] = isset($value[$titleKey]) ? $value[$titleKey] : '';
                }
                $rowData['createdAt']        = time();
                $rowData['Assigned_by']      = 'By Admin';
                array_push($insertData, $rowData);
            }
        }else if ($duoifile == 'csv') {
            $titleData = array();
            if (($h = fopen($filePath, "r")) !== FALSE) 
            {
                while (($rowData = fgetcsv($h, 1000, ",")) !== FALSE) 
                {     
                    array_push($titleData, $rowData);
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
                    foreach ($titleData[0] as $titleKey => $titleValue) {
                        if ($titleValue == '') {
                            continue;
                        }
                        $titleValue = trim(str_replace(".", "", $titleValue));
                        $titleValue = str_replace(" ", "_", $titleValue);
                        $titleValue = str_replace("\n", "", $titleValue);
                        if(isset($value[$titleKey]) && strtotime($value[$titleKey])) {
                            $value[$titleKey] = strtotime($value[$titleKey]);
                        }
                        $rowData[$titleValue] = isset($value[$titleKey]) ? $value[$titleKey] : '';                       
                    }
                    $rowData['createdAt']        = time();
                    $rowData['Assigned_by']      = 'By Admin';
                    array_push($insertData, $rowData);
                    $i++;
                }
              fclose($h);
            }
        }
        
        $this->mongo_db->batch_insert($collection, $insertData);
    }

    public function importFile($data)
    {
        $this->mongo_db->insert($this->collection, $data);    
    }
}
<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Import_model extends CI_Model {

    private $collection = "Import";
    

    function __construct() {
        parent::__construct();
        $this->load->library('mongo_db');
        $this->load->library("crud");
        $this->load->library("session");
        $this->sub = set_sub_collection();
        $this->collection = $this->sub . $this->collection;
        

    }

    function importData($filePath,$duoifile,$collection,$idImport)
    {
        $collection = $this->sub . $collection;

        $request = array (
          'take' => 30,
          'skip' => 0,
          'page' => 1,
          'pageSize' => 30,
          "sort" => array(array("field" => "index", "dir" => "asc"))
        );
        $this->crud->select_db($this->config->item("_mongo_db"));
        $match = array( "collection" => $collection );
        $response = $this->crud->read("Model", $request, ["index","field", "title", "type"], $match);
        if(!empty($response['data'])) {
            $titleData = $response['data'];
        }
        // var_dump($titleData);exit;
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

                    if ($type != 'n' && !is_numeric($value) && ($titleValue['type'] =='int' || $titleValue['type'] == 'double')) {
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
                // $rowData['last_modified']    = 0;
                $rowData['id_import']        = $idImport;
                if ($rowData['assign'] != '') {
                    $rowData['assigned_by']  = 'Byfixed-Import';
                }else{
                    $rowData['assigned_by']  = '';
                }
                
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
                    $rowData['id_import']        = $idImport;
                    $rowData['assigned_by']      = 'By Admin';
                    array_push($insertData, $rowData);
                    $i++;
                }
              fclose($h);
            }
        }
        $this->mongo_db->switch_db();
        if (count($error) <= 0) {
           $this->mongo_db->batch_insert($collection, $insertData);
           return 1;
        }else{
            return $error;
        }
        
    }

    public function importFile($data)
    {
        $response = $this->mongo_db->insert($this->collection, $data);    
        return $response['id'];
    }

    public function updateImportHistory($id,$data)
    {
        $this->mongo_db->where_id($id)->set($data)->update($this->collection);
    }
}
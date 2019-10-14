<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class ExcecuteExcel extends CI_Controller {

    function __construct()
    {
        parent::__construct();
        header('Content-type: application/json');
        $this->load->library("Excel");
    }

    function read()
    {
    	try {
	    	$filepath = $this->input->get("filepath");
	    	$limitColumn = $this->input->get("limit_column");
	    	$pageSize = $this->input->get("pageSize");
	    	if(!isset($pageSize)) {
                $pageSize = 5;
            }
	    	if(!$filepath) throw new Exception("Filepath is empty!!!");
	    	if(!empty($limitColumn)) {
                $res = $this->excel->read($filepath, $pageSize, 1, $limitColumn);
            }
	    	else {
                $res = $this->excel->read($filepath);
            }
	    	echo json_encode(array("status" => 1, "data" => $res["data"], "total" => $res["total"]));
    	} catch (Exception $e) {
    		echo json_encode(array("status" => 0, "message" => $e->getMessage()));
    	} 
    }

    function export()
    {
    	try {
    		if ($_SERVER['REQUEST_METHOD'] !== 'PATCH') throw new Exception("Wrong method!", 1);
			$request = json_decode(file_get_contents('php://input'), TRUE);
            if(empty($request["q"]) || empty($request["collection"])) throw new Exception("Error Processing Request", 1);
            
            $filename = !empty($request["filename"]) ? $request["filename"] : "report.xlsx";
            $query = $request["q"];
            
            $model = $this->_build_model($request["collection"]);

            $this->load->library("crud");
            $result = $this->crud->read($request["collection"], $query, array_keys($model));
            $data = $result["data"];

            $this->load->model("language_model");
            $model = $this->language_model->translate($model, "CONTENT");

            $filepath = $this->excel->write($data, $model, $filename);
            echo json_encode(array("status" => 1, "filepath" => $filepath));
    	} catch (Exception $e) {
    		echo json_encode(array("status" => 0, "message" => $e->getMessage()));
    	} 
    }

    function _build_model($collection)
    {
        $this->load->library("mongo_private");
        $model_data = $this->mongo_private->where(array("collection" => $collection))->get("Model");

        $export_model = array();
        foreach ($model_data as $doc) {
            $doc["sub_type"] = !empty($doc["sub_type"]) ? json_decode($doc["sub_type"], TRUE) : null;
            if(!empty($doc["sub_type"]["export"]))
                $export_model [] = $doc;
        }

        usort($export_model, function($a, $b) {
            $export_a = (int) $a["sub_type"]["export"];
            $export_b = (int) $b["sub_type"]["export"];
            return $export_a - $export_b;
        });
        $model = array();
        foreach($export_model as $doc) {
            $model[$doc["field"]] = $doc;
        }
        return $model;
    }
}
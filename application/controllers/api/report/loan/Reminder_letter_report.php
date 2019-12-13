<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader;
use PhpOffice\PhpSpreadsheet\Style;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

Class Reminder_letter_report extends WFF_Controller {

    private $collection = "Reminder_letter_report";
    private $model_collection = "Model";

    function __construct()
    {
        parent::__construct();
        header('Content-type: application/json');
        $this->load->library("crud");
        $this->load->library("excel");
        $this->load->library("mongo_private");
        $this->collection = set_sub_collection($this->collection);

    }

    function read() {
        try {
            $request = json_decode($this->input->get("q"), TRUE);
            $now = getdate();
            $today = $now['mday'].'-'.$now['month'].'-'.$now['year'];
            $date = strtotime("$today");
            $match = array('createdAt' => array('$gte' => $date));
            $data = $this->crud->read($this->collection, $request,array(),$match);
            echo json_encode($data);
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }



    function stringFromColumnIndex($columnIndex) {
        return $this->excel->stringFromColumnIndex($columnIndex);
    }

    function debtGroupDueDate() {
		ini_set("display_errors", 1);
		ini_set("display_startup_errors", 1);
		error_reporting(E_ALL);
		$data = array();
		$this->load->library("mongo_private");
		$debtGroupRaw = $this->mongo_private->where(array('tags' => array('debt', 'group')))->getOne($this->sub . "Jsondata");
		$dueDateRaw = $this->mongo_private->where(array('tags' => array('debt', 'duedate')))->getOne($this->sub . "Jsondata");
		if(!empty($debtGroupRaw['data']) && !empty($dueDateRaw['data'])) {
			$tempDebtGroupRaw = $debtGroupRaw['data'];
			$tempDueDateRaw = $dueDateRaw['data'];
			$debtGroup = array_column($tempDebtGroupRaw, 'text');
			$dueDate = array_column($tempDueDateRaw, 'text');
			asort($debtGroup);
			asort($dueDate);
			foreach($debtGroup as $group) {
				foreach($dueDate as $duedate) {
					array_push($data, $group . $duedate);
				}
			}
		}
		return $data;
	}
}
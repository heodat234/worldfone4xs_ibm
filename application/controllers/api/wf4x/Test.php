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
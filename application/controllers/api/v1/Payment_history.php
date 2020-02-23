<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class Payment_history extends WFF_Controller {

    private $collection = "Payment_history";

    function __construct()
    {
        parent::__construct();
        header('Content-type: application/json');
        $this->load->library("crud");
        $this->sub = set_sub_collection();
        $this->collection = $this->sub . $this->collection;
    }

    function read() {
        try {
            $request = json_decode($this->input->get("q"), TRUE);
            $response = $this->crud->read($this->collection, $request);
            $this_year = date('Y', time());
            $this_year = substr($this_year, 0,2);
            foreach ($response['data'] as $key => &$doc) {
                if(gettype($doc['payment_date']) == 'string'){
                    // 271219 -> 27/12/2019
                    $payment_date = $doc['payment_date'];
                    if(strlen($doc['payment_date']) == 5){
                        $doc['payment_date'] = '0' . $doc['payment_date'];
                    }
                    $newstr = substr_replace($payment_date, $this_year, 4, 0);
                    $newstr = substr_replace($newstr, "/", 2, 0);
                    $newstr = substr_replace($newstr, "/",5, 0);

                    $dt = DateTime::createFromFormat('d/m/Y', $newstr);
                    $payment_date_timestamp = $dt->getTimestamp();

                    $doc['overdue_days'] = (int)( $payment_date_timestamp - $doc['due_date']) / 86400;
                }else{
                    $doc['overdue_days'] = (int)($doc['payment_date'] - $doc['due_date']) / 86400;
                }
                if($doc['overdue_days'] < 0) $doc['overdue_days'] =0;
                $doc['overdue_days'] = floor($doc['overdue_days']);
                $doc['overdue_amount'] = (int)str_replace(',','',$doc['overdue_amount']);
                if($doc['payment_amount'] >= $doc['overdue_amount'] && $doc['overdue_days'] >= 10){
                    $doc['appear_count'] =1;
                }else{
                    $doc['appear_count'] =0;
                }
            }
            echo json_encode($response);
        } catch(Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }
    }
}
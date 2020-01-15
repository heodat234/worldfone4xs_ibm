<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Campaign_divide_rule extends WFF_Controller
{

    private $sub = "";

    public function __construct()
    {
        parent::__construct();
        header('Content-type: application/json');
        $this->sub = set_sub_collection("");
        error_reporting(E_ALL);
        $this->load->library("mongo_db");
    }

    public function read_card_group_A()
    {
        $mongodb = $this->mongo_db;
        $groupA = $mongodb->where(
            array(
                '$and' => array(
                    ['name' => ['$regex' => 'Card']],
                    ['name' => ['$regex' => 'Group A']],
                ),
            )
        )->get('LO_Group');

        $result = [];
        foreach ($groupA as $key => $group) {
            $result[] = $group['name'];
        }

        echo json_encode($result);
    }

    public function saveRuleCard(){
        $mongodb = $this->mongo_db;
        $request = json_decode(file_get_contents('php://input'), TRUE);
        $config = [];
        foreach ($request as $key => $value) {
            $data = ['debt_group' => $key, 'group_divided' => $value,'type' => 'CARD'];
            $mongodb->where('debt_group', $key)->update('LO_Campaign_divide_rule', $data, array('upsert' => true));
        }
        echo 1;
    }

    public function readRuleCard(){
        $mongodb = $this->mongo_db;
        $data = $mongodb->where('type','CARD')->get('LO_Campaign_divide_rule');

        echo json_encode($data);
    }

}
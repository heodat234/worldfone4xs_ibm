<?php

require_once dirname(__DIR__) . "/Header.php";
require_once "autoCreateDiallist_Config.php";
require_once "autoCreateDialDetail.php";

CARD_processA();
CARD_processA2();
CARD_process();
 
function CARD_processA() {
    global $mongo_db;
    global $CARD_GroupA;
    global $today;
    $TYPE = "CARD";
        
    foreach ($CARD_GroupA as $key => $group_name) {
            $campaign_name =  $TYPE ."_" .'A01'. "_" . $today;
            $group = getGroupByName($group_name);
            $a_type = substr($group_name, -3);
            brint($campaign_name);

            $diallist_init = array(
                "target"        => 90,
                "name"          => $group['name'] . ' ' . $today,
                "team"          => $TYPE,
                "members"       => $group['members'],
                "group_name"    => $group['name'],
                "group_id"      => $group['id'],
                "mode"          => "manual",
                "leader_assign" => isset($group['lead']) ? $group['lead'] : 'empty',
                "createdBy"     => "System",
                "createdAt"     => time(),
                "loan_campaign_name" => $campaign_name,
                "runStatus"     => false,
            );
            $members = null;
            if(checkGroupA($campaign_name)){
                $members = $group['members'];
            }
            $diallist_id = $mongo_db->insert('LO_Diallist', $diallist_init)["id"];
            $index = $mongo_db->where_object_id("diallist_id", $diallist_id)->count('LO_Diallist_detail');
            importFrom_Loan_campaign_listA($TYPE, $campaign_name, $diallist_id, $index, $a_type, $group['members']);
    }
}

function CARD_processA2() {
    global $mongo_db;
    global $CARD_GroupA2;
    global $today;
    $TYPE = "CARD";
        
    foreach ($CARD_GroupA2 as $key => $group_name) {
            $campaign_name =  $TYPE ."_" .'A02'. "_" . $today;
            $group = getGroupByName($group_name);
            $a_type = substr($group_name, -3);
            brint($campaign_name);

            $diallist_init = array(
                "target"        => 90,
                "name"          => $group['name'] . ' ' . $today,
                "team"          => $TYPE,
                "members"       => $group['members'],
                "group_name"    => $group['name'],
                "group_id"      => $group['id'],
                "leader_assign" => isset($group['lead']) ? $group['lead'] : 'empty',
                "mode"          => "manual",
                "createdBy"     => "System",
                "createdAt"     => time(),
                "loan_campaign_name" => $campaign_name,
                "runStatus"     => false,
            );
            $members = null;
            if(checkGroupA($campaign_name)){
                $members = $group['members'];
            }
            $diallist_id = $mongo_db->insert('LO_Diallist', $diallist_init)["id"];
            $index = $mongo_db->where_object_id("diallist_id", $diallist_id)->count('LO_Diallist_detail');
            importFrom_Loan_campaign_listA($TYPE, $campaign_name, $diallist_id, $index, $a_type, $group['members']);
    }
}

function CARD_process() {
    global $mongo_db;
    global $CARD_Group;
    global $today;

    $TYPE = "CARD";
        
    foreach ($CARD_Group as $key => $group_name) {
       $group_id = getGroupIdByName($group_name);
       if($group_id){
            $campaign_name =  $TYPE ."_" .$group_id. "_" . $today;
            $group = getGroupByName($group_name);
            brint($campaign_name);

            $diallist_init = array(
                "target"        => 90,
                "name"          => $group['name'] . ' ' . $today,
                "team"          => $TYPE,
                "members"       => $group['members'],
                "group_name"    => $group['name'],
                "group_id"      => $group['id'],
                "mode"          => "manual",
                "createdBy"     => "System",
                "createdAt"     => time(),
                "loan_campaign_name" => $campaign_name,
                "runStatus"     => false,
            );
            $members = null;
            if(checkGroupA($campaign_name)){
                $members = $group['members'];
            }
            $diallist_id = $mongo_db->insert('LO_Diallist', $diallist_init)["id"];
            $index = $mongo_db->where_object_id("diallist_id", $diallist_id)->count('LO_Diallist_detail');
            importFrom_Loan_campaign_list($TYPE, $campaign_name, $diallist_id, $index, $members);
        }
    }
}

function checkGroupA($group_name) {
    if(strpos($group_name, '_A0') == true){
        return false; 
    }
    return true;
}


function getGroupByName($name) {
    global $mongo_db;
    $group = $mongo_db->where('name', $name)->getOne('LO_Group');
    return $group;
}

function getLeaderOfGroupByName($name) {
    global $mongo_db;

    $group = getGroupByName($name);

    if(empty($group['lead'])) return 0;
    return $group['lead'] ? 'JIVF00'.$group['lead'] : 0;
}

function getGroupIdByName($name) {
    global $mongo_db;

    $group = getGroupByName($name);

    if(empty($group)) return 0;
    return isset($group['debt_groups']) ? $group['debt_groups'][0] : 0;

}

function getLoanCampaign($name) {
    global $mongo_db;
    $mongo_db->switch_db('LOAN_campaign_list');
    $data = $mongo_db->get($name);

    $mongo_db->switch_db('worldfone4xs');
    return $data;
}

function brint($txt) {
    if(gettype($txt) != 'object' && gettype($txt) != 'array')
        print_r($txt . PHP_EOL);
    else
        print_r($txt);
}
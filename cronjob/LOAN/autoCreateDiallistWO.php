<?php

require_once dirname(__DIR__) . "/Header.php";
require_once "autoCreateDiallist_Config.php";
require_once "autoCreateDialDetail.php";

WO_process();

function WO_process() {
    global $mongo_db;
    global $today;

    $TYPE = "WO";
        
            $campaign_name =  $TYPE . "_" . $today;
            $group = getGroupByName('WO');
            brint($campaign_name);

            $diallist_init = array(
                "target"        => 90,
                "name"          => $group['name'] . ' ' . $today,
                "team"          => $TYPE,
                "members"       => $group['members'],
                "group_name"    => $group['name'],
                "group_id"      => $group['id'],
                "mode"          => "manual",
                "leader_assign" => $group['lead'],
                "createdBy"     => "System",
                "createdAt"     => time(),
                "loan_campaign_name" => $campaign_name,
                "runStatus"     => false,
            );
            
            $diallist_id = $mongo_db->insert('LO_Diallist', $diallist_init)["id"];
            $index = $mongo_db->where_object_id("diallist_id", $diallist_id)->count('LO_Diallist_detail');
            importFrom_Loan_campaign_list($TYPE, $campaign_name, $diallist_id, $index, $group['members']);
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
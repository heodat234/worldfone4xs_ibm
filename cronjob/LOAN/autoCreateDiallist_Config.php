<?php

ini_set("log_errors", 1);
error_reporting(E_ALL);
ini_set("error_log", __DIR__ . "autoCreateDial_Logs.txt");

use Pheanstalk\Pheanstalk;
$queue = new Pheanstalk('127.0.0.1');

$mongo_db = new Mongo_db();
$today = date('Y-m-d', time());

// $SIBS_Group_A = array(
//     "SIBS/Group A/Team Yến Vân/G3",
//     "SIBS/Group A/Team Yến Vân/G2",
//     "SIBS/Group A/Team Yến Vân/G1",
//     "SIBS/Group A/Team Thúy Hậu/G3",
//     "SIBS/Group A/Team Thúy Hậu/G2",
//     "SIBS/Group A/Team Thúy Hậu/G1",
//     "SIBS/Group A/Team Hồng Cúc/G3",
//     "SIBS/Group A/Team Hồng Cúc/G2",
//     "SIBS/Group A/Team Hồng Cúc/G1",
//     "SIBS/Group A/Team Kim Tuyền/G3",
//     "SIBS/Group A/Team Kim Tuyền/G2",
//     "SIBS/Group A/Team Kim Tuyền/G1",
//     "SIBS/Group A/Team Vũ Tin/G3",
//     "SIBS/Group A/Team Vũ Tin/G2",
//     "SIBS/Group A/Team Vũ Tin/G1",
//     "SIBS/Group A/Team Đỗ Nga/G3",
//     "SIBS/Group A/Team Đỗ Nga/G2",
//     "SIBS/Group A/Team Đỗ Nga/G1",
// );
$SIBS_Group_A_contain = $mongo_db->where(array('debt_type' => 'SIBS', 'debt_group' => 'Group A'))->get('LO_Group_mapping_campaign');

foreach ($SIBS_Group_A_contain as $value) {
    $SIBS_Group_A[] = $value['name'];
}

// $SIBS_Group_Others = array(
//     'SIBS/Group B/B01',

//     'SIBS/Group B/B02',

//     'SIBS/Group B/B03',

//     'SIBS/Group C/C01',

//     'SIBS/Group C/C02',

//     'SIBS/Group C/C03',
// );
$SIBS_Group_B_contain = $mongo_db->where(array('debt_type' => 'SIBS', 'debt_group' => 'Group B'))->get('LO_Group_mapping_campaign');
$SIBS_Group_C_contain = $mongo_db->where(array('debt_type' => 'SIBS', 'debt_group' => 'Group C'))->get('LO_Group_mapping_campaign');

foreach ($SIBS_Group_B_contain as $value) {
    $SIBS_Group_Others[] = $value['name'];
}
foreach ($SIBS_Group_C_contain as $value) {
    $SIBS_Group_Others[] = $value['name'];
}

// $SIBS_Group_D_E = array(
//     'SIBS/Group D/D01',

//     'SIBS/Group D/D02',

//     'SIBS/Group D/D03',

//     'SIBS/Group E/E01',

//     'SIBS/Group E/E02',

//     'SIBS/Group E/E03',
// );
$SIBS_Group_D_contain = $mongo_db->where(array('debt_type' => 'SIBS', 'debt_group' => 'Group D'))->get('LO_Group_mapping_campaign');
$SIBS_Group_E_contain  = $mongo_db->where(array('debt_type' => 'SIBS', 'debt_group' => 'Group E'))->get('LO_Group_mapping_campaign');

foreach ($SIBS_Group_D_contain as $value) {
    $SIBS_Group_D_E[] = $value['name'];
}
foreach ($SIBS_Group_E_contain as $value) {
    $SIBS_Group_D_E[] = $value['name'];
}


// $CARD_GroupA = array(
//     'Card/Group A/Team Nguyễn Phượng/G1',
//     'Card/Group A/Team Nguyễn Phượng/G2',
//     'Card/Group A/Team Nguyễn Phượng/G3',
// );
// $CARD_GroupA2 = array(
//     'Card/Group A/Team Thùy Trang/G1',
//     'Card/Group A/Team Thùy Trang/G2',
//     'Card/Group A/Team Thùy Trang/G3',
// );

$CARD_Group_A_contain = $mongo_db->where(array('debt_group' => 'Group A'))->like('debt_type','card')->get('LO_Group_mapping_campaign');

foreach ($CARD_Group_A_contain as $value) {
    $CARD_GroupA[] = $value['name'];
}

// $CARD_Group = array(

//     'Card/Group B/B01',

//     'Card/Group B/B02',

//     'Card/Group B/B03',

//     'Card/Group C/C01',

//     'Card/Group C/C02',

//     'Card/Group C/C03',

//     'Card/Group D/D01',

//     'Card/Group D/D02',

//     'Card/Group D/D03',

//     'Card/Group E/E01',

//     'Card/Group E/E02',

//     'Card/Group E/E03',
// );

$CARD_Group_B_plus_contain = $mongo_db->where(array('debt_group' => array('$ne' => 'Group A')))->like('debt_type','card')->get('LO_Group_mapping_campaign');

foreach ($CARD_Group_B_plus_contain as $value) {
    $CARD_Group_B_plus[] = $value['name'];
}

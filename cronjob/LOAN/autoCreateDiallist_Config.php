<?php

ini_set("log_errors", 1);
error_reporting(E_ALL);
ini_set("error_log", __DIR__ . "autoCreateDial_Logs.txt");

use Pheanstalk\Pheanstalk;
$queue = new Pheanstalk('127.0.0.1');

$mongo_db               = new Mongo_db();
$today                  = date('Y-m-d', time());

$SIBS_Group_A = array(
    'SIBS/Group A/Team Trà Mi/A01' ,
    'SIBS/Group A/Team Trà Mi/A02' ,
    'SIBS/Group A/Team Trà Mi/A03' ,
    'SIBS/Group A/Team Vĩnh An/A01' ,
    'SIBS/Group A/Team Vĩnh An/A02' ,
    'SIBS/Group A/Team Vĩnh An/A03' ,
    'SIBS/Group A/Team Kim Tuyền/A01',
    'SIBS/Group A/Team Kim Tuyền/A02',
    'SIBS/Group A/Team Kim Tuyền/A03',
    'SIBS/Group A/Team Phương Đông/A01',
    'SIBS/Group A/Team Phương Đông/A02',
    'SIBS/Group A/Team Phương Đông/A03',
);

$SIBS_Group_Others = array(
    'SIBS/Group B/B01',

    'SIBS/Group B/B02',

    'SIBS/Group B/B03',

    'SIBS/Group C/C01',

    'SIBS/Group C/C02',

    'SIBS/Group C/C03',

    'SIBS/Group D/D01',

    'SIBS/Group D/D02',

    'SIBS/Group D/D03',

    'SIBS/Group E/E01',

    'SIBS/Group E/E02',

    'SIBS/Group E/E03',
);
$CARD_GroupA = array(
    'Card/Group A/Team Nguyễn Phượng/A01',
    'Card/Group A/Team Nguyễn Phượng/A02',
    'Card/Group A/Team Nguyễn Phượng/A03',
);
$CARD_GroupA2 = array(
    'Card/Group A/Team Thùy Trang/A01',
    'Card/Group A/Team Thùy Trang/A02',
    'Card/Group A/Team Thùy Trang/A03',
);
$CARD_Group = array(

    'Card/Group B/B01',

    'Card/Group B/B02',

    'Card/Group B/B03',

    'Card/Group C/C01',

    'Card/Group C/C02',

    'Card/Group C/C03',

    'Card/Group D/D01',

    'Card/Group D/D02',

    'Card/Group D/D03',

    'Card/Group E/E01',

    'Card/Group E/E02',

    'Card/Group E/E03',
);
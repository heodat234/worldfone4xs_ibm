<?php

require_once dirname(__DIR__) . "../../Header.php";
$mongo_db               = new Mongo_db();
$_mongo_db               = new Mongo_db();

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$collection = 'TS_Telesalelist';
$exportCollection = 'TS_Export';

$total = 0;
$complete = 0;

if(!empty($argv[1])) {
    $exportLogId = (string)$argv[1];
    $exportInfo = $mongo_db->where_id($exportLogId)->getOne($exportCollection);

    $model = $mongo_db->where(array('collection' => $collection))->get('Model')
}
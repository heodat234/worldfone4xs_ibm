<?php

require_once dirname(__DIR__) . "../../Header.php";
$mongo_db = new Mongo_db();

Run();

function Run()
{
    global $mongo_db;
    $sevendays = strtotime("-4 week");

    $diallist = $mongo_db->
        where(array('createdAt' => array('$lt' => $sevendays)))->
        delete_all('LO_Diallist_detail');

}

function godown()
{
    echo PHP_EOL;
}
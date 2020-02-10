<?php
require_once dirname(__DIR__) . "../../Header.php";
use Pheanstalk\Pheanstalk;
$queue = new Pheanstalk('127.0.0.1');

function pushToQueueImport($doc) {
  global $queue;

  $queueData = array(
      "startTimestamp"    => time(),
      "doc"               => $doc,
      "collection"        => 'LO_Relationship',
      "key_field"         => 'raw'
  );

  $queue->useTube('import')->put(json_encode($queueData));
}

function func_getKyDue($overdue_date){
  $check = (int)date('d', $overdue_date);
  if($check >= 12 && $check <= 21){
    $kydue = '01';
  }else if($check >= 22 && $check <= 30){
    $kydue = '02';
  }else{
    $kydue = '03';
  }

  return $kydue;

}

function func_define_A_type($param_overdue_date){
  $check = (int)date('d', $param_overdue_date);
  if($check >= 12 && $check <= 21){
    $kydue = 'A01';
  }else if($check >= 22 && $check <= 30){
    $kydue = 'A02';
  }else{
    $kydue = 'A03';
  }

  return $kydue;
}

function func_define_G_type($param_overdue_date){
  $_temp = time() - $param_overdue_date;
  $overdue_days = floor($_temp / (3600 * 24));

  if($overdue_days >= 1 && $overdue_days <= 6){
    return 'G1';
  }else if($overdue_days >= 7 && $overdue_days <= 19){
    return 'G2';
  }else if($overdue_days >= 20 && $overdue_days <= 40){
    return 'G3';
  }else{
    return '>40';
  }
}

function func_createRelationshipTable($zaccf, $account_number){

  $REF = [];
  $phoneArr = [];

  if(!empty($zaccf["WRK_REF"]) && $zaccf["WRK_REF"] != null && $zaccf["WRK_REF"] != '' && $zaccf["WRK_REF"] != ' '){

    $raw        = $zaccf['WRK_REF'];
    $splited    = explode("-", $raw);

    if(count($splited) >=3){
      $REF[0]['account_number']   = $account_number;
      $REF[0]['PRODGRP_ID']       = $zaccf['PRODGRP_ID'];
      $REF[0]['LIC_NO']           = $zaccf['LIC_NO'];
      $REF[0]['name']             = $splited[0];
      $REF[0]['relation']         = $splited[1];
      $REF[0]['phone']            = $splited[count($splited) - 1];
      $phoneArr[]                 = $REF[0]['phone'];
    }

    $REF[0]['raw']                  = $raw;

    pushToQueueImport($REF[0]);
  }

  for($i=1; $i<=7; $i++){
    if(!empty($zaccf["WRK_REF$i"]) && $zaccf["WRK_REF$i"] != null && $zaccf["WRK_REF$i"] != '' && $zaccf["WRK_REF$i"] != ' '){
      $raw = $zaccf["WRK_REF$i"];
      $splited = explode("-", $raw);
      if(count($splited) >=3){
       $REF[$i]['account_number']   = $account_number;
       $REF[$i]['PRODGRP_ID']       = $zaccf['PRODGRP_ID'];
       $REF[$i]['LIC_NO']           = $zaccf['LIC_NO'];
       $REF[$i]['name']             = $splited[0];
       $REF[$i]['relation']         = $splited[1];
       $REF[$i]['type']             = $splited[count($splited) - 2];
       $REF[$i]['phone']            = $splited[count($splited) - 1];
       $phoneArr[]                  = $REF[$i]['phone'];
     }
     $REF[$i]['raw']     = $raw;

     pushToQueueImport($REF[$i]);
   }
 }

 return $phoneArr;
}

function func_reorder_array($array){
    $result = [];
    
    if(count($array) <= 1) return $array;
    $temp = $array[0];
    for($i=0; $i< count($array) -1;$i++){
        $result[$i] = $array[$i+1];
    }
    $result[count($array) - 1] = $temp;

    return $result;
}


?>
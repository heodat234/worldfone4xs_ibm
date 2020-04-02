<?php
$username = 'worldfone4x';
$password = 'St3l37779db';
$collection = 'LO_worldfonepbxmanager';
$db = 'worldfone4xs';

$export_name = 'LO_worldfonepbxmanager.json';
$path_export = __DIR__ .'/'. $export_name;
$limit = 100000;

$command = "mongoexport --username {$username} --password {$password} --authenticationDatabase admin --db $db --collection $collection --sort='{_id:-1}' --limit=$limit --out=$path_export";
$result = exec($command);
echo $result;
//mongoimport --db worldfone4xs --collection LO_Diallist_detail --mode=upsert --file=/var/www/html/worldfone4xs_ibm/cronjob/LOAN/mongo_export/LO_Diallist_detail.json

 ?>
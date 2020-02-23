<?php

shell_exec('curl "http://127.0.0.1:7777/wfpbx/pbxevents?secret=a357e8e5fbce92dd44269146416b0b4d&callstatus=Dialing&calluuid=1581674134.10&direction=outbound&callernumber=999&destinationnumber=0986322412&agentname=ADMIN+TEST&starttime=20200214T165548&dnis=0862858729&calltype=Outbound_non-ACD&version=3"');

// function Fibonacci($number)
// {

//     if ($number == 0) {
//         return 0;
//     } else if ($number == 1) {
//         return 1;
//     } else {
//         return (Fibonacci($number - 1) +
//             Fibonacci($number - 2));
//     }

// }

// $number = 100;
// $starttime = microtime(true);
// echo Fibonacci($number);

// $endtime = microtime(true);
// echo PHP_EOL . ($endtime - $starttime) . PHP_EOL;
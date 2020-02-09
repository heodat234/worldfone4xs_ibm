#!/bin/bash

if [ $(ps -ef | grep -v grep | grep worker-agentstatus.php | wc -l) -lt 1 ]; then
   /usr/bin/php /var/www/html/worldfone4xs/cronjob/worker-agentstatus.php  > /dev/null 2>&1 &
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

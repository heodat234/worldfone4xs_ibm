#!/bin/bash

if [ $(ps -ef | grep -v grep | grep worker-import.php | wc -l) -lt 1 ]; then
   /usr/bin/php /var/www/html/worldfone4xs_ibm/cronjob/worker-import.php  > /dev/null 2>&1 &
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

#!/bin/bash

if [ $(ps -ef | grep -v grep | grep worker-pushData.php | wc -l) -lt 1 ]; then
   /usr/bin/php /data/worldfone4x/application/third_party/cli/worker-pushData.php > /dev/null 2>&1 &
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

#!/bin/bash

if [ $(ps -ef | grep -v grep | grep worker-scanJob.php | wc -l) -lt 8 ]; then
   /usr/bin/nohup /usr/bin/php  /var/www/worldfone4x_dev/worldfone4x/application/third_party/cli/worker-scanJob.php </dev/null &>/dev/null &
else
   echo "Maximum process are running"
   exit 0
fi

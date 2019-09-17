#!/bin/bash

if [ $(ps -ef | grep -v grep | grep worker-scanJob.php | wc -l) -lt 4 ]; then
    /usr/bin/php  /data/worldfone4x/application/third_party/cli/worker-scanJob2.php </dev/null &>/dev/null &
else
   echo "Maximum process are running"
   exit 0
fi

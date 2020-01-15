#!/bin/bash

BASEDIR=$(dirname "$0")
if [ $(ps -ef | grep -v grep | grep worker-currentcall.php | wc -l) -lt 1 ]; then
   /usr/bin/nohup /usr/bin/php "${BASEDIR}/worker-currentcall.php" > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/worker-currentcall.php"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

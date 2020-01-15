#!/bin/bash

BASEDIR=$(dirname "$0")
if [ $(ps -ef | grep -v grep | grep worker-call.php | wc -l) -lt 4 ]; then
   /usr/bin/php "${BASEDIR}/worker-call.php" > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/worker-call.php"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

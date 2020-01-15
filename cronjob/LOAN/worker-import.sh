#!/bin/bash

BASEDIR=$(dirname "$0")
if [ $(ps -ef | grep -v grep | grep worker-import.php | wc -l) -lt 20 ]; then
   /usr/bin/php "${BASEDIR}/worker-import.php" > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/worker-import.php"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

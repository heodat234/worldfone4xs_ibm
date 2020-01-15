#!/bin/bash

BASEDIR=$(dirname "$0")
if [ $(ps -ef | grep -v grep | grep worker-followup.php | wc -l) -lt 1 ]; then
   /usr/bin/php "${BASEDIR}/worker-followup.php" > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/worker-followup.php"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

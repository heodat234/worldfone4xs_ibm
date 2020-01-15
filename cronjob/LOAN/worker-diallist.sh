#!/bin/bash

BASEDIR=$(dirname "$0")
if [ $(ps -ef | grep -v grep | grep worker-diallist.php | wc -l) -lt 1 ]; then
   /usr/bin/php "${BASEDIR}/worker-diallist.php" > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/worker-diallist.php"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

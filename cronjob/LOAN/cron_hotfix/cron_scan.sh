#!/bin/bash

BASEDIR=$(dirname "$0")
echo "${BASEDIR}"
if [ $(ps -ef | grep -v grep | grep cron_scan.php | wc -l) -lt 1 ]; then
   /usr/bin/php ${BASEDIR}/cron_scan.php  > /dev/null 2>&1 &
else
   echo "Worldfone cron_scan service is running"
   exit 0
fi

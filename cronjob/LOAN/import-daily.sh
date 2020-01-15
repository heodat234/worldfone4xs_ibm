#!/bin/bash

BASEDIR=$(dirname "$0")
if [ $(ps -ef | grep -v grep | grep importZACCF.php | wc -l) -lt 1 ]; then
   /usr/bin/php ${BASEDIR}/importZACCF.php > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/importZACCF.php"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

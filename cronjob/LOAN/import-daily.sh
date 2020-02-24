#!/bin/bash

BASEDIR=$(dirname "$0")
if [ $(ps -ef | grep -v grep | grep importZACCF.php | wc -l) -lt 1 ]; then
   /usr/bin/php ${BASEDIR}/importZACCF.php > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/importZACCF.php"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

BASEDIR=$(dirname "$0")
if [ $(ps -ef | grep -v grep | grep importZACCFYesterday.php | wc -l) -lt 1 ]; then
   /usr/bin/php ${BASEDIR}/importZACCFYesterday.php > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/importZACCFYesterday.php"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

BASEDIR=$(dirname "$0")
if [ $(ps -ef | grep -v grep | grep importZACCFReport.php | wc -l) -lt 1 ]; then
   /usr/bin/php ${BASEDIR}/importZACCFReport.php > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/importZACCFReport.php"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

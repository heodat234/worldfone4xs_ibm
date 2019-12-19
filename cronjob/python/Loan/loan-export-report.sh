#!/bin/bash

BASEDIR=$(dirname "$0")
if [ $(ps -ef | grep -v grep | grep exportDailyPayment.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 ${BASEDIR}/exportDailyPayment.py > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/exportDailyPayment.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

if [ $(ps -ef | grep -v grep | grep exportMasterData.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 ${BASEDIR}/exportMasterData.py > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/exportMasterData.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

if [ $(ps -ef | grep -v grep | exportDailyAssignment.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 ${BASEDIR}/exportDailyAssignment.py > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/exportDailyAssignment.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

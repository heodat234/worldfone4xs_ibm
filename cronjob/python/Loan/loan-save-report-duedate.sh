#!/bin/bash

BASEDIR=$(dirname "$0")
if [ $(ps -ef | grep -v grep | grep calDueDateValue.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 ${BASEDIR}/calDueDateValue.py > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/calDueDateValue.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

if [ $(ps -ef | grep -v grep | grep calDueDateValueByGroup.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 ${BASEDIR}/calDueDateValueByGroup.py > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/calDueDateValueByGroup.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

if [ $(ps -ef | grep -v grep | grep updateGroupCard.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 ${BASEDIR}/updateGroupCard.py > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/updateGroupCard.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

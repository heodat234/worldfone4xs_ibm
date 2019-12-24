#!/bin/bash

BASEDIR=$(dirname "$0")

if [ $(ps -ef | grep -v grep | grep saveMasterData.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 ${BASEDIR}/saveMasterData.py > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/saveMasterData.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

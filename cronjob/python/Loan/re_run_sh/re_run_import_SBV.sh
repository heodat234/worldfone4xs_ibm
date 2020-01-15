#!/bin/bash

BASEDIR=$(dirname "$0")
BASEDIR=$(dirname "$BASEDIR")

if [ $(ps -ef | grep -v grep | grep importSBV.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 ${BASEDIR}/importSBV.py > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/importSBV.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi
#!/bin/bash

BASEDIR=$(dirname "$0")
BASEDIR=$(dirname "$BASEDIR")

if [ $(ps -ef | grep -v grep | grep importListOfAccount.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 ${BASEDIR}/importListOfAccount.py > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/importListOfAccount.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi
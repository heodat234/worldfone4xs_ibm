#!/bin/bash

BASEDIR=$(dirname "$0")
if [ $(ps -ef | grep -v grep | grep importDataLibrary.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 ${BASEDIR}/importDataLibrary.py > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/importDataLibrary.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

if [ $(ps -ef | grep -v grep | grep importTelesale.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 ${BASEDIR}/importTelesale.py > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/importTelesale.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

#!/bin/bash

BASEDIR=$(dirname "$0")
FILENAME=$1

if [ $(ps -ef | grep -v grep | grep ${FILENAME}.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 ${BASEDIR}/${FILENAME}.py > /dev/null 2>&1 &
   # python3.6 ${BASEDIR}/${FILENAME}.py > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/${FILENAME}.py"
   echo "/usr/local/bin/python3.6 ${BASEDIR}/${FILENAME}.py > /dev/null 2>&1 &"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

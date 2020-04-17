#!/bin/bash

BASEDIR=$(dirname "$0")

if [ $(ps -ef | grep -v grep | grep totalListOfAllCustomer.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 $BASEDIR/totalListOfAllCustomer.py > /dev/null 2>&1 &
   echo "RUN $BASEDIR/totalListOfAllCustomer.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi
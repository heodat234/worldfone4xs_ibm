#!/bin/bash

BASEDIR=$(dirname "$0")
if [ $(ps -ef | grep -v grep | grep importLNJC05F.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 ${BASEDIR}/importLNJC05F.py > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/importLNJC05F.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0

if [ $(ps -ef | grep -v grep | grep importListOfAccount.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 ${BASEDIR}/importListOfAccount.py > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/importListOfAccount.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0

if [ $(ps -ef | grep -v grep | grep importLN3206F.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 ${BASEDIR}/importLN3206F.py > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/importLN3206F.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0

if [ $(ps -ef | grep -v grep | grep importReportInputPaymentOfCard.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 ${BASEDIR}/importReportInputPaymentOfCard.py > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/importReportInputPaymentOfCard.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0

if [ $(ps -ef | grep -v grep | grep importReportInputPaymentOfCard.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 ${BASEDIR}/importReportInputPaymentOfCard.py > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/importReportInputPaymentOfCard.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0

if [ $(ps -ef | grep -v grep | grep importSBV.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 ${BASEDIR}/importSBV.py > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/importSBV.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

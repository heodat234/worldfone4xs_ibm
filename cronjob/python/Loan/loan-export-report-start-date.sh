#!/bin/bash

BASEDIR=$(dirname "$0")

if [ $(ps -ef | grep -v grep | grep exportMasterData.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 ${BASEDIR}/exportMasterData.py > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/exportMasterData.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

if [ $(ps -ef | grep -v grep | grep exportSmsDailyReport.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 ${BASEDIR}/exportSmsDailyReport.py > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/exportSmsDailyReport.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

if [ $(ps -ef | grep -v grep | grep exportDailyPayment.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 ${BASEDIR}/exportDailyPayment.py > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/exportDailyPayment.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

if [ $(ps -ef | grep -v grep | grep exportTendencyDelinquent.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 ${BASEDIR}/exportTendencyDelinquent.py > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/exportTendencyDelinquent.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

if [ $(ps -ef | grep -v grep | grep exportFirsttimePaymentDelinquency.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 ${BASEDIR}/exportFirsttimePaymentDelinquency.py > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/exportFirsttimePaymentDelinquency.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

if [ $(ps -ef | grep -v grep | grep exportReminderLetter.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 ${BASEDIR}/exportReminderLetter.py > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/exportReminderLetter.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

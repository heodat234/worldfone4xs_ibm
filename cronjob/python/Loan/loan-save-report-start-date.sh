#!/bin/bash

BASEDIR=$(dirname "$0")

if [ $(ps -ef | grep -v grep | grep saveMasterData.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 ${BASEDIR}/saveMasterData.py > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/saveMasterData.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

if [ $(ps -ef | grep -v grep | grep saveSmsDaily.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 ${BASEDIR}/saveSmsDaily.py > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/saveSmsDaily.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

if [ $(ps -ef | grep -v grep | grep clearSmallDaily.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 ${BASEDIR}/clearSmallDaily.py > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/clearSmallDaily.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

if [ $(ps -ef | grep -v grep | grep saveBlockCardReport.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 ${BASEDIR}/saveBlockCardReport.py > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/saveBlockCardReport.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

if [ $(ps -ef | grep -v grep | grep saveCardLoanGroupReport.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 ${BASEDIR}/saveCardLoanGroupReport.py > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/saveCardLoanGroupReport.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

if [ $(ps -ef | grep -v grep | grep saveReminderLetter.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 ${BASEDIR}/saveReminderLetter.py > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/saveReminderLetter.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

if [ $(ps -ef | grep -v grep | grep saveDailyPayment.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 ${BASEDIR}/saveDailyPayment.py > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/saveDailyPayment.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi


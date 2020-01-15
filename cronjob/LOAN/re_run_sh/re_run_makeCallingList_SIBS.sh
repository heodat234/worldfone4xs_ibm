#!/bin/bash
BASEDIR=$(dirname "$0")
BASEDIR=$(dirname "$BASEDIR")
echo "$BASEDIR"

if [ $(ps -ef | grep -v grep | grep trigger_makeCallingList_MAIN_A.php | wc -l) -lt 1 ]; then
   /usr/bin/php ${BASEDIR}/trigger_makeCallingList_MAIN_A.php  > /dev/null 2>&1 &
else
   echo "Worldfone trigger_makeCallingList_MAIN_A service is running"
   exit 0
fi

if [ $(ps -ef | grep -v grep | grep makeCallingList_MAIN_B_plus.php | wc -l) -lt 1 ]; then
   /usr/bin/php ${BASEDIR}/makeCallingList_MAIN_B_plus.php  > /dev/null 2>&1 &
else
   echo "Worldfone makeCallingList_MAIN_B_plus service is running"
   exit 0
fi
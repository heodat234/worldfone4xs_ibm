#!/bin/bash
BASEDIR=$(dirname "$0")
BASEDIR=$(dirname "$BASEDIR")
echo "$BASEDIR"


if [ $(ps -ef | grep -v grep | grep makeCallingList_CARD.php | wc -l) -lt 1 ]; then
   /usr/bin/php ${BASEDIR}/makeCallingList_CARD.php  > /dev/null 2>&1 &
else
   echo "Worldfone makeCallingList_CARD service is running"
   exit 0
fi

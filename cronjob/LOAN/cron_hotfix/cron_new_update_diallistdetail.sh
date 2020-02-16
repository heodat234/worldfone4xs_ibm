#!/bin/bash

BASEDIR=$(dirname "$0")
echo "${BASEDIR}"


if [ $(ps -ef | grep -v grep | grep cron_update_CARD.php | wc -l) -lt 1 ]; then
   /usr/bin/php ${BASEDIR}/cron_update_CARD.php  > /dev/null 2>&1 &
else
   echo "Worldfone cron_update_CARD service is running"
   exit 0
fi

if [ $(ps -ef | grep -v grep | grep cron_update_SIBS.php | wc -l) -lt 1 ]; then
   /usr/bin/php ${BASEDIR}/cron_update_SIBS.php  > /dev/null 2>&1 &
else
   echo "Worldfone cron_update_SIBS service is running"
   exit 0
fi

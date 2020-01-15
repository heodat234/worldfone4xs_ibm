#!/bin/bash

BASEDIR=$(dirname "$0")
BASEDIR=$(dirname "$BASEDIR")
echo "$BASEDIR"

if [ $(ps -ef | grep -v grep | grep autoCreateDiallistSIBS.php | wc -l) -lt 1 ]; then
   /usr/bin/php ${BASEDIR}/autoCreateDiallistSIBS.php  > /dev/null 2>&1 &
else
   echo "Worldfone autoCreateDiallistSIBS service is running"
   exit 0
fi
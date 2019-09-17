#!/bin/bash

if [ $(ps -ef | grep -v grep | grep service-scanjob.php | wc -l) -lt 1 ]; then
   /usr/bin/phpscanjob /var/www/worldfone4x_prod/worldfone4x/application/third_party/cli/service-scanJob.php
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

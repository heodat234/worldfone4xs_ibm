#!/bin/sh
BASEDIR=$(dirname "$0")

if [ $(ps -ef | grep -v grep | grep saveDailyAssignment.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 $BASEDIR/saveDailyAssignment.py > /dev/null 2>&1 &
   echo "RUN $BASEDIR/saveDailyAssignment.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

if [ $(ps -ef | grep -v grep | grep saveDailyPayment.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 $BASEDIR/saveDailyPayment.py > /dev/null 2>&1 &
   echo "RUN $BASEDIR/saveDailyPayment.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

if [ $(ps -ef | grep -v grep | grep saveDailyProdEachUserGroup.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 $BASEDIR/saveDailyProdEachUserGroup.py > /dev/null 2>&1 &
   echo "RUN $BASEDIR/saveDailyProdEachUserGroup.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

if [ $(ps -ef | grep -v grep | grep saveDailyProdProdEachUserGroupRewrite.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 $BASEDIR/saveDailyProdProdEachUserGroupRewrite.py > /dev/null 2>&1 &
   echo "RUN $BASEDIR/saveDailyProdProdEachUserGroupRewrite.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

if [ $(ps -ef | grep -v grep | grep saveDailyReportEachDueDateEachGroup.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 $BASEDIR/saveDailyReportEachDueDateEachGroup.py > /dev/null 2>&1 &
   echo "RUN $BASEDIR/saveDailyReportEachDueDateEachGroup.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

if [ $(ps -ef | grep -v grep | grep saveDPWorkingDay.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 $BASEDIR/saveDPWorkingDay.py > /dev/null 2>&1 &
   echo "RUN $BASEDIR/saveDPWorkingDay.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

# if [ $(ps -ef | grep -v grep | saveMasterData.py | wc -l) -lt 1 ]; then
#    /usr/local/bin/python3.6 $BASEDIR/saveMasterData.py > /dev/null 2>&1 &
#    echo "RUN $BASEDIR/saveMasterData.py"
# else
#    echo "Worldfone ScanJob service is running"
#    exit 0
# fi

# if [ $(ps -ef | grep -v grep | saveReminderLetter.py | wc -l) -lt 1 ]; then
#    /usr/local/bin/python3.6 $BASEDIR/saveReminderLetter.py > /dev/null 2>&1 &
#    echo "RUN $BASEDIR/saveReminderLetter.py"
# else
#    echo "Worldfone ScanJob service is running"
#    exit 0
# fi

if [ $(ps -ef | grep -v grep | grep savewriteOf.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 ${BASEDIR}/savewriteOf.py > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/savewriteOf.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

# if [ $(ps -ef | grep -v grep | grep thuhoixe_report.py | wc -l) -lt 1 ]; then
#    /usr/local/bin/python3.6 $BASEDIR/thuhoixe_report.py > /dev/null 2>&1 &
#    echo "RUN $BASEDIR/thuhoixe_report.py"
# else
#    echo "Worldfone ScanJob service is running"
#    exit 0
# fi

if [ $(ps -ef | grep -v grep | grep saveProdAllUser.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 $BASEDIR/saveProdAllUser.py > /dev/null 2>&1 &
   echo "RUN $BASEDIR/saveProdAllUser.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi


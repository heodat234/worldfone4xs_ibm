#!/bin/sh
BASEDIR=$(dirname "$0")

if [ $(ps -ef | grep -v grep | grep saveDailyAssignment.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 $BASEDIR/saveDailyAssignment.py > /dev/null 2>&1 &
   echo "RUN $BASEDIR/saveDailyAssignment.py"
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

if [ $(ps -ef | grep -v grep | grep saveOutsoucingCollectionTrend.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 $BASEDIR/saveOutsoucingCollectionTrend.py > /dev/null 2>&1 &
   echo "RUN $BASEDIR/saveOutsoucingCollectionTrend.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

if [ $(ps -ef | grep -v grep | grep saveMonthlyBadDebtProvince.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 ${BASEDIR}/saveMonthlyBadDebtProvince.py > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/saveMonthlyBadDebtProvince.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

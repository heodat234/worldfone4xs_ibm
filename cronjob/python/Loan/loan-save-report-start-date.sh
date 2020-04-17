#!/bin/bash

BASEDIR=$(dirname "$0")

if [ $(ps -ef | grep -v grep | grep saveMasterData.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 ${BASEDIR}/saveMasterData.py > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/saveMasterData.py"
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
   /usr/local/bin/python3.6 $BASEDIR/saveDailyPayment.py > /dev/null 2>&1 &
   echo "RUN $BASEDIR/saveDailyPayment.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

if [ $(ps -ef | grep -v grep | grep saveProdAllUser.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 $BASEDIR/saveProdAllUser.py > /dev/null 2>&1 &
   echo "RUN $BASEDIR/saveProdAllUser.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

if [ $(ps -ef | grep -v grep | grep saveLastPastYearArrearsOccurrenceTable.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 $BASEDIR/saveLastPastYearArrearsOccurrenceTable.py > /dev/null 2>&1 &
   echo "RUN $BASEDIR/saveLastPastYearArrearsOccurrenceTable.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi
if [ $(ps -ef | grep -v grep | grep listOfAllCustomer.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 $BASEDIR/listOfAllCustomer.py > /dev/null 2>&1 &
   echo "RUN $BASEDIR/listOfAllCustomer.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

if [ $(ps -ef | grep -v grep | grep saveDailyReportOfOSBalanceOfGroupABCDE.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 ${BASEDIR}/saveDailyReportOfOSBalanceOfGroupABCDE.py > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/saveDailyReportOfOSBalanceOfGroupABCDE.py"
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

if [ $(ps -ef | grep -v grep | grep saveTendencyDelinquent.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 $BASEDIR/saveTendencyDelinquent.py > /dev/null 2>&1 &
   echo "RUN $BASEDIR/saveTendencyDelinquent.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

if [ $(ps -ef | grep -v grep | grep saveDelinquentOccurenceTransition.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 $BASEDIR/saveDelinquentOccurenceTransition.py > /dev/null 2>&1 &
   echo "RUN $BASEDIR/saveDelinquentOccurenceTransition.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

if [ $(ps -ef | grep -v grep | grep saveFirstTimePaymentDelinquency.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 $BASEDIR/saveFirstTimePaymentDelinquency.py > /dev/null 2>&1 &
   echo "RUN $BASEDIR/saveFirstTimePaymentDelinquency.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

# if [ $(ps -ef | grep -v grep | grep saveCardLoanGroupReport.py | wc -l) -lt 1 ]; then
#    /usr/local/bin/python3.6 ${BASEDIR}/saveCardLoanGroupReport.py > /dev/null 2>&1 &
#    echo "RUN ${BASEDIR}/saveCardLoanGroupReport.py"
# else
#    echo "Worldfone ScanJob service is running"
#    exit 0
# fi

if [ $(ps -ef | grep -v grep | grep saveBlockCardReport.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 ${BASEDIR}/saveBlockCardReport.py > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/saveBlockCardReport.py"
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

if [ $(ps -ef | grep -v grep | grep saveMonthlyBadDebtProvince.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 ${BASEDIR}/saveMonthlyBadDebtProvince.py > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/saveMonthlyBadDebtProvince.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

if [ $(ps -ef | grep -v grep | grep saveMonthlyReportJapnanese.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 ${BASEDIR}/saveMonthlyReportJapnanese.py > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/saveMonthlyReportJapnanese.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

if [ $(ps -ef | grep -v grep | grep calDueDateValue.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 ${BASEDIR}/calDueDateValue.py > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/calDueDateValue.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi
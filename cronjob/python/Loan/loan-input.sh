#!/bin/bash

BASEDIR=$(dirname "$0")
if [ $(ps -ef | grep -v grep | grep importLNJC05F.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 ${BASEDIR}/importLNJC05F.py > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/importLNJC05F.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

if [ $(ps -ef | grep -v grep | grep importListOfAccount.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 ${BASEDIR}/importListOfAccount.py > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/importListOfAccount.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

if [ $(ps -ef | grep -v grep | grep importLN3206F.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 ${BASEDIR}/importLN3206F.py > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/importLN3206F.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

if [ $(ps -ef | grep -v grep | grep importReportInputPaymentOfCard.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 ${BASEDIR}/importReportInputPaymentOfCard.py > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/importReportInputPaymentOfCard.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

if [ $(ps -ef | grep -v grep | grep importSBV.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 ${BASEDIR}/importSBV.py > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/importSBV.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

if [ $(ps -ef | grep -v grep | grep importWoAllProd.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 ${BASEDIR}/importWoAllProd.py > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/importWoAllProd.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

if [ $(ps -ef | grep -v grep | grep importWoMonthly.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 ${BASEDIR}/importWoMonthly.py > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/importWoMonthly.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

if [ $(ps -ef | grep -v grep | grep importWoPayment.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 ${BASEDIR}/importWoPayment.py > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/importWoPayment.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

if [ $(ps -ef | grep -v grep | grep importReportReleaseSale.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 ${BASEDIR}/importReportReleaseSale.py > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/importReportReleaseSale.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

if [ $(ps -ef | grep -v grep | grep importSiteResult.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 ${BASEDIR}/importSiteResult.py > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/importSiteResult.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

if [ $(ps -ef | grep -v grep | grep importCollateralInfo.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 ${BASEDIR}/importCollateralInfo.py > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/importCollateralInfo.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

if [ $(ps -ef | grep -v grep | grep importListOfCusAssignedToPartner.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 ${BASEDIR}/importListOfCusAssignedToPartner.py > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/importListOfCusAssignedToPartner.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

if [ $(ps -ef | grep -v grep | grep importTrialBalanceReport.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 ${BASEDIR}/importTrialBalanceReport.py > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/importTrialBalanceReport.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

if [ $(ps -ef | grep -v grep | grep importThuHoiXe.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 ${BASEDIR}/importThuHoiXe.py > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/importThuHoiXe.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

if [ $(ps -ef | grep -v grep | grep importLawsuit.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 ${BASEDIR}/importLawsuit.py > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/importLawsuit.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi

if [ $(ps -ef | grep -v grep | grep importK20190812.0244.R18.20191030.py | wc -l) -lt 1 ]; then
   /usr/local/bin/python3.6 ${BASEDIR}/importK20190812.0244.R18.20191030.py > /dev/null 2>&1 &
   echo "RUN ${BASEDIR}/importK20190812.0244.R18.20191030.py"
else
   echo "Worldfone ScanJob service is running"
   exit 0
fi
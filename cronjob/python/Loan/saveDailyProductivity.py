#!/usr/bin/python3.6
# -*- coding: utf-8 -*-

import re
import ftplib
import calendar
import time
import sys
import os
import json
from pprint import pprint
from datetime import datetime
from datetime import date
from bson import ObjectId
from helper.ftp import Ftp
from helper.mongod import Mongodb
from helper.excel import Excel
from helper.jaccs import Config
from helper.common import Common

mongodb = Mongodb("worldfone4xs")
_mongodb = Mongodb("_worldfone4xs")
excel = Excel()
config = Config()
ftp = Ftp()
common = Common()
base_url = config.base_url()
log = open(base_url + "cronjob/python/Loan/log/saveDailyProductivity.txt","a")
now = datetime.now()
subUserType = 'LO'
collection = common.getSubUser(subUserType, 'Os_balance_group')

try:
    insertData = {}

    today = date.today()
    day = today.day
    month = today.month
    year = today.year
    weekday = today.weekday()

    # todayString = time.strftime("%d/%m/%Y")
    todayString = '11/10/2019'
    todayTimeStamp = int(time.mktime(time.strptime(str(todayString + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))

    lastDayOfMonth = calendar.monthrange(year, month)[1]

    starttime = int(time.mktime(time.strptime(str(todayString + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
    endtime = int(time.mktime(time.strptime(str(todayString + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))

    startMonth = int(time.mktime(time.strptime(str('01/' + str(month) + '/' + str(year) + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
    endMonth = int(time.mktime(time.strptime(str(str(lastDayOfMonth) + '/' + str(month) + '/' + str(year) + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))
    
    listDueDate = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_due_date'), WHERE={'due_date': {'$gte': startMonth, '$lte': endMonth}})
    # lastDayOfMonth = calendar.monthrange(year, month)[1]

    # # todayString = time.strftime("%d/%m/%Y")
    # todayString = '11/10/2019'
    # todayTimeStamp = int(time.mktime(time.strptime(str(todayString + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))

    # starttime = int(time.mktime(time.strptime(str(todayString + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
    # endtime = int(time.mktime(time.strptime(str(todayString + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))

    # startMonth = int(time.mktime(time.strptime(str('01/' + str(month) + '/' + str(year) + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
    # endMonth = int(time.mktime(time.strptime(str(str(lastDayOfMonth) + '/' + str(month) + '/' + str(year) + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))

    # holidayOfMonth = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_off_sys'), WHERE={'off_date': {'$gte': startMonth, '$lte': endMonth}})
    # listHoliday = map(lambda offDateRow: {offDateRow['off_date']}, holidayOfMonth)

    # dueDayOfMonth = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_due_date'), WHERE={'due_date': {'$gte': startMonth, '$lte': endMonth}})
    # listDueDate = {}
    # for dueDate in dueDayOfMonth:
    #     listDueDate[dueDate['debt_group']] = dueDate['due_date']

    # debtGroup = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Debt_group'))

    # listLNJCO5 = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'LNJC05'), WHERE={"due_date": {'$gte': starttime, '$lte': endtime}})

    # if todayTimeStamp in listHoliday:
    #     pprint("HOLIDAY")
    #     sys.exit()

    # if listLNJCO5 is not None:
    #     for group in debtGroup:
    #         insertData['debt_group_name'] = (group['type'].upper() + ' ' + group['group']) if group['type'] == 'card' else (group['group'])
    #         if listDueDate[str(group['_id'])] == todayTimeStamp:
    #             dueDateCurrentBalance = map(lambda row: row['current_balance'], listLNJCO5)
    #             insertData['start_os_bl'] = sum(list(dueDateCurrentBalance))
    #             insertData['start_no'] = len(listLNJCO5)
except Exception as e:
    log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
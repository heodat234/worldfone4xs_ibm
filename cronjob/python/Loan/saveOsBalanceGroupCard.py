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
from datetime import date, timedelta
from bson import ObjectId
from helper.ftp import Ftp
from helper.mongod import Mongodb
from helper.excel import Excel
from helper.jaccs import Config
from helper.common import Common
from helper.mongodbaggregate import Mongodbaggregate

mongodb = Mongodb("worldfone4xs")
_mongodb = Mongodb("_worldfone4xs")
excel = Excel()
config = Config()
ftp = Ftp()
common = Common()
mongodbaggregate = Mongodbaggregate("worldfone4xs")
base_url = config.base_url()
log = open(base_url + "cronjob/python/Loan/log/saveOsBalanceGroup.txt","a")
now = datetime.now()
subUserType = 'LO'
collection = common.getSubUser(subUserType, 'Os_balance_group_sibs')
try:
    insertData = []
    updateData = []
    listDebtGroup = []
    lnjc05ByGroup = {}

    # today = date.today()
    today = datetime.strptime('22/10/2019', "%d/%m/%Y").date()

    day = today.day
    month = today.month
    year = today.year
    weekday = today.weekday()
    lastDayOfMonth = calendar.monthrange(year, month)[1]

    todayString = today.strftime("%d/%m/%Y")
    todayTimeStamp = int(time.mktime(time.strptime(str(todayString + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))

    starttime = int(time.mktime(time.strptime(str(todayString + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
    endtime = int(time.mktime(time.strptime(str(todayString + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))

    startMonth = int(time.mktime(time.strptime(str('01/' + str(month) + '/' + str(year) + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
    endMonth = int(time.mktime(time.strptime(str(str(lastDayOfMonth) + '/' + str(month) + '/' + str(year) + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))

    holidayOfMonth = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_off_sys'))
    listHoliday = map(lambda offDateRow: {offDateRow['off_date']}, holidayOfMonth)

    if todayTimeStamp in listHoliday:
        sys.exit()

    checkYesterday = False
    days = 1
    while checkYesterday is False:
        yesterday = today - timedelta(days=days)
        yesterdayString = yesterday.strftime("%d/%m/%Y")
        yesterdayTimeStamp = int(time.mktime(time.strptime(str(yesterdayString + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
        if yesterdayTimeStamp not in listHoliday and (yesterday.weekday() != 5) and yesterday.weekday() != 6:
            checkYesterday = True
        days += 1

    dueDayOfMonth = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_due_date'), WHERE={'for_month': str(month)})
    listDueDate = {}
    listDueDateAddOne = {}
    for dueDate in dueDayOfMonth:
        listDueDate[dueDate['debt_group']] = dueDate['due_date']
        listDueDateAddOne[dueDate['debt_group']] = dueDate['due_date_add_1']

    debtGroup = _mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Jsondata'), WHERE={'tags': ["debt", "duedate"]})
    debtDueDate = _mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Jsondata'), WHERE={'tags': ["debt", "group"]})
    for group in debtGroup['data']:
        for dueDate in debtDueDate['data']:
            listDebtGroup.append(dueDate['text'] + group['text'])

    mongodbaggregate.set_collection(collection=common.getSubUser(subUserType, 'LNJC05'))
    aggregate_pipeline = [
        {
            '$match'                        : {
                'due_date'                  : {
                    '$gte'                  : starttime,
                    '$lte'                  : endtime
                }
            }
        },
        {
            '$group'                        : {
                '_id'                       : '$group_id',
                'dataReport'                : {
                    '$push'                 : {
                        'current_balance'   : '$current_balance',
                        'account_number'    : '$account_number'
                    }
                }
            }
        }
    ]
    mongodbaggregate.add_aggregate(aggregate_element=aggregate_pipeline)
    listLNJCO5 = mongodbaggregate.aggregate()
    for row in listLNJCO5:
        lnjc05ByGroup[row['_id']] = {}
        lnjc05ByGroup[row['_id']]['daily_os_bl'] = 0
        lnjc05ByGroup[row['_id']]['daily_no'] = 0
        for data in row['dataReport']:
            lnjc05ByGroup[row['_id']]['daily_os_bl'] += data['current_balance']
            lnjc05ByGroup[row['_id']]['daily_no'] += 1

    if listLNJCO5 is not None:
        for group in listDebtGroup:
            temp = {}
            tempYesterday = {}
            temp['debt_group_name'] = group
            temp['created_at'] = todayTimeStamp
            temp['created_by'] = 'system'

            if group in lnjc05ByGroup.keys():
                temp['daily_os_bl'] = lnjc05ByGroup[group]['daily_os_bl']
                temp['daily_no'] = lnjc05ByGroup[group]['daily_no']
            else:
                temp['daily_os_bl'] = 0
                temp['daily_no'] = 0
            
            if listDueDate[group[1:3]] == todayTimeStamp:
                temp['start_os_bl'] = temp['daily_os_bl']
                temp['start_no'] = temp['daily_no']
            else:
                temp['start_os_bl'] = start_os_bl
                temp['start_no'] = start_no

            yesterdayDataReport = mongodb.getOne(MONGO_COLLECTION=collection, WHERE={'created_at': {'$gte': yesterdayTimeStamp, '$lte': (yesterdayTimeStamp + 86399)}, 'debt_group_name': group})
            if yesterdayDataReport is not None:
                tempYesterday['end_date_os_bl'] = yesterdayDataReport['daily_os_bl'] - temp['daily_os_bl']
                tempYesterday['end_date_no'] = yesterdayDataReport['daily_no'] - temp['daily_no']
                start_os_bl = tempYesterday['start_os_bl']
                start_no = tempYesterday['start_no']
            else: 
                tempYesterday['end_date_os_bl'] = 0
                tempYesterday['end_date_no'] = 0
                start_os_bl = 0
                start_no = 0

            target_os_bl = 1
            target_no = 1
            # groupName = 'Main Product/' + 'Team ' + group[0:1] + '/Group ' + group
            groupInfo = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Group'), WHERE={'name': {'$regex' : ".*" + group + ".*"}})
            if groupInfo is not None:
                diallistInfo = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Diallist'), WHERE={'group_id': groupInfo['_id']})
                if diallistInfo is not None:
                    target_os_bl = diallistInfo['target']
                    target_no = diallistInfo['target']

            temp['target_os_bl'] = temp['start_os_bl'] * target_os_bl
            temp['target_no'] = temp['start_no'] * target_no
            
            temp['accumulated_os_bl'] = temp['start_os_bl'] - temp['daily_os_bl']
            temp['accumulated_no'] = temp['start_no'] - temp['daily_no']

            if temp['target_os_bl'] != 0:
                temp['ratio_target_os_bl'] = (temp['accumulated_os_bl'] / temp['target_os_bl']) * 100
            else:
                temp['ratio_target_os_bl'] = 0

            if temp['target_no'] != 0:
                if group[0:1] == 'A':
                    temp['ratio_target_no'] = temp['daily_no'] - (temp['start_os_bl'] - temp['target_no'])
                else:
                    temp['ratio_target_no'] = (temp['accumulated_no'] / temp['target_no']) * 100
            else:
                temp['ratio_target_no'] = 0

            if temp['start_os_bl'] != 0:
                temp['ratio_start_os_bl'] = (temp['accumulated_os_bl'] / temp['start_os_bl']) * 100
            else:
                temp['ratio_start_os_bl'] = 0

            if temp['start_no'] != 0:
                temp['ratio_start_no'] = (temp['accumulated_no'] / temp['start_no']) * 100
            else:
                temp['ratio_start_no'] = 0

            mongodb.insert(MONGO_COLLECTION=collection, insert_data=temp)
            mongodb.update(MONGO_COLLECTION=collection, WHERE={'created_at': {'$gte': yesterdayTimeStamp, '$lte': (yesterdayTimeStamp + 86399)}, 'debt_group_name': group}, VALUE=tempYesterday)
        # pprint(insertData)
except Exception as e:
    log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
        
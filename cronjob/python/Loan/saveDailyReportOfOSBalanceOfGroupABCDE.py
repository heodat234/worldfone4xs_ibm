#!/usr/bin/python3.6
# -*- coding: utf-8 -*-
# LE THANH HUNG 23/02/2020
import sys
import os
import time
import ntpath
import json
import calendar
import traceback
from helper.mongod import Mongodb
from datetime import datetime
from datetime import date
from pprint import pprint
from bson import ObjectId
from helper.common import Common
from helper.jaccs import Config

common      = Common()
base_url    = common.base_url()
wff_env     = common.wff_env(base_url)
mongodb     = Mongodb(MONGODB="worldfone4xs", WFF_ENV=wff_env)
_mongodb    = Mongodb(MONGODB="_worldfone4xs", WFF_ENV=wff_env)
now         = datetime.now()
subUserType = 'LO'
collection         = common.getSubUser(subUserType, 'Os_balance_group_report')
lnjc05_collection  = common.getSubUser(subUserType, 'LNJC05')
target_collection  = common.getSubUser(subUserType, 'Target_of_report')
report_due_date_collection      = common.getSubUser(subUserType, 'Report_due_date')

log         = open(base_url + "cronjob/python/Loan/log/Os_balance_group_log.txt","a")


try:
    insertData = []
    updateData = []
    listDebtGroup = []
    lnjc05ByGroup = {}

    # today = date.today()
    today = datetime.strptime('13/02/2020', "%d/%m/%Y").date()

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

    targetInfo = mongodb.get(MONGO_COLLECTION=target_collection)
    for targetGroup in targetInfo:
      # print(targetGroup['show_B_plus_duedate_type'])
      if targetGroup['show_B_plus_duedate_type'] == False:
        duedate_type = targetGroup['duedate_type']
        print(duedate_type)
        dueDayOfMonth = mongodb.getOne(MONGO_COLLECTION=report_due_date_collection, WHERE={'for_month': str(month), 'debt_group': duedate_type[1:3]})
        print(dueDayOfMonth)
    # aggregate_pipeline = [
    #     {
    #         '$match'                        : {
    #             'due_date'                  : {
    #                 '$gte'                  : starttime,
    #                 '$lte'                  : endtime
    #             }
    #         }
    #     },
    #     {
    #         '$group'                        : {
    #             '_id'                       : '$group_id',
    #             'dataReport'                : {
    #                 '$push'                 : {
    #                     'current_balance'   : '$current_balance',
    #                     'account_number'    : '$account_number'
    #                 }
    #             }
    #         }
    #     }
    # ]
    # mongodbaggregate.add_aggregate(aggregate_element=aggregate_pipeline)
    # listLNJCO5 = mongodbaggregate.aggregate()
    # for row in listLNJCO5:
    #     lnjc05ByGroup[row['_id']] = {}
    #     lnjc05ByGroup[row['_id']]['daily_os_bl'] = 0
    #     lnjc05ByGroup[row['_id']]['daily_no'] = 0
    #     for data in row['dataReport']:
    #         lnjc05ByGroup[row['_id']]['daily_os_bl'] += data['current_balance']
    #         lnjc05ByGroup[row['_id']]['daily_no'] += 1

    # if listLNJCO5 is not None:
    #     for group in listDebtGroup:
    #         temp = {}
    #         tempYesterday = {}
    #         temp['debt_group_name'] = group
    #         temp['created_at'] = todayTimeStamp
    #         temp['created_by'] = 'system'

    #         if group in lnjc05ByGroup.keys():
    #             temp['daily_os_bl'] = lnjc05ByGroup[group]['daily_os_bl']
    #             temp['daily_no'] = lnjc05ByGroup[group]['daily_no']
    #         else:
    #             temp['daily_os_bl'] = 0
    #             temp['daily_no'] = 0

    #         if listDueDate[group[1:3]] == todayTimeStamp:
    #             temp['start_os_bl'] = temp['daily_os_bl']
    #             temp['start_no'] = temp['daily_no']
    #         else:
    #             temp['start_os_bl'] = start_os_bl
    #             temp['start_no'] = start_no

    #         yesterdayDataReport = mongodb.getOne(MONGO_COLLECTION=collection, WHERE={'created_at': {'$gte': yesterdayTimeStamp, '$lte': (yesterdayTimeStamp + 86399)}, 'debt_group_name': group})
    #         if yesterdayDataReport is not None:
    #             tempYesterday['end_date_os_bl'] = yesterdayDataReport['daily_os_bl'] - temp['daily_os_bl']
    #             tempYesterday['end_date_no'] = yesterdayDataReport['daily_no'] - temp['daily_no']
    #             start_os_bl = tempYesterday['start_os_bl']
    #             start_no = tempYesterday['start_no']
    #         else:
    #             tempYesterday['end_date_os_bl'] = 0
    #             tempYesterday['end_date_no'] = 0
    #             start_os_bl = 0
    #             start_no = 0

    #         target_os_bl = 1
    #         target_no = 1
    #         groupName = 'Main Product/' + 'Team ' + group[0:1] + '/Group ' + group
    #         groupInfo = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Group'), WHERE={'name': groupName})
    #         if groupInfo is not None:
    #             diallistInfo = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Diallist'), WHERE={'group_id': groupInfo['_id']})
    #             if diallistInfo is not None:
    #                 target_os_bl = diallistInfo['target']
    #                 target_no = diallistInfo['target']

    #         temp['target_os_bl'] = temp['start_os_bl'] * target_os_bl
    #         temp['target_no'] = temp['start_no'] * target_no

    #         temp['accumulated_os_bl'] = temp['start_os_bl'] - temp['daily_os_bl']
    #         temp['accumulated_no'] = temp['start_no'] - temp['daily_no']

    #         if temp['target_os_bl'] != 0:
    #             temp['ratio_target_os_bl'] = (temp['accumulated_os_bl'] / temp['target_os_bl']) * 100
    #         else:
    #             temp['ratio_target_os_bl'] = 0

    #         if temp['target_no'] != 0:
    #             if group[0:1] == 'A':
    #                 temp['ratio_target_no'] = temp['daily_no'] - (temp['start_os_bl'] - temp['target_no'])
    #             else:
    #                 temp['ratio_target_no'] = (temp['accumulated_no'] / temp['target_no']) * 100
    #         else:
    #             temp['ratio_target_no'] = 0

    #         if temp['start_os_bl'] != 0:
    #             temp['ratio_start_os_bl'] = (temp['accumulated_os_bl'] / temp['start_os_bl']) * 100
    #         else:
    #             temp['ratio_start_os_bl'] = 0

    #         if temp['start_no'] != 0:
    #             temp['ratio_start_no'] = (temp['accumulated_no'] / temp['start_no']) * 100
    #         else:
    #             temp['ratio_start_no'] = 0

    #         mongodb.insert(MONGO_COLLECTION=collection, insert_data=temp)
    #         mongodb.update(MONGO_COLLECTION=collection, WHERE={'created_at': {'$gte': yesterdayTimeStamp, '$lte': (yesterdayTimeStamp + 86399)}, 'debt_group_name': group}, VALUE=tempYesterday)
    #     # pprint(insertData)

    print('DONE')
except Exception as e:
    print(traceback.format_exc())
    log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')

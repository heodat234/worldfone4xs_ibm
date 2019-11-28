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
log = open(base_url + "cronjob/python/Loan/log/calDueDateValue.txt","a")
now = datetime.now()
subUserType = 'LO'
collection = common.getSubUser(subUserType, 'Due_date_one_value')
try:
    insertData = []
    updateData = []
    listDebtGroup = []
    
    # today = date.today()
    today = datetime.strptime('13/11/2019', "%d/%m/%Y").date()

    day = today.day
    month = today.month
    year = today.year
    weekday = today.weekday()
    lastDayOfMonth = calendar.monthrange(year, month)[1]

    todayString = today.strftime("%d/%m/%Y")
    todayTimeStamp = int(time.mktime(time.strptime(str(todayString + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))

    startMonth = int(time.mktime(time.strptime(str('01/' + str(month) + '/' + str(year) + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
    endMonth = int(time.mktime(time.strptime(str(str(lastDayOfMonth) + '/' + str(month) + '/' + str(year) + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))

    holidayOfMonth = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_off_sys'))
    listHoliday = map(lambda offDateRow: {offDateRow['off_date']}, holidayOfMonth)

    # if todayTimeStamp in listHoliday or (weekday == 5) or weekday == 6:
    #     sys.exit()

    todayString = today.strftime("%d/%m/%Y")
    starttime = int(time.mktime(time.strptime(str(todayString + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
    endtime = int(time.mktime(time.strptime(str(todayString + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))

    collectionName = 'LO_input_report_' + str(year) + str(month)

    if day == 1:
        mongodb.create_db(collectionName)

    mongodbReport = Mongodb(collectionName)

    lnjc05InfoFull = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'LNJC05'))
    lnjc05ColName = 'LNJC05_' + str(year) + str(month) + str(day)
    mongodbReport.create_col(COL_NAME=lnjc05ColName)
    mongodbReport.remove_document(MONGO_COLLECTION=lnjc05ColName)
    mongodbReport.batch_insert(MONGO_COLLECTION=lnjc05ColName, insert_data=lnjc05InfoFull)

    listOfAccFull = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'List_of_account_in_collection'))
    listOfAccColName = 'List_of_account_in_collection_' + str(year) + str(month) + str(day)
    mongodbReport.create_col(COL_NAME=listOfAccColName)
    mongodbReport.remove_document(MONGO_COLLECTION=listOfAccColName)
    mongodbReport.batch_insert(MONGO_COLLECTION=listOfAccColName, insert_data=listOfAccFull)

    mainProduct = {}
    mainProductRaw = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Product'))
    for prod in mainProductRaw:
        mainProduct[prod['code']] = prod['name']

    debtGroup = _mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Jsondata'), WHERE={'tags': ['debt', 'group']})
    dueDate = _mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Jsondata'), WHERE={'tags': ['debt', 'duedate']})

    for group in debtGroup['data']:
        for duedate in dueDate['data']:
            listDebtGroup.append(group['text'] + duedate['text'])

    listDebtGroup = sorted(listDebtGroup)

    listGroupProductRaw = _mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Jsondata'), WHERE={'tags': ['group', 'debt', 'product']})
    listGroupProduct = listGroupProductRaw['data']

    for debtGroupCell in list(listDebtGroup):
        if debtGroupCell[0:1] is not 'F':
            dueDayOfMonth = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_due_date'), WHERE={'for_month': str(month), 'debt_group': debtGroupCell[1:3]})
            if todayTimeStamp == dueDayOfMonth['due_date_add_1']:
                for groupProduct in list(listGroupProduct):
                    groupInfoByDueDate = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Group'), WHERE={'debt_groups': debtGroupCell, 'name': {"$regex": groupProduct['text'] + '.*'}})
                    for groupCell in list(groupInfoByDueDate):
                        temp = {
                            'due_date'              : todayTimeStamp - 86400,
                            'due_date_one'          : todayTimeStamp,
                            'debt_group'            : debtGroupCell[0:1],
                            'due_date_code'         : debtGroupCell[1:3],
                            'team_id'               : str(groupCell['_id']),
                            'debt_acc_no'           : 0,
                            'current_balance_total' : 0,
                        }
                        
                        for key, value in mainProduct.items():
                            temp['debt_acc_' + key] = 0
                            temp['current_balance_' + key] = 0

                        if groupProduct['value'] == 'SIBS':
                            lead = ['JIVF00' + groupCell['lead']] if 'lead' in groupCell.keys() else []
                            member = ('JIVF00' + s for s in groupCell['members']) if 'members' in groupCell.keys() else []
                            officerIdRaw = list(lead) + list(member)
                            officerId = list(dict.fromkeys(officerIdRaw))

                            lnjc05Info = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'LNJC05'), WHERE={'group_id': debtGroupCell, 'officer_id': {'$in': officerId}})
                            for lnjc05 in lnjc05Info:
                                zaccfInfo = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'ZACCF'), WHERE={'account_number': lnjc05['account_number']})
                                temp['debt_acc_no'] += 1
                                temp['current_balance_total'] += float(lnjc05['current_balance'])
                                if zaccfInfo is not None:
                                    temp['debt_acc_' + zaccfInfo['PRODGRP_ID']] += 1
                                    temp['current_balance_' + zaccfInfo['PRODGRP_ID']] += float(lnjc05['current_balance'])
                        
                        temp['created_at'] = time.time()
                        temp['created_by'] = 'system'
                        temp['for_month'] = str(month)
                        mongodb.insert(MONGO_COLLECTION=common.getSubUser(subUserType, 'Due_date_next_date'), insert_data=temp)
    
    pprint("DONE")
except Exception as e:
    log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
    pprint(str(e))
        
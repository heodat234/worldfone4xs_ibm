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
log = open(base_url + "cronjob/python/Loan/log/saveDailyProdProdEachUserGroup.txt","a")
now = datetime.now()
subUserType = 'LO'
collection = common.getSubUser(subUserType, 'Daily_prod_prod_each_user_group')
try:
    insertData = []
    updateData = []
    listDebtGroup = []
    
    # today = date.today()
    today = datetime.strptime('12/10/2019', "%d/%m/%Y").date()

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

    for groupProduct in list(listGroupProduct):
        if groupProduct == 'SIBS':
            for debtGroupCell in list(listDebtGroup):
                dueDayOfMonth = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_due_date'), WHERE={'for_month': str(month), 'debt_group': debtGroupCell[1:3]})
                groupInfoByDueDate = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Group'), WHERE={'debt_groups': debtGroupCell, 'name': {"$regex": groupProduct['text'] + '.*'}})
                for groupCell in list(groupInfoByDueDate):
                    temp = {}
                    if todayTimeStamp < dueDayOfMonth['due_date_add_1']:
                        dueDayLastMonth = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_due_date'), WHERE={'for_month': str(month - 1), 'debt_group': debtGroupCell[1:3]})
                        temp['due_date'] = dueDayLastMonth['due_date'] if dueDayLastMonth is not None else ''
                        pprint(dueDayLastMonth)
                    else:
                        temp['due_date'] = dueDayOfMonth['due_date']
                    temp['debt_group'] = debtGroupCell[0:1]
                    temp['due_date_code'] = debtGroupCell[1:3]
                    temp['product'] = groupProduct['text']
                    temp['team'] = groupCell['name']
                    temp['team_id'] = str(groupCell['_id'])
                    temp['inci'] = 0
                    temp['inci_amt'] = 0
                    temp['col'] = 0
                    temp['col_amt'] = 0
                    temp['today_rem'] = 0
                    temp['today_rem_amt'] = 0

                    for key, value in mainProduct.items():
                        temp['inci_' + key] = 0
                        temp['inci_amt_' + key] = 0
                        temp['col_' + key] = 0
                        temp['col_amt_' + key] = 0
                        temp['today_rem_' + key] = 0
                        temp['today_rem_amt_' + key] = 0

                    lead = ['JIVF00' + groupCell['lead']] if 'lead' in groupCell.keys() else []
                    member = ('JIVF00' + s for s in groupCell['members']) if 'members' in groupCell.keys() else []
                    officerIdRaw = list(lead) + list(member)
                    officerId = list(dict.fromkeys(officerIdRaw))

                    lnjc05Info = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'LNJC05'), WHERE={'createdAt': {'$gte': starttime, '$lte': endtime}, 'group_id': debtGroupCell, 'officer_id': {'$in': officerId}})
                    lnjc05InfoYesterday = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'LNJC05'), WHERE={'createdAt': {'$gte': (starttime - 86400), '$lte': (endtime - 86400)}, 'group_id': debtGroupCell, 'officer_id': officerId})
                    lnjc05InfoYesterday = dict(lnjc05InfoYesterday)               
                    for lnjc05 in lnjc05Info:
                        zaccfInfo = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'ZACCF'), WHERE={'account_number': lnjc05['account_number']})
                        if zaccfInfo is not None:
                            temp['today_rem'] += 1
                            temp['today_rem_amt'] += float(lnjc05['current_balance'])
                            temp['today_rem_' + zaccfInfo['PRODGRP_ID']] += 1
                            temp['today_rem_amt_' + zaccfInfo['PRODGRP_ID']] += float(lnjc05['current_balance'])
                            
                    if todayTimeStamp == dueDayOfMonth['due_date_add_1']:
                        temp['inci'] = temp['today_rem']
                        temp['inci_amt'] = temp['today_rem_amt']
                        temp['col'] = temp['inci'] - temp['today_rem']
                        temp['col_amt'] = temp['inci_amt'] - temp['today_rem_amt']
                        for key, value in mainProduct.items():
                            temp['inci_' + key] = temp['today_rem_' + key]
                            temp['inci_amt_' + key] = temp['today_rem_amt_' + key]
                            temp['col_' + key] = temp['inci_' + key] - temp['today_rem_' + key]
                            temp['col_amt_' + key] = temp['inci_amt_' + key] - temp['today_rem_amt_' + key]
                    else:
                        temp['inci'] = lnjc05InfoYesterday['inci'] if 'inci' in lnjc05InfoYesterday.keys() else 0
                        temp['inci_amt'] = lnjc05InfoYesterday['inci_amt'] if 'inci_amt' in lnjc05InfoYesterday.keys() else 0
                        temp['col'] = temp['inci'] - temp['today_rem']
                        temp['col_amt'] = temp['inci_amt'] - temp['today_rem_amt']
                        for key, value in mainProduct.items():
                            temp['inci_' + key] = lnjc05InfoYesterday['inci_' + key] if ('inci_' + key) in lnjc05InfoYesterday.keys() else 0
                            temp['inci_amt_' + key] = lnjc05InfoYesterday['inci_amt_' + key] if ('inci_amt_' + key) in lnjc05InfoYesterday.keys() else 0
                            temp['col_' + key] = temp['inci_' + key] - temp['today_rem_' + key]
                            temp['col_amt_' + key] = temp['inci_amt_' + key] - temp['today_rem_amt_' + key]
                    
                    temp['flow_rate'] = temp['today_rem'] / temp['inci'] if temp['inci'] != 0 else 0
                    temp['col_rate'] = temp['today_rem'] / temp['col'] if temp['col'] != 0 else 0
                    temp['flow_rate_amt'] = temp['today_rem_amt'] / temp['inci_amt'] if temp['inci_amt'] != 0 else 0
                    temp['col_rate_amt'] = temp['today_rem_amt'] / temp['col_amt'] if temp['col_amt'] != 0 else 0
                    temp['created_at'] = todayTimeStamp
                    temp['created_by'] = 'system'
                    # mongodb.insert(MONGO_COLLECTION=collection, insert_data=temp)
                    # log.write(json.dumps(temp))
                    # log.write('\n')
                    # log.write('\n')
        else:
            listAcc = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Account'), WHERE={'updated_at': {'$gte': starttime, '$lte': endtime}})
            for acc in listAcc:
                pprint(acc)

    # tempWO = {
    #     'debt_group'        : 'F',
    #     'due_date_code'     : '',
    #     'product'           : '',
    #     'team'              : '',
    #     'team_id'           : ''
    # }
    
    # if todayTimeStamp < dueDayOfMonth['due_date_add_1']:
    #     dueDayLastMonth = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_due_date'), WHERE={'for_month': str(month - 1), 'debt_group': debtGroupCell[1:3]})
    #     tempWO['due_date'] = dueDayLastMonth['due_date'] if dueDayLastMonth is not None else ''
    # else:
    #     tempWO['due_date'] = dueDayOfMonth['due_date']

    # woMonthlyInfo = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'WO_monthly'), WHERE={'created_at': {'$gte': starttime, '$lte': endtime}})
    # temp['inci'] = len(list(woMonthlyInfo))


    # listAccount = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Account'), WHERE={'overdue': todayString})
    # for account in listAccount:
    #     temp = {}
    #     groupInfo = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Group_card'), WHERE={'account_no': account['account_number']})
        # pprint(dict(groupInfo)
except Exception as e:
    log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
    pprint(str(e))
        
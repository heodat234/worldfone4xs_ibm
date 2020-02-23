#!/usr/bin/python3.6
# -*- coding: utf-8 -*-

import re
import ftplib
import calendar
import time
import sys
import os
import json
import traceback
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
from math import ceil

excel = Excel()
config = Config()
ftp = Ftp()
common = Common()
base_url = common.base_url()
wff_env = common.wff_env(base_url)
mongodb = Mongodb(MONGODB="worldfone4xs", WFF_ENV=wff_env)
_mongodb = Mongodb(MONGODB="_worldfone4xs", WFF_ENV=wff_env)
log = open(base_url + "cronjob/python/Loan/log/importSBV.txt","a")
now = datetime.now()
subUserType = 'LO'
collection = common.getSubUser(subUserType, 'First_time_payment_delinqunecy')

try:
    # today = date.today()
    today = datetime.strptime('14/01/2020', "%d/%m/%Y").date()
    yesterday = today - timedelta(days=1)
    day = today.day
    month = today.month
    year = today.year
    weekday = today.weekday()
    lastDayOfMonth = calendar.monthrange(year, month)[1]
    insertData = []
    todayString = today.strftime("%d/%m/%Y")
    todayTimeStamp = int(time.mktime(time.strptime(str(todayString + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
    startMonth = int(time.mktime(time.strptime(str('01/' + str(month) + '/' + str(year) + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
    startMonthStarttime = startMonth
    startMonthEndtime = int(time.mktime(time.strptime(str('01/' + str(month) + '/' + str(year) + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))
    
    first = today.replace(day=1)
    lastMonthLastDate = first - timedelta(days=1)
    lastMonthMonth = lastMonthLastDate.month
    lastMonthYear = lastMonthLastDate.year
    lastMonthStarttime = int(time.mktime(time.strptime(str('01/' + str(lastMonthMonth) + '/' + str(lastMonthYear) + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
    lastMonthEndtime = int(time.mktime(time.strptime(str(str(lastMonthLastDate.day) + '/' + str(lastMonthMonth) + '/' + str(lastMonthYear) + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))

    starttime = int(time.mktime(time.strptime(str(todayString + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
    endtime = int(time.mktime(time.strptime(str(todayString + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))

    products = list(_mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Jsondata'), WHERE={'tags': ['product', 'group']}, SORT=[("code", 1)]))

    dueDates = list(mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_due_date'), WHERE={'for_month': str(month), 'for_year': str(year)}))
    reportDate = {}
    checkReportDate = []
    for dueDate in dueDates:
        reportDate[str(dueDate['due_date'])] = dueDate
        reportDate[str(dueDate['due_date'] + 432000)] = {'createdAt': datetime.now(), 'due_date': dueDate['due_date'] + 432000, 'for_month': dueDate['for_month'], 'for_year': dueDate['for_year'], 'debt_group': dueDate['debt_group'], 'createdBy': 'system', 'due_date_add_1': dueDate['due_date'] + 518400, 'is_due_date': False}
        checkReportDate.append(dueDate['due_date_add_1'])
        checkReportDate.append(dueDate['due_date'] + 432000)

    if todayTimeStamp not in checkReportDate:
        sys.exit()
    
    monthInYear = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]

    rowsValue = [{'name': 'Pay day', 'value': 'payday'}, {'value': 'first_payment', 'name': 'First payment'}, {'name': 'Remaining after 5 days', 'value': 'remaining_after_5_days'}, {'name': 'Date', 'value': 'date'}, {'name': 'Rate', 'value': 'rate'}]
    
    for product in products[0]['data']:
        if product['group_code'] != '300':
            rows = []
            for rowValue in rowsValue:
                if rowValue['value'] == 'payday':
                    temp = {
                        'for_month'         : str(month),
                        'for_year'          : str(year),
                        'prod_group_code'   : product['group_code'],
                        'prod_group_name'   : product['group_name'],
                        'month'             : datetime.fromtimestamp(todayTimeStamp).strftime('%b-%y'),
                        'number'            : 'Number',
                        'cal_value'         : rowValue['value'],
                        'cal_name'          : rowValue['name'],
                    }
                else:
                    temp = {
                        'for_month'         : str(month),
                        'for_year'          : str(year),
                        'prod_group_code'   : product['group_code'],
                        'prod_group_name'   : product['group_name'],
                        'month'             : '',
                        'number'            : '',
                        'cal_value'         : rowValue['value'],
                        'cal_name'          : rowValue['name'],
                    }
                pprint(product)
                zaccf_aggregate = [{
                    '$match'            : {
                        'F_PDT'         : str(format(day, '02d')) + str(format(month, '02d')) + str(year),
                        'PRODGRP_ID'    : product['product_code'].split(",")
                    }
                }, {
                    '$group'            : {
                        '_id'           : '$INT_RATE',
                        'account_number': {
                            '$push'     : '$account_number'
                        }
                    }
                }]
                zaccf_info = list(mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'ZACCF_14012020'), aggregate_pipeline=zaccf_aggregate))
                for zaccf in zaccf_info:
                    if rowValue['value'] == 'payday':
                        temp['a' + reportDate[todayTimeStamp]['debt_group'] + '_' + zaccf['_id']] = reportDate[todayTimeStamp]['due_date'].strftime("%d/%m")
                    else:
                        if reportDate[todayTimeStamp]['is_due_date'] == None:
                            temp['a' + reportDate[todayTimeStamp]['debt_group'] + '_' + zaccf['_id']] = len(zaccf[account_number])
                        else:
                            countAcc = 0
                            for acc in zaccf['account_number']:
                                lnjc05 = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'LNJC05_14012020'), WHERE={'account_number': acc, 'installment_type': '1'})
                                if lnjc05 != None:
                                    countAcc += 1
                            temp['a' + reportDate[todayTimeStamp]['debt_group'] + '_' + zaccf['_id']] = countAcc

                mongodb.update(MONGO_COLLECTION=collection, WHERE={'for_month': str(month), 'for_year': str(year), 'cal_value': rowValue['value']}, VALUE=temp)

except Exception as e:
    # log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
    print(traceback.format_exc())
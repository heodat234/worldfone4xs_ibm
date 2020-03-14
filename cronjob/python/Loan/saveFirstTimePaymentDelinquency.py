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
lnjc05_collection = 'LNJC05_18012020'
zaccf_collection = 'ZACCF_18012020'

try:
    # today = date.today()
    today = datetime.strptime('18/01/2020', "%d/%m/%Y").date()
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

    dueDates = list(mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_due_date'), WHERE={'for_month': {'$in': [str(lastMonthMonth), str(month)]}, 'for_year': {'$in': [str(year), str(lastMonthYear)]}}))
    
    if day == 1:
        report_month = lastMonthMonth
        report_year = lastMonthYear
    else:
        report_month = month
        report_year = year

    reportDate = {}
    checkReportDate = []
    for dueDate in dueDates:
        reportDate[str(dueDate['due_date'])] = dueDate
        reportDate[str(dueDate['due_date'] + 432000)] = {'createdAt': datetime.now(), 'due_date': dueDate['due_date'] + 432000, 'for_month': dueDate['for_month'], 'for_year': dueDate['for_year'], 'debt_group': dueDate['debt_group'], 'createdBy': 'system', 'due_date_add_1': dueDate['due_date'] + 518400, 'is_due_date': False}
        checkReportDate.append(dueDate['due_date'])
        checkReportDate.append(dueDate['due_date'] + 432000)

    if todayTimeStamp not in checkReportDate:
        sys.exit(checkReportDate)
    
    monthInYear = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]

    rowsValue = [{'name': 'Pay day', 'value': 'payday'}, {'value': 'first_payment', 'name': 'First payment'}, {'name': 'Remaining after 5 days', 'value': 'remaining_after_5_days'}, {'name': 'Date', 'value': 'date'}, {'name': 'Rate', 'value': 'rate'}]
    
    for product in products[0]['data']:
        if product['group_code'] != '300':
            rows = []
            total_firstPayment = 0
            total_remaining_after_five_days = 0
            listCoumn = {
                'for_year'          : reportDate[str(todayTimeStamp)]['for_year'],
                'prod_group_code'   : product['group_code'],
            }
            mongodb.update(MONGO_COLLECTION=common.getSubUser(subUserType, 'First_time_payment_delinqunecy_columns'), WHERE={'for_year': str(reportDate[str(todayTimeStamp)]['for_year']), 'prod_group_code': product['group_code']}, VALUE=listCoumn)
            temp_column = []
            for rowValue in rowsValue:
                if rowValue['value'] == 'payday':
                    temp = {
                        'for_month'         : str(reportDate[str(todayTimeStamp)]['for_month']),
                        'for_year'          : str(reportDate[str(todayTimeStamp)]['for_year']),
                        'prod_group_code'   : product['group_code'],
                        'prod_group_name'   : product['group_name'],
                        'month'             : datetime.fromtimestamp(todayTimeStamp).strftime('%b-%y'),
                        'number'            : 'Number',
                        'cal_value'         : rowValue['value'],
                        'cal_name'          : rowValue['name'],
                    }
                else:
                    temp = {
                        'for_month'         : str(reportDate[str(todayTimeStamp)]['for_month']),
                        'for_year'          : str(reportDate[str(todayTimeStamp)]['for_year']),
                        'prod_group_code'   : product['group_code'],
                        'prod_group_name'   : product['group_name'],
                        'month'             : '',
                        'number'            : '',
                        'cal_value'         : rowValue['value'],
                        'cal_name'          : rowValue['name'],
                    }
                if rowValue['value'] == 'payday':
                    # if 'is_due_date' in 
                    temp_total = {
                        'for_month'         : str(reportDate[str(todayTimeStamp)]['for_month']),
                        'for_year'          : str(reportDate[str(todayTimeStamp)]['for_year']),
                        'prod_group_code'   : product['group_code'],
                        'prod_group_name'   : product['group_name'],
                        'month'             : datetime.fromtimestamp(todayTimeStamp).strftime('%b-%y'),
                        'number'            : 'Number',
                        'cal_value'         : rowValue['value'],
                        'cal_name'          : rowValue['name'],
                        # 'a' + reportDate[str(todayTimeStamp)]['debt_group'] + '_total': datetime.fromtimestamp(reportDate[str(todayTimeStamp)]['due_date']).strftime("%d/%m") 
                    }
                else:
                    temp_total = {
                        'for_month'         : str(reportDate[str(todayTimeStamp)]['for_month']),
                        'for_year'          : str(reportDate[str(todayTimeStamp)]['for_year']),
                        'prod_group_code'   : product['group_code'],
                        'prod_group_name'   : product['group_name'],
                        'month'             : '',
                        'number'            : '',
                        'cal_value'         : rowValue['value'],
                        'cal_name'          : rowValue['name'],
                    }
                # pprint(yesterday.strftime("%d%m%Y"))
                if 'is_due_date' in reportDate[str(todayTimeStamp)]:
                    filter_cri = {
                        'PRODGRP_ID'    : {
                            '$in'       : product['product_code'].split(","),
                        },
                        "$or"           : [{
                            "createdAt" : {
                                "$gte"  : starttime,
                                "$lte"  : endtime
                            },
                        }, {
                            "updatedAt" : {
                                "$gte"  : starttime,
                                "$lte"  : endtime
                            }
                        }]
                    }
                else:
                    filter_cri = {
                        'F_PDT'         : (today).strftime("%d%m%Y"),
                        'PRODGRP_ID'    : {
                            '$in'       : product['product_code'].split(","),
                        },
                        "$or"           : [{
                            "createdAt" : {
                                "$gte"  : starttime,
                                "$lte"  : endtime
                            },
                        }, {
                            "updatedAt" : {
                                "$gte"  : starttime,
                                "$lte"  : endtime
                            }
                        }]
                    }
                zaccf_aggregate = [{
                    '$match'            : filter_cri
                }, {
                    '$group'            : {
                        '_id'           : '$INT_RATE',
                        'account_number': {
                            '$push'     : '$account_number'
                        }
                    }
                }]
                zaccf_info = list(mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, zaccf_collection), aggregate_pipeline=zaccf_aggregate))
                pprint(len(zaccf_info))
                total_by_rate_cell = {
                    'for_month'         : str(reportDate[str(todayTimeStamp)]['for_month']),
                    'for_year'          : str(reportDate[str(todayTimeStamp)]['for_year']),
                    'prod_group_code'   : product['group_code'],
                    'prod_group_name'   : product['group_name'],
                    'month'             : '',
                    'number'            : '',
                    'cal_value'         : rowValue['value'],
                    'cal_name'          : rowValue['name'],
                }
                for zaccf in zaccf_info:
                    total_by_rate = 0
                    if ('a' + reportDate[str(todayTimeStamp)]['debt_group'] + '_' + zaccf['_id'].replace('.', '')) not in temp_column:
                        temp_column.append(zaccf['_id'])
                        mongodb.update_add_to_set(MONGO_COLLECTION=common.getSubUser(subUserType, 'First_time_payment_delinqunecy_columns'), WHERE= {'for_year': str(reportDate[str(todayTimeStamp)]['for_year']), 'prod_group_code': product['group_code']}, VALUE={'columns': zaccf['_id']})

                    if rowValue['value'] == 'payday':
                        if 'is_due_date' not in reportDate[str(todayTimeStamp)]:
                            temp['index'] = 1
                            temp['a' + reportDate[str(todayTimeStamp)]['debt_group'] + '_' + zaccf['_id'].replace('.', '')] = datetime.fromtimestamp(reportDate[str(todayTimeStamp)]['due_date']).strftime("%d/%m")
                    
                    if rowValue['value'] == 'first_payment':
                        if 'is_due_date' not in reportDate[str(todayTimeStamp)]:
                            temp['index'] = 2
                            temp['a' + reportDate[str(todayTimeStamp)]['debt_group'] + '_' + zaccf['_id'].replace('.', '')] = len(zaccf['account_number'])
                            total_firstPayment += temp['a' + reportDate[str(todayTimeStamp)]['debt_group'] + '_' + zaccf['_id'].replace('.', '')]
                            total_by_rate += len(zaccf['account_number'])

                    if rowValue['value'] == 'remaining_after_5_days':
                        if 'is_due_date' in reportDate[str(todayTimeStamp)]:
                            countAcc = 0
                            for acc in zaccf['account_number']:
                                lnjc05 = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, lnjc05_collection), WHERE={'account_number': acc, 'installment_type': '1'})
                                if lnjc05 != None:
                                    countAcc += 3
                            temp['a' + reportDate[str(todayTimeStamp)]['debt_group'] + '_' + zaccf['_id'].replace('.', '')] = countAcc
                            temp['index'] = 3
                            total_remaining_after_five_days += countAcc
                            total_by_rate += countAcc

                    if rowValue['value'] == 'date':
                        if 'is_due_date' in reportDate[str(todayTimeStamp)]:
                            temp['index'] = 4
                            temp['a' + reportDate[str(todayTimeStamp)]['debt_group'] + '_' + zaccf['_id'].replace('.', '')] = datetime.fromtimestamp(reportDate[str(todayTimeStamp)]['due_date']).strftime("%d/%m")

                    if rowValue['value'] == 'rate':
                        if 'is_due_date' in reportDate[str(todayTimeStamp)]: 
                            first_payment = mongodb.getOne(MONGO_COLLECTION=collection, WHERE={'for_month': str(reportDate[str(todayTimeStamp)]['for_month']), 'for_year': str(reportDate[str(todayTimeStamp)]['for_year']), 'prod_group_code': product['group_code'], 'cal_value': 'first_payment'})
                            remaining = mongodb.getOne(MONGO_COLLECTION=collection, WHERE={'for_month': str(reportDate[str(todayTimeStamp)]['for_month']), 'for_year': str(reportDate[str(todayTimeStamp)]['for_year']), 'prod_group_code': product['group_code'], 'cal_value': 'remaining_after_5_days'})
                            if first_payment != None and 'a' + reportDate[str(todayTimeStamp)]['debt_group'] + '_' + zaccf['_id'].replace('.', '') in first_payment.keys() and first_payment['a' + reportDate[str(todayTimeStamp)]['debt_group'] + '_' + zaccf['_id'].replace('.', '')] != 0 and 'a' + reportDate[str(todayTimeStamp)]['debt_group'] + '_' + zaccf['_id'].replace('.', '') in remaining.keys():
                                temp['a' + reportDate[str(todayTimeStamp)]['debt_group'] + '_' + zaccf['_id'].replace('.', '')] = remaining['a' + reportDate[str(todayTimeStamp)]['debt_group'] + '_' + zaccf['_id'].replace('.', '')] / first_payment['a' + reportDate[str(todayTimeStamp)]['debt_group'] + '_' + zaccf['_id'].replace('.', '')]
                                temp['index'] = 5
                                pprint(temp['a' + reportDate[str(todayTimeStamp)]['debt_group'] + '_' + zaccf['_id'].replace('.', '')])
                                total_by_rate += temp['a' + reportDate[str(todayTimeStamp)]['debt_group'] + '_' + zaccf['_id'].replace('.', '')]
                    
                    temp_total_by_rate = {}
                    temp_total_by_rate['total_' + zaccf['_id'].replace('.', '')] = total_by_rate
                    # pprint(temp_total_by_rate)
                    mongodb.inc(MONGO_COLLECTION=collection, WHERE={'for_month': str(reportDate[str(todayTimeStamp)]['for_month']), 'for_year': str(reportDate[str(todayTimeStamp)]['for_year']), 'cal_value': rowValue['value'], 'prod_group_code': product['group_code']}, VALUE=temp_total_by_rate)
                
                if rowValue['value'] == 'payday': 
                    if 'is_due_date' not in reportDate[str(todayTimeStamp)]:
                        temp_total['a' + reportDate[str(todayTimeStamp)]['debt_group'] + '_total'] = datetime.fromtimestamp(reportDate[str(todayTimeStamp)]['due_date']).strftime("%d/%m")
                
                if rowValue['value'] == 'first_payment': 
                    if 'is_due_date' not in reportDate[str(todayTimeStamp)]:
                        temp_total['a' + reportDate[str(todayTimeStamp)]['debt_group'] + '_total'] = total_firstPayment

                if rowValue['value'] == 'remaining_after_5_days': 
                    if 'is_due_date' in reportDate[str(todayTimeStamp)]:
                        temp_total['a' + reportDate[str(todayTimeStamp)]['debt_group'] + '_total'] = total_remaining_after_five_days

                if rowValue['value'] == 'date':
                    if 'is_due_date' in reportDate[str(todayTimeStamp)]:
                        temp_total['a' + reportDate[str(todayTimeStamp)]['debt_group'] + '_total'] = datetime.fromtimestamp(reportDate[str(todayTimeStamp)]['due_date']).strftime("%d/%m")

                if rowValue['value'] == 'rate':
                    if 'is_due_date' in reportDate[str(todayTimeStamp)]:
                        temp_total['a' + reportDate[str(todayTimeStamp)]['debt_group'] + '_total'] = total_remaining_after_five_days / total_firstPayment if total_firstPayment != 0 else 0

                mongodb.update(MONGO_COLLECTION=collection, WHERE={'for_month': str(reportDate[str(todayTimeStamp)]['for_month']), 'for_year': str(reportDate[str(todayTimeStamp)]['for_year']), 'cal_value': rowValue['value'], 'prod_group_code': product['group_code']}, VALUE=temp)
                mongodb.update(MONGO_COLLECTION=collection, WHERE={'for_month': str(reportDate[str(todayTimeStamp)]['for_month']), 'for_year': str(reportDate[str(todayTimeStamp)]['for_year']), 'cal_value': rowValue['value'], 'prod_group_code': product['group_code']}, VALUE=temp_total)

except Exception as e:
    # log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
    print(traceback.format_exc())
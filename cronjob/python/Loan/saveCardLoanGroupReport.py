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
from math import floor
import traceback

common      = Common()
base_url    = common.base_url()
wff_env     = common.wff_env(base_url)
mongodb     = Mongodb(MONGODB="worldfone4xs", WFF_ENV=wff_env)
_mongodb    = Mongodb(MONGODB="_worldfone4xs", WFF_ENV=wff_env)

subUserType = 'LO'
collection = common.getSubUser(subUserType, 'Loan_group_report')
log = open(base_url + "cronjob/python/Loan/log/cardLoanGroup_log.txt","a")
now = datetime.now()
log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': Start Import' + '\n')
try:
    insertData = []
    updateData = []
    listDebtGroup = []

    today = date.today()
    today = datetime.strptime('21/12/2019', "%d/%m/%Y").date()

    day = today.day
    month = today.month
    year = today.year
    weekday = today.weekday()
    lastDayOfMonth = calendar.monthrange(year, month)[1]

    first_day = today.replace(day=1)

    weekdayMonth = first_day.weekday()
    if weekdayMonth == 6:
        weekdayMonth = 0
    else:
        weekdayMonth += 1
    adjusted_dom = day + weekdayMonth
    weekOfMonth =  (floor((adjusted_dom - 1)/7) + 1)

    todayString = today.strftime("%d/%m/%Y")
    todayTimeStamp = int(time.mktime(time.strptime(str(todayString + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))

    startMonth = int(time.mktime(time.strptime(str('01/' + str(month) + '/' + str(year) + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
    endMonth = int(time.mktime(time.strptime(str(str(lastDayOfMonth) + '/' + str(month) + '/' + str(year) + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))

    holidayOfMonth = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_off_sys'))
    listHoliday = map(lambda offDateRow: {offDateRow['off_date']}, holidayOfMonth)

    if todayTimeStamp in listHoliday or (weekday == 5) or weekday == 6:
        sys.exit()

    todayString = today.strftime("%d/%m/%Y")
    starttime = int(time.mktime(time.strptime(str(todayString + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
    endtime = int(time.mktime(time.strptime(str(todayString + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))

    mainProduct = {}
    mainProductRaw = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Product'))
    for prod in mainProductRaw:
        mainProduct[prod['code']] = prod['name']

    
    month = today.strftime("%B")
    weekday = today.strftime("%A")

    # ZACCF
    aggregate_zaccf = [
        {
            "$match":
            {
                "W_ORG": {'$gt': '0'},
            }
        },{
            "$group":
            {
                "_id": '$ODIND_FG',
                "total_org": {'$sum': '$W_ORG'},
                "count_data": {'$sum': 1},
            }
        }
    ]
    zaccfInfo = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'ZACCF'),aggregate_pipeline=aggregate_zaccf)
    zaccfInfo1 = zaccfInfo
    sum_org = 0
    sum_acc = 0
    sum_org_g2 = 0
    sum_acc_g2 = 0
    sum_org_g3 = 0
    sum_acc_g3 = 0
    if zaccfInfo is not None:
        for zaccf in zaccfInfo:
            if zaccf['_id'] != '':
                sum_org += zaccf['total_org']
                sum_acc += zaccf['count_data']
                if zaccf['_id'] != 'A':
                    sum_org_g2 += zaccf['total_org']
                    sum_acc_g2 += zaccf['count_data']
                if zaccf['_id'] != 'A' or zaccf['_id'] != 'B':
                    sum_org_g3 += zaccf['total_org']
                    sum_acc_g3 += zaccf['count_data']

    aggregate_zaccf = [
        {
            "$match":
            {
                "W_ORG": {'$gt': '0'},
            }
        },{
            "$group":
            {
                "_id": '$ODIND_FG',
                "total_org": {'$sum': '$W_ORG'},
                "count_data": {'$sum': 1},
            }
        }
    ]
    zaccfInfo = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'ZACCF'),aggregate_pipeline=aggregate_zaccf)
    if zaccfInfo is not None:
        for zaccf in zaccfInfo:
            if zaccf['_id'] != '':
                temp = {}
                if zaccf['_id'] == 'A':
                    temp['group'] = '1'
                if zaccf['_id'] == 'B':
                    temp['group'] = '2'
                if zaccf['_id'] == 'C':
                    temp['group'] = '3'
                if zaccf['_id'] == 'D':
                    temp['group'] = '4'
                if zaccf['_id'] == 'E':
                    temp['group'] = '5'

                temp['count_data'] = zaccf['count_data']
                temp['total_org'] = zaccf['total_org']
                temp['ratio'] = temp['total_org']/sum_org if sum_org != 0 else 0
                temp['year'] = str(year)
                temp['month'] = month
                temp['weekday'] = weekday
                temp['day'] = todayString
                temp['weekOfMonth'] = weekOfMonth
                temp['type'] = 'sibs'
                temp['createdBy'] = 'system'
                temp['createdAt'] = time.time()
                mongodb.insert(MONGO_COLLECTION=collection, insert_data=temp)
    
        insertTotal = {
            'year'          : str(year),
            'month'         : month,
            'weekday'       : weekday,
            'day'           : todayString,
            'weekOfMonth'   : weekOfMonth,
            'type'          : 'sibs',
            'createdBy'     : 'system',
            'createdAt'     : time.time()
        }
        insertTotalG2 = {
            'year'          : str(year),
            'month'         : month,
            'weekday'       : weekday,
            'day'           : todayString,
            'weekOfMonth'   : weekOfMonth,
            'type'          : 'sibs',
            'createdBy'     : 'system',
            'createdAt'     : time.time()
        }
        insertTotalG3 = {
            'year'          : str(year),
            'month'         : month,
            'weekday'       : weekday,
            'day'           : todayString,
            'weekOfMonth'   : weekOfMonth,
            'type'          : 'sibs',
            'createdBy'     : 'system',
            'createdAt'     : time.time()
        }
        insertTotal['group']       = 'Total'
        insertTotal['total_org']   = sum_org
        insertTotal['count_data']  = sum_acc
        mongodb.insert(MONGO_COLLECTION=collection, insert_data=insertTotal)
        insertTotalG2['group']       = 'G2'
        insertTotalG2['total_org']   = sum_org_g2
        insertTotalG2['count_data']  = sum_acc_g2
        insertTotalG2['ratio']       = insertTotalG2['total_org']/sum_org if sum_org != 0 else 0
        mongodb.insert(MONGO_COLLECTION=collection, insert_data=insertTotalG2)
        insertTotalG3['group']       = 'G3'
        insertTotalG3['total_org']   = sum_org_g3
        insertTotalG3['count_data']  = sum_acc_g3
        insertTotalG3['ratio']       = insertTotalG3['total_org']/sum_org if sum_org != 0 else 0
        mongodb.insert(MONGO_COLLECTION=collection, insert_data=insertTotalG3)




    # Card
    aggregate_sbv = [
        {
            "$match":
            {
                '$or' : [{
                    'ob_principal_sale': {'$gt': 0},
                    'ob_principal_cash' : {'$gt': 0}
                }]
            }
        },
        {
            "$group":
            {
                "_id": '$first_due_group',
                "total_ob_principal_sale": {'$sum': '$ob_principal_sale'},
                "total_ob_principal_cash": {'$sum': '$ob_principal_cash'},
                "count_data": {'$sum': 1},
            }
        }
    ]
    sbvInfo = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'SBV'),aggregate_pipeline=aggregate_sbv)
    sum_org = 0
    sum_acc = 0
    sum_org_g2 = 0
    sum_acc_g2 = 0
    sum_org_g3 = 0
    sum_acc_g3 = 0
    if sbvInfo is not None:
        for sbv in sbvInfo:
            if sbv['_id'] != None:
                total_org =  sbv['total_ob_principal_sale'] + sbv['total_ob_principal_cash']
                sum_org += total_org
                sum_acc += sbv['count_data']
                if sbv['_id'] != '01':
                    sum_org_g2 += total_org
                    sum_acc_g2 += sbv['count_data']
                if sbv['_id'] != '01' or sbv['_id'] != '02':
                    sum_org_g3 += total_org
                    sum_acc_g3 += sbv['count_data']



    aggregate_sbv = [
        {
            "$match":
            {
                '$or' : [{
                    'ob_principal_sale': {'$gt': 0},
                    'ob_principal_cash' : {'$gt': 0}
                }]
            }
        },
        {
            "$group":
            {
                "_id": '$first_due_group',
                "total_ob_principal_sale": {'$sum': '$ob_principal_sale'},
                "total_ob_principal_cash": {'$sum': '$ob_principal_cash'},
                "count_data": {'$sum': 1},
            }
        }
    ]
    sbvInfo = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'SBV'),aggregate_pipeline=aggregate_sbv)
    if sbvInfo is not None:
        for sbv in sbvInfo:
            if sbv['_id'] != None:
                temp = {}
                temp['group'] = sbv['_id']
                temp['count_data'] = sbv['count_data']
                temp['total_org'] = sbv['total_ob_principal_sale'] + sbv['total_ob_principal_cash']
                temp['ratio'] = temp['total_org']/sum_org if sum_org != 0 else 0
                temp['year'] = str(year)
                temp['month'] = month
                temp['weekday'] = weekday
                temp['day'] = todayString
                temp['weekOfMonth'] = weekOfMonth
                temp['type'] = 'card'
                temp['createdBy'] = 'system'
                temp['createdAt'] = time.time()
                mongodb.insert(MONGO_COLLECTION=collection, insert_data=temp)

        insertTotal = {
            'year'          : str(year),
            'month'         : month,
            'weekday'       : weekday,
            'day'           : todayString,
            'weekOfMonth'   : weekOfMonth,
            'type'          : 'card',
            'createdBy'     : 'system',
            'createdAt'     : time.time()
        }
        insertTotalG2 = {
            'year'          : str(year),
            'month'         : month,
            'weekday'       : weekday,
            'day'           : todayString,
            'weekOfMonth'   : weekOfMonth,
            'type'          : 'card',
            'createdBy'     : 'system',
            'createdAt'     : time.time()
        }
        insertTotalG3 = {
            'year'          : str(year),
            'month'         : month,
            'weekday'       : weekday,
            'day'           : todayString,
            'weekOfMonth'   : weekOfMonth,
            'type'          : 'card',
            'createdBy'     : 'system',
            'createdAt'     : time.time()
        }
        insertTotal['group']       = 'Total'
        insertTotal['total_org']   = sum_org
        insertTotal['count_data']  = sum_acc
        mongodb.insert(MONGO_COLLECTION=collection, insert_data=insertTotal)
        insertTotalG2['group']       = 'G2'
        insertTotalG2['total_org']   = sum_org_g2
        insertTotalG2['count_data']  = sum_acc_g2
        insertTotalG2['ratio']       = insertTotalG2['total_org']/sum_org if sum_org != 0 else 0
        mongodb.insert(MONGO_COLLECTION=collection, insert_data=insertTotalG2)
        insertTotalG3['group']       = 'G3'
        insertTotalG3['total_org']   = sum_org_g3
        insertTotalG3['count_data']  = sum_acc_g3
        insertTotalG3['ratio']       = insertTotalG3['total_org']/sum_org if sum_org != 0 else 0
        mongodb.insert(MONGO_COLLECTION=collection, insert_data=insertTotalG3)



    now_end         = datetime.now()
    log.write(now_end.strftime("%d/%m/%Y, %H:%M:%S") + ': End Log' + '\n')
    print('DONE')
except Exception as e:
    log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
    print(traceback.format_exc())

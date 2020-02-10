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
    zaccfData = []
    today = date.today()
    # today = datetime.strptime('05/01/2020', "%d/%m/%Y").date()
    yesterday = today - timedelta(days=1)

    day = yesterday.day
    month = yesterday.month
    year = yesterday.year
    weekday = yesterday.weekday()
    lastDayOfMonth = calendar.monthrange(year, month)[1]

    first_day = yesterday.replace(day=1)

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

    # holidayOfMonth = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_off_sys'), WHERE={'off_date': todayTimeStamp})
    # if holidayOfMonth != None:
    #     sys.exit()
    # listHoliday = map(lambda offDateRow: {offDateRow['off_date']}, holidayOfMonth)

    # if todayTimeStamp in listHoliday:
    #     sys.exit()

    yesterdayString = yesterday.strftime("%d/%m/%Y")
    

    mainProduct = {}
    mainProductRaw = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Product'))
    for prod in mainProductRaw:
        mainProduct[prod['code']] = prod['name']

    
    month = yesterday.strftime("%B")
    weekday = yesterday.strftime("%A")

    # ZACCF
    aggregate_zaccf = [
        {
            "$match":
            {
                "W_ORG_1": {'$gt': 0},
            }
        },{
            "$group":
            {
                "_id": '$ODIND_FG',
                "total_org": {'$sum': '$W_ORG_1'},
                'W_ORG_arr': {'$push': '$W_ORG_1'},
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
    sum_org_B = 0
    sum_acc_B = 0
    if zaccfInfo is not None:
        for zaccf in zaccfInfo:
            if zaccf['_id'] != None:
                temp = zaccf
                # temp['total_org'] = 0
                # for orgInfo in zaccf['W_ORG_arr']:
                #     org = float(orgInfo)
                #     temp['total_org'] += org
                # # print(zaccf['total_org'])
                zaccfData.append(temp)

        for zaccf in zaccfData:
            if zaccf['_id'] != None:
                sum_org += zaccf['total_org']
                sum_acc += zaccf['count_data']
                if zaccf['_id'] != 'A':
                    sum_org_g2 += zaccf['total_org']
                    sum_acc_g2 += zaccf['count_data']
                if zaccf['_id'] == 'B':
                    sum_org_B += zaccf['total_org']
                    sum_acc_B += zaccf['count_data']

    sum_org_g3 = sum_org_g2 - sum_org_B
    sum_acc_g3 = sum_acc_g2 - sum_acc_B

    for zaccf in zaccfData:
        if zaccf['_id'] != None:
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
            temp['day'] = yesterdayString
            temp['weekOfMonth'] = weekOfMonth
            temp['type'] = 'sibs'
            temp['createdBy'] = 'system'
            temp['createdAt'] = todayTimeStamp
            mongodb.insert(MONGO_COLLECTION=collection, insert_data=temp)

    insertTotal = {
        'year'          : str(year),
        'month'         : month,
        'weekday'       : weekday,
        'day'           : yesterdayString,
        'weekOfMonth'   : weekOfMonth,
        'type'          : 'sibs',
        'createdBy'     : 'system',
        'createdAt'     : todayTimeStamp
    }
    insertTotalG2 = {
        'year'          : str(year),
        'month'         : month,
        'weekday'       : weekday,
        'day'           : yesterdayString,
        'weekOfMonth'   : weekOfMonth,
        'type'          : 'sibs',
        'createdBy'     : 'system',
        'createdAt'     : todayTimeStamp
    }
    insertTotalG3 = {
        'year'          : str(year),
        'month'         : month,
        'weekday'       : weekday,
        'day'           : yesterdayString,
        'weekOfMonth'   : weekOfMonth,
        'type'          : 'sibs',
        'createdBy'     : 'system',
        'createdAt'     : todayTimeStamp
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
    sbvData = []
    
    aggregate_sbv_1 = [
        {
            "$project":
            {
                'pay': {'$sum' : [ '$ob_principal_sale', '$ob_principal_cash']},
                'delinquency_group': 1
            }
        },
        {
            "$match":
            {
                'pay': {'$gt' : 0}
            }
        },
        {
            "$group":
            {
                "_id": '$delinquency_group',
                "total_ob_principal": {'$sum': '$pay'},
                "count_data": {'$sum': 1},
            }
        }
    ]
    dataSBV = list(mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'SBV'),aggregate_pipeline=aggregate_sbv_1))
             
    sum_org = 0
    sum_acc = 0
    sum_org_g2 = 0
    sum_acc_g2 = 0
    sum_org_g3 = 0
    sum_acc_g3 = 0
    sum_org_B = 0
    sum_acc_B = 0
    if dataSBV is not None:
        for sbv in dataSBV:
            temp = sbv
            total_org =  sbv['total_ob_principal']

            temp['total_org'] = total_org
            sum_org += total_org
            sum_acc += sbv['count_data']
            if sbv['_id'] != '01':
                sum_org_g2 += total_org
                sum_acc_g2 += sbv['count_data']
            if  sbv['_id'] == '02':
                sum_org_B += total_org
                sum_acc_B += sbv['count_data']

            sbvData.append(temp)

    sum_org_g3 = sum_org_g2 - sum_org_B
    sum_acc_g3 = sum_acc_g2 - sum_acc_B

    # print(sum_acc_g2)
    # print(sum_acc_g3)

    if sbvData is not None:
        for sbv in sbvData:
            temp = {}
            temp['group'] = sbv['_id']
            temp['count_data'] = sbv['count_data']
            temp['total_org'] = sbv['total_org']
            temp['ratio'] = temp['total_org']/sum_org if sum_org != 0 else 0
            temp['year'] = str(year)
            temp['month'] = month
            temp['weekday'] = weekday
            temp['day'] = yesterdayString
            temp['weekOfMonth'] = weekOfMonth
            temp['type'] = 'card'
            temp['createdBy'] = 'system'
            temp['createdAt'] = todayTimeStamp
            # print(temp)
            mongodb.insert(MONGO_COLLECTION=collection, insert_data=temp)

        insertTotal = {
            'year'          : str(year),
            'month'         : month,
            'weekday'       : weekday,
            'day'           : yesterdayString,
            'weekOfMonth'   : weekOfMonth,
            'type'          : 'card',
            'createdBy'     : 'system',
            'createdAt'     : todayTimeStamp
        }
        insertTotalG2 = {
            'year'          : str(year),
            'month'         : month,
            'weekday'       : weekday,
            'day'           : yesterdayString,
            'weekOfMonth'   : weekOfMonth,
            'type'          : 'card',
            'createdBy'     : 'system',
            'createdAt'     : todayTimeStamp
        }
        insertTotalG3 = {
            'year'          : str(year),
            'month'         : month,
            'weekday'       : weekday,
            'day'           : yesterdayString,
            'weekOfMonth'   : weekOfMonth,
            'type'          : 'card',
            'createdBy'     : 'system',
            'createdAt'     : todayTimeStamp
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

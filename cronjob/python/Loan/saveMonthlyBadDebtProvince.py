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
collection = common.getSubUser(subUserType, 'Monthly_bad_debt_province')
try:
    total = 0
    complete = 0
    today = date.today()
    # today = datetime.strptime('13/12/2019', "%d/%m/%Y").date()
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
    endMonth = int(time.mktime(time.strptime(str(str(lastDayOfMonth) + '/' + str(month) + '/' + str(year) + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))
    endMonthEndtime = int(time.mktime(time.strptime(str(str(lastDayOfMonth) + '/' + str(month) + '/' + str(year) + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))
    endMonthStarttime = endMonthEndtime - 86399
    holidayOfMonth = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_off_sys'))
    listHoliday = map(lambda offDateRow: {offDateRow['off_date']}, holidayOfMonth)

    listDayOfMonth = []
    startDayOfMonth = 1
    while startDayOfMonth <= lastDayOfMonth:
        listDayOfMonth.append(str(format(startDayOfMonth, '02d')) + str(format(month, '02d')) + str(year))
        startDayOfMonth += 1
    
    if todayTimeStamp in listHoliday or (weekday == 5) or weekday == 6:
        sys.exit()

    # Check hom nay co phai la ngay cuoi thang
    if todayTimeStamp > endMonthStarttime or todayTimeStamp < endMonthEndtime:
        sys.exit()

    todayString = today.strftime("%d/%m/%Y")
    starttime = int(time.mktime(time.strptime(str(todayString + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
    endtime = int(time.mktime(time.strptime(str(todayString + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))
    
    list_state = mongodb.get(common.getSubUser(subUserType, 'Province'))

    for state_code, state_value in enumerate(list_state):
        temp = {}

        # ZACCF
        aggregate_zaccf = [
            {
                "$match"                            : {
                    'STAT_CD'                       : state_value['code'],
                    "$or"                           : [{
                        "createdAt"                 : {
                            "$gte"                  : starttime,
                            "$lte"                  : endtime
                        },
                    }, {
                        "updatedAt"                 : {
                            "$gte"                  : starttime,
                            "$lte"                  : endtime
                        }
                    }] 
                }
            }, 
            {
                "$group"                            : {
                    "_id"                           : None,
                    "release_acc_no_sibs"           : {
                        "$sum"                      : {
                            "$cond"                 : [
                                {
                                    '$and'          : [
                                        {"$in"      : ["$FRELD8", listDayOfMonth]},
                                    ]
                                },
                                1,
                                0
                            ]
                        }
                    },
                    "release_amt_sibs"              : {
                        "$push"                     : "$APPROV_LMT"
                    },
                    "group_two_acc_sibs"            : {
                        '$sum'                      : {
                            '$cond'                 : [
                                {
                                    '$eq'           : ['$ODIND_FG', 'B']
                                },
                                1,
                                0
                            ]
                        }
                    },
                    'group_two_w_org_sibs'          : {
                        '$push'                     : {
                            '$cond'                 : [
                                {
                                    '$eq'           : ['$ODIND_FG', 'B']
                                },
                                '$W_ORG',
                                0
                            ]
                        }
                    },
                    'group_two_plus_acc_sibs'       : {
                        '$sum'                      : {
                            '$cond'                 : [
                                {
                                    '$in'           : ['$ODIND_FG', ['C', 'D', 'E']]
                                },
                                1,
                                0
                            ]
                        }
                    },
                    'group_two_plus_w_org_sibs'     : {
                        '$push'                     : {
                            '$cond'                 : [
                                {
                                    '$eq'           : ['$ODIND_FG', ['C', 'D', 'E']]
                                },
                                '$W_ORG',
                                0
                            ]
                        }
                    },
                }
            }
        ]
        zaccfInfo = list(mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'ZACCF'),aggregate_pipeline=aggregate_zaccf))
        temp['province_id']                     = state_value['code']
        temp['province_name']                   = state_value['name']
        if zaccfInfo not in [None, []] and zaccfInfo[0] is not None:
            temp['release_acc_no_sibs']         = zaccfInfo[0]['release_acc_no_sibs'] if zaccfInfo[0]['release_acc_no_sibs'] is not None else 0
            temp['release_amt_sibs']            = sum(map(lambda x: float(x), zaccfInfo[0]['release_amt_sibs'])) if zaccfInfo[0]['release_amt_sibs'] is not None else 0
            temp['group_two_acc_sibs']          = zaccfInfo[0]['group_two_acc_sibs'] if zaccfInfo[0]['group_two_acc_sibs'] is not None else 0
            temp['group_two_w_org_sibs']        = sum(map(lambda x: float(x), zaccfInfo[0]['group_two_w_org_sibs'])) if zaccfInfo[0]['group_two_w_org_sibs'] is not None else 0
            temp['group_two_plus_acc_sibs']     = zaccfInfo[0]['group_two_plus_acc_sibs'] if zaccfInfo[0]['group_two_plus_acc_sibs'] is not None else 0
            temp['group_two_plus_w_org_sibs']   = sum(map(lambda x: float(x), zaccfInfo[0]['group_two_plus_w_org_sibs'])) if zaccfInfo[0]['group_two_plus_w_org_sibs'] is not None else 0

        state_filter_sbv = str(int(state_value['code']))
        if len(state_filter_sbv) == 1:
            state_filter_sbv = '0' + state_filter_sbv

        # SBV
        aggregate_sbv = [
            {
                '$match'                        : {
                    'state'                     : state_filter_sbv
                }
            },
            {
                '$group'                        : {
                    '_id'                       : None,
                    "release_acc_no_card"       : {
                        "$sum"                  : {
                            "$cond"             : [
                                {
                                    '$and'      : [
                                        {"$in"  : ["$open_card_date", listDayOfMonth]},
                                    ]
                                },
                                1,
                                0
                            ]
                        }
                    },
                    'release_amt_card'          : {
                        '$sum'                  : '$approved_limit'
                    },
                    "group_two_acc_card"        : {
                        '$sum'                  : {
                            '$cond'             : [
                                {
                                    '$eq'       : ['$overdue_indicator', 'B']
                                },
                                1,
                                0
                            ]
                        }
                    },
                    'group_two_w_org_card'      : {
                        '$sum'                  : {
                            '$cond'             : [
                                {
                                    '$eq'       : ['$overdue_indicator', 'B']
                                },
                                {'$add'         : ['$ob_principal_sale', '$ob_principal_cash']},
                                0
                            ]
                        }
                    },
                    'group_two_plus_acc_card'   : {
                        '$sum'                  : {
                            '$cond'             : [
                                {
                                    '$in'       : ['$overdue_indicator', ['C', 'D', 'E']]
                                },
                                1,
                                0
                            ]
                        }
                    },
                    'group_two_plus_w_org_card' : {
                        '$sum'                  : {
                            '$cond'             : [
                                {
                                    '$eq'       : ['$overdue_indicator', ['C', 'D', 'E']]
                                },
                                {'$add'         : ['$ob_principal_sale', '$ob_principal_cash']},
                                0
                            ]
                        }
                    },
                },
            }
        ]
        sbvInfo = list(mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'SBV'),aggregate_pipeline=aggregate_sbv))
        temp['release_acc_no_card'] = sbvInfo[0]['release_acc_no_card'] if sbvInfo not in [None, []] and sbvInfo[0] is not None and sbvInfo[0]['release_acc_no_card'] is not None else 0
        temp['release_amt_card'] = sbvInfo[0]['release_amt_card'] if sbvInfo not in [None, []] and sbvInfo[0] is not None and sbvInfo[0]['release_amt_card'] is not None else 0
        temp['group_two_acc_card'] = sbvInfo[0]['group_two_acc_card'] if sbvInfo not in [None, []] and sbvInfo[0] is not None and sbvInfo[0]['group_two_acc_card'] is not None else 0
        temp['group_two_w_org_card'] = sbvInfo[0]['group_two_w_org_card'] if sbvInfo not in [None, []] and sbvInfo[0] is not None and sbvInfo[0]['group_two_w_org_card'] is not None else 0
        temp['group_two_plus_acc_card'] = sbvInfo[0]['group_two_plus_acc_card'] if sbvInfo not in [None, []] and sbvInfo[0] is not None and sbvInfo[0]['group_two_plus_acc_card'] is not None else 0
        temp['group_two_plus_w_org_card'] = sbvInfo[0]['group_two_plus_w_org_card'] if sbvInfo not in [None, []] and sbvInfo[0] is not None and sbvInfo[0]['group_two_plus_w_org_card'] is not None else 0

        temp['total_acc_no_sibs'] = temp['release_acc_no_sibs']  + temp['group_two_acc_sibs'] + temp['group_two_plus_acc_sibs']
        temp['total_amt_sibs'] = temp['release_amt_sibs'] + temp['group_two_w_org_sibs'] + temp['group_two_plus_w_org_sibs']
        temp['total_acc_no_card'] = temp['release_acc_no_card'] + temp['group_two_acc_card'] + temp['group_two_plus_acc_card']
        temp['total_amt_card'] = temp['release_amt_card'] + temp['group_two_w_org_card'] + temp['group_two_plus_w_org_card']

        temp['total_acc'] = temp['total_acc_no_sibs'] + temp['total_acc_no_card']
        temp['total_amt'] = temp['total_amt_sibs'] + temp['total_amt_card']

        temp['bad_debt_ratio_sibs'] = temp['group_two_plus_w_org_sibs'] / temp['total_amt_sibs'] if temp['total_amt_sibs'] != 0 else 0
        temp['bad_debt_ratio_card'] = temp['group_two_plus_w_org_card'] / temp['total_amt_card'] if temp['total_amt_card'] != 0 else 0
        temp['bad_debt_ratio'] = (temp['group_two_plus_w_org_sibs'] + temp['group_two_plus_w_org_card']) / temp['total_amt'] if temp['total_amt'] != 0 else 0

        insertData.append(temp)
        
    mongodb.batch_insert(MONGO_COLLECTION=collection, insert_data=insertData)
    print('DONE')
except Exception as e:
    log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
    print(traceback.format_exc())

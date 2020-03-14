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
    # today = datetime.strptime('01/02/2020', "%d/%m/%Y").date()
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

    holidayOfMonth = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_off_sys'))
    listHoliday = map(lambda offDateRow: {offDateRow['off_date']}, holidayOfMonth)
    
    listDayOfMonth = []
    startDayOfMonth = 1
    while startDayOfMonth <= int(lastMonthLastDate.day):
        listDayOfMonth.append(str(format(startDayOfMonth, '02d')) + str(format(lastMonthMonth, '02d')) + str(lastMonthYear))
        startDayOfMonth += 1

    # Check hom nay co phai la ngay cuoi thang
    if day != 1:
        pprint('khong phai ngay 1')
        sys.exit()
    
    todayString = today.strftime("%d/%m/%Y")
    starttime = int(time.mktime(time.strptime(str(todayString + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
    endtime = int(time.mktime(time.strptime(str(todayString + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))
    
    list_state = list(mongodb.get(common.getSubUser(subUserType, 'Province')))

    for state_code, state_value in enumerate(list_state):
        temp = {}

        # ZACCF
        aggregate_zaccf = [
            {
                "$match"                            : {
                    'STAT_CD'                       : state_value['code'],
                    'W_ORG_1'                       : {'$gt'     : 0},
                }
            }, 
            {
                "$group"                            : {
                    "_id"                           : None,
                    "total_acc_no_sibs"             : {
                        '$sum'                      : 1
                    },
                    "total_amt_sibs"                : {
                        '$sum'                      : '$W_ORG_1',
                    },
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
                        '$sum'                      : {
                            '$cond'                 : [
                                {
                                    '$and'          : [
                                        {"$in"      : ["$FRELD8", listDayOfMonth]},
                                    ]
                                },
                                '$W_ORG_1',
                                0
                            ]
                        }
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
                        '$sum'                      : {
                            '$cond'                 : [
                                {
                                    '$eq'           : ['$ODIND_FG', 'B']
                                },
                                '$W_ORG_1',
                                0
                            ]
                        }
                    },
                    'group_two_plus_acc_sibs'       : {
                        '$sum'                      : {
                            '$cond'                 : [
                                {
                                    '$in'           : ['$ODIND_FG', ['B', 'C', 'D', 'E']]
                                },
                                1,
                                0
                            ]
                        }
                    },
                    'group_two_plus_w_org_sibs'     : {
                        '$sum'                      : {
                            '$cond'                 : [
                                {
                                    '$in'           : ['$ODIND_FG', ['B', 'C', 'D', 'E']]
                                },
                                '$W_ORG_1',
                                0
                            ]
                        }
                    },
                    'group_three_plus_acc_sibs'       : {
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
                    'group_three_plus_w_org_sibs'     : {
                        '$sum'                      : {
                            '$cond'                 : [
                                {
                                    '$in'           : ['$ODIND_FG', ['C', 'D', 'E']]
                                },
                                '$W_ORG_1',
                                0
                            ]
                        }
                    },
                }
            }
        ]
        zaccfInfo = list(mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'ZACCF_report'),aggregate_pipeline=aggregate_zaccf))
        temp['province_id']                     = state_value['code']
        temp['province_name']                   = state_value['name']
        if zaccfInfo not in [None, []] and zaccfInfo[0] is not None:
            temp['release_acc_no_sibs']         = zaccfInfo[0]['release_acc_no_sibs'] if zaccfInfo[0]['release_acc_no_sibs'] is not None else 0
            temp['release_amt_sibs']            = zaccfInfo[0]['release_amt_sibs'] if zaccfInfo[0]['release_amt_sibs'] is not None else 0
            temp['group_two_acc_sibs']          = zaccfInfo[0]['group_two_acc_sibs'] if zaccfInfo[0]['group_two_acc_sibs'] is not None else 0
            temp['group_two_w_org_sibs']        = zaccfInfo[0]['group_two_w_org_sibs'] if zaccfInfo[0]['group_two_w_org_sibs'] is not None else 0
            temp['group_two_plus_acc_sibs']     = zaccfInfo[0]['group_two_plus_acc_sibs'] if zaccfInfo[0]['group_two_plus_acc_sibs'] is not None else 0
            temp['group_two_plus_w_org_sibs']   = zaccfInfo[0]['group_two_plus_w_org_sibs'] if zaccfInfo[0]['group_two_plus_w_org_sibs'] is not None else 0
            temp['group_three_plus_acc_sibs']   = zaccfInfo[0]['group_three_plus_acc_sibs'] if zaccfInfo[0]['group_three_plus_acc_sibs'] is not None else 0
            temp['group_three_plus_w_org_sibs'] = zaccfInfo[0]['group_three_plus_w_org_sibs'] if zaccfInfo[0]['group_three_plus_w_org_sibs'] is not None else 0
            temp['total_acc_no_sibs']           = zaccfInfo[0]['total_acc_no_sibs'] if zaccfInfo[0]['total_acc_no_sibs'] is not None else 0
            temp['total_amt_sibs']              = zaccfInfo[0]['total_amt_sibs'] if zaccfInfo[0]['total_amt_sibs'] is not None else 0
        else:
            temp['release_acc_no_sibs']         = 0
            temp['release_amt_sibs']            = 0
            temp['group_two_acc_sibs']          = 0
            temp['group_two_w_org_sibs']        = 0
            temp['group_two_plus_acc_sibs']     = 0
            temp['group_two_plus_w_org_sibs']   = 0
            temp['group_three_plus_acc_sibs']   = 0
            temp['group_three_plus_w_org_sibs'] = 0
            temp['total_acc_no_sibs']           = 0
            temp['total_amt_sibs']              = 0

        state_filter_sbv = str(int(state_value['code']))
        if len(state_filter_sbv) == 1:
            state_filter_sbv = '0' + state_filter_sbv

        # SBV
        aggregate_sbv = [
            {
                '$project'                      :
                {
                    'cond'                      : {'$sum' : ['$ob_principal_cash','$ob_principal_sale']},
                    'open_card_date'            : '$open_card_date',
                    'ob_principal_cash'         : '$ob_principal_cash',
                    'ob_principal_sale'         : '$ob_principal_sale',
                    'delinquency_group'         : '$delinquency_group',
                    'state'                     : '$state'
                }
            },
            {
                '$match'                        : {
                    'state'                     : state_filter_sbv,
                    'cond'                      : {'$gt'     : 0},
                }
            },
            {
                '$group'                        : {
                    '_id'                       : None,
                    "total_acc_no_card"         : {
                        "$sum"                  : 1
                    },
                    "total_amt_card"            : {
                        "$sum"                  : {
                            '$add'              : ['$ob_principal_sale', '$ob_principal_cash']
                        },
                    },
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
                        '$sum'                  : {
                            '$cond'             : [
                                {
                                    '$and'      : [
                                        {"$in"  : ["$open_card_date", listDayOfMonth]},
                                    ]
                                },
                                {'$add': ['$ob_principal_cash','$ob_principal_sale']},
                                0
                            ]
                        }
                    },
                    "group_two_acc_card"        : {
                        '$sum'                  : {
                            '$cond'             : [
                                {
                                    '$eq'       : ['$delinquency_group', '02']
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
                                    '$eq'       : ['$delinquency_group', '02']
                                },
                                {'$add'         : ['$ob_principal_sale', '$ob_principal_cash']},
                                '0'
                            ]
                        }
                    },
                    'group_two_plus_acc_card'   : {
                        '$sum'                  : {
                            '$cond'             : [
                                {
                                    '$in'       : ['$delinquency_group', ['02', '03', '04', '05']]
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
                                    '$in'       : ['$delinquency_group', ['02', '03', '04', '05']]
                                },
                                {'$add'         : ['$ob_principal_sale', '$ob_principal_cash']},
                                '0'
                            ]
                        }
                    },
                    'group_three_plus_acc_card'   : {
                        '$sum'                  : {
                            '$cond'             : [
                                {
                                    '$in'       : ['$delinquency_group', ['03', '04', '05']]
                                },
                                1,
                                0
                            ]
                        }
                    },
                    'group_three_plus_w_org_card' : {
                        '$sum'                  : {
                            '$cond'             : [
                                {
                                    '$in'       : ['$delinquency_group', ['03', '04', '05']]
                                },
                                {'$add'         : ['$ob_principal_sale', '$ob_principal_cash']},
                                '0'
                            ]
                        }
                    },
                },
            }
        ]
        sbvInfo = list(mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'SBV'),aggregate_pipeline=aggregate_sbv))
        
        temp['release_acc_no_card']         = sbvInfo[0]['release_acc_no_card'] if sbvInfo not in [None, []] and sbvInfo[0] is not None and sbvInfo[0]['release_acc_no_card'] is not None else 0
        temp['release_amt_card']            = sbvInfo[0]['release_amt_card'] if sbvInfo not in [None, []] and sbvInfo[0] is not None and sbvInfo[0]['release_amt_card'] is not None else 0
        temp['group_two_acc_card']          = sbvInfo[0]['group_two_acc_card'] if sbvInfo not in [None, []] and sbvInfo[0] is not None and sbvInfo[0]['group_two_acc_card'] is not None else 0
        temp['group_two_w_org_card']        = sbvInfo[0]['group_two_w_org_card'] if sbvInfo not in [None, []] and sbvInfo[0] is not None and sbvInfo[0]['group_two_w_org_card'] is not None else 0
        temp['group_two_plus_acc_card']     = sbvInfo[0]['group_two_plus_acc_card'] if sbvInfo not in [None, []] and sbvInfo[0] is not None and sbvInfo[0]['group_two_plus_acc_card'] is not None else 0
        temp['group_two_plus_w_org_card']   = sbvInfo[0]['group_two_plus_w_org_card'] if sbvInfo not in [None, []] and sbvInfo[0] is not None and sbvInfo[0]['group_two_plus_w_org_card'] is not None else 0
        temp['group_three_plus_acc_card']   = sbvInfo[0]['group_three_plus_acc_card'] if sbvInfo not in [None, []] and sbvInfo[0] is not None and sbvInfo[0]['group_three_plus_acc_card'] is not None else 0
        temp['group_three_plus_w_org_card'] = sbvInfo[0]['group_three_plus_w_org_card'] if sbvInfo not in [None, []] and sbvInfo[0] is not None and sbvInfo[0]['group_three_plus_w_org_card'] is not None else 0
        temp['total_acc_no_card']           = sbvInfo[0]['total_acc_no_card'] if sbvInfo not in [None, []] and sbvInfo[0] is not None and sbvInfo[0]['total_acc_no_card'] is not None else 0
        temp['total_amt_card']              = sbvInfo[0]['total_amt_card'] if sbvInfo not in [None, []] and sbvInfo[0] is not None and sbvInfo[0]['total_amt_card'] is not None else 0

        temp['total_acc'] = temp['total_acc_no_sibs'] + temp['total_acc_no_card']
        temp['total_amt'] = temp['total_amt_sibs'] + temp['total_amt_card']

        temp['total_acc_group_two'] = temp['group_two_acc_sibs'] + temp['group_two_acc_card']
        temp['total_amt_group_two'] = temp['group_two_w_org_sibs'] + temp['group_two_w_org_card']

        temp['total_acc_group_two_plus'] = temp['group_two_plus_acc_sibs'] + temp['group_two_plus_acc_card']
        temp['total_amt_group_two_plus'] = temp['group_two_plus_w_org_sibs'] + temp['group_two_plus_w_org_card']

        temp['total_acc_group_three_plus'] = temp['group_three_plus_acc_sibs'] + temp['group_three_plus_acc_card']
        temp['total_amt_group_three_plus'] = temp['group_three_plus_w_org_sibs'] + temp['group_three_plus_w_org_card']

        temp['bad_debt_ratio_sibs'] = temp['group_two_plus_w_org_sibs'] / temp['total_amt_sibs'] if temp['total_amt_sibs'] != 0 else 0
        temp['bad_debt_ratio_card'] = temp['group_two_plus_w_org_card'] / temp['total_amt_card'] if temp['total_amt_card'] != 0 else 0
        temp['bad_debt_ratio'] = (temp['group_two_plus_w_org_sibs'] + temp['group_two_plus_w_org_card']) / temp['total_amt'] if temp['total_amt'] != 0 else 0
        temp['created_at'] = todayTimeStamp - 3600 *24
        insertData.append(temp)
    mongodb.batch_insert(MONGO_COLLECTION=collection, insert_data=insertData)
    print('DONE')
except Exception as e:
    # log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
    print(traceback.format_exc())

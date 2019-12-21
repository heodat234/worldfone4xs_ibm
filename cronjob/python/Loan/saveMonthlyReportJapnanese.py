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
collection = common.getSubUser(subUserType, 'Monthly_report_japanese')
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

    last_month_last_day = today.replace(day=1) - timedelta(days=1)
    last_month_day = last_month_last_day.day
    last_month_month = last_month_last_day.month
    last_month_year = last_month_last_day.year
    pprint(last_month_last_day)

    lastDayOfMonth = calendar.monthrange(year, month)[1]
    insertDataTotal = []
    insertDataDetail = []
    todayString = today.strftime("%d/%m/%Y")
    todayTimeStamp = int(time.mktime(time.strptime(str(todayString + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
    startMonth = int(time.mktime(time.strptime(str('01/' + str(month) + '/' + str(year) + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
    endMonth = int(time.mktime(time.strptime(str(str(lastDayOfMonth) + '/' + str(month) + '/' + str(year) + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))
    endMonthEndtime = int(time.mktime(time.strptime(str(str(lastDayOfMonth) + '/' + str(month) + '/' + str(year) + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))
    endMonthStarttime = endMonthEndtime - 86399
    endLastMonthEndtime = int(time.mktime(time.strptime(str(str(lastDayOfMonth) + '/' + str(month) + '/' + str(year) + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))
    endLastMonthEndtime = int(time.mktime(time.strptime(str(str(last_month_day) + '/' + str(last_month_month) + '/' + str(last_month_year) + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))
    endLastMonthStarttime = endLastMonthEndtime - 86399
    holidayOfMonth = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_off_sys'))
    listHoliday = map(lambda offDateRow: {offDateRow['off_date']}, holidayOfMonth)
    total_list_value = ['acc', 'amt']
    total_total_acc = 0
    total_total_amt = 0
    total_g_b_acc = 0
    total_g_b_amt = 0
    total_g_c_acc = 0
    total_g_c_amt = 0
    total_g_d_acc = 0
    total_g_d_amt = 0
    total_g_b_plus_acc = 0
    total_g_b_plus_amt = 0
    total_g_c_plus_acc = 0
    total_g_c_plus_amt = 0

    listDayOfMonth = []
    startDayOfMonth = 1
    while startDayOfMonth <= lastDayOfMonth:
        listDayOfMonth.append(str(format(startDayOfMonth, '02d')) + str(format(month, '02d')) + str(year))
        startDayOfMonth += 1
    
    if todayTimeStamp in listHoliday:
        sys.exit()

    # Check hom nay co phai la ngay cuoi thang
    # if todayTimeStamp > endMonthStarttime or todayTimeStamp < endMonthEndtime:
    #     sys.exit()

    todayString = today.strftime("%d/%m/%Y")
    starttime = int(time.mktime(time.strptime(str(todayString + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
    endtime = int(time.mktime(time.strptime(str(todayString + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))
    
    list_product = mongodb.get(common.getSubUser(subUserType, 'Product'), SORT=[("$name", 1)])
    credit_card_range = list(map(lambda x: str(format(x, '03d')), range(1, 100, 1)))

    for product_code, product_value in enumerate(list_product):
        temp_group = {}
        # ZACCF
        if product_value['code'] not in ['301', '302']:
            aggregate_zaccf = [
                {
                    "$match"                            : {
                        'PRODGRP_ID'                    : product_value['code'],
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
                        "this_month_acc"                : {
                            "$sum"                      : {
                                "$cond"                 : [
                                    {
                                        '$and'          : [
                                            {"$ne"      : ["$W_ORG", '.00']},
                                        ]
                                    },
                                    1,
                                    0
                                ]
                            }
                        },
                        "this_month_amt"                : {
                            "$push"                     : "$W_ORG"
                        },
                        "this_month_g_b_acc"            : {
                            "$sum"                      : {
                                "$cond"                 : [
                                    {
                                        '$and'          : [
                                            {"$ne"      : ["$W_ORG", '.00']},
                                            {"$eq"      : ["$ODIND_FG", "B"]}
                                        ]
                                    },
                                    1,
                                    0
                                ]
                            }
                        },
                        "this_month_g_b_amt"            : {
                            "$push"                     : {
                                "$cond"                 : [
                                    {
                                        '$and'          : [
                                            {"$eq"      : ["$ODIND_FG", "B"]}
                                        ]
                                    },
                                    "$W_ORG",
                                    "0"
                                ]
                            }
                        },
                        "this_month_g_c_acc"            : {
                            "$sum"                      : {
                                "$cond"                 : [
                                    {
                                        '$and'          : [
                                            {"$ne"      : ["$W_ORG", '.00']},
                                            {"$eq"      : ["$ODIND_FG", "C"]}
                                        ]
                                    },
                                    1,
                                    0
                                ]
                            }
                        },
                        "this_month_g_c_amt"            : {
                            "$push"                     : {
                                "$cond"                 : [
                                    {
                                        '$and'          : [
                                            {"$eq"      : ["$ODIND_FG", "C"]}
                                        ]
                                    },
                                    "$W_ORG",
                                    "0"
                                ]
                            }
                        },
                        "this_month_g_d_acc"            : {
                            "$sum"                      : {
                                "$cond"                 : [
                                    {
                                        '$and'          : [
                                            {"$ne"      : ["$W_ORG", '.00']},
                                            {"$eq"      : ["$ODIND_FG", "D"]}
                                        ]
                                    },
                                    1,
                                    0
                                ]
                            }
                        },
                        "this_month_g_d_amt"            : {
                            "$push"                     : {
                                "$cond"                 : [
                                    {
                                        '$and'          : [
                                            {"$eq"      : ["$ODIND_FG", "D"]}
                                        ]
                                    },
                                    "$W_ORG",
                                    "0"
                                ]
                            }
                        },
                        "this_month_g_e_acc"            : {
                            "$sum"                      : {
                                "$cond"                 : [
                                    {
                                        '$and'          : [
                                            {"$ne"      : ["$W_ORG", '.00']},
                                            {"$eq"      : ["$ODIND_FG", "E"]}
                                        ]
                                    },
                                    1,
                                    0
                                ]
                            }
                        },
                        "this_month_g_e_amt"            : {
                            "$push"                     : {
                                "$cond"                 : [
                                    {
                                        '$and'          : [
                                            {"$eq"      : ["$ODIND_FG", "E"]}
                                        ]
                                    },
                                    "$W_ORG",
                                    "0"
                                ]
                            }
                        },
                    }
                }
            ]
            zaccfInfo = list(mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'ZACCF'),aggregate_pipeline=aggregate_zaccf))
            if zaccfInfo not in [None, []] and zaccfInfo[0] is not None:
                temp_total_amt_zaccf = 0
                for total_detail in total_list_value:
                    temp_total = {}
                    temp_total['detail'] = total_detail
                    temp_total['product_code'] = product_value['code']
                    temp_total['product_name'] = product_value['name']
                    temp_total['created_at'] = time.time()
                    temp_total['created_by'] = 'system'
                    temp_total['index'] = '2'
                    if total_detail == 'acc':
                        temp_total['this_month'] = zaccfInfo[0]['this_month_acc']
                        total_total_acc += temp_total['this_month']
                        lastMonthInfoTotal = mongodb.getOne(common.getSubUser(subUserType, 'Collection_factors_total'), WHERE={'created_at': {'$gte': endLastMonthStarttime, '$lte': endLastMonthEndtime}, 'detail': 'acc', 'product_code': product_value['code']})
                        temp_total['last_month'] = lastMonthInfoTotal['this_month'] if lastMonthInfoTotal is not None and lastMonthInfoTotal['this_month'] is not None else 0
                    else:
                        temp_total['this_month'] = sum(map(lambda x: float(x), zaccfInfo[0]['this_month_amt'])) if zaccfInfo[0]['this_month_amt'] is not None else 0
                        total_total_amt += temp_total['this_month']
                        temp_total_amt_zaccf = temp_total['this_month']
                        lastMonthInfoTotal = mongodb.getOne(common.getSubUser(subUserType, 'Collection_factors_total'), WHERE={'created_at': {'$gte': endLastMonthStarttime, '$lte': endLastMonthEndtime}, 'detail': 'amt', 'product_code': product_value['code']})
                        temp_total['last_month'] = lastMonthInfoTotal['this_month'] if lastMonthInfoTotal is not None and lastMonthInfoTotal['this_month'] is not None else 0
                    insertDataTotal.append(temp_total)

                temp_2 = {
                    'index'         : '1b',
                    'type_detail'   : 'group_b',
                    'group_name'    : '',
                    'product_code'  : product_value['code'],
                    'product_name'  : product_value['name'],
                    'this_month_acc': zaccfInfo[0]['this_month_g_b_acc'] if zaccfInfo[0]['this_month_g_b_acc'] is not None else 0,
                    'this_month_amt': sum(map(lambda x: float(x), zaccfInfo[0]['this_month_g_b_amt'])) if zaccfInfo[0]['this_month_g_b_amt'] is not None else 0,
                    'created_at'    : time.time(),
                    'created_by'    : 'system'
                }
                total_g_b_acc += temp_2['this_month_acc']
                total_g_b_amt += temp_2['this_month_amt']
                lastMonthInfoTotal = mongodb.getOne(common.getSubUser(subUserType, 'Collection_factors_detail'), WHERE={'created_at': {'$gte': endLastMonthStarttime, '$lte': endLastMonthEndtime}, 'product_code': product_value['code'], 'type_detail': 'group_b'})
                temp_total['last_month_acc'] = lastMonthInfoTotal['this_month_acc'] if lastMonthInfoTotal is not None and lastMonthInfoTotal['this_month_acc'] is not None else 0
                temp_total['last_month_amt'] = lastMonthInfoTotal['this_month_amt'] if lastMonthInfoTotal is not None and lastMonthInfoTotal['this_month_amt'] is not None else 0
                insertDataDetail.append(temp_2)

                temp_3 = {
                    'index'         : '2b',
                    'type_detail'   : 'group_c',
                    'group_name'    : '',
                    'product_code'  : product_value['code'],
                    'product_name'  : product_value['name'],
                    'this_month_acc': zaccfInfo[0]['this_month_g_c_acc'] if zaccfInfo[0]['this_month_g_c_acc'] is not None else 0,
                    'this_month_amt': sum(map(lambda x: float(x), zaccfInfo[0]['this_month_g_c_amt'])) if zaccfInfo[0]['this_month_g_c_amt'] is not None else 0,
                    'created_at'    : time.time(),
                    'created_by'    : 'system'
                }
                total_g_c_acc += temp_3['this_month_acc']
                total_g_c_amt += temp_3['this_month_amt']
                lastMonthInfoTotal = mongodb.getOne(common.getSubUser(subUserType, 'Collection_factors_detail'), WHERE={'created_at': {'$gte': endLastMonthStarttime, '$lte': endLastMonthEndtime}, 'product_code': product_value['code'], 'type_detail': 'group_c'})
                temp_total['last_month_acc'] = lastMonthInfoTotal['this_month_acc'] if lastMonthInfoTotal is not None and lastMonthInfoTotal['this_month_acc'] is not None else 0
                temp_total['last_month_amt'] = lastMonthInfoTotal['this_month_amt'] if lastMonthInfoTotal is not None and lastMonthInfoTotal['this_month_amt'] is not None else 0
                insertDataDetail.append(temp_3)

                temp_4 = {
                    'index'         : '3b',
                    'type_detail'   : 'group_d',
                    'group_name'    : '',
                    'product_code'  : product_value['code'],
                    'product_name'  : product_value['name'],
                    'this_month_acc': zaccfInfo[0]['this_month_g_d_acc'] if zaccfInfo[0]['this_month_g_d_acc'] is not None else 0,
                    'this_month_amt': sum(map(lambda x: float(x), zaccfInfo[0]['this_month_g_d_amt'])) if zaccfInfo[0]['this_month_g_d_amt'] is not None else 0,
                    'created_at'    : time.time(),
                    'created_by'    : 'system'
                }
                total_g_d_acc += temp_4['this_month_acc']
                total_g_d_amt += temp_4['this_month_amt']
                lastMonthInfoTotal = mongodb.getOne(common.getSubUser(subUserType, 'Collection_factors_detail'), WHERE={'created_at': {'$gte': endLastMonthStarttime, '$lte': endLastMonthEndtime}, 'product_code': product_value['code'], 'type_detail': 'group_d'})
                temp_total['last_month_acc'] = lastMonthInfoTotal['this_month_acc'] if lastMonthInfoTotal is not None and lastMonthInfoTotal['this_month_acc'] is not None else 0
                temp_total['last_month_amt'] = lastMonthInfoTotal['this_month_amt'] if lastMonthInfoTotal is not None and lastMonthInfoTotal['this_month_amt'] is not None else 0
                insertDataDetail.append(temp_4)

                temp_5 = {
                    'index'         : '4b',
                    'type_detail'   : 'group_e',
                    'group_name'    : '',
                    'product_code'  : product_value['code'],
                    'product_name'  : product_value['name'],
                    'this_month_acc': zaccfInfo[0]['this_month_g_e_acc'] if zaccfInfo[0]['this_month_g_e_acc'] is not None else 0,
                    'this_month_amt': sum(map(lambda x: float(x), zaccfInfo[0]['this_month_g_e_amt'])) if zaccfInfo[0]['this_month_g_e_amt'] is not None else 0,
                    'created_at'    : time.time(),
                    'created_by'    : 'system'
                }
                insertDataDetail.append(temp_5)

                pprint(product_code)
                sys.exit()
                if product_code == 0:
                    temp_2_plus = {
                        'index'         : '5b',
                        'type_detail'   : 'group_b_plus',
                        'group_name'    : '(Ｇ２以上）',
                        'product_code'  : product_value['code'],
                        'product_name'  : product_value['name'],
                        'this_month_acc': temp_2['this_month_acc'] + temp_3['this_month_acc'] + temp_4['this_month_acc'],
                        'this_month_amt': temp_2['this_month_amt'] + temp_3['this_month_amt'] + temp_4['this_month_amt'],
                        'created_at'    : time.time(),
                        'created_by'    : 'system'
                    }
                else:
                    temp_2_plus = {
                        'index'         : '5b',
                        'type_detail'   : 'group_b_plus',
                        'group_name'    : '',
                        'product_code'  : product_value['code'],
                        'product_name'  : product_value['name'],
                        'this_month_acc': temp_2['this_month_acc'] + temp_3['this_month_acc'] + temp_4['this_month_acc'],
                        'this_month_amt': temp_2['this_month_amt'] + temp_3['this_month_amt'] + temp_4['this_month_amt'],
                        'created_at'    : time.time(),
                        'created_by'    : 'system'
                    }

                total_g_b_plus_acc += temp_2_plus['this_month_acc']
                total_g_b_plus_amt += temp_2_plus['this_month_amt']
                lastMonthInfoTotal = mongodb.getOne(common.getSubUser(subUserType, 'Collection_factors_detail'), WHERE={'created_at': {'$gte': endLastMonthStarttime, '$lte': endLastMonthEndtime}, 'product_code': product_value['code'], 'type_detail': 'group_b_plus'})
                temp_total['last_month_acc'] = lastMonthInfoTotal['this_month_acc'] if lastMonthInfoTotal is not None and lastMonthInfoTotal['this_month_acc'] is not None else 0
                temp_total['last_month_amt'] = lastMonthInfoTotal['this_month_amt'] if lastMonthInfoTotal is not None and lastMonthInfoTotal['this_month_amt'] is not None else 0
                insertDataDetail.append(temp_2_plus)

                if product_code == 0:
                    temp_2_ratio = {
                        'index'         : '6b',
                        'type_detail'   : 'group_b_plus_ratio',
                        'group_name'    : '(Ｇ２以上）',
                        'product_code'  : product_value['code'],
                        'product_name'  : product_value['name'],
                        'this_month_amt': temp_2_plus['this_month_amt'] / temp_total_amt_zaccf if temp_total_amt_zaccf != 0 else 0,
                        'created_at'    : time.time(),
                        'created_by'    : 'system'
                    }
                else:
                    temp_2_ratio = {
                        'index'         : '6b',
                        'type_detail'   : 'group_b_plus_ratio',
                        'group_name'    : '',
                        'product_code'  : product_value['code'],
                        'product_name'  : product_value['name'],
                        'this_month_amt': temp_2_plus['this_month_amt'] / temp_total_amt_zaccf if temp_total_amt_zaccf != 0 else 0,
                        'created_at'    : time.time(),
                        'created_by'    : 'system'
                    }

                lastMonthInfoTotal = mongodb.getOne(common.getSubUser(subUserType, 'Collection_factors_detail'), WHERE={'created_at': {'$gte': endLastMonthStarttime, '$lte': endLastMonthEndtime}, 'product_code': product_value['code'], 'type_detail': 'group_b_plus_ratio'})
                temp_total['last_month_acc'] = lastMonthInfoTotal['this_month_acc'] if lastMonthInfoTotal is not None and lastMonthInfoTotal['this_month_acc'] is not None else 0
                temp_total['last_month_amt'] = lastMonthInfoTotal['this_month_amt'] if lastMonthInfoTotal is not None and lastMonthInfoTotal['this_month_amt'] is not None else 0
                insertDataDetail.append(temp_2_ratio)

                temp_3_plus = {
                    'index'         : '7b',
                    'type_detail'   : 'group_c_plus',
                    'group_name'    : '',
                    'product_code'  : product_value['code'],
                    'product_name'  : product_value['name'],
                    'this_month_acc': temp_3['this_month_acc'] + temp_4['this_month_acc'],
                    'this_month_amt': temp_3['this_month_amt'] + temp_4['this_month_amt'],
                    'created_at'    : time.time(),
                    'created_by'    : 'system'
                }
                total_g_c_plus_acc += temp_3_plus['this_month_acc']
                total_g_c_plus_amt += temp_3_plus['this_month_amt']
                lastMonthInfoTotal = mongodb.getOne(common.getSubUser(subUserType, 'Collection_factors_detail'), WHERE={'created_at': {'$gte': endLastMonthStarttime, '$lte': endLastMonthEndtime}, 'product_code': product_value['code'], 'type_detail': 'group_c_plus'})
                temp_total['last_month_acc'] = lastMonthInfoTotal['this_month_acc'] if lastMonthInfoTotal is not None and lastMonthInfoTotal['this_month_acc'] is not None else 0
                temp_total['last_month_amt'] = lastMonthInfoTotal['this_month_amt'] if lastMonthInfoTotal is not None and lastMonthInfoTotal['this_month_amt'] is not None else 0
                insertDataDetail.append(temp_3_plus)

                if product_code == 0:
                    temp_3_ratio = {
                        'index'         : '9b',
                        'type_detail'   : 'group_c_plus_ratio',
                        'group_name'    : '(Ｇ２以上）',
                        'product_code'  : product_value['code'],
                        'product_name'  : product_value['name'],
                        'this_month_amt': temp_3_plus['this_month_amt'] / temp_total_amt_zaccf if temp_total_amt_zaccf != 0 else 0,
                        'created_at'    : time.time(),
                        'created_by'    : 'system'
                    }
                else:
                    temp_3_ratio = {
                        'index'         : '9b',
                        'type_detail'   : 'group_c_plus_ratio',
                        'group_name'    : '',
                        'product_code'  : product_value['code'],
                        'product_name'  : product_value['name'],
                        'this_month_amt': temp_3_plus['this_month_amt'] / temp_total_amt_zaccf if temp_total_amt_zaccf != 0 else 0,
                        'created_at'    : time.time(),
                        'created_by'    : 'system'
                    }
                lastMonthInfoTotal = mongodb.getOne(common.getSubUser(subUserType, 'Collection_factors_detail'), WHERE={'created_at': {'$gte': endLastMonthStarttime, '$lte': endLastMonthEndtime}, 'product_code': product_value['code'], 'type_detail': 'group_c_plus_ratio'})
                temp_total['last_month_acc'] = lastMonthInfoTotal['this_month_acc'] if lastMonthInfoTotal is not None and lastMonthInfoTotal['this_month_acc'] is not None else 0
                temp_total['last_month_amt'] = lastMonthInfoTotal['this_month_amt'] if lastMonthInfoTotal is not None and lastMonthInfoTotal['this_month_amt'] is not None else 0
                insertDataDetail.append(temp_3_ratio)
        else:
            # SBV
            aggregate_sbv = []
            if product_value['code'] == '301':
                aggregate_sbv.append({
                    '$match'                        : {
                        'card_type'                 : {
                            '$in'                   : credit_card_range
                        }
                    }
                })
            else:
                aggregate_sbv.append({
                    '$match'                        : {
                        'card_type'                 : {
                            '$nin'                  : credit_card_range
                        }
                    }
                })
            aggregate_sbv.append(
                {
                    '$group'                        : {
                        '_id'                       : None,
                        "this_month_acc"            : {
                            "$sum"                  : {
                                "$cond"             : [
                                    {
                                        '$and'      : [
                                            {"$gt"  : [{'$add': ['$ob_principal_sale', '$ob_principal_cash']}, 0]},
                                        ]
                                    },
                                    1,
                                    0
                                ]
                            }
                        },
                        'this_month_amt'            : {
                            '$sum'                  : {'$add': ['$ob_principal_sale', '$ob_principal_cash']}
                        },
                        "this_month_g_b_acc"            : {
                            "$sum"                      : {
                                "$cond"                 : [
                                    {
                                        '$and'          : [
                                            {"$gt"      : [{'$add': ['$ob_principal_sale', '$ob_principal_cash']}, 0]},
                                            {"$eq"      : ["$overdue_indicator", "B"]}
                                        ]
                                    },
                                    1,
                                    0
                                ]
                            }
                        },
                        "this_month_g_b_amt"            : {
                            "$push"                     : {
                                "$cond"                 : [
                                    {
                                        '$and'          : [
                                            {"$gt"      : [{'$add': ['$ob_principal_sale', '$ob_principal_cash']}, 0]},
                                            {"$eq"      : ["$overdue_indicator", "B"]}
                                        ]
                                    },
                                    {'$add'             : ['$ob_principal_sale', '$ob_principal_cash']},
                                    0
                                ]
                            }
                        },
                        "this_month_g_c_acc"            : {
                            "$sum"                      : {
                                "$cond"                 : [
                                    {
                                        '$and'          : [
                                            {"$gt"      : [{'$add': ['$ob_principal_sale', '$ob_principal_cash']}, 0]},
                                            {"$eq"      : ["$overdue_indicator", "C"]}
                                        ]
                                    },
                                    1,
                                    0
                                ]
                            }
                        },
                        "this_month_g_c_amt"            : {
                            "$push"                     : {
                                "$cond"                 : [
                                    {
                                        '$and'          : [
                                            {"$gt"      : [{'$add': ['$ob_principal_sale', '$ob_principal_cash']}, 0]},
                                            {"$eq"      : ["$overdue_indicator", "C"]}
                                        ]
                                    },
                                    {'$add'             : ['$ob_principal_sale', '$ob_principal_cash']},
                                    0
                                ]
                            }
                        },
                        "this_month_g_d_acc"            : {
                            "$sum"                      : {
                                "$cond"                 : [
                                    {
                                        '$and'          : [
                                            {"$gt"      : [{'$add': ['$ob_principal_sale', '$ob_principal_cash']}, 0]},
                                            {"$eq"      : ["$overdue_indicator", "D"]}
                                        ]
                                    },
                                    1,
                                    0
                                ]
                            }
                        },
                        "this_month_g_d_amt"            : {
                            "$push"                     : {
                                "$cond"                 : [
                                    {
                                        '$and'          : [
                                            {"$gt"      : [{'$add': ['$ob_principal_sale', '$ob_principal_cash']}, 0]},
                                            {"$eq"      : ["$overdue_indicator", "D"]}
                                        ]
                                    },
                                    {'$add'             : ['$ob_principal_sale', '$ob_principal_cash']},
                                    0
                                ]
                            }
                        },
                        "this_month_g_e_acc"            : {
                            "$sum"                      : {
                                "$cond"                 : [
                                    {
                                        '$and'          : [
                                            {"$gt"      : [{'$add': ['$ob_principal_sale', '$ob_principal_cash']}, 0]},
                                            {"$eq"      : ["$overdue_indicator", "E"]}
                                        ]
                                    },
                                    1,
                                    0
                                ]
                            }
                        },
                        "this_month_g_e_amt"            : {
                            "$push"                     : {
                                "$cond"                 : [
                                    {
                                        '$and'          : [
                                            {"$gt"      : [{'$add': ['$ob_principal_sale', '$ob_principal_cash']}, 0]},
                                            {"$eq"      : ["$overdue_indicator", "E"]}
                                        ]
                                    },
                                    {'$add'             : ['$ob_principal_sale', '$ob_principal_cash']},
                                    0
                                ]
                            }
                        },
                    },
                }
            )
            sbvInfo = list(mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'SBV'),aggregate_pipeline=aggregate_sbv))
            # pprint(product_value['code'])
            # pprint(list(sbvInfo))
            if sbvInfo not in [None, []] and sbvInfo[0] is not None:
                temp_total_amt_sbv = 0
                for total_detail in total_list_value:
                    temp_total = {}
                    temp_total['detail'] = total_detail
                    temp_total['product_code'] = product_value['code']
                    temp_total['product_name'] = product_value['name']
                    temp_total['created_at'] = time.time()
                    temp_total['created_by'] = 'system'
                    temp_total['index'] = '2'
                    if total_detail == 'acc':
                        temp_total['this_month'] = sbvInfo[0]['this_month_acc']
                        lastMonthInfoTotal = mongodb.getOne(common.getSubUser(subUserType, 'Collection_factors_total'), WHERE={'created_at': {'$gte': endLastMonthStarttime, '$lte': endLastMonthEndtime}, 'detail': 'acc', 'product_code': product_value['code']})
                        temp_total['last_month'] = lastMonthInfoTotal['this_month'] if lastMonthInfoTotal is not None and lastMonthInfoTotal['this_month'] is not None else 0
                        total_total_acc += temp_total['this_month']
                    else:
                        temp_total['this_month'] = sbvInfo[0]['this_month_amt']
                        lastMonthInfoTotal = mongodb.getOne(common.getSubUser(subUserType, 'Collection_factors_total'), WHERE={'created_at': {'$gte': endLastMonthStarttime, '$lte': endLastMonthEndtime}, 'detail': 'amt', 'product_code': product_value['code']})
                        temp_total['last_month'] = lastMonthInfoTotal['this_month'] if lastMonthInfoTotal is not None and lastMonthInfoTotal['this_month'] is not None else 0
                        total_total_amt += temp_total['this_month']
                        temp_total_amt_sbv = temp_total['this_month']
                    insertDataTotal.append(temp_total)

                temp_2 = {
                    'index'         : '1b',
                    'type_detail'   : 'group_b',
                    'group_name'    : '',
                    'product_code'  : product_value['code'],
                    'product_name'  : product_value['name'],
                    'this_month_acc': sbvInfo[0]['this_month_g_b_acc'] if sbvInfo[0]['this_month_g_b_acc'] is not None else 0,
                    'this_month_amt': sum(map(lambda x: float(x), sbvInfo[0]['this_month_g_b_amt'])) if sbvInfo[0]['this_month_g_b_amt'] is not None else 0,
                    'created_at'    : time.time(),
                    'created_by'    : 'system'
                }
                total_g_b_acc += temp_2['this_month_acc']
                total_g_b_amt += temp_2['this_month_amt']
                lastMonthInfoTotal = mongodb.getOne(common.getSubUser(subUserType, 'Collection_factors_detail'), WHERE={'created_at': {'$gte': endLastMonthStarttime, '$lte': endLastMonthEndtime}, 'product_code': product_value['code'], 'type_detail': 'group_b'})
                temp_total['last_month_acc'] = lastMonthInfoTotal['this_month_acc'] if lastMonthInfoTotal is not None and lastMonthInfoTotal['this_month_acc'] is not None else 0
                temp_total['last_month_amt'] = lastMonthInfoTotal['this_month_amt'] if lastMonthInfoTotal is not None and lastMonthInfoTotal['this_month_amt'] is not None else 0
                insertDataDetail.append(temp_2)

                temp_3 = {
                    'index'         : '2b',
                    'type_detail'   : 'group_c',
                    'group_name'    : '',
                    'product_code'  : product_value['code'],
                    'product_name'  : product_value['name'],
                    'this_month_acc': sbvInfo[0]['this_month_g_c_acc'] if sbvInfo[0]['this_month_g_c_acc'] is not None else 0,
                    'this_month_amt': sum(map(lambda x: float(x), sbvInfo[0]['this_month_g_c_amt'])) if sbvInfo[0]['this_month_g_c_amt'] is not None else 0,
                    'created_at'    : time.time(),
                    'created_by'    : 'system'
                }
                total_g_c_acc += temp_3['this_month_acc']
                total_g_c_amt += temp_3['this_month_amt']
                lastMonthInfoTotal = mongodb.getOne(common.getSubUser(subUserType, 'Collection_factors_detail'), WHERE={'created_at': {'$gte': endLastMonthStarttime, '$lte': endLastMonthEndtime}, 'product_code': product_value['code'], 'type_detail': 'group_c'})
                temp_total['last_month_acc'] = lastMonthInfoTotal['this_month_acc'] if lastMonthInfoTotal is not None and lastMonthInfoTotal['this_month_acc'] is not None else 0
                temp_total['last_month_amt'] = lastMonthInfoTotal['this_month_amt'] if lastMonthInfoTotal is not None and lastMonthInfoTotal['this_month_amt'] is not None else 0
                insertDataDetail.append(temp_3)

                temp_4 = {
                    'index'         : '3b',
                    'type_detail'   : 'group_d',
                    'group_name'    : '',
                    'product_code'  : product_value['code'],
                    'product_name'  : product_value['name'],
                    'this_month_acc': sbvInfo[0]['this_month_g_d_acc'] if sbvInfo[0]['this_month_g_d_acc'] is not None else 0,
                    'this_month_amt': sum(map(lambda x: float(x), sbvInfo[0]['this_month_g_d_amt'])) if sbvInfo[0]['this_month_g_d_amt'] is not None else 0,
                    'created_at'    : time.time(),
                    'created_by'    : 'system'
                }
                total_g_d_acc += temp_4['this_month_acc']
                total_g_d_amt += temp_4['this_month_amt']
                lastMonthInfoTotal = mongodb.getOne(common.getSubUser(subUserType, 'Collection_factors_detail'), WHERE={'created_at': {'$gte': endLastMonthStarttime, '$lte': endLastMonthEndtime}, 'product_code': product_value['code'], 'type_detail': 'group_d'})
                temp_total['last_month_acc'] = lastMonthInfoTotal['this_month_acc'] if lastMonthInfoTotal is not None and lastMonthInfoTotal['this_month_acc'] is not None else 0
                temp_total['last_month_amt'] = lastMonthInfoTotal['this_month_amt'] if lastMonthInfoTotal is not None and lastMonthInfoTotal['this_month_amt'] is not None else 0
                insertDataDetail.append(temp_4)

                temp_5 = {
                    'index'         : '4b',
                    'type_detail'   : 'group_e',
                    'group_name'    : '',
                    'product_code'  : product_value['code'],
                    'product_name'  : product_value['name'],
                    'this_month_acc': sbvInfo[0]['this_month_g_e_acc'] if sbvInfo[0]['this_month_g_e_acc'] is not None else 0,
                    'this_month_amt': sum(map(lambda x: float(x), sbvInfo[0]['this_month_g_e_amt'])) if sbvInfo[0]['this_month_g_e_amt'] is not None else 0,
                    'created_at'    : time.time(),
                    'created_by'    : 'system'
                }
                insertDataDetail.append(temp_5)

                if product_code == 0:
                    temp_2_plus = {
                        'index'         : '5b',
                        'type_detail'   : 'group_b_plus',
                        'group_name'    : '(Ｇ２以上）',
                        'product_code'  : product_value['code'],
                        'product_name'  : product_value['name'],
                        'this_month_acc': temp_2['this_month_acc'] + temp_3['this_month_acc'] + temp_4['this_month_acc'],
                        'this_month_amt': temp_2['this_month_amt'] + temp_3['this_month_amt'] + temp_4['this_month_amt'],
                        'created_at'    : time.time(),
                        'created_by'    : 'system'
                    }
                else:
                    temp_2_plus = {
                        'index'         : '5b',
                        'type_detail'   : 'group_b_plus',
                        'group_name'    : '',
                        'product_code'  : product_value['code'],
                        'product_name'  : product_value['name'],
                        'this_month_acc': temp_2['this_month_acc'] + temp_3['this_month_acc'] + temp_4['this_month_acc'],
                        'this_month_amt': temp_2['this_month_amt'] + temp_3['this_month_amt'] + temp_4['this_month_amt'],
                        'created_at'    : time.time(),
                        'created_by'    : 'system'
                    }

                total_g_b_plus_acc += temp_2_plus['this_month_acc']
                total_g_b_plus_amt += temp_2_plus['this_month_amt']
                lastMonthInfoTotal = mongodb.getOne(common.getSubUser(subUserType, 'Collection_factors_detail'), WHERE={'created_at': {'$gte': endLastMonthStarttime, '$lte': endLastMonthEndtime}, 'product_code': product_value['code'], 'type_detail': 'group_b_plus'})
                temp_total['last_month_acc'] = lastMonthInfoTotal['this_month_acc'] if lastMonthInfoTotal is not None and lastMonthInfoTotal['this_month_acc'] is not None else 0
                temp_total['last_month_amt'] = lastMonthInfoTotal['this_month_amt'] if lastMonthInfoTotal is not None and lastMonthInfoTotal['this_month_amt'] is not None else 0
                insertDataDetail.append(temp_2_plus)

                if product_code == 0:
                    temp_2_ratio = {
                        'index'         : '6b',
                        'type_detail'   : 'group_b_plus_ratio',
                        'group_name'    : '(Ｇ２以上）',
                        'product_code'  : product_value['code'],
                        'product_name'  : product_value['name'],
                        'this_month_amt': temp_2_plus['this_month_amt'] / temp_total_amt_sbv if temp_total_amt_sbv != 0 else 0,
                        'created_at'    : time.time(),
                        'created_by'    : 'system'
                    }
                else:
                    temp_2_ratio = {
                        'index'         : '6b',
                        'type_detail'   : 'group_b_plus_ratio',
                        'group_name'    : '',
                        'product_code'  : product_value['code'],
                        'product_name'  : product_value['name'],
                        'this_month_amt': temp_2_plus['this_month_amt'] / temp_total_amt_sbv if temp_total_amt_sbv != 0 else 0,
                        'created_at'    : time.time(),
                        'created_by'    : 'system'
                    }
                lastMonthInfoTotal = mongodb.getOne(common.getSubUser(subUserType, 'Collection_factors_detail'), WHERE={'created_at': {'$gte': endLastMonthStarttime, '$lte': endLastMonthEndtime}, 'product_code': product_value['code'], 'type_detail': 'group_b_plus_ratio'})
                temp_total['last_month_acc'] = lastMonthInfoTotal['this_month_acc'] if lastMonthInfoTotal is not None and lastMonthInfoTotal['this_month_acc'] is not None else 0
                temp_total['last_month_amt'] = lastMonthInfoTotal['this_month_amt'] if lastMonthInfoTotal is not None and lastMonthInfoTotal['this_month_amt'] is not None else 0
                insertDataDetail.append(temp_2_ratio)

                temp_3_plus = {
                    'index'         : '7b',
                    'type_detail'   : 'group_c_plus',
                    'group_name'    : '',
                    'product_code'  : product_value['code'],
                    'product_name'  : product_value['name'],
                    'this_month_acc': temp_3['this_month_acc'] + temp_4['this_month_acc'],
                    'this_month_amt': temp_3['this_month_amt'] + temp_4['this_month_amt'],
                    'created_at'    : time.time(),
                    'created_by'    : 'system'
                }
                total_g_c_plus_acc += temp_3_plus['this_month_acc']
                total_g_c_plus_amt += temp_3_plus['this_month_amt']
                lastMonthInfoTotal = mongodb.getOne(common.getSubUser(subUserType, 'Collection_factors_detail'), WHERE={'created_at': {'$gte': endLastMonthStarttime, '$lte': endLastMonthEndtime}, 'product_code': product_value['code'], 'type_detail': 'group_c_plus'})
                temp_total['last_month_acc'] = lastMonthInfoTotal['this_month_acc'] if lastMonthInfoTotal is not None and lastMonthInfoTotal['this_month_acc'] is not None else 0
                temp_total['last_month_amt'] = lastMonthInfoTotal['this_month_amt'] if lastMonthInfoTotal is not None and lastMonthInfoTotal['this_month_amt'] is not None else 0
                insertDataDetail.append(temp_3_plus)

                if product_code == 0:
                    temp_3_ratio = {
                        'index'         : '9b',
                        'type_detail'   : 'group_c_plus_ratio',
                        'group_name'    : '(Ｇ２以上）',
                        'product_code'  : product_value['code'],
                        'product_name'  : product_value['name'],
                        'this_month_amt': temp_3_plus['this_month_amt'] / temp_total_amt_sbv if temp_total_amt_sbv != 0 else 0,
                        'created_at'    : time.time(),
                        'created_by'    : 'system',
                    }
                else:
                    temp_3_ratio = {
                        'index'         : '9b',
                        'type_detail'   : 'group_c_plus_ratio',
                        'group_name'    : '',
                        'product_code'  : product_value['code'],
                        'product_name'  : product_value['name'],
                        'this_month_amt': temp_3_plus['this_month_amt'] / temp_total_amt_sbv if temp_total_amt_sbv != 0 else 0,
                        'created_at'    : time.time(),
                        'created_by'    : 'system',
                    }
                lastMonthInfoTotal = mongodb.getOne(common.getSubUser(subUserType, 'Collection_factors_detail'), WHERE={'created_at': {'$gte': endLastMonthStarttime, '$lte': endLastMonthEndtime}, 'product_code': product_value['code'], 'type_detail': 'group_c_plus_ratio'})
                temp_total['last_month_acc'] = lastMonthInfoTotal['this_month_acc'] if lastMonthInfoTotal is not None and lastMonthInfoTotal['this_month_acc'] is not None else 0
                temp_total['last_month_amt'] = lastMonthInfoTotal['this_month_amt'] if lastMonthInfoTotal is not None and lastMonthInfoTotal['this_month_amt'] is not None else 0
                insertDataDetail.append(temp_3_ratio)
    
    insertDataTotal.append({
        'index'         : '1',
        'type_detail'   : 'total_total_acc',
        'detail'        : 'acc',
        'detail_name'   : '会員数 (AC number)',
        'product_code'  : 'total',
        'product_name'  : '合計',
        'this_month'    : total_total_acc,
        'created_at'    : time.time(),
        'created_by'    : 'system'
    })

    insertDataTotal.append({
        'index'         : '1',
        'type_detail'   : 'total_total_amt',
        'detail'        : 'amt',
        'detail_name'   : '債権残高 (O/S principal)',
        'product_code'  : 'total',
        'product_name'  : '合計',
        'this_month'    : total_total_amt,
        'created_at'    : time.time(),
        'created_by'    : 'system'
    })

    insertDataDetail.append({
        'index'         : '1a',
        'type_detail'   : 'total_group_b',
        'group_name'    : 'Group 2 (10-90)',
        'product_code'  : '',
        'product_name'  : '合計',
        'this_month_acc': total_g_b_acc,
        'this_month_amt': total_g_b_amt,
        'created_at'    : time.time(),
        'created_by'    : 'system'
    })

    insertDataDetail.append({
        'index'         : '2a',
        'type_detail'   : 'total_group_c',
        'group_name'    : 'Group 3 (91-180)',
        'product_code'  : '',
        'product_name'  : '合計',
        'this_month_acc': total_g_c_acc,
        'this_month_amt': total_g_c_amt,
        'created_at'    : time.time(),
        'created_by'    : 'system'
    })

    insertDataDetail.append({
        'index'         : '3a',
        'type_detail'   : 'total_group_d',
        'group_name'    : 'Group 4 (181-360)',
        'product_code'  : '',
        'product_name'  : '合計',
        'this_month_acc': total_g_d_acc,
        'this_month_amt': total_g_d_amt,
        'created_at'    : time.time(),
        'created_by'    : 'system'
    })

    insertDataDetail.append({
        'index'         : '5a',
        'type_detail'   : 'total_group_b_plus',
        'group_name'    : 'Group 2 ～',
        'product_code'  : '',
        'product_name'  : '',
        'this_month_acc': total_g_b_plus_acc,
        'this_month_amt': total_g_b_plus_amt,
        'created_at'    : time.time(),
        'created_by'    : 'system'
    })

    insertDataDetail.append({
        'index'         : '6a',
        'type_detail'   : 'total_group_b_plus_ratio',
        'group_name'    : 'G２以上　不良債権率 (Ratio)',
        'product_code'  : '',
        'product_name'  : '',
        'this_month_acc': 0,
        'this_month_amt': total_g_b_plus_amt / total_total_amt if total_total_amt != 0 else 0,
        'created_at'    : time.time(),
        'created_by'    : 'system'
    })

    insertDataDetail.append({
        'index'         : '7a',
        'type_detail'   : 'total_group_c_plus',
        'group_name'    : 'Group 3 ～',
        'product_code'  : '',
        'product_name'  : '',
        'this_month_acc': total_g_c_plus_acc,
        'this_month_amt': total_g_c_plus_amt,
        'created_at'    : time.time(),
        'created_by'    : 'system'
    })

    lastMonthInfoTotal = mongodb.getOne(common.getSubUser(subUserType, 'Collection_factors_detail'), WHERE={'created_at': {'$gte': endLastMonthStarttime, '$lte': endLastMonthEndtime}, 'type_detail': 'total_inc_dec'})

    insertDataDetail.append({
        'index'         : '8a',
        'type_detail'   : 'total_inc_dec',
        'group_name'    : '増減 (Increase and Decrease)',
        'product_code'  : '',
        'product_name'  : '',
        'this_month_acc': total_g_c_plus_acc - lastMonthInfoTotal['this_month_acc'] if lastMonthInfoTotal is not None and lastMonthInfoTotal['this_month_acc'] is not None else 0,
        'this_month_amt': total_g_c_plus_amt - lastMonthInfoTotal['this_month_amt'] if lastMonthInfoTotal is not None and lastMonthInfoTotal['this_month_amt'] is not None else 0,
        'created_at'    : time.time(),
        'created_by'    : 'system'
    })

    insertDataDetail.append({
        'index'         : '9a',
        'type_detail'   : 'total_group_c_plus_ratio',
        'group_name'    : 'G3以上　不良債権率',
        'product_code'  : '',
        'product_name'  : '全部門',
        'this_month_acc': 0,
        'this_month_amt': total_g_c_plus_amt / total_total_amt if total_total_amt != 0 else 0,
        'created_at'    : time.time(),
        'created_by'    : 'system'
    })
    pprint(insertDataDetail)
    mongodb.batch_insert(MONGO_COLLECTION=common.getSubUser(subUserType, 'Collection_factors_total'), insert_data=insertDataTotal)
    mongodb.batch_insert(MONGO_COLLECTION=common.getSubUser(subUserType, 'Collection_factors_detail'), insert_data=insertDataDetail)
    print('DONE')
except Exception as e:
    log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
    print(traceback.format_exc())

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
# log = open(base_url + "cronjob/python/Loan/log/importSBV.txt","a")
now = datetime.now()
subUserType = 'LO'
collection = common.getSubUser(subUserType, 'Monthly_delinquent_occurence_transition')
collection_total = common.getSubUser(subUserType, 'Monthly_delinquent_occurence_transition_total')

try:
    today = date.today()
    # today = datetime.strptime('01/03/2020', "%d/%m/%Y").date()
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

    # Check hom nay co phai la ngay cuoi thang
    if day != 1 :
        pprint('khong phai ngay 1')
        sys.exit()

    report_month = lastMonthMonth
    report_year = lastMonthYear
    
    products = list(mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Product_group'), SORT=[("group_code", 1)]))

    total = {
        'group'         : 'total',
        'int_rate'      : 'total',
        'int_rate_name' : 'JIVF  TOTAL',
        'year'          : year,
    }
    total['total_w_org_' + str(report_month) + '_' + str(report_year)]               = 0
    total['total_acc_count_' + str(report_month) + '_' + str(report_year)]           = 0
    total['group_2_w_org_' + str(report_month) + '_' + str(report_year)]             = 0
    total['group_2_acc_count_' + str(report_month) + '_' + str(report_year)]         = 0
    total['group_2_overdue_ratio_' + str(report_month) + '_' + str(report_year)]     = 0
    total['group_3_over_w_org_' + str(report_month) + '_' + str(report_year)]        = 0
    total['group_3_over_acc_count_' + str(report_month) + '_' + str(report_year)]    = 0
    total['group_3_over_overdue_ratio_' + str(report_month) + '_' + str(report_year)]= 0
    
    for product in products:
        # pprint(product['code'])
        if product['group_code'] not in ['300']:
            list_product_code = list(common.array_column(product['product_code'], 'code'))
            temp_total = {
                'group_code'                        : product['group_code'],
                'int_rate'                          : product['group_code'] + ' Total',
                'int_rate_name'                     : product['group_name'] + ' Total',
                'year'                              : year,
            }
            temp_total['total_w_org_' + str(report_month) + '_' + str(report_year)]               = 0
            temp_total['total_acc_count_' + str(report_month) + '_' + str(report_year)]           = 0
            temp_total['group_2_w_org_' + str(report_month) + '_' + str(report_year)]             = 0
            temp_total['group_2_acc_count_' + str(report_month) + '_' + str(report_year)]         = 0
            temp_total['group_2_overdue_ratio_' + str(report_month) + '_' + str(report_year)]     = 0
            temp_total['group_3_over_w_org_' + str(report_month) + '_' + str(report_year)]        = 0
            temp_total['group_3_over_acc_count_' + str(report_month) + '_' + str(report_year)]    = 0
            temp_total['group_3_over_overdue_ratio_' + str(report_month) + '_' + str(report_year)]= 0
            zaccf_pipeline = [{
                '$match'                            : {
                    'PRODGRP_ID'                    : {
                        '$in'                       : list_product_code
                    },
                }
            },
            {
                '$group'                            : {
                    '_id'                           : '$INT_RATE',
                    'total_w_org'                   : {
                        '$push'                     : '$W_ORG'
                    },
                    'total_acc_count'               : {
                        '$sum'                      : {
                            '$cond'                 : [
                                {
                                    '$and'          : [
                                        {
                                            '$ne'   : ['$W_ORG', '.00']},
                                    ]
                                },
                                1,
                                0
                            ]
                        }
                    },
                    'group_2_w_org'                 : {
                        '$push'                     : {
                            '$cond'                 : [
                                {
                                    '$and'          : [
                                        {
                                            '$eq'   : ['$ODIND_FG', 'B']
                                        }
                                    ]
                                },
                                '$W_ORG',
                                '0'
                            ]
                        }
                    },
                    'group_2_acc_count'             : {
                        '$sum'                      : {
                            '$cond'                 : [
                                {
                                    '$and'          : [
                                        {
                                            '$ne'   : ['$W_ORG', '.00']
                                        },
                                        {
                                            '$eq'   : ['$ODIND_FG', 'B']
                                        }
                                    ]
                                },
                                1,
                                0
                            ]
                        }
                    },
                    'group_3_over_w_org'            : {
                        '$push'                     : {
                            '$cond'                 : [
                                {
                                    '$and'          : [
                                        {
                                            '$in'   : ['$ODIND_FG', ['C', 'D', 'E']]
                                        }
                                    ]
                                },
                                '$W_ORG',
                                '0'
                            ]
                        }
                    },
                    'group_3_over_acc_count'        : {
                        '$sum'                      : {
                            '$cond'                 : [
                                {
                                    '$and'          : [
                                        {
                                            '$ne'   : ['$W_ORG', '.00']
                                        },
                                        {
                                            '$in'   : ['$ODIND_FG', ['C', 'D', 'E']]
                                        }
                                    ]
                                },
                                1,
                                0
                            ]
                        }
                    }
                }
            }]
            zaccfInfo = zaccfInfo = list(mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'ZACCF_report'),aggregate_pipeline=zaccf_pipeline))
            if zaccfInfo != None:
                for zaccf in zaccfInfo:
                    temp_detail = {
                        'group_code'    : product['group_code'],
                        'int_rate'      : zaccf['_id'],
                        'int_rate_name' : float(zaccf['_id']),
                        'year'          : year,
                    }
                    temp_detail['total_w_org_' + str(report_month) + '_' + str(report_year)]              = sum(map(lambda x: float(x), zaccf['total_w_org'] if 'total_w_org' in zaccf.keys() else 0))
                    temp_detail['total_acc_count_' + str(report_month) + '_' + str(report_year)]          = zaccf['total_acc_count'] if 'total_acc_count' in zaccf.keys() else 0
                    temp_detail['group_2_w_org_' + str(report_month) + '_' + str(report_year)]            = sum(map(lambda x: float(x), zaccf['group_2_w_org'] if 'group_2_w_org' in zaccf.keys() else 0))
                    temp_detail['group_2_acc_count_' + str(report_month) + '_' + str(report_year)]        = zaccf['group_2_acc_count'] if 'group_2_acc_count' in zaccf.keys() else 0
                    temp_detail['group_3_over_w_org_' + str(report_month) + '_' + str(report_year)]       = sum(map(lambda x: float(x), zaccf['group_3_over_w_org'] if 'group_3_over_w_org' in zaccf.keys() else 0))
                    temp_detail['group_3_over_acc_count_' + str(report_month) + '_' + str(report_year)]   = zaccf['group_3_over_acc_count'] if 'group_3_over_acc_count' in zaccf.keys() else 0
                    # pprint(temp_detail)
                    mongodb.update(MONGO_COLLECTION=collection, WHERE={'group_code': product['group_code'], 'int_rate': zaccf['_id'], 'year': year}, VALUE=temp_detail)
                    temp_total['total_w_org_' + str(report_month) + '_' + str(report_year)]               += temp_detail['total_w_org_' + str(report_month) + '_' + str(report_year)]
                    temp_total['total_acc_count_' + str(report_month) + '_' + str(report_year)]           += temp_detail['total_acc_count_' + str(report_month) + '_' + str(report_year)]
                    temp_total['group_2_w_org_' + str(report_month) + '_' + str(report_year)]             += temp_detail['group_2_w_org_' + str(report_month) + '_' + str(report_year)]
                    temp_total['group_2_acc_count_' + str(report_month) + '_' + str(report_year)]         += temp_detail['group_2_acc_count_' + str(report_month) + '_' + str(report_year)]
                    temp_total['group_3_over_w_org_' + str(report_month) + '_' + str(report_year)]        += temp_detail['group_3_over_w_org_' + str(report_month) + '_' + str(report_year)]
                    temp_total['group_3_over_acc_count_' + str(report_month) + '_' + str(report_year)]    += temp_detail['group_3_over_acc_count_' + str(report_month) + '_' + str(report_year)]
            mongodb.update(MONGO_COLLECTION=collection, WHERE={'group_code': product['group_code'], 'int_rate': product['group_code'] + ' Total', 'year': year}, VALUE=temp_total)
            total['total_w_org_' + str(report_month) + '_' + str(report_year)]                += temp_total['total_w_org_' + str(report_month) + '_' + str(report_year)]
            total['total_acc_count_' + str(report_month) + '_' + str(report_year)]            += temp_total['total_acc_count_' + str(report_month) + '_' + str(report_year)]
            total['group_2_w_org_' + str(report_month) + '_' + str(report_year)]              += temp_total['group_2_w_org_' + str(report_month) + '_' + str(report_year)]
            total['group_2_acc_count_' + str(report_month) + '_' + str(report_year)]          += temp_total['group_2_acc_count_' + str(report_month) + '_' + str(report_year)]
            total['group_3_over_w_org_' + str(report_month) + '_' + str(report_year)]         += temp_total['group_3_over_w_org_' + str(report_month) + '_' + str(report_year)]
            total['group_3_over_acc_count_' + str(report_month) + '_' + str(report_year)]     += temp_total['group_3_over_acc_count_' + str(report_month) + '_' + str(report_year)]
            mongodb.update(MONGO_COLLECTION=collection, WHERE={'group_code': product['group_code'], 'int_rate': product['group_code'] + ' Total', 'year': year}, VALUE=temp_total)
        else:
            card_types = ['301', '302']
            temp_total = {
                'group_code'                        : product['group_code'],
                'int_rate'                          : product['group_code'] + ' Total',
                'int_rate_name'                     : product['group_name'] + ' Total',
                'year'                              : year,
            }
            temp_total['total_w_org_' + str(report_month) + '_' + str(report_year)]               = 0
            temp_total['total_acc_count_' + str(report_month) + '_' + str(report_year)]           = 0
            temp_total['group_2_w_org_' + str(report_month) + '_' + str(report_year)]             = 0
            temp_total['group_2_acc_count_' + str(report_month) + '_' + str(report_year)]         = 0
            temp_total['group_3_over_w_org_' + str(report_month) + '_' + str(report_year)]        = 0
            temp_total['group_3_over_acc_count_' + str(report_month) + '_' + str(report_year)]    = 0
            credit_card_range = list(map(lambda x: str(format(x, '03d')), range(1, 100, 1)))
            pprint(json.dumps(credit_card_range))
            for card_type in card_types:
                sbv_pipeline = []
                if card_type == '301':
                    sbv_pipeline.append({
                        '$match'                        : {
                            'card_type'                 : {
                                '$in'                   : credit_card_range
                            }
                        }
                    })
                else:
                    sbv_pipeline.append({
                        '$match'                        : {
                            'card_type'                 : {
                                '$nin'                  : credit_card_range
                            }
                        }
                    })
                sbv_pipeline.append(
                {
                    '$group'                            : {
                        '_id'                           : None,
                        'total_w_org'                   : {
                            '$sum'                      : {
                                '$add'                  : ['$ob_principal_cash', '$ob_principal_sale']
                            }
                        },
                        'total_acc_count'               : {
                            "$sum"                      : {
                                "$cond"                 : [
                                    {
                                        '$and'          : [
                                            {"$gt"      : [{'$add': ['$ob_principal_sale', '$ob_principal_cash']}, 0]},
                                        ]
                                    },
                                    1,
                                    0
                                ]
                            }
                        },
                        'group_2_w_org'                 : {
                            '$sum'                      : {
                                '$cond'                 : [
                                    {
                                        '$and'          : [
                                            {
                                                '$eq'   : ['$delinquency_group', '02']
                                            }
                                        ]
                                    },
                                    {'$add'             : ['$ob_principal_cash', '$ob_principal_sale']},
                                    0
                                ]
                            }
                        },
                        'group_2_acc_count'             : {
                            "$sum"                      : {
                                "$cond"                 : [
                                    {
                                        '$and'          : [
                                            {"$gt"      : [{'$add': ['$ob_principal_sale', '$ob_principal_cash']}, 0]},
                                            {'$eq'      : ['$delinquency_group', '02']}
                                        ]
                                    },
                                    1,
                                    0
                                ]
                            }
                        },
                        'group_3_over_w_org'            : {
                            '$sum'                      : {
                                '$cond'                 : [
                                    {
                                        '$and'          : [
                                            {
                                                '$in'   : ['$delinquency_group', ['03', '04', '05']]
                                            }
                                        ]
                                    },
                                    {'$add'             : ['$ob_principal_cash', '$ob_principal_sale']},
                                    0
                                ]
                            }
                        },
                        'group_3_over_acc_count'        : {
                            "$sum"                      : {
                                "$cond"                 : [
                                    {
                                        '$and'          : [
                                            {"$gt"      : [{'$add': ['$ob_principal_sale', '$ob_principal_cash']}, 0]},
                                            {'$in'      : ['$delinquency_group', ['03', '04', '05']]}
                                        ]
                                    },
                                    1,
                                    0
                                ]
                            }
                        }
                    }
                })
                sbvInfo = list(mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'SBV'),aggregate_pipeline=sbv_pipeline))
                if sbvInfo != None:
                    for sbv in sbvInfo:
                        # pprint(sbv)
                        temp_detail = {
                            'group_code'    : product['group_code'],
                            'int_rate'      : card_type,
                            'int_rate_name' : 'Credit card' if card_type == '301' else 'Cash card',
                            'year'          : year
                        }
                        # pprint(sbv['total_acc_count'])
                        temp_detail['total_w_org_' + str(report_month) + '_' + str(report_year)]                  = sbv['total_w_org'] if 'total_w_org' in sbv.keys() else 0
                        temp_detail['total_acc_count_' + str(report_month) + '_' + str(report_year)]              = sbv['total_acc_count'] if 'total_acc_count' in sbv.keys() else 0
                        temp_detail['group_2_w_org_' + str(report_month) + '_' + str(report_year)]                = sbv['group_2_w_org'] if 'group_2_w_org' in sbv.keys() else 0
                        temp_detail['group_2_acc_count_' + str(report_month) + '_' + str(report_year)]            = sbv['group_2_acc_count'] if 'group_2_acc_count' in sbv.keys() else 0
                        temp_detail['group_3_over_w_org_' + str(report_month) + '_' + str(report_year)]           = sbv['group_3_over_w_org'] if 'group_3_over_w_org' in sbv.keys() else 0
                        temp_detail['group_3_over_acc_count_' + str(report_month) + '_' + str(report_year)]       = sbv['group_3_over_acc_count'] if 'group_3_over_acc_count' in sbv.keys() else 0
                        # pprint(temp_detail)
                        mongodb.update(MONGO_COLLECTION=collection, WHERE={'group_code': product['group_code'], 'int_rate': card_type, 'year': year}, VALUE=temp_detail)
                        temp_total['total_w_org_' + str(report_month) + '_' + str(report_year)]                   += temp_detail['total_w_org_' + str(report_month) + '_' + str(report_year)]
                        temp_total['total_acc_count_' + str(report_month) + '_' + str(report_year)]               += temp_detail['total_acc_count_' + str(report_month) + '_' + str(report_year)]
                        temp_total['group_2_w_org_' + str(report_month) + '_' + str(report_year)]                 += temp_detail['group_2_w_org_' + str(report_month) + '_' + str(report_year)]
                        temp_total['group_2_acc_count_' + str(report_month) + '_' + str(report_year)]             += temp_detail['group_2_acc_count_' + str(report_month) + '_' + str(report_year)]
                        temp_total['group_3_over_w_org_' + str(report_month) + '_' + str(report_year)]            += temp_detail['group_3_over_w_org_' + str(report_month) + '_' + str(report_year)]
                        temp_total['group_3_over_acc_count_' + str(report_month) + '_' + str(report_year)]        += temp_detail['group_3_over_acc_count_' + str(report_month) + '_' + str(report_year)]
                        
                        total['total_w_org_' + str(report_month) + '_' + str(report_year)]                += temp_detail['total_w_org_' + str(report_month) + '_' + str(report_year)] if ('total_w_org_' + str(report_month) + '_' + str(report_year)) in temp_total.keys() else 0
                        total['total_acc_count_' + str(report_month) + '_' + str(report_year)]            += temp_detail['total_acc_count_' + str(report_month) + '_' + str(report_year)]
                        total['group_2_w_org_' + str(report_month) + '_' + str(report_year)]              += temp_detail['group_2_w_org_' + str(report_month) + '_' + str(report_year)]
                        total['group_2_acc_count_' + str(report_month) + '_' + str(report_year)]          += temp_detail['group_2_acc_count_' + str(report_month) + '_' + str(report_year)]
                        total['group_3_over_w_org_' + str(report_month) + '_' + str(report_year)]         += temp_detail['group_3_over_w_org_' + str(report_month) + '_' + str(report_year)]
                        total['group_3_over_acc_count_' + str(report_month) + '_' + str(report_year)]     += temp_detail['group_3_over_acc_count_' + str(report_month) + '_' + str(report_year)]
                mongodb.update(MONGO_COLLECTION=collection, WHERE={'group_code': product['group_code'], 'int_rate': product['group_code'] + ' Total', 'year': year}, VALUE=temp_total)
                # pprint(temp_total['total_acc_count_' + str(report_month) + '_' + str(report_year)])
                # total['total_w_org_' + str(report_month) + '_' + str(report_year)]                += temp_detail['total_w_org_' + str(report_month) + '_' + str(report_year)] if ('total_w_org_' + str(report_month) + '_' + str(report_year)) in temp_total.keys() else 0
                # total['total_acc_count_' + str(report_month) + '_' + str(report_year)]            += temp_detail['total_acc_count_' + str(report_month) + '_' + str(report_year)]
                # total['group_2_w_org_' + str(report_month) + '_' + str(report_year)]              += temp_detail['group_2_w_org_' + str(report_month) + '_' + str(report_year)]
                # total['group_2_acc_count_' + str(report_month) + '_' + str(report_year)]          += temp_detail['group_2_acc_count_' + str(report_month) + '_' + str(report_year)]
                # total['group_3_over_w_org_' + str(report_month) + '_' + str(report_year)]         += temp_detail['group_3_over_w_org_' + str(report_month) + '_' + str(report_year)]
                # total['group_3_over_acc_count_' + str(report_month) + '_' + str(report_year)]     += temp_detail['group_3_over_acc_count_' + str(report_month) + '_' + str(report_year)]
                
                pprint(temp_total['total_acc_count_' + str(report_month) + '_' + str(report_year)])
    mongodb.update(MONGO_COLLECTION=collection_total, WHERE={'group_code': 'total', 'int_rate': 'total', 'year': year}, VALUE=total)
except Exception as e:
    # log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
    print(traceback.format_exc())
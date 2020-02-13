#!/usr/bin/python3.6
# -*- coding: utf-8 -*-
import sys
import os
import time
import ntpath
import json
import calendar
import traceback
import math
from helper.mongod import Mongodb
from datetime import datetime
from datetime import date
from pprint import pprint
from bson import ObjectId
from helper.common import Common
from helper.jaccs import Config
# help round down
def round_down(n, decimals=0):
    multiplier = 10 ** decimals
    return math.floor(n * multiplier) / multiplier
#help
common      = Common()
base_url    = common.base_url()
wff_env     = common.wff_env(base_url)
mongodb     = Mongodb(MONGODB="worldfone4xs", WFF_ENV=wff_env)
_mongodb    = Mongodb(MONGODB="_worldfone4xs", WFF_ENV=wff_env)
now         = datetime.now()
subUserType = 'LO'

collection           = common.getSubUser(subUserType, 'List_of_all_customer_report')

zaccf_collection     = common.getSubUser(subUserType, 'ZACCF')
sbv_collection            = common.getSubUser(subUserType, 'SBV')

product_collection   = common.getSubUser(subUserType, 'Product')
user_collection           = common.getSubUser(subUserType, 'User')

log         = open(base_url + "cronjob/python/Loan/log/List_of_all_customer_only_SIBS.txt","a")
log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': Start Import' + '\n')
try:
    data        = []
    cardData        = []
    insertData  = []
    list_group = ['1','2','3','4','5']
    list_group_total = ['G2','G2~','G3~']
    resultData  = []
    errorData   = []

    g1 = 0
    g2 = 0
    g3 = 0
    g4 = 0
    g5 = 0

    today = date.today()
    # today = datetime.strptime('12/10/2019', "%d/%m/%Y").date()

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

    if todayTimeStamp in listHoliday:
      sys.exit()

    users = _mongodb.get(MONGO_COLLECTION=user_collection, SELECT=['extension','agentname'],SORT=([('_id', -1)]),SKIP=0, TAKE=200)


   # Zaccf
#     aggregate_zaccf = [
#         {
#            "$match":
#            {
#                'created_at': {'$gte' : startMonth,'$lte' : endMonth},
#            }
#        },
#        {
#            "$project":
#            {    
#             #    col field
#                "account_number": 1,
#                "CUS_ID"        : 1,
#                "name"          : 1,
#                "ODIND_FG"      : 1,
#                "CAR_ID"        : 1,
#                "TERM_ID"       : 1,
#                "W_ORG"         : 1,
#                "PRODGRP_ID"    : 1,
#                "LIC_NO"        : 1,
#                "INT_RATE"      : 1,
#                "WRK_BRN"       : 1,
#                "STAT_CD"       : 1

               
#            }
#        }
#    ]
#     data = mongodb.aggregate_pipeline(MONGO_COLLECTION=zaccf_collection,aggregate_pipeline=aggregate_zaccf)
 
#     for row in data:
#       if 'account_number' in row.keys():
#             temp = {}
#             temp['DT_TX'] = now.strftime("%d/%m/%Y")
#             temp['ACC_ID'] = row['account_number']
#             temp['CUS_ID'] = row['CUS_ID']
#             temp['CUS_NM'] = row['name']
#             temp['Loan_Group'] = row['ODIND_FG']
#             temp['CAR_ID'] = row['CAR_ID']
#             temp['TERM_ID'] = row['TERM_ID']
#             temp['W_ORG'] = int(float(row['W_ORG']))
#             temp['TOTAL_ACC'] = '1'
#             temp['TOTAL_CUS'] = '1'
#             temp['PRODGRP_ID'] = row['PRODGRP_ID']
#             temp['LIC_NO'] =  row['LIC_NO']
#             temp['interest_rate'] = float(row['INT_RATE'])*100
#             temp['Dealer_code'] = row['WRK_BRN']
#             temp['Province_code'] = row['STAT_CD']
#             temp['createdAt'] = time.time()   
#             temp['createdBy'] = 'system'   
#             insertData.append(temp)
    
# # sbv
#     aggregate_sbv = [
        
#         {
#             "$project":
#             {
#             #    col field
#                 "contract_no"            : 1,
#                 "cus_no"                 : 1,
#                 "name"                   : 1,
#                 "delinquency_group"      : 1,
#                 "CAR_ID"                 : 1,
#                 "ob_principal_sale"      : 1,
#                 "ob_principal_cash"      : 1,
#                 "card_type"              : 1,
#                 "license_no"             : 1,
#                 "interest_rate"          : 1,
#                 "state"                  : 1
                

                
#             }
#         }
#     ]
#     data1 = mongodb.aggregate_pipeline(MONGO_COLLECTION=sbv_collection,aggregate_pipeline=aggregate_sbv)


#     for row1 in data1:
#         temp1 = {}
#         zaccf = mongodb.getOne(MONGO_COLLECTION=zaccf_collection, WHERE={'LIC_NO': row1['license_no']},SELECT=['WRK_BRN']) 
#         if 'contract_no' in row1.keys():
          
                
#             temp1['DT_TX'] = now.strftime("%d/%m/%Y")
#             temp1['ACC_ID'] = row1['contract_no']
#             temp1['CUS_ID'] = row1['cus_no']
#             temp1['CUS_NM'] = row1['name']
#             temp1['Loan_Group'] = row1['delinquency_group']
#             temp1['CAR_ID'] = ''
#             temp1['TERM_ID'] = ''
#             temp1['W_ORG'] = int(row1['ob_principal_sale']) + int(row1['ob_principal_cash'])
#             temp1['TOTAL_ACC'] = '1'
#             temp1['TOTAL_CUS'] = '1'
#             temp1['PRODGRP_ID'] = "301" if int(row1['card_type']) < 100 else "302"
#             temp1['LIC_NO'] =  row1['license_no']
#             temp1['interest_rate'] = float(row1['interest_rate']) *100
#             temp1['Dealer_code'] = ''
#             temp1['Province_code'] = row1['state']
#             temp1['createdAt'] = time.time()   
#             temp1['createdBy'] = 'system'   
#             insertData.append(temp1)

    list_product = mongodb.get(common.getSubUser(subUserType, 'Product'), SORT=[("code", 1)])
    for product_code, product_value in enumerate(list_product):
        temp_group = {}
        aggregate_sum = [
                {
                    "$match"                            : {
                        'PRODGRP_ID'                    : product_value['code'],
                        "$or"                           : [{
                            "createdAt"                 : {
                                "$gte"                  : startMonth,
                                "$lte"                  : endMonth
                            },
                        }] 
                    }
                }, 
                {
                    "$group"                            : {
                        "_id"                           : None,
                        
                        "g1"            : {
                            "$sum"                      : {
                                "$cond"                 : [
                                    {
                                        '$or'          : [
                                            {"$eq"      : ["$Loan_Group", '1']},
                                            {"$eq"      : ["$Loan_Group", "A"]}
                                        ]
                                    },
                                    1,
                                    0
                                ]
                            }
                        },
                        "g2"            : {
                            "$sum"                     : {
                                "$cond"                 : [
                                    {
                                        '$or'          : [
                                            {"$eq"      : ["$Loan_Group", "2"]},
                                            {"$eq"      : ["$Loan_Group", "B"]},
                                        ]
                                    },
                                    1,0
                                ]
                            }
                        },
                        "g3"            : {
                            "$sum"                      : {
                                "$cond"                 : [
                                    {
                                        '$or'          : [
                                          {"$eq"      : ["$Loan_Group", "3"]},
                                            {"$eq"      : ["$Loan_Group", "C"]},
                                        ]
                                    },
                                    1,
                                    0
                                ]
                            }
                        },
                        "g4"            : {
                            "$sum"                     : {
                                "$cond"                 : [
                                    {
                                        '$or'          : [
                                          {"$eq"      : ["$Loan_Group", "4"]},
                                            {"$eq"      : ["$Loan_Group", "D"]},
                                        ]
                                    },
                                    1,0
                                ]
                            }
                        },
                        "g5"            : {
                            "$sum"                      : {
                                "$cond"                 : [
                                    {
                                       '$or'          : [
                                          {"$eq"      : ["$Loan_Group", "5"]},
                                            {"$eq"      : ["$Loan_Group", "E"]},
                                        ]
                                    },
                                    1,
                                    0
                                ]
                            }
                        }
                    }
                }
            ]
        
        total_report = list(mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'List_of_all_customer_report'),aggregate_pipeline=aggregate_sum))
        print(total_report)
        sys.exit()


    if len(insertData) > 0:
    #   mongodb.remove_document(MONGO_COLLECTION=collection)
      mongodb.batch_insert(MONGO_COLLECTION=collection, insert_data=insertData)
    now_end         = datetime.now()
    log.write(now_end.strftime("%d/%m/%Y, %H:%M:%S") + ': End Log' + '\n')
    print('DONE')
except Exception as e:
    pprint(e)
    traceback.print_exc()
    log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')


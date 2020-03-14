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

# Python code to merge dict using update() method 
def Merge(dict1, dict2): 
    return(dict2.update(dict1))         
#help
# Switcher is dictionary data type here 
def convert_group(argument): 
    switcher = { 
        "A": "01", 
        "B": "02", 
        "C": "03", 
        "D": "04", 
        "E": "05", 
       
    } 
  
    # get() method of dictionary data type returns  
    # value of passed argument if it is present  
    # in dictionary otherwise second argument will 
    # be assigned as default value of passed argument 
    return switcher.get(argument, argument) 
common      = Common()
base_url    = common.base_url()
wff_env     = common.wff_env(base_url)
mongodb     = Mongodb(MONGODB="worldfone4xs", WFF_ENV=wff_env)
_mongodb    = Mongodb(MONGODB="_worldfone4xs", WFF_ENV=wff_env)
now         = datetime.now()
subUserType = 'LO'

collection           = common.getSubUser(subUserType, 'List_of_all_customer_report')
collection1          = common.getSubUser(subUserType, 'List_of_all_customer_total_report_temp')

zaccf_collection     = common.getSubUser(subUserType, 'ZACCF_01022020')
sbv_collection            = common.getSubUser(subUserType, 'SBV_01022020')

product_collection   = common.getSubUser(subUserType, 'Product')
province_collection   = common.getSubUser(subUserType, 'Province')
user_collection           = common.getSubUser(subUserType, 'User')

log         = open(base_url + "cronjob/python/Loan/log/List_of_all_customer_only_SIBS.txt","a")
log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': Start Import' + '\n')
try:
    data        = []
    cardData        = []
    insertData  = []
    insertData1  = []
    list_group = ["01","02","03","04","05","A","B","C","D","E"]
    list_group_total = ['G2','G2~','G3~']
    resultData  = []
    errorData   = []

    gr1 = 0
    gr2 = 0
    gr3 = 0
    gr4 = 0
    gr5 = 0
    g2 = 1
    total = 0
    today = date.today()
    
    # today = datetime.strptime('12/10/2019', "%d/%m/%Y").date()

    day = today.day
    month = today.month
    year = today.year
    weekday = today.weekday()
    lastDayOfMonth = calendar.monthrange(year, month)[1]
    
    
    todayString = today.strftime("%d/%m/%Y")
    todayTimeStamp = int(time.mktime(time.strptime(str(todayString + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
    endTodayTimeStamp = int(time.mktime(time.strptime(str(todayString + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))
    
    startMonth = int(time.mktime(time.strptime(str('01/' + str(month) + '/' + str(year) + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
    endMonth = int(time.mktime(time.strptime(str(str(lastDayOfMonth) + '/' + str(month) + '/' + str(year) + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))

    # holidayOfMonth = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_off_sys'))
    # listHoliday = map(lambda offDateRow: {offDateRow['off_date']}, holidayOfMonth)

    # if day != 1:
    #   print('stop!')  
    #   sys.exit()

    
   # Zaccf
#     aggregate_zaccf = [
#         {
#            "$match":
#            {
#                'W_ORG_1'                       :{'$gt'     : 0},
#                "$or":[
#                     {'createdAt': {'$gte' : todayTimeStamp,'$lte' : endTodayTimeStamp}},
#                     {'updatedAt': {'$gte' : todayTimeStamp,'$lte' : endTodayTimeStamp}}
#                ]
               
#            }
#        },
      
#        {
#             "$lookup":
#                 {
#                 "from": product_collection,
#                 "localField":"PRODGRP_ID",
#                 "foreignField": "code",
#                 "as" : "product"
#                 }
#         },
#        {
#             "$unwind":
#                 {
#                 "path": "$product",
#                 "preserveNullAndEmptyArrays": True
#                 }
#         },
#        {
#             "$lookup":
#                 {
#                 "from": province_collection,
#                 "localField":"STAT_CD",
#                 "foreignField": "code",
#                 "as" : "province"
#                 }
#         },
#          {
#             "$unwind":
#                 {
#                 "path": "$province",
#                 "preserveNullAndEmptyArrays": True
#                 }
#         },
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
#                "PRODGRP_NAME"  :"$product.name",
#                "LIC_NO"        : 1,
#                "INT_RATE"      : 1,
#                "WRK_BRN"       : 1,
#                "STAT_CD"       : 1,
#                "STAT_CD_NAME"       : "$province.name"

               
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
#             temp['Loan_Group'] = convert_group(row['ODIND_FG'])
#             temp['CAR_ID'] = row['CAR_ID']
#             temp['TERM_ID'] = int(row['TERM_ID'])
#             temp['W_ORG'] = int(float(row['W_ORG']))
#             temp['TOTAL_ACC'] = 1
#             temp['TOTAL_CUS'] = 1
#             temp['PRODGRP_CODE'] = row['PRODGRP_ID']
#             temp['PRODGRP_ID'] = row['PRODGRP_NAME'] if 'PRODGRP_NAME' in row.keys() else row['PRODGRP_ID']
#             temp['LIC_NO'] =  row['LIC_NO']
#             temp['interest_rate'] = float(row['INT_RATE'])
#             temp['Dealer_code'] = row['WRK_BRN']
#             temp['Province_code'] = row['STAT_CD_NAME'] if 'STAT_CD_NAME' in row.keys() else row['STAT_CD']
#             temp['createdAt'] = todayTimeStamp  
#             temp['createdBy'] = 'system'
              
#             insertData.append(temp)
    
# #  # sbv
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
#         province = mongodb.getOne(MONGO_COLLECTION=province_collection, WHERE={'code': "0"+row1['state']},SELECT=['name'])
#         if 'contract_no' in row1.keys() and int(row1['ob_principal_sale']) + int(row1['ob_principal_cash']) > 0:
          
                
#             temp1['DT_TX'] = now.strftime("%d/%m/%Y")
#             temp1['ACC_ID'] = row1['contract_no']
#             temp1['CUS_ID'] = row1['cus_no']
#             temp1['CUS_NM'] = row1['name']
#             temp1['Loan_Group'] = row1['delinquency_group']
#             temp1['CAR_ID'] = ''
#             temp1['TERM_ID'] = ''
#             temp1['W_ORG'] = int(row1['ob_principal_sale']) + int(row1['ob_principal_cash'])
#             temp1['TOTAL_ACC'] = 1
#             temp1['TOTAL_CUS'] = 1
#             temp1['PRODGRP_CODE'] = "301" if int(row1['card_type']) < 100 else "302"
#             temp1['PRODGRP_ID'] = "301 – Credit card" if int(row1['card_type']) < 100 else "302 – Cash card"
#             temp1['LIC_NO'] =  row1['license_no']
#             temp1['interest_rate'] = float(row1['interest_rate'])
#             temp1['Dealer_code'] = ''
#             temp1['Province_code'] = province['name'] if 'name' in province.keys() else row1['state']
#             temp1['createdAt'] = todayTimeStamp   
#             temp1['createdBy'] = 'system'
    
#             insertData.append(temp1)
#     if len(insertData) > 0:
#     #   mongodb.remove_document(MONGO_COLLECTION=collection)
#         mongodb.batch_insert(MONGO_COLLECTION=collection, insert_data=insertData)
    
    for group in list_group:
        temp_group = {}
    
        list_product = mongodb.get(common.getSubUser(subUserType, 'Product'), SORT=[("code", 1)])
        for product_code, product_value in enumerate(list_product):
            
            aggregate_sum = [
                    {
                        "$match"                            : {
                            'Loan_Group'                    : group,
                            # "$and"                           : [{
                            #     "createdAt"                 : {
                            #         "$gte"                  : todayTimeStamp,
                            #         "$lte"                  : endTodayTimeStamp
                            #     },
                            # }] 
                        }
                    }, 
                    {
                        "$group"                            : {
                            "_id"                           : None,
                            "g"+product_value['code']            : {
                                "$sum"                      : {
                                    "$cond"                 : [
                                        {
                                            '$or'          : [
                                                {"$eq"      : ["$PRODGRP_CODE", product_value['code'] ]},
                                               
                                            ],
                                        },
                                        1,
                                        0
                                    ]
                                }
                            },
                           "amount_"+product_value['code']            : {
                                "$sum"                      : {
                                    "$cond"                 : [
                                        {
                                            '$or'          : [
                                                {"$eq"      : ["$PRODGRP_CODE", product_value['code'] ]},
                                               
                                            ],
                                        },
                                        "$W_ORG",
                                        0
                                    ]
                                }
                            },
                            "t_g"+group         :{
                                "$sum"  : {
                                    "$cond"                 : [
                                        {
                                            '$or'          : [
                                                {"$eq"      : ["$Loan_Group", group ]},
                                               
                                            ],
                                        },
                                        1,
                                        0
                                    ]

                                }
                            },

                            "t_a"+group         :{
                                "$sum"  : {
                                    "$cond"                 : [
                                        {
                                            '$or'          : [
                                                {"$eq"      : ["$Loan_Group", group ]},
                                               
                                            ],
                                        },
                                        "$W_ORG",
                                        0
                                    ]

                                }
                            },
                        }
                    },
                    {
                    "$project"              :{
                            "group"         : group,
                            "product"       :   product_value['code'],  
                            "g"+product_value['code']          :   "$g"+product_value['code'],
                            "a"+product_value['code']          :   "$amount_"+product_value['code'],
                            "t_g"+group                        :    "$t_g"+group,
                            "t_a"+group                        :    "$t_a"+group 
                            
                            # "total"         :   "$total",
                            # "g2"               : {
                            #     "$divide"      : ["$gr2","$total"]
                            # }
                    }
                    }
                ]
            
            total_report = list(mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'List_of_all_customer_report'),aggregate_pipeline=aggregate_sum))
            if total_report not in [None, []] and total_report[0] is not None:
                    
                    temp_group['group'] = group
                    temp_group["g"+product_value['code']] = total_report[0]["g"+product_value['code']]
                    temp_group["a"+product_value['code']] = total_report[0]["a"+product_value['code']]
                    temp_group['t_g']             =   total_report[0]["t_g"+group]
                    temp_group['t_a']             =   total_report[0]["t_a"+group]
                    temp_group['createdAt'] = todayTimeStamp
                    # if total_report[0]['group'] == "A":  
                    #     temp_group['group'] = "01"
                    #     temp_group["g"+product_value['code']] =  total_report[0]["g"+product_value['code']]  
                    #     temp_group["a"+product_value['code']] = total_report[0]["a"+product_value['code']]  
                    #     temp_group['t_g']             =   total_report[0]["t_g"+group]
                    #     temp_group['t_a']             =   total_report[0]["t_a"+group]
                    #     temp_group['createdAt'] = time.time()
                    # elif total_report[0]['group'] == "B":  
                    #     temp_group['group'] = "02"
                    #     temp_group["g"+product_value['code']] =  total_report[0]["g"+product_value['code']] 
                    #     temp_group["a"+product_value['code']] =  total_report[0]["a"+product_value['code']]
                    #     temp_group['t_g']             =   total_report[0]["t_g"+group]
                    #     temp_group['t_a']             =   total_report[0]["t_a"+group]
                    #     temp_group['createdAt'] = time.time()  
                    # elif total_report[0]['group'] == "C":  
                    #     temp_group['group'] = "03"
                    #     temp_group["g"+product_value['code']] =  total_report[0]["g"+product_value['code']] 
                    #     temp_group["a"+product_value['code']] =  total_report[0]["a"+product_value['code']]  
                    #     temp_group['t_g']             =   total_report[0]["t_g"+group]
                    #     temp_group['t_a']             =   total_report[0]["t_a"+group]
                    #     temp_group['createdAt'] = time.time()
                    # elif total_report[0]['group'] == "D":  
                    #     temp_group['group'] = "04"
                    #     temp_group["g"+product_value['code']] = total_report[0]["g"+product_value['code']]  
                    #     temp_group["a"+product_value['code']] =  total_report[0]["a"+product_value['code']]
                    #     temp_group['t_g']             =   total_report[0]["t_g"+group]
                    #     temp_group['t_a']             =   total_report[0]["t_a"+group]
                    #     temp_group['createdAt'] = time.time()  
                    # elif total_report[0]['group'] == "E":  
                    #     temp_group['group'] = "05"
                    #     temp_group["g"+product_value['code']] =  total_report[0]["g"+product_value['code']] 
                    #     temp_group["a"+product_value['code']] =  total_report[0]["a"+product_value['code']] 
                    #     temp_group['t_g']             =   total_report[0]["t_g"+group]
                    #     temp_group['t_a']             =   total_report[0]["t_a"+group]
                    #     temp_group['createdAt'] = time.time()
            # insertData1.append(temp_group) 
        if temp_group !={}:
            insertData1.append(temp_group)      
              
         

    tt_temp ={}
    list_product = mongodb.get(common.getSubUser(subUserType, 'Product'), SORT=[("code", 1)])
    for product_code, product_value in enumerate(list_product):
            
        aggregate_tt = [
                {
                    "$match"                            : {
                        
                        # "$and"                           : [{
                        #     "createdAt"                 : {
                        #         "$gte"                  : todayTimeStamp,
                        #         "$lte"                  : endTodayTimeStamp
                        #     },
                        # }] 
                    }
                }, 
                {
                    "$group"                            : {
                        "_id"                           : None,
                        "g"+product_value['code']            : {
                            "$sum"                      : {
                                "$cond"                 : [
                                    {
                                        '$or'          : [
                                            {"$eq"      : ["$PRODGRP_CODE", product_value['code'] ]},
                                            
                                        ],
                                    },
                                    1,
                                    0
                                ]
                            }
                        },
                        "amount_"+product_value['code']            : {
                            "$sum"                      : {
                                "$cond"                 : [
                                    {
                                        '$or'          : [
                                            {"$eq"      : ["$PRODGRP_CODE", product_value['code'] ]},
                                            
                                        ],
                                    },
                                    "$W_ORG",
                                    0
                                ]
                            }
                        },
                        "t_g"         :{
                            "$sum"  : 1
                             

                        },

                        "t_a":{
                            "$sum"  : "$W_ORG"
                             

                        },
                    }
                },
                {
                "$project"              :{
                       
                          
                        "g"+product_value['code']          :   "$g"+product_value['code'],
                        "a"+product_value['code']          :   "$amount_"+product_value['code'],
                        "t_g"                        :    "$t_g",
                        "t_a"                        :    "$t_a" 
                        
                        # "total"         :   "$total",
                        # "g2"               : {
                        #     "$divide"      : ["$gr2","$total"]
                        # }
                }
                }
            ]
        
        total_report1 = list(mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'List_of_all_customer_report'),aggregate_pipeline=aggregate_tt))
        if total_report1 not in [None, []] and total_report1[0] is not None: 
            tt_temp['group'] ="TOTAL"
            tt_temp["g"+product_value['code']] = total_report1[0]["g"+product_value['code']]
            tt_temp["a"+product_value['code']] = total_report1[0]["a"+product_value['code']]
            tt_temp['t_g']             =   total_report1[0]["t_g"]
            tt_temp['t_a']             =   total_report1[0]["t_a"]
            tt_temp['createdAt'] = todayTimeStamp
    insertData1.append(tt_temp)

    
    if len(insertData1) > 0:
      mongodb.batch_insert(MONGO_COLLECTION=collection1, insert_data=insertData1)
    now_end         = datetime.now()
    log.write(now_end.strftime("%d/%m/%Y, %H:%M:%S") + ': End Log' + '\n')
    print('DONE')
except Exception as e:
    pprint(e)
    traceback.print_exc()
    log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')

   

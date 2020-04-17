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
from datetime import date,timedelta
from pprint import pprint
from bson import ObjectId
from helper.common import Common
from helper.jaccs import Config
import pandas as pd
import xlsxwriter

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
collection1          = common.getSubUser(subUserType, 'List_of_all_customer_total_report')
temp_collection      = common.getSubUser(subUserType, 'List_of_all_customer_total_report_temp')

zaccf_collection     = common.getSubUser(subUserType, 'ZACCF_report')
sbv_collection            = common.getSubUser(subUserType, 'SBV')

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
    insertData2  = []
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

    yesterday = today - timedelta(days=1)
    dateExport = "0"+ str( int(yesterday.strftime('%m')) ) + yesterday.strftime("%Y")

    if day != 1:
      print('stop!')  
      sys.exit()

    mongodb.remove_document(MONGO_COLLECTION=collection, WHERE={'createdAt': {'$gte': todayTimeStamp, '$lte': endTodayTimeStamp} })
    mongodb.remove_document(MONGO_COLLECTION=collection1, WHERE={'createdAt': {'$gte': todayTimeStamp, '$lte': endTodayTimeStamp} })

    # Zaccf
    aggregate_zaccf = [
        {
           "$match":
           {
               'W_ORG_1'                       :{'$gt'     : 0},
               "$or":[
                    {'createdAt': {'$gte' : startMonth,'$lte' : endMonth}},
                    {'updatedAt': {'$gte' : todayTimeStamp,'$lte' : endTodayTimeStamp}}
               ]
               
           }
       },
      
       {
            "$lookup":
                {
                "from": product_collection,
                "localField":"PRODGRP_ID",
                "foreignField": "code",
                "as" : "product"
                }
        },
       {
            "$unwind":
                {
                "path": "$product",
                "preserveNullAndEmptyArrays": True
                }
        },
       {
            "$lookup":
                {
                "from": province_collection,
                "localField":"STAT_CD",
                "foreignField": "code",
                "as" : "province"
                }
        },
         {
            "$unwind":
                {
                "path": "$province",
                "preserveNullAndEmptyArrays": True
                }
        },
       {
           "$project":
           {    
            #    col field
               "account_number": 1,
               "CUS_ID"        : 1,
               "name"          : 1,
               "ODIND_FG"      : 1,
               "CAR_ID"        : 1,
               "TERM_ID"       : 1,
               "W_ORG"         : 1,
               "PRODGRP_ID"    : 1,
               "PRODGRP_NAME"  :"$product.name",
               "LIC_NO"        : 1,
               "INT_RATE"      : 1,
               "WRK_BRN"       : 1,
               "STAT_CD"       : 1,
               "STAT_CD_NAME"       : "$province.name"

               
           }
       }
   ]
    data = mongodb.aggregate_pipeline(MONGO_COLLECTION=zaccf_collection,aggregate_pipeline=aggregate_zaccf)
    
    for row in data:
    
        
      if 'account_number' in row.keys():
            
            temp = {}
            temp['DT_TX'] = now.strftime("%d/%m/%Y")
            temp['ACC_ID'] = row['account_number']
            temp['CUS_ID'] = row['CUS_ID']
            temp['CUS_NM'] = row['name']
            temp['Loan_Group'] = convert_group(row['ODIND_FG'])
            temp['CAR_ID'] = row['CAR_ID']
            temp['TERM_ID'] = int(row['TERM_ID'])
            temp['W_ORG'] = int(float(row['W_ORG']))
            temp['TOTAL_ACC'] = 1
            temp['TOTAL_CUS'] = 1
            temp['PRODGRP_CODE'] = row['PRODGRP_ID']
            temp['PRODGRP_ID'] = row['PRODGRP_NAME'] if 'PRODGRP_NAME' in row.keys() else row['PRODGRP_ID']
            temp['LIC_NO'] =  row['LIC_NO']
            temp['interest_rate'] = float(row['INT_RATE'])
            temp['Dealer_code'] = row['WRK_BRN']
            temp['Province_code'] = row['STAT_CD_NAME'] if 'STAT_CD_NAME' in row.keys() else row['STAT_CD']
            temp['createdAt'] = time.time()   
            temp['createdBy'] = 'system'
              
            insertData.append(temp)
    
#  # sbv
    aggregate_sbv = [
      
        
       
        {
            "$project":
            {
            #    col field
                "contract_no"            : 1,
                "cus_no"                 : 1,
                "name"                   : 1,
                "delinquency_group"      : 1,
                "CAR_ID"                 : 1,
                "ob_principal_sale"      : 1,
                "ob_principal_cash"      : 1,
                "card_type"              : 1,
                "license_no"             : 1,
                "interest_rate"          : 1,
                "state"                  : 1
                

                
            }
        }
    ]
    data1 = mongodb.aggregate_pipeline(MONGO_COLLECTION=sbv_collection,aggregate_pipeline=aggregate_sbv)


    for row1 in data1:
        

        temp1 = {}
        province = mongodb.getOne(MONGO_COLLECTION=province_collection, WHERE={'code': "0"+row1['state']},SELECT=['name'])
        if 'contract_no' in row1.keys() and int(row1['ob_principal_sale']) + int(row1['ob_principal_cash']) > 0:
          
                
            temp1['DT_TX'] = now.strftime("%d/%m/%Y")
            temp1['ACC_ID'] = row1['contract_no']
            temp1['CUS_ID'] = row1['cus_no']
            temp1['CUS_NM'] = row1['name']
            temp1['Loan_Group'] = row1['delinquency_group']
            temp1['CAR_ID'] = ''
            temp1['TERM_ID'] = ''
            temp1['W_ORG'] = int(row1['ob_principal_sale']) + int(row1['ob_principal_cash'])
            temp1['TOTAL_ACC'] = 1
            temp1['TOTAL_CUS'] = 1
            temp1['PRODGRP_CODE'] = "301" if int(row1['card_type']) < 100 else "302"
            temp1['PRODGRP_ID'] = "301 – Credit card" if int(row1['card_type']) < 100 else "302 – Cash card"
            temp1['LIC_NO'] =  row1['license_no']
            temp1['interest_rate'] = float(row1['interest_rate'])
            temp1['Dealer_code'] = ''
            temp1['Province_code'] = province['name'] if province!=None else row1['state']
            temp1['createdAt'] = time.time()   
            temp1['createdBy'] = 'system'
    
            insertData.append(temp1)
    if len(insertData) > 0:
        mongodb.batch_insert(MONGO_COLLECTION=collection, insert_data=insertData)
    





    # tinh du lieu sheet total
    for group in list_group:
        temp_group = {}
    
        list_product = mongodb.get(common.getSubUser(subUserType, 'Product'), SORT=[("code", 1)])
        for product_code, product_value in enumerate(list_product):
            
            aggregate_sum = [
                    {
                        "$match"                            : {
                            'Loan_Group'                    : group,
                            "$and"                           : [{
                                "createdAt"                 : {
                                    "$gte"                  : todayTimeStamp,
                                    "$lte"                  : endTodayTimeStamp
                                },
                            }] 
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
            
            total_report = list(mongodb.aggregate_pipeline(MONGO_COLLECTION= collection ,aggregate_pipeline=aggregate_sum))
            if total_report not in [None, []] and total_report[0] is not None:
                    
                    temp_group['group'] = group
                    temp_group["g"+product_value['code']] = total_report[0]["g"+product_value['code']]
                    temp_group["a"+product_value['code']] = total_report[0]["a"+product_value['code']]
                    temp_group['t_g']             =   total_report[0]["t_g"+group]
                    temp_group['t_a']             =   total_report[0]["t_a"+group]
                    temp_group['createdAt'] = time.time()
        if temp_group !={}:
            insertData1.append(temp_group)      
              
         

    tt_temp ={}
    list_product = mongodb.get(common.getSubUser(subUserType, 'Product'), SORT=[("code", 1)])
    for product_code, product_value in enumerate(list_product):
            
        aggregate_tt = [
                {
                    "$match"                            : {
                        
                        "$and"                           : [{
                            "createdAt"                 : {
                                "$gte"                  : todayTimeStamp,
                                "$lte"                  : endTodayTimeStamp
                            },
                        }] 
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
        
        total_report1 = list(mongodb.aggregate_pipeline(MONGO_COLLECTION= collection ,aggregate_pipeline=aggregate_tt))
        if total_report1 not in [None, []] and total_report1[0] is not None: 
            tt_temp['group'] ="TOTAL"
            tt_temp["g"+product_value['code']] = total_report1[0]["g"+product_value['code']]
            tt_temp["a"+product_value['code']] = total_report1[0]["a"+product_value['code']]
            tt_temp['t_g']             =   total_report1[0]["t_g"]
            tt_temp['t_a']             =   total_report1[0]["t_a"]
            tt_temp['createdAt'] = time.time()
    insertData1.append(tt_temp)

    
    if len(insertData1) > 0:
        mongodb.batch_insert(MONGO_COLLECTION=temp_collection, insert_data=insertData1)

    # doc du lieu ra tu bang tam
    grouplist = mongodb.getDistinct(MONGO_COLLECTION=temp_collection, SELECT='group')
    i = 0
    for group in grouplist:
        i = i + 1
        temp = {}
        list_product = mongodb.get(common.getSubUser(subUserType, 'Product'), SORT=[("code", 1)])
        for product_code, product_value in enumerate(list_product):
            aggregate_sum = [
                        {
                            "$match"                            : {
                                'group'                    : group,
                                # "$and"                           : [{
                                #     "createdAt"                 : {
                                #         "$gte"                  : startMonth,
                                #         "$lte"                  : endMonth
                                #     },
                                # }] 
                            }
                        }, 
                        {
                            "$group"                            : {
                                "_id"                           : None,
                                # ""+product_value['code']            : {
                                #     "$sum"                      : {
                                #         "$cond"                 : [
                                #             {
                                #                 '$or'          : [
                                #                     {"$eq"      : ["$"+product_value['code'], product_value['code'] ]},
                                                
                                #                 ],
                                #             },
                                #             1,
                                #             0
                                #         ]
                                #     }
                                # },
                                "g"+product_value['code']            : {
                                    "$sum"                      : "$g"+product_value['code']
                                },
                                "t_g"            : {
                                "$sum"                      : {
                                    "$cond"                 : [
                                        {
                                            '$or'          : [
                                                {"$eq"      : ["$group", group ]},
                                               
                                            ],
                                        },
                                        "$t_g",
                                        0
                                    ]
                                }
                            },
                                "t_a"            : {
                                "$sum"                      : {
                                    "$cond"                 : [
                                        {
                                            '$or'          : [
                                                {"$eq"      : ["$group", group ]},
                                               
                                            ],
                                        },
                                        "$t_a",
                                        0
                                    ]
                                }
                            },
                                "a"+product_value['code']            : {
                                    "$sum"                      : "$a"+product_value['code']
                                },

                                

                            }
                        },
                        {
                        "$project"              :{
                                "group"         : group,
                                "product"       :   product_value['code'],  
                                "g"+product_value['code']          :   "$g"+product_value['code'],
                                "t_g"                         :"$t_g",
                                "t_a"                         :"$t_a",
                                "a"+product_value['code']          :   "$a"+product_value['code'],
                                
                                
                                # "g2"               : {
                                #     "$divide"      : [{"$cond":[{"$eq":["$group","02"]},],"$total"]
                                # }
                        }
                        }
                    ]
                
            total_report = list(mongodb.aggregate_pipeline(MONGO_COLLECTION=temp_collection,aggregate_pipeline=aggregate_sum))
            if total_report not in [None, []] and total_report[0] is not None:
                temp['group'] = group
                temp["g"+product_value['code']] = total_report[0]["g"+product_value['code']]
                temp["t_g"] = total_report[0]["t_g"]
                temp["t_a"] = total_report[0]["t_a"]
                temp["a"+product_value['code']] = total_report[0]["a"+product_value['code']]
                temp['index'] = i 
                temp['createdAt'] = time.time()
        insertData2.append(temp)
    

    for gr in list_group_total:
        temp1 ={}
        list_product1 = mongodb.get(common.getSubUser(subUserType, 'Product'), SORT=[("code", 1)])
        for product_code1, product_value1 in enumerate(list_product1):            
            aggregate_total = [
                        
                        {
                            "$group"                            : {
                                "_id"                           : None,
                            "g2_"+product_value1['code']          : {
                                    "$sum"                      : {
                                        "$cond"                 : [
                                            {
                                                '$or'          : [
                                                    {"$eq"      : ["$group", "02" ]},
                                                
                                                ],
                                            },
                                            "$g"+product_value1['code'],
                                            0
                                        ]
                                    }
                                },
                            "a2_"+product_value1['code']          : {
                                    "$sum"                      : {
                                        "$cond"                 : [
                                            {
                                                '$or'          : [
                                                    {"$eq"      : ["$group", "02" ]},
                                                
                                                ],
                                            },
                                            "$a"+product_value1['code'],
                                            0
                                        ]
                                    }
                                },
                            "ttg_g2"          : {
                                    "$sum"                      : {
                                        "$cond"                 : [
                                            {
                                                '$or'          : [
                                                    {"$eq"      : ["$group", "02" ]},
                                                
                                                ],
                                            },
                                            "$t_g",
                                            0
                                        ]
                                    }
                                },
                            "tta_g2"          : {
                                    "$sum"                      : {
                                        "$cond"                 : [
                                            {
                                                '$or'          : [
                                                    {"$eq"      : ["$group", "02" ]},
                                                
                                                ],
                                            },
                                            "$t_a",
                                            0
                                        ]
                                    }
                                },
                            "g21_"+product_value1['code']          : {
                                    "$sum"                      : {
                                        "$cond"                 : [
                                            {
                                                '$and'          : [
                                                    {"$gte"      : ["$group", "02" ]},
                                                    {"$lte"      : ["$group", "05" ]},
                                                
                                                ],
                                            },
                                            "$g"+product_value1['code'],
                                            0
                                        ]
                                    }
                                },
                            "a21_"+product_value1['code']          : {
                                    "$sum"                      : {
                                        "$cond"                 : [
                                            {
                                                '$and'          : [
                                                    {"$gte"      : ["$group", "02" ]},
                                                    {"$lte"      : ["$group", "05" ]},
                                                
                                                ],
                                            },
                                            "$a"+product_value1['code'],
                                            0
                                        ]
                                    }
                                },
                            "ttg_g21"         : {
                                    "$sum"                      : {
                                        "$cond"                 : [
                                            {
                                                '$and'          : [
                                                    {"$gte"      : ["$group", "02" ]},
                                                    {"$lte"      : ["$group", "05" ]},
                                                
                                                ],
                                            },
                                            "$t_g",
                                            0
                                        ]
                                    }
                                },
                            "tta_g21"         : {
                                    "$sum"                      : {
                                        "$cond"                 : [
                                            {
                                                '$and'          : [
                                                    {"$gte"      : ["$group", "02" ]},
                                                    {"$lte"      : ["$group", "05" ]},
                                                
                                                ],
                                            },
                                            "$t_a",
                                            0
                                        ]
                                    }
                                },
                            "g3_"+product_value1['code']          : {
                                    "$sum"                      : {
                                        "$cond"                 : [
                                            {
                                                '$and'          : [
                                                    {"$gte"      : ["$group", "03" ]},
                                                    {"$lte"      : ["$group", "05" ]},
                                                
                                                ],
                                            },
                                            "$g"+product_value1['code'],
                                            0
                                        ]
                                    }
                                },
                            "a3_"+product_value1['code']          : {
                                    "$sum"                      : {
                                        "$cond"                 : [
                                            {
                                                '$and'          : [
                                                    {"$gte"      : ["$group", "03" ]},
                                                    {"$lte"      : ["$group", "05" ]},
                                                
                                                ],
                                            },
                                            "$a"+product_value1['code'],
                                            0
                                        ]
                                    }
                                },
                            "ttg_g3"          : {
                                    "$sum"                      : {
                                        "$cond"                 : [
                                            {
                                                '$and'          : [
                                                    {"$gte"      : ["$group", "03" ]},
                                                    {"$lte"      : ["$group", "05" ]},
                                                
                                                ],
                                            },
                                            "$t_g",
                                            0
                                        ]
                                    }
                                },
                            "tta_g3"         : {
                                    "$sum"                      : {
                                        "$cond"                 : [
                                            {
                                                '$and'          : [
                                                    {"$gte"      : ["$group", "03" ]},
                                                    {"$lte"      : ["$group", "05" ]},
                                                
                                                ],
                                            },
                                            "$t_a",
                                            0
                                        ]
                                    }
                                },
                            "ttg_"+product_value1['code']          : {
                                    "$sum"                      : {
                                        "$cond"                 : [
                                            {
                                                '$or'          : [
                                                    {"$eq"      : ["$group", "TOTAL" ]},
                                                
                                                ],
                                            },
                                            "$g"+product_value1['code'],
                                            0
                                        ]
                                    }
                                },
                            
                                "tta_"+product_value1['code']          : {
                                    "$sum"                      : {
                                        "$cond"                 : [
                                            {
                                                '$or'          : [
                                                    {"$eq"      : ["$group", "TOTAL" ]},
                                                
                                                ],
                                            },
                                            "$a"+product_value1['code'],
                                            0
                                        ]
                                    }
                                },
                                "ttg_tt"          : {
                                    "$sum"                      : {
                                        "$cond"                 : [
                                            {
                                                '$or'          : [
                                                    {"$eq"      : ["$group", "TOTAL" ]},
                                                
                                                ],
                                            },
                                            "$t_g",
                                            0
                                        ]
                                    }
                                },
                                "tta_tt"          : {
                                    "$sum"                      : {
                                        "$cond"                 : [
                                            {
                                                '$or'          : [
                                                    {"$eq"      : ["$group", "TOTAL" ]},
                                                
                                                ],
                                            },
                                            "$t_a",
                                            0
                                        ]
                                    }
                                },
                            }
                        },
                    
                        {
                        "$project":{
                            
                            "g2_g"+product_value1['code'] : { "$cond": [ { "$eq": [ "$g2_"+product_value1['code'], 0 ] }, 0, {"$divide":["$g2_"+product_value1['code'], "$ttg_"+product_value1['code'] ]} ] },
                            "g21_g"+product_value1['code'] : { "$cond": [ { "$eq": [ "$g21_"+product_value1['code'], 0 ] }, 0, {"$divide":["$g21_"+product_value1['code'], "$ttg_"+product_value1['code'] ]} ] },
                            "g3_g"+product_value1['code'] : { "$cond": [ { "$eq": [ "$g3_"+product_value1['code'], 0 ] }, 0, {"$divide":["$g3_"+product_value1['code'], "$ttg_"+product_value1['code'] ]} ] },
                            "ttg_g2" : { "$cond": [ { "$eq": [ "$ttg_g2", 0 ] }, 0, {"$divide":["$ttg_g2", "$ttg_tt"]} ] },
                            "ttg_g21" : { "$cond": [ { "$eq": [ "$ttg_g21", 0 ] }, 0, {"$divide":["$ttg_g21", "$ttg_tt"]} ] },
                            "ttg_g3" : { "$cond": [ { "$eq": [ "$ttg_g3", 0 ] }, 0, {"$divide":["$ttg_g3", "$ttg_tt"]} ] },

                            "g2_a"+product_value1['code'] : { "$cond": [ { "$eq": [ "$a2_"+product_value1['code'], 0 ] }, 0, {"$divide":["$a2_"+product_value1['code'], "$tta_"+product_value1['code'] ]} ] },
                            "g21_a"+product_value1['code'] : { "$cond": [ { "$eq": [ "$a21_"+product_value1['code'], 0 ] }, 0, {"$divide":["$a21_"+product_value1['code'], "$tta_"+product_value1['code'] ]} ] },
                            "g3_a"+product_value1['code'] : { "$cond": [ { "$eq": [ "$a3_"+product_value1['code'], 0 ] }, 0, {"$divide":["$a3_"+product_value1['code'], "$tta_"+product_value1['code'] ]} ] },
                            "tta_g2" : { "$cond": [ { "$eq": [ "$tta_g2", 0 ] }, 0, {"$divide":["$tta_g2", "$tta_tt"]} ] },
                            "tta_g21" : { "$cond": [ { "$eq": [ "$tta_g21", 0 ] }, 0, {"$divide":["$tta_g21", "$tta_tt"]} ] },
                            "tta_g3" : { "$cond": [ { "$eq": [ "$tta_g3", 0 ] }, 0, {"$divide":["$tta_g3", "$tta_tt"]} ] },
                        }
                        }
                    
                        
                    ]
                
            total_report1 = list(mongodb.aggregate_pipeline(MONGO_COLLECTION=temp_collection,aggregate_pipeline=aggregate_total))        
            if total_report1 not in [None, []] and total_report1[0] is not None:

                if gr =="G2":
                    temp1['group'] = "G2"
                    temp1["g"+product_value1['code']] = str('{:05.2f}'.format(total_report1[0]["g2_g"+product_value1['code']]*100))+"%"  
                    temp1["a"+product_value1['code']] = str('{:05.2f}'.format(total_report1[0]["g2_a"+product_value1['code']]*100))+"%"
                    temp1['t_g'] = str('{:05.2f}'.format(total_report1[0]["ttg_g2"]*100)) +"%"
                    temp1['t_a'] = str('{:05.2f}'.format(total_report1[0]["tta_g2"]*100)) +"%"
                    temp1['index'] = 20
                    temp1['createdAt'] = time.time()
                elif gr == "G2~":
                    temp1['group'] = "G2~"
                    temp1["g"+product_value1['code']] = str('{:05.2f}'.format(total_report1[0]["g21_g"+product_value1['code']]*100))+"%"
                    temp1["a"+product_value1['code']] = str('{:05.2f}'.format(total_report1[0]["g21_a"+product_value1['code']]*100))+"%"
                    temp1['t_g'] = str('{:05.2f}'.format(total_report1[0]["ttg_g21"]*100)) +"%"
                    temp1['t_a'] = str('{:05.2f}'.format(total_report1[0]["tta_g21"]*100)) +"%"
                    temp1['index'] = 21
                    temp1['createdAt'] = time.time()
                elif gr =="G3~":
                    temp1['group'] = "G3~"
                    temp1["g"+product_value1['code']] = str('{:05.2f}'.format(total_report1[0]["g3_g"+product_value1['code']]*100))+"%" 
                    temp1["a"+product_value1['code']] = str('{:05.2f}'.format(total_report1[0]["g3_a"+product_value1['code']]*100))  +"%"
                    temp1['t_g'] = str('{:05.2f}'.format(total_report1[0]["ttg_g3"]*100)) +"%"
                    temp1['t_a'] = str('{:05.2f}'.format(total_report1[0]["tta_g3"]*100)) +"%"
                    temp1['index'] = 22
                    temp1['createdAt'] = time.time()
        # pprint(temp1)        
        insertData2.append(temp1)
    
    

    if len(insertData2) > 0:
        mongodb.batch_insert(MONGO_COLLECTION=collection1, insert_data=insertData2)
        # mongodb.remove_document(MONGO_COLLECTION=temp_collection)







    # export data to excel
    fileOutput  = base_url + 'upload/loan/export/ListofallcustomerReport_'+ dateExport +'.xlsx' 

    aggregate_acc = [
        {
          "$match":
          {
              "createdAt": {'$gte' : todayTimeStamp,'$lte' : endTodayTimeStamp},

          }
        },
        # { '$sort' : { '_id' : -1} },
        # { "$limit": 1000 },
        {
         "$project":
          {
              "_id": 0,
          }
        }
    ]
    data = mongodb.aggregate_pipeline(MONGO_COLLECTION=collection,aggregate_pipeline=aggregate_acc)


    df = pd.DataFrame(data, columns= ['DT_TX','ACC_ID','CUS_ID','CUS_NM','Loan_Group','CAR_ID','TERM_ID','W_ORG','TOTAL_ACC','TOTAL_CUS','PRODGRP_ID','LIC_NO','interest_rate','Dealer_code','Province_code'])
    writer = pd.ExcelWriter(fileOutput, engine='xlsxwriter')

    df.to_excel(writer,sheet_name='AllLoanGroup',header=['DT_TX','ACC_ID','CUS_ID','CUS_NM','Loan Group based on ZACCF and TB','CAR_ID based on ZACCF file','TERM_ID based on ZACCF file','W_ORG on ZACCF file and TB','Total No. of ACC','Total No. of customer','PRODGRP_ID','LIC_NO','Interest rate (%/year)','Dealer code','Province code'],index = False) 
    workbook  = writer.book
    worksheet = writer.sheets['AllLoanGroup']

    # Add some cell formats.
    format1 = workbook.add_format({'num_format': '#,##0', 'bottom':1, 'top':1, 'left':1, 'right':1})
    format2 = workbook.add_format({'num_format': '0.00%','bottom':1, 'top':1, 'left':1, 'right':1})
    border_fmt = workbook.add_format({'bottom':1, 'top':1, 'left':1, 'right':1})
    header_fmt = workbook.add_format({'bottom':1, 'top':1, 'left':1, 'right':1, 'bold': True, 'fg_color': '#008738','font_color': '#ffffff','text_wrap': True,})
    date_fmt = workbook.add_format({'num_format': 'dd/mm/yy'})

    worksheet.set_column('A:O', 20, border_fmt)
    worksheet.set_column('M:M', 20, format2)
    worksheet.set_column('H:H', 30, format1)

    writer.save()




    now_end         = datetime.now()
    log.write(now_end.strftime("%d/%m/%Y, %H:%M:%S") + ': End Log' + '\n')
    print('DONE')
except Exception as e:
    pprint(e)
    traceback.print_exc()
    log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')

   

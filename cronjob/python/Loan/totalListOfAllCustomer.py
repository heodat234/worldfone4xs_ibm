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
common      = Common()
base_url    = common.base_url()
wff_env     = common.wff_env(base_url)
mongodb     = Mongodb(MONGODB="worldfone4xs", WFF_ENV=wff_env)
_mongodb    = Mongodb(MONGODB="_worldfone4xs", WFF_ENV=wff_env)
now         = datetime.now()
subUserType = 'LO'

temp_collection           = common.getSubUser(subUserType, 'List_of_all_customer_total_report_temp')
collection1          = common.getSubUser(subUserType, 'List_of_all_customer_total_report')

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
    insertData1  = []
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

    holidayOfMonth = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_off_sys'))
    listHoliday = map(lambda offDateRow: {offDateRow['off_date']}, holidayOfMonth)

    if day != 1:
      print('stop!')  
      sys.exit()

    users = _mongodb.get(MONGO_COLLECTION=user_collection, SELECT=['extension','agentname'],SORT=([('_id', -1)]),SKIP=0, TAKE=200)
    
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
                
            total_report = list(mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'List_of_all_customer_total_report_temp'),aggregate_pipeline=aggregate_sum))
            if total_report not in [None, []] and total_report[0] is not None:
                temp['group'] = group
                temp["g"+product_value['code']] = total_report[0]["g"+product_value['code']]
                temp["t_g"] = total_report[0]["t_g"]
                temp["t_a"] = total_report[0]["t_a"]
                temp["a"+product_value['code']] = total_report[0]["a"+product_value['code']]
                temp['index'] = i 
                temp['createdAt'] = time.time()
        insertData.append(temp)
    

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
                
            total_report1 = list(mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'List_of_all_customer_total_report_temp'),aggregate_pipeline=aggregate_total))        
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
        insertData.append(temp1)
    
    

    if len(insertData) > 0:
    
      mongodb.batch_insert(MONGO_COLLECTION=collection1, insert_data=insertData)
      mongodb.remove_document(MONGO_COLLECTION=temp_collection)
    now_end         = datetime.now()
    log.write(now_end.strftime("%d/%m/%Y, %H:%M:%S") + ': End Log' + '\n')
      
    print('DONE')
except Exception as e:
    pprint(e)
    traceback.print_exc()
    log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')

   

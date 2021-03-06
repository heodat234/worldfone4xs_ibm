#!/usr/bin/python3.6
# -*- coding: utf-8 -*-
import sys
import os
import time
import ntpath
import json
import calendar
import traceback
from helper.mongod import Mongodb
from datetime import datetime
from datetime import date,timedelta
from dateutil.relativedelta import relativedelta
from pprint import pprint
from bson import ObjectId
from helper.common import Common
from helper.jaccs import Config

common      = Common()
base_url    = common.base_url()
wff_env     = common.wff_env(base_url)
mongodb     = Mongodb(MONGODB="worldfone4xs", WFF_ENV=wff_env)
_mongodb    = Mongodb(MONGODB="_worldfone4xs", WFF_ENV=wff_env)
now         = datetime.now()
subUserType = 'LO'
collection         = common.getSubUser(subUserType, 'Last_past_year_arrears_occurrence_report')
zaccf_collection   = common.getSubUser(subUserType, 'ZACCF_report')
product_group_collection   = common.getSubUser(subUserType, 'Product_group')



log         = open(base_url + "cronjob/python/Loan/log/Last_past_year_arrears_occurrence_log.txt","a")
log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': Start Import' + '\n')

try:
   data        = []
   insertData  = []
   totalCode   = []

   today = date.today()
   # today = datetime.strptime('1/02/2020', "%d/%m/%Y").date()

   day = today.day
   todayString = today.strftime("%d/%m/%Y")
   todayTimeStamp = int(time.mktime(time.strptime(str(todayString + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
   endTodayTimeStamp = int(time.mktime(time.strptime(str(todayString + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))

   if day != 1:
      sys.exit()

   mongodb.remove_document(MONGO_COLLECTION=collection, WHERE={'createdAt': {'$gte': todayTimeStamp, '$lte': endTodayTimeStamp} })

   # tru 1 ngay de tinh cho thang truoc 
   today = today - timedelta(days=1)

   day = today.day
   month = today.month
   year = today.year
   weekday = today.weekday()
   lastDayOfMonth = calendar.monthrange(year, month)[1]

   todayString = today.strftime("%d/%m/%Y")
   endTodayTimeStamp = int(time.mktime(time.strptime(str(todayString + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))

   startMonth = int(time.mktime(time.strptime(str('01/' + str(month) + '/' + str(year) + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
   endMonth = int(time.mktime(time.strptime(str(str(lastDayOfMonth) + '/' + str(month) + '/' + str(year) + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))


   

   # 1 thang truoc
   last_one_months = today - relativedelta(months=1)
   month_last_one_months   = last_one_months.month
   year_last_one_months    = last_one_months.year
   lastDayOfOneMonth       = calendar.monthrange(year_last_one_months, month_last_one_months)[1]
   endOneMonth             = int(time.mktime(time.strptime(str(str(lastDayOfOneMonth) + '/' + str(month_last_one_months) + '/' + str(year_last_one_months) + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))


   # 6 thang truoc
   last_six_months         = today - relativedelta(months=6)
   last_six_months         = last_six_months.replace(day=1)
   last_six_monthsString   = last_six_months.strftime("%d/%m/%Y")
   last_six_monthsTimeStamp = int(time.mktime(time.strptime(str(last_six_monthsString + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
   month_last_six_months   = last_six_months.month
   year_last_six_months    = last_six_months.year
   lastDayOfSixMonth       = calendar.monthrange(year_last_six_months, month_last_six_months)[1]

   startSixMonth           = int(time.mktime(time.strptime(str('01/' + str(month_last_six_months) + '/' + str(year_last_six_months) + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
   endSixMonth             = int(time.mktime(time.strptime(str(str(lastDayOfSixMonth) + '/' + str(month_last_six_months) + '/' + str(year_last_six_months) + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))

   # 7 thang truoc
   last_7_months = today - relativedelta(months=7)
   month_last_7_months     = last_7_months.month
   year_last_7_months      = last_7_months.year
   lastDayOf7Month         = calendar.monthrange(year_last_7_months, month_last_7_months)[1]
   end7Month               = int(time.mktime(time.strptime(str(str(lastDayOf7Month) + '/' + str(month_last_7_months) + '/' + str(year_last_7_months) + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))


   # 1 nam truoc
   last_year                  = today - relativedelta(years=1)
   last_year                  = last_year.replace(day=1)
   last_yearString            = last_year.strftime("%d/%m/%Y")
   last_yearTimeStamp         = int(time.mktime(time.strptime(str(last_yearString + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
   month_last_year            = last_year.month
   year_last_year             = last_year.year
   lastDayOfSixMonthLastYear  = calendar.monthrange(year_last_year, month_last_year)[1]

   startMonthLastYear      = int(time.mktime(time.strptime(str('01/' + str(month_last_year) + '/' + str(year_last_year) + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
   endSixMonthLastYear     = int(time.mktime(time.strptime(str(str(lastDayOfSixMonthLastYear) + '/' + str(month_last_year) + '/' + str(year_last_year) + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))



   # 13 thang truoc
   last_13_months = today - relativedelta(months=13)
   month_last_13_months    = last_13_months.month
   year_last_13_months     = last_13_months.year
   lastDayOf13Month        = calendar.monthrange(year_last_13_months, month_last_13_months)[1]
   endLast13Month          = int(time.mktime(time.strptime(str(str(lastDayOf13Month) + '/' + str(month_last_13_months) + '/' + str(year_last_13_months) + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))


   # 2 nam truoc
   last_two_year          = today - relativedelta(years=2)
   last_two_year          = last_two_year.replace(day=1)
   last_two_yearString    = last_two_year.strftime("%d/%m/%Y")
   last_two_yearTimeStamp = int(time.mktime(time.strptime(str(last_two_yearString + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
   month_last_two_year    = last_two_year.month
   year_last_two_year     = last_two_year.year
   lastDayOfSixMonthTwoyear      = calendar.monthrange(year_last_two_year, month_last_two_year)[1]

   startMonthLastTwoYear  = int(time.mktime(time.strptime(str('01/' + str(month_last_two_year) + '/' + str(year_last_two_year) + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))




   aggregate = [
      {
           "$match":
           {
               'group_code' : {'$nin' : ['300']},
           }
      },{
        "$group":
        {
            "_id": '$group_code',
            "group_name": {'$last' : '$group_name'},
            "code": {'$push' : '$product_code.code'}
        }
      }
   ]
   groupData = mongodb.aggregate_pipeline(MONGO_COLLECTION=product_group_collection,aggregate_pipeline=aggregate)
   if groupData != None:
      for group in groupData:
         p_type  = group['group_name']
         pprint(p_type)
         code = []
         for product_code in group['code']:
            code = product_code

         totalCode += code

         # TOTAL
         aggregate = [
             {
                 "$match":
                 {
                     'PRODGRP_ID' : {'$in' : code},
                     'W_ORG_1': {'$gt': 0},
                     # '$or' : [ { "createdAt" : {'$gte' : todayTimeStamp,'$lte' : endTodayTimeStamp} }, {"updatedAt" : {'$gte' : todayTimeStamp,'$lte' : endTodayTimeStamp} }],
                     "FRELD8_BJ" : {'$gte' : startMonth,'$lte' : endMonth} 
                 }
             },
             {
                 "$group":
                 {
                     "_id": 'null',
                     "total_account": {'$sum' : 1},
                     "total_w_org": {'$sum' : '$W_ORG_1'},
                 }
             }
         ]
         accData = mongodb.aggregate_pipeline(MONGO_COLLECTION=zaccf_collection,aggregate_pipeline=aggregate)
         total_w_org    = 0
         total_account  = 0
         if accData != None:
            for row in accData:
               total_account  = row['total_account']
               total_w_org    = row['total_w_org']

         temp = {
            'daily'                 : 'true',
            'name'                  : '',
            'type'                  : p_type,
            'index'                 : 1,
            'sales_period'          : str(year) + '/' + str(month),
            'total_w_org'           : total_w_org,
            'total_account'         : total_account,
            'w_org_group_b'         : 0,
            'account_group_b'       : 0,
            'group_b_ratio'         : 0,
            'w_org_group_c'         : 0,
            'account_group_c'       : 0,
            'group_c_ratio'         : 0,
            'w_org_group_c_over'    : 0,
            'account_group_c_over'  : 0,
            'group_c_over_ratio'    : 0,
            'year'                  : year,
            'for_month'             : month,
            'createdAt'             : todayTimeStamp,
            'createdBy'             : 'system',
         }
         insertData.append(temp)



         # Last 6 month
         # TOTAL
         aggregate = [
             {
                 "$match":
                 {
                     'PRODGRP_ID' : {'$in' : code},
                     'W_ORG_1'   : {'$gt': 0},
                     "FRELD8_BJ" : {'$gte' : startSixMonth,'$lte' : endOneMonth},
                 }
             },
             {
                 "$group":
                 {
                     "_id": 'null',
                     "total_account": {'$sum' : 1},
                     "total_w_org": {'$sum' : '$W_ORG_1'},
                 }
             }
         ]
         accData = mongodb.aggregate_pipeline(MONGO_COLLECTION=zaccf_collection,aggregate_pipeline=aggregate)
         total_w_org_6    = 0
         total_account_6  = 0
         if accData != None:
            for row in accData:
               total_account_6  = row['total_account']
               total_w_org_6    = row['total_w_org']



         # GROUP B
         aggregate = [
             {
                 "$match":
                 {
                     'ODIND_FG' : 'B',
                     'W_ORG_1'   : {'$gt': 0},
                     'PRODGRP_ID' : {'$in' : code},
                     "FRELD8_BJ" : {'$gte' : startSixMonth,'$lte' : endOneMonth},
                 }
             },
             {
                 "$group":
                 {
                     "_id": 'null',
                     "total_account": {'$sum' : 1},
                     "total_w_org": {'$sum' : '$W_ORG_1'},
                 }
             }
         ]
         accData = mongodb.aggregate_pipeline(MONGO_COLLECTION=zaccf_collection,aggregate_pipeline=aggregate)
         w_org_group_b_6   = 0
         account_group_b_6  = 0
         if accData != None:
            for row in accData:
               account_group_b_6  = row['total_account']
               w_org_group_b_6    = row['total_w_org']



         # GROUP C
         aggregate = [
             {
                 "$match":
                 {
                     'ODIND_FG' : 'C',
                     'W_ORG_1'   : {'$gt': 0},
                     'PRODGRP_ID' : {'$in' : code},
                     "FRELD8_BJ" : {'$gte' : startSixMonth,'$lte' : endOneMonth},
                 }
             },
             {
                 "$group":
                 {
                     "_id": 'null',
                     "total_account": {'$sum' : 1},
                     "total_w_org": {'$sum' : '$W_ORG_1'},
                 }
             }
         ]
         accData = mongodb.aggregate_pipeline(MONGO_COLLECTION=zaccf_collection,aggregate_pipeline=aggregate)
         w_org_group_c_6   = 0
         account_group_c_6  = 0
         if accData != None:
            for row in accData:
               account_group_c_6  = row['total_account']
               w_org_group_c_6    = row['total_w_org']



         # GROUP C+
         aggregate = [
             {
                 "$match":
                 {
                     'W_ORG_1'   : {'$gt': 0},
                     'ODIND_FG' : {'$in' : ['C','D','E']},
                     'PRODGRP_ID' : {'$in' : code},
                     "FRELD8_BJ" : {'$gte' : startSixMonth,'$lte' : endOneMonth},
                 }
             },
             {
                 "$group":
                 {
                     "_id": 'null',
                     "total_account": {'$sum' : 1},
                     "total_w_org": {'$sum' : '$W_ORG_1'},
                 }
             }
         ]
         accData = mongodb.aggregate_pipeline(MONGO_COLLECTION=zaccf_collection,aggregate_pipeline=aggregate)
         w_org_group_c_over_6   = 0
         account_group_c_over_6  = 0
         if accData != None:
            for row in accData:
               account_group_c_over_6  = row['total_account']
               w_org_group_c_over_6    = row['total_w_org']

         


         temp_6_month = {
            'name'                  : 'Last 6 months',
            'type'                  : p_type,
            'index'                 : 2,
            'sales_period'          : str(year_last_six_months) + '/' + str(month_last_six_months) + '~' + str(year_last_one_months) + '/' + str(month_last_one_months),
            'total_w_org'           : total_w_org_6,
            'total_account'         : total_account_6,
            'w_org_group_b'         : w_org_group_b_6,
            'account_group_b'       : account_group_b_6,
            'group_b_ratio'         : w_org_group_b_6/total_w_org_6 if total_w_org_6 != 0 else 0,
            'w_org_group_c'         : w_org_group_c_6,
            'account_group_c'       : account_group_c_6,
            'group_c_ratio'         : w_org_group_c_6/total_w_org_6 if total_w_org_6 != 0 else 0,
            'w_org_group_c_over'    : w_org_group_c_over_6,
            'account_group_c_over'  : account_group_c_over_6,
            'group_c_over_ratio'    : w_org_group_c_over_6/total_w_org_6 if total_w_org_6 != 0 else 0,
            'for_month'             : month,
            'year'                  : year,
            'createdAt'             : todayTimeStamp,
            'createdBy'             : 'system',
         }
         insertData.append(temp_6_month)






         # Last 12~7 month
         # TOTAL
         aggregate = [
             {
                 "$match":
                 {
                     'W_ORG_1'   : {'$gt': 0},
                     'PRODGRP_ID' : {'$in' : code},
                     "FRELD8_BJ" : {'$gte' : startMonthLastYear,'$lte' : end7Month},
                 }
             },
             {
                 "$group":
                 {
                     "_id": 'null',
                     "total_account": {'$sum' : 1},
                     "total_w_org": {'$sum' : '$W_ORG_1'},
                 }
             }
         ]
         accData = mongodb.aggregate_pipeline(MONGO_COLLECTION=zaccf_collection,aggregate_pipeline=aggregate)
         total_w_org_127    = 0
         total_account_127  = 0
         if accData != None:
            for row in accData:
               total_account_127  = row['total_account']
               total_w_org_127    = row['total_w_org']



         # GROUP B
         aggregate = [
             {
                 "$match":
                 {
                     'ODIND_FG' : 'B',
                     'W_ORG_1'   : {'$gt': 0},
                     'PRODGRP_ID' : {'$in' : code},
                     "FRELD8_BJ" : {'$gte' : startMonthLastYear,'$lte' : end7Month},
                 }
             },
             {
                 "$group":
                 {
                     "_id": 'null',
                     "total_account": {'$sum' : 1},
                     "total_w_org": {'$sum' : '$W_ORG_1'},
                 }
             }
         ]
         accData = mongodb.aggregate_pipeline(MONGO_COLLECTION=zaccf_collection,aggregate_pipeline=aggregate)
         w_org_group_b_127   = 0
         account_group_b_127  = 0
         if accData != None:
            for row in accData:
               account_group_b_127  = row['total_account']
               w_org_group_b_127    = row['total_w_org']



         # GROUP C
         aggregate = [
             {
                 "$match":
                 {
                     'ODIND_FG' : 'C',
                     'W_ORG_1'   : {'$gt': 0},
                     'PRODGRP_ID' : {'$in' : code},
                     "FRELD8_BJ" : {'$gte' : startMonthLastYear,'$lte' : end7Month},
                 }
             },
             {
                 "$group":
                 {
                     "_id": 'null',
                     "total_account": {'$sum' : 1},
                     "total_w_org": {'$sum' : '$W_ORG_1'},
                 }
             }
         ]
         accData = mongodb.aggregate_pipeline(MONGO_COLLECTION=zaccf_collection,aggregate_pipeline=aggregate)
         w_org_group_c_127   = 0
         account_group_c_127  = 0
         if accData != None:
            for row in accData:
               account_group_c_127  = row['total_account']
               w_org_group_c_127    = row['total_w_org']



         # GROUP C+
         aggregate = [
             {
                 "$match":
                 {
                     'ODIND_FG' : {'$in' : ['C','D','E']},
                     'W_ORG_1'   : {'$gt': 0},
                     'PRODGRP_ID' : {'$in' : code},
                     "FRELD8_BJ" : {'$gte' : startMonthLastYear,'$lte' : end7Month},
                 }
             },
             {
                 "$group":
                 {
                     "_id": 'null',
                     "total_account": {'$sum' : 1},
                     "total_w_org": {'$sum' : '$W_ORG_1'},
                 }
             }
         ]
         accData = mongodb.aggregate_pipeline(MONGO_COLLECTION=zaccf_collection,aggregate_pipeline=aggregate)
         w_org_group_c_over_127   = 0
         account_group_c_over_127  = 0
         if accData != None:
            for row in accData:
               account_group_c_over_127  = row['total_account']
               w_org_group_c_over_127    = row['total_w_org']


         temp_12_7_month = {
            'name'                  : 'Last 12～7 month',
            'type'                  : p_type,
            'index'                 : 3,
            'sales_period'          : str(year_last_year) + '/' + str(month_last_year) + '~' + str(year_last_7_months) + '/' + str(month_last_7_months),
            'total_w_org'           : total_w_org_127,
            'total_account'         : total_account_127,
            'w_org_group_b'         : w_org_group_b_127,
            'account_group_b'       : account_group_b_127,
            'group_b_ratio'         : w_org_group_b_127/total_w_org_127 if total_w_org_127 != 0 else 0,
            'w_org_group_c'         : w_org_group_c_127,
            'account_group_c'       : account_group_c_127,
            'group_c_ratio'         : w_org_group_c_127/total_w_org_127 if total_w_org_127 != 0 else 0,
            'w_org_group_c_over'    : w_org_group_c_over_127,
            'account_group_c_over'  : account_group_c_over_127,
            'group_c_over_ratio'    : w_org_group_c_over_127/total_w_org_127 if total_w_org_127 != 0 else 0,
            'for_month'             : month,
            'year'                  : year,
            'createdAt'             : todayTimeStamp,
            'createdBy'             : 'system',
         }
         insertData.append(temp_12_7_month)





         # Last 12 month
         # TOTAL
         aggregate = [
             {
                 "$match":
                 {
                     'W_ORG_1'   : {'$gt': 0},
                     'PRODGRP_ID' : {'$in' : code},
                     "FRELD8_BJ" : {'$gte' : startMonthLastTwoYear,'$lte' : endLast13Month},
                 }
             },
             {
                 "$group":
                 {
                     "_id": 'null',
                     "total_account": {'$sum' : 1},
                     "total_w_org": {'$sum' : '$W_ORG_1'},
                 }
             }
         ]
         accData = mongodb.aggregate_pipeline(MONGO_COLLECTION=zaccf_collection,aggregate_pipeline=aggregate)
         total_w_org_last_12    = 0
         total_account_last_12  = 0
         if accData != None:
            for row in accData:
               total_account_last_12  = row['total_account']
               total_w_org_last_12    = row['total_w_org']



         # GROUP B
         aggregate = [
             {
                 "$match":
                 {
                     'ODIND_FG' : 'B',
                     'W_ORG_1'   : {'$gt': 0},
                     'PRODGRP_ID' : {'$in' : code},
                     "FRELD8_BJ" : {'$gte' : startMonthLastTwoYear,'$lte' : endLast13Month},
                 }
             },
             {
                 "$group":
                 {
                     "_id": 'null',
                     "total_account": {'$sum' : 1},
                     "total_w_org": {'$sum' : '$W_ORG_1'},
                 }
             }
         ]
         accData = mongodb.aggregate_pipeline(MONGO_COLLECTION=zaccf_collection,aggregate_pipeline=aggregate)
         w_org_group_b_last_12      = 0
         account_group_b_last_12    = 0
         if accData != None:
            for row in accData:
               account_group_b_last_12  = row['total_account']
               w_org_group_b_last_12    = row['total_w_org']



         # GROUP C
         aggregate = [
             {
                 "$match":
                 {
                     'ODIND_FG' : 'C',
                     'W_ORG_1'   : {'$gt': 0},
                     'PRODGRP_ID' : {'$in' : code},
                     "FRELD8_BJ" : {'$gte' : startMonthLastTwoYear,'$lte' : endLast13Month},
                 }
             },
             {
                 "$group":
                 {
                     "_id": 'null',
                     "total_account": {'$sum' : 1},
                     "total_w_org": {'$sum' : '$W_ORG_1'},
                 }
             }
         ]
         accData = mongodb.aggregate_pipeline(MONGO_COLLECTION=zaccf_collection,aggregate_pipeline=aggregate)
         w_org_group_c_last_12      = 0
         account_group_c_last_12    = 0
         if accData != None:
            for row in accData:
               account_group_c_last_12  = row['total_account']
               w_org_group_c_last_12    = row['total_w_org']



         # GROUP C+
         aggregate = [
             {
                 "$match":
                 {
                     'ODIND_FG' : {'$in' : ['C','D','E']},
                     'W_ORG_1'   : {'$gt': 0},
                     'PRODGRP_ID' : {'$in' : code},
                     "FRELD8_BJ" : {'$gte' : startMonthLastTwoYear,'$lte' : endLast13Month},
                 }
             },
             {
                 "$group":
                 {
                     "_id": 'null',
                     "total_account": {'$sum' : 1},
                     "total_w_org": {'$sum' : '$W_ORG_1'},
                 }
             }
         ]
         accData = mongodb.aggregate_pipeline(MONGO_COLLECTION=zaccf_collection,aggregate_pipeline=aggregate)
         w_org_group_c_over_last_12    = 0
         account_group_c_over_last_12  = 0
         if accData != None:
            for row in accData:
               account_group_c_over_last_12  = row['total_account']
               w_org_group_c_over_last_12    = row['total_w_org']


         temp_last_12_month = {
            'name'                  : 'Last 12 month',
            'type'                  : p_type,
            'index'                 : 4,
            'sales_period'          : str(year_last_two_year) + '/' + str(month_last_two_year) + '~' + str(year_last_13_months) + '/' + str(month_last_13_months),
            'total_w_org'           : total_w_org_last_12,
            'total_account'         : total_account_last_12,
            'w_org_group_b'         : w_org_group_b_last_12,
            'account_group_b'       : account_group_b_last_12,
            'group_b_ratio'         : w_org_group_b_last_12/total_w_org_last_12 if total_w_org_last_12 != 0 else 0,
            'w_org_group_c'         : w_org_group_c_last_12,
            'account_group_c'       : account_group_c_last_12,
            'group_c_ratio'         : w_org_group_c_last_12/total_w_org_last_12 if total_w_org_last_12 != 0 else 0,
            'w_org_group_c_over'    : w_org_group_c_over_last_12,
            'account_group_c_over'  : account_group_c_over_last_12,
            'group_c_over_ratio'    : w_org_group_c_over_last_12/total_w_org_last_12 if total_w_org_last_12 != 0 else 0,
            'for_month'             : month,
            'year'                  : year,
            'createdAt'             : todayTimeStamp,
            'createdBy'             : 'system',
         }
         insertData.append(temp_last_12_month)




         # Total
         # TOTAL
         aggregate = [
            {
                 "$match":
                 {
                     'W_ORG_1'   : {'$gt': 0},
                     'PRODGRP_ID' : {'$in' : code},
                 }
            },
            {
                 "$group":
                 {
                     "_id": 'null',
                     "total_account": {'$sum' : 1},
                     "total_w_org": {'$sum' : '$W_ORG_1'},
                 }
            }
         ]
         accData = mongodb.aggregate_pipeline(MONGO_COLLECTION=zaccf_collection,aggregate_pipeline=aggregate)
         total_w_org_total    = 0
         total_account_total  = 0
         if accData != None:
            for row in accData:
               total_account_total  = row['total_account']
               total_w_org_total    = row['total_w_org']


         # GROUP B
         aggregate = [
            {
                 "$match":
                 {
                     'ODIND_FG' : 'B',
                     'W_ORG_1'   : {'$gt': 0},
                     'PRODGRP_ID' : {'$in' : code},
                 }
            },
            {
                 "$group":
                 {
                     "_id": 'null',
                     "total_account": {'$sum' : 1},
                     "total_w_org": {'$sum' : '$W_ORG_1'},
                 }
            }
         ]
         accData = mongodb.aggregate_pipeline(MONGO_COLLECTION=zaccf_collection,aggregate_pipeline=aggregate)
         w_org_group_b_total   = 0
         account_group_b_total  = 0
         if accData != None:
            for row in accData:
               account_group_b_total  = row['total_account']
               w_org_group_b_total    = row['total_w_org']


         # GROUP C
         aggregate = [
            {
                 "$match":
                 {
                     'ODIND_FG' : 'C',
                     'W_ORG_1'   : {'$gt': 0},
                     'PRODGRP_ID' : {'$in' : code},
                 }
            },
            {
                 "$group":
                 {
                     "_id": 'null',
                     "total_account": {'$sum' : 1},
                     "total_w_org": {'$sum' : '$W_ORG_1'},
                 }
            }
         ]
         accData = mongodb.aggregate_pipeline(MONGO_COLLECTION=zaccf_collection,aggregate_pipeline=aggregate)
         w_org_group_c_total   = 0
         account_group_c_total  = 0
         if accData != None:
            for row in accData:
               account_group_c_total  = row['total_account']
               w_org_group_c_total    = row['total_w_org']


         # GROUP C+
         aggregate = [
            {
                 "$match":
                 {
                     'ODIND_FG' : {'$in' : ['C','D','E']},
                     'W_ORG_1'   : {'$gt': 0},
                     'PRODGRP_ID' : {'$in' : code},
                 }
            },
            {
                 "$group":
                 {
                     "_id": 'null',
                     "total_account": {'$sum' : 1},
                     "total_w_org": {'$sum' : '$W_ORG_1'},
                 }
            }
         ]
         accData = mongodb.aggregate_pipeline(MONGO_COLLECTION=zaccf_collection,aggregate_pipeline=aggregate)
         w_org_group_c_over_total   = 0
         account_group_c_over_total = 0
         if accData != None:
            for row in accData:
               account_group_c_over_total  = row['total_account']
               w_org_group_c_over_total    = row['total_w_org']


         temp_total = {
            'name'                  : 'TOTAL',
            'sales_period'          : '',
            'type'                  : p_type,
            'index'                 : 6,
            'total_w_org'           : total_w_org_total,
            'total_account'         : total_account_total,
            'w_org_group_b'         : w_org_group_b_total,
            'account_group_b'       : account_group_b_total,
            'group_b_ratio'         : w_org_group_b_total/total_w_org_total if total_w_org_total != 0 else 0,
            'w_org_group_c'         : w_org_group_c_total,
            'account_group_c'       : account_group_c_total,
            'group_c_ratio'         : w_org_group_c_total/total_w_org_total if total_w_org_total != 0 else 0,
            'w_org_group_c_over'    : w_org_group_c_over_total,
            'account_group_c_over'  : account_group_c_over_total,
            'group_c_over_ratio'    : w_org_group_c_over_total/total_w_org_total if total_w_org_total != 0 else 0,
            'for_month'             : month,
            'year'                  : year,
            'createdAt'             : todayTimeStamp,
            'createdBy'             : 'system',
         }
         insertData.append(temp_total)



         # Other than that
         total_w_org_other = total_w_org_total - total_w_org - total_w_org_6 - total_w_org_127 - total_w_org_last_12
         temp_other_than = {
            'name'                  : 'Other than that',
            'type'                  : p_type,
            'index'                 : 5,
            'sales_period'          : '~' + str(year_last_13_months) + '/' + str(month_last_13_months),
            'total_w_org'           : total_w_org_other,
            'total_account'         : total_account_total - total_account - total_account_6 - total_account_127 - total_account_last_12,
            'w_org_group_b'         : w_org_group_b_total - w_org_group_b_6 - w_org_group_b_127 - w_org_group_b_last_12,
            'account_group_b'       : account_group_b_total - account_group_b_6 - account_group_b_127 - account_group_b_last_12,
            'group_b_ratio'         : (w_org_group_b_total - w_org_group_b_6 - w_org_group_b_127- w_org_group_b_last_12)/total_w_org_other if total_w_org_other != 0 else 0,
            'w_org_group_c'         : w_org_group_c_total - w_org_group_c_6 - w_org_group_c_127 - w_org_group_c_last_12,
            'account_group_c'       : account_group_c_total - account_group_c_6 - account_group_c_127 - account_group_c_last_12,
            'group_c_ratio'         : (w_org_group_c_total - w_org_group_c_6 - w_org_group_c_127 - w_org_group_c_last_12)/total_w_org_other if total_w_org_other != 0 else 0,
            'w_org_group_c_over'    : w_org_group_c_over_total - w_org_group_c_over_6 - w_org_group_c_over_127 - w_org_group_c_over_last_12,
            'account_group_c_over'  : account_group_c_over_total - account_group_c_over_6 - account_group_c_over_127 - account_group_c_over_last_12,
            'group_c_over_ratio'    : (w_org_group_c_over_total - w_org_group_c_over_6 - w_org_group_c_over_127 - w_org_group_c_over_last_12)/total_w_org_other if total_w_org_other != 0 else 0,
            'for_month'             : month,
            'year'                  : year,
            'createdAt'             : todayTimeStamp,
            'createdBy'             : 'system',
         }
         insertData.append(temp_other_than)











   # Sheet Total
   p_type   = 'Total'
   code     = totalCode
   pprint(p_type)
   # TOTAL
   aggregate = [
       {
           "$match":
           {
               'PRODGRP_ID' : {'$in' : code},
               'W_ORG_1': {'$gt': 0},
               "FRELD8_BJ" : {'$gte' : startMonth,'$lte' : endMonth} 
           }
       },
       {
           "$group":
           {
               "_id": 'null',
               "total_account": {'$sum' : 1},
               "total_w_org": {'$sum' : '$W_ORG_1'},
           }
       }
   ]
   accData = mongodb.aggregate_pipeline(MONGO_COLLECTION=zaccf_collection,aggregate_pipeline=aggregate)
   total_w_org    = 0
   total_account  = 0
   if accData != None:
      for row in accData:
         total_account  = row['total_account']
         total_w_org    = row['total_w_org']

   temp = {
      'daily'                 : 'true',
      'name'                  : '',
      'type'                  : p_type,
      'index'                 : 1,
      'sales_period'          : str(year) + '/' + str(month),
      'total_w_org'           : total_w_org,
      'total_account'         : total_account,
      'w_org_group_b'         : 0,
      'account_group_b'       : 0,
      'group_b_ratio'         : 0,
      'w_org_group_c'         : 0,
      'account_group_c'       : 0,
      'group_c_ratio'         : 0,
      'w_org_group_c_over'    : 0,
      'account_group_c_over'  : 0,
      'group_c_over_ratio'    : 0,
      'year'                  : year,
      'for_month'             : month,
      'createdAt'             : todayTimeStamp,
      'createdBy'             : 'system',
   }
   insertData.append(temp)



   # Last 6 month
   # TOTAL
   aggregate = [
       {
           "$match":
           {
               'PRODGRP_ID' : {'$in' : code},
               'W_ORG_1'   : {'$gt': 0},
               "FRELD8_BJ" : {'$gte' : startSixMonth,'$lte' : endOneMonth},
           }
       },
       {
           "$group":
           {
               "_id": 'null',
               "total_account": {'$sum' : 1},
               "total_w_org": {'$sum' : '$W_ORG_1'},
           }
       }
   ]
   accData = mongodb.aggregate_pipeline(MONGO_COLLECTION=zaccf_collection,aggregate_pipeline=aggregate)
   total_w_org_6    = 0
   total_account_6  = 0
   if accData != None:
      for row in accData:
         total_account_6  = row['total_account']
         total_w_org_6    = row['total_w_org']



   # GROUP B
   aggregate = [
       {
           "$match":
           {
               'ODIND_FG' : 'B',
               'W_ORG_1'   : {'$gt': 0},
               'PRODGRP_ID' : {'$in' : code},
               "FRELD8_BJ" : {'$gte' : startSixMonth,'$lte' : endOneMonth},
           }
       },
       {
           "$group":
           {
               "_id": 'null',
               "total_account": {'$sum' : 1},
               "total_w_org": {'$sum' : '$W_ORG_1'},
           }
       }
   ]
   accData = mongodb.aggregate_pipeline(MONGO_COLLECTION=zaccf_collection,aggregate_pipeline=aggregate)
   w_org_group_b_6   = 0
   account_group_b_6  = 0
   if accData != None:
      for row in accData:
         account_group_b_6  = row['total_account']
         w_org_group_b_6    = row['total_w_org']



   # GROUP C
   aggregate = [
       {
           "$match":
           {
               'ODIND_FG' : 'C',
               'W_ORG_1'   : {'$gt': 0},
               'PRODGRP_ID' : {'$in' : code},
               "FRELD8_BJ" : {'$gte' : startSixMonth,'$lte' : endOneMonth},
           }
       },
       {
           "$group":
           {
               "_id": 'null',
               "total_account": {'$sum' : 1},
               "total_w_org": {'$sum' : '$W_ORG_1'},
           }
       }
   ]
   accData = mongodb.aggregate_pipeline(MONGO_COLLECTION=zaccf_collection,aggregate_pipeline=aggregate)
   w_org_group_c_6   = 0
   account_group_c_6  = 0
   if accData != None:
      for row in accData:
         account_group_c_6  = row['total_account']
         w_org_group_c_6    = row['total_w_org']



   # GROUP C+
   aggregate = [
       {
           "$match":
           {
               'W_ORG_1'   : {'$gt': 0},
               'ODIND_FG' : {'$in' : ['C','D','E']},
               'PRODGRP_ID' : {'$in' : code},
               "FRELD8_BJ" : {'$gte' : startSixMonth,'$lte' : endOneMonth},
           }
       },
       {
           "$group":
           {
               "_id": 'null',
               "total_account": {'$sum' : 1},
               "total_w_org": {'$sum' : '$W_ORG_1'},
           }
       }
   ]
   accData = mongodb.aggregate_pipeline(MONGO_COLLECTION=zaccf_collection,aggregate_pipeline=aggregate)
   w_org_group_c_over_6   = 0
   account_group_c_over_6  = 0
   if accData != None:
      for row in accData:
         account_group_c_over_6  = row['total_account']
         w_org_group_c_over_6    = row['total_w_org']

   


   temp_6_month = {
      'name'                  : 'Last 6 months',
      'type'                  : p_type,
      'index'                 : 2,
      'sales_period'          : str(year_last_six_months) + '/' + str(month_last_six_months) + '~' + str(year_last_one_months) + '/' + str(month_last_one_months),
      'total_w_org'           : total_w_org_6,
      'total_account'         : total_account_6,
      'w_org_group_b'         : w_org_group_b_6,
      'account_group_b'       : account_group_b_6,
      'group_b_ratio'         : w_org_group_b_6/total_w_org_6 if total_w_org_6 != 0 else 0,
      'w_org_group_c'         : w_org_group_c_6,
      'account_group_c'       : account_group_c_6,
      'group_c_ratio'         : w_org_group_c_6/total_w_org_6 if total_w_org_6 != 0 else 0,
      'w_org_group_c_over'    : w_org_group_c_over_6,
      'account_group_c_over'  : account_group_c_over_6,
      'group_c_over_ratio'    : w_org_group_c_over_6/total_w_org_6 if total_w_org_6 != 0 else 0,
      'for_month'             : month,
      'year'                  : year,
      'createdAt'             : todayTimeStamp,
      'createdBy'             : 'system',
   }
   insertData.append(temp_6_month)






   # Last 12~7 month
   # TOTAL
   aggregate = [
       {
           "$match":
           {
               'W_ORG_1'   : {'$gt': 0},
               'PRODGRP_ID' : {'$in' : code},
               "FRELD8_BJ" : {'$gte' : startMonthLastYear,'$lte' : end7Month},
           }
       },
       {
           "$group":
           {
               "_id": 'null',
               "total_account": {'$sum' : 1},
               "total_w_org": {'$sum' : '$W_ORG_1'},
           }
       }
   ]
   accData = mongodb.aggregate_pipeline(MONGO_COLLECTION=zaccf_collection,aggregate_pipeline=aggregate)
   total_w_org_127    = 0
   total_account_127  = 0
   if accData != None:
      for row in accData:
         total_account_127  = row['total_account']
         total_w_org_127    = row['total_w_org']



   # GROUP B
   aggregate = [
       {
           "$match":
           {
               'ODIND_FG' : 'B',
               'W_ORG_1'   : {'$gt': 0},
               'PRODGRP_ID' : {'$in' : code},
               "FRELD8_BJ" : {'$gte' : startMonthLastYear,'$lte' : end7Month},
           }
       },
       {
           "$group":
           {
               "_id": 'null',
               "total_account": {'$sum' : 1},
               "total_w_org": {'$sum' : '$W_ORG_1'},
           }
       }
   ]
   accData = mongodb.aggregate_pipeline(MONGO_COLLECTION=zaccf_collection,aggregate_pipeline=aggregate)
   w_org_group_b_127   = 0
   account_group_b_127  = 0
   if accData != None:
      for row in accData:
         account_group_b_127  = row['total_account']
         w_org_group_b_127    = row['total_w_org']



   # GROUP C
   aggregate = [
       {
           "$match":
           {
               'ODIND_FG' : 'C',
               'W_ORG_1'   : {'$gt': 0},
               'PRODGRP_ID' : {'$in' : code},
               "FRELD8_BJ" : {'$gte' : startMonthLastYear,'$lte' : end7Month},
           }
       },
       {
           "$group":
           {
               "_id": 'null',
               "total_account": {'$sum' : 1},
               "total_w_org": {'$sum' : '$W_ORG_1'},
           }
       }
   ]
   accData = mongodb.aggregate_pipeline(MONGO_COLLECTION=zaccf_collection,aggregate_pipeline=aggregate)
   w_org_group_c_127   = 0
   account_group_c_127  = 0
   if accData != None:
      for row in accData:
         account_group_c_127  = row['total_account']
         w_org_group_c_127    = row['total_w_org']



   # GROUP C+
   aggregate = [
       {
           "$match":
           {
               'ODIND_FG' : {'$in' : ['C','D','E']},
               'W_ORG_1'   : {'$gt': 0},
               'PRODGRP_ID' : {'$in' : code},
               "FRELD8_BJ" : {'$gte' : startMonthLastYear,'$lte' : end7Month},
           }
       },
       {
           "$group":
           {
               "_id": 'null',
               "total_account": {'$sum' : 1},
               "total_w_org": {'$sum' : '$W_ORG_1'},
           }
       }
   ]
   accData = mongodb.aggregate_pipeline(MONGO_COLLECTION=zaccf_collection,aggregate_pipeline=aggregate)
   w_org_group_c_over_127   = 0
   account_group_c_over_127  = 0
   if accData != None:
      for row in accData:
         account_group_c_over_127  = row['total_account']
         w_org_group_c_over_127    = row['total_w_org']


   temp_12_7_month = {
      'name'                  : 'Last 12～7 month',
      'type'                  : p_type,
      'index'                 : 3,
      'sales_period'          : str(year_last_year) + '/' + str(month_last_year) + '~' + str(year_last_7_months) + '/' + str(month_last_7_months),
      'total_w_org'           : total_w_org_127,
      'total_account'         : total_account_127,
      'w_org_group_b'         : w_org_group_b_127,
      'account_group_b'       : account_group_b_127,
      'group_b_ratio'         : w_org_group_b_127/total_w_org_127 if total_w_org_127 != 0 else 0,
      'w_org_group_c'         : w_org_group_c_127,
      'account_group_c'       : account_group_c_127,
      'group_c_ratio'         : w_org_group_c_127/total_w_org_127 if total_w_org_127 != 0 else 0,
      'w_org_group_c_over'    : w_org_group_c_over_127,
      'account_group_c_over'  : account_group_c_over_127,
      'group_c_over_ratio'    : w_org_group_c_over_127/total_w_org_127 if total_w_org_127 != 0 else 0,
      'for_month'             : month,
      'year'                  : year,
      'createdAt'             : todayTimeStamp,
      'createdBy'             : 'system',
   }
   insertData.append(temp_12_7_month)





   # Last 12 month
   # TOTAL
   aggregate = [
       {
           "$match":
           {
               'W_ORG_1'   : {'$gt': 0},
               'PRODGRP_ID' : {'$in' : code},
               "FRELD8_BJ" : {'$gte' : startMonthLastTwoYear,'$lte' : endLast13Month},
           }
       },
       {
           "$group":
           {
               "_id": 'null',
               "total_account": {'$sum' : 1},
               "total_w_org": {'$sum' : '$W_ORG_1'},
           }
       }
   ]
   accData = mongodb.aggregate_pipeline(MONGO_COLLECTION=zaccf_collection,aggregate_pipeline=aggregate)
   total_w_org_last_12    = 0
   total_account_last_12  = 0
   if accData != None:
      for row in accData:
         total_account_last_12  = row['total_account']
         total_w_org_last_12    = row['total_w_org']



   # GROUP B
   aggregate = [
       {
           "$match":
           {
               'ODIND_FG' : 'B',
               'W_ORG_1'   : {'$gt': 0},
               'PRODGRP_ID' : {'$in' : code},
               "FRELD8_BJ" : {'$gte' : startMonthLastTwoYear,'$lte' : endLast13Month},
           }
       },
       {
           "$group":
           {
               "_id": 'null',
               "total_account": {'$sum' : 1},
               "total_w_org": {'$sum' : '$W_ORG_1'},
           }
       }
   ]
   accData = mongodb.aggregate_pipeline(MONGO_COLLECTION=zaccf_collection,aggregate_pipeline=aggregate)
   w_org_group_b_last_12      = 0
   account_group_b_last_12    = 0
   if accData != None:
      for row in accData:
         account_group_b_last_12  = row['total_account']
         w_org_group_b_last_12    = row['total_w_org']



   # GROUP C
   aggregate = [
       {
           "$match":
           {
               'ODIND_FG' : 'C',
               'W_ORG_1'   : {'$gt': 0},
               'PRODGRP_ID' : {'$in' : code},
               "FRELD8_BJ" : {'$gte' : startMonthLastTwoYear,'$lte' : endLast13Month},
           }
       },
       {
           "$group":
           {
               "_id": 'null',
               "total_account": {'$sum' : 1},
               "total_w_org": {'$sum' : '$W_ORG_1'},
           }
       }
   ]
   accData = mongodb.aggregate_pipeline(MONGO_COLLECTION=zaccf_collection,aggregate_pipeline=aggregate)
   w_org_group_c_last_12      = 0
   account_group_c_last_12    = 0
   if accData != None:
      for row in accData:
         account_group_c_last_12  = row['total_account']
         w_org_group_c_last_12    = row['total_w_org']



   # GROUP C+
   aggregate = [
       {
           "$match":
           {
               'ODIND_FG' : {'$in' : ['C','D','E']},
               'W_ORG_1'   : {'$gt': 0},
               'PRODGRP_ID' : {'$in' : code},
               "FRELD8_BJ" : {'$gte' : startMonthLastTwoYear,'$lte' : endLast13Month},
           }
       },
       {
           "$group":
           {
               "_id": 'null',
               "total_account": {'$sum' : 1},
               "total_w_org": {'$sum' : '$W_ORG_1'},
           }
       }
   ]
   accData = mongodb.aggregate_pipeline(MONGO_COLLECTION=zaccf_collection,aggregate_pipeline=aggregate)
   w_org_group_c_over_last_12    = 0
   account_group_c_over_last_12  = 0
   if accData != None:
      for row in accData:
         account_group_c_over_last_12  = row['total_account']
         w_org_group_c_over_last_12    = row['total_w_org']


   temp_last_12_month = {
      'name'                  : 'Last 12 month',
      'type'                  : p_type,
      'index'                 : 4,
      'sales_period'          : str(year_last_two_year) + '/' + str(month_last_two_year) + '~' + str(year_last_13_months) + '/' + str(month_last_13_months),
      'total_w_org'           : total_w_org_last_12,
      'total_account'         : total_account_last_12,
      'w_org_group_b'         : w_org_group_b_last_12,
      'account_group_b'       : account_group_b_last_12,
      'group_b_ratio'         : w_org_group_b_last_12/total_w_org_last_12 if total_w_org_last_12 != 0 else 0,
      'w_org_group_c'         : w_org_group_c_last_12,
      'account_group_c'       : account_group_c_last_12,
      'group_c_ratio'         : w_org_group_c_last_12/total_w_org_last_12 if total_w_org_last_12 != 0 else 0,
      'w_org_group_c_over'    : w_org_group_c_over_last_12,
      'account_group_c_over'  : account_group_c_over_last_12,
      'group_c_over_ratio'    : w_org_group_c_over_last_12/total_w_org_last_12 if total_w_org_last_12 != 0 else 0,
      'for_month'             : month,
      'year'                  : year,
      'createdAt'             : todayTimeStamp,
      'createdBy'             : 'system',
   }
   insertData.append(temp_last_12_month)




   # Total
   # TOTAL
   aggregate = [
      {
           "$match":
           {
               'W_ORG_1'   : {'$gt': 0},
               'PRODGRP_ID' : {'$in' : code},
           }
      },
      {
           "$group":
           {
               "_id": 'null',
               "total_account": {'$sum' : 1},
               "total_w_org": {'$sum' : '$W_ORG_1'},
           }
      }
   ]
   accData = mongodb.aggregate_pipeline(MONGO_COLLECTION=zaccf_collection,aggregate_pipeline=aggregate)
   total_w_org_total    = 0
   total_account_total  = 0
   if accData != None:
      for row in accData:
         total_account_total  = row['total_account']
         total_w_org_total    = row['total_w_org']


   # GROUP B
   aggregate = [
      {
           "$match":
           {
               'ODIND_FG' : 'B',
               'W_ORG_1'   : {'$gt': 0},
               'PRODGRP_ID' : {'$in' : code},
           }
      },
      {
           "$group":
           {
               "_id": 'null',
               "total_account": {'$sum' : 1},
               "total_w_org": {'$sum' : '$W_ORG_1'},
           }
      }
   ]
   accData = mongodb.aggregate_pipeline(MONGO_COLLECTION=zaccf_collection,aggregate_pipeline=aggregate)
   w_org_group_b_total   = 0
   account_group_b_total  = 0
   if accData != None:
      for row in accData:
         account_group_b_total  = row['total_account']
         w_org_group_b_total    = row['total_w_org']


   # GROUP C
   aggregate = [
      {
           "$match":
           {
               'ODIND_FG' : 'C',
               'W_ORG_1'   : {'$gt': 0},
               'PRODGRP_ID' : {'$in' : code},
           }
      },
      {
           "$group":
           {
               "_id": 'null',
               "total_account": {'$sum' : 1},
               "total_w_org": {'$sum' : '$W_ORG_1'},
           }
      }
   ]
   accData = mongodb.aggregate_pipeline(MONGO_COLLECTION=zaccf_collection,aggregate_pipeline=aggregate)
   w_org_group_c_total   = 0
   account_group_c_total  = 0
   if accData != None:
      for row in accData:
         account_group_c_total  = row['total_account']
         w_org_group_c_total    = row['total_w_org']


   # GROUP C+
   aggregate = [
      {
           "$match":
           {
               'ODIND_FG' : {'$in' : ['C','D','E']},
               'W_ORG_1'   : {'$gt': 0},
               'PRODGRP_ID' : {'$in' : code},
           }
      },
      {
           "$group":
           {
               "_id": 'null',
               "total_account": {'$sum' : 1},
               "total_w_org": {'$sum' : '$W_ORG_1'},
           }
      }
   ]
   accData = mongodb.aggregate_pipeline(MONGO_COLLECTION=zaccf_collection,aggregate_pipeline=aggregate)
   w_org_group_c_over_total   = 0
   account_group_c_over_total = 0
   if accData != None:
      for row in accData:
         account_group_c_over_total  = row['total_account']
         w_org_group_c_over_total    = row['total_w_org']


   temp_total = {
      'name'                  : 'TOTAL',
      'sales_period'          : '',
      'type'                  : p_type,
      'index'                 : 6,
      'total_w_org'           : total_w_org_total,
      'total_account'         : total_account_total,
      'w_org_group_b'         : w_org_group_b_total,
      'account_group_b'       : account_group_b_total,
      'group_b_ratio'         : w_org_group_b_total/total_w_org_total if total_w_org_total != 0 else 0,
      'w_org_group_c'         : w_org_group_c_total,
      'account_group_c'       : account_group_c_total,
      'group_c_ratio'         : w_org_group_c_total/total_w_org_total if total_w_org_total != 0 else 0,
      'w_org_group_c_over'    : w_org_group_c_over_total,
      'account_group_c_over'  : account_group_c_over_total,
      'group_c_over_ratio'    : w_org_group_c_over_total/total_w_org_total if total_w_org_total != 0 else 0,
      'for_month'             : month,
      'year'                  : year,
      'createdAt'             : todayTimeStamp,
      'createdBy'             : 'system',
   }
   insertData.append(temp_total)



   # Other than that
   total_w_org_other = total_w_org_total - total_w_org - total_w_org_6 - total_w_org_127 - total_w_org_last_12
   temp_other_than = {
      'name'                  : 'Other than that',
      'type'                  : p_type,
      'index'                 : 5,
      'sales_period'          : '~' + str(year_last_13_months) + '/' + str(month_last_13_months),
      'total_w_org'           : total_w_org_other,
      'total_account'         : total_account_total - total_account - total_account_6 - total_account_127 - total_account_last_12,
      'w_org_group_b'         : w_org_group_b_total - w_org_group_b_6 - w_org_group_b_127 - w_org_group_b_last_12,
      'account_group_b'       : account_group_b_total - account_group_b_6 - account_group_b_127 - account_group_b_last_12,
      'group_b_ratio'         : (w_org_group_b_total - w_org_group_b_6 - w_org_group_b_127- w_org_group_b_last_12)/total_w_org_other if total_w_org_other != 0 else 0,
      'w_org_group_c'         : w_org_group_c_total - w_org_group_c_6 - w_org_group_c_127 - w_org_group_c_last_12,
      'account_group_c'       : account_group_c_total - account_group_c_6 - account_group_c_127 - account_group_c_last_12,
      'group_c_ratio'         : (w_org_group_c_total - w_org_group_c_6 - w_org_group_c_127 - w_org_group_c_last_12)/total_w_org_other if total_w_org_other != 0 else 0,
      'w_org_group_c_over'    : w_org_group_c_over_total - w_org_group_c_over_6 - w_org_group_c_over_127 - w_org_group_c_over_last_12,
      'account_group_c_over'  : account_group_c_over_total - account_group_c_over_6 - account_group_c_over_127 - account_group_c_over_last_12,
      'group_c_over_ratio'    : (w_org_group_c_over_total - w_org_group_c_over_6 - w_org_group_c_over_127 - w_org_group_c_over_last_12)/total_w_org_other if total_w_org_other != 0 else 0,
      'for_month'             : month,
      'year'                  : year,
      'createdAt'             : todayTimeStamp,
      'createdBy'             : 'system',
   }
   insertData.append(temp_other_than)







   if len(insertData) > 0:
      # print(len(insertData))
      mongodb.batch_insert(MONGO_COLLECTION=collection, insert_data=insertData)

   now_end         = datetime.now()
   log.write(now_end.strftime("%d/%m/%Y, %H:%M:%S") + ': End Log' + '\n')
   print('DONE')
except Exception as e:
   print(traceback.format_exc())
   log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
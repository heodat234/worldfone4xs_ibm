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
from datetime import date
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
collection         = common.getSubUser(subUserType, 'Daily_assignment_report')
lnjc05_collection  = common.getSubUser(subUserType, 'LNJC05')
ln3206_collection  = common.getSubUser(subUserType, 'LN3206F')
zaccf_collection   = common.getSubUser(subUserType, 'ZACCF')
product_collection   = common.getSubUser(subUserType, 'Product')
sbv_collection       = common.getSubUser(subUserType, 'SBV')
group_collection     = common.getSubUser(subUserType, 'Group_card')
account_collection   = common.getSubUser(subUserType, 'List_of_account_in_collection')
payment_of_card_collection  = common.getSubUser(subUserType, 'Report_input_payment_of_card')
diallist_detail_collection  = common.getSubUser(subUserType, 'Diallist_detail')
cdr_collection       = common.getSubUser(subUserType, 'worldfonepbxmanager')
jsonData_collection  = common.getSubUser(subUserType, 'Jsondata')
user_collection      = common.getSubUser(subUserType, 'User_product')
wo_collection        = common.getSubUser(subUserType, 'WO_monthly')
action_code_collection        = common.getSubUser(subUserType, 'Action_code')
log         = open(base_url + "cronjob/python/Loan/log/testDailyAssignment_log.txt","a")

try:
   data        = []
   insertData  = []
   insertDataCard   = []
   insertDataWO   = []
   now         = datetime.now()
   log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': Start Import' + '\n')

   today = date.today()
   # today = datetime.strptime('31/12/2019', "%d/%m/%Y").date()

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

   if todayTimeStamp in listHoliday:
      sys.exit()


   lawsuit_fields = _mongodb.getOne(MONGO_COLLECTION=jsonData_collection,SELECT=['data'],WHERE={'tags': ['LAWSUIT', 'fields']})
   raa_fields = _mongodb.getOne(MONGO_COLLECTION=jsonData_collection,SELECT=['data'],WHERE={'tags': ['RAA', 'fields']})
   users = _mongodb.get(MONGO_COLLECTION=user_collection,SELECT=['extension','agentname'],WHERE={'active': 'true'})
   i = 1
   # LNJC05

   aggregate_pipeline = [
      {
           "$match":
           {
               "createdAt" : {'$gte' : 1577034000},
               # "createdAt" : {'$lte' : endTodayTimeStamp}
           }
      }
            
   ]
   # print(aggregate_pipeline)
   data = mongodb.aggregate_pipeline(MONGO_COLLECTION=collection,aggregate_pipeline=aggregate_pipeline)
   # print(len(list(data)))
   for row in data:
      dataInsert = {}
      if 'calluuid' in row.keys():
         cdr = mongodb.getOne(MONGO_COLLECTION=cdr_collection, WHERE={'calluuid': str(row['calluuid'])},SELECT=['customernumber'])
         if cdr != None:
            dataInsert['contacted'] = cdr['customernumber']
      else:
         dataInsert['contacted'] = ''
         
      mongodb.update(MONGO_COLLECTION=collection, WHERE={'account_number': str(row['account_number'])}, VALUE=dataInsert)
      print(row['account_number'])
      # break

   

   now_end         = datetime.now()
   log.write(now_end.strftime("%d/%m/%Y, %H:%M:%S") + ': End Log' + '\n')
   print('DONE')
except Exception as e:
   print(traceback.format_exc())
   log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
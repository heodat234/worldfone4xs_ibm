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
lnjc05_collection  = common.getSubUser(subUserType, 'LNJC05_yesterday')
ln3206_collection  = common.getSubUser(subUserType, 'LN3206F')
account_collection   = common.getSubUser(subUserType, 'List_of_account_in_collection_yesterday')
payment_of_card_collection  = common.getSubUser(subUserType, 'Report_input_payment_of_card')
log         = open(base_url + "cronjob/python/Loan/log/updatePayment_log.txt","a")

try:
   data        = []
   accountYes  = []
   accountNo   = []
   temp        = {}
   now         = datetime.now()
   log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': Start Import' + '\n')

   today = date.today()
   # today = datetime.strptime('26/02/2020', "%d/%m/%Y").date()

   day = today.day
   month = today.month
   year = today.year
   weekday = today.weekday()
   lastDayOfMonth = calendar.monthrange(year, month)[1]

   todayString = today.strftime("%d/%m/%Y")
   todayTimeStamp = int(time.mktime(time.strptime(str(todayString + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
   endTodayTimeStamp = int(time.mktime(time.strptime(str(todayString + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))


   # LN2306f
   aggregate_pipeline = [
      {
           "$match":
           {
               "created_at" : {'$gte' : todayTimeStamp,'$lte' : endTodayTimeStamp},
               
           }
      }
            
   ]
   data = mongodb.aggregate_pipeline(MONGO_COLLECTION=ln3206_collection,aggregate_pipeline=aggregate_pipeline)
   
   if data != None:
      for row in data:
         lnjc05Data = mongodb.getOne(MONGO_COLLECTION=lnjc05_collection, WHERE={'account_number': str(row['account_number'])} )
         if lnjc05Data != None:
            accountYes.append(row['account_number'])
         else:
            accountNo.append(row['account_number'])
   

   print(len(accountYes))
   temp['coNoHayKhong'] = 'Y'
   mongodb.batch_update(MONGO_COLLECTION=ln3206_collection,  WHERE={'account_number': {'$in': accountYes}}, VALUE=temp)
   temp['coNoHayKhong'] = 'N'
   mongodb.batch_update(MONGO_COLLECTION=ln3206_collection,  WHERE={'account_number': {'$in': accountNo}}, VALUE=temp)
   

   # card
   aggregate_pipeline = [
      {
           "$match":
           {
               "created_at" : {'$gte' : todayTimeStamp,'$lte' : endTodayTimeStamp},
               
           }
      }
            
   ]
   data = mongodb.aggregate_pipeline(MONGO_COLLECTION=payment_of_card_collection,aggregate_pipeline=aggregate_pipeline)
   accountYes  = []
   accountNo   = []
   if data != None:
      for row in data:
         lnjc05Data = mongodb.getOne(MONGO_COLLECTION=lnjc05_collection, WHERE={'account_number': str(row['account_number'])} )
         if lnjc05Data != None:
            accountYes.append(row['account_number'])
         else:
            accountNo.append(row['account_number'])
   

   temp['coNoHayKhong'] = 'Y'
   mongodb.batch_update(MONGO_COLLECTION=ln3206_collection,  WHERE={'account_number': {'$in': accountYes}}, VALUE=temp)
   temp['coNoHayKhong'] = 'N'
   mongodb.batch_update(MONGO_COLLECTION=ln3206_collection,  WHERE={'account_number': {'$in': accountNo}}, VALUE=temp)
   


   now_end         = datetime.now()
   log.write(now_end.strftime("%d/%m/%Y, %H:%M:%S") + ': End Log' + '\n')
   print('DONE')
except Exception as e:
   print(traceback.format_exc())
   log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
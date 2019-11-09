#!/usr/bin/python3.6
# -*- coding: utf-8 -*-
import sys
import os
import time
import ntpath
import json
from helper.mongod import Mongodb
from datetime import datetime
from datetime import date
from datetime import timedelta
from pprint import pprint
from bson import ObjectId
from helper.common import Common
import pandas as pd

mongodb     = Mongodb("worldfone4xs")
_mongodb    = Mongodb("_worldfone4xs")
common      = Common()
now         = datetime.now()
subUserType = 'LO'
account_collection      = common.getSubUser(subUserType, 'Account')
sbv_collection          = common.getSubUser(subUserType, 'SBV')
temp_collection          = common.getSubUser(subUserType, 'Group_card')
log         = open("/var/www/html/worldfone4xs_ibm/cronjob/python/Loan/log/groupCard_log.txt","a")

try:
   data        = []
   insertData  = []

   day = now.strftime("%d")
   log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': Start Log' + '\n')

   count = mongodb.count(MONGO_COLLECTION=account_collection)
   limit = 10000
   quotient = int(count)/limit
   mod = int(count)%limit
   for x in range(int(quotient)):
      result = mongodb.get(MONGO_COLLECTION=account_collection, SELECT=['account_number','overdue'],SORT=([('_id', -1)]),SKIP=int(x*limit), TAKE=int(limit))
      for idx,row in enumerate(result):
         temp = {}
         if row['overdue'] != '':
            tomorrow = datetime.strptime(row['overdue'], '%d/%m/%Y') + timedelta(days = 1)
            tomorrow       = tomorrow.strftime("%d")
            if int(tomorrow) == 13:
               group = '01'
            if int(tomorrow) == 23:
               group = '02'
            if int(tomorrow) == 1:
               group = '03'
            if int(tomorrow) == int(day):
               sbv = mongodb.getOne(MONGO_COLLECTION=sbv_collection, WHERE={'contract_no': str(row['account_no'])},SELECT=['delinquency_group'])
               if sbv != None:
                  if int(sbv['delinquency_group']) == 1:
                     group = 'A'+group
                  if int(sbv['delinquency_group']) == 2:
                     group = 'B'+group
                  if int(sbv['delinquency_group']) == 3:
                     group = 'C'+group
                  if int(sbv['delinquency_group']) == 4:
                     group = 'D'+group
                  if int(sbv['delinquency_group']) == 5:
                     group = 'E'+group
            
                  temp['account_no']   = row['account_no']
                  temp['due_date']     = row['overdue']
                  temp['group']        = group
                  temp['created_at']   = int(time.time())
                  temp['created_by']   = 'system'
                  insertData.append(temp)
      # break
   if mod >0:
      result = mongodb.get(MONGO_COLLECTION=account_collection, SELECT=['account_number','overdue'],SORT=([('_id', -1)]),SKIP=int(int(quotient)*limit), TAKE=int(mod))
      for idx,row in enumerate(result):
         temp = {}
         if row['overdue'] != '':
            tomorrow = datetime.strptime(row['overdue'], '%d/%m/%Y') + timedelta(days = 1)
            tomorrow       = tomorrow.strftime("%d")
            if int(tomorrow) == 13:
               group = '01'
            if int(tomorrow) == 23:
               group = '02'
            if int(tomorrow) == 1:
               group = '03'
            if int(tomorrow) == int(day):
               sbv = mongodb.getOne(MONGO_COLLECTION=sbv_collection, WHERE={'contract_no': str(row['account_no'])},SELECT=['delinquency_group'])
               if sbv != None:
                  if int(sbv['delinquency_group']) == 1:
                     group = 'A'+group
                  if int(sbv['delinquency_group']) == 2:
                     group = 'B'+group
                  if int(sbv['delinquency_group']) == 3:
                     group = 'C'+group
                  if int(sbv['delinquency_group']) == 4:
                     group = 'D'+group
                  if int(sbv['delinquency_group']) == 5:
                     group = 'E'+group
            
                  temp['account_no']   = row['account_no']
                  temp['due_date']     = row['overdue']
                  temp['group']        = group
                  temp['created_at']   = int(time.time())
                  temp['created_by']   = 'system'
                  insertData.append(temp)

   if len(insertData) > 0:
      mongodb.remove_document(MONGO_COLLECTION=temp_collection)
      mongodb.batch_insert(MONGO_COLLECTION=temp_collection, insert_data=insertData)
      
   now_end         = datetime.now()
   print('success')
   log.write(now_end.strftime("%d/%m/%Y, %H:%M:%S") + ': End Log' + '\n')
   # print(1)
except Exception as e:
   log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
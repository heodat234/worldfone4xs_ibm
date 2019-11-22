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
account_collection      = common.getSubUser(subUserType, 'List_of_account_in_collection')
sbv_collection          = common.getSubUser(subUserType, 'SBV')
collection              = common.getSubUser(subUserType, 'Group_card')
due_date_collection     = common.getSubUser(subUserType, 'Report_due_date')
log         = open("/var/www/html/worldfone4xs_ibm/cronjob/python/Loan/log/groupCard_log.txt","a")

try:
   data        = []
   insertData  = []
   updateDate  = []

   day = now.strftime("%d/%m/%Y")
   day = common.convertTimestamp(value=day)
   result_due_date = mongodb.getOne(MONGO_COLLECTION=due_date_collection,WHERE={'due_date_add_1':day})
   log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': Start Log' + '\n')
   count = mongodb.count(MONGO_COLLECTION=account_collection)
   limit = 10000
   quotient = int(count)/limit
   mod = int(count)%limit

   checkGroup = mongodb.count(MONGO_COLLECTION=collection)
   if checkGroup <= 0:
      for x in range(int(quotient)):
         result = mongodb.get(MONGO_COLLECTION=account_collection, SELECT=['account_no','overdue_date'],SORT=([('_id', -1)]),SKIP=int(x*limit), TAKE=int(limit))
         for idx,row in enumerate(result):
            temp = {}
            tempSbv = {}
            group = ''
            if row['overdue_date'] != 0:
               date_time = datetime.fromtimestamp(row['overdue_date'])
               overdue   = date_time.strftime('%d/%m/%Y')
               tomorrow = datetime.strptime(overdue, '%d/%m/%Y') + timedelta(days = 1)
               tomorrow       = tomorrow.strftime("%d")
               if int(tomorrow) == 13:
                  debt_group = '01'
               if int(tomorrow) == 23:
                  debt_group = '02'
               if int(tomorrow) == 1:
                  debt_group = '03'
               sbv = mongodb.getOne(MONGO_COLLECTION=sbv_collection, WHERE={'contract_no': str(row['account_no'])},SELECT=['delinquency_group'])
               if sbv != None:
                  if int(sbv['delinquency_group']) == 1:
                     group = 'A'+debt_group
                  if int(sbv['delinquency_group']) == 2:
                     group = 'B'+debt_group
                  if int(sbv['delinquency_group']) == 3:
                     group = 'C'+debt_group
                  if int(sbv['delinquency_group']) == 4:
                     group = 'D'+debt_group
                  if int(sbv['delinquency_group']) == 5:
                     group = 'E'+debt_group
            
                  temp['account_no']   = row['account_no']
                  temp['due_date']     = row['overdue_date']
                  temp['group']        = group
                  temp['group_number'] = sbv['delinquency_group']
                  temp['created_at']   = int(time.time())
                  temp['created_by']   = 'system'
                  insertData.append(temp)
                  tempSbv['first_due_group'] = sbv['delinquency_group']
                  mongodb.update(MONGO_COLLECTION=sbv_collection, WHERE={'contract_no': str(row['account_no'])}, VALUE=tempSbv)
                  # print(temp['group_number'])
         # break

      if mod >0:
         result = mongodb.get(MONGO_COLLECTION=account_collection, SELECT=['account_no','overdue_date'],SORT=([('_id', -1)]),SKIP=int(int(quotient)*limit), TAKE=int(mod))
         for idx,row in enumerate(result):
            temp = {}
            tempSbv = {}
            group = ''
            if row['overdue_date'] != 0:
               date_time = datetime.fromtimestamp(row['overdue_date'])
               overdue   = date_time.strftime('%d/%m/%Y')
               tomorrow = datetime.strptime(overdue, '%d/%m/%Y') + timedelta(days = 1)
               tomorrow       = tomorrow.strftime("%d")
               if int(tomorrow) == 13:
                  debt_group = '01'
               if int(tomorrow) == 23:
                  debt_group = '02'
               if int(tomorrow) == 1:
                  debt_group = '03'
               sbv = mongodb.getOne(MONGO_COLLECTION=sbv_collection, WHERE={'contract_no': str(row['account_no'])},SELECT=['delinquency_group'])
               if sbv != None:
                  if int(sbv['delinquency_group']) == 1:
                     group = 'A'+debt_group
                  if int(sbv['delinquency_group']) == 2:
                     group = 'B'+debt_group
                  if int(sbv['delinquency_group']) == 3:
                     group = 'C'+debt_group
                  if int(sbv['delinquency_group']) == 4:
                     group = 'D'+debt_group
                  if int(sbv['delinquency_group']) == 5:
                     group = 'E'+debt_group
            
                  temp['account_no']   = row['account_no']
                  temp['due_date']     = row['overdue_date']
                  temp['group']        = group
                  temp['group_number'] = sbv['delinquency_group']
                  temp['created_at']   = int(time.time())
                  temp['created_by']   = 'system'
                  insertData.append(temp)
                  tempSbv['first_due_group'] = sbv['delinquency_group']
                  mongodb.update(MONGO_COLLECTION=sbv_collection, WHERE={'contract_no': str(row['account_no'])}, VALUE=tempSbv)
 
   else:
      if result_due_date != None:
         debt_group = str(result_due_date['debt_group'])
         # due_date = datetime.fromtimestamp(result_due_date['due_date'])
         # due_date = due_date.strftime("%d/%m/%Y")
         due_date = result_due_date['due_date']

         for x in range(int(quotient)):
            result = mongodb.get(MONGO_COLLECTION=account_collection, WHERE={'overdue_date': str(due_date)}, SELECT=['account_no'],SORT=([('_id', -1)]),SKIP=int(x*limit), TAKE=int(limit))
            for idx,row in enumerate(result):
               temp = {}
               tempSbv = {}
               group = ''
               sbv = mongodb.getOne(MONGO_COLLECTION=sbv_collection, WHERE={'contract_no': str(row['account_no'])},SELECT=['delinquency_group'])
               if sbv != None:
                  if int(sbv['delinquency_group']) == 1:
                     group = 'A'+debt_group
                  if int(sbv['delinquency_group']) == 2:
                     group = 'B'+debt_group
                  if int(sbv['delinquency_group']) == 3:
                     group = 'C'+debt_group
                  if int(sbv['delinquency_group']) == 4:
                     group = 'D'+debt_group
                  if int(sbv['delinquency_group']) == 5:
                     group = 'E'+debt_group
            
                  temp['account_no']   = row['account_no']
                  temp['due_date']     = due_date
                  temp['group']        = group
                  temp['group_number'] = sbv['delinquency_group']
                  temp['created_at']   = int(time.time())
                  temp['created_by']   = 'system'
                  checkDataInDB = mongodb.getOne(MONGO_COLLECTION=collection, WHERE={'account_no': temp['account_no']})
                  if checkDataInDB is not None:
                     updateDate.append(temp)
                  else:
                     insertData.append(temp)

                  tempSbv['first_due_group'] = sbv['delinquency_group']
                  mongodb.update(MONGO_COLLECTION=sbv_collection, WHERE={'contract_no': str(row['account_no'])}, VALUE=tempSbv)
         if mod >0:
            result = mongodb.get(MONGO_COLLECTION=account_collection, WHERE={'overdue_date': due_date}, SELECT=['account_no'],SORT=([('_id', -1)]),SKIP=int(int(quotient)*limit), TAKE=int(mod))
            for idx,row in enumerate(result):
               temp = {}
               tempSbv = {}
               group = ''
               sbv = mongodb.getOne(MONGO_COLLECTION=sbv_collection, WHERE={'contract_no': str(row['account_no'])},SELECT=['delinquency_group'])
               if sbv != None:
                  if int(sbv['delinquency_group']) == 1:
                     group = 'A'+debt_group
                  if int(sbv['delinquency_group']) == 2:
                     group = 'B'+debt_group
                  if int(sbv['delinquency_group']) == 3:
                     group = 'C'+debt_group
                  if int(sbv['delinquency_group']) == 4:
                     group = 'D'+debt_group
                  if int(sbv['delinquency_group']) == 5:
                     group = 'E'+debt_group
            
                  temp['account_no']   = row['account_no']
                  temp['due_date']     = due_date
                  temp['group']        = group
                  temp['group_number'] = sbv['delinquency_group']
                  temp['created_at']   = int(time.time())
                  temp['created_by']   = 'system'
                  checkDataInDB = mongodb.getOne(MONGO_COLLECTION=collection, WHERE={'account_no': temp['account_no']})
                  if checkDataInDB is not None:
                     updateDate.append(temp)
                  else:
                     insertData.append(temp)

                  tempSbv['first_due_group'] = sbv['delinquency_group']
                  mongodb.update(MONGO_COLLECTION=sbv_collection, WHERE={'contract_no': str(row['account_no'])}, VALUE=tempSbv)

   if len(insertData) > 0:
      mongodb.batch_insert(MONGO_COLLECTION=collection, insert_data=insertData)

   if len(updateDate) > 0:
      for updateD in updateDate:
          mongodb.update(MONGO_COLLECTION=collection, WHERE={'account_no': updateD['account_no']}, VALUE=updateD)

      
   now_end         = datetime.now()
   print('success')
   log.write(now_end.strftime("%d/%m/%Y, %H:%M:%S") + ': End Log' + '\n')
   # print(1)
except Exception as e:
   log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
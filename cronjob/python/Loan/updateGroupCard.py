#!/usr/bin/python3.6
# -*- coding: utf-8 -*-
import sys
import os
import time
import ntpath
import json
import calendar
from helper.mongod import Mongodb
from datetime import datetime
from datetime import date
from datetime import timedelta
from pprint import pprint
from bson import ObjectId
from helper.common import Common
import pandas as pd

common      = Common()
base_url    = common.base_url()
wff_env     = common.wff_env(base_url)
mongodb     = Mongodb(MONGODB="worldfone4xs", WFF_ENV=wff_env)
_mongodb    = Mongodb(MONGODB="_worldfone4xs", WFF_ENV=wff_env)
now         = datetime.now()
subUserType = 'LO'
account_collection      = common.getSubUser(subUserType, 'List_of_account_in_collection')
sbv_collection          = common.getSubUser(subUserType, 'SBV')
collection              = common.getSubUser(subUserType, 'Group_card')
due_date_collection     = common.getSubUser(subUserType, 'Report_due_date')
log         = open(base_url + "cronjob/python/Loan/log/groupCard_log.txt","a")
log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': Start Log' + '\n')
try:
   data        = []
   insertData  = []
   updateDate  = []

   today = date.today()
   # today = datetime.strptime('02/01/2020', "%d/%m/%Y").date()

   day = today.day
   month = today.month
   year = today.year
   weekday = today.weekday()
   lastDayOfMonth = calendar.monthrange(year, month)[1]

   todayString = today.strftime("%d/%m/%Y")
   todayTimeStamp = int(time.mktime(time.strptime(str(todayString + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))

   startMonth = int(time.mktime(time.strptime(str('01/' + str(month) + '/' + str(year) + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
   endMonth = int(time.mktime(time.strptime(str(str(lastDayOfMonth) + '/' + str(month) + '/' + str(year) + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))

   holidayOfMonth = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_off_sys'), WHERE={'off_date': todayTimeStamp})
   if holidayOfMonth != None:
      sys.exit()

   dayStamp = common.convertTimestamp(value=today.strftime("%d/%m/%Y"))
   result_due_date = mongodb.getOne(MONGO_COLLECTION=due_date_collection,WHERE={'due_date_add_1':todayTimeStamp})

   count = mongodb.count(MONGO_COLLECTION=account_collection)
   limit = 10000
   quotient = int(count)/limit
   mod = int(count)%limit

   checkGroup = mongodb.count(MONGO_COLLECTION=collection)
   if checkGroup <= 0:
      for x in range(int(quotient)):
         result = mongodb.get(MONGO_COLLECTION=account_collection, SELECT=['account_number','overdue_date','due_date'],SORT=([('_id', -1)]),SKIP=int(x*limit), TAKE=int(limit))
         for idx,row in enumerate(result):
            temp = {}
            tempSbv = {}
            group = ''
            debt_group = ''
            # if row['overdue_date'] != 0:
               # date_time = datetime.fromtimestamp(row['overdue_date'])
               # overdue   = date_time.strftime('%d/%m/%Y')
               # tomorrow = datetime.strptime(overdue, '%d/%m/%Y') + timedelta(days = 1)
               # tomorrow       = tomorrow.strftime("%d")
            dueDayOfMonth = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_due_date'), WHERE={'for_month': str(month), 'due_date': row['due_date']})
            if dueDayOfMonth != None:
               debt_group = dueDayOfMonth['debt_group']

               sbv = mongodb.getOne(MONGO_COLLECTION=sbv_collection, WHERE={'contract_no': str(row['account_number'])},SELECT=['delinquency_group'])
               if sbv != None:
                  # group = sbv['overdue_indicator']+debt_group
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
            
                  temp['account_number']   = row['account_number']
                  temp['due_date']     = row['overdue_date']
                  temp['group']        = group
                  temp['group_number'] = sbv['delinquency_group']
                  temp['created_at']   = todayTimeStamp
                  temp['created_by']   = 'system'
                  insertData.append(temp)
                  # tempSbv['first_due_group'] = sbv['delinquency_group']
                  # mongodb.update(MONGO_COLLECTION=sbv_collection, WHERE={'contract_no': str(row['account_number'])}, VALUE=tempSbv)

      if mod >0:
         result = mongodb.get(MONGO_COLLECTION=account_collection, SELECT=['account_number','overdue_date','due_date'],SORT=([('_id', -1)]),SKIP=int(int(quotient)*limit), TAKE=int(mod))
         for idx,row in enumerate(result):
            temp = {}
            tempSbv = {}
            group = ''
            debt_group = ''
            # if row['overdue_date'] != 0:
               # date_time = datetime.fromtimestamp(row['overdue_date'])
               # overdue   = date_time.strftime('%d/%m/%Y')
               # tomorrow = datetime.strptime(overdue, '%d/%m/%Y') + timedelta(days = 1)
               # tomorrow       = tomorrow.strftime("%d")
               # if int(tomorrow) == 13:
               #    debt_group = '01'
               # if int(tomorrow) == 23:
               #    debt_group = '02'
               # if int(tomorrow) == 1:
               #    debt_group = '03'
            dueDayOfMonth = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_due_date'), WHERE={'for_month': str(month), 'due_date': row['due_date']})
            if dueDayOfMonth != None:
               debt_group = dueDayOfMonth['debt_group']
               
               sbv = mongodb.getOne(MONGO_COLLECTION=sbv_collection, WHERE={'contract_no': str(row['account_number'])},SELECT=['delinquency_group'])
               if sbv != None:
                  # group = sbv['overdue_indicator']+debt_group
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
                     
                  temp['account_number']   = row['account_number']
                  temp['due_date']     = row['overdue_date']
                  temp['group']        = group
                  temp['group_number'] = sbv['delinquency_group']
                  temp['created_at']   = todayTimeStamp
                  temp['created_by']   = 'system'
                  insertData.append(temp)
                  # tempSbv['first_due_group'] = sbv['delinquency_group']
                  # mongodb.update(MONGO_COLLECTION=sbv_collection, WHERE={'contract_no': str(row['account_number'])}, VALUE=tempSbv)

   else:
      if result_due_date != None:
         due_date = result_due_date['due_date']
         debt_group = result_due_date['debt_group']

         for x in range(int(quotient)):
            result = mongodb.get(MONGO_COLLECTION=account_collection, WHERE={'due_date':due_date}, SELECT=['account_number','due_date'],SORT=([('_id', -1)]),SKIP=int(x*limit), TAKE=int(limit))
            for idx,row in enumerate(result):
               temp = {}
               tempSbv = {}
               group = ''
               # debt_group = ''
               # dueDayOfMonth = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_due_date'), WHERE={'for_month': str(month), 'due_date': row['due_date']})
               # if dueDayOfMonth != None:
               #    debt_group = dueDayOfMonth['debt_group']
               sbv = mongodb.getOne(MONGO_COLLECTION=sbv_collection, WHERE={'contract_no': str(row['account_number'])},SELECT=['delinquency_group'])
               if sbv != None:
                  # group = sbv['overdue_indicator']+debt_group
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
            
                  temp['account_number']   = row['account_number']
                  temp['due_date']     = due_date
                  temp['group']        = group
                  temp['group_number'] = sbv['delinquency_group']
                  temp['created_at']   = todayTimeStamp
                  temp['created_by']   = 'system'
                  checkDataInDB = mongodb.getOne(MONGO_COLLECTION=collection, WHERE={'account_number': temp['account_number']})
                  if checkDataInDB is not None:
                     updateDate.append(temp)
                  else:
                     insertData.append(temp)

                  # tempSbv['first_due_group'] = sbv['delinquency_group']
                  # mongodb.update(MONGO_COLLECTION=sbv_collection, WHERE={'contract_no': str(row['account_number'])}, VALUE=tempSbv)
         
         if mod >0:
            result = mongodb.get(MONGO_COLLECTION=account_collection, WHERE={'due_date':due_date}, SELECT=['account_number','due_date'],SORT=([('_id', -1)]),SKIP=int(int(quotient)*limit), TAKE=int(mod))
            for idx,row in enumerate(result):
               temp = {}
               tempSbv = {}
               group = ''
               # debt_group = ''
               # dueDayOfMonth = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_due_date'), WHERE={'for_month': str(month), 'due_date': row['due_date']})
               # if dueDayOfMonth != None:
               #    debt_group = dueDayOfMonth['debt_group']
               sbv = mongodb.getOne(MONGO_COLLECTION=sbv_collection, WHERE={'contract_no': str(row['account_number'])},SELECT=['delinquency_group'])
               if sbv != None:
                  # group = sbv['overdue_indicator']+debt_group
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
            
                  temp['account_number']   = row['account_number']
                  temp['due_date']     = due_date
                  temp['group']        = group
                  temp['group_number'] = sbv['delinquency_group']
                  temp['created_at']   = todayTimeStamp
                  temp['created_by']   = 'system'
                  checkDataInDB = mongodb.getOne(MONGO_COLLECTION=collection, WHERE={'account_number': temp['account_number']})
                  if checkDataInDB is not None:
                     updateDate.append(temp)
                  else:
                     insertData.append(temp)

                  # tempSbv['first_due_group'] = sbv['delinquency_group']
                  # mongodb.update(MONGO_COLLECTION=sbv_collection, WHERE={'contract_no': str(row['account_number'])}, VALUE=tempSbv)

   if len(insertData) > 0:
      mongodb.batch_insert(MONGO_COLLECTION=collection, insert_data=insertData)

   if len(updateDate) > 0:
      for updateD in updateDate:
          mongodb.update(MONGO_COLLECTION=collection, WHERE={'account_number': updateD['account_number']}, VALUE=updateD)

      
   now_end         = datetime.now()
   log.write(now_end.strftime("%d/%m/%Y, %H:%M:%S") + ': End Log' + '\n')
   print('DONE')
   
except Exception as e:
   print(e)
   log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
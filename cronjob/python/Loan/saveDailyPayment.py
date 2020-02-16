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
collection         = common.getSubUser(subUserType, 'Daily_payment_report')
lnjc05_collection  = common.getSubUser(subUserType, 'LNJC05_yesterday')
ln3206_collection  = common.getSubUser(subUserType, 'LN3206F')
zaccf_collection   = common.getSubUser(subUserType, 'ZACCF_yesterday')
product_collection   = common.getSubUser(subUserType, 'Product')
sbv_collection       = common.getSubUser(subUserType, 'SBV_yesterday')
store_collection     = common.getSubUser(subUserType, 'SBV_Stored')
account_collection   = common.getSubUser(subUserType, 'List_of_account_in_collection_yesterday')
payment_of_card_collection  = common.getSubUser(subUserType, 'Report_input_payment_of_card')
diallist_collection  = common.getSubUser(subUserType, 'Diallist_detail')
user_collection      = common.getSubUser(subUserType, 'User_product')



log         = open(base_url + "cronjob/python/Loan/log/DailyPayment_log.txt","a")
log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': Start Import' + '\n')

try:
   data        = []
   insertData  = []
   PaymentData   = []
   insertDataPayment = []

   today = date.today()
   # today = datetime.strptime('13/02/2020', "%d/%m/%Y").date()

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

   yesterday = today - timedelta(days=1)
   yesterdayString = yesterday.strftime("%d/%m/%Y")
   yesterdayTimeStamp = int(time.mktime(time.strptime(str(yesterdayString + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
   endYesterdayTimeStamp = int(time.mktime(time.strptime(str(yesterdayString + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))



   i = 1
   # LN3206F
   aggregate_pipeline = [
       {
           "$match":
           {
               'created_at': {'$gte' : todayTimeStamp,'$lte' : endTodayTimeStamp},
               'code': '10'
           }
       },
       {
           "$project":
           {
               "account_number": 1,
               "amt": 1,
               "date": 1,
           }
       }
   ]
   data = mongodb.aggregate_pipeline(MONGO_COLLECTION=ln3206_collection,aggregate_pipeline=aggregate_pipeline)
   
   for row in data:
      if 'account_number' in row.keys():
         zaccf = mongodb.getOne(MONGO_COLLECTION=zaccf_collection, WHERE={'account_number': str(row['account_number'])},SELECT=['name','rpy_prn','RPY_INT','RPY_FEE','PRODGRP_ID'])
         if zaccf != None:
            row['name']             = zaccf['name']
            row['paid_principal']   = int(float(zaccf['rpy_prn']))
            row['paid_interest']    = int(float(zaccf['RPY_INT']))
            row['RPY_FEE']          = int(float(zaccf['RPY_FEE']))
            product = mongodb.getOne(MONGO_COLLECTION=product_collection, WHERE={'code': str(zaccf['PRODGRP_ID'])},SELECT=['name'])
            if product != None:
               row['product_name'] = product['name']
            else:
              row['product_name'] = ''

         if len(str(row['date'])) == 5:
            row['date']       = '0'+str(row['date'])
         date                 = str(row['date'])
         d1                   = date[0:2]+'/'+date[2:4]+'/'+date[4:6]
         row['payment_date']  = int(time.mktime(time.strptime(str(d1 + " 00:00:00"), "%d/%m/%y %H:%M:%S")))

         lnjc05 = mongodb.getOne(MONGO_COLLECTION=lnjc05_collection, WHERE={'account_number': str(row['account_number'])},SELECT=['due_date','group_id'])
         if lnjc05 != None:
            row['group']      = lnjc05['group_id']
            date_time = datetime.fromtimestamp(lnjc05['due_date'])
            d2       = date_time.strftime('%d/%m/%y')
            tdelta   = datetime.strptime(d1, '%d/%m/%y') - datetime.strptime(d2, '%d/%m/%y')
            row['num_of_overdue_day'] = tdelta.days
            row['due_date']   = lnjc05['due_date']

         diallistInfo = mongodb.getOne(MONGO_COLLECTION=diallist_collection, WHERE={'account_number': str(row['account_number']), 'createdAt': {'$gte' : yesterdayTimeStamp,'$lte' : endYesterdayTimeStamp} },
                     SELECT=['assign'])
         if diallistInfo != None:
            if 'assign' in diallistInfo.keys():
               users = _mongodb.getOne(MONGO_COLLECTION=user_collection,WHERE={'extension': str(diallistInfo['assign'])}, SELECT=['extension','agentname'])
               if users != None:
                  row['pic'] = diallistInfo['assign'] + ' - ' + users['agentname']

         
         row['note'] = ''
         row.pop('_id')
         row.pop('date')
         # row['stt'] = i
         row['createdAt'] = int(yesterdayTimeStamp)
         row['createdBy'] = 'system'
         insertData.append(row)
         i += 1
         # break

   if len(insertData) > 0:
      mongodb.batch_insert(MONGO_COLLECTION=collection, insert_data=insertData)


   # Report_input_payment_of_card
   code = ['2000','2100']
   aggregate_pipeline = [
       {
           "$match":
           {
               'created_at': {'$gte' : todayTimeStamp,'$lte' : endTodayTimeStamp},
               '$or' : [ { "code" : '2000' }, {"code" : '2100' }]
           }
       },
       {
           "$project":
           {
               "account_number": 1,
               "amount": 1,
               "effective_date": 1,
           }
       }
   ]
   PaymentData = mongodb.aggregate_pipeline(MONGO_COLLECTION=payment_of_card_collection,aggregate_pipeline=aggregate_pipeline)
   
   for row in PaymentData:
      if 'account_number' in row.keys():
         sbv = mongodb.getOne(MONGO_COLLECTION=sbv_collection, WHERE={'contract_no': str(row['account_number'])},SELECT=['name','repayment_principal','repayment_interest','repayment_fees','card_type'])
         if sbv != None:
            row['name']             = sbv['name']
            row['paid_principal']   = int(float(sbv['repayment_principal']))
            row['paid_interest']    = int(float(sbv['repayment_interest']))
            row['RPY_FEE']          = int(float(sbv['repayment_fees']))
            if int(sbv['card_type']) < 100:
              row['product_name'] = '301 – Credit card'
            else:
              row['product_name'] = '302 – Cash card'
            # product_card = mongodb.getOne(MONGO_COLLECTION=product_collection, WHERE={'code': product_id },SELECT=['name'])
            # if product != None:
            #    row['product_name'] = product['name']


         row['effective_date'] = str(int(float(row['effective_date'])))
         if len(str(row['effective_date'])) == 5:
            row['effective_date']       = '0'+str(row['effective_date'])
         date                 = str(row['effective_date'])
         d1                   = date[0:2]+'/'+date[2:4]+'/'+date[4:6]
         row['payment_date']  = int(time.mktime(time.strptime(str(d1 + " 00:00:00"), "%d/%m/%y %H:%M:%S")))

         sbv_stored = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'SBV_Stored'), WHERE={'contract_no': str(row['account_number'])},SELECT=['overdue_indicator','kydue'],SORT=[("_id", -1)], SKIP=0, TAKE=1)
         if sbv_stored != None:
            for store in sbv_stored:
               row['group']                 = store['overdue_indicator'] + store['kydue']

         account = mongodb.getOne(MONGO_COLLECTION=account_collection, WHERE={'account_number': str(row['account_number'])},SELECT=['overdue_date'])
         if account != None:
            date_time   = datetime.fromtimestamp(account['overdue_date'])
            d2          = date_time.strftime('%d/%m/%y')
            row['due_date']   = account['overdue_date']
            tdelta   = datetime.strptime(d1, '%d/%m/%y') - datetime.strptime(d2, '%d/%m/%y')
            row['num_of_overdue_day'] = tdelta.days

         diallistInfo = mongodb.getOne(MONGO_COLLECTION=diallist_collection, WHERE={'account_number': str(row['account_number']), 'createdAt': {'$gte' : yesterdayTimeStamp,'$lte' : endYesterdayTimeStamp} },
                     SELECT=['assign'])
         if diallistInfo != None:
            if 'assign' in diallistInfo.keys():
               users = _mongodb.getOne(MONGO_COLLECTION=user_collection,WHERE={'extension': str(diallistInfo['assign'])}, SELECT=['extension','agentname'])
               if users != None:
                  row['pic'] = diallistInfo['assign'] + ' - ' + users['agentname']


         row['amt'] = row['amount']
         row['note'] = ''
         row.pop('_id')
         row.pop('effective_date')
         row.pop('amount')
         # row['stt'] = i
         row['createdAt'] = int(yesterdayTimeStamp)
         row['createdBy'] = 'system'
         insertDataPayment.append(row)
         i += 1
      # break

   if len(insertDataPayment) > 0:
      mongodb.batch_insert(MONGO_COLLECTION=collection, insert_data=insertDataPayment)

   now_end         = datetime.now()
   log.write(now_end.strftime("%d/%m/%Y, %H:%M:%S") + ': End Log' + '\n')
   print('DONE')
except Exception as e:
   print(traceback.format_exc())
   log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
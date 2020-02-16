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
   today = datetime.strptime('13/02/2020', "%d/%m/%Y").date()

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
   # aggregate_pipeline = [
   #     {
   #         "$match":
   #         {
   #             'created_at': {'$gte' : todayTimeStamp,'$lte' : endTodayTimeStamp},
   #             'code': '10'
   #         }
   #     },
   #     {
   #         "$project":
   #         {
   #             "account_number": 1,
   #             "amt": 1,
   #             "date": 1,
   #         }
   #     }
   # ]
   # data = mongodb.aggregate_pipeline(MONGO_COLLECTION=ln3206_collection,aggregate_pipeline=aggregate_pipeline)



   acc__sibs_arr = []
   aggregate_sibs = [
       {
           "$match":
           {
               'created_at': {'$gte' : todayTimeStamp,'$lte' : endTodayTimeStamp},
               'code' : '10'
           }
       },
       {
           "$group":
           {
               "_id": 'null',
               "acc_arr": {'$addToSet' : '$account_number'},
           }
       }
   ]
   accData = mongodb.aggregate_pipeline(MONGO_COLLECTION=ln3206_collection,aggregate_pipeline=aggregate_sibs)
   if accData != None:
      for row in accData:
         acc__sibs_arr = row['acc_arr']

   for acc in acc__sibs_arr:
      aggregate_paid = [
         {
             "$match":
             {
                 "created_at": {'$gte': todayTimeStamp, '$lte': endTodayTimeStamp},
                 "account_number": acc,
                 "code" : '10',
             }
         },
         {
             "$project":
             {
                 "account_number" : 1,
                 "amt" : 1,
                 "date": 1,
             }
         }
      ]
      paidData = mongodb.aggregate_pipeline(MONGO_COLLECTION=ln3206_collection,aggregate_pipeline=aggregate_paid)
      sum_amount = 0
      date = ''
      if paidData != None:
         for row in paidData:
            date = row['date']
            sum_amount += row['amt']

         temp = {
            'account_number' : acc
         }

         zaccf = mongodb.getOne(MONGO_COLLECTION=zaccf_collection, WHERE={'account_number': str(acc)},SELECT=['name','rpy_prn','RPY_INT','RPY_FEE','PRODGRP_ID'])
         if zaccf != None:
            temp['name']             = zaccf['name']
            temp['paid_principal']   = int(float(zaccf['rpy_prn']))
            temp['paid_interest']    = int(float(zaccf['RPY_INT']))
            temp['RPY_FEE']          = int(float(zaccf['RPY_FEE']))
            product = mongodb.getOne(MONGO_COLLECTION=product_collection, WHERE={'code': str(zaccf['PRODGRP_ID'])},SELECT=['name'])
            if product != None:
               temp['product_name'] = product['name']
            else:
              temp['product_name'] = ''

         if len(str(date)) == 5:
            date       = '0'+str(date)
         due_date                = str(date)
         d1                      = due_date[0:2]+'/'+due_date[2:4]+'/'+due_date[4:6]
         temp['payment_date']    = int(time.mktime(time.strptime(str(d1 + " 00:00:00"), "%d/%m/%y %H:%M:%S")))

         lnjc05 = mongodb.getOne(MONGO_COLLECTION=lnjc05_collection, WHERE={'account_number': str(acc)},SELECT=['due_date','group_id'])
         if lnjc05 != None:
            temp['group']      = lnjc05['group_id']
            date_time = datetime.fromtimestamp(lnjc05['due_date'])
            d2       = date_time.strftime('%d/%m/%y')
            tdelta   = datetime.strptime(d1, '%d/%m/%y') - datetime.strptime(d2, '%d/%m/%y')
            temp['num_of_overdue_day'] = tdelta.days
            temp['due_date']   = lnjc05['due_date']

         diallistInfo = mongodb.getOne(MONGO_COLLECTION=diallist_collection, WHERE={'account_number': str(acc), 'createdAt': {'$gte' : yesterdayTimeStamp,'$lte' : endYesterdayTimeStamp} },
                     SELECT=['assign'])
         if diallistInfo != None:
            if 'assign' in diallistInfo.keys():
               users = _mongodb.getOne(MONGO_COLLECTION=user_collection,WHERE={'extension': str(diallistInfo['assign'])}, SELECT=['extension','agentname'])
               if users != None:
                  temp['pic'] = diallistInfo['assign'] + ' - ' + users['agentname']


         temp['note'] = ''

         temp['createdAt'] = int(yesterdayTimeStamp)
         temp['createdBy'] = 'system'
         insertData.append(temp)
         i += 1
         # break

   if len(insertData) > 0:
      mongodb.batch_insert(MONGO_COLLECTION=collection, insert_data=insertData)


   # Report_input_payment_of_card
   code = ['2000','2100','2700']
   acc_arr = []
   aggregate_gl = [
       {
           "$match":
           {
               'created_at': {'$gte' : todayTimeStamp,'$lte' : endTodayTimeStamp},
               'code' : {'$in' : code}
           }
       },
       {
           "$group":
           {
               "_id": 'null',
               "acc_arr": {'$addToSet' : '$account_number'},
           }
       }
   ]
   accData = mongodb.aggregate_pipeline(MONGO_COLLECTION=payment_of_card_collection,aggregate_pipeline=aggregate_gl)
   if accData != None:
      for row in accData:
         accData = row['acc_arr']

   for acc in accData:
      aggregate_paid = [
         {
             "$match":
             {
                 "created_at": {'$gte': todayTimeStamp, '$lte': endTodayTimeStamp},
                 "account_number": acc,
                 "code" : {'$in' : code},
             }
         },
         {
             "$project":
             {
                 "account_number" : 1,
                 "amount" : 1,
                 "code" : 1,
                 "effective_date": 1,
             }
         }
      ]
      paidData = mongodb.aggregate_pipeline(MONGO_COLLECTION=payment_of_card_collection,aggregate_pipeline=aggregate_paid)
      code_2000 = 0
      code_2700 = 0
      sum_amount = 0
      effective_date = ''
      if paidData != None:
         for row in paidData:
            if row['code'] == '2000' or row['code'] == '2100':
               effective_date = row['effective_date']
               code_2000 += row['amount']
            else:
               code_2700 += row['amount']
         sum_amount = code_2000 - code_2700
         if sum_amount > 0:
            temp = {
               'account_number' : acc
            }
            sbv = mongodb.getOne(MONGO_COLLECTION=sbv_collection, WHERE={'contract_no': str(acc)},SELECT=['name','repayment_principal','repayment_interest','repayment_fees','card_type'])
            if sbv != None:
               temp['name']             = sbv['name']
               temp['paid_principal']   = int(float(sbv['repayment_principal']))
               temp['paid_interest']    = int(float(sbv['repayment_interest']))
               temp['RPY_FEE']          = int(float(sbv['repayment_fees']))
               if int(sbv['card_type']) < 100:
                 temp['product_name'] = '301 – Credit card'
               else:
                 temp['product_name'] = '302 – Cash card'
               # product_card = mongodb.getOne(MONGO_COLLECTION=product_collection, WHERE={'code': product_id },SELECT=['name'])
               # if product != None:
               #    row['product_name'] = product['name']


            effective_date = str(int(float(effective_date)))
            if len(str(effective_date)) == 5:
               effective_date       = '0'+str(effective_date)
            date                 = str(effective_date)
            d1                   = date[0:2]+'/'+date[2:4]+'/'+date[4:6]
            temp['payment_date']  = int(time.mktime(time.strptime(str(d1 + " 00:00:00"), "%d/%m/%y %H:%M:%S")))

            sbv_stored = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'SBV_Stored'), WHERE={'contract_no': str(acc)},SELECT=['overdue_indicator','kydue'],SORT=[("_id", -1)], SKIP=0, TAKE=1)
            if sbv_stored != None:
               for store in sbv_stored:
                  temp['group']                 = store['overdue_indicator'] + store['kydue']

            account = mongodb.getOne(MONGO_COLLECTION=account_collection, WHERE={'account_number': str(acc)},SELECT=['overdue_date'])
            if account != None:
               date_time   = datetime.fromtimestamp(account['overdue_date'])
               d2          = date_time.strftime('%d/%m/%y')
               temp['due_date']   = account['overdue_date']
               tdelta   = datetime.strptime(d1, '%d/%m/%y') - datetime.strptime(d2, '%d/%m/%y')
               temp['num_of_overdue_day'] = tdelta.days

            diallistInfo = mongodb.getOne(MONGO_COLLECTION=diallist_collection, WHERE={'account_number': str(acc), 'createdAt': {'$gte' : yesterdayTimeStamp,'$lte' : endYesterdayTimeStamp} },
                        SELECT=['assign'])
            if diallistInfo != None:
               if 'assign' in diallistInfo.keys():
                  users = _mongodb.getOne(MONGO_COLLECTION=user_collection,WHERE={'extension': str(diallistInfo['assign'])}, SELECT=['extension','agentname'])
                  if users != None:
                     temp['pic'] = diallistInfo['assign'] + ' - ' + users['agentname']


            temp['amt'] = sum_amount
            temp['note'] = ''
            # row['stt'] = i
            temp['createdAt'] = int(yesterdayTimeStamp)
            temp['createdBy'] = 'system'
            insertDataPayment.append(temp)
            i += 1

   if len(insertDataPayment) > 0:
      mongodb.batch_insert(MONGO_COLLECTION=collection, insert_data=insertDataPayment)

   now_end         = datetime.now()
   log.write(now_end.strftime("%d/%m/%Y, %H:%M:%S") + ': End Log' + '\n')
   print('DONE')
except Exception as e:
   print(traceback.format_exc())
   log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
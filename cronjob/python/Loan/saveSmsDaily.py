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
collection         = common.getSubUser(subUserType, 'Sms_daily_report')
lnjc05_collection  = common.getSubUser(subUserType, 'LNJC05')
group_collection     = common.getSubUser(subUserType, 'Group_card')
account_collection   = common.getSubUser(subUserType, 'List_of_account_in_collection')

log         = open(base_url + "cronjob/python/Loan/log/SMSDaily_log.txt","a")
log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': Start Import' + '\n')

try:
   data        = []
   insertData  = []
   PaymentData   = []

   today = date.today()
   # today = datetime.strptime('12/10/2019', "%d/%m/%Y").date()

   day = today.day
   month = today.month
   year = today.year
   weekday = today.weekday()
   lastDayOfMonth = calendar.monthrange(year, month)[1]

   todayString = today.strftime("%d/%m/%Y")
   todayTimeStamp = int(time.mktime(time.strptime(str(todayString + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))

   startMonth = int(time.mktime(time.strptime(str('01/' + str(month) + '/' + str(year) + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
   endMonth = int(time.mktime(time.strptime(str(str(lastDayOfMonth) + '/' + str(month) + '/' + str(year) + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))

   holidayOfMonth = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_off_sys'))
   listHoliday = map(lambda offDateRow: {offDateRow['off_date']}, holidayOfMonth)

   if todayTimeStamp in listHoliday or (weekday == 5) or weekday == 6:
      sys.exit()

   # SIBS
   count = mongodb.count(MONGO_COLLECTION=lnjc05_collection)
   quotient = int(count)/10000
   mod = int(count)%10000
   if quotient != 0:
      for x in range(int(quotient)):
         result = mongodb.get(MONGO_COLLECTION=lnjc05_collection,
          SELECT=['overdue_amount_this_month','advance_balance','installment_type','group_id','account_number','mobile_num','cus_name'],SORT=([('_id', -1)]),SKIP=int(x*10000), TAKE=int(10000))
         for idx,row in enumerate(result):
            if (float(row['overdue_amount_this_month']) - float(row['advance_balance']) > 40000) or (float(row['overdue_amount_this_month']) - float(row['advance_balance']) < 40000 and row['installment_type'] == 'n'):
               data.append(row)

   if int(mod) > 0:
      result = mongodb.get(MONGO_COLLECTION=lnjc05_collection,
         SELECT=['overdue_amount_this_month','advance_balance','installment_type','group_id','account_number','mobile_num','cus_name'], SORT=([('_id', -1)]),SKIP=int(int(quotient)*10000), TAKE=int(mod))
      for idx,row in enumerate(result):
         if (float(row['overdue_amount_this_month']) - float(row['advance_balance']) > 40000) or (float(row['overdue_amount_this_month']) - float(row['advance_balance']) < 40000 and row['installment_type'] == 'n'):
            data.append(row)

   for key,row in enumerate(data):
      temp = {}
      if 'account_number' in row.keys():
         temp['type']            = 'sibs'
         temp['stt']             = key
         temp['account_number']  = row['account_number']
         temp['group']           = row['group_id']
         temp['phone']           = row['mobile_num']
         temp['name']            = row['cus_name']
         temp['amount']          = row['overdue_amount_this_month']
         temp['sending_date']    = now.strftime("%d/%m/%Y")
         temp['createdAt']       = time.time()
         insertData.append(temp)

   # card
   count = mongodb.count(MONGO_COLLECTION=account_collection)
   quotient = int(count)/10000
   mod = int(count)%10000
   for x in range(int(quotient)):
      result = mongodb.get(MONGO_COLLECTION=account_collection, SELECT=['phone','cus_name','overdue_amt','cur_bal','account_number'],SORT=([('_id', -1)]),SKIP=int(x*10000), TAKE=int(10000))
      for idx,row in enumerate(result):
         PaymentData.append(row)

   if int(mod) > 0:
      result = mongodb.get(MONGO_COLLECTION=account_collection,SELECT=['phone','cus_name','overdue_amt','cur_bal','account_number'], SORT=([('_id', -1)]),SKIP=int(int(quotient)*10000), TAKE=int(mod))
      for idx,row in enumerate(result):
         PaymentData.append(row)

   for key,row in enumerate(PaymentData):
      temp = {}
      if 'account_number' in row.keys():
         group = mongodb.getOne(MONGO_COLLECTION=group_collection,WHERE={'account_number':str(row['account_number'])},SELECT=['group'])
         temp['type']            = 'card'
         temp['stt']             = key
         temp['account_number']  = row['account_number']
         if group != None:
            temp['group']        = group['group']
         else:
            temp['group']        = ''
         temp['phone']           = row['phone']
         temp['name']            = row['cus_name']
         temp['os']              = row['overdue_amt']
         temp['amount']          = row['cur_bal']
         temp['sending_date']    = now.strftime("%d/%m/%Y")
         temp['createdAt']       = time.time()
         insertData.append(temp)

   if len(insertData) > 0:
      mongodb.batch_insert(MONGO_COLLECTION=collection, insert_data=insertData)
   now_end         = datetime.now()
   log.write(now_end.strftime("%d/%m/%Y, %H:%M:%S") + ': End Log' + '\n')
   print('DONE')
except Exception as e:
   pprint(e)
   log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
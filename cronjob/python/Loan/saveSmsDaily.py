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
import pandas as pd
import xlsxwriter

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
config_collection         = common.getSubUser(subUserType, 'Dial_config')

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
   endTodayTimeStamp = int(time.mktime(time.strptime(str(todayString + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))

   startMonth = int(time.mktime(time.strptime(str('01/' + str(month) + '/' + str(year) + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
   endMonth = int(time.mktime(time.strptime(str(str(lastDayOfMonth) + '/' + str(month) + '/' + str(year) + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))

   holidayOfMonth = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_off_sys'))
   listHoliday = map(lambda offDateRow: {offDateRow['off_date']}, holidayOfMonth)

   if todayTimeStamp in listHoliday:
      sys.exit()

   mongodb.remove_document(MONGO_COLLECTION=collection, WHERE={'createdAt': {'$gte': todayTimeStamp, '$lte': endTodayTimeStamp} })


   price = mongodb.getOne(MONGO_COLLECTION=config_collection, SELECT=['conditionDonotCall']) 


   # SIBS
   aggregate_acc = [
      {
           "$lookup":
           {
               "from": common.getSubUser(subUserType, 'ZACCF_report'),
               "localField": "account_number",
               "foreignField": "account_number",
               "as": "detail"
           }
      },{
          "$match":
          {
              "detail.PRODGRP_ID": {'$in': ['103','402','502','602','702','802','902']},
          }
      }
   ]
   data_acc = mongodb.aggregate_pipeline(MONGO_COLLECTION=lnjc05_collection,aggregate_pipeline=aggregate_acc)
   if data_acc != None:
      for key,row in enumerate(data_acc):
         temp = {}
         if 'account_number' in row.keys():
            temp['type']            = 'sibs'
            temp['stt']             = key
            temp['account_number']  = row['account_number']
            temp['group']           = row['group_id']
            temp['phone']           = row['mobile_num']
            temp['name']            = row['cus_name']
            temp['amount']          = float(row['overdue_amount_this_month']) - float(row['advance_balance'])
            temp['sending_date']    = now.strftime("%d/%m/%Y")
            temp['createdAt']       = time.time()
            insertData.append(temp)

   aggregate_acc = [
      {
           "$lookup":
           {
               "from": common.getSubUser(subUserType, 'ZACCF_report'),
               "localField": "account_number",
               "foreignField": "account_number",
               "as": "detail"
           }
      },{
          "$match":
          {
              "detail.PRODGRP_ID": {'$in': ['103','402','502','602','702','802','902']},
          }
      },{
          "$group":
          {
              "_id": 'null',
              "acc_arr": {'$push': '$account_number'},
          }
      }
   ]
   data_acc_1 = mongodb.aggregate_pipeline(MONGO_COLLECTION=lnjc05_collection,aggregate_pipeline=aggregate_acc)
   acc_arr = []
   if data_acc_1 != None:
      for row in data_acc_1:
         acc_arr = row['acc_arr']


   aggregate_acc_2 = [
      {
          "$match":
          {
              "account_number": {'$nin': acc_arr},
          }
      }
   ]
   data_acc_2 = mongodb.aggregate_pipeline(MONGO_COLLECTION=lnjc05_collection,aggregate_pipeline=aggregate_acc_2)
   if data_acc_2 != None:
      for row in data_acc_2:
         if (float(row['overdue_amount_this_month']) - float(row['advance_balance']) > price['conditionDonotCall']) or (float(row['overdue_amount_this_month']) - float(row['advance_balance']) <= price['conditionDonotCall'] and row['installment_type'] == 'n' and row['outstanding_principal'] > 0):
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
         temp['amount']          = float(row['overdue_amount_this_month']) - float(row['advance_balance'])
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
         temp['type']            = 'card'
         temp['stt']             = key
         temp['account_number']  = row['account_number']
         sbv_store = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'SBV_Stored'), WHERE={'contract_no': str(row['account_number'])},SELECT=['overdue_indicator','kydue'])
         if sbv_store != None:
            temp['group']                    = sbv_store['overdue_indicator']+sbv_store['kydue']
         temp['phone']           = row['phone']
         temp['name']            = row['cus_name']
         temp['os']              = float(row['overdue_amt'])
         temp['amount']          = float(row['cur_bal'])
         temp['sending_date']    = now.strftime("%d/%m/%Y")
         temp['createdAt']       = time.time()
         insertData.append(temp)

   if len(insertData) > 0:
      mongodb.batch_insert(MONGO_COLLECTION=collection, insert_data=insertData)

   




   fileOutput  = base_url + 'upload/loan/export/SMS Daily Report_'+ today.strftime("%d%m%Y") +'.xlsx' 

   aggregate_acc = [
      {
          "$match":
          {
              "createdAt": {'$gte' : todayTimeStamp},
              "type": 'sibs'
          }
      },
      {
         "$project":
          {
              "_id": 0,
          }
      },
      {
         "$sort":
          {
              "group": 1,
              "amount": 1
          }
      }
   ]
   data = mongodb.aggregate_pipeline(MONGO_COLLECTION=collection,aggregate_pipeline=aggregate_acc)

   aggregate_card = [
      {
          "$match":
          {
              "createdAt": {'$gte' : todayTimeStamp},
              "type": 'card'
          }
      },
      {
         "$project":
          {
              "_id": 0,
          }
      },
      {
         "$sort":
          {
              "group": 1,
              "amount": 1
          }
      }
   ]
   dataCard = mongodb.aggregate_pipeline(MONGO_COLLECTION=collection,aggregate_pipeline=aggregate_card)
   # dataReport = []
   # for row in data:
   #    temp = row
   #    # if 'os' in row.keys():
   #    #    temp['os']      = '{:,.2f}'.format(float(row['os']))

   #    # if 'amount' in row.keys():
   #    #    temp['amount']      = '{:,.2f}'.format(float(row['amount']))

   #    dataReport.append(temp)


   df = pd.DataFrame(data, columns= ['stt','group','account_number','phone','name','amount'])

   # Create a Pandas Excel writer using XlsxWriter as the engine.
   writer = pd.ExcelWriter(fileOutput, engine='xlsxwriter')

   # Convert the dataframe to an XlsxWriter Excel object.
   df.to_excel(writer,sheet_name='SIBS',index=False,header=['No','GROUP','ACCOUNT NUMBER','PHONE','NAME','AMOUNT'])  
   
   # Get the xlsxwriter workbook and worksheet objects.
   workbook  = writer.book
   worksheet = writer.sheets['SIBS']

   # Add some cell formats.
   format1 = workbook.add_format({'num_format': '#,##0', 'bottom':1, 'top':1, 'left':1, 'right':1})
   # format2 = workbook.add_format({'num_format': '0%'})
   border_fmt = workbook.add_format({'bottom':1, 'top':1, 'left':1, 'right':1})


   # Set the column width and format.
   worksheet.set_column('A:F', 20, border_fmt)

   worksheet.set_column('F:F', 20, format1)
   # Set the format but not the column width.

   # Close the Pandas Excel writer and output the Excel file.
   # writer.save()



   # CARD
   df = pd.DataFrame(dataCard, columns= ['stt','group','account_number','phone','name','amount', 'os'])

   # Create a Pandas Excel writer using XlsxWriter as the engine.
   # writer = pd.ExcelWriter(fileOutput, engine='xlsxwriter')

   # Convert the dataframe to an XlsxWriter Excel object.
   df.to_excel(writer,sheet_name='CARD',index=False,header=['No','GROUP','ACCOUNT NUMBER','PHONE','NAME','OS','AMOUNT'])  
   
   # Get the xlsxwriter workbook and worksheet objects.
   workbook  = writer.book
   worksheet = writer.sheets['CARD']

   # Add some cell formats.
   format1 = workbook.add_format({'num_format': '#,##0', 'bottom':1, 'top':1, 'left':1, 'right':1})
   # format2 = workbook.add_format({'num_format': '0%'})
   border_fmt = workbook.add_format({'bottom':1, 'top':1, 'left':1, 'right':1})


   # Set the column width and format.
   worksheet.set_column('A:G', 20, border_fmt)

   worksheet.set_column('F:F', 20, format1)
   worksheet.set_column('G:G', 20, format1)
   # Set the format but not the column width.

   # Close the Pandas Excel writer and output the Excel file.
   writer.save()



   now_end         = datetime.now()
   log.write(now_end.strftime("%d/%m/%Y, %H:%M:%S") + ': End Log' + '\n')
   print('DONE')
except Exception as e:
   pprint(e)
   log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
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
log         = open(base_url + "cronjob/python/Loan/log/exportDailySMS_log.txt","a")

try:
   data        = []
   insertData  = []
   resultData  = []
   errorData   = []

   today = date.today()
   # today = datetime.strptime('22/12/2019', "%d/%m/%Y").date()

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

   if todayTimeStamp in listHoliday:
      sys.exit()

   fileOutput  = base_url + 'upload/loan/export/SMS Daily Report - SIBS_'+ today.strftime("%d%m%Y") +'.xlsx' 

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
      }
   ]
   data = mongodb.aggregate_pipeline(MONGO_COLLECTION=collection,aggregate_pipeline=aggregate_acc)
   dataReport = []
   for row in data:
      temp = row
      if 'os' in row.keys():
         temp['os']      = '{:,.2f}'.format(float(row['os']))

      if 'amount' in row.keys():
         temp['amount']      = '{:,.2f}'.format(float(row['amount']))

      dataReport.append(temp)


   df = pd.DataFrame(dataReport, columns= ['stt','account_number','group','phone','name','amount','sending_date'])
   writer = pd.ExcelWriter(fileOutput, engine='xlsxwriter')
   df.to_excel(writer,sheet_name='SIBS',index=False,header=['No','ACCOUNT NUMBER','GROUP','PHONE','NAME','AMOUNT','SENDING DATE'])  
   workbook = writer.book
   worksheet = writer.sheets['SIBS']

   border_fmt = workbook.add_format({'bottom':1, 'top':1, 'left':1, 'right':1})
   worksheet.conditional_format(xlsxwriter.utility.xl_range(0, 0, len(df), len(df.columns)), {'type': 'no_errors', 'format': border_fmt})
   for i, col in enumerate(df.columns):
      column_len = df[col].astype(str).str.len().max()
      column_len = max(column_len, len(col))
      worksheet.set_column(i, i, column_len)

   writer.save()


   now_end         = datetime.now()
   log.write(now_end.strftime("%d/%m/%Y, %H:%M:%S") + ': End Log' + '\n')
   print('DONE')
except Exception as e:
   pprint(e)
   log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
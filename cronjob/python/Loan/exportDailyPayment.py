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
from datetime import date, timedelta
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
collection         = common.getSubUser(subUserType, 'Daily_payment_report')
log         = open(base_url + "cronjob/python/Loan/log/exportDailyPayment_log.txt","a")

try:
   data        = []
   insertData  = []
   resultData  = []
   errorData   = []

   today = date.today()
   today = datetime.strptime('13/02/2020', "%d/%m/%Y").date()

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

   yesterday   = today - timedelta(days=1)
   yesterdayString = yesterday.strftime("%d/%m/%Y")
   yesterdayTimeStamp = int(time.mktime(time.strptime(str(yesterdayString + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
   endYesterdayTimeStamp = int(time.mktime(time.strptime(str(yesterdayString + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))

   fileOutput  = base_url + 'upload/loan/export/DailyPayment_'+ yesterday.strftime("%d%m%Y") +'.xlsx'

   aggregate_acc = [
      {
          "$match":
          {
              "createdAt": {'$gte' : yesterdayTimeStamp, '$lte' : endYesterdayTimeStamp},
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
      temp = {}
      temp = row
      print(temp['product_name'])
      try:
         if 'due_date' in row.keys():
            date_time = datetime.fromtimestamp(int(row['due_date']))
            temp['due_date']      = date_time.strftime('%d-%m-%Y')
      except Exception as e:
         temp['due_date']      = row['due_date']

      if 'payment_date' in row.keys():
         if row['payment_date'] != None:
            date_time = datetime.fromtimestamp(row['payment_date'])
            temp['payment_date']      = date_time.strftime('%d-%m-%Y')
         else:
            temp['payment_date']      = ''

      dataReport.append(temp)

   df = pd.DataFrame(dataReport, columns= ['account_number','name','due_date','payment_date','amt','paid_principal','paid_interest','RPY_FEE','group','num_of_overdue_day','pic','product_name','note'])

   # Create a Pandas Excel writer using XlsxWriter as the engine.
   writer = pd.ExcelWriter(fileOutput, engine='xlsxwriter')

   # Convert the dataframe to an XlsxWriter Excel object.
   df.to_excel(writer,sheet_name='Sheet1',header=['AC NUMBER','NAME','OVERDUE DATE','PAYMENT DATE','AMOUNT','PAID PRINCIPAL','PAID INTEREST','PAID LATE CHARGE & FEE','GROUP','NUMBER OF OVERDUE DAYS','PIC','PRODUCT','NOTE']) 
   
   # Get the xlsxwriter workbook and worksheet objects.
   workbook  = writer.book
   worksheet = writer.sheets['Sheet1']

   # Add some cell formats.
   format1 = workbook.add_format({'num_format': '#,##0', 'bottom':1, 'top':1, 'left':1, 'right':1})
   # format2 = workbook.add_format({'num_format': '0%'})
   border_fmt = workbook.add_format({'bottom':1, 'top':1, 'left':1, 'right':1})


   # Set the column width and format.
   worksheet.set_column('A:N', 20, border_fmt)

   worksheet.set_column('F:I', 20, format1)
   # worksheet.set_column('H:H', 20, format1)
   # worksheet.set_column('I:I', 20, format1)

   writer.save()


   now_end         = datetime.now()
   # log.write(now_end.strftime("%d/%m/%Y, %H:%M:%S") + ': End Log' + '\n')
   print('DONE')
except Exception as e:
   pprint(e)
   log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
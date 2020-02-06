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


   df = pd.DataFrame(data, columns= ['stt','account_number','group','phone','name','amount','sending_date'])

   # Create a Pandas Excel writer using XlsxWriter as the engine.
   writer = pd.ExcelWriter(fileOutput, engine='xlsxwriter')

   # Convert the dataframe to an XlsxWriter Excel object.
   df.to_excel(writer,sheet_name='SIBS',index=False,header=['No','ACCOUNT NUMBER','GROUP','PHONE','NAME','AMOUNT','SENDING DATE'])  
   
   # Get the xlsxwriter workbook and worksheet objects.
   workbook  = writer.book
   worksheet = writer.sheets['SIBS']

   # Add some cell formats.
   format1 = workbook.add_format({'num_format': '#,##0', 'bottom':1, 'top':1, 'left':1, 'right':1})
   # format2 = workbook.add_format({'num_format': '0%'})
   border_fmt = workbook.add_format({'bottom':1, 'top':1, 'left':1, 'right':1})


   # Set the column width and format.
   worksheet.set_column('A:G', 20, border_fmt)

   worksheet.set_column('F:F', 20, format1)
   # Set the format but not the column width.

   # Close the Pandas Excel writer and output the Excel file.
   # writer.save()



   # CARD
   df = pd.DataFrame(dataCard, columns= ['stt','account_number','group','phone','name','os', 'amount','sending_date'])

   # Create a Pandas Excel writer using XlsxWriter as the engine.
   # writer = pd.ExcelWriter(fileOutput, engine='xlsxwriter')

   # Convert the dataframe to an XlsxWriter Excel object.
   df.to_excel(writer,sheet_name='CARD',index=False,header=['No','ACCOUNT NUMBER','GROUP','PHONE','NAME','OS','AMOUNT','SENDING DATE'])  
   
   # Get the xlsxwriter workbook and worksheet objects.
   workbook  = writer.book
   worksheet = writer.sheets['CARD']

   # Add some cell formats.
   format1 = workbook.add_format({'num_format': '#,##0', 'bottom':1, 'top':1, 'left':1, 'right':1})
   # format2 = workbook.add_format({'num_format': '0%'})
   border_fmt = workbook.add_format({'bottom':1, 'top':1, 'left':1, 'right':1})


   # Set the column width and format.
   worksheet.set_column('A:H', 20, border_fmt)

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
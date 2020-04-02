#!/usr/bin/python3.6
# -*- coding: utf-8 -*-
import sys
import os
import time
import ntpath
import json
import calendar
import urllib
from helper.mongod import Mongodb
from datetime import datetime
from datetime import date
from pprint import pprint
from bson import ObjectId
from helper.common import Common
import pandas as pd
from helper.jaccs import Config
import xlsxwriter
import traceback

common      = Common()
base_url    = common.base_url()
wff_env     = common.wff_env(base_url)
mongodb     = Mongodb(MONGODB="worldfone4xs", WFF_ENV=wff_env)
_mongodb    = Mongodb(MONGODB="_worldfone4xs", WFF_ENV=wff_env)

now         = datetime.now()
subUserType = 'LO'
collection         = common.getSubUser(subUserType, 'Reminder_letter_report')
log         = open(base_url + "cronjob/python/Loan/log/exportReminder_letter_report_log.txt","a")
log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': Start Import' + '\n')


# fileOutput = "C:\\Users\\DELL\\Desktop\\temp-excel.xlsx"
try:
   data        = []
   insertData  = []
   resultData  = []
   errorData   = []

   today = date.today()
   # today = datetime.strptime('28/03/2020', "%d/%m/%Y").date()

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

   fileOutput  = base_url + 'upload/loan/export/Reminder Letter Report_'+ today.strftime("%d%m%Y") +'.xlsx'

   try:
      date = sys.argv[1]
      today = datetime.strptime(date, "%d/%m/%Y").date()
      todayString = today.strftime("%d/%m/%Y")
      todayTimeStamp = int(time.mktime(time.strptime(str(todayString + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
      endTodayTimeStamp = int(time.mktime(time.strptime(str(todayString + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))
      aggregate_acc = [
         {
             "$match":
             {
                 "createdAt" : {'$gte' : todayTimeStamp,'$lte' : endTodayTimeStamp},
             }
         },
         {
            "$project":
             {
                 "_id": 0,
             }
         }
      ]

   except Exception as SysArgvError:
      aggregate_acc = [
         {
             "$match":
             {
                 "createdAt" : {'$gte' : todayTimeStamp,'$lte' : endTodayTimeStamp},
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
      # if 'loan_overdue_amount' in row.keys():
      #    temp['loan_overdue_amount']      = '{:,.2f}'.format(float(row['loan_overdue_amount']))

      # if 'current_balance' in row.keys():
      #    temp['current_balance']      = '{:,.2f}'.format(float(row['current_balance']))

      # if 'outstanding_principal' in row.keys():
      #    try:
      #       temp['outstanding_principal']      = '{:,.2f}'.format(float(row['outstanding_principal']))
      #    except Exception as e:
      #       temp['outstanding_principal']      = row['outstanding_principal']


      try:
         if 'due_date' in row.keys():
            date_time = datetime.fromtimestamp(int(row['due_date']))
            temp['due_date']      = date_time.strftime('%d-%m-%Y')
      except Exception as e:
         temp['due_date']      = row['due_date']
      
      try:
         if 'cif_birth_date' in row.keys():
            if row['cif_birth_date'] != None:
               date_time = datetime.fromtimestamp(row['cif_birth_date'])
               temp['cif_birth_date']      = date_time.strftime('%d-%m-%Y')
            else:
               temp['cif_birth_date']      = ''
      except Exception as e:
         temp['cif_birth_date']      = row['cif_birth_date']

      try:
         if 'license_date' in row.keys():
            if row['license_date'] != None:
               date_time = datetime.fromtimestamp(row['license_date'])
               temp['license_date']      = date_time.strftime('%d-%m-%Y')
      except Exception as e:
         temp['license_date']      = row['license_date']

      if 'createdAt' in row.keys():
         if row['createdAt'] != None:
            date_time = datetime.fromtimestamp(row['createdAt'])
            temp['createdAt']      = date_time.strftime('%d-%m-%Y')

      dataReport.append(temp)

   
   df = pd.DataFrame(dataReport, columns= ['index','account_number','name','address','contract_date','day','month','year','approved_amt','cur_bal','overdue_amt','phone','createdAt','due_date','overdue_date','group','product_code','outstanding_bal','pic','product_name','dealer_name','brand','model','engine_no','chassis_no','color','license_plates','production_time','license_no','cif_birth_date','license_date'])

   # Create a Pandas Excel writer using XlsxWriter as the engine.
   writer = pd.ExcelWriter(fileOutput, engine='xlsxwriter')

   # Convert the dataframe to an XlsxWriter Excel object.
   df.to_excel(writer,sheet_name='Sheet1',index=False,header=['No','Account number','Customer name','Customer address','Signed contract date','Day','Month','Year','Approved amount','Current balance','Overdue amount','Phone number','Sending date','Due date','Overdue date','Group','Product code','Outstanding balance','PIC','Product name','Dealer name','Brand','Kiểu xe','Số máy','Số khung','Màu xe','Biển Số','Năm sản xuất','CMND','cif birth date','license date'])

   # Get the xlsxwriter workbook and worksheet objects.
   workbook  = writer.book
   worksheet = writer.sheets['Sheet1']

   # Add some cell formats.
   
   format1 = workbook.add_format({'num_format': '#,##0', 'bottom':1, 'top':1, 'left':1, 'right':1})
   format2 = workbook.add_format({'num_format': 'dd-mm-yy', 'bottom':1, 'top':1, 'left':1, 'right':1})
   # format2 = workbook.add_format({'num_format': '0%'})
   border_fmt = workbook.add_format({'bottom':1, 'top':1, 'left':1, 'right':1})


   # Set the column width and format.
   worksheet.set_column('A:AE', 20, border_fmt)

   worksheet.set_column('I:K', 20, format1)
   worksheet.set_column('M:N', 20, format2)
   worksheet.set_column('R:R', 20, format1)
   worksheet.set_column('AD:AE', 20, format2)

   writer.save()

   now_end         = datetime.now()
   log.write(now_end.strftime("%d/%m/%Y, %H:%M:%S") + ': End Log' + '\n')
   print('DONE')
except Exception as e:
   print(traceback.format_exc())
   log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
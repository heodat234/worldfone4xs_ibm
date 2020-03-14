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
collection         = common.getSubUser(subUserType, 'List_of_all_customer_report')
log         = open(base_url + "cronjob/python/Loan/log/List_of_all_customer_report_log.txt","a")

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
   endTodayTimeStamp = int(time.mktime(time.strptime(str(todayString + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))
   dateExport = "0"+ str( int(time.strftime('%m'))-1 ) + today.strftime("%Y")

   startMonth = int(time.mktime(time.strptime(str('01/' + str(month) + '/' + str(year) + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
   endMonth = int(time.mktime(time.strptime(str(str(lastDayOfMonth) + '/' + str(month) + '/' + str(year) + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))
   
   holidayOfMonth = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_off_sys'))
   listHoliday = map(lambda offDateRow: {offDateRow['off_date']}, holidayOfMonth)

   # if day != 1:
   #    print('stop!')  
   #    sys.exit()

   fileOutput  = base_url + 'upload/loan/export/ListofallcustomerReport_'+ dateExport +'.xlsx' 

   aggregate_acc = [
      {
          "$match":
          {
              "createdAt": {'$gte' : todayTimeStamp,'$lte' : endTodayTimeStamp},
        
          }
      },
      # { '$sort' : { '_id' : -1} },
      # { "$limit": 1000 },
      {
         "$project":
          {
              "_id": 0,
          }
      }
   ]
   data = mongodb.aggregate_pipeline(MONGO_COLLECTION=collection,aggregate_pipeline=aggregate_acc)

  
   df = pd.DataFrame(data, columns= ['DT_TX','ACC_ID','CUS_ID','CUS_NM','Loan_Group','CAR_ID','TERM_ID','W_ORG','TOTAL_ACC','TOTAL_CUS','PRODGRP_ID','LIC_NO','interest_rate','Dealer_code','Province_code'])
   # Create a Pandas Excel writer using XlsxWriter as the engine.
   writer = pd.ExcelWriter(fileOutput, engine='xlsxwriter')

   # Convert the dataframe to an XlsxWriter Excel object.
   df.to_excel(writer,sheet_name='AllLoanGroup',header=['DT_TX','ACC_ID','CUS_ID','CUS_NM','Loan Group based on ZACCF and TB','CAR_ID based on ZACCF file','TERM_ID based on ZACCF file','W_ORG on ZACCF file and TB','Total No. of ACC','Total No. of customer','PRODGRP_ID','LIC_NO','Interest rate (%/year)','Dealer code','Province code'],index = False) 
   # Get the xlsxwriter workbook and worksheet objects.
   workbook  = writer.book
   worksheet = writer.sheets['AllLoanGroup']

   # Add some cell formats.
   format1 = workbook.add_format({'num_format': '#,##0', 'bottom':1, 'top':1, 'left':1, 'right':1})
   format2 = workbook.add_format({'num_format': '0.00%','bottom':1, 'top':1, 'left':1, 'right':1})
   border_fmt = workbook.add_format({'bottom':1, 'top':1, 'left':1, 'right':1})
   header_fmt = workbook.add_format({'bottom':1, 'top':1, 'left':1, 'right':1, 'bold': True, 'fg_color': '#008738','font_color': '#ffffff','text_wrap': True,})
   date_fmt = workbook.add_format({'num_format': 'dd/mm/yy'})
    

   # Set the column width and format.
   worksheet.set_column('A:O', 20, border_fmt)
   worksheet.set_column('M:M', 20, format2)
   # worksheet.set_row(0, 30, header_fmt)
   # worksheet.set_column('B:B', 30, date_fmt)

    
   
   worksheet.set_column('H:H', 30, format1)
   # Set the format but not the column width.
   # Write the column headers with the defined format.
  
  
   # Close the Pandas Excel writer and output the Excel file.
   writer.save()



   now_end         = datetime.now()
   log.write(now_end.strftime("%d/%m/%Y, %H:%M:%S") + ': End Log' + '\n')
   print('DONE')
except Exception as e:
   pprint(e)
   log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
#!/usr/bin/python3.6
# -*- coding: utf-8 -*-
import sys
import os
import time
import ntpath
import json
from helper.mongod import Mongodb
from datetime import datetime
from datetime import date
from pprint import pprint
from bson import ObjectId
from helper.common import Common
import pandas as pd
from helper.jaccs import Config
import xlsxwriter

common      = Common()
base_url    = common.base_url()
wff_env     = common.wff_env(base_url)
mongodb     = Mongodb(MONGODB="worldfone4xs", WFF_ENV=wff_env)
_mongodb    = Mongodb(MONGODB="_worldfone4xs", WFF_ENV=wff_env)

now         = datetime.now()
subUserType = 'LO'
collection         = common.getSubUser(subUserType, 'Master_data_report')
log         = open(base_url + "cronjob/python/Loan/log/exportMasterData_log.txt","a")
fileOutput  = base_url + 'upload/loan/export/MasterData.xlsx' 
try:
   data        = []
   insertData  = []
   resultData  = []
   errorData   = []

   count = mongodb.count(MONGO_COLLECTION=collection)
   quotient = int(count)/10000
   mod = int(count)%10000
   dem = 0
   if quotient != 0:
      for x in range(int(quotient)):
         result = mongodb.get(MONGO_COLLECTION=collection, SORT=([('_id', -1)]),SKIP=int(x*10000), TAKE=int(10000))
         for idx,row in enumerate(result):
            # if row['due_date'] != '':
            #    due_date = datetime.fromtimestamp(row['due_date'])
            #    row['due_date']       = due_date.strftime("%d-%m-%Y")
            
            data.append(row)

   if int(mod) > 0:
      result = mongodb.get(MONGO_COLLECTION=collection, SORT=([('_id', -1)]),SKIP=int(int(quotient)*10000), TAKE=int(mod))
      for idx,row in enumerate(result):
         # if row['due_date'] != '':
         #    due_date = datetime.fromtimestamp(row['due_date'])
         #    row['due_date']       = due_date.strftime("%d-%m-%Y")
         
         data.append(row)


   df = pd.DataFrame(data, columns= ['account_number','cus_name','BIR_DT8','CUS_ID','FRELD8','product_name','LIC_NO','APPROV_LMT','TERM_ID','RPY_PRD','F_PDT','DT_MAT','current_balance','CURRENT_DPD','MOBILE_NO','WRK_REF','current_add','current_district','current_province','pernament_add','pernament_district','pernament_province','W_ORG','INT_RATE','OVER_DY','DATE_HANDOVER','license_plates_no','COMPANY'])
   # df.to_excel(writer,sheet_name='Daily',header=['CONTRACTNR','CLIENT_NAME','BIRTH_DATE','CIF','SIGNED_DATE','PRODUCTNAME','ID NO','CREDIT AMOUNT','INSTALLMENT NUMBER','INSTALMENT AMOUNT','DATE_FIRST_DUE','DATE_LAST_DUE','CURRENT_DEBT','CURRENT_DPD','PHONE NUMBER','REFERENCE PHONE','Current_ADDRESS (if any)','District','PROVINCE','PERNAMENT_ADDRESS','District','PROVINCE','PRINCIPAL','INTEREST/ year','DPD','DATE HANDOVER','lICENSE PLATES NO','COMPANY']) 
   writer = pd.ExcelWriter(fileOutput, engine='xlsxwriter')
   df.to_excel(writer,sheet_name='Sheet1',header=['CONTRACTNR','CLIENT_NAME','BIRTH_DATE','CIF','SIGNED_DATE','PRODUCTNAME','ID NO','CREDIT AMOUNT','INSTALLMENT NUMBER','INSTALMENT AMOUNT','DATE_FIRST_DUE','DATE_LAST_DUE','CURRENT_DEBT','CURRENT_DPD','PHONE NUMBER','REFERENCE PHONE','Current_ADDRESS (if any)','District','PROVINCE','PERNAMENT_ADDRESS','District','PROVINCE','PRINCIPAL','INTEREST/ year','DPD','DATE HANDOVER','lICENSE PLATES NO','COMPANY']) 
   workbook = writer.book
   worksheet = writer.sheets['Sheet1']

   border_fmt = workbook.add_format({'bottom':1, 'top':1, 'left':1, 'right':1})
   worksheet.conditional_format(xlsxwriter.utility.xl_range(0, 0, len(df), len(df.columns)), {'type': 'no_errors', 'format': border_fmt})
   for i, col in enumerate(df.columns):
      # find length of column i
      column_len = df[col].astype(str).str.len().max()
      # Setting the length if the column header is larger
      # than the max column value length
      column_len = max(column_len, len(col))
      # set the column length
      worksheet.set_column(i, i, column_len)

   writer.save()

   now_end         = datetime.now()
   log.write(now_end.strftime("%d/%m/%Y, %H:%M:%S") + ': End Log' + '\n')
   print('DONE')
except Exception as e:
   pprint(e)
   log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
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

mongodb     = Mongodb("worldfone4xs")
_mongodb    = Mongodb("_worldfone4xs")
common      = Common()
now         = datetime.now()
subUserType = 'LO'
collection         = common.getSubUser(subUserType, 'Daily_payment_report')
log         = open("/var/www/html/worldfone4xs_ibm/cronjob/python/Loan/log/exportDailyPayment_log.txt","a")
fileOutput  = '/var/www/html/worldfone4xs_ibm/upload/loan/export/DailyPayment.xlsx' 
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


   df = pd.DataFrame(data, columns= ['account','name','due_date','payment_date','amt','paid_principal','paid_interest','RPY_FEE','group','num_of_overdue_day','pic','product_name','note'])
   df.to_excel(fileOutput,sheet_name='Daily',header=['AC NUMBER','NAME','OVERDUE DATE','PAYMENT DATE','AMOUNT','PAID PRINCIPAL','PAID INTEREST','PAID LATE CHARGE & FEE','GROUP','NUMBER OF OVERDUE DAYS','PIC','PRODUCT','NOTE']) 
   
   now_end         = datetime.now()
   log.write(now_end.strftime("%d/%m/%Y, %H:%M:%S") + ': End Log' + '\n')
   print(1)
except Exception as e:
   pprint(e)
   log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
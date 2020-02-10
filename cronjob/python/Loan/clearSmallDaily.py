#!/usr/bin/python3.6
# -*- coding: utf-8 -*-
import sys
import os
import time
import ntpath
import json
import calendar
import traceback
import math
from helper.mongod import Mongodb
from datetime import datetime
from datetime import date
from pprint import pprint
from bson import ObjectId
from helper.common import Common
from helper.jaccs import Config
# help round down
def round_down(n, decimals=0):
    multiplier = 10 ** decimals
    return math.floor(n * multiplier) / multiplier
#help
common      = Common()
base_url    = common.base_url()
wff_env     = common.wff_env(base_url)
mongodb     = Mongodb(MONGODB="worldfone4xs", WFF_ENV=wff_env)
_mongodb    = Mongodb(MONGODB="_worldfone4xs", WFF_ENV=wff_env)
now         = datetime.now()
subUserType = 'LO'

collection           = common.getSubUser(subUserType, 'Clear_small_daily_report')

lnjc05_collection    = common.getSubUser(subUserType, 'LNJC05')
zaccf_collection     = common.getSubUser(subUserType, 'ZACCF')

release_sale_collection   = common.getSubUser(subUserType, 'Report_release_sale')
investigation_collection  = common.getSubUser(subUserType, 'Investigation_file')
sbv_collection            = common.getSubUser(subUserType, 'SBV')
trialBalance_collection   = common.getSubUser(subUserType, 'Trial_balance_report')
wo_monthly_collection     = common.getSubUser(subUserType, 'WO_monthly')
diallist_collection       = common.getSubUser(subUserType, 'Diallist_detail')
config_collection         = common.getSubUser(subUserType, 'Dial_config')
user_collection           = common.getSubUser(subUserType, 'User')

log         = open(base_url + "cronjob/python/Loan/log/ClearSmall_log.txt","a")
log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': Start Import' + '\n')
try:
   data        = []
   cardData        = []
   insertData  = []
   resultData  = []
   errorData   = []

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

   if todayTimeStamp in listHoliday:
      sys.exit()

   users = _mongodb.get(MONGO_COLLECTION=user_collection, SELECT=['extension','agentname'],SORT=([('_id', -1)]),SKIP=0, TAKE=200)


   # writeOf
   aggregate_pipeline = [
      
       {
           "$project":
           {
            #    col field
               "group_id": 1, 
               "account_number": 1,
               "cus_name": 1,
               "overdue_amount_this_month": 1,
               'installment_type': 1,
               'advance_balance': 1,
               'outstanding_principal': 1,
           }
       }
   ]
   price = mongodb.getOne(MONGO_COLLECTION=config_collection, SELECT=['conditionDonotCall']) 
   data = mongodb.aggregate_pipeline(MONGO_COLLECTION=lnjc05_collection,aggregate_pipeline=aggregate_pipeline)
   count = 0
   for row in data:
      if 'account_number' in row.keys():
        condition = row['overdue_amount_this_month'] - row['advance_balance']
        if condition <= price['conditionDonotCall']:
        
            zaccf = mongodb.getOne(MONGO_COLLECTION=zaccf_collection, WHERE={'account_number': str(row['account_number'])},
                SELECT=['PRODGRP_ID'])    
            if zaccf != None:
                if zaccf['PRODGRP_ID'] != "103" and zaccf['PRODGRP_ID'] != "402" and zaccf['PRODGRP_ID'] != "502" and zaccf['PRODGRP_ID'] != "602" and zaccf['PRODGRP_ID'] != "702" and zaccf['PRODGRP_ID'] != "802" and zaccf['PRODGRP_ID'] != "902":
                        count +=1
                        temp = {}
                        if row['installment_type'] =='n' and row['outstanding_principal'] == 0:
                            temp['No'] = count 
                            temp['Account_No'] = row['account_number']
                            temp['cus_name'] = row['cus_name']
                            temp['Amount'] = float(row['overdue_amount_this_month']) - float(row['advance_balance'])
                            temp['Income'] = ''
                            temp['Expense'] = 'x'
                            temp['Group'] = row['group_id']
                            temp['Product'] = zaccf['PRODGRP_ID']
                            temp['Empty_column'] = "Kì Cuối"
                            temp['createdAt'] = time.time()
                            temp['createdBy'] = 'system'   
                            insertData.append(temp)
                        elif row['installment_type'] !='n' and row['outstanding_principal'] > 0:
                            temp['No'] = count 
                            temp['Account_No'] = row['account_number']
                            temp['cus_name'] = row['cus_name']
                            temp['Amount'] = float(row['overdue_amount_this_month']) - float(row['advance_balance'])
                            temp['Income'] = ''
                            temp['Expense'] = 'x'
                            temp['Group'] = row['group_id']
                            temp['Product'] = zaccf['PRODGRP_ID']
                            temp['Empty_column'] = ""
                            temp['createdAt'] = time.time()
                            temp['createdBy'] = 'system'   
                            insertData.append(temp)
 

   if len(insertData) > 0:
      mongodb.remove_document(MONGO_COLLECTION=collection)
      mongodb.batch_insert(MONGO_COLLECTION=collection, insert_data=insertData)
   now_end         = datetime.now()
   log.write(now_end.strftime("%d/%m/%Y, %H:%M:%S") + ': End Log' + '\n')
   print('DONE')
except Exception as e:
    pprint(e)
    traceback.print_exc()
    log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
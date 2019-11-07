#!/usr/bin/python3.6
# -*- coding: utf-8 -*-
import sys
import os
# sys.path.insert(1, '/var/www/html/worldfone4xs_ibm/cronjob/python')
import time
import ntpath
import json
from helper.mongod import Mongodb
from datetime import datetime
from datetime import date
from pprint import pprint
from bson import ObjectId
from helper.common import Common

mongodb     = Mongodb("worldfone4xs")
_mongodb    = Mongodb("_worldfone4xs")
common      = Common()
now         = datetime.now()
subUserType = 'LO'
collection         = common.getSubUser(subUserType, 'Daily_payment_report')
lnjc05_collection  = common.getSubUser(subUserType, 'LNJC05')
ln3206_collection  = common.getSubUser(subUserType, 'LN3206F')
zaccf_collection   = common.getSubUser(subUserType, 'ZACCF')
product_collection  = common.getSubUser(subUserType, 'Product')
log         = open("/var/www/html/worldfone4xs_ibm/cronjob/python/Loan/log/DailyPayment_log.txt","a")

try:
   data        = []
   insertData  = []
   resultData  = []
   errorData   = []

   count = mongodb.count(MONGO_COLLECTION=ln3206_collection)
   quotient = int(count)/10000
   mod = int(count)%10000
   dem = 0
   if quotient != 0:
      for x in range(int(quotient)):
         result = mongodb.get(MONGO_COLLECTION=ln3206_collection, SELECT=['account','amt','date'],SORT=([('_id', -1)]),SKIP=int(x*10000), TAKE=int(10000))
         for idx,row in enumerate(result):
            data.append(row)

   if int(mod) > 0:
      result = mongodb.get(MONGO_COLLECTION=ln3206_collection,SELECT=['account','amt','date'], SORT=([('_id', -1)]),SKIP=int(int(quotient)*10000), TAKE=int(mod))
      for idx,row in enumerate(result):
         data.append(row)

   for row in data:
      if 'account' in row.keys():
         zaccf = mongodb.getOne(MONGO_COLLECTION=zaccf_collection, WHERE={'ACC_ID': str(row['account'])},SELECT=['CUS_NM','rpy_prn','RPY_INT','RPY_FEE','PRODGRP_ID'])
         if zaccf != None:
            row['name'] = zaccf['CUS_NM']
            row['paid_principal'] = zaccf['rpy_prn']
            row['paid_interest'] = zaccf['RPY_INT']
            row['RPY_FEE'] = zaccf['RPY_FEE']
            product = mongodb.getOne(MONGO_COLLECTION=product_collection, WHERE={'code': str(zaccf['PRODGRP_ID'])},SELECT=['due_date','group_id'])
            if product != None:
               row['name'] = product['name']

         if len(row['date']) == 5:
            row['date'] = '0'+str(row['date'])
         date = str(row['date'])
         d1 = date[0:2]+'-'+date[2:4]+'-'+date[4:6]
         row['payment_date'] = int(time.mktime(time.strptime(str(d1), '%d-%m-%y')))

         lnjc05 = mongodb.getOne(MONGO_COLLECTION=lnjc05_collection, WHERE={'account_number': str(row['account'])},SELECT=['due_date','group_id'])
         if lnjc05 != None:
            row['due_date'] = lnjc05['due_date']
            row['group'] = lnjc05['group_id']

            due_date = datetime.fromtimestamp(row['due_date'])
            FMT = '%d-%m-%y'
            d2 = due_date.strftime("%d-%m-%y")
            tdelta = datetime.strptime(d1, FMT) - datetime.strptime(d2, FMT)
            row['num_of_overdue_day'] = tdelta.days

      row['pic'] = ''
      row['note'] = ''
      row.pop('_id')
      insertData.append(row)
      break

   # print(insertData)
   if len(insertData) > 0:
      mongodb.remove_document(MONGO_COLLECTION=collection)
      mongodb.batch_insert(MONGO_COLLECTION=collection, insert_data=insertData)
   now_end         = datetime.now()
   log.write(now_end.strftime("%d/%m/%Y, %H:%M:%S") + ': End Log' + '\n')
   # print(count)
except Exception as e:
    # pprint(e)
    log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
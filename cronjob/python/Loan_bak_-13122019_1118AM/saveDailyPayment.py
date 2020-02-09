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
from helper.jaccs import Config

common      = Common()
base_url    = common.base_url()
wff_env     = common.wff_env(base_url)
mongodb     = Mongodb(MONGODB="worldfone4xs", WFF_ENV=wff_env)
_mongodb    = Mongodb(MONGODB="_worldfone4xs", WFF_ENV=wff_env)
now         = datetime.now()
subUserType = 'LO'
collection         = common.getSubUser(subUserType, 'Daily_payment_report')
lnjc05_collection  = common.getSubUser(subUserType, 'LNJC05')
ln3206_collection  = common.getSubUser(subUserType, 'LN3206F')
zaccf_collection   = common.getSubUser(subUserType, 'ZACCF')
product_collection   = common.getSubUser(subUserType, 'Product')
sbv_collection       = common.getSubUser(subUserType, 'SBV')
group_collection     = common.getSubUser(subUserType, 'Group_card')
account_collection   = common.getSubUser(subUserType, 'List_of_account_in_collection')
payment_of_card_collection  = common.getSubUser(subUserType, 'Report_input_payment_of_card')
# log         = open(base_url + "cronjob/python/Loan/log/DailyPayment_log.txt","a")

try:
   data        = []
   insertData  = []
   PaymentData   = []

   now         = datetime.now()
   # log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': Start Import' + '\n')
   # LN3206F
   count = mongodb.count(MONGO_COLLECTION=ln3206_collection)
   quotient = int(count)/10000
   mod = int(count)%10000
   if quotient != 0:
      for x in range(int(quotient)):
         result = mongodb.get(MONGO_COLLECTION=ln3206_collection, SELECT=['account_number','amt','date'],SORT=([('_id', -1)]),SKIP=int(x*10000), TAKE=int(10000))
         for idx,row in enumerate(result):
            data.append(row)

   if int(mod) > 0:
      result = mongodb.get(MONGO_COLLECTION=ln3206_collection,SELECT=['account_number','amt','date'], SORT=([('_id', -1)]),SKIP=int(int(quotient)*10000), TAKE=int(mod))
      for idx,row in enumerate(result):
         data.append(row)

   for row in data:
      if 'account_number' in row.keys():
         zaccf = mongodb.getOne(MONGO_COLLECTION=zaccf_collection, WHERE={'account_number': str(row['account_number'])},SELECT=['name','rpy_prn','RPY_INT','RPY_FEE','PRODGRP_ID'])
         if zaccf != None:
            row['name']             = zaccf['name']
            row['paid_principal']   = zaccf['rpy_prn']
            row['paid_interest']    = zaccf['RPY_INT']
            row['RPY_FEE']          = zaccf['RPY_FEE']
            product = mongodb.getOne(MONGO_COLLECTION=product_collection, WHERE={'code': str(zaccf['PRODGRP_ID'])},SELECT=['name'])
            if product != None:
               row['product_name'] = product['name']
            else:
               row['product_name'] = ''
         # else:
         #    row['name']             = ''
         #    row['paid_principal']   = ''
         #    row['paid_interest']    = ''
         #    row['RPY_FEE']          = ''
         #    row['product_name']     = ''

         if len(row['date']) == 5:
            row['date']       = '0'+str(row['date'])
         date                 = str(row['date'])
         d1                   = date[0:2]+'/'+date[2:4]+'/'+date[4:6]
         row['payment_date']  = d1

         lnjc05 = mongodb.getOne(MONGO_COLLECTION=lnjc05_collection, WHERE={'account_number': str(row['account_number'])},SELECT=['due_date','group_id'])
         if lnjc05 != None:
            row['group']      = lnjc05['group_id'] 
            date_time = datetime.fromtimestamp(lnjc05['due_date'])
            d2       = date_time.strftime('%d/%m/%y')
            tdelta   = datetime.strptime(d1, '%d/%m/%y') - datetime.strptime(d2, '%d/%m/%y')
            row['num_of_overdue_day'] = tdelta.days
            row['due_date']   = d2
         else:
            row['due_date']            = ''
            row['group']               = ''
            row['num_of_overdue_day']  = ''
      row['pic'] = ''
      row['note'] = ''
      row.pop('_id')
      row.pop('date')
      insertData.append(row)
      # break

   # Report_input_payment_of_card
   count = mongodb.count(MONGO_COLLECTION=payment_of_card_collection)
   quotient = int(count)/10000
   mod = int(count)%10000
   for x in range(int(quotient)):
      result = mongodb.get(MONGO_COLLECTION=payment_of_card_collection, SELECT=['account_number','effective_date','amount'],SORT=([('_id', -1)]),SKIP=int(x*10000), TAKE=int(10000))
      for idx,row in enumerate(result):
         PaymentData.append(row)

   if int(mod) > 0:
      result = mongodb.get(MONGO_COLLECTION=payment_of_card_collection,SELECT=['account_number','effective_date','amount'], SORT=([('_id', -1)]),SKIP=int(int(quotient)*10000), TAKE=int(mod))
      for idx,row in enumerate(result):
         PaymentData.append(row)

   for row in PaymentData:
      if 'account_number' in row.keys():
         sbv = mongodb.getOne(MONGO_COLLECTION=sbv_collection, WHERE={'contract_no': str(row['account_number'])},SELECT=['name','repayment_principal','repayment_interest','repayment_fees','card_type'])
         if sbv != None:
            row['name']             = sbv['name']
            row['paid_principal']   = sbv['repayment_principal']
            row['paid_interest']    = sbv['repayment_interest']
            row['RPY_FEE']          = sbv['repayment_fees']
            product = mongodb.getOne(MONGO_COLLECTION=product_collection, WHERE={'code': str(sbv['card_type'])},SELECT=['name'])
            if product != None:
               row['product_name'] = product['name']
            else:
               row['product_name'] = ''
         else:
            row['name']             = ''
            row['paid_principal']   = ''
            row['paid_interest']    = ''
            row['RPY_FEE']          = ''
            row['product_name']     = ''

         row['effective_date'] = str(int(float(row['effective_date'])))
         if len(row['effective_date']) == 5:
            row['effective_date']       = '0'+str(row['effective_date'])
         date                 = str(row['effective_date'])
         d1                   = date[0:2]+'/'+date[2:4]+'/'+date[4:6]
         row['payment_date']  = d1

         group = mongodb.getOne(MONGO_COLLECTION=group_collection, WHERE={'account_number': str(row['account_number'])},SELECT=['group'])
         if group != None:
            row['group'] = group['group']

         account = mongodb.getOne(MONGO_COLLECTION=account_collection, WHERE={'account_number': str(row['account_number'])},SELECT=['overdue_date'])
         if account != None:
            date_time   = datetime.fromtimestamp(account['overdue_date'])
            d2          = date_time.strftime('%d/%m/%y')
            row['due_date']   = d2
            # d2       = account['overdue']
            tdelta   = datetime.strptime(d1, '%d/%m/%y') - datetime.strptime(d2, '%d/%m/%Y')
            row['num_of_overdue_day'] = tdelta.days
         
         row['amt'] = row['amount']
         row['pic'] = ''
         row['note'] = ''
         row.pop('_id')
         row.pop('effective_date')
         row.pop('amount')
         insertData.append(row)
      # break

   # print(insertData)
   if len(insertData) > 0:
      mongodb.remove_document(MONGO_COLLECTION=collection)
      mongodb.batch_insert(MONGO_COLLECTION=collection, insert_data=insertData)
   now_end         = datetime.now()
   # log.write(now_end.strftime("%d/%m/%Y, %H:%M:%S") + ': End Log' + '\n')
   print('DONE')
except Exception as e:
   pprint(e)
   # log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
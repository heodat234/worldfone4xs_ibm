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
collection         = common.getSubUser(subUserType, 'Reminder_letter_report')
lnjc05_collection  = common.getSubUser(subUserType, 'LNJC05')
ln3206_collection  = common.getSubUser(subUserType, 'LN3206F')
zaccf_collection   = common.getSubUser(subUserType, 'ZACCF')
product_collection   = common.getSubUser(subUserType, 'Product')
sbv_collection       = common.getSubUser(subUserType, 'SBV')
group_collection     = common.getSubUser(subUserType, 'Group_card')
account_collection   = common.getSubUser(subUserType, 'List_of_account_in_collection')
payment_of_card_collection  = common.getSubUser(subUserType, 'Report_input_payment_of_card')
diallist_detail_collection  = common.getSubUser(subUserType, 'Diallist_detail')
cdr_collection       = common.getSubUser(subUserType, 'worldfonepbxmanager')
jsonData_collection  = common.getSubUser(subUserType, 'Jsondata')
user_collection      = common.getSubUser(subUserType, 'User')
wo_collection        = common.getSubUser(subUserType, 'WO_monthly')
# log         = open(base_url + "cronjob/python/Loan/log/Reminder_letter_log.txt","a")

try:
   data        = []
   insertData  = []
   now         = datetime.now()
   # log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': Start Import' + '\n')

   # Account
   aggregate_acc = [
      {
           "$lookup":
           {
               "from": lnjc05_collection,
               "localField": "account_number",
               "foreignField": "account_number",
               "as": "detailLC05"
           }
      },{
          "$match":
          {
              "detailLC05.account_number": {'$exists' : 'true'},
          }
      }
   ]
   data_faccf = mongodb.aggregate_pipeline(MONGO_COLLECTION=zaccf_collection,aggregate_pipeline=aggregate_acc)

   account_number_arr = []
   i = 1
   if data_faccf != None:
      for row in data_faccf:
         for detail in row['detailLC05']:
            temp = {
               'index'           : i,
               'account_number'  : row['account_number'],
               'name'            : row['name'],
               'address'         : row['ADDR_1'],
               'contract_date'   : row['CIF_CR8'],
               'day'             : row['CIF_CR8'],
               'group'           : '',
               'createdBy'       : 'system',
               'createdAt'       : time.time()
            }

            FMT      = '%d-%m-%y'
            d1       = now.strftime(FMT)
            date_time = datetime.fromtimestamp(detail['due_date'])
            d2       = date_time.strftime(FMT)
            tdelta   = datetime.strptime(d1, FMT) - datetime.strptime(d2, FMT)
            print(datetime.strptime(d2, FMT).day)
            break
            # if tdelta.days == 35:
               

            #    break
         break

   # accountInfo = mongodb.get(MONGO_COLLECTION=account_collection,WHERE={'account_number': {'$in' : account_number_arr}})
   # if accountInfo != None:
   #    i = 1
   #    for acc_row in accountInfo:
   #       temp = {
   #          'index'           : i,
   #          'account_number'  : acc_row['account_number'],
   #          'name'            : acc_row['cus_name'],
   #          'block'           : 'true',
   #          'accl'            : '',
   #          'sibs'            : '',
   #          'group'           : '',
   #          'createdBy'       : 'system',
   #          'createdAt'       : time.time()
   #       }
   #       groupInfo = mongodb.getOne(MONGO_COLLECTION=group_collection,WHERE={'account_number': acc_row['account_number']})
   #       if groupInfo  != None:
   #          group = groupInfo['group']
   #          temp['accl'] = group

   #       insertData.append(temp)
   #       i += 1
   

   # if len(insertData) > 0:
   #    # mongodb.remove_document(MONGO_COLLECTION=collection)
   #    mongodb.batch_insert(MONGO_COLLECTION=collection, insert_data=insertData)
   # now_end         = datetime.now()
   # log.write(now_end.strftime("%d/%m/%Y, %H:%M:%S") + ': End Log' + '\n')
   print('DONE')
except Exception as e:
   pprint(e)
   # log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
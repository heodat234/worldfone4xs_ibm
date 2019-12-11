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

mongodb     = Mongodb("worldfone4xs",'DEV')
_mongodb    = Mongodb("_worldfone4xs",'DEV')
common      = Common()
now         = datetime.now()
subUserType = 'LO'
collection         = common.getSubUser(subUserType, 'Block_card_report')
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
# log         = open("/var/www/html/worldfone4xs_ibm/cronjob/python/Loan/log/DailyAssignment_log.txt","a")

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
               "from": group_collection,
               "localField": "account_number",
               "foreignField": "account_number",
               "as": "detail"
           }
      },{
          "$match":
          {
              "detail.group_number": '02',
          }
      },{
          "$group":
          {
              "_id": 'null',
              "acc_arr": {'$push': '$account_number'},
          }
      }
   ]
   data_acc = mongodb.aggregate_pipeline(MONGO_COLLECTION=account_collection,aggregate_pipeline=aggregate_acc)

   account_number_arr = []
   for row in data_acc:
      account_number_arr = row['acc_arr']

   accountInfo = mongodb.get(MONGO_COLLECTION=account_collection,WHERE={'account_number': {'$in' : account_number_arr}})
   if accountInfo != None:
      i = 1
      for acc_row in accountInfo:
         temp = {
            'index'           : i,
            'account_number'  : acc_row['account_number'],
            'name'            : acc_row['cus_name'],
            'block'           : 'true',
            'accl'            : '',
            'sibs'            : '',
            'group'           : '',
            'createdBy'       : 'system',
            'createdAt'       : time.time()
         }
         groupInfo = mongodb.getOne(MONGO_COLLECTION=group_collection,WHERE={'account_number': acc_row['account_number']})
         if groupInfo  != None:
            group = groupInfo['group']
            temp['accl'] = group

         insertData.append(temp)
         i += 1


   # lnjc05
   aggregate_lnjc05 = [
      {
          "$match":
          {
              "group_id": {'$in' : ['B01','B02','B03']},
          }
      },{
           "$lookup":
           {
               "from": zaccf_collection,
               "localField": "account_number",
               "foreignField": "account_number",
               "as": "detailZaccf"
           }
      },{
           "$lookup":
           {
               "from": sbv_collection,
               "localField": "detailZaccf.LIC_NO",
               "foreignField": "license_no",
               "as": "detailSBV"
           }
      },{
          "$match":
          {
              "detailSBV.license_no": {'$exists' : 'true'},
          }
      },{
          "$group":
          {
              "_id": 'null',
              "acc_arr": {'$push': '$account_number'},
          }
      }
   ]
   data_lnjc05 = mongodb.aggregate_pipeline(MONGO_COLLECTION=lnjc05_collection,aggregate_pipeline=aggregate_lnjc05)
   if data_lnjc05 != None:
      account_number_arr = []
      for row in data_lnjc05:
         account_number_arr = row['acc_arr']

   lnjc05Info = mongodb.get(MONGO_COLLECTION=lnjc05_collection,WHERE={'account_number': {'$in' : account_number_arr}})
   if lnjc05Info != None:
      for acc_row in lnjc05Info:
         group = acc_row['group_id']
         temp = {
            'index'           : i,
            'account_number'  : acc_row['account_number'],
            'name'            : acc_row['cus_name'],
            'block'           : 'true',
            'accl'            : '',
            'sibs'            : group,
            'group'           : '',
            'createdBy'       : 'system',
            'createdAt'       : time.time()
         }
         
         insertData.append(temp)
         i += 1

   # print(insertData)
   if len(insertData) > 0:
      # mongodb.remove_document(MONGO_COLLECTION=collection)
      mongodb.batch_insert(MONGO_COLLECTION=collection, insert_data=insertData)
   # now_end         = datetime.now()
   # log.write(now_end.strftime("%d/%m/%Y, %H:%M:%S") + ': End Log' + '\n')
   print('DONE')
except Exception as e:
   pprint(e)
   # log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
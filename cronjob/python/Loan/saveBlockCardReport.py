#!/usr/bin/python3.6
# -*- coding: utf-8 -*-
import sys
import os
import time
import ntpath
import json
import calendar
import traceback
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
k2019_collection        = common.getSubUser(subUserType, 'K201908120244R18')
log         = open(base_url + "cronjob/python/Loan/log/BlockCard_log.txt","a")
log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': Start Import' + '\n')
try:
   data        = []
   insertData  = []
   now         = datetime.now()

   today = date.today()
   # today = datetime.strptime('03/02/2020', "%d/%m/%Y").date()

   day = today.day
   month = today.month
   year = today.year
   weekday = today.weekday()
   lastDayOfMonth = calendar.monthrange(year, month)[1]

   todayString = today.strftime("%d/%m/%Y")
   todayTimeStamp = int(time.mktime(time.strptime(str(todayString + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))

   startMonth = int(time.mktime(time.strptime(str('01/' + str(month) + '/' + str(year) + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
   endMonth = int(time.mktime(time.strptime(str(str(lastDayOfMonth) + '/' + str(month) + '/' + str(year) + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))

   holidayOfMonth = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_off_sys'), WHERE={'off_date': todayTimeStamp})
   if holidayOfMonth != None:
      sys.exit()

   aggregate_blockcard = [
      {
          "$group":
          {
              "_id": 'null',
              "acc_blook_card": {'$push': '$account_number'},
          }
      }
   ]
   blook_acc = mongodb.aggregate_pipeline(MONGO_COLLECTION=collection,aggregate_pipeline=aggregate_blockcard)

   blockCard_arr = []
   for row in blook_acc:
      blockCard_arr = row['acc_blook_card']

   i = 1
   # Account
   aggregate_acc = [
      {
           "$lookup":
           {
               "from": common.getSubUser(subUserType, 'SBV_Stored'),
               "localField": "account_number",
               "foreignField": "contract_no",
               "as": "detail"
           }
      },{
          "$match":
          {
              "detail.overdue_indicator": 'B',
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


   accountInfo = mongodb.get(MONGO_COLLECTION=account_collection,WHERE={'account_number': {'$in' : account_number_arr, '$nin': blockCard_arr}})
   if accountInfo != None:
      for acc_row in accountInfo:
         temp = {
            'index'           : i,
            'account_number'  : acc_row['account_number'],
            'customer_name'            : acc_row['cus_name'],
            'block'           : 'true',
            'acll'            : '',
            'sibs'            : '',
            'group_3_over_other_bank'           : '',
            'createdBy'       : 'system',
            'report_date'       : todayTimeStamp,
            'createdAt'       : todayTimeStamp
         }
         sbv_stored = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'SBV_Stored'), WHERE={'contract_no': str(acc_row['account_number'])},SELECT=['overdue_indicator','kydue'],SORT=[("_id", -1)], SKIP=0, TAKE=1)
         if sbv_stored != None:
            for store in sbv_stored:
               temp['acll']                 = store['overdue_indicator'] + store['kydue']

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
          "$match":
          {
              "detailSBV.contract_no" : {'$nin' : blockCard_arr},
          }
      },{
         "$project":
         {
            "cus_name": 1,
            "detailSBV.contract_no": 1,
            "group_id": 1
         }
      }
      # ,{
      #     "$group":
      #     {
      #         "_id": 'null',
      #         "acc_arr": {'$push': '$account_number'},
      #     }
      # }
   ]
   data_lnjc05 = mongodb.aggregate_pipeline(MONGO_COLLECTION=lnjc05_collection,aggregate_pipeline=aggregate_lnjc05)
   if data_lnjc05 != None:
      account_number_arr = []
      for row in data_lnjc05:
         # account_number_arr = row['acc_arr']
         for detail in row['detailSBV']:
            acc = detail['contract_no']
         temp = {
            'index'           : i,
            'account_number'  : acc,
            'customer_name'            : row['cus_name'],
            'block'           : 'true',
            'acll'            : '',
            'sibs'            : row['group_id'],
            'group_3_over_other_bank'           : '',
            'createdBy'       : 'system',
            'report_date'       : todayTimeStamp,
            'createdAt'       : todayTimeStamp
         }
         insertData.append(temp)
         i += 1







   # k2019
   aggregate_k2019 = [
      {
          "$match":
          {
              "nhom_cao_nhat_tai_tctd_khac": {'$gte' : 3},
          }
      },{
           "$lookup":
           {
               "from": zaccf_collection,
               "localField": "customer_number",
               "foreignField": "CUS_ID",
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
          "$match":
          {
              "detailSBV.contract_no" : {'$nin' : blockCard_arr},
          }
      },{
         "$project":
         {
            "name": 1,
            "detailSBV.contract_no": 1,
            "nhom_cao_nhat_tai_tctd_khac": 1
         }
      }
   ]
   data_k2019 = mongodb.aggregate_pipeline(MONGO_COLLECTION=k2019_collection,aggregate_pipeline=aggregate_k2019)
   if data_k2019 != None:
      account_number_arr = []
      for row in data_k2019:
         for detail in row['detailSBV']:
            acc = detail['contract_no']
         temp = {
            'index'           : i,
            'account_number'  : acc,
            'customer_name'            : row['name'],
            'block'           : 'true',
            'acll'            : '',
            'sibs'            : '',
            'group_3_over_other_bank'           : row['nhom_cao_nhat_tai_tctd_khac'],
            'createdBy'       : 'system',
            'report_date'       : todayTimeStamp,
            'createdAt'       : todayTimeStamp
         }
         insertData.append(temp)
         i += 1

   if len(insertData) > 0:
      mongodb.batch_insert(MONGO_COLLECTION=collection, insert_data=insertData)

   now_end         = datetime.now()
   log.write(now_end.strftime("%d/%m/%Y, %H:%M:%S") + ': End Log' + '\n')
   print('DONE')
except Exception as e:
   print(traceback.format_exc())
   log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
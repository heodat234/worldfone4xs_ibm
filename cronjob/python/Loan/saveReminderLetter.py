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
from helper.jaccs import Config

common      = Common()
base_url    = common.base_url()
wff_env     = common.wff_env(base_url)
mongodb     = Mongodb(MONGODB="worldfone4xs", WFF_ENV=wff_env)
_mongodb    = Mongodb(MONGODB="_worldfone4xs", WFF_ENV=wff_env)

now         = datetime.now()
subUserType = 'LO'
collection                    = common.getSubUser(subUserType, 'Reminder_letter_report')
lnjc05_collection             = common.getSubUser(subUserType, 'LNJC05')
zaccf_collection              = common.getSubUser(subUserType, 'ZACCF_report')
sbv_collection                = common.getSubUser(subUserType, 'SBV')
investigation_collection      = common.getSubUser(subUserType, 'Investigation_file')
account_collection            = common.getSubUser(subUserType, 'List_of_account_in_collection')
report_release_sale_collection   = common.getSubUser(subUserType, 'Report_release_sale')
diallist_detail_collection    = common.getSubUser(subUserType, 'Diallist_detail')
user_collection               = common.getSubUser(subUserType, 'User')



log         = open(base_url + "cronjob/python/Loan/log/Reminder_letter_log.txt","a")
log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': Start Import' + '\n')

try:
   data        = []
   insertData  = []
   now         = datetime.now()

   today = date.today()
   # today = datetime.strptime('13/12/2019', "%d/%m/%Y").date()

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

   i = 1

   # Zaccf
   

   aggregate_pipeline = [
      { "$project": { 'account_number': 1, 'current_balance': 1, 'loan_overdue_amount': 1, 'mobile_num': 1, 'due_date': 1, 'dateDifference' :{"$divide" : [{ "$subtract" : [todayTimeStamp,'$due_date']}, 86400]}  } },
      { "$match" : {'dateDifference': {"$eq": 35} } },
      # {
      #     "$group":
      #     {
      #         "_id": 'null',
      #         "account_arr": {'$push': '$account_number'},
      #         # "lic_no_arr": {'$push': '$detail.LIC_NO'},
      #     }
      # }

   ]
   dataLnjc05 = mongodb.aggregate_pipeline(MONGO_COLLECTION=lnjc05_collection,aggregate_pipeline=aggregate_pipeline)

   if dataLnjc05 != None:
      for row in dataLnjc05:
         zaccf = mongodb.getOne(MONGO_COLLECTION=zaccf_collection, WHERE={'account_number': str(row['account_number']) })
         if zaccf != None:
            temp = {
               'index'           : i,
               'account_number'  : row['account_number'],
               'name'            : zaccf['name'],
               'address'         : zaccf['ADDR_1'],
               'contract_date'   : zaccf['CIF_CR8'],
               'approved_amt'    : zaccf['APPROV_LMT'],
               'cur_bal'         : row['current_balance'],
               'overdue_amt'     : row['loan_overdue_amount'],
               'phone'           : row['mobile_num'],
               'due_date'        : row['due_date'],
               'overdue_date'    : zaccf['OVER_DY'],
               'group'           : zaccf['ODIND_FG'],
               'product_code'    : zaccf['PRODGRP_ID'],
               'outstanding_bal' : row['current_balance'],
               'pic'             : '',
               'product_name'    : zaccf['PROD_NM'],
               'dealer_name'     : zaccf['WRK_BRN'],
               'brand'           : '',
               'model'           : '',
               'engine_no'       : '',
               'chassis_no'      : '',
               'color'           : '',
               'license_plates'  : '',
               'production_time' : '',
               'createdBy'       : 'system',
               'createdAt'       : todayTimeStamp
            }

            
            contract_date  = zaccf['CIF_CR8']
            temp['day']    = contract_date[0:2]
            temp['month']  = contract_date[2:4]
            temp['year']   = contract_date[4:8]

            diallistInfo = mongodb.getOne(MONGO_COLLECTION=diallist_detail_collection, WHERE={'account_number': str(row['account_number'])},SELECT=['assign'])
            if diallistInfo != None:
               if 'assign' in diallistInfo.keys():
                  temp['pic']        = diallistInfo['assign']
                  user = _mongodb.getOne(MONGO_COLLECTION=user_collection, WHERE={'extension': str(diallistInfo['assign'])},SELECT=['agentname'])
                  if user != None:
                     temp['pic']          += '-' + user['agentname']

            releaseInfo = mongodb.getOne(MONGO_COLLECTION=report_release_sale_collection, WHERE={'account_number': str(row['account_number'])},SELECT=['cus_name','temp_address','address'])
            if releaseInfo != None:
               temp['name']        = releaseInfo['cus_name']
               if releaseInfo['temp_address'] != '':
                  temp['address'] = releaseInfo['temp_address']
               else:
                  temp['address'] = releaseInfo['address']

            investigationInfo = mongodb.getOne(MONGO_COLLECTION=investigation_collection, WHERE={'contract_no': str(row['account_number'])},SELECT=['brand','model','engine_no','chassis_no','license_plates_no'])
            if investigationInfo != None:
               temp['brand']        = investigationInfo['brand']
               temp['model']        = investigationInfo['model']
               temp['engine_no']    = investigationInfo['engine_no']
               temp['chassis_no']   = investigationInfo['chassis_no']
               temp['license_plates']   = investigationInfo['license_plates_no']

            insertData.append(temp)
            i += 1







   # sbv
   aggregate_pipeline = [
      { "$project": { 'account_number': 1, 'overdue_amt': 1, 'phone': 1, 'overdue_date': 1, 'cur_bal': 1, 'dateDifference' :{"$divide" : [{ "$subtract" : [todayTimeStamp,'$overdue_date']}, 86400]}  } },
      { "$match" : {'dateDifference': {"$eq": 35} } },

   ]
   dataAccount = mongodb.aggregate_pipeline(MONGO_COLLECTION=account_collection,aggregate_pipeline=aggregate_pipeline)
   if dataAccount != None:
      for row in dataAccount:
         sbv = mongodb.getOne(MONGO_COLLECTION=sbv_collection, WHERE={'account_number': str(row['account_number']) })
         if sbv != None:
            temp = {
               'index'           : i,
               'account_number'  : row['account_number'],
               'name'            : sbv['name'],
               'address'         : sbv['address'],
               'contract_date'   : sbv['open_card_date'],
               'approved_amt'    : sbv['approved_limit'],
               'cur_bal'         : row['cur_bal'],
               'overdue_amt'     : row['overdue_amt'],
               'phone'           : row['phone'],
               'due_date'        : row['overdue_date'],
               'overdue_date'    : sbv['overdue_days_no'],
               'group'           : sbv['delinquency_group'],
               'product_code'    : sbv['card_type'],
               'outstanding_bal' : row['cur_bal'],
               'pic'             : '',
               'product_name'    : '',
               'dealer_name'     : '',
               'brand'           : '',
               'model'           : '',
               'engine_no'       : '',
               'chassis_no'      : '',
               'color'           : '',
               'license_plates'  : '',
               'production_time' : '',
               'createdBy'       : 'system',
               'createdAt'       : todayTimeStamp
            }
            if sbv['card_type'] == '301':
               temp['product_name']  = 'Credit Card'
            else:
               temp['product_name']  = 'Cash Card'

            
            contract_date  = sbv['open_card_date']
            temp['day']    = contract_date[0:2]
            temp['month']  = contract_date[2:4]
            temp['year']   = contract_date[4:8]

            diallistInfo = mongodb.getOne(MONGO_COLLECTION=diallist_detail_collection, WHERE={'account_number': str(row['account_number'])},SELECT=['assign'])
            if diallistInfo != None:
               if 'assign' in diallistInfo.keys():
                  temp['pic']        = diallistInfo['assign']
                  user = _mongodb.getOne(MONGO_COLLECTION=user_collection, WHERE={'extension': str(diallistInfo['assign'])},SELECT=['agentname'])
                  if user != None:
                     temp['pic']          += '-' + user['agentname']

            releaseInfo = mongodb.getOne(MONGO_COLLECTION=report_release_sale_collection, WHERE={'account_number': str(row['account_number'])},SELECT=['cus_name','temp_address','address'])
            if releaseInfo != None:
               temp['name']        = releaseInfo['cus_name']
               if releaseInfo['temp_address'] != '':
                  temp['address'] = releaseInfo['temp_address']
               else:
                  temp['address'] = releaseInfo['address']

            zaccfInfo = mongodb.getOne(MONGO_COLLECTION=zaccf_collection, WHERE={'LIC_NO': str(sbv['license_no'])},SELECT=['WRK_BRN'])
            if zaccfInfo != None:
               temp['dealer_name']        = zaccfInfo['WRK_BRN']

            insertData.append(temp)
            i += 1


   if len(insertData) > 0:
      # mongodb.remove_document(MONGO_COLLECTION=collection)
      mongodb.batch_insert(MONGO_COLLECTION=collection, insert_data=insertData)
   now_end         = datetime.now()
   log.write(now_end.strftime("%d/%m/%Y, %H:%M:%S") + ': End Log' + '\n')
   print('DONE')
except Exception as e:
   pprint(e)
   log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
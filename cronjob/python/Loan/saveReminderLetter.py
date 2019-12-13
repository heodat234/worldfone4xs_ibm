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
collection         = common.getSubUser(subUserType, 'Reminder_letter_report')
lnjc05_collection  = common.getSubUser(subUserType, 'LNJC05')
zaccf_collection   = common.getSubUser(subUserType, 'ZACCF')
sbv_collection       = common.getSubUser(subUserType, 'SBV')
investigation_collection     = common.getSubUser(subUserType, 'Investigation_file')
account_collection   = common.getSubUser(subUserType, 'List_of_account_in_collection')
report_release_sale_collection   = common.getSubUser(subUserType, 'Report_release_sale')
diallist_detail_collection  = common.getSubUser(subUserType, 'Diallist_detail')
log         = open(base_url + "cronjob/python/Loan/log/Reminder_letter_log.txt","a")
log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': Start Import' + '\n')

try:
   data        = []
   insertData  = []
   now         = datetime.now()
   
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

   if todayTimeStamp in listHoliday or (weekday == 5) or weekday == 6:
      sys.exit()

   i = 1

   # Zaccf
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
   
   if data_faccf != None:
      for row in data_faccf:
         for detail in row['detailLC05']:
            temp = {
               'index'           : i,
               'account_number'  : row['account_number'],
               'name'            : row['name'],
               'address'         : row['ADDR_1'],
               'contract_date'   : row['CIF_CR8'],
               'approved_amt'    : row['APPROV_LMT'],
               'cur_bal'         : detail['current_balance'],
               'overdue_amt'     : detail['loan_overdue_amount'],
               'phone'           : detail['mobile_num'],
               'due_date'        : detail['due_date'],
               'overdue_date'    : row['OVER_DY'],
               'group'           : row['ODIND_FG'],
               'product_code'    : row['PRODGRP_ID'],
               'outstanding_bal' : detail['current_balance'],
               'outstanding_bal' : detail['current_balance'],
               'pic'             : '',
               'product_name'    : row['PROD_NM'],
               'dealer_name'     : row['WRK_BRN'],
               'brand'           : '',
               'model'           : '',
               'engine_no'       : '',
               'chassis_no'      : '',
               'color'           : '',
               'license_plates'  : '',
               'production_time' : '',
               'createdBy'       : 'system',
               'createdAt'       : time.time()
            }

            FMT      = '%d-%m-%y'
            d1       = now.strftime(FMT)
            date_time = datetime.fromtimestamp(detail['due_date'])
            d2       = date_time.strftime(FMT)
            tdelta   = datetime.strptime(d1, FMT) - datetime.strptime(d2, FMT)
            contract_date  = row['CIF_CR8']
            temp['day']    = contract_date[0:2]
            temp['month']  = contract_date[2:4]
            temp['year']   = contract_date[4:8]
            
            if tdelta.days == 35:
               diallistInfo = mongodb.getOne(MONGO_COLLECTION=diallist_detail_collection, WHERE={'account_number': str(row['account_number'])},SELECT=['assign'])
               if diallistInfo != None:
                  temp['pic']        = diallistInfo['assign']

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
   aggregate_sbv = [
      {
           "$lookup":
           {
               "from": account_collection,
               "localField": "contract_no",
               "foreignField": "account_number",
               "as": "detailAcc"
           }
      },{
          "$match":
          {
              "detailAcc.account_number": {'$exists' : 'true'},
          }
      }
   ]
   data_sbv = mongodb.aggregate_pipeline(MONGO_COLLECTION=sbv_collection,aggregate_pipeline=aggregate_sbv)

   if data_sbv != None:
      for row in data_sbv:
         for detail in row['detailAcc']:
            temp = {
               'index'           : i,
               'account_number'  : row['contract_no'],
               'name'            : row['name'],
               'address'         : row['address'],
               'contract_date'   : row['open_card_date'],
               'approved_amt'    : row['approved_limit'],
               'cur_bal'         : detail['cur_bal'],
               'overdue_amt'     : detail['overdue_amt'],
               'phone'           : detail['phone'],
               'due_date'        : detail['overdue_date'],
               'overdue_date'    : row['overdue_days_no'],
               'group'           : row['delinquency_group'],
               'product_code'    : row['card_type'],
               'outstanding_bal' : detail['cur_bal'],
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
               'createdAt'       : time.time()
            }
            if row['card_type'] == '301':
               temp['product_name']  = 'Credit Card'
            else:
               temp['product_name']  = 'Cash Card'

            FMT      = '%d-%m-%y'
            d1       = now.strftime(FMT)
            date_time = datetime.fromtimestamp(detail['overdue_date'])
            d2       = date_time.strftime(FMT)
            tdelta   = datetime.strptime(d1, FMT) - datetime.strptime(d2, FMT)
            contract_date  = row['open_card_date']
            temp['day']    = contract_date[0:2]
            temp['month']  = contract_date[2:4]
            temp['year']   = contract_date[4:8]
            
            if tdelta.days == 35:
               diallistInfo = mongodb.getOne(MONGO_COLLECTION=diallist_detail_collection, WHERE={'account_number': str(row['contract_no'])},SELECT=['assign'])
               if diallistInfo != None:
                  temp['pic']        = diallistInfo['assign']
               releaseInfo = mongodb.getOne(MONGO_COLLECTION=report_release_sale_collection, WHERE={'account_number': str(row['contract_no'])},SELECT=['cus_name','temp_address','address'])
               if releaseInfo != None:
                  temp['name']        = releaseInfo['cus_name']
                  if releaseInfo['temp_address'] != '':
                     temp['address'] = releaseInfo['temp_address']
                  else:
                     temp['address'] = releaseInfo['address']

               zaccfInfo = mongodb.getOne(MONGO_COLLECTION=zaccf_collection, WHERE={'LIC_NO': str(row['license_no'])},SELECT=['WRK_BRN'])
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
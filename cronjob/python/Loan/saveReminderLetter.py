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
import pandas as pd
import xlsxwriter

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
product_collection            = common.getSubUser(subUserType, 'Product')
report_due_date_collection      = common.getSubUser(subUserType, 'Report_due_date')
stored_collection           = common.getSubUser(subUserType, 'SBV_Stored')

log         = open(base_url + "cronjob/python/Loan/log/Reminder_letter_log.txt","a")
log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': Start Import' + '\n')

try:
   data        = []
   insertData  = []
   now         = datetime.now()

   today = date.today()
   # today = datetime.strptime('28/03/2020', "%d/%m/%Y").date()

   day = today.day
   month = today.month
   year = today.year
   weekday = today.weekday()
   lastDayOfMonth = calendar.monthrange(year, month)[1]

   todayString = today.strftime("%d/%m/%Y")
   todayTimeStamp = int(time.mktime(time.strptime(str(todayString + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
   endTodayTimeStamp = int(time.mktime(time.strptime(str(todayString + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))

   startMonth = int(time.mktime(time.strptime(str('01/' + str(month) + '/' + str(year) + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
   endMonth = int(time.mktime(time.strptime(str(str(lastDayOfMonth) + '/' + str(month) + '/' + str(year) + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))

   mongodb.remove_document(MONGO_COLLECTION=collection, WHERE={'createdAt': {'$gte': todayTimeStamp, '$lte': endTodayTimeStamp} })

   i = 1

   last_five_day = todayTimeStamp - (86400*5) 
   dueDayOfMonth = mongodb.getOne(MONGO_COLLECTION=report_due_date_collection, WHERE={'for_month': str(month), 'due_date' : last_five_day})
   if dueDayOfMonth != None:
      
      due_date    = datetime.fromtimestamp(dueDayOfMonth['due_date'])
      d2          = due_date.strftime('%d/%m/%Y')
      due_date    = datetime.strptime(d2, "%d/%m/%Y").date()
      day         = due_date.day
      if day >= 12 and day <= 15:
         dept_group = '01'
      if day >= 22 and day <= 25:
         dept_group = '02'
      if (day >= 28 and day <= 31) or (day >= 1 and day <= 5):
         dept_group = '03'

      aggregate_pipeline = [
         { 
            "$project": 
            { 
               'account_number': 1, 
               'current_balance': 1, 
               'overdue_amount_this_month': 1, 
               'mobile_num': 1, 
               'due_date': 1, 
               'group_id': 1
               # 'dateDifference' :{"$divide" : [{ "$subtract" : [todayTimeStamp,'$due_date']}, 86400]}  
            }
         },{ 
            "$match" : 
            {
               'group_id': {'$regex': dept_group, '$nin': [ 'A'+dept_group ]}
               # '$or' : [ {'dateDifference': {"$eq": 35} }, {'dateDifference': {"$eq": 65} }, {'dateDifference': {"$eq": 95} }, {'dateDifference': {"$eq": 185} }]
            } 
         }

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
                  'address'         : zaccf['ADDR_1']+ ', '+zaccf['ADDR_2']+', '+zaccf['ADDR_3'],
                  'contract_date'   : zaccf['CIF_CR8'],
                  'approved_amt'    : int(float(zaccf['APPROV_LMT'])),
                  'cur_bal'         : row['current_balance'],
                  'overdue_amt'     : row['overdue_amount_this_month'],
                  'phone'           : row['mobile_num'],
                  'due_date'        : row['due_date'],
                  'overdue_date'    : zaccf['OVER_DY'],
                  'group'           : row['group_id'],
                  'product_code'    : zaccf['PRODGRP_ID'],
                  'outstanding_bal' : row['current_balance'],
                  'pic'             : '',
                  'product_name'    : zaccf['PROD_NM'],
                  'dealer_name'     : zaccf['WRK_BRN'],
                  'license_no'      : zaccf['LIC_NO'],
                  'cif_birth_date'  : '',
                  'license_date'    : '',
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
               if int(zaccf['cif_birth_date']) > 0 :
                  if len(str(zaccf['cif_birth_date'])) == 7:
                     zaccf['cif_birth_date']       = '0'+str(zaccf['cif_birth_date'])
                  cif_birth_date                = str(zaccf['cif_birth_date'])
                  d1                      = cif_birth_date[0:2]+'/'+cif_birth_date[2:4]+'/'+cif_birth_date[4:9]
                  temp['cif_birth_date']    = int(time.mktime(time.strptime(str(d1 + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))

               if int(zaccf['LIC_DT8']) > 0 :
                  if len(str(zaccf['LIC_DT8'])) == 7:
                     zaccf['LIC_DT8']       = '0'+str(zaccf['LIC_DT8'])
                  LIC_DT8                = str(zaccf['LIC_DT8'])
                  d1                      = LIC_DT8[0:2]+'/'+LIC_DT8[2:4]+'/'+LIC_DT8[4:8]
                  temp['license_date']    = int(time.mktime(time.strptime(str(d1 + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))

               product = mongodb.getOne(MONGO_COLLECTION=product_collection, WHERE={'code': str(zaccf['PRODGRP_ID'])},SELECT=['name'])
               if product != None:
                  temp['product_code'] = product['name']

               lnjc05Info1 = mongodb.getOne(MONGO_COLLECTION=lnjc05_collection, WHERE={'dealer_no': str(zaccf['WRK_BRN'])},
                        SELECT=['dealer_name'])
               if lnjc05Info1 != None:
                  temp['dealer_name'] = lnjc05Info1['dealer_name']

               contract_date  = zaccf['CIF_CR8']
               temp['day']    = contract_date[0:2]
               temp['month']  = contract_date[2:4]
               temp['year']   = contract_date[4:8]

               diallistInfo = mongodb.getOne(MONGO_COLLECTION=diallist_detail_collection, WHERE={'account_number': str(row['account_number']), 'createdAt': {'$gte' : todayTimeStamp,'$lte' : endTodayTimeStamp} },SELECT=['assign'])
               if diallistInfo != None:
                  if 'assign' in diallistInfo.keys():
                     temp['pic']        = diallistInfo['assign']
                     user = _mongodb.getOne(MONGO_COLLECTION=user_collection, WHERE={'extension': str(diallistInfo['assign'])},SELECT=['agentname'])
                     if user != None:
                        temp['pic']          += '-' + user['agentname']

               releaseInfo = mongodb.getOne(MONGO_COLLECTION=report_release_sale_collection, WHERE={'account_number': str(row['account_number'])},SELECT=['cus_name','temp_address','address'])
               if releaseInfo != None:
                  temp['name']        = releaseInfo['cus_name']
                  if releaseInfo['temp_address'] != '' and releaseInfo['temp_address'] != '0':
                     temp['address'] = releaseInfo['temp_address']
                  else:
                     temp['address'] = releaseInfo['address']

               if temp['address'] == '0' or temp['address'] == '':
                  temp['address']    = zaccf['ADDR_1']+ ', '+zaccf['ADDR_2']+', '+zaccf['ADDR_3']



               investigationInfo = mongodb.getOne(MONGO_COLLECTION=investigation_collection, WHERE={'contract_no': str(row['account_number'])},SELECT=['brand','model','engine_no','chassis_no','license_plates_no'])
               if investigationInfo != None:
                  temp['brand']        = investigationInfo['brand']
                  temp['model']        = investigationInfo['model']
                  temp['engine_no']    = investigationInfo['engine_no']
                  temp['chassis_no']   = investigationInfo['chassis_no']
                  temp['license_plates']   = investigationInfo['license_plates_no']

               insertData.append(temp)
               print(i)
               i += 1







      # sbv
      aggregate_stored = [
         { 
            "$match" : 
            {
               'kydue':  dept_group,
               'overdue_indicator': {'$nin': ['A']}
            } 
         },{ 
            "$group" : 
            {
               "_id": 'null',
               "acc_arr": {'$addToSet': '$contract_no'}
            } 
         }

      ]
      sbvStored = mongodb.aggregate_pipeline(MONGO_COLLECTION=stored_collection,aggregate_pipeline=aggregate_stored)
      acc_arr = []
      if sbvStored != None:
         for row in sbvStored:
            acc_arr = row['acc_arr']


      aggregate_pipeline = [
         { 
            "$match" : 
            {
               'account_number': {'$in': acc_arr},
            } 
         },{ 
            "$project": 
            { 
               'account_number': 1, 
               'overdue_amt': 1, 
               'phone': 1, 
               'overdue_date': 1, 
               'cur_bal': 1, 
               # 'dateDifference' :{"$divide" : [{ "$subtract" : [todayTimeStamp,'$overdue_date']}, 86400]}  
            } 
         },
         # { "$match" : {'$or' : [ {'dateDifference': {"$eq": 35} }, {'dateDifference': {"$eq": 65} }, {'dateDifference': {"$eq": 95} }, {'dateDifference': {"$eq": 185} }]} }

      ]
      dataAccount = mongodb.aggregate_pipeline(MONGO_COLLECTION=account_collection,aggregate_pipeline=aggregate_pipeline)
      if dataAccount != None:
         for row in dataAccount:
            group = ''
            storedInfo = mongodb.getOne(MONGO_COLLECTION=stored_collection, WHERE={'contract_no': str(row['account_number']) })
            if storedInfo != None:
               group = storedInfo['overdue_indicator'] + storedInfo['kydue']

            sbv = mongodb.getOne(MONGO_COLLECTION=sbv_collection, WHERE={'contract_no': str(row['account_number']) })
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
                  'group'           : group,
                  'product_code'    : sbv['card_type'],
                  'outstanding_bal' : row['cur_bal'],
                  'pic'             : '',
                  'product_name'    : '',
                  'dealer_name'     : '',
                  'license_no'      : sbv['license_no'],
                  'cif_birth_date'  : sbv['cif_birth_date'],
                  'license_date'    : '',
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
               if int(sbv['card_type']) < 100:
                  row['product_code'] = '301 - Credit Card'
                  row['product_name'] = 'Credit Card'
               else:
                  row['product_code'] = '302 - Cash Card'
                  row['product_name'] = 'Cash Card'
               
               contract_date  = sbv['open_card_date']
               temp['day']    = contract_date[0:2]
               temp['month']  = contract_date[2:4]
               temp['year']   = contract_date[4:8]

               if int(sbv['license_date']) > 0 :
                  if len(str(sbv['license_date'])) == 7:
                     sbv['license_date']       = '0'+str(sbv['license_date'])
                  license_date                = str(sbv['license_date'])
                  d1                      = license_date[0:2]+'/'+license_date[2:4]+'/'+license_date[4:8]
                  temp['license_date']    = int(time.mktime(time.strptime(str(d1 + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))

               diallistInfo = mongodb.getOne(MONGO_COLLECTION=diallist_detail_collection, WHERE={'account_number': str(row['account_number']), 'createdAt': {'$gte' : todayTimeStamp,'$lte' : endTodayTimeStamp} },SELECT=['assign'])
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
                  lnjc05Info1 = mongodb.getOne(MONGO_COLLECTION=lnjc05_collection, WHERE={'dealer_no': str(zaccfInfo['WRK_BRN'])},
                        SELECT=['dealer_name'])
                  if lnjc05Info1 != None:
                     temp['dealer_name'] = lnjc05Info1['dealer_name']

               insertData.append(temp)
               pprint(i)
               i += 1


   if len(insertData) > 0:
      # mongodb.remove_document(MONGO_COLLECTION=collection)
      mongodb.batch_insert(MONGO_COLLECTION=collection, insert_data=insertData)

   
   fileOutput  = base_url + 'upload/loan/export/Reminder Letter Report_'+ today.strftime("%d%m%Y") +'.xlsx'

   aggregate_acc = [
      {
          "$match":
          {
              "createdAt" : {'$gte' : todayTimeStamp,'$lte' : endTodayTimeStamp},
          }
      },
      {
         "$project":
          {
              "_id": 0,
          }
      }
   ]

   data = mongodb.aggregate_pipeline(MONGO_COLLECTION=collection,aggregate_pipeline=aggregate_acc)
   dataReport = []
   for row in data:
      temp = row
      # if 'loan_overdue_amount' in row.keys():
      #    temp['loan_overdue_amount']      = '{:,.2f}'.format(float(row['loan_overdue_amount']))

      # if 'current_balance' in row.keys():
      #    temp['current_balance']      = '{:,.2f}'.format(float(row['current_balance']))

      # if 'outstanding_principal' in row.keys():
      #    try:
      #       temp['outstanding_principal']      = '{:,.2f}'.format(float(row['outstanding_principal']))
      #    except Exception as e:
      #       temp['outstanding_principal']      = row['outstanding_principal']


      try:
         if 'due_date' in row.keys():
            date_time = datetime.fromtimestamp(int(row['due_date']))
            temp['due_date']      = date_time.strftime('%d-%m-%Y')
      except Exception as e:
         temp['due_date']      = row['due_date']
      
      try:
         if 'cif_birth_date' in row.keys():
            if row['cif_birth_date'] != None:
               date_time = datetime.fromtimestamp(row['cif_birth_date'])
               temp['cif_birth_date']      = date_time.strftime('%d-%m-%Y')
            else:
               temp['cif_birth_date']      = ''
      except Exception as e:
         temp['cif_birth_date']      = row['cif_birth_date']

      try:
         if 'license_date' in row.keys():
            if row['license_date'] != None:
               date_time = datetime.fromtimestamp(row['license_date'])
               temp['license_date']      = date_time.strftime('%d-%m-%Y')
      except Exception as e:
         temp['license_date']      = row['license_date']

      if 'createdAt' in row.keys():
         if row['createdAt'] != None:
            date_time = datetime.fromtimestamp(row['createdAt'])
            temp['createdAt']      = date_time.strftime('%d-%m-%Y')

      dataReport.append(temp)

   
   df = pd.DataFrame(dataReport, columns= ['index','account_number','name','address','contract_date','day','month','year','approved_amt','cur_bal','overdue_amt','phone','createdAt','due_date','overdue_date','group','product_code','outstanding_bal','pic','product_name','dealer_name','brand','model','engine_no','chassis_no','color','license_plates','production_time','license_no','cif_birth_date','license_date'])

   # Create a Pandas Excel writer using XlsxWriter as the engine.
   writer = pd.ExcelWriter(fileOutput, engine='xlsxwriter')

   # Convert the dataframe to an XlsxWriter Excel object.
   df.to_excel(writer,sheet_name='Sheet1',index=False,header=['No','Account number','Customer name','Customer address','Signed contract date','Day','Month','Year','Approved amount','Current balance','Overdue amount','Phone number','Sending date','Due date','Overdue date','Group','Product code','Outstanding balance','PIC','Product name','Dealer name','Brand','Kiểu xe','Số máy','Số khung','Màu xe','Biển Số','Năm sản xuất','CMND','cif birth date','license date'])

   # Get the xlsxwriter workbook and worksheet objects.
   workbook  = writer.book
   worksheet = writer.sheets['Sheet1']

   # Add some cell formats.
   
   format1 = workbook.add_format({'num_format': '#,##0', 'bottom':1, 'top':1, 'left':1, 'right':1})
   format2 = workbook.add_format({'num_format': 'dd-mm-yy', 'bottom':1, 'top':1, 'left':1, 'right':1})
   # format2 = workbook.add_format({'num_format': '0%'})
   border_fmt = workbook.add_format({'bottom':1, 'top':1, 'left':1, 'right':1})


   # Set the column width and format.
   worksheet.set_column('A:AE', 20, border_fmt)

   worksheet.set_column('I:K', 20, format1)
   worksheet.set_column('M:N', 20, format2)
   worksheet.set_column('R:R', 20, format1)
   worksheet.set_column('AD:AE', 20, format2)

   writer.save()


   now_end         = datetime.now()
   log.write(now_end.strftime("%d/%m/%Y, %H:%M:%S") + ': End Log' + '\n')
   print('DONE')
except Exception as e:
   pprint(e)
   log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
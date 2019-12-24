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

collection           = common.getSubUser(subUserType, 'Write_of_report')
lnjc05_collection    = common.getSubUser(subUserType, 'LNJC05')
account_collection   = common.getSubUser(subUserType, 'List_of_account_in_collection')
zaccf_collection     = common.getSubUser(subUserType, 'ZACCF')
customer_collection  = common.getSubUser(subUserType, 'Cus_assigned_partner')
product_collection   = common.getSubUser(subUserType, 'Product')
release_sale_collection   = common.getSubUser(subUserType, 'Report_release_sale')
investigation_collection  = common.getSubUser(subUserType, 'Investigation_file')
sbv_collection            = common.getSubUser(subUserType, 'SBV')
trialBalance_collection   = common.getSubUser(subUserType, 'Trial_balance_report')
wo_monthly_collection     = common.getSubUser(subUserType, 'WO_monthly')
diallist_collection       = common.getSubUser(subUserType, 'Diallist_detail')
user_collection           = common.getSubUser(subUserType, 'User')

log         = open(base_url + "cronjob/python/Loan/log/WriteOf_log.txt","a")
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
               "due_date": 1,
               
           }
       }
   ]
   data = mongodb.aggregate_pipeline(MONGO_COLLECTION=lnjc05_collection,aggregate_pipeline=aggregate_pipeline)
   for row in data:
      if 'account_number' in row.keys():
         get = {}
         get['acc'] = row['account_number']
      zaccf = mongodb.getOne(MONGO_COLLECTION=zaccf_collection, WHERE={'account_number': str(row['account_number'])},
         SELECT=['FRELD8','APPROV_LMT','INT_RATE','TERM_ID','W_ORG'])
      today    = datetime.now()
      FMT      = '%d-%m-%y'
      d1       = today.strftime(FMT)
      date_time = datetime.fromtimestamp(row['due_date'])
      d2       = date_time.strftime(FMT)
      tdelta   = datetime.strptime(d1, FMT) - datetime.strptime(d2, FMT)      
      if zaccf != None and tdelta.days >=361:
         temp = {}
         temp['Group'] = row['group_id']
         temp['Account_number'] = row['account_number']
         temp['Name'] = row['cus_name']
         temp['Due_date'] = row['due_date']
         temp['Release_date'] = zaccf['FRELD8']
         temp['Release_amount'] = zaccf['APPROV_LMT']
         temp['Interest_rate'] = zaccf['INT_RATE']
         temp['Loan_Term'] = zaccf['TERM_ID']
         temp['Off_balance'] = zaccf['W_ORG']
         temp['time'] = tdelta.days
         temp['createdAt'] = time.time()
         temp['createdBy'] = 'system'
         insertData.append(temp)
   # # CARD
   # aggregate_pipeline = [
   #     {
   #         "$project":
   #         {
   #             "account_number": 1,
   #             "cus_name": 1,
   #             # "current_balance": 1,
   #             "overdue_date": 1,
   #             "phone": 1,
   #         }
   #     }
   # ]
   # cardData = mongodb.aggregate_pipeline(MONGO_COLLECTION=account_collection,aggregate_pipeline=aggregate_pipeline)
   # for row in cardData:
   #    if 'account_number' in row.keys():
   #       sbv = mongodb.getOne(MONGO_COLLECTION=sbv_collection, WHERE={'contract_no': str(row['account_number'])},
   #          SELECT=['cif_birth_date','cus_no','open_date','card_type','license_no','approved_limit','address','ob_principal_sale','ob_principal_cash','interest_rate','overdue_days_no'])
   #       if sbv != None:
   #          try:
   #             birth_date   = datetime.fromtimestamp(sbv['cif_birth_date'])
   #             birth_date   = birth_date.strftime('%d/%m/%Y')
   #             row['BIR_DT8']          = birth_date
   #          except Exception as e:
   #             row['BIR_DT8']          = str(sbv['cif_birth_date'])
            
   #          row['CUS_ID']           = sbv['cus_no']
   #          row['FRELD8']           = sbv['open_date']
   #          row['LIC_NO']           = sbv['license_no']
   #          row['APPROV_LMT']       = sbv['approved_limit']
   #          row['current_add']      = sbv['address']
   #          row['W_ORG']            = float(sbv['ob_principal_sale']) + float(sbv['ob_principal_cash'])
   #          row['INT_RATE']         = sbv['interest_rate']
   #          row['OVER_DY']          = sbv['overdue_days_no']

   #          product = mongodb.getOne(MONGO_COLLECTION=product_collection, WHERE={'code': str(sbv['card_type'])},SELECT=['name'])
   #          if product != None:
   #             row['product_name'] = product['name']

   #       customer = mongodb.getOne(MONGO_COLLECTION=customer_collection, WHERE={'CONTRACTNR': str(row['account_number'])},SELECT=['DATE_HANDOVER','COMPANY','DISTRICT','PROVINCE','PERNAMENT_ADDRESS','PERNAMENT_DISTRICT','PERNAMENT_PROVINCE'])
   #       if customer != None:
   #          row['DATE_HANDOVER']    = customer['DATE_HANDOVER']
   #          row['COMPANY']          = customer['COMPANY']
   #          row['current_district']       = customer['DISTRICT']
   #          row['current_province']       = customer['PROVINCE']
   #          row['pernament_add']          = customer['PERNAMENT_ADDRESS']
   #          row['pernament_district']     = customer['PERNAMENT_DISTRICT']
   #          row['pernament_province']     = customer['PERNAMENT_PROVINCE']
   #       else:
   #          row['COMPANY']          = ''

   #       investigation = mongodb.getOne(MONGO_COLLECTION=investigation_collection, WHERE={'contract_no': str(row['account_number'])},SELECT=['license_plates_no'])
   #       if investigation != None:
   #          row['license_plates_no']    = investigation['license_plates_no']


   #       release_sale = mongodb.getOne(MONGO_COLLECTION=release_sale_collection, WHERE={'account_number': str(row['account_number'])},SELECT=['temp_address','temp_district','temp_province','address','district','province'])
   #       if release_sale != None:
   #          row['current_district']       = release_sale['temp_district']
   #          row['current_province']       = release_sale['temp_province']
   #          row['pernament_add']          = release_sale['address']
   #          row['pernament_district']     = release_sale['district']
   #          row['pernament_province']     = release_sale['province']

   #       balance = mongodb.getOne(MONGO_COLLECTION=trialBalance_collection, WHERE={'account_number': str(row['account_number'])},SELECT=['prin_cash_balance','prin_retail_balance','int_balance','fee_balance','cash_int_accrued'])
   #       if balance != None:
   #          row['current_balance']    = float(balance['prin_cash_balance']) + float(balance['prin_retail_balance'])+ float(balance['int_balance'])+ float(balance['fee_balance'])+ float(balance['cash_int_accrued'])

   #       zaccf = mongodb.getOne(MONGO_COLLECTION=zaccf_collection, WHERE={'account_number': str(row['account_number'])},
   #          SELECT=['WRK_REF','WRK_REF1','WRK_REF2','WRK_REF3','WRK_REF4','WRK_REF5'])
   #       if zaccf != None:
   #          row['WRK_REF']          = zaccf['WRK_REF']+'; '+zaccf['WRK_REF1']+'; '+zaccf['WRK_REF2']+'; '+zaccf['WRK_REF3']+'; '+zaccf['WRK_REF4']+'; '+zaccf['WRK_REF5']

   #       diallist = mongodb.getOne(MONGO_COLLECTION=diallist_collection, WHERE={'account_number': str(row['account_number'])},
   #          SELECT=['assign'])
   #       if diallist != None:
   #          if row['COMPANY'] == '':
   #             if 'assign' in diallist.keys():
   #                for user in list(users):
   #                   if user['extension'] == diallist['assign']:
   #                      row['COMPANY']          = user['agentname']
   #                      break

   #       today    = datetime.now()
   #       FMT      = '%d/%m/%Y'
   #       d1       = today.strftime(FMT)
   #       # d2       = row['overdue']
   #       date_time   = datetime.fromtimestamp(row['overdue_date'])
   #       d2          = date_time.strftime(FMT)
   #       tdelta   = datetime.strptime(d1, FMT) - datetime.strptime(d2, FMT)
   #       row['CURRENT_DPD'] = tdelta.days

   #       row['MOBILE_NO']      = row['phone']
   #       row.pop('_id')
   #       row.pop('overdue_date')
   #       row.pop('phone')
   #       row['createdAt'] = time.time()
   #       row['createdBy'] = 'system'
   #       insertData.append(row)
   #    # break


   


   if len(insertData) > 0:
      # mongodb.remove_document(MONGO_COLLECTION=collection)
      mongodb.batch_insert(MONGO_COLLECTION=collection, insert_data=insertData)
   now_end         = datetime.now()
   log.write(now_end.strftime("%d/%m/%Y, %H:%M:%S") + ': End Log' + '\n')
   print('DONE')
except Exception as e:
    pprint(e)
    log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
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
import pandas as pd
import xlsxwriter

common      = Common()
base_url    = common.base_url()
wff_env     = common.wff_env(base_url)
mongodb     = Mongodb(MONGODB="worldfone4xs", WFF_ENV=wff_env)
_mongodb    = Mongodb(MONGODB="_worldfone4xs", WFF_ENV=wff_env)
now         = datetime.now()
subUserType = 'LO'
collection           = common.getSubUser(subUserType, 'Master_data_report')
lnjc05_collection    = common.getSubUser(subUserType, 'LNJC05')
account_collection   = common.getSubUser(subUserType, 'List_of_account_in_collection')
zaccf_collection     = common.getSubUser(subUserType, 'ZACCF_report')
customer_collection  = common.getSubUser(subUserType, 'Cus_assigned_partner')
product_collection   = common.getSubUser(subUserType, 'Product')
release_sale_collection   = common.getSubUser(subUserType, 'Report_release_sale')
investigation_collection  = common.getSubUser(subUserType, 'Investigation_file')
sbv_collection            = common.getSubUser(subUserType, 'SBV')
trialBalance_collection   = common.getSubUser(subUserType, 'Trial_balance_report')
wo_monthly_collection     = common.getSubUser(subUserType, 'WO_monthly')
diallistDetail_collection = common.getSubUser(subUserType, 'Diallist_detail')
diallist_collection       = common.getSubUser(subUserType, 'Diallist')
user_collection           = common.getSubUser(subUserType, 'User')
group_collection          = common.getSubUser(subUserType, 'Group')

log         = open(base_url + "cronjob/python/Loan/log/MasterData_log.txt","a")
log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': Start Import' + '\n')
try:
   data        = []
   cardData        = []
   insertData  = []
   resultData  = []
   errorData   = []

   today = date.today()
   # today = datetime.strptime('14/01/2020', "%d/%m/%Y").date()

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

   holidayOfMonth = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_off_sys'), WHERE={'off_date': todayTimeStamp})
   if holidayOfMonth != None:
      sys.exit()

   mongodb.remove_document(MONGO_COLLECTION=collection, WHERE={'createdAt': {'$gte': todayTimeStamp, '$lte': endTodayTimeStamp} })

   # SIBS
   aggregate_pipeline = [
      {
           "$match":
           {
               "created_at" : {'$gte' : todayTimeStamp,'$lte' : endTodayTimeStamp},
           }
      },{
           "$project":
           {
               "group_id": 1,
               "account_number": 1,
               "cus_name": 1,
               "current_balance": 1,
               "due_date": 1,
               "address": 1,
               "officer_id": 1,
               "officer_name": 1,
           }
      }
   ]
   data = mongodb.aggregate_pipeline(MONGO_COLLECTION=lnjc05_collection,aggregate_pipeline=aggregate_pipeline)
   for row in data:
      if 'account_number' in row.keys():
         row['APPROV_LMT']       = 0
         row['RPY_PRD']       = 0
         row['W_ORG']       = 0
         zaccf = mongodb.getOne(MONGO_COLLECTION=zaccf_collection, WHERE={'account_number': str(row['account_number']) },
            SELECT=['cif_birth_date','CUS_ID','FRELD8','PRODGRP_ID','LIC_NO','APPROV_LMT','TERM_ID','RPY_PRD','F_PDT','DT_MAT','MOBILE_NO','WRK_REF','WRK_REF1','WRK_REF2','WRK_REF3','WRK_REF4','WRK_REF5','W_ORG','INT_RATE','OVER_DY'])
         if zaccf != None:
            row['BIR_DT8']          = str(zaccf['cif_birth_date'])
            row['CUS_ID']           = zaccf['CUS_ID']
            row['FRELD8']           = zaccf['FRELD8']
            row['LIC_NO']           = zaccf['LIC_NO']
            row['APPROV_LMT']       = float(zaccf['APPROV_LMT'])
            row['TERM_ID']          = zaccf['TERM_ID']
            row['RPY_PRD']          = float(zaccf['RPY_PRD'])
            row['F_PDT']            = str(zaccf['F_PDT'])
            row['DT_MAT']           = str(zaccf['DT_MAT'])
            row['MOBILE_NO']        = zaccf['MOBILE_NO']
            row['WRK_REF']          = zaccf['WRK_REF']+'; '+zaccf['WRK_REF1']+'; '+zaccf['WRK_REF2']+'; '+zaccf['WRK_REF3']+'; '+zaccf['WRK_REF4']+'; '+zaccf['WRK_REF5']
            row['W_ORG']            = float(zaccf['W_ORG'])
            int_rate                = round(float(zaccf['INT_RATE']) * 100, 2) 
            row['INT_RATE']         = str(int_rate) + '%'
            # row['OVER_DY']          = zaccf['OVER_DY']

            product = mongodb.getOne(MONGO_COLLECTION=product_collection, WHERE={'code': str(zaccf['PRODGRP_ID'])},SELECT=['name'])
            if product != None:
               row['product_name'] = product['name']

         customer = mongodb.getOne(MONGO_COLLECTION=customer_collection, WHERE={'CONTRACTNR': str(row['account_number'])},SELECT=['DATE_HANDOVER','COMPANY','DISTRICT','PROVINCE','PERNAMENT_ADDRESS','PERNAMENT_DISTRICT','PERNAMENT_PROVINCE'])
         if customer != None:
            row['DATE_HANDOVER']    = customer['DATE_HANDOVER']
            row['COMPANY']          = customer['COMPANY']
            row['current_district']       = customer['DISTRICT']
            row['current_province']       = customer['PROVINCE']
            row['pernament_add']          = customer['PERNAMENT_ADDRESS']
            row['pernament_district']     = customer['PERNAMENT_DISTRICT']
            row['pernament_province']     = customer['PERNAMENT_PROVINCE']
         else:
            row['COMPANY']          = ''

         investigation = mongodb.getOne(MONGO_COLLECTION=investigation_collection, WHERE={'contract_no': str(row['account_number'])},SELECT=['license_plates_no'])
         if investigation != None:
            row['license_plates_no']    = investigation['license_plates_no']

         release_sale = mongodb.getOne(MONGO_COLLECTION=release_sale_collection, WHERE={'account_number': str(row['account_number'])},SELECT=['temp_address','temp_district','temp_province','address','district','province'])
         if release_sale != None:
            row['current_add']            = release_sale['temp_address']
            row['current_district']       = release_sale['temp_district']
            row['current_province']       = release_sale['temp_province']
            row['pernament_add']          = release_sale['address']
            row['pernament_district']     = release_sale['district']
            row['pernament_province']     = release_sale['province']
         else:
            row['current_add']    = row['address']

         if row['current_add'] == '0' or row['current_add'] == '':
            row['current_add']    = row['address']

         # today    = datetime.now()
         if row['due_date'] != "":
            FMT      = '%d-%m-%y'
            d1       = today.strftime(FMT)
            date_time = datetime.fromtimestamp(row['due_date'])
            d2       = date_time.strftime(FMT)
            tdelta   = datetime.strptime(d1, FMT) - datetime.strptime(d2, FMT)

            row['CURRENT_DPD'] = int(tdelta.days)
            
            if int(tdelta.days) < 30:
               row['OVER_DY'] = '<30'
            if int(tdelta.days) >= 30 and int(tdelta.days) < 60:
               row['OVER_DY'] = '30+'
            if int(tdelta.days) >= 60 and int(tdelta.days) < 90:
               row['OVER_DY'] = '60+'
            if int(tdelta.days) >= 90 and int(tdelta.days) < 180:
               row['OVER_DY'] = '90+'
            if int(tdelta.days) >= 180 and int(tdelta.days) < 360:
               row['OVER_DY'] = '180+'
            if int(tdelta.days) >= 360:
               row['OVER_DY'] = '360+'
         else:
            row['CURRENT_DPD'] = 0
            row['OVER_DY'] = ''

         diallist = mongodb.getOne(MONGO_COLLECTION=diallistDetail_collection, WHERE={'account_number': str(row['account_number']),'createdAt': {'$gte' : todayTimeStamp,'$lte' : endTodayTimeStamp}},
            SELECT=['assign'])
         if diallist != None:
            if row['COMPANY'] == '':
               if 'assign' in diallist.keys():
                  row['COMPANY'] = str(diallist['assign'])
                  user = _mongodb.getOne(MONGO_COLLECTION=user_collection, WHERE={'extension': str(diallist['assign'])},SELECT=['agentname'])
                  if user != None:
                     row['COMPANY']          += '-' + user['agentname']
                     # print(row['COMPANY'])
               else: 
                  name = row['officer_id']
                  extension = name[6:10]
                  row['COMPANY'] = extension
                  user = _mongodb.getOne(MONGO_COLLECTION=user_collection, WHERE={'extension': str(extension)},SELECT=['agentname'])
                  if user != None:
                     row['COMPANY']          += '-' + user['agentname']
                     # print(row['COMPANY'])

         if row['COMPANY'] == '':
            name = row['officer_id']
            extension = name[6:10]
            row['COMPANY'] = extension
            user = _mongodb.getOne(MONGO_COLLECTION=user_collection, WHERE={'extension': str(extension)},SELECT=['agentname'])
            if user != None:
               row['COMPANY']          += '-' + user['agentname']
               # print(row['COMPANY'])
         
         row['current_balance'] = float(row['current_balance'])
         row.pop('_id')
         row.pop('officer_name')
         # row.pop('officer_id')
         row.pop('due_date')
         row['createdAt'] = int(todayTimeStamp)
         row['createdBy'] = 'system'
         insertData.append(row)


   # CARD
   aggregate_pipeline = [
      {
           "$match":
           {
               "created_at" : {'$gte' : todayTimeStamp,'$lte' : endTodayTimeStamp},
           }
      },{
           "$project":
           {
               "account_number": 1,
               "cus_name": 1,
               "cur_bal": 1,
               "overdue_date": 1,
               "phone": 1,
           }
       }
   ]
   cardData = mongodb.aggregate_pipeline(MONGO_COLLECTION=account_collection,aggregate_pipeline=aggregate_pipeline)
   for row in cardData:
      if 'account_number' in row.keys():
         row['group_id'] = ''
         row['APPROV_LMT']       = 0
         row['RPY_PRD']       = 0
         row['W_ORG']       = 0
         sbv_store = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'SBV_Stored'), WHERE={'contract_no': str(row['account_number'])},SELECT=['overdue_indicator'],SORT=[("_id", -1)], SKIP=0, TAKE=1)
         if sbv_store != None:
            for store in sbv_store:
               row['group_id']                    = store['overdue_indicator']

         sbv = mongodb.getOne(MONGO_COLLECTION=sbv_collection, WHERE={'contract_no': str(row['account_number']), "created_at" : {'$gte' : todayTimeStamp,'$lte' : endTodayTimeStamp} },
            SELECT=['cif_birth_date','cus_no','open_card_date','card_type','license_no','approved_limit','address','ob_principal_sale','ob_principal_cash','interest_rate','overdue_days_no'])
         if sbv != None:
            try:
               birth_date   = datetime.fromtimestamp(sbv['cif_birth_date'])
               birth_date   = birth_date.strftime('%d/%m/%Y')
               row['BIR_DT8']          = birth_date
            except Exception as e:
               row['BIR_DT8']          = str(sbv['cif_birth_date'])
            
            row['CUS_ID']           = sbv['cus_no']
            row['FRELD8']           = sbv['open_card_date']
            row['LIC_NO']           = sbv['license_no']
            row['APPROV_LMT']       = float(sbv['approved_limit'])
            row['current_add']      = sbv['address']
            row['W_ORG']            = float(sbv['ob_principal_sale']) + float(sbv['ob_principal_cash'])
            row['INT_RATE']         = str(round(float(sbv['interest_rate']) * 100, 2)) + '%'  
            # row['OVER_DY']          = sbv['overdue_days_no']

            if int(sbv['card_type']) < 100:
               row['product_name'] = '301 - Credit Card'
            else:
               row['product_name'] = '302 - Cash Card'

         customer = mongodb.getOne(MONGO_COLLECTION=customer_collection, WHERE={'CONTRACTNR': str(row['account_number'])},SELECT=['DATE_HANDOVER','COMPANY','DISTRICT','PROVINCE','PERNAMENT_ADDRESS','PERNAMENT_DISTRICT','PERNAMENT_PROVINCE'])
         if customer != None:
            row['DATE_HANDOVER']    = customer['DATE_HANDOVER']
            row['COMPANY']          = customer['COMPANY']
            row['current_district']       = customer['DISTRICT']
            row['current_province']       = customer['PROVINCE']
            row['pernament_add']          = customer['PERNAMENT_ADDRESS']
            row['pernament_district']     = customer['PERNAMENT_DISTRICT']
            row['pernament_province']     = customer['PERNAMENT_PROVINCE']
         else:
            row['COMPANY']          = ''

         investigation = mongodb.getOne(MONGO_COLLECTION=investigation_collection, WHERE={'contract_no': str(row['account_number'])},SELECT=['license_plates_no'])
         if investigation != None:
            row['license_plates_no']    = investigation['license_plates_no']


         release_sale = mongodb.getOne(MONGO_COLLECTION=release_sale_collection, WHERE={'account_number': str(row['account_number'])},SELECT=['temp_address','temp_district','temp_province','address','district','province'])
         if release_sale != None:
            row['current_district']       = release_sale['temp_district']
            row['current_province']       = release_sale['temp_province']
            row['pernament_add']          = release_sale['address']
            row['pernament_district']     = release_sale['district']
            row['pernament_province']     = release_sale['province']

         # balance = mongodb.getOne(MONGO_COLLECTION=trialBalance_collection, WHERE={'account_number': str(row['account_number'])},SELECT=['prin_cash_balance','prin_retail_balance','int_balance','fee_balance','cash_int_accrued'])
         # if balance != None:
         #    row['current_balance']    = float(balance['prin_cash_balance']) + float(balance['prin_retail_balance'])+ float(balance['int_balance'])+ float(balance['fee_balance'])+ float(balance['cash_int_accrued'])

         zaccf = mongodb.getOne(MONGO_COLLECTION=zaccf_collection, WHERE={'account_number': str(row['account_number']) },
            SELECT=['WRK_REF','WRK_REF1','WRK_REF2','WRK_REF3','WRK_REF4','WRK_REF5'])
         if zaccf != None:
            row['WRK_REF']          = zaccf['WRK_REF']+'; '+zaccf['WRK_REF1']+'; '+zaccf['WRK_REF2']+'; '+zaccf['WRK_REF3']+'; '+zaccf['WRK_REF4']+'; '+zaccf['WRK_REF5']

         if row['group_id'] == 'A':
            diallistDetail = mongodb.getOne(MONGO_COLLECTION=diallistDetail_collection, WHERE={'account_number': str(row['account_number']),'createdAt': {'$gte' : todayTimeStamp,'$lte' : endTodayTimeStamp}},
            SELECT=['diallist_id'])
            # if row['account_number'] == '0020020000001184':
               # print(diallistDetail)
            if diallistDetail != None:
               diallist = mongodb.getOne(MONGO_COLLECTION=diallist_collection, WHERE={'_id': diallistDetail['diallist_id']},SELECT=['group_id'])
               if diallist != None:
                  group = mongodb.getOne(MONGO_COLLECTION=group_collection, WHERE={'_id': ObjectId(diallist['group_id']) },SELECT=['lead'])
                  if group != None:
                     if row['COMPANY'] == '':
                        row['COMPANY'] = str(group['lead'])
                        user = _mongodb.getOne(MONGO_COLLECTION=user_collection, WHERE={'extension': str(group['lead'])},SELECT=['agentname'])
                        if user != None:
                           row['COMPANY']          += '-' + user['agentname']
                           # print(row['COMPANY'])

         else:
            diallist = mongodb.getOne(MONGO_COLLECTION=diallistDetail_collection, WHERE={'account_number': str(row['account_number']),'createdAt': {'$gte' : todayTimeStamp,'$lte' : endTodayTimeStamp}},
               SELECT=['assign'])
            if diallist != None:
               if 'assign' in diallist.keys():
                  if row['COMPANY'] == '':
                     row['COMPANY'] = str(diallist['assign'])
                     user = _mongodb.getOne(MONGO_COLLECTION=user_collection, WHERE={'extension': str(diallist['assign'])},SELECT=['agentname'])
                     if user != None:
                        row['COMPANY']          += '-' + user['agentname']

         # today    = datetime.now()
         if row['overdue_date'] !="":
            FMT      = '%d/%m/%Y'
            d1       = today.strftime(FMT)
            date_time   = datetime.fromtimestamp(row['overdue_date'])
            d2          = date_time.strftime(FMT)
            tdelta   = datetime.strptime(d1, FMT) - datetime.strptime(d2, FMT)
            row['CURRENT_DPD'] = tdelta.days
            
            if int(tdelta.days) < 30:
               row['OVER_DY'] = '<30'
            if int(tdelta.days) >= 30 and int(tdelta.days) < 60:
               row['OVER_DY'] = '30+'
            if int(tdelta.days) >= 60 and int(tdelta.days) < 90:
               row['OVER_DY'] = '60+'
            if int(tdelta.days) >= 90 and int(tdelta.days) < 180:
               row['OVER_DY'] = '90+'
            if int(tdelta.days) >= 180 and int(tdelta.days) < 360:
               row['OVER_DY'] = '180+'
            if int(tdelta.days) >= 360:
               row['OVER_DY'] = '360+'
         else:
            row['CURRENT_DPD'] = 0
            row['OVER_DY'] = ''
            

         overdue_date = datetime.strptime(d2, "%d/%m/%Y").date()
         day = overdue_date.day
         if day >= 12 and day <= 15:
            row['group_id'] += '01'
         if day >= 22 and day <= 25:
            row['group_id'] += '02'
         if (day >= 28 and day <= 31) or (day >= 1 and day <= 5):
            row['group_id'] += '03'

         row['MOBILE_NO']      = row['phone']
         row['current_balance'] = float(row['cur_bal'])
         row.pop('_id')
         row.pop('overdue_date')
         row.pop('phone')
         row.pop('cur_bal')
         row['createdAt'] = int(todayTimeStamp)
         row['createdBy'] = 'system'
         insertData.append(row)
      # break


   # # WO_monthly
   # aggregate_pipeline = [
   #     {
   #         "$project":
   #         {
   #             "ACCTNO": 1,
   #             "CUS_NM": 1,
   #             "WO9711": 1,
   #             "WO9712": 1,
   #             "WO9713": 1,
   #             "PROD_ID": 1,
   #             "PHONE": 1,
   #         }
   #     }
   # ]
   # woData = mongodb.aggregate_pipeline(MONGO_COLLECTION=wo_monthly_collection,aggregate_pipeline=aggregate_pipeline)
   # for row in woData:
   #    temp = {}
   #    if 'ACCTNO' in row.keys():
   #       temp['cus_name']         = row['CUS_NM']
   #       temp['MOBILE_NO']        = row['PHONE']
   #       temp['product_name']     = row['PROD_ID']
   #       temp['group_id']         = ''
   #       sbv = mongodb.getOne(MONGO_COLLECTION=sbv_collection, WHERE={'contract_no': str(row['ACCTNO'])},
   #          SELECT=['cif_birth_date','name','cus_no','open_date','card_type','license_no','approved_limit','ob_principal_sale','ob_principal_cash','interest_rate','overdue_days_no','overdue_indicator'])
   #       if sbv != None:
   #          temp['group_id']          = sbv['overdue_indicator']
   #          temp['BIR_DT8']          = str(sbv['cif_birth_date'])
   #          temp['CUS_ID']           = sbv['cus_no']
   #          temp['FRELD8']           = sbv['open_date']
   #          temp['LIC_NO']           = sbv['license_no']
   #          temp['APPROV_LMT']       = sbv['approved_limit']
   #          temp['cus_name']         = sbv['name']
   #          temp['W_ORG']            = float(sbv['ob_principal_sale']) + float(sbv['ob_principal_cash'])
   #          temp['INT_RATE']         = sbv['interest_rate']
   #          temp['OVER_DY']          = sbv['overdue_days_no']

   #          if int(sbv['card_type']) < 100:
   #             temp['product_name'] = '301 - Credit Card'
   #          else:
   #             temp['product_name'] = '302 - Cash Card'

   #       zaccf = mongodb.getOne(MONGO_COLLECTION=zaccf_collection, WHERE={'account_number': str(row['ACCTNO'])},
   #          SELECT=['cif_birth_date','name','CUS_ID','FRELD8','PRODGRP_ID','LIC_NO','APPROV_LMT','TERM_ID','RPY_PRD','F_PDT','DT_MAT','MOBILE_NO','WRK_REF','WRK_REF1','WRK_REF2','WRK_REF3','WRK_REF4','WRK_REF5','W_ORG','INT_RATE','OVER_DY'])
   #       if zaccf != None:
   #          temp['BIR_DT8']          = str(zaccf['cif_birth_date'])
   #          temp['CUS_ID']           = zaccf['CUS_ID']
   #          temp['FRELD8']           = zaccf['FRELD8']
   #          temp['LIC_NO']           = zaccf['LIC_NO']
   #          temp['APPROV_LMT']       = zaccf['APPROV_LMT']
   #          temp['cus_name']         = zaccf['name']
   #          temp['TERM_ID']          = zaccf['TERM_ID']
   #          temp['RPY_PRD']          = zaccf['RPY_PRD']
   #          temp['F_PDT']            = str(zaccf['F_PDT'])
   #          temp['DT_MAT']           = str(zaccf['DT_MAT'])
   #          temp['MOBILE_NO']        = zaccf['MOBILE_NO']
   #          temp['WRK_REF']          = zaccf['WRK_REF']+'; '+zaccf['WRK_REF1']+'; '+zaccf['WRK_REF2']+'; '+zaccf['WRK_REF3']+'; '+zaccf['WRK_REF4']+'; '+zaccf['WRK_REF5']
   #          temp['W_ORG']            = zaccf['W_ORG']
   #          temp['INT_RATE']         = zaccf['INT_RATE']
   #          temp['OVER_DY']          = zaccf['OVER_DY']

   #          product = mongodb.getOne(MONGO_COLLECTION=product_collection, WHERE={'code': str(zaccf['PRODGRP_ID'])},SELECT=['name'])
   #          if product != None:
   #             temp['product_name'] = product['name']

   #       customer = mongodb.getOne(MONGO_COLLECTION=customer_collection, WHERE={'CONTRACTNR': str(row['ACCTNO'])},SELECT=['DATE_HANDOVER','COMPANY','DISTRICT','PROVINCE','PERNAMENT_ADDRESS','PERNAMENT_DISTRICT','PERNAMENT_PROVINCE','CURRENT_ADDRESS'])
   #       if customer != None:
   #          temp['current_add']      = customer['CURRENT_ADDRESS']
   #          temp['DATE_HANDOVER']    = customer['DATE_HANDOVER']
   #          temp['COMPANY']          = customer['COMPANY']
   #          temp['current_district']       = customer['DISTRICT']
   #          temp['current_province']       = customer['PROVINCE']
   #          temp['pernament_add']          = customer['PERNAMENT_ADDRESS']
   #          temp['pernament_district']     = customer['PERNAMENT_DISTRICT']
   #          temp['pernament_province']     = customer['PERNAMENT_PROVINCE']
   #       else:
   #          temp['COMPANY']          = ''

   #       investigation = mongodb.getOne(MONGO_COLLECTION=investigation_collection, WHERE={'contract_no': str(row['ACCTNO'])},
   #          SELECT=['license_plates_no'])
   #       if investigation != None:
   #          temp['license_plates_no']    = investigation['license_plates_no']

   #       account = mongodb.getOne(MONGO_COLLECTION=account_collection, WHERE={'account_number': str(row['ACCTNO'])},
   #          SELECT=['overdue_date','phone'])
   #       if account != None:
   #          today    = datetime.now()
   #          FMT      = '%d/%m/%Y'
   #          d1       = today.strftime(FMT)
   #          # d2       = account['overdue']
   #          date_time   = datetime.fromtimestamp(account['overdue_date'])
   #          d2          = date_time.strftime(FMT)
   #          tdelta   = datetime.strptime(d1, FMT) - datetime.strptime(d2, FMT)

   #          holidayOfMonth = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_off_sys'), WHERE={'off_date': {'$gte': account['overdue_date'], '$lte': todayTimeStamp}})
   #          countHoliday = len(list(holidayOfMonth))

   #          temp['CURRENT_DPD'] = int(tdelta.days) - int(countHoliday)
   #          temp['MOBILE_NO']     = account['phone']


   #       release_sale = mongodb.getOne(MONGO_COLLECTION=release_sale_collection, WHERE={'account_number': str(row['ACCTNO'])},
   #          SELECT=['temp_address','temp_district','temp_province','address','district','province'])
   #       if release_sale != None:
   #          temp['current_add']            = release_sale['temp_address']
   #          temp['current_district']       = release_sale['temp_district']
   #          temp['current_province']       = release_sale['temp_province']
   #          temp['pernament_add']          = release_sale['address']
   #          temp['pernament_district']     = release_sale['district']
   #          temp['pernament_province']     = release_sale['province']


   #       lnjc05 = mongodb.getOne(MONGO_COLLECTION=lnjc05_collection, WHERE={'account_number': str(row['ACCTNO'])},
   #          SELECT=['due_date','group_id','officer_id'])
   #       officer_id                 = ''
   #       if lnjc05 != None:
   #          officer_id                 = lnjc05['officer_id']
   #          temp['group_id']           = lnjc05['group_id']
   #          today    = datetime.now()
   #          date_time = datetime.fromtimestamp(lnjc05['due_date'])
   #          FMT      = '%d-%m-%y'
   #          d1       = today.strftime(FMT)
   #          d2       = date_time.strftime(FMT)
   #          tdelta   = datetime.strptime(d1, FMT) - datetime.strptime(d2, FMT)
   #          temp['CURRENT_DPD'] = tdelta.days

   #       if temp['group_id'] == 'A':
   #          diallistDetail = mongodb.getOne(MONGO_COLLECTION=diallistDetail_collection, WHERE={'account_number': str(row['ACCTNO']),'createdAt': {'$gte' : todayTimeStamp,'$lte' : endTodayTimeStamp}},
   #          SELECT=['diallist_id'])
   #          if diallistDetail != None:
   #             diallist = mongodb.getOne(MONGO_COLLECTION=diallist_collection, WHERE={'_id': diallistDetail['diallist_id']},SELECT=['group_id'])
   #             if diallist != None:
   #                group = mongodb.getOne(MONGO_COLLECTION=group_collection, WHERE={'_id': ObjectId(diallist['group_id']) },SELECT=['lead'])
   #                if group != None:
   #                   # print(group['lead'])
   #                   temp['COMPANY'] = str(group['lead'])
   #                   user = _mongodb.getOne(MONGO_COLLECTION=user_collection, WHERE={'extension': str(group['lead'])},SELECT=['agentname'])
   #                   if user != None:
   #                      temp['COMPANY']          += '-' + user['agentname']

   #       else:
   #          diallist = mongodb.getOne(MONGO_COLLECTION=diallistDetail_collection, WHERE={'account_number': str(row['ACCTNO']),'createdAt': {'$gte' : todayTimeStamp,'$lte' : endTodayTimeStamp}},
   #             SELECT=['assign'])
   #          if diallist != None:
   #             if temp['COMPANY'] == '':
   #                if 'assign' in diallist.keys():
   #                   temp['COMPANY'] = str(diallist['assign'])
   #                   user = _mongodb.getOne(MONGO_COLLECTION=user_collection, WHERE={'extension': str(diallist['assign'])},SELECT=['agentname'])
   #                   if user != None:
   #                      temp['COMPANY']          += '-' + user['agentname']
   #                else: 
   #                   if officer_id != '':
   #                      name = officer_id
   #                      extension = name[6:10]
   #                      temp['COMPANY'] = extention
   #                      user = _mongodb.getOne(MONGO_COLLECTION=user_collection, WHERE={'extension': str(extension)},SELECT=['agentname'])
   #                      if user != None:
   #                         temp['COMPANY']          += '-' + user['agentname']
   #       if temp['COMPANY'] == '':
   #          if officer_id != '':
   #             name = officer_id
   #             extension = name[6:10]
   #             temp['COMPANY'] = extention
   #             user = _mongodb.getOne(MONGO_COLLECTION=user_collection, WHERE={'extension': str(extension)},SELECT=['agentname'])
   #             if user != None:
   #                temp['COMPANY']          += '-' + user['agentname']

   #       temp['current_balance']    = float(row['WO9711']) + float(row['WO9712'])+ float(row['WO9713'])

   #       temp['account_number'] = row['ACCTNO']
   #       temp['createdAt'] = time.time()
   #       temp['createdBy'] = 'system'
   #       insertData.append(temp)



   if len(insertData) > 0:
      # mongodb.remove_document(MONGO_COLLECTION=collection)
      mongodb.batch_insert(MONGO_COLLECTION=collection, insert_data=insertData)

   
   # export file
   fileOutput  = base_url + 'upload/loan/export/MasterData_'+ today.strftime("%d%m%Y") +'.xlsx' 

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
      try:
         if 'createdAt' in row.keys():
            date_time = datetime.fromtimestamp(int(row['createdAt']))
            temp['createdAt']      = date_time.strftime('%d-%m-%Y')
      except Exception as e:
         temp['createdAt']      = row['createdAt']

      dataReport.append(temp)

   df = pd.DataFrame(dataReport, columns= ['createdAt','group_id','account_number','cus_name','BIR_DT8','CUS_ID','FRELD8','product_name','LIC_NO','APPROV_LMT','TERM_ID','RPY_PRD','F_PDT','DT_MAT','current_balance','CURRENT_DPD','MOBILE_NO','WRK_REF','current_add','current_district','current_province','pernament_add','pernament_district','pernament_province','W_ORG','INT_RATE','OVER_DY','DATE_HANDOVER','license_plates_no','COMPANY'])

   writer = pd.ExcelWriter(fileOutput, engine='xlsxwriter')

   df.to_excel(writer,sheet_name='Sheet1',header=['Date Export','GROUP','CONTRACTNR','CLIENT_NAME','BIRTH_DATE','CIF','SIGNED_DATE','PRODUCTNAME','ID NO','CREDIT AMOUNT','INSTALLMENT NUMBER','INSTALMENT AMOUNT','DATE_FIRST_DUE','DATE_LAST_DUE','CURRENT_DEBT','CURRENT_DPD','PHONE NUMBER','REFERENCE PHONE','Current_ADDRESS (if any)','District','PROVINCE','PERNAMENT_ADDRESS','District','PROVINCE','PRINCIPAL','INTEREST/ year','DPD','DATE HANDOVER','lICENSE PLATES NO','COMPANY']) 

   workbook  = writer.book
   worksheet = writer.sheets['Sheet1']

   # Add some cell formats.
   format1 = workbook.add_format({'num_format': '#,##0', 'bottom':1, 'top':1, 'left':1, 'right':1})
   # format2 = workbook.add_format({'num_format': '0%'})
   border_fmt = workbook.add_format({'bottom':1, 'top':1, 'left':1, 'right':1})

   # Set the column width and format.
   worksheet.set_column('A:AD', 20, border_fmt)

   worksheet.set_column('J:J', 20, format1)
   worksheet.set_column('L:L', 20, format1)
   worksheet.set_column('O:O', 20, format1)
   worksheet.set_column('Y:Y', 20, format1)
   # Close the Pandas Excel writer and output the Excel file.
   writer.save()


   now_end         = datetime.now()
   log.write(now_end.strftime("%d/%m/%Y, %H:%M:%S") + ': End Log' + '\n')
   print('DONE')
except Exception as e:
    print(traceback.format_exc())
    log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
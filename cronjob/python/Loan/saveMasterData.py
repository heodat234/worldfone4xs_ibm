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
collection           = common.getSubUser(subUserType, 'Master_data_report')
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

log         = open(base_url + "cronjob/python/Loan/log/MasterData_log.txt","a")
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

   if todayTimeStamp in listHoliday or (weekday == 5) or weekday == 6:
      sys.exit()

   users = _mongodb.get(MONGO_COLLECTION=user_collection, SELECT=['extension','agentname'],SORT=([('_id', -1)]),SKIP=0, TAKE=200)


   # SIBS
   count = mongodb.count(MONGO_COLLECTION=lnjc05_collection)
   quotient = int(count)/10000
   mod = int(count)%10000
   if quotient != 0:
      for x in range(int(quotient)):
         result = mongodb.get(MONGO_COLLECTION=lnjc05_collection, SELECT=['account_number','cus_name','current_balance','due_date','address','officer_name'],SORT=([('_id', -1)]),SKIP=int(x*10000), TAKE=int(10000))
         for idx,row in enumerate(result):
            data.append(row)

   if int(mod) > 0:
      result = mongodb.get(MONGO_COLLECTION=lnjc05_collection,SELECT=['account_number','cus_name','current_balance','due_date','address','officer_name'], SORT=([('_id', -1)]),SKIP=int(int(quotient)*10000), TAKE=int(mod))
      for idx,row in enumerate(result):
         data.append(row)

   for row in data:
      if 'account_number' in row.keys():
         zaccf = mongodb.getOne(MONGO_COLLECTION=zaccf_collection, WHERE={'account_number': str(row['account_number'])},
            SELECT=['cif_birth_date','CUS_ID','FRELD8','PRODGRP_ID','LIC_NO','APPROV_LMT','TERM_ID','RPY_PRD','F_PDT','DT_MAT','MOBILE_NO','WRK_REF','WRK_REF1','WRK_REF2','WRK_REF3','WRK_REF4','WRK_REF5','W_ORG','INT_RATE','OVER_DY'])
         if zaccf != None:
            row['BIR_DT8']          = str(zaccf['cif_birth_date'])
            row['CUS_ID']           = zaccf['CUS_ID']
            row['FRELD8']           = zaccf['FRELD8']
            row['LIC_NO']           = zaccf['LIC_NO']
            row['APPROV_LMT']       = zaccf['APPROV_LMT']
            row['TERM_ID']          = zaccf['TERM_ID']
            row['RPY_PRD']          = zaccf['RPY_PRD']
            row['F_PDT']            = str(zaccf['F_PDT'])
            row['DT_MAT']           = str(zaccf['DT_MAT'])
            row['MOBILE_NO']        = zaccf['MOBILE_NO']
            row['WRK_REF']          = zaccf['WRK_REF']+'; '+zaccf['WRK_REF1']+'; '+zaccf['WRK_REF2']+'; '+zaccf['WRK_REF3']+'; '+zaccf['WRK_REF4']+'; '+zaccf['WRK_REF5']
            row['W_ORG']            = zaccf['W_ORG']
            row['INT_RATE']         = zaccf['INT_RATE']
            row['OVER_DY']          = zaccf['OVER_DY']

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

         today    = datetime.now()
         FMT      = '%d-%m-%y'
         d1       = today.strftime(FMT)
         date_time = datetime.fromtimestamp(row['due_date'])
         d2       = date_time.strftime(FMT)
         tdelta   = datetime.strptime(d1, FMT) - datetime.strptime(d2, FMT)
         row['CURRENT_DPD'] = tdelta.days

         diallist = mongodb.getOne(MONGO_COLLECTION=diallist_collection, WHERE={'account_number': str(row['account_number'])},
            SELECT=['assign'])
         if diallist != None:
            if row['COMPANY'] == '':
               if 'assign' in diallist.keys():
                  for user in list(users):
                     if user['extension'] == diallist['assign']:
                        row['COMPANY']          = user['agentname']
                        break

         row.pop('_id')
         row.pop('officer_name')
         row.pop('due_date')
         row['createdAt'] = time.time()
         row['createdBy'] = 'system'
         insertData.append(row)


   # CARD
   count = mongodb.count(MONGO_COLLECTION=account_collection)
   quotient = int(count)/10000
   mod = int(count)%10000
   if quotient != 0:
      for x in range(int(quotient)):
         result = mongodb.get(MONGO_COLLECTION=account_collection, SELECT=['account_number','cus_name','overdue_date','phone'],SORT=([('_id', -1)]),SKIP=int(x*10000), TAKE=int(10000))
         for idx,row in enumerate(result):
            cardData.append(row)

   if int(mod) > 0:
      result = mongodb.get(MONGO_COLLECTION=account_collection,SELECT=['account_number','cus_name','overdue_date','phone'], SORT=([('_id', -1)]),SKIP=int(int(quotient)*10000), TAKE=int(mod))
      for idx,row in enumerate(result):
         cardData.append(row)

   for row in cardData:
      if 'account_number' in row.keys():
         sbv = mongodb.getOne(MONGO_COLLECTION=sbv_collection, WHERE={'contract_no': str(row['account_number'])},
            SELECT=['cif_birth_date','cus_no','open_date','card_type','license_no','approved_limit','address','ob_principal_sale','ob_principal_cash','interest_rate','overdue_days_no'])
         if sbv != None:
            try:
               birth_date   = datetime.fromtimestamp(sbv['cif_birth_date'])
               birth_date   = birth_date.strftime('%d/%m/%Y')
               row['BIR_DT8']          = birth_date
            except Exception as e:
               row['BIR_DT8']          = str(sbv['cif_birth_date'])
            
            row['CUS_ID']           = sbv['cus_no']
            row['FRELD8']           = sbv['open_date']
            row['LIC_NO']           = sbv['license_no']
            row['APPROV_LMT']       = sbv['approved_limit']
            row['current_add']      = sbv['address']
            row['W_ORG']            = float(sbv['ob_principal_sale']) + float(sbv['ob_principal_cash'])
            row['INT_RATE']         = sbv['interest_rate']
            row['OVER_DY']          = sbv['overdue_days_no']

            product = mongodb.getOne(MONGO_COLLECTION=product_collection, WHERE={'code': str(sbv['card_type'])},SELECT=['name'])
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
            row['current_district']       = release_sale['temp_district']
            row['current_province']       = release_sale['temp_province']
            row['pernament_add']          = release_sale['address']
            row['pernament_district']     = release_sale['district']
            row['pernament_province']     = release_sale['province']

         balance = mongodb.getOne(MONGO_COLLECTION=trialBalance_collection, WHERE={'account_number': str(row['account_number'])},SELECT=['prin_cash_balance','prin_retail_balance','int_balance','fee_balance','cash_int_accrued'])
         if balance != None:
            row['current_balance']    = float(balance['prin_cash_balance']) + float(balance['prin_retail_balance'])+ float(balance['int_balance'])+ float(balance['fee_balance'])+ float(balance['cash_int_accrued'])

         zaccf = mongodb.getOne(MONGO_COLLECTION=zaccf_collection, WHERE={'account_number': str(row['account_number'])},
            SELECT=['WRK_REF','WRK_REF1','WRK_REF2','WRK_REF3','WRK_REF4','WRK_REF5'])
         if zaccf != None:
            row['WRK_REF']          = zaccf['WRK_REF']+'; '+zaccf['WRK_REF1']+'; '+zaccf['WRK_REF2']+'; '+zaccf['WRK_REF3']+'; '+zaccf['WRK_REF4']+'; '+zaccf['WRK_REF5']

         diallist = mongodb.getOne(MONGO_COLLECTION=diallist_collection, WHERE={'account_number': str(row['account_number'])},
            SELECT=['assign'])
         if diallist != None:
            if row['COMPANY'] == '':
               if 'assign' in diallist.keys():
                  for user in list(users):
                     if user['extension'] == diallist['assign']:
                        row['COMPANY']          = user['agentname']
                        break

         today    = datetime.now()
         FMT      = '%d/%m/%Y'
         d1       = today.strftime(FMT)
         # d2       = row['overdue']
         date_time   = datetime.fromtimestamp(row['overdue_date'])
         d2          = date_time.strftime(FMT)
         tdelta   = datetime.strptime(d1, FMT) - datetime.strptime(d2, FMT)
         row['CURRENT_DPD'] = tdelta.days

         row['MOBILE_NO']      = row['phone']
         row.pop('_id')
         row.pop('overdue_date')
         row.pop('phone')
         row['createdAt'] = time.time()
         row['createdBy'] = 'system'
         insertData.append(row)
      # break


   # WO_monthly
   count_wo = mongodb.count(MONGO_COLLECTION=wo_monthly_collection)
   quotient = int(count_wo)/10000
   mod = int(count_wo)%10000
   if quotient != 0:
      for x in range(int(quotient)):
         result = mongodb.get(MONGO_COLLECTION=wo_monthly_collection, SELECT=['ACCTNO','CUS_NM','PHONE','PROD_ID','WO9711','WO9712','WO9713'],SORT=([('_id', -1)]),SKIP=int(x*10000), TAKE=int(10000))
         for idx,row in enumerate(result):
            cardData.append(row)

   if int(mod) > 0:
      result = mongodb.get(MONGO_COLLECTION=wo_monthly_collection,SELECT=['ACCTNO','CUS_NM','PHONE','PROD_ID','WO9711','WO9712','WO9713'], SORT=([('_id', -1)]),SKIP=int(int(quotient)*10000), TAKE=int(mod))
      for idx,row in enumerate(result):
         cardData.append(row)

   for row in cardData:
      temp = {}
      if 'ACCTNO' in row.keys():
         temp['cus_name']         = row['CUS_NM']
         temp['MOBILE_NO']         = row['PHONE']
         temp['product_name']         = row['PROD_ID']
         sbv = mongodb.getOne(MONGO_COLLECTION=sbv_collection, WHERE={'contract_no': str(row['ACCTNO'])},
            SELECT=['cif_birth_date','name','cus_no','open_date','card_type','license_no','approved_limit','ob_principal_sale','ob_principal_cash','interest_rate','overdue_days_no'])
         if sbv != None:
            temp['BIR_DT8']          = str(sbv['cif_birth_date'])
            temp['CUS_ID']           = sbv['cus_no']
            temp['FRELD8']           = sbv['open_date']
            temp['LIC_NO']           = sbv['license_no']
            temp['APPROV_LMT']       = sbv['approved_limit']
            temp['cus_name']         = sbv['name']
            temp['W_ORG']            = float(sbv['ob_principal_sale']) + float(sbv['ob_principal_cash'])
            temp['INT_RATE']         = sbv['interest_rate']
            temp['OVER_DY']          = sbv['overdue_days_no']

            product = mongodb.getOne(MONGO_COLLECTION=product_collection, WHERE={'code': str(sbv['card_type'])},SELECT=['name'])
            if product != None:
               temp['product_name'] = product['name']

         zaccf = mongodb.getOne(MONGO_COLLECTION=zaccf_collection, WHERE={'account_number': str(row['ACCTNO'])},
            SELECT=['cif_birth_date','name','CUS_ID','FRELD8','PRODGRP_ID','LIC_NO','APPROV_LMT','TERM_ID','RPY_PRD','F_PDT','DT_MAT','MOBILE_NO','WRK_REF','WRK_REF1','WRK_REF2','WRK_REF3','WRK_REF4','WRK_REF5','W_ORG','INT_RATE','OVER_DY'])
         if zaccf != None:
            temp['BIR_DT8']          = str(zaccf['cif_birth_date'])
            temp['CUS_ID']           = zaccf['CUS_ID']
            temp['FRELD8']           = zaccf['FRELD8']
            temp['LIC_NO']           = zaccf['LIC_NO']
            temp['APPROV_LMT']       = zaccf['APPROV_LMT']
            temp['cus_name']         = zaccf['name']
            temp['TERM_ID']          = zaccf['TERM_ID']
            temp['RPY_PRD']          = zaccf['RPY_PRD']
            temp['F_PDT']            = str(zaccf['F_PDT'])
            temp['DT_MAT']           = str(zaccf['DT_MAT'])
            temp['MOBILE_NO']        = zaccf['MOBILE_NO']
            temp['WRK_REF']          = zaccf['WRK_REF']+'; '+zaccf['WRK_REF1']+'; '+zaccf['WRK_REF2']+'; '+zaccf['WRK_REF3']+'; '+zaccf['WRK_REF4']+'; '+zaccf['WRK_REF5']
            temp['W_ORG']            = zaccf['W_ORG']
            temp['INT_RATE']         = zaccf['INT_RATE']
            temp['OVER_DY']          = zaccf['OVER_DY']

            product = mongodb.getOne(MONGO_COLLECTION=product_collection, WHERE={'code': str(zaccf['PRODGRP_ID'])},SELECT=['name'])
            if product != None:
               temp['product_name'] = product['name']

         customer = mongodb.getOne(MONGO_COLLECTION=customer_collection, WHERE={'CONTRACTNR': str(row['ACCTNO'])},SELECT=['DATE_HANDOVER','COMPANY','DISTRICT','PROVINCE','PERNAMENT_ADDRESS','PERNAMENT_DISTRICT','PERNAMENT_PROVINCE','CURRENT_ADDRESS'])
         if customer != None:
            temp['current_add']      = customer['CURRENT_ADDRESS']
            temp['DATE_HANDOVER']    = customer['DATE_HANDOVER']
            temp['COMPANY']          = customer['COMPANY']
            temp['current_district']       = customer['DISTRICT']
            temp['current_province']       = customer['PROVINCE']
            temp['pernament_add']          = customer['PERNAMENT_ADDRESS']
            temp['pernament_district']     = customer['PERNAMENT_DISTRICT']
            temp['pernament_province']     = customer['PERNAMENT_PROVINCE']
         else:
            temp['COMPANY']          = ''

         investigation = mongodb.getOne(MONGO_COLLECTION=investigation_collection, WHERE={'contract_no': str(row['ACCTNO'])},
            SELECT=['license_plates_no'])
         if investigation != None:
            temp['license_plates_no']    = investigation['license_plates_no']

         account = mongodb.getOne(MONGO_COLLECTION=account_collection, WHERE={'account_number': str(row['ACCTNO'])},
            SELECT=['overdue_date','phone'])
         if account != None:
            today    = datetime.now()
            FMT      = '%d/%m/%Y'
            d1       = today.strftime(FMT)
            # d2       = account['overdue']
            date_time   = datetime.fromtimestamp(account['overdue_date'])
            d2          = date_time.strftime(FMT)
            tdelta   = datetime.strptime(d1, FMT) - datetime.strptime(d2, FMT)
            temp['CURRENT_DPD']   = tdelta.days
            temp['MOBILE_NO']     = account['phone']


         release_sale = mongodb.getOne(MONGO_COLLECTION=release_sale_collection, WHERE={'account_number': str(row['ACCTNO'])},
            SELECT=['temp_address','temp_district','temp_province','address','district','province'])
         if release_sale != None:
            temp['current_add']            = release_sale['temp_address']
            temp['current_district']       = release_sale['temp_district']
            temp['current_province']       = release_sale['temp_province']
            temp['pernament_add']          = release_sale['address']
            temp['pernament_district']     = release_sale['district']
            temp['pernament_province']     = release_sale['province']


         lnjc05 = mongodb.getOne(MONGO_COLLECTION=lnjc05_collection, WHERE={'account_number': str(row['ACCTNO'])},
            SELECT=['due_date'])
         if lnjc05 != None:
            today    = datetime.now()
            date_time = datetime.fromtimestamp(lnjc05['due_date'])
            FMT      = '%d-%m-%y'
            d1       = today.strftime(FMT)
            d2       = date_time.strftime(FMT)
            tdelta   = datetime.strptime(d1, FMT) - datetime.strptime(d2, FMT)
            temp['CURRENT_DPD'] = tdelta.days

         diallist = mongodb.getOne(MONGO_COLLECTION=diallist_collection, WHERE={'account_number': str(row['ACCTNO'])},
            SELECT=['assign'])
         if diallist != None:
            if row['COMPANY'] == '':
               if 'assign' in diallist.keys():
                  for user in list(users):
                     if user['extension'] == diallist['assign']:
                        row['COMPANY']          = user['agentname']
                        break

         temp['current_balance']    = float(row['WO9711']) + float(row['WO9712'])+ float(row['WO9713'])

         temp['account_number'] = row['ACCTNO']
         temp['createdAt'] = time.time()
         temp['createdBy'] = 'system'
         insertData.append(temp)



   if len(insertData) > 0:
      mongodb.remove_document(MONGO_COLLECTION=collection)
      mongodb.batch_insert(MONGO_COLLECTION=collection, insert_data=insertData)
   now_end         = datetime.now()
   log.write(now_end.strftime("%d/%m/%Y, %H:%M:%S") + ': End Log' + '\n')
   print('DONE')
except Exception as e:
    pprint(e)
    log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
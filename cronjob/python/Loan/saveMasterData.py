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

mongodb     = Mongodb("worldfone4xs")
_mongodb    = Mongodb("_worldfone4xs")
common      = Common()
now         = datetime.now()
subUserType = 'LO'
collection           = common.getSubUser(subUserType, 'Master_data_report')
lnjc05_collection    = common.getSubUser(subUserType, 'LNJC05')
account_collection   = common.getSubUser(subUserType, 'Account')
zaccf_collection     = common.getSubUser(subUserType, 'ZACCF')
customer_collection  = common.getSubUser(subUserType, 'Cus_assigned_partner')
product_collection   = common.getSubUser(subUserType, 'Product')
release_sale_collection   = common.getSubUser(subUserType, 'Report_release_sale')
investigation_collection  = common.getSubUser(subUserType, 'Investigation_file')
sbv_collection       = common.getSubUser(subUserType, 'SBV')
trialBalance_collection       = common.getSubUser(subUserType, 'Trial_balance_report')

log         = open("/var/www/html/worldfone4xs_ibm/cronjob/python/Loan/log/MasterData_log.txt","a")

try:
   data        = []
   cardData        = []
   insertData  = []
   resultData  = []
   errorData   = []

   # SIBS
   count = mongodb.count(MONGO_COLLECTION=lnjc05_collection)
   quotient = int(count)/10000
   mod = int(count)%10000
   if quotient != 0:
      for x in range(int(quotient)):
         result = mongodb.get(MONGO_COLLECTION=lnjc05_collection, SELECT=['account_number','cus_name','current_balance','due_date','address'],SORT=([('_id', -1)]),SKIP=int(x*10000), TAKE=int(10000))
         for idx,row in enumerate(result):
            data.append(row)

   if int(mod) > 0:
      result = mongodb.get(MONGO_COLLECTION=lnjc05_collection,SELECT=['account_number','cus_name','current_balance','due_date','address'], SORT=([('_id', -1)]),SKIP=int(int(quotient)*10000), TAKE=int(mod))
      for idx,row in enumerate(result):
         data.append(row)

   for row in data:
      if 'account_number' in row.keys():
         zaccf = mongodb.getOne(MONGO_COLLECTION=zaccf_collection, WHERE={'ACC_ID': str(row['account_number'])},
            SELECT=['BIR_DT8','CUS_ID','FRELD8','PRODGRP_ID','LIC_NO','APPROV_LMT','TERM_ID','RPY_PRD','F_PDT','DT_MAT','MOBILE_NO','WRK_REF','WRK_REF1','WRK_REF2','WRK_REF3','WRK_REF4','WRK_REF5','W_ORG','INT_RATE','OVER_DY'])
         if zaccf != None:
            row['BIR_DT8']          = str(zaccf['BIR_DT8'])
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
         
         investigation = mongodb.getOne(MONGO_COLLECTION=investigation_collection, WHERE={'contract_no': str(row['account_number'])},SELECT=['license_plates_no'])
         if investigation != None:
            row['license_plates_no']    = investigation['license_plates_no']
         
         release_sale = mongodb.getOne(MONGO_COLLECTION=release_sale_collection, WHERE={'acc_no': str(row['account_number'])},SELECT=['temp_address','temp_district','temp_province','address','district','province'])
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
         due_date = datetime.fromtimestamp(row['due_date'])
         FMT      = '%d-%m-%y'
         d1       = today.strftime(FMT)
         d2       = due_date.strftime(FMT)
         tdelta   = datetime.strptime(d1, FMT) - datetime.strptime(d2, FMT)
         row['CURRENT_DPD'] = tdelta.days
         
         row.pop('_id')
         row.pop('due_date')
         insertData.append(row)


   # CARD
   count = mongodb.count(MONGO_COLLECTION=account_collection)
   quotient = int(count)/10000
   mod = int(count)%10000
   if quotient != 0:
      for x in range(int(quotient)):
         result = mongodb.get(MONGO_COLLECTION=account_collection, SELECT=['account_number','cus_name','overdue','mobile_num'],SORT=([('_id', -1)]),SKIP=int(x*10000), TAKE=int(10000))
         for idx,row in enumerate(result):
            cardData.append(row)

   if int(mod) > 0:
      result = mongodb.get(MONGO_COLLECTION=account_collection,SELECT=['account_number','cus_name','overdue','mobile_num'], SORT=([('_id', -1)]),SKIP=int(int(quotient)*10000), TAKE=int(mod))
      for idx,row in enumerate(result):
         cardData.append(row)

   for row in cardData:
      if 'account_number' in row.keys():
         sbv = mongodb.getOne(MONGO_COLLECTION=sbv_collection, WHERE={'contract_no': str(row['account_number'])},
            SELECT=['cif_birth_date','cus_no','open_date','card_type','license_no','approved_limit','address_1','ob_principal_sale','ob_principal_cash','interest_rate','overdue_days_no'])
         if sbv != None:
            row['BIR_DT8']          = str(sbv['cif_birth_date'])
            row['CUS_ID']           = sbv['cus_no']
            row['FRELD8']           = sbv['open_date']
            row['LIC_NO']           = sbv['license_no']
            row['APPROV_LMT']       = sbv['approved_limit']
            row['current_add']      = sbv['address_1']
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
         
         investigation = mongodb.getOne(MONGO_COLLECTION=investigation_collection, WHERE={'contract_no': str(row['account_number'])},SELECT=['license_plates_no'])
         if investigation != None:
            row['license_plates_no']    = investigation['license_plates_no']
         
         
         release_sale = mongodb.getOne(MONGO_COLLECTION=release_sale_collection, WHERE={'acc_no': str(row['account_number'])},SELECT=['temp_address','temp_district','temp_province','address','district','province'])
         if release_sale != None:
            row['current_district']       = release_sale['temp_district']      
            row['current_province']       = release_sale['temp_province']      
            row['pernament_add']          = release_sale['address']      
            row['pernament_district']     = release_sale['district']      
            row['pernament_province']     = release_sale['province']      
         
         balance = mongodb.getOne(MONGO_COLLECTION=trialBalance_collection, WHERE={'acc_no': str(row['account_number'])},SELECT=['prin_cash_balance','prin_retail_balance','int_balance','fee_balance','cash_int_accrued'])
         if balance != None:
            row['current_balance']    = float(balance['prin_cash_balance']) + float(balance['prin_retail_balance'])+ float(balance['int_balance'])+ float(balance['fee_balance'])+ float(balance['cash_int_accrued'])

         zaccf = mongodb.getOne(MONGO_COLLECTION=zaccf_collection, WHERE={'ACC_ID': str(row['account_number'])},
            SELECT=['WRK_REF','WRK_REF1','WRK_REF2','WRK_REF3','WRK_REF4','WRK_REF5'])
         if zaccf != None:
            row['WRK_REF']          = zaccf['WRK_REF']+'; '+zaccf['WRK_REF1']+'; '+zaccf['WRK_REF2']+'; '+zaccf['WRK_REF3']+'; '+zaccf['WRK_REF4']+'; '+zaccf['WRK_REF5']
         
         today    = datetime.now()
         FMT      = '%d/%m/%Y'
         d1       = today.strftime(FMT)
         d2       = row['overdue']
         tdelta   = datetime.strptime(d1, FMT) - datetime.strptime(d2, FMT)
         row['CURRENT_DPD'] = tdelta.days

         row['MOBILE_NO']      = row['mobile_num']
         row.pop('_id')
         row.pop('overdue')
         row.pop('mobile_num')
         insertData.append(row)
      # break

   if len(insertData) > 0:
      mongodb.remove_document(MONGO_COLLECTION=collection)
      mongodb.batch_insert(MONGO_COLLECTION=collection, insert_data=insertData)
   now_end         = datetime.now()
   log.write(now_end.strftime("%d/%m/%Y, %H:%M:%S") + ': End Log' + '\n')
   print(111)
except Exception as e:
    # pprint(e)
    log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
#!/usr/bin/python3.6
# -*- coding: utf-8 -*-
import sys
import os
import time
import ntpath
import json
import calendar
import traceback
import math
from helper.mongod import Mongodb
from datetime import datetime
from datetime import date
from pprint import pprint
from bson import ObjectId
from helper.common import Common
from helper.jaccs import Config
# help round down
def round_down(n, decimals=0):
    multiplier = 10 ** decimals
    return math.floor(n * multiplier) / multiplier
#help
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

release_sale_collection   = common.getSubUser(subUserType, 'Report_release_sale')
investigation_collection  = common.getSubUser(subUserType, 'Investigation_file')
sbv_collection            = common.getSubUser(subUserType, 'SBV')
trialBalance_collection   = common.getSubUser(subUserType, 'Trial_balance_report')
wo_monthly_collection     = common.getSubUser(subUserType, 'WO_monthly')
action_code_collection     = common.getSubUser(subUserType, 'Action_code')
diallist_collection       = common.getSubUser(subUserType, 'Diallist_detail')
site_collection           = common.getSubUser(subUserType, 'Site_result_result')
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
               "outstanding_principal": 1
               
           }
       }
   ]
   data = mongodb.aggregate_pipeline(MONGO_COLLECTION=lnjc05_collection,aggregate_pipeline=aggregate_pipeline)
   for row in data:
      if 'account_number' in row.keys():
         
         zaccf = mongodb.getOne(MONGO_COLLECTION=zaccf_collection, WHERE={'account_number': str(row['account_number'])},
            SELECT=['account_number','LIC_NO'])
         today    = datetime.now()
         FMT      = '%d-%m-%y'
         d1       = today.strftime(FMT)
         date_time = datetime.fromtimestamp(row['due_date'])
         d2       = date_time.strftime(FMT)
         tdelta   = datetime.strptime(d1, FMT) - datetime.strptime(d2, FMT)
         
         if zaccf != None and tdelta.days >=350:
            code = mongodb.getOne(MONGO_COLLECTION=action_code_collection, WHERE={'account_number': str(zaccf['account_number'])},
                  SELECT=['reason_nonpayment','action_code','raaStatus'])
            invest = mongodb.getOne(MONGO_COLLECTION=investigation_collection, WHERE={'contract_no': str(zaccf['account_number'])},
                  SELECT=['cndk_no'])
            site = mongodb.getOne(MONGO_COLLECTION=site_collection, WHERE={'contract_no': str(zaccf['account_number'])},
                  SELECT=['contract_no','staff_code'])
            countsite = mongodb.count(MONGO_COLLECTION=site_collection, WHERE={'contract_no': str(zaccf['account_number'])})

            dialist = mongodb.getOne(MONGO_COLLECTION=diallist_collection, WHERE={'account_number': str(zaccf['account_number'])},
                  SELECT=['profession'])
            zaccf11 = mongodb.get(MONGO_COLLECTION=zaccf_collection, WHERE={'LIC_NO': str(zaccf['LIC_NO'])},
            SELECT=['account_number','FRELD8','NP_DT8','APPROV_LMT','INT_RATE','TERM_ID','W_ORG','LIC_NO','F_PDT','CUS_ID','PRODGRP_ID','MOBILE_NO','WRK_BRN'])
            
            for zaccf1 in zaccf11:
              
               temp = {}
               
               if zaccf1['account_number'] == row['account_number']:
                   
                  temp['Group'] = row['group_id']
                  temp['Account_number'] = row['account_number']
                  temp['Name'] = row['cus_name']
                  temp['Due_date'] = row['due_date']  
                  temp['Release_date'] = zaccf1['FRELD8']
                  temp['Release_amount'] = zaccf1['APPROV_LMT']
                  temp['Interest_rate'] = zaccf1['INT_RATE']
                  temp['Loan_Term'] = zaccf1['TERM_ID']
                  temp['Off_balance'] = zaccf1['W_ORG']
                  num = int(row['due_date']) - int(zaccf1['F_PDT'])
                  kq = num /30
                  temp['Actual_payment'] = round_down(kq,1)
                  temp['Profession'] = dialist['profession'] if 'profession' in dialist else ''
                  temp['MRC'] = invest['cndk_no'] if invest !=None else ''
                  temp['Reason_of_uncollected'] = code['reason_nonpayment'] if code != None and 'reason_nonpayment' in code else ''
                  if code != None and code['action_code'] =="RAA":
                     cod = code['raaStatus']
                  else:
                     cod = ''
                  temp['If_bike_is_defined'] =  cod
                  temp['If_site_visit_made'] = "Yes" if countsite > 0 else 'No'
                  temp['If_there_is_fielder_in_location'] = 'Yes' if site != None and 'staff_code' in site else 'No'
                  temp['Last_date_made_field_visit'] = site['report_date'] if site != None and 'report_date' in site else ''
                  temp['No_of_site_visit_made'] = countsite
                  temp['Last_date_made_collections_call'] = ''
                  temp['If_still_collectable'] = ''
                  temp['SMS_sent'] = '1'
                  temp['Call'] = '1'
                  temp['Send_reminder_letter'] = ''
                  temp['Litigation'] = ''
                  temp['Note'] =''
                  temp['Cus_ID'] = zaccf1['CUS_ID']
                  temp['Product_code'] = zaccf1['PRODGRP_ID']
                  temp['Partner_name_company'] = ''
                  temp['Phone'] = zaccf1['MOBILE_NO']
                  temp['Outstanding_balance'] = row['outstanding_principal']
                  temp['Dealer_code'] = zaccf1['WRK_BRN']
                  temp['Dealer_name'] = ''
                  # temp['time'] = tdelta.days
                  temp['createdAt'] = time.time()
                  temp['createdBy'] = 'system'
                  insertData.append(temp)
               else:
                  temp['Group'] = row['group_id']
                  temp['Account_number'] = row['account_number']
                  temp['Name'] = row['cus_name']
                  
                  temp['Due_date'] = ''
                  temp['Release_date'] = zaccf1['FRELD8']
                  temp['Release_amount'] = zaccf1['APPROV_LMT']
                  temp['Interest_rate'] = zaccf1['INT_RATE']
                  temp['Loan_Term'] = zaccf1['TERM_ID']
                  temp['Off_balance'] = zaccf1['W_ORG']
                  num = int(zaccf1['NP_DT8']) - int(zaccf1['F_PDT'])
                  kq = num /30
                  temp['Actual_payment'] = round_down(kq,1)
                  temp['Profession'] = ''
                  temp['MRC'] = ''
                  temp['Reason_of_uncollected'] = ''
                  temp['If_bike_is_defined'] = ''
                  temp['If_site_visit_made'] = ''
                  temp['If_there_is_fielder_in_location'] = ''
                  temp['Last_date_made_field_visit'] = ''
                  temp['No_of_site_visit_made'] = ''
                  temp['Last_date_made_collections_call'] = ''
                  temp['If_still_collectable'] = ''
                  temp['SMS_sent'] = ''
                  temp['Call'] = ''
                  temp['Send_reminder_letter'] = ''
                  temp['Litigation'] = ''
                  temp['Note'] =''
                  temp['Cus_ID'] = zaccf1['CUS_ID']
                  temp['Product_code'] = zaccf1['PRODGRP_ID']
                  temp['Partner_name_company'] = ''
                  temp['Phone'] = zaccf1['MOBILE_NO']
                  temp['Outstanding_balance'] = row['outstanding_principal']
                  temp['Dealer_code'] = zaccf1['WRK_BRN']
                  temp['Dealer_name'] = ''
                  # temp['time'] = tdelta.days
                  temp['createdAt'] = time.time()
                  temp['createdBy'] = 'system'   
                  insertData.append(temp)

   # # list of account
   # aggregate_pipeline1 = [
      
   #     {
   #         "$project":
   #         {
   #          #    col field 
   #             "account_number": 1,
   #             "cus_name": 1,
   #             "due_date": 1,
   #             "overdue_date":1
   #         }
   #     }
   # ]
   # listacc = mongodb.aggregate_pipeline(MONGO_COLLECTION=account_collection,aggregate_pipeline=aggregate_pipeline1)
       
   # for row1 in listacc:
   #    if 'account_number' in row1.keys():
   #       sbv = mongodb.getOne(MONGO_COLLECTION=sbv_collection, WHERE={'contract_no': str(row1['account_number'])},
   #       SELECT=['license_no'])
   #       today    = datetime.now()
   #       FMT      = '%d-%m-%y'
   #       d1       = today.strftime(FMT)
   #       date_time = datetime.fromtimestamp(row1['overdue_date'])
   #       d2       = date_time.strftime(FMT)
   #       tdelta1   = datetime.strptime(d1, FMT) - datetime.strptime(d2, FMT)  
   #       if sbv != None and tdelta1.days >=361:
   #          sbv02 = mongodb.get(MONGO_COLLECTION=sbv_collection, WHERE={'license_no': str(sbv['license_no'])},
   #          SELECT=['delinquency_group','interest_rate','cus_no','card_type','phone','approved_limit','ob_principal_sale','ob_principal_cash','license_no','contract_no','name'])   
   #          balance = mongodb.getOne(MONGO_COLLECTION=trialBalance_collection, WHERE={'account_number': str(row1['account_number'])},
   #          SELECT=['prin_cash_balance','prin_retail_balance'])
            
            
   #          for sbv1 in sbv02:  
   #             temp1={}
   #             if sbv1['contract_no'] == row1['account_number']: 
   #                temp1['Group'] = sbv1['delinquency_group']
   #                temp1['Account_number'] = row1['account_number']
   #                temp1['Name'] = row1['cus_name']
   #                temp1['Due_date'] = row1['overdue_date']
   #                temp1['Release_date'] = ''
   #                temp1['Release_amount'] = sbv1['approved_limit']
   #                temp1['Interest_rate'] = sbv1['interest_rate']
   #                temp1['Loan_Term'] = ''
   #                temp1['Off_balance'] = sbv1['ob_principal_sale'] + sbv1['ob_principal_cash']
   #                temp1['Actual_payment'] = ''
   #                temp1['Profession'] = ''
   #                temp1['MRC'] = ''
   #                temp1['Reason_of_uncollected'] = ''
   #                temp1['If_bike_is_defined'] = ''
   #                temp1['If_site_visit_made'] = ''
   #                temp1['If_there_is_fielder_in_location'] = ''
   #                temp1['Last_date_made_field_visit'] = ''
   #                temp1['No_of_site_visit_made'] = ''
   #                temp1['Last_date_made_collections_call'] = ''
   #                temp1['If_still_collectable'] = ''
   #                temp1['SMS_sent'] = ''
   #                temp1['Call'] = ''
   #                temp1['Send_reminder_letter'] = ''
   #                temp1['Litigation'] = ''
   #                temp1['Note'] =''
   #                temp1['Cus_ID'] = sbv1['cus_no']
   #                if 1 <= int(sbv1['card_type']) <= 99:
   #                   code_type = "301_ Credit_Card"
   #                else:
   #                   code_type = "302_Cash_Card"
   #                temp1['Product_code'] = code_type
   #                temp1['Partner_name_company'] = ''
   #                temp1['Phone'] = sbv1['phone']
   #                temp1['Outstanding_balance'] = balance['prin_cash_balance'] + balance['prin_retail_balance']
   #                zaccf2 = mongodb.getOne(MONGO_COLLECTION=zaccf_collection, WHERE={'LIC_NO': str(sbv1['license_no'])},
   #                SELECT=['WRK_BRN'])
   #                if zaccf2 != None:
   #                   temp1['Dealer_code'] = zaccf2['WRK_BRN']
   #                temp1['Dealer_name'] = ''
   #                # temp['time'] = tdelta.days
   #                temp1['createdAt'] = time.time()
   #                temp1['createdBy'] = 'system'
   #                insertData.append(temp1)
   #             else:
   #                temp1['Group'] = sbv1['delinquency_group']
   #                temp1['Account_number'] = sbv1['contract_no']
   #                temp1['Name'] = sbv1['name']
   #                temp1['Due_date'] = ''
   #                temp1['Release_date'] = ''
   #                temp1['Release_amount'] = sbv1['approved_limit']
   #                temp1['Interest_rate'] = sbv1['interest_rate']
   #                temp1['Loan_Term'] = ''
   #                temp1['Off_balance'] = sbv1['ob_principal_sale'] + sbv1['ob_principal_cash']
   #                temp1['Actual_payment'] = ''
   #                temp1['Profession'] = ''
   #                temp1['MRC'] = ''
   #                temp1['Reason_of_uncollected'] = ''
   #                temp1['If_bike_is_defined'] = ''
   #                temp1['If_site_visit_made'] = ''
   #                temp1['If_there_is_fielder_in_location'] = ''
   #                temp1['Last_date_made_field_visit'] = ''
   #                temp1['No_of_site_visit_made'] = ''
   #                temp1['Last_date_made_collections_call'] = ''
   #                temp1['If_still_collectable'] = ''
   #                temp1['SMS_sent'] = ''
   #                temp1['Call'] = ''
   #                temp1['Send_reminder_letter'] = ''
   #                temp1['Litigation'] = ''
   #                temp1['Note'] =''
   #                temp1['Cus_ID'] = sbv1['cus_no']
   #                if 1 <= int(sbv1['card_type']) <= 99:
   #                   code_type = "301_ Credit_Card"
   #                else:
   #                   code_type = "302_Cash_Card"
   #                temp1['Product_code'] = code_type
   #                temp1['Partner_name_company'] = ''
   #                temp1['Phone'] = sbv1['phone']
   #                temp1['Outstanding_balance'] = balance['prin_cash_balance'] + balance['prin_retail_balance']
               
   #                temp1['Dealer_code'] = ''
   #                temp1['Dealer_name'] = ''
   #                # temp['time'] = tdelta.days
   #                temp1['createdAt'] = time.time()
   #                temp1['createdBy'] = 'system'
   #                insertData.append(temp1)

   if len(insertData) > 0:
      # mongodb.remove_document(MONGO_COLLECTION=collection)
      pprint(insertData)
      mongodb.batch_insert(MONGO_COLLECTION=collection, insert_data=insertData)
   now_end         = datetime.now()
   log.write(now_end.strftime("%d/%m/%Y, %H:%M:%S") + ': End Log' + '\n')
   print('DONE')
except Exception as e:
    pprint(e)
    traceback.print_exc()
    log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
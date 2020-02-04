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

collection           = common.getSubUser(subUserType, 'Write_of_expectation')

lnjc05_collection    = common.getSubUser(subUserType, 'LNJC05')
account_collection   = common.getSubUser(subUserType, 'List_of_account_in_collection')
zaccf_collection     = common.getSubUser(subUserType, 'ZACCF')

release_sale_collection   = common.getSubUser(subUserType, 'Report_release_sale')
investigation_collection  = common.getSubUser(subUserType, 'Investigation_file')
sbv_collection            = common.getSubUser(subUserType, 'SBV')
trialBalance_collection   = common.getSubUser(subUserType, 'Trial_balance_report')
cdr_collection            = common.getSubUser(subUserType, 'worldfonepbxmanager')
action_code_collection    = common.getSubUser(subUserType, 'Action_code')
customer_collection       = common.getSubUser(subUserType, 'Customer')
site_collection           = common.getSubUser(subUserType, 'Site_result_result')
user_collection           = common.getSubUser(subUserType, 'User')
cus_assigned_collection   = common.getSubUser(subUserType, 'Cus_assigned_partner')
trial_collection          = common.getSubUser(subUserType, 'Trial_balance_report')
product_collection        = common.getSubUser(subUserType, 'Product')

log         = open(base_url + "cronjob/python/Loan/log/WriteOf_log.txt","a")
log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': Start Import' + '\n')
try:
   data        = []
   cardData        = []
   insertData  = []
   resultData  = []
   errorData   = []
   FMT         = '%d-%m-%Y'

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

   listDay = [28,29,30,31,1,2,3,4,5]
   if day not in listDay:
      sys.exit()

   users = _mongodb.get(MONGO_COLLECTION=user_collection, SELECT=['extension','agentname'],SORT=([('_id', -1)]),SKIP=0, TAKE=200)


   # SIBS
   aggregate_pipeline = [
      { "$project": { 'account_number': 1, 'dateDifference' :{"$divide" : [{ "$subtract" : [todayTimeStamp,'$due_date']}, 84000]}  } },
      { "$match" : {'dateDifference': {"$gte": 361} } },
      # {
      #    "$lookup":
      #      {
      #        "from": zaccf_collection,
      #        "localField": 'account_number',
      #        "foreignField": 'account_number',
      #        "as": 'detail'
      #      }
      # },
      {
          "$group":
          {
              "_id": 'null',
              "account_arr": {'$push': '$account_number'},
              # "lic_no_arr": {'$push': '$detail.LIC_NO'},
          }
      }
      
   ]
   data = mongodb.aggregate_pipeline(MONGO_COLLECTION=lnjc05_collection,aggregate_pipeline=aggregate_pipeline)

   account_arr = []
   if data != None:
      for row in data:
         account_arr = row['account_arr']
         # lic_no_arr = row['lic_no_arr']

   aggregate_zaccf = [
      {
          "$match":
          {
              "account_number": {'$in' : account_arr},
          }
      },
      {
          "$group":
          {
              "_id": 'null',
              "lic_no_arr": {'$push': '$LIC_NO'},
          }
      }
   ]
   data_zaccf1 = mongodb.aggregate_pipeline(MONGO_COLLECTION=zaccf_collection,aggregate_pipeline=aggregate_zaccf)
   lic_no_arr = []
   if data_zaccf1 != None:
      for row in data_zaccf1:
         lic_no_arr = row['lic_no_arr']

   aggregate_zaccf = [
      {
          "$match":
          {
              "LIC_NO": {'$in' : lic_no_arr},
          }
      }
   ]
   data_zaccf = mongodb.aggregate_pipeline(MONGO_COLLECTION=zaccf_collection,aggregate_pipeline=aggregate_zaccf)
   if data_zaccf != None:
      for row in data_zaccf:
         if 'account_number' in row.keys():
            # print(row['account_number'])
            lnjc05Info = mongodb.getOne(MONGO_COLLECTION=lnjc05_collection, WHERE={'account_number': str(row['account_number'])},
               SELECT=['group_id','cus_name','due_date'])

            customerInfo = mongodb.getOne(MONGO_COLLECTION=customer_collection, WHERE={'account_number': str(row['account_number'])},
                     SELECT=['profession'])
            invest = mongodb.getOne(MONGO_COLLECTION=investigation_collection, WHERE={'contract_no': str(row['account_number'])},
                     SELECT=['cndk_no'])
            code = mongodb.getOne(MONGO_COLLECTION=action_code_collection, WHERE={'account_number': str(row['account_number'])},
                     SELECT=['reason_nonpayment','action_code','raaStatus'])
            
            countsite = mongodb.count(MONGO_COLLECTION=site_collection, WHERE={'contract_no': str(row['account_number'])})
            
            countsiteFielder = mongodb.count(MONGO_COLLECTION=site_collection, WHERE={'contract_no': str(row['account_number']), 'field_staff': {'$nin': ['', 'null']} })
            
            site = mongodb.get(MONGO_COLLECTION=site_collection, WHERE={'contract_no': str(row['account_number'])},
                     SELECT=['contract_no','report_date'],SORT=[("created_at", -1)], SKIP=0, TAKE=1)
            
            cdrInfo = mongodb.get(MONGO_COLLECTION=cdr_collection, WHERE={'customernumber': str(row['MOBILE_NO']), "direction" : "outbound"},
                     SELECT=['starttime'],SORT=[("starttime", -1)], SKIP=0, TAKE=1)

            companyInfo = mongodb.getOne(MONGO_COLLECTION=cus_assigned_collection, WHERE={'CONTRACTNR': str(row['account_number'])},
                     SELECT=['COMPANY'])

            temp = {}
            temp['Account_number']  = row['account_number']
            temp['Name']            = row['name']
            temp['Release_date']    = row['FRELD8']
            temp['Release_amount']  = row['APPROV_LMT']
            temp['Interest_rate']   = row['INT_RATE']
            temp['Loan_Term']       = row['TERM_ID']
            temp['Off_balance']     = row['W_ORG']


            if len(str(row['NP_DT8'])) == 7:
               row['NP_DT8'] = '0'+str(row['NP_DT8'])
            NP_DT8 = str(row['NP_DT8'])
            NP_DT8 = NP_DT8[0:2]+'-'+NP_DT8[2:4]+'-'+NP_DT8[4:8]

            if len(str(row['F_PDT'])) == 7:
               row['F_PDT'] = '0'+str(row['F_PDT'])
            F_PDT = str(row['F_PDT'])
            F_PDT = F_PDT[0:2]+'-'+F_PDT[2:4]+'-'+F_PDT[4:8]

            # d1       = NP_DT8.strftime(FMT)
            # d2       = F_PDT.strftime(FMT)
            

            if lnjc05Info != None:
               temp['Group']        = lnjc05Info['group_id']
               temp['Name']         = lnjc05Info['cus_name']
               temp['Due_date']     = lnjc05Info['due_date']  

               due_date = datetime.fromtimestamp(lnjc05Info['due_date'])
               d1       = due_date.strftime(FMT)
               # d2       = F_PDT.strftime(FMT)
               tdelta   = datetime.strptime(d1, FMT) - datetime.strptime(F_PDT, FMT)
               kq       = tdelta.days /30
               temp['Actual_payment']  = round_down(kq,1)
               
               if 'outstanding_principal' in lnjc05Info.keys():
                  temp['Outstanding_balance'] = lnjc05Info['outstanding_principal']

            else:
               tdelta   = datetime.strptime(NP_DT8, FMT) - datetime.strptime(F_PDT, FMT)
               kq       = tdelta.days /30
               temp['Actual_payment']  = round_down(kq,1)

            if customerInfo != None and 'profession' in customerInfo.keys():
               temp['Profession'] = customerInfo['profession']

            temp['MRC'] = invest['cndk_no'] if invest !=None else ''
            temp['Reason_of_uncollected'] = code['reason_nonpayment'] if code != None and 'reason_nonpayment' in code else ''
            if code != None and code['action_code'] =="RAA":
               cod = code['raaStatus']
            else:
               cod = ''
            temp['If_bike_is_defined'] =  cod
            temp['If_site_visit_made'] = "Yes" if countsite > 0 else 'No'
            temp['If_there_is_fielder_in_location'] = "Yes" if countsiteFielder > 0 else 'No'
            temp['No_of_site_visit_made'] = countsite
            if site != None:
               for st in site:
                  temp['Last_date_made_field_visit'] = st['report_date']

            if cdrInfo != None:
               for cdr in cdrInfo:
                  starttime = datetime.fromtimestamp(cdr['starttime'])
                  temp['Last_date_made_collections_call'] = starttime.strftime(FMT)
            
            temp['If_still_collectable'] = ''

            if lnjc05Info != None:
               temp['SMS_sent']              = '1' 
               temp['Call']                  = '1'
               temp['Send_reminder_letter']  = '1'
               temp['Litigation']            = '1'
            else:
               temp['SMS_sent']              = '3' 
               temp['Call']                  = '3'
               temp['Send_reminder_letter']  = '3'
               temp['Litigation']            = '3'

            if row['account_number'] in account_arr:
               today          = datetime.now()
               d1             = today.strftime(FMT)
               date_time      = datetime.fromtimestamp(lnjc05Info['due_date'])
               d2             = date_time.strftime(FMT)
               tdelta         = datetime.strptime(d1, FMT) - datetime.strptime(d2, FMT)
               temp['Note']   = tdelta.days
            else: 
               temp['Note']   = 'Follow Bike/PL'

            temp['Cus_ID']                = row['CUS_ID']
            product = mongodb.getOne(MONGO_COLLECTION=product_collection, WHERE={'code': str(row['PRODGRP_ID'])},SELECT=['name'])
            if product != None:
               temp['Product_code']          = product['name']
            if companyInfo != None:
               temp['Partner_name_company']  = companyInfo['COMPANY'] 
            else: 
               temp['Partner_name_company']  = ''

            temp['Phone']                 = row['MOBILE_NO']
            temp['Dealer_code'] = row['WRK_BRN']
            temp['Dealer_name'] = ''
            # temp['time'] = tdelta.days
            temp['createdAt'] = time.time()
            temp['createdBy'] = 'system'
            insertData.append(temp)


   
   aggregate_sbv = [
      {
          "$match":
          {
              "license_no": {'$in' : lic_no_arr},
          }
      }
   ]
   data_sbv = mongodb.aggregate_pipeline(MONGO_COLLECTION=zaccf_collection,aggregate_pipeline=aggregate_sbv)
   if data_sbv != None:
      for row in data_sbv:
         if 'account_number' in row.keys():
            # print(row['account_number'])
            accountInfo = mongodb.getOne(MONGO_COLLECTION=account_collection, WHERE={'account_number': str(row['contract_no'])},
               SELECT=['cus_name','overdue_date'])

            customerInfo = mongodb.getOne(MONGO_COLLECTION=customer_collection, WHERE={'account_number': str(row['contract_no'])},
                     SELECT=['profession'])
            invest = mongodb.getOne(MONGO_COLLECTION=investigation_collection, WHERE={'contract_no': str(row['contract_no'])},
                     SELECT=['cndk_no'])
            code = mongodb.getOne(MONGO_COLLECTION=action_code_collection, WHERE={'account_number': str(row['contract_no'])},
                     SELECT=['reason_nonpayment','action_code','raaStatus'])
            
            countsite = mongodb.count(MONGO_COLLECTION=site_collection, WHERE={'contract_no': str(row['contract_no'])})
            
            countsiteFielder = mongodb.count(MONGO_COLLECTION=site_collection, WHERE={'contract_no': str(row['contract_no']), 'field_staff': {'$nin': ['', 'null']} })
            
            site = mongodb.get(MONGO_COLLECTION=site_collection, WHERE={'contract_no': str(row['contract_no'])},
                     SELECT=['contract_no','report_date'],SORT=[("created_at", -1)], SKIP=0, TAKE=1)
            
            cdrInfo = mongodb.get(MONGO_COLLECTION=cdr_collection, WHERE={'customernumber': str(row['phone']), "direction" : "outbound"},
                     SELECT=['starttime'],SORT=[("starttime", -1)], SKIP=0, TAKE=1)

            companyInfo = mongodb.getOne(MONGO_COLLECTION=cus_assigned_collection, WHERE={'CONTRACTNR': str(row['contract_no'])},
                     SELECT=['COMPANY'])

            trialInfo = mongodb.getOne(MONGO_COLLECTION=trial_collection, WHERE={'account_number': str(row['contract_no'])},
                     SELECT=['prin_retail_balance','prin_cash_balance'])

            zaccfInfo = mongodb.getOne(MONGO_COLLECTION=zaccf_collection, WHERE={'LIC_NO': str(row['license_no'])},
                     SELECT=['WRK_BRN'])

            temp = {}
            temp['Group']           = row['delinquency_group']
            temp['Account_number']  = row['contract_no']
            temp['Name']            = row['name']
            # temp['Release_date']    = row['FRELD8']
            temp['Release_amount']  = row['approved_limit']
            temp['Interest_rate']   = row['interest_rate']
            # temp['Loan_Term']       = row['TERM_ID']
            temp['Off_balance']     = float(row['ob_principal_sale']) + float(row['ob_principal_cash'])

            if customerInfo != None and 'profession' in customerInfo.keys():
               temp['Profession'] = customerInfo['profession']

            if accountInfo != None:
               temp['Name']         = accountInfo['cus_name']
               temp['Due_date']     = accountInfo['overdue_date']  

            
            if trialInfo != None:
               temp['Outstanding_balance']     = float(trialInfo['prin_retail_balance']) + float(trialInfo['prin_cash_balance'])


            temp['MRC'] = invest['cndk_no'] if invest !=None else ''
            temp['Reason_of_uncollected'] = code['reason_nonpayment'] if code != None and 'reason_nonpayment' in code else ''
            if code != None and code['action_code'] =="RAA":
               cod = code['raaStatus']
            else:
               cod = ''
            temp['If_bike_is_defined'] =  cod
            temp['If_site_visit_made'] = "Yes" if countsite > 0 else 'No'
            temp['If_there_is_fielder_in_location'] = "Yes" if countsiteFielder > 0 else 'No'
            temp['No_of_site_visit_made'] = countsite
            if site != None:
               for st in site:
                  temp['Last_date_made_field_visit'] = st['report_date']

            if cdrInfo != None:
               for cdr in cdrInfo:
                  starttime = datetime.fromtimestamp(cdr['starttime'])
                  temp['Last_date_made_collections_call'] = starttime.strftime(FMT)
            
            temp['If_still_collectable'] = ''

            if accountInfo != None:
               temp['SMS_sent']              = '1' 
               temp['Call']                  = '1'
               temp['Send_reminder_letter']  = '1'
               temp['Litigation']            = '1'
            else:
               temp['SMS_sent']              = '3' 
               temp['Call']                  = '3'
               temp['Send_reminder_letter']  = '3'
               temp['Litigation']            = '3'

            if row['account_number'] in account_arr:
               today          = datetime.now()
               d1             = today.strftime(FMT)
               date_time      = datetime.fromtimestamp(accountInfo['overdue_date'])
               d2             = date_time.strftime(FMT)
               tdelta         = datetime.strptime(d1, FMT) - datetime.strptime(d2, FMT)
               temp['Note']   = tdelta.days
            else: 
               temp['Note']   = 'Follow Bike/PL'

            temp['Cus_ID']                = row['CUS_ID']
            if int(row['card_type']) < 100:
               temp['Product_code'] = '301 - Credit Card'
            else:
               temp['Product_code'] = '302 - Cash Card'
            
            if companyInfo != None:
               temp['Partner_name_company']  = companyInfo['COMPANY'] 
            else: 
               temp['Partner_name_company']  = ''

            temp['Phone']       = row['phone']

            if zaccfInfo != None:
               temp['Dealer_code']  = zaccfInfo['WRK_BRN'] 
            else: 
               temp['Dealer_code']  = ''

            temp['Dealer_name'] = ''
            # temp['time'] = tdelta.days
            temp['createdAt'] = time.time()
            temp['createdBy'] = 'system'
            insertData.append(temp)










   # CARD
   aggregate_pipeline = [
      { "$project": { 'account_number': 1, 'dateDifference' :{"$divide" : [{ "$subtract" : [todayTimeStamp,'$overdue_date']}, 84000]}  } },
      { "$match" : {'dateDifference': {"$gte": 361} } },
      {
          "$group":
          {
              "_id": 'null',
              "account_arr": {'$push': '$account_number'},
          }
      }
      
   ]
   data = mongodb.aggregate_pipeline(MONGO_COLLECTION=account_collection,aggregate_pipeline=aggregate_pipeline)
   account_arr = []
   if data != None:
      for row in data:
         account_arr = row['account_arr']


   aggregate_sbv = [
      {
          "$match":
          {
              "contract_no": {'$in' : account_arr},
          }
      },
      {
          "$group":
          {
              "_id": 'null',
              "lic_no_arr": {'$push': '$license_no'},
          }
      }
   ]
   data_sbv1 = mongodb.aggregate_pipeline(MONGO_COLLECTION=zaccf_collection,aggregate_pipeline=aggregate_sbv)
   lic_no_arr = []
   if data_sbv1 != None:
      for row in data_sbv1:
         lic_no_arr = row['lic_no_arr']
         # print(lic_no_arr)

   aggregate_sbv = [
      {
          "$match":
          {
              "license_no": {'$in' : lic_no_arr},
          }
      }
   ]
   data_sbv = mongodb.aggregate_pipeline(MONGO_COLLECTION=zaccf_collection,aggregate_pipeline=aggregate_sbv)
   if data_sbv != None:
      for row in data_sbv:
         if 'account_number' in row.keys():
            accountInfo = mongodb.getOne(MONGO_COLLECTION=account_collection, WHERE={'account_number': str(row['contract_no'])},
               SELECT=['cus_name','overdue_date'])

            customerInfo = mongodb.getOne(MONGO_COLLECTION=customer_collection, WHERE={'account_number': str(row['contract_no'])},
                     SELECT=['profession'])
            invest = mongodb.getOne(MONGO_COLLECTION=investigation_collection, WHERE={'contract_no': str(row['contract_no'])},
                     SELECT=['cndk_no'])
            code = mongodb.getOne(MONGO_COLLECTION=action_code_collection, WHERE={'account_number': str(row['contract_no'])},
                     SELECT=['reason_nonpayment','action_code','raaStatus'])
            
            countsite = mongodb.count(MONGO_COLLECTION=site_collection, WHERE={'contract_no': str(row['contract_no'])})
            
            countsiteFielder = mongodb.count(MONGO_COLLECTION=site_collection, WHERE={'contract_no': str(row['contract_no']), 'field_staff': {'$nin': ['', 'null']} })
            
            site = mongodb.get(MONGO_COLLECTION=site_collection, WHERE={'contract_no': str(row['contract_no'])},
                     SELECT=['contract_no','report_date'],SORT=[("created_at", -1)], SKIP=0, TAKE=1)
            
            cdrInfo = mongodb.get(MONGO_COLLECTION=cdr_collection, WHERE={'customernumber': str(row['phone']), "direction" : "outbound"},
                     SELECT=['starttime'],SORT=[("starttime", -1)], SKIP=0, TAKE=1)

            companyInfo = mongodb.getOne(MONGO_COLLECTION=cus_assigned_collection, WHERE={'CONTRACTNR': str(row['contract_no'])},
                     SELECT=['COMPANY'])

            trialInfo = mongodb.getOne(MONGO_COLLECTION=trial_collection, WHERE={'account_number': str(row['contract_no'])},
                     SELECT=['prin_retail_balance','prin_cash_balance'])

            zaccfInfo = mongodb.getOne(MONGO_COLLECTION=zaccf_collection, WHERE={'LIC_NO': str(row['license_no'])},
                     SELECT=['WRK_BRN'])

            temp = {}
            temp['Group']           = row['delinquency_group']
            temp['Account_number']  = row['contract_no']
            temp['Name']            = row['name']
            # temp['Release_date']    = row['FRELD8']
            temp['Release_amount']  = row['approved_limit']
            temp['Interest_rate']   = row['interest_rate']
            # temp['Loan_Term']       = row['TERM_ID']
            temp['Off_balance']     = float(row['ob_principal_sale']) + float(row['ob_principal_cash'])

            if customerInfo != None and 'profession' in customerInfo.keys():
               temp['Profession'] = customerInfo['profession']

            if accountInfo != None:
               temp['Name']         = accountInfo['cus_name']
               temp['Due_date']     = accountInfo['overdue_date']  

            
            if trialInfo != None:
               temp['Outstanding_balance']     = float(trialInfo['prin_retail_balance']) + float(trialInfo['prin_cash_balance'])


            temp['MRC'] = invest['cndk_no'] if invest !=None else ''
            temp['Reason_of_uncollected'] = code['reason_nonpayment'] if code != None and 'reason_nonpayment' in code else ''
            if code != None and code['action_code'] =="RAA":
               cod = code['raaStatus']
            else:
               cod = ''
            temp['If_bike_is_defined'] =  cod
            temp['If_site_visit_made'] = "Yes" if countsite > 0 else 'No'
            temp['If_there_is_fielder_in_location'] = "Yes" if countsiteFielder > 0 else 'No'
            temp['No_of_site_visit_made'] = countsite
            if site != None:
               for st in site:
                  temp['Last_date_made_field_visit'] = st['report_date']

            if cdrInfo != None:
               for cdr in cdrInfo:
                  starttime = datetime.fromtimestamp(cdr['starttime'])
                  temp['Last_date_made_collections_call'] = starttime.strftime(FMT)
            
            temp['If_still_collectable'] = ''

            if accountInfo != None:
               temp['SMS_sent']              = '1' 
               temp['Call']                  = '1'
               temp['Send_reminder_letter']  = '1'
               temp['Litigation']            = '1'
            else:
               temp['SMS_sent']              = '3' 
               temp['Call']                  = '3'
               temp['Send_reminder_letter']  = '3'
               temp['Litigation']            = '3'

            if row['account_number'] in account_arr:
               today          = datetime.now()
               d1             = today.strftime(FMT)
               date_time      = datetime.fromtimestamp(accountInfo['overdue_date'])
               d2             = date_time.strftime(FMT)
               tdelta         = datetime.strptime(d1, FMT) - datetime.strptime(d2, FMT)
               temp['Note']   = tdelta.days
            else: 
               temp['Note']   = 'Follow Bike/PL'

            temp['Cus_ID']                = row['CUS_ID']
            if int(row['card_type']) < 100:
               temp['Product_code'] = '301 - Credit Card'
            else:
               temp['Product_code'] = '302 - Cash Card'
            
            if companyInfo != None:
               temp['Partner_name_company']  = companyInfo['COMPANY'] 
            else: 
               temp['Partner_name_company']  = ''

            temp['Phone']       = row['phone']

            if zaccfInfo != None:
               temp['Dealer_code']  = zaccfInfo['WRK_BRN'] 
            else: 
               temp['Dealer_code']  = ''

            temp['Dealer_name'] = ''
            # temp['time'] = tdelta.days
            temp['createdAt'] = time.time()
            temp['createdBy'] = 'system'
            insertData.append(temp)



   aggregate_zaccf = [
      {
          "$match":
          {
              "LIC_NO": {'$in' : lic_no_arr},
          }
      }
   ]
   data_zaccf = mongodb.aggregate_pipeline(MONGO_COLLECTION=zaccf_collection,aggregate_pipeline=aggregate_zaccf)
   if data_zaccf != None:
      for row in data_zaccf:
         if 'account_number' in row.keys():
            # print(row['account_number'])
            lnjc05Info = mongodb.getOne(MONGO_COLLECTION=lnjc05_collection, WHERE={'account_number': str(row['account_number'])},
               SELECT=['group_id','cus_name','due_date'])

            customerInfo = mongodb.getOne(MONGO_COLLECTION=customer_collection, WHERE={'account_number': str(row['account_number'])},
                     SELECT=['profession'])
            invest = mongodb.getOne(MONGO_COLLECTION=investigation_collection, WHERE={'contract_no': str(row['account_number'])},
                     SELECT=['cndk_no'])
            code = mongodb.getOne(MONGO_COLLECTION=action_code_collection, WHERE={'account_number': str(row['account_number'])},
                     SELECT=['reason_nonpayment','action_code','raaStatus'])
            
            countsite = mongodb.count(MONGO_COLLECTION=site_collection, WHERE={'contract_no': str(row['account_number'])})
            
            countsiteFielder = mongodb.count(MONGO_COLLECTION=site_collection, WHERE={'contract_no': str(row['account_number']), 'field_staff': {'$nin': ['', 'null']} })
            
            site = mongodb.get(MONGO_COLLECTION=site_collection, WHERE={'contract_no': str(row['account_number'])},
                     SELECT=['contract_no','report_date'],SORT=[("created_at", -1)], SKIP=0, TAKE=1)
            
            cdrInfo = mongodb.get(MONGO_COLLECTION=cdr_collection, WHERE={'customernumber': str(row['MOBILE_NO']), "direction" : "outbound"},
                     SELECT=['starttime'],SORT=[("starttime", -1)], SKIP=0, TAKE=1)

            companyInfo = mongodb.getOne(MONGO_COLLECTION=cus_assigned_collection, WHERE={'CONTRACTNR': str(row['account_number'])},
                     SELECT=['COMPANY'])

            temp = {}
            temp['Account_number']  = row['account_number']
            temp['Name']            = row['name']
            temp['Release_date']    = row['FRELD8']
            temp['Release_amount']  = row['APPROV_LMT']
            temp['Interest_rate']   = row['INT_RATE']
            temp['Loan_Term']       = row['TERM_ID']
            temp['Off_balance']     = row['W_ORG']


            if len(str(row['NP_DT8'])) == 7:
               row['NP_DT8'] = '0'+str(row['NP_DT8'])
            NP_DT8 = str(row['NP_DT8'])
            NP_DT8 = NP_DT8[0:2]+'-'+NP_DT8[2:4]+'-'+NP_DT8[4:8]

            if len(str(row['F_PDT'])) == 7:
               row['F_PDT'] = '0'+str(row['F_PDT'])
            F_PDT = str(row['F_PDT'])
            F_PDT = F_PDT[0:2]+'-'+F_PDT[2:4]+'-'+F_PDT[4:8]

            # d1       = NP_DT8.strftime(FMT)
            # d2       = F_PDT.strftime(FMT)
            

            if lnjc05Info != None:
               temp['Group']        = lnjc05Info['group_id']
               temp['Name']         = lnjc05Info['cus_name']
               temp['Due_date']     = lnjc05Info['due_date']  

               due_date = datetime.fromtimestamp(lnjc05Info['due_date'])
               d1       = due_date.strftime(FMT)
               # d2       = F_PDT.strftime(FMT)
               tdelta   = datetime.strptime(d1, FMT) - datetime.strptime(F_PDT, FMT)
               kq       = tdelta.days /30
               temp['Actual_payment']  = round_down(kq,1)
               
               if 'outstanding_principal' in lnjc05Info.keys():
                  temp['Outstanding_balance'] = lnjc05Info['outstanding_principal']

            else:
               tdelta   = datetime.strptime(NP_DT8, FMT) - datetime.strptime(F_PDT, FMT)
               kq       = tdelta.days /30
               temp['Actual_payment']  = round_down(kq,1)

            if customerInfo != None and 'profession' in customerInfo.keys():
               temp['Profession'] = customerInfo['profession']

            temp['MRC'] = invest['cndk_no'] if invest !=None else ''
            temp['Reason_of_uncollected'] = code['reason_nonpayment'] if code != None and 'reason_nonpayment' in code else ''
            if code != None and code['action_code'] =="RAA":
               cod = code['raaStatus']
            else:
               cod = ''
            temp['If_bike_is_defined'] =  cod
            temp['If_site_visit_made'] = "Yes" if countsite > 0 else 'No'
            temp['If_there_is_fielder_in_location'] = "Yes" if countsiteFielder > 0 else 'No'
            temp['No_of_site_visit_made'] = countsite
            if site != None:
               for st in site:
                  temp['Last_date_made_field_visit'] = st['report_date']

            if cdrInfo != None:
               for cdr in cdrInfo:
                  starttime = datetime.fromtimestamp(cdr['starttime'])
                  temp['Last_date_made_collections_call'] = starttime.strftime(FMT)
            
            temp['If_still_collectable'] = ''

            if lnjc05Info != None:
               temp['SMS_sent']              = '1' 
               temp['Call']                  = '1'
               temp['Send_reminder_letter']  = '1'
               temp['Litigation']            = '1'
            else:
               temp['SMS_sent']              = '3' 
               temp['Call']                  = '3'
               temp['Send_reminder_letter']  = '3'
               temp['Litigation']            = '3'

            if row['account_number'] in account_arr:
               today          = datetime.now()
               d1             = today.strftime(FMT)
               date_time      = datetime.fromtimestamp(lnjc05Info['due_date'])
               d2             = date_time.strftime(FMT)
               tdelta         = datetime.strptime(d1, FMT) - datetime.strptime(d2, FMT)
               temp['Note']   = tdelta.days
            else: 
               temp['Note']   = 'Follow Bike/PL'

            temp['Cus_ID']                = row['CUS_ID']
            product = mongodb.getOne(MONGO_COLLECTION=product_collection, WHERE={'code': str(row['PRODGRP_ID'])},SELECT=['name'])
            if product != None:
               temp['Product_code']          = product['name']
            if companyInfo != None:
               temp['Partner_name_company']  = companyInfo['COMPANY'] 
            else: 
               temp['Partner_name_company']  = ''

            temp['Phone']                 = row['MOBILE_NO']
            temp['Dealer_code'] = row['WRK_BRN']
            temp['Dealer_name'] = ''
            # temp['time'] = tdelta.days
            temp['createdAt'] = time.time()
            temp['createdBy'] = 'system'
            insertData.append(temp)

            

   if len(insertData) > 0:
      # mongodb.remove_document(MONGO_COLLECTION=collection)
      # pprint(insertData)
      mongodb.batch_insert(MONGO_COLLECTION=collection, insert_data=insertData)
   now_end         = datetime.now()
   log.write(now_end.strftime("%d/%m/%Y, %H:%M:%S") + ': End Log' + '\n')
   print('DONE')
except Exception as e:
    pprint(e)
    traceback.print_exc()
    log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
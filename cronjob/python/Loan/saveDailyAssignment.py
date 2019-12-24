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
collection         = common.getSubUser(subUserType, 'Daily_assignment_report')
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
wo_collection        = common.getSubUser(subUserType, 'WO_monthly')
action_code_collection        = common.getSubUser(subUserType, 'Action_code')
log         = open(base_url + "cronjob/python/Loan/log/DailyAssignment_log.txt","a")

try:
   data        = []
   insertData  = []
   insertDataCard   = []
   insertDataWO   = []
   now         = datetime.now()
   log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': Start Import' + '\n')

   today = date.today()
   # today = datetime.strptime('21/12/2019', "%d/%m/%Y").date()

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

   holidayOfMonth = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_off_sys'))
   listHoliday = map(lambda offDateRow: {offDateRow['off_date']}, holidayOfMonth)

   if todayTimeStamp in listHoliday:
      sys.exit()


   lawsuit_fields = _mongodb.getOne(MONGO_COLLECTION=jsonData_collection,SELECT=['data'],WHERE={'tags': ['LAWSUIT', 'fields']})
   raa_fields = _mongodb.getOne(MONGO_COLLECTION=jsonData_collection,SELECT=['data'],WHERE={'tags': ['RAA', 'fields']})
   users = _mongodb.get(MONGO_COLLECTION=user_collection,SELECT=['extension','agentname'],WHERE={'active': 'true'})
   i = 1
   # LNJC05
   aggregate_pipeline = [
      {
           "$project":
           {
               "account_number": 1,
               "mobile_num": 1,
               "group_id": 1,
               "cus_name": 1,
               "due_date": 1,
               "loan_overdue_amount": 1,
               "current_balance": 1,
               "outstanding_principal": 1,
               "officer_id": 1,
           }
       }
                # {
                #     "$lookup":
                #     {
                #         "from": action_code_collection,
                #         "localField": "account_number",
                #         "foreignField": "account_number",
                #         "as": "detail"
                #     }
                # }
   ]
   data = mongodb.aggregate_pipeline(MONGO_COLLECTION=lnjc05_collection,aggregate_pipeline=aggregate_pipeline)

   for row in data:
      action_code = mongodb.get(MONGO_COLLECTION=action_code_collection,WHERE={'account_number': str(row['account_number']), 'createdAt': {'$gte': todayTimeStamp}, 'createdAt': {'$lte': endTodayTimeStamp}},SORT=([('_id', -1)]),SKIP=0, TAKE=int(10000))
      if action_code != None:
         for detail in action_code:
            temp = {}
            temp['export_date']     = now.strftime("%d/%m/%Y")
            temp['index']           = i
            temp['account_number']  = row['account_number']
            temp['phone']           = row['mobile_num']
            temp['group_id']        = row['group_id']
            temp['name']            = row['cus_name']
            temp['overdue_date']    = row['due_date']
            temp['loan_overdue_amount']      = row['loan_overdue_amount']
            temp['current_balance']          = row['current_balance']
            temp['outstanding_principal']    = row['outstanding_principal']

            dialistDetail = mongodb.getOne(MONGO_COLLECTION=diallist_detail_collection,WHERE={'account_number': str(row['account_number']), 'createdAt': {'$gte': todayTimeStamp}, 'createdAt': {'$lte': endTodayTimeStamp}}, SELECT=['assign'])
            if dialistDetail != None:
               if 'assign' in dialistDetail.keys():
                  for user in list(users):
                     if user['extension'] == dialistDetail['assign']:
                        temp['assign']   = dialistDetail['assign']+' '+user['agentname']
               else:
                  extension = row['officer_id']
                  for user in list(users):
                     if user['extension'] == extension[6:9]:
                        temp['assign']   = extension[6:9]+' '+user['agentname']

            temp['chief']   = ''
            if 'action_code' in detail.keys():
               temp['action_code']          = detail['action_code']
            else:
               temp['action_code']          = ''

            temp['note']            = detail['note'] if 'note' in detail.keys() else ''
            for field in list(lawsuit_fields['data']):
               if field['field'] in detail.keys():
                  temp[field['field']]          = detail[field['field']]
            if 'promised_amount' in detail.keys():
               temp['promised_amount']          = detail['promised_amount']
            if 'reason_nonpayment' in detail.keys():
               temp['reason_nonpayment']          = detail['reason_nonpayment']
            if 'promised_person' in detail.keys():
               temp['promised_person']          = detail['promised_person']
            if 'promised_date' in detail.keys():
               temp['promised_date']          = detail['promised_date']
            if 'death_info' in detail.keys():
               temp['death_info']          = detail['death_info']
            if 'contact_person' in detail.keys():
               temp['contact_person']          = detail['contact_person']
            if 'reason_die' in detail.keys():
               temp['reason_die']          = detail['reason_die']
            if 'contact_person_phone' in detail.keys():
               temp['contact_person_phone']          = detail['contact_person_phone']
            if 'payment_amount' in detail.keys():
               temp['payment_amount']          = detail['payment_amount']
            if 'payment_date' in detail.keys():
               temp['payment_date']          = detail['payment_date']
            if 'payment_person' in detail.keys():
               temp['payment_person']          = detail['payment_person']
            if 'channel' in detail.keys():
               temp['channel']          = detail['channel']
            if 'promised_person_phone' in detail.keys():
               temp['promised_person_phone']          = detail['promised_person_phone']
            if 'fc_name' in detail.keys():
               temp['fc_name']          = detail['fc_name']
            if 'report_date' in detail.keys():
               temp['report_date']          = detail['report_date']
            for field_raa in list(raa_fields['data']):
               if field_raa['field'] in detail.keys():
                  temp[field_raa['field']]          = detail[field_raa['field']]
               # print(x['field'])

            temp['contacted'] = mongodb.count(MONGO_COLLECTION=cdr_collection,WHERE={'customernumber': str(row['mobile_num']), 'starttime': {'$gte': todayTimeStamp}, 'starttime': {'$lte': endTodayTimeStamp}})
            zaccf = mongodb.getOne(MONGO_COLLECTION=zaccf_collection, WHERE={'account_number': str(row['account_number'])},SELECT=['PRODGRP_ID'])
            if zaccf != None:
               temp['product_id']          = zaccf['PRODGRP_ID']

            temp['createdAt'] = todayTimeStamp
            temp['createdBy'] = 'system'
            # mongodb.insert(MONGO_COLLECTION=collection, insert_data=temp)
            insertData.append(temp)
            i = i+1
            # break

   if len(insertData) > 0:
      # mongodb.remove_document(MONGO_COLLECTION=collection)
      mongodb.batch_insert(MONGO_COLLECTION=collection, insert_data=insertData)

   # List of Account
   aggregate_pipeline = [
      {
           "$project":
           {
               "account_number": 1,
               "phone": 1,
               "cus_name": 1,
               "overdue_date": 1,
               "overdue_amt": 1,
               "cur_bal": 1,
           }
       }
             
   ]
   data_acc = mongodb.aggregate_pipeline(MONGO_COLLECTION=account_collection,aggregate_pipeline=aggregate_pipeline)
   for row in data_acc:
      sbv = mongodb.getOne(MONGO_COLLECTION=sbv_collection, WHERE={'contract_no': str(row['account_number'])},
               SELECT=['card_type','ob_principal_sale','ob_principal_cash'])
      group = mongodb.getOne(MONGO_COLLECTION=group_collection, WHERE={'account_number': str(row['account_number'])},
               SELECT=['group'])
      action_code = mongodb.get(MONGO_COLLECTION=action_code_collection,WHERE={'account_number': str(row['account_number']),'createdAt': {'$gte': todayTimeStamp}, 'createdAt': {'$lte': endTodayTimeStamp}},SORT=([('_id', -1)]),SKIP=0, TAKE=int(10000))
      if action_code != None:
         for detail in action_code:
            temp = {}
            temp['export_date']     = now.strftime("%d/%m/%Y")
            temp['index']           = i
            temp['account_number']  = row['account_number']
            temp['phone']           = row['phone']
            temp['name']            = row['cus_name']
            temp['overdue_date']    = row['overdue_date']
            temp['loan_overdue_amount']      = row['overdue_amt']
            temp['current_balance']          = row['cur_bal']

            if sbv != None:
               temp['product_id']                  = str(sbv['card_type'])
               temp['outstanding_principal']       = float(sbv['ob_principal_sale']) + float(sbv['ob_principal_cash'])

            if group != None:
               temp['group_id']                  = str(group['group'])

            temp['contacted'] = mongodb.count(MONGO_COLLECTION=cdr_collection,WHERE={'customernumber': str(row['phone']), 'starttime': {'$gte': todayTimeStamp}, 'starttime': {'$lte': endTodayTimeStamp}})

            dialistDetail = mongodb.getOne(MONGO_COLLECTION=diallist_detail_collection,WHERE={'account_number': str(row['account_number']), 'createdAt': {'$gte': todayTimeStamp}, 'createdAt': {'$lte': endTodayTimeStamp}}, SELECT=['assign'])
            if dialistDetail != None:
               for user in list(users):
                  if user['extension'] == dialistDetail['assign']:
                     temp['assign']   = dialistDetail['assign']+' '+user['agentname']
            else:
               temp['assign']          = ''

            temp['chief']   = ''
            if 'action_code' in detail.keys():
               temp['action_code']          = detail['action_code']

            temp['note']            = detail['note'] if 'note' in detail.keys() else ''

            for field in list(lawsuit_fields['data']):
               if field['field'] in detail.keys():
                  temp[field['field']]          = detail[field['field']]
            if 'promised_amount' in detail.keys():
               temp['promised_amount']          = detail['promised_amount']
            if 'reason_nonpayment' in detail.keys():
               temp['reason_nonpayment']          = detail['reason_nonpayment']
            if 'promised_person' in detail.keys():
               temp['promised_person']          = detail['promised_person']
            if 'promised_date' in detail.keys():
               temp['promised_date']          = detail['promised_date']
            if 'death_info' in detail.keys():
               temp['death_info']          = detail['death_info']
            if 'contact_person' in detail.keys():
               temp['contact_person']          = detail['contact_person']
            if 'reason_die' in detail.keys():
               temp['reason_die']          = detail['reason_die']
            if 'contact_person_phone' in detail.keys():
               temp['contact_person_phone']          = detail['contact_person_phone']
            if 'payment_amount' in detail.keys():
               temp['payment_amount']          = detail['payment_amount']
            if 'payment_date' in detail.keys():
               temp['payment_date']          = detail['payment_date']
            if 'payment_person' in detail.keys():
               temp['payment_person']          = detail['payment_person']
            if 'channel' in detail.keys():
               temp['channel']          = detail['channel']
            if 'promised_person_phone' in detail.keys():
               temp['promised_person_phone']          = detail['promised_person_phone']
            if 'fc_name' in detail.keys():
               temp['fc_name']          = detail['fc_name']
            if 'report_date' in detail.keys():
               temp['report_date']          = detail['report_date']
            for field_raa in list(raa_fields['data']):
               if field_raa['field'] in detail.keys():
                  temp[field_raa['field']]          = detail[field_raa['field']]


            temp['createdAt'] = todayTimeStamp
            temp['createdBy'] = 'system'
            # mongodb.insert(MONGO_COLLECTION=collection, insert_data=temp)
            insertDataCard.append(temp)
            i = i+1
            # break

   if len(insertDataCard) > 0:
      # mongodb.remove_document(MONGO_COLLECTION=collection)
      mongodb.batch_insert(MONGO_COLLECTION=collection, insert_data=insertDataCard)

   

   # Wo
   aggregate_pipeline = [
      {
           "$project":
           {
               "ACCTNO": 1,
               "PHONE": 1,
               "NGAY_QUA_HAN": 1,
               "CUS_NM": 1,
               "WO9711": 1,
               "WO9712": 1,
               "WO9713": 1,
               "PROD_ID": 1,
           }
       }
             
   ]
   data_wo = mongodb.aggregate_pipeline(MONGO_COLLECTION=wo_collection,aggregate_pipeline=aggregate_pipeline)
   for row in data_wo:
      action_code = mongodb.get(MONGO_COLLECTION=action_code_collection,WHERE={'account_number': str(row['ACCTNO']), 'createdAt': {'$gte': todayTimeStamp}, 'createdAt': {'$lte': endTodayTimeStamp}},SORT=([('_id', -1)]),SKIP=0, TAKE=int(10000))
      if action_code != None:
         for detail in action_code:
            temp = {}
            temp['export_date']     = now.strftime("%d/%m/%Y")
            temp['index']           = i
            temp['account_number']  = row['ACCTNO']
            temp['phone']           = row['PHONE']
            temp['name']            = row['CUS_NM']
            if row['NGAY_QUA_HAN'] != '':
               try:
                  temp['overdue_date']    = common.convertTimestamp(row['NGAY_QUA_HAN'],formatString='%m/%d/%Y')
               except Exception as e:
                  temp['overdue_date']    = common.convertTimestamp(row['NGAY_QUA_HAN'],formatString='%d/%m/%Y')
            else:
               temp['overdue_date'] = ''

            temp['loan_overdue_amount']      = float(row['WO9711'])+float(row['WO9712'])+float(row['WO9713'])
            temp['current_balance']          = float(row['WO9711'])+float(row['WO9712'])+float(row['WO9713'])
            temp['product_id']               = str(row['PROD_ID'])
            temp['outstanding_principal']    = float(row['WO9711'])

            temp['group_id']                  = 'F'
            temp['contacted'] = mongodb.count(MONGO_COLLECTION=cdr_collection,WHERE={'customernumber': str(row['PHONE']), 'starttime': {'$gte': todayTimeStamp}, 'starttime': {'$lte': endTodayTimeStamp}})

            dialistDetail = mongodb.getOne(MONGO_COLLECTION=diallist_detail_collection,WHERE={'account_number': str(row['account_number']), 'createdAt': {'$gte': todayTimeStamp}, 'createdAt': {'$lte': endTodayTimeStamp}}, SELECT=['assign'])
            if dialistDetail != None:
               for user in list(users):
                  if user['extension'] == dialistDetail['assign']:
                     temp['assign']   = dialistDetail['assign']+' '+user['agentname']
            else:
               temp['assign']          = ''

            temp['chief']   = ''

            if 'action_code' in detail.keys():
               temp['action_code']          = detail['action_code']

            temp['note']            = detail['note'] if 'note' in detail.keys() else ''

            for field in list(lawsuit_fields['data']):
               if field['field'] in detail.keys():
                  temp[field['field']]          = detail[field['field']]
            if 'promised_amount' in detail.keys():
               temp['promised_amount']          = detail['promised_amount']
            if 'reason_nonpayment' in detail.keys():
               temp['reason_nonpayment']          = detail['reason_nonpayment']
            if 'promised_person' in detail.keys():
               temp['promised_person']          = detail['promised_person']
            if 'promised_date' in detail.keys():
               temp['promised_date']          = detail['promised_date']
            if 'death_info' in detail.keys():
               temp['death_info']          = detail['death_info']
            if 'contact_person' in detail.keys():
               temp['contact_person']          = detail['contact_person']
            if 'reason_die' in detail.keys():
               temp['reason_die']          = detail['reason_die']
            if 'contact_person_phone' in detail.keys():
               temp['contact_person_phone']          = detail['contact_person_phone']
            if 'payment_amount' in detail.keys():
               temp['payment_amount']          = detail['payment_amount']
            if 'payment_date' in detail.keys():
               temp['payment_date']          = detail['payment_date']
            if 'payment_person' in detail.keys():
               temp['payment_person']          = detail['payment_person']
            if 'channel' in detail.keys():
               temp['channel']          = detail['channel']
            if 'promised_person_phone' in detail.keys():
               temp['promised_person_phone']          = detail['promised_person_phone']
            if 'fc_name' in detail.keys():
               temp['fc_name']          = detail['fc_name']
            if 'report_date' in detail.keys():
               temp['report_date']          = detail['report_date']
            for field_raa in list(raa_fields['data']):
               if field_raa['field'] in detail.keys():
                  temp[field_raa['field']]          = detail[field_raa['field']]


            temp['createdAt'] = todayTimeStamp
            temp['createdBy'] = 'system'

            # mongodb.insert(MONGO_COLLECTION=collection, insert_data=temp)
            insertDataWO.append(temp)
            i = i+1



   if len(insertDataWO) > 0:
      # mongodb.remove_document(MONGO_COLLECTION=collection)
      mongodb.batch_insert(MONGO_COLLECTION=collection, insert_data=insertDataWO)

   now_end         = datetime.now()
   log.write(now_end.strftime("%d/%m/%Y, %H:%M:%S") + ': End Log' + '\n')
   print('DONE')
except Exception as e:
   pprint(e)
   log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
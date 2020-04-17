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

common      = Common()
base_url    = common.base_url()
wff_env     = common.wff_env(base_url)
mongodb     = Mongodb(MONGODB="worldfone4xs", WFF_ENV=wff_env)
_mongodb    = Mongodb(MONGODB="_worldfone4xs", WFF_ENV=wff_env)
now         = datetime.now()
subUserType = 'LO'
collection         = common.getSubUser(subUserType, 'Daily_assignment_report')
# lnjc05_collection  = common.getSubUser(subUserType, 'LNJC05_yesterday')
zaccf_collection     = common.getSubUser(subUserType, 'ZACCF')
product_collection   = common.getSubUser(subUserType, 'Product')
sbv_collection       = common.getSubUser(subUserType, 'SBV')
diallist_detail_collection  = common.getSubUser(subUserType, 'Diallist_detail')
cdr_collection       = common.getSubUser(subUserType, 'worldfonepbxmanager')
jsonData_collection  = common.getSubUser(subUserType, 'Jsondata')
user_collection      = common.getSubUser(subUserType, 'User')
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
   # today = datetime.strptime('25/03/2020', "%d/%m/%Y").date()

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
           "$match":
           {
               "starttime" : {'$gte' : todayTimeStamp,'$lte' : endTodayTimeStamp},
               "direction" : 'outbound',
               # "userextension": '4022'
               
           }
      }
            
   ]
   data = mongodb.aggregate_pipeline(MONGO_COLLECTION=cdr_collection,aggregate_pipeline=aggregate_pipeline)
   if data != None:
      for cdr in data:
         temp = {}
         temp['export_date']     = todayString
         temp['index']           = i
         temp['contacted']       = cdr['customernumber']
         temp['disposition']     = cdr['disposition'] if 'disposition' in cdr.keys() else ''

         if 'dialtype' in cdr.keys() and cdr['dialtype'] == 'auto':
            temp['duration'] = cdr['callduration'] if 'callduration' in cdr.keys() else 0
         else:
            temp['duration'] = cdr['totalduration'] if 'totalduration' in cdr.keys() else 0

         temp['callduration'] = cdr['callduration'] if 'callduration' in cdr.keys() else 0

         if 'action_code' in cdr.keys():
           temp['action_code']     = cdr['action_code'] 
        
         user = _mongodb.getOne(MONGO_COLLECTION=user_collection, WHERE={'extension': str(cdr['userextension'])},SELECT=['agentname'])
         if user != None:
            temp['assign']       = cdr['userextension'] + '-' + user['agentname']
         else:
            temp['assign']       = cdr['userextension']

         groupInfo = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Group'), WHERE={'members': str(cdr['userextension'])},SELECT=['lead'],SORT=[("updatedAt", -1)], SKIP=0, TAKE=1)
         if groupInfo != None:
            for group in groupInfo:
               if 'lead' in group.keys():
                  user1 = _mongodb.getOne(MONGO_COLLECTION=user_collection, WHERE={'extension': str(group['lead'])},SELECT=['agentname'])
                  if user1 != None:
                     temp['chief']       = group['lead'] + '-' + user1['agentname']
               else:
                  user1 = _mongodb.getOne(MONGO_COLLECTION=user_collection, WHERE={'extension': str(cdr['userextension'])},SELECT=['agentname'])
                  if user1 != None:
                     temp['chief']       = cdr['userextension'] + '-' + user1['agentname']

         diallistInfo = None
         if 'dialid' in cdr.keys():
            if len(str(cdr['dialid'])) == 24:
               diallistInfo = mongodb.getOne(MONGO_COLLECTION=diallist_detail_collection, WHERE={'_id': ObjectId(str(cdr['dialid'])),  "createdAt" : {'$gte' : todayTimeStamp,'$lte' : endTodayTimeStamp}}) 
            else:
               diallistInfo = mongodb.getOne(MONGO_COLLECTION=diallist_detail_collection, WHERE={'$or' : [{ 'mobile_num' : str(cdr['customernumber'])}, { 'phone' : str(cdr['customernumber'])},  { 'other_phones' :str(cdr['customernumber'])} ],  "createdAt" : {'$gte' : todayTimeStamp,'$lte' : endTodayTimeStamp} }) 

         if 'dialid' not in cdr.keys():
            diallistInfo = mongodb.getOne(MONGO_COLLECTION=diallist_detail_collection, WHERE={'$or' : [{ 'mobile_num' : str(cdr['customernumber'])}, { 'phone' : str(cdr['customernumber'])},  { 'other_phones' :str(cdr['customernumber'])} ],  "createdAt" : {'$gte' : todayTimeStamp,'$lte' : endTodayTimeStamp} }) 

         temp['connected'] = 1
         
         aggregate_pipeline_action = [
            {
                 "$match":
                 {
                     "calluuid" : cdr['calluuid']
                 }
            },{
                 "$project":
                 {
                     "_id": 0,
                     # "LIC_NO": 0,
                     "account_number": 0,
                     "account_type": 0,
                 }
            }
                  
         ]
         actionCode = mongodb.aggregate_pipeline(MONGO_COLLECTION=action_code_collection,aggregate_pipeline=aggregate_pipeline_action)
         if actionCode != None:
            for row_1 in list(actionCode):
               for x in row_1.keys():
                  temp[x] = row_1[x]
         
         if diallistInfo != None:
            temp['account_number']  = diallistInfo['account_number']
            temp['name']            = diallistInfo['cus_name']
            temp['overdue_date']             = diallistInfo['overdue_date'] if 'overdue_date' in diallistInfo.keys() else diallistInfo['due_date']
            temp['loan_overdue_amount']      = diallistInfo['overdue_amt'] if 'overdue_amt' in diallistInfo.keys() else diallistInfo['loan_overdue_amount']
            temp['current_balance']          = diallistInfo['cur_bal'] if 'cur_bal' in diallistInfo.keys() else diallistInfo['current_balance']
            temp['group_id']                 = diallistInfo['group_id'].replace('-','') if 'group_id' in diallistInfo.keys() else ''
            
            product_id = ''
            if 'overdue_indicator' in diallistInfo:
               # groupData = mongodb.getOne(MONGO_COLLECTION=diallist_detail_collection, WHERE={'account_number': temp['account_number'],  "createdAt" : {'$gte' : todayTimeStamp,'$lte' : endTodayTimeStamp}}) 
               # if groupData != None:
               #    temp['group_id']                 = groupData['group_id'].replace('-','') if 'group_id' in groupData.keys() else ''
               sbv_stored = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'SBV_Stored'), WHERE={'contract_no': str(temp['account_number'])},SELECT=['overdue_indicator','kydue'],SORT=[("_id", -1)], SKIP=0, TAKE=1)
               if sbv_stored != None:
                  for store in sbv_stored:
                     temp['group_id']                 = store['overdue_indicator'] + store['kydue']
               

               sbv = mongodb.getOne(MONGO_COLLECTION=sbv_collection, WHERE={'contract_no': str(temp['account_number'])},SELECT=['card_type','ob_principal_sale','ob_principal_cash'])
               if sbv != None:
                  temp['outstanding_principal']       = float(sbv['ob_principal_sale']) + float(sbv['ob_principal_cash'])
                  if int(sbv['card_type']) < 100:
                     product_id = '301'
                  else:
                     product_id = '302'
            else:
               if 'PRODGRP_ID' in diallistInfo.keys():
                  product_id                       = diallistInfo['PRODGRP_ID']
               else:
                  zaccfInfo = mongodb.getOne(MONGO_COLLECTION=zaccf_collection, WHERE={'account_number': str(temp['account_number'])},SELECT=['PRODGRP_ID'])
                  if zaccfInfo != None:
                     product_id                       = zaccfInfo['PRODGRP_ID']

               temp['outstanding_principal']    = diallistInfo['outstanding_principal'] if 'outstanding_principal' in diallistInfo.keys() else ''

            product = mongodb.getOne(MONGO_COLLECTION=product_collection, WHERE={'code': str(product_id)},SELECT=['name'])
            if product != None:
               temp['product_id'] = product['name']


         temp['createdAt'] = int(todayTimeStamp)
         temp['createdBy'] = 'system'

         print(i)
         insertData.append(temp)
         i = i+1
   #       # break
   # # break

   if len(insertData) > 0:
      mongodb.batch_insert(MONGO_COLLECTION=collection, insert_data=insertData)

   now_end         = datetime.now()
   log.write(now_end.strftime("%d/%m/%Y, %H:%M:%S") + ': End Log' + '\n')
   print('DONE')
except Exception as e:
   print(traceback.format_exc())
   log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
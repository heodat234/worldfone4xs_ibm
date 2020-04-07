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
from datetime import date, timedelta
from dateutil.relativedelta import relativedelta
from pprint import pprint
from bson import ObjectId
from helper.common import Common
from helper.jaccs import Config
from calendar import monthrange

common      = Common()
base_url    = common.base_url()
wff_env     = common.wff_env(base_url)
mongodb = Mongodb(MONGODB="worldfone4xs",WFF_ENV=wff_env)
_mongodb = Mongodb(MONGODB="_worldfone4xs",WFF_ENV=wff_env)
config = Config()
common = Common()
log = open(base_url + "cronjob/python/Loan/log/saveOutsoucingCollectionTrend.txt","a")
now = datetime.now()
subUserType = 'LO'
collection              = common.getSubUser(subUserType, 'Cus_assigned_partner_prod')
collection_temp         = common.getSubUser(subUserType, 'Outsoucing_Collection_Trend_report_temp')
collection_lnjc05       = common.getSubUser(subUserType, 'LNJC05')
collection_listofAccount = common.getSubUser(subUserType, 'List_of_account_in_collection_01042020')

collection_ln3206f      =  common.getSubUser(subUserType, 'LN3206F')
collection_gl_2018      =  common.getSubUser(subUserType, 'Report_input_payment_of_card')

collection_amount_report = common.getSubUser(subUserType, 'Outsoucing_Collection_Trend_Amount_Report')
collection_assigned_report = common.getSubUser(subUserType, 'Outsoucing_Collection_Trend_AssignDPD_Report')
try :
   insertData = []
   today = date.today()
   # today = datetime.strptime('12/10/2019', "%d/%m/%Y").date()

   day = today.day
   month = today.month
   year = today.year
   weekday = today.weekday()
   lastDayOfMonth = calendar.monthrange(year, month)[1]

   todayString = today.strftime("%d/%m/%Y")
   todayTimeStamp = int(time.mktime(time.strptime(str(todayString + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
	# todayString = "26/03/2020"
   day_of_month      = monthrange(year, month)[1]
   end_day_of_month  = today.replace(day=day_of_month)
   endDayString      = end_day_of_month.strftime("%d/%m/%Y")
   endDayTimeStamp   = int(time.mktime(time.strptime(str(endDayString + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))

   start_day_of_month   = today.replace(day=1)
   startDayString       = start_day_of_month.strftime("%d/%m/%Y")
   startDayTimeStamp      = int(time.mktime(time.strptime(str(startDayString + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))

   yesterday 	= today - timedelta(days=1)
   if day == 1 :
      lastMonth = yesterday.month
   else:
      lastMonth = month


	# TẠO ROW CHO TỪNG PARTNER NẾU CHƯA TỒN TẠI
   partner_lists = mongodb.getDistinct(MONGO_COLLECTION=collection , SELECT="COMPANY")
   if partner_lists != None:
      for row_partner in partner_lists:
         pprint(row_partner)
         exists_amount = mongodb.count(MONGO_COLLECTION=collection_amount_report,WHERE={'partner':row_partner, 'year' : {'$eq' : str(year)} })
         if exists_amount == 0 : mongodb.insert(MONGO_COLLECTION=collection_amount_report, insert_data={
				'partner': row_partner,
				'outsoucing' : {
					'account' : {
						'before' : {
							'l30' : { 'T1':0,'T2':0,'T3':0,'T4':0,'T5':0,'T6':0,'T7':0,'T8':0,'T9':0,'T10':0,'T11':0,'T12':0},
							'p30' : { 'T1':0,'T2':0,'T3':0,'T4':0,'T5':0,'T6':0,'T7':0,'T8':0,'T9':0,'T10':0,'T11':0,'T12':0},
							'p60' : { 'T1':0,'T2':0,'T3':0,'T4':0,'T5':0,'T6':0,'T7':0,'T8':0,'T9':0,'T10':0,'T11':0,'T12':0},
							'p90' : { 'T1':0,'T2':0,'T3':0,'T4':0,'T5':0,'T6':0,'T7':0,'T8':0,'T9':0,'T10':0,'T11':0,'T12':0},
							'p180' : { 'T1':0,'T2':0,'T3':0,'T4':0,'T5':0,'T6':0,'T7':0,'T8':0,'T9':0,'T10':0,'T11':0,'T12':0},
							'subtotal':{ 'T1':0,'T2':0,'T3':0,'T4':0,'T5':0,'T6':0,'T7':0,'T8':0,'T9':0,'T10':0,'T11':0,'T12':0},
						},
						'writeof':{
							'p360':{ 'T1':0,'T2':0,'T3':0,'T4':0,'T5':0,'T6':0,'T7':0,'T8':0,'T9':0,'T10':0,'T11':0,'T12':0},
						}
					},
					'amount':{
						'before' : {
							'l30' : { 'T1':0,'T2':0,'T3':0,'T4':0,'T5':0,'T6':0,'T7':0,'T8':0,'T9':0,'T10':0,'T11':0,'T12':0},
							'p30' : { 'T1':0,'T2':0,'T3':0,'T4':0,'T5':0,'T6':0,'T7':0,'T8':0,'T9':0,'T10':0,'T11':0,'T12':0},
							'p60' : { 'T1':0,'T2':0,'T3':0,'T4':0,'T5':0,'T6':0,'T7':0,'T8':0,'T9':0,'T10':0,'T11':0,'T12':0},
							'p90' : { 'T1':0,'T2':0,'T3':0,'T4':0,'T5':0,'T6':0,'T7':0,'T8':0,'T9':0,'T10':0,'T11':0,'T12':0},
							'p180' : { 'T1':0,'T2':0,'T3':0,'T4':0,'T5':0,'T6':0,'T7':0,'T8':0,'T9':0,'T10':0,'T11':0,'T12':0},
							'subtotal': { 'T1':0,'T2':0,'T3':0,'T4':0,'T5':0,'T6':0,'T7':0,'T8':0,'T9':0,'T10':0,'T11':0,'T12':0},
						},
						'writeof' : {
							'p360' : { 'T1':0,'T2':0,'T3':0,'T4':0,'T5':0,'T6':0,'T7':0,'T8':0,'T9':0,'T10':0,'T11':0,'T12':0},
						}
					}
				},
				'collected' : {
					'account' : {
						'before' : {
							'l30' : { 'T1':0,'T2':0,'T3':0,'T4':0,'T5':0,'T6':0,'T7':0,'T8':0,'T9':0,'T10':0,'T11':0,'T12':0},
							'p30' : { 'T1':0,'T2':0,'T3':0,'T4':0,'T5':0,'T6':0,'T7':0,'T8':0,'T9':0,'T10':0,'T11':0,'T12':0},
							'p60' : { 'T1':0,'T2':0,'T3':0,'T4':0,'T5':0,'T6':0,'T7':0,'T8':0,'T9':0,'T10':0,'T11':0,'T12':0},
							'p90' : { 'T1':0,'T2':0,'T3':0,'T4':0,'T5':0,'T6':0,'T7':0,'T8':0,'T9':0,'T10':0,'T11':0,'T12':0},
							'p180' : { 'T1':0,'T2':0,'T3':0,'T4':0,'T5':0,'T6':0,'T7':0,'T8':0,'T9':0,'T10':0,'T11':0,'T12':0},
							'subtotal' : { 'T1':0,'T2':0,'T3':0,'T4':0,'T5':0,'T6':0,'T7':0,'T8':0,'T9':0,'T10':0,'T11':0,'T12':0},
						},
						'writeof' : {
							'p360' : { 'T1':0,'T2':0,'T3':0,'T4':0,'T5':0,'T6':0,'T7':0,'T8':0,'T9':0,'T10':0,'T11':0,'T12':0},
						}
					},
					'amount' : {
						'before' : {
							'l30' : {'T1':0,'T2':0,'T3':0,'T4':0,'T5':0,'T6':0,'T7':0,'T8':0,'T9':0,'T10':0,'T11':0,'T12':0},
							'p30' : {'T1':0,'T2':0,'T3':0,'T4':0,'T5':0,'T6':0,'T7':0,'T8':0,'T9':0,'T10':0,'T11':0,'T12':0},
							'p60' : {'T1':0,'T2':0,'T3':0,'T4':0,'T5':0,'T6':0,'T7':0,'T8':0,'T9':0,'T10':0,'T11':0,'T12':0},
							'p90' : {'T1':0,'T2':0,'T3':0,'T4':0,'T5':0,'T6':0,'T7':0,'T8':0,'T9':0,'T10':0,'T11':0,'T12':0},
							'p180' : {'T1':0,'T2':0,'T3':0,'T4':0,'T5':0,'T6':0,'T7':0,'T8':0,'T9':0,'T10':0,'T11':0,'T12':0},
							'subtotal':{'T1':0,'T2':0,'T3':0,'T4':0,'T5':0,'T6':0,'T7':0,'T8':0,'T9':0,'T10':0,'T11':0,'T12':0},
						},
						'writeof' : {
							'p360' : {'T1':0,'T2':0,'T3':0,'T4':0,'T5':0,'T6':0,'T7':0,'T8':0,'T9':0,'T10':0,'T11':0,'T12':0},
						}
					}
				},
				'year' : str(year)
			})

         exists_assign_dpd = mongodb.count(MONGO_COLLECTION=collection_assigned_report,WHERE={'partner':row_partner, 'year' : {'$eq' : str(year)} })
         if exists_assign_dpd == 0 : mongodb.insert(MONGO_COLLECTION=collection_assigned_report, insert_data={
				'partner': row_partner,
				'outsoucing' : {
					'account' : {
						'before' : {
							'l30' : { 'T1':0,'T2':0,'T3':0,'T4':0,'T5':0,'T6':0,'T7':0,'T8':0,'T9':0,'T10':0,'T11':0,'T12':0},
							'p30' : { 'T1':0,'T2':0,'T3':0,'T4':0,'T5':0,'T6':0,'T7':0,'T8':0,'T9':0,'T10':0,'T11':0,'T12':0},
							'p60' : { 'T1':0,'T2':0,'T3':0,'T4':0,'T5':0,'T6':0,'T7':0,'T8':0,'T9':0,'T10':0,'T11':0,'T12':0},
							'p90' : { 'T1':0,'T2':0,'T3':0,'T4':0,'T5':0,'T6':0,'T7':0,'T8':0,'T9':0,'T10':0,'T11':0,'T12':0},
							'p180' : { 'T1':0,'T2':0,'T3':0,'T4':0,'T5':0,'T6':0,'T7':0,'T8':0,'T9':0,'T10':0,'T11':0,'T12':0},
							'subtotal':{ 'T1':0,'T2':0,'T3':0,'T4':0,'T5':0,'T6':0,'T7':0,'T8':0,'T9':0,'T10':0,'T11':0,'T12':0},
						},
						'writeof':{
							'p360':{ 'T1':0,'T2':0,'T3':0,'T4':0,'T5':0,'T6':0,'T7':0,'T8':0,'T9':0,'T10':0,'T11':0,'T12':0},
						}
					},
					'amount':{
						'before' : {
							'l30' : { 'T1':0,'T2':0,'T3':0,'T4':0,'T5':0,'T6':0,'T7':0,'T8':0,'T9':0,'T10':0,'T11':0,'T12':0},
							'p30' : { 'T1':0,'T2':0,'T3':0,'T4':0,'T5':0,'T6':0,'T7':0,'T8':0,'T9':0,'T10':0,'T11':0,'T12':0},
							'p60' : { 'T1':0,'T2':0,'T3':0,'T4':0,'T5':0,'T6':0,'T7':0,'T8':0,'T9':0,'T10':0,'T11':0,'T12':0},
							'p90' : { 'T1':0,'T2':0,'T3':0,'T4':0,'T5':0,'T6':0,'T7':0,'T8':0,'T9':0,'T10':0,'T11':0,'T12':0},
							'p180' : { 'T1':0,'T2':0,'T3':0,'T4':0,'T5':0,'T6':0,'T7':0,'T8':0,'T9':0,'T10':0,'T11':0,'T12':0},
							'subtotal': { 'T1':0,'T2':0,'T3':0,'T4':0,'T5':0,'T6':0,'T7':0,'T8':0,'T9':0,'T10':0,'T11':0,'T12':0},
						},
						'writeof' : {
							'p360' : { 'T1':0,'T2':0,'T3':0,'T4':0,'T5':0,'T6':0,'T7':0,'T8':0,'T9':0,'T10':0,'T11':0,'T12':0},
						}
					}
				},
				'collected' : {
					'account' : {
						'before' : {
							'l30' : { 'T1':0,'T2':0,'T3':0,'T4':0,'T5':0,'T6':0,'T7':0,'T8':0,'T9':0,'T10':0,'T11':0,'T12':0},
							'p30' : { 'T1':0,'T2':0,'T3':0,'T4':0,'T5':0,'T6':0,'T7':0,'T8':0,'T9':0,'T10':0,'T11':0,'T12':0},
							'p60' : { 'T1':0,'T2':0,'T3':0,'T4':0,'T5':0,'T6':0,'T7':0,'T8':0,'T9':0,'T10':0,'T11':0,'T12':0},
							'p90' : { 'T1':0,'T2':0,'T3':0,'T4':0,'T5':0,'T6':0,'T7':0,'T8':0,'T9':0,'T10':0,'T11':0,'T12':0},
							'p180' : { 'T1':0,'T2':0,'T3':0,'T4':0,'T5':0,'T6':0,'T7':0,'T8':0,'T9':0,'T10':0,'T11':0,'T12':0},
							'subtotal' : { 'T1':0,'T2':0,'T3':0,'T4':0,'T5':0,'T6':0,'T7':0,'T8':0,'T9':0,'T10':0,'T11':0,'T12':0},
						},
						'writeof' : {
							'p360' : { 'T1':0,'T2':0,'T3':0,'T4':0,'T5':0,'T6':0,'T7':0,'T8':0,'T9':0,'T10':0,'T11':0,'T12':0},
						}
					},
					'amount' : {
						'before' : {
							'l30' : {'T1':0,'T2':0,'T3':0,'T4':0,'T5':0,'T6':0,'T7':0,'T8':0,'T9':0,'T10':0,'T11':0,'T12':0},
							'p30' : {'T1':0,'T2':0,'T3':0,'T4':0,'T5':0,'T6':0,'T7':0,'T8':0,'T9':0,'T10':0,'T11':0,'T12':0},
							'p60' : {'T1':0,'T2':0,'T3':0,'T4':0,'T5':0,'T6':0,'T7':0,'T8':0,'T9':0,'T10':0,'T11':0,'T12':0},
							'p90' : {'T1':0,'T2':0,'T3':0,'T4':0,'T5':0,'T6':0,'T7':0,'T8':0,'T9':0,'T10':0,'T11':0,'T12':0},
							'p180' : {'T1':0,'T2':0,'T3':0,'T4':0,'T5':0,'T6':0,'T7':0,'T8':0,'T9':0,'T10':0,'T11':0,'T12':0},
							'subtotal':{'T1':0,'T2':0,'T3':0,'T4':0,'T5':0,'T6':0,'T7':0,'T8':0,'T9':0,'T10':0,'T11':0,'T12':0},
						},
						'writeof' : {
							'p360' : {'T1':0,'T2':0,'T3':0,'T4':0,'T5':0,'T6':0,'T7':0,'T8':0,'T9':0,'T10':0,'T11':0,'T12':0},
						}
					}
				},
				'year' : str(year)
			})
	#SHEET AMOUNT------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
   # lấy dữ liệu theo từng account đã tính DPD và tính payment trong ln3206f và gl2018 đưa vào bảng tạm
   pipeline_outsoucing = [
      {
         '$match' : {
            '$or': [{'created_at': { '$gte' :  startDayTimeStamp,'$lte' : endDayTimeStamp}}, {'updated_at': { '$gte' :  startDayTimeStamp,'$lte' : endDayTimeStamp}}]
         }
      },
      {
         '$lookup' : {
            'from' : collection_lnjc05,
            'localField' : 'CONTRACTNR',
            'foreignField' : 'account_number',
            'as' : 'lnjc05'
         }
      },
      {
         '$unwind' : {
            'path' : '$lnjc05',
            'preserveNullAndEmptyArrays' : True
         }
      },
      {
         '$lookup' : {
            'from' : collection_listofAccount,
            'localField' : 'CONTRACTNR',
            'foreignField' : 'account_number',
            'as' : 'listofAccount'
         }
      },
      {
         '$unwind' : {
            'path' : '$listofAccount',
            'preserveNullAndEmptyArrays' : True
         }
      }
   ]
   results_outsoucing = mongodb.aggregate_pipeline(MONGO_COLLECTION=collection,aggregate_pipeline=pipeline_outsoucing)
   if results_outsoucing != None:
      for idx,row_result in enumerate(results_outsoucing):
         count_account  = 0
         payment        = 0
         firstDayOfMonth = datetime(year, month, 1, 0, 0)
         if "lnjc05" in row_result:
            due_date    = datetime.fromtimestamp(row_result['lnjc05']['due_date'])
            os_balance  = row_result['lnjc05']['current_balance']
            check_ln3206f = mongodb.count(MONGO_COLLECTION=collection_ln3206f, WHERE={'account_number':str(row_result['CONTRACTNR']), 'code':'10'})
            # check_ln3206f = mongodb.count(MONGO_COLLECTION=collection_ln3206f, WHERE={'account_number':str(row_result['CONTRACTNR']), 'code':'10', 'created_at': { '$gte' :  startDayTimeStamp + 86400,'$lte' : endDayTimeStamp + 86400}})
            if check_ln3206f > 0:
               get_ln3206f = mongodb.get(MONGO_COLLECTION=collection_ln3206f, WHERE={'account_number':str(row_result['CONTRACTNR']), 'code':'10'})
               # get_ln3206f = mongodb.get(MONGO_COLLECTION=collection_ln3206f, WHERE={'account_number':str(row_result['CONTRACTNR']), 'code':'10', 'created_at': { '$gte' :  startDayTimeStamp + 86400,'$lte' : endDayTimeStamp + 86400}})
               print('SIBS: '+str(idx))
               if get_ln3206f != None:
                  count_account  = 1
                  for row_3026 in get_ln3206f:
                     payment         += row_3026['amt']

         elif "listofAccount" in row_result:
            due_date    = datetime.fromtimestamp(row_result['listofAccount']['overdue_date'])
            os_balance  =  row_result['listofAccount']['cur_bal']
            check_gl    = mongodb.count(MONGO_COLLECTION=collection_gl_2018, WHERE={'$and' : [{'account_number':str(row_result['CONTRACTNR'])}, {'$or' : [{'code': '2000'}, {'code': '2100'}, {'code': '2700'}]}]})
            # check_gl    = mongodb.count(MONGO_COLLECTION=collection_gl_2018, WHERE={'$and' : [{'created_at': { '$gte' :  startDayTimeStamp + 86400,'$lte' : endDayTimeStamp + 86400}},{'account_number':str(row_result['CONTRACTNR'])}, {'$or' : [{'code': '2000'}, {'code': '2100'}, {'code': '2700'}]}]})
            if check_gl > 0:
               get_gl_2018 = mongodb.get(MONGO_COLLECTION=collection_gl_2018, WHERE={'$and' : [ {'account_number':str(row_result['CONTRACTNR'])}, {'$or' : [{'code': '2000'}, {'code': '2100'}, {'code': '2700'}]}]})
               # get_gl_2018 = mongodb.get(MONGO_COLLECTION=collection_gl_2018, WHERE={'$and' : [{'created_at': { '$gte' :  startDayTimeStamp + 86400,'$lte' : endDayTimeStamp + 86400}}, {'account_number':str(row_result['CONTRACTNR'])}, {'$or' : [{'code': '2000'}, {'code': '2100'}, {'code': '2700'}]}]})
               print('Card: '+str(idx))
               code_2000 = False
               code_2100 = False
               code_2700 = False
               code_amount_2000 = 0
               code_amount_2100 = 0
               code_amount_2700 = 0
               if get_gl_2018 != None:
                  for row_code in get_gl_2018:
                     if row_code['code'] == '2000':
                        code_2000 = True
                        code_amount_2000 += row_code['amount']
                     if row_code['code'] == '2100':
                        code_2100 = True
                        code_amount_2100 += row_code['amount']
                     if row_code['code'] == '2700':
                        code_2700 = True
                        code_amount_2700 += row_code['amount']
               if code_2000 and code_2100 and code_2700 and (code_amount_2000+code_amount_2100-code_amount_2700)>0:
                  count_account = 1
                  payment = code_amount_2000+code_amount_2100-code_amount_2700
         else: continue

         subdate = firstDayOfMonth - due_date
         temp_DPD = ''
         if(subdate.days<30):
            temp_DPD = '<30'
         elif(subdate.days>=30 and subdate.days < 60):
            temp_DPD = '30+'
         elif(subdate.days>=60 and subdate.days < 90):
            temp_DPD = '60+'
         elif(subdate.days>=90 and subdate.days < 180):
            temp_DPD = '90+'
         elif(subdate.days>=180 and subdate.days < 360):
            temp_DPD = '180+'
         else:
            temp_DPD = '360+'

         temp = {
            'account_number' : row_result['CONTRACTNR'],
            'amount'         : os_balance,
            'COMPANY'        : row_result['COMPANY'],
            'DPD'            : temp_DPD,
            'count_account'  : count_account,
            'payment'        : payment
         }
         insertData.append(temp)



   if len(insertData) > 0:
      mongodb.batch_insert(MONGO_COLLECTION=collection_temp, insert_data=insertData)



   #1.OUTSOURCING
   # đọc dữ liệu từ bảng tạm ra
   print('OUTSOURCING SHEET AMOUNT')
   if partner_lists != None:
      for row_partner in partner_lists:
         where = {'partner':row_partner, 'year' : {'$exists' : True, '$eq' : str(year)} }
         aggregate_pipeline = [
            {
               '$match' : {
                  'COMPANY': row_partner
               }
            },
            {
               "$group":
               {
                  "_id": '$DPD',
                  "sum_acc": {'$sum': 1},
                  "sum_amount": {'$sum': '$amount'},
               }
            }
         ]
         data = mongodb.aggregate_pipeline(MONGO_COLLECTION=collection_temp,aggregate_pipeline=aggregate_pipeline)
         if data != None:
            for row_result in data:
               if(row_result['_id'] == '<30'):
                  modified = mongodb.update(MONGO_COLLECTION=collection_amount_report,WHERE=where,VALUE={'outsoucing.account.before.l30.T'+str(month) :row_result['sum_acc'], 'outsoucing.amount.before.l30.T'+str(month) : row_result['sum_amount']})
               elif(row_result['_id'] == '30+'):
                  modified = mongodb.update(MONGO_COLLECTION=collection_amount_report,WHERE=where,VALUE={'outsoucing.account.before.l30.T'+str(month) :row_result['sum_acc'], 'outsoucing.amount.before.l30.T'+str(month) : row_result['sum_amount']})
               elif(row_result['_id'] == '60+'):
                  modified = mongodb.update(MONGO_COLLECTION=collection_amount_report,WHERE=where,VALUE={'outsoucing.account.before.p60.T'+str(month) :row_result['sum_acc'], 'outsoucing.amount.before.p60.T'+str(month) : row_result['sum_amount']})
               elif(row_result['_id'] == '90+'):
                  modified = mongodb.update(MONGO_COLLECTION=collection_amount_report,WHERE=where,VALUE={'outsoucing.account.before.p90.T'+str(month) :row_result['sum_acc'], 'outsoucing.amount.before.p90.T'+str(month) : row_result['sum_amount']})
               elif(row_result['_id'] == '180+'):
                  modified = mongodb.update(MONGO_COLLECTION=collection_amount_report,WHERE=where,VALUE={'outsoucing.account.before.p180.T'+str(month):row_result['sum_acc'], 'outsoucing.amount.before.p180.T'+str(month) : row_result['sum_amount']})
               else: continue


         # tinh subtotal
         aggregate_pipeline = [
            {
               '$match' : {
                  'COMPANY': row_partner,
                  'DPD' : {'$nin': ['360+']}
               }
            },
            {
               "$group":
               {
                  "_id": 'null',
                  "sum_acc": {'$sum': 1},
                  "sum_amount": {'$sum': '$amount'},
               }
            }
         ]
         data = mongodb.aggregate_pipeline(MONGO_COLLECTION=collection_temp,aggregate_pipeline=aggregate_pipeline)
         if data != None:
            for row_result in data:
               modified = mongodb.update(MONGO_COLLECTION=collection_amount_report,WHERE=where,VALUE={'outsoucing.account.before.subtotal.T'+str(month) :row_result['sum_acc'], 'outsoucing.amount.before.subtotal.T'+str(month) : row_result['sum_amount']})



   #2.COLLECTED
   print('COLLECTED SHEET AMOUNT')
   if partner_lists != None:
      for row_partner in partner_lists:
         where = {'partner':row_partner, 'year' : {'$exists' : True, '$eq' : str(year)} }
         aggregate_pipeline = [
            {
               '$match' : {
                  'COMPANY': row_partner
               }
            },
            {
               "$group":
               {
                  "_id": '$DPD',
                  "sum_acc": {'$sum': '$count_account'},
                  "sum_amount": {'$sum': '$payment'},
               }
            }
         ]
         data = mongodb.aggregate_pipeline(MONGO_COLLECTION=collection_temp,aggregate_pipeline=aggregate_pipeline)
         if data != None:
            for row_result in data:
               if(row_result['_id'] == '<30'):
                  modified = mongodb.update(MONGO_COLLECTION=collection_amount_report,WHERE=where,VALUE={'collected.account.before.l30.T'+str(lastMonth) :row_result['sum_acc'], 'collected.amount.before.l30.T'+str(lastMonth) : row_result['sum_amount']})
               elif(row_result['_id'] == '30+'):
                  modified = mongodb.update(MONGO_COLLECTION=collection_amount_report,WHERE=where,VALUE={'collected.account.before.l30.T'+str(lastMonth) :row_result['sum_acc'], 'collected.amount.before.l30.T'+str(lastMonth) : row_result['sum_amount']})
               elif(row_result['_id'] == '60+'):
                  modified = mongodb.update(MONGO_COLLECTION=collection_amount_report,WHERE=where,VALUE={'collected.account.before.p60.T'+str(lastMonth) :row_result['sum_acc'], 'collected.amount.before.p60.T'+str(lastMonth) : row_result['sum_amount']})
               elif(row_result['_id'] == '90+'):
                  modified = mongodb.update(MONGO_COLLECTION=collection_amount_report,WHERE=where,VALUE={'collected.account.before.p90.T'+str(lastMonth) :row_result['sum_acc'], 'collected.amount.before.p90.T'+str(lastMonth) : row_result['sum_amount']})
               elif(row_result['_id'] == '180+'):
                  modified = mongodb.update(MONGO_COLLECTION=collection_amount_report,WHERE=where,VALUE={'collected.account.before.p180.T'+str(lastMonth):row_result['sum_acc'], 'collected.amount.before.p180.T'+str(lastMonth) : row_result['sum_amount']})
               else: continue

         # tinh subtotal
         aggregate_pipeline = [
            {
               '$match' : {
                  'COMPANY': row_partner,
                  'DPD' : {'$nin': ['360+']}
               }
            },
            {
               "$group":
               {
                  "_id": 'null',
                  "sum_acc": {'$sum': '$count_account'},
                  "sum_amount": {'$sum': '$payment'},
               }
            }
         ]
         data = mongodb.aggregate_pipeline(MONGO_COLLECTION=collection_temp,aggregate_pipeline=aggregate_pipeline)
         if data != None:
            for row_result in data:
               modified = mongodb.update(MONGO_COLLECTION=collection_amount_report,WHERE=where,VALUE={'collected.account.before.subtotal.T'+str(lastMonth) :row_result['sum_acc'], 'collected.amount.before.subtotal.T'+str(lastMonth) : row_result['sum_amount']})


   # xóa dữ liệu bảng tạm
   mongodb.remove_document(MONGO_COLLECTION=collection_temp)




   #SHEET ASSIGNED DPD------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	#1.OUTSOURCING
   print('OUTSOURCING SHEET ASSIGNED DPD')
   if partner_lists != None:
      for row_partner in partner_lists:
         where = {'partner':row_partner, 'year' : {'$exists' : True, '$eq' : str(year)} }
         aggregate_pipeline = [
				{
					'$match' : {
						'$or': [{'created_at': { '$gte' :  startDayTimeStamp,'$lte' : endDayTimeStamp}}, {'updated_at': { '$gte' :  startDayTimeStamp,'$lte' : endDayTimeStamp}}],
						'COMPANY': row_partner
					}
				},
				{
				 	"$group":
				 	{
				    	"_id": '$DPD',
		        		"sum_acc": {'$sum': 1},
		        		"sum_amount": {'$sum': '$CURRENT_DEBT'},
				 	}
				}

			]
         data = mongodb.aggregate_pipeline(MONGO_COLLECTION=collection,aggregate_pipeline=aggregate_pipeline)
         if data != None:
            total_account_p60 = 0
            total_amount_p60 = 0
            for row_result in data:
               if(row_result['_id'] == '<30'):
                  modified = mongodb.update(MONGO_COLLECTION=collection_assigned_report,WHERE=where,VALUE={'outsoucing.account.before.l30.T'+str(month) :row_result['sum_acc'], 'outsoucing.amount.before.l30.T'+str(month) : row_result['sum_amount']})
               elif(row_result['_id'] == '30+' or row_result['_id'] == '<60'):
                  total_account_p60 	+= row_result['sum_acc']
                  total_amount_p60 	+= row_result['sum_amount']
               elif(row_result['_id'] == '60+'):
                  modified = mongodb.update(MONGO_COLLECTION=collection_assigned_report,WHERE=where,VALUE={'outsoucing.account.before.p60.T'+str(month) :row_result['sum_acc'], 'outsoucing.amount.before.p60.T'+str(month) : row_result['sum_amount']})
               elif(row_result['_id'] == '90+'):
                  modified = mongodb.update(MONGO_COLLECTION=collection_assigned_report,WHERE=where,VALUE={'outsoucing.account.before.p90.T'+str(month) :row_result['sum_acc'], 'outsoucing.amount.before.p90.T'+str(month) : row_result['sum_amount']})
               elif(row_result['_id'] == '180+'):
                  modified = mongodb.update(MONGO_COLLECTION=collection_assigned_report,WHERE=where,VALUE={'outsoucing.account.before.p180.T'+str(month):row_result['sum_acc'], 'outsoucing.amount.before.p180.T'+str(month) : row_result['sum_amount']})
               else: continue

            modified = mongodb.update(MONGO_COLLECTION=collection_assigned_report,WHERE=where,VALUE={'outsoucing.account.before.p30.T'+str(month) :total_account_p60, 'outsoucing.amount.before.p30.T'+str(month) : total_amount_p60})


         # tinh subtotal
         aggregate_pipeline = [
            {
               '$match' : {
                  '$or': [{'created_at': { '$gte' :  startDayTimeStamp,'$lte' : endDayTimeStamp}}, {'updated_at': { '$gte' :  startDayTimeStamp,'$lte' : endDayTimeStamp}}],
                  'COMPANY': row_partner,
                  'DPD' : {'$nin': ['360+']}
               }
            },
            {
               "$group":
               {
                  "_id": 'null',
                  "sum_acc": {'$sum': 1},
                  "sum_amount": {'$sum': '$CURRENT_DEBT'},
               }
            }
         ]
         data = mongodb.aggregate_pipeline(MONGO_COLLECTION=collection,aggregate_pipeline=aggregate_pipeline)
         if data != None:
            for row_result in data:
               modified = mongodb.update(MONGO_COLLECTION=collection_assigned_report,WHERE=where,VALUE={'outsoucing.account.before.subtotal.T'+str(month) :row_result['sum_acc'], 'outsoucing.amount.before.subtotal.T'+str(month) : row_result['sum_amount']})




	#2.COLLECTED
   print('COLLECTED SHEET ASSIGNED DPD')
   if partner_lists != None:
      for row_partner in partner_lists:
         subtotal_account   = 0
         subtotal_amount    = 0
         where = {'partner':row_partner, 'year' : {'$exists' : True, '$eq' : str(year)} }
         aggregate_pipeline = [
				{
					'$match' : {
						'$or': [{'created_at': { '$gte' :  startDayTimeStamp,'$lte' : endDayTimeStamp}}, {'updated_at': { '$gte' :  startDayTimeStamp,'$lte' : endDayTimeStamp}}],
						'COMPANY': row_partner
					}
				},
				{
				 	"$group":
				 	{
				    	"_id": '$DPD',
		        		"acc_arr": {'$push': '$CONTRACTNR'},
				 	}
				}

			]
         data = mongodb.aggregate_pipeline(MONGO_COLLECTION=collection,aggregate_pipeline=aggregate_pipeline)
         if data != None:
            total_account_p60 = 0
            total_amount_p60 = 0
            for row_result in data:
               account = 0
               amount = 0
               aggregate_payment = [
						{
							'$match' : {
								# 'created_at': { '$gte' :  startDayTimeStamp + 86400,'$lte' : endDayTimeStamp + 86400},
								'account_number': {'$in': row_result['acc_arr']},
								'code':'10'
							}
						},
						{
						 	"$group":
						 	{
						    	"_id": 'null',
				        		"sum_amount": {'$sum': '$amt'},
				        		"sum_account": {'$sum': 1},
						 	}
						}

					]
               dataLn3206f = mongodb.aggregate_pipeline(MONGO_COLLECTION=collection_ln3206f,aggregate_pipeline=aggregate_payment)
               if dataLn3206f != None:
                  for row in dataLn3206f:
                     account  = row['sum_account']
                     amount   = row['sum_amount']
                     subtotal_account   += row['sum_account']
                     subtotal_amount    += row['sum_amount']
               if account == 0:
                  for acc in row_result['acc_arr']:
                     check_gl    = mongodb.count(MONGO_COLLECTION=collection_gl_2018, WHERE={'$and' : [{'account_number':str(acc)}, {'$or' : [{'code': '2000'}, {'code': '2100'}, {'code': '2700'}]}]})
                     # check_gl    = mongodb.count(MONGO_COLLECTION=collection_gl_2018, WHERE={'$and' : [{'created_at': { '$gte' :  startDayTimeStamp + 86400,'$lte' : endDayTimeStamp + 86400}},{'account_number':str(acc)}, {'$or' : [{'code': '2000'}, {'code': '2100'}, {'code': '2700'}]}]})
                     if check_gl > 0:
                        get_gl_2018 = mongodb.get(MONGO_COLLECTION=collection_gl_2018, WHERE={'$and' : [ {'account_number':acc}, {'$or' : [{'code': '2000'}, {'code': '2100'}, {'code': '2700'}]}]})
                        # get_gl_2018 = mongodb.get(MONGO_COLLECTION=collection_gl_2018, WHERE={'$and' : [{'created_at': { '$gte' :  startDayTimeStamp + 86400,'$lte' : endDayTimeStamp + 86400}}, {'account_number':acc}, {'$or' : [{'code': '2000'}, {'code': '2100'}, {'code': '2700'}]}]})
                        code_2000 = False
                        code_2100 = False
                        code_2700 = False
                        code_amount_2000 = 0
                        code_amount_2100 = 0
                        code_amount_2700 = 0
                        for row_code in get_gl_2018:
                           if row_code['code'] == '2000':
                              code_2000 = True
                              code_amount_2000 = row_code['amount']
                           if row_code['code'] == '2100':
                              code_2100 = True
                              code_amount_2100 = row_code['amount']
                           if row_code['code'] == '2700':
                              code_2700 = True
                              code_amount_2700 = row_code['amount']
                        if code_2000 and code_2100 and code_2700 and (code_amount_2000+code_amount_2100-code_amount_2700)>0:
                           account += 1
                           amount 	+= code_amount_2000+code_amount_2100-code_amount_2700
                           subtotal_account += 1
                           subtotal_amount += amount

               if(row_result['_id'] == '<30'):
                  modified = mongodb.update(MONGO_COLLECTION=collection_assigned_report,WHERE=where,VALUE={'collected.account.before.l30.T'+str(lastMonth) :account, 'collected.amount.before.l30.T'+str(lastMonth) : amount})
               elif(row_result['_id'] == '30+' or row_result['_id'] == '<60'):
                  total_account_p60 += account
                  total_amount_p60 += amount
               elif(row_result['_id'] == '60+'):
                  modified = mongodb.update(MONGO_COLLECTION=collection_assigned_report,WHERE=where,VALUE={'collected.account.before.p60.T'+str(lastMonth) :account, 'collected.amount.before.p60.T'+str(lastMonth) : amount})
               elif(row_result['_id'] == '90+'):
                  modified = mongodb.update(MONGO_COLLECTION=collection_assigned_report,WHERE=where,VALUE={'collected.account.before.p90.T'+str(lastMonth) :account, 'collected.amount.before.p90.T'+str(lastMonth) : amount})
               elif(row_result['_id'] == '180+'):
                  modified = mongodb.update(MONGO_COLLECTION=collection_assigned_report,WHERE=where,VALUE={'collected.account.before.p180.T'+str(lastMonth):account, 'collected.amount.before.p180.T'+str(lastMonth) : amount})
               else: continue

            modified = mongodb.update(MONGO_COLLECTION=collection_assigned_report,WHERE=where,VALUE={'collected.account.before.p30.T'+str(lastMonth) :total_account_p60, 'collected.amount.before.p30.T'+str(lastMonth) : total_amount_p60})


         # tinh subtotal
         modified = mongodb.update(MONGO_COLLECTION=collection_assigned_report,WHERE=where,VALUE={'collected.account.before.subtotal.T'+str(lastMonth) :subtotal_account, 'collected.amount.before.subtotal.T'+str(lastMonth) : subtotal_amount})

   print('DONE')

except Exception as ex :
	pprint(ex)
	log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(ex) + '\n')
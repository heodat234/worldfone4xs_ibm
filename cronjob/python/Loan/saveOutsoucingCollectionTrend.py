#!/usr/bin/python3.6
# -*- coding: utf-8 -*-

import re
import ftplib
import calendar
import time
import sys
import os
import json
from pprint import pprint
from datetime import datetime
from datetime import date, timedelta
from bson import ObjectId
from helper.ftp import Ftp
from helper.mongod import Mongodb
from helper.excel import Excel
from helper.jaccs import Config
from helper.common import Common
from helper.mongodbaggregate import Mongodbaggregate

common      = Common()
base_url    = common.base_url()
wff_env     = common.wff_env(base_url)
mongodb = Mongodb(MONGODB="worldfone4xs",WFF_ENV=wff_env)
_mongodb = Mongodb(MONGODB="_worldfone4xs",WFF_ENV=wff_env)
excel = Excel()
config = Config()
ftp = Ftp()
common = Common()
mongodbaggregate = Mongodbaggregate("worldfone4xs")
# base_url = config.base_url()
log = open(base_url + "cronjob/python/Loan/log/saveOutsoucingCollectionTrend.txt","a")
now = datetime.now()
subUserType = 'LO'
collection = common.getSubUser(subUserType, 'Cus_assigned_partner')
collection_lnjc05 = common.getSubUser(subUserType, 'LNJC05')
collection_listofAccount = common.getSubUser(subUserType, 'List_of_account_in_collection')

collection_ln3206f =  common.getSubUser(subUserType, 'LN3206F')
collection_gl_2018 =  common.getSubUser(subUserType, 'Report_input_payment_of_card') 

collection_amount_report = common.getSubUser(subUserType, 'Outsoucing_Collection_Trend_Amount_Report')
collection_assigned_report = common.getSubUser(subUserType, 'Outsoucing_Collection_Trend_AssignDPD_Report')
try :
	today = date.today()
	# today = datetime.strptime('26/03/2020', "%d/%m/%Y").date()
	day = today.day
	month = today.month
	year = today.year
	weekday = today.weekday()
	lastDayOfMonth = calendar.monthrange(year, month)[1]

	todayString = today.strftime("%d/%m/%Y")
	todayTimeStamp = int(time.mktime(time.strptime(str(todayString + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
	# todayString = "26/03/2020"
	startday = int(time.mktime(time.strptime(str(todayString + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
	endday = int(time.mktime(time.strptime(str(todayString + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))
	
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
	#1.OUTSOURCING
	# if day == 1 :

	# pipeline_count = [
	# 	{
	# 		'$match' : {
	# 			'created_at' : { '$gte' :  startday,'$lt' : endday}
	# 		}
	# 	},
	# 	{
	# 		'$lookup' : {
	# 			'from' : collection_lnjc05,
	# 			'localField' : 'CONTRACTNR',
	# 			'foreignField' : 'account_number',
	# 			'as' : 'lnjc05'
	# 		}
	# 	},
	# 	{
	# 		'$unwind' : {
	# 			'path' : '$lnjc05',
	# 			'preserveNullAndEmptyArrays' : True
	# 		}
	# 	},
	# 	{
	# 		'$lookup' : {
	# 			'from' : collection_listofAccount,
	# 			'localField' : 'CONTRACTNR',
	# 			'foreignField' : 'account_number',
	# 			'as' : 'listofAccount'
	# 		}
	# 	},
	# 	{
	# 		'$unwind' : {
	# 			'path' : '$listofAccount',
	# 			'preserveNullAndEmptyArrays' : True
	# 		}
	# 	}		
	# ]
	# pipeline_count.append({'$count' : 'passing'})
	# count_outsoucing = mongodb.aggregate_pipeline(MONGO_COLLECTION=collection,aggregate_pipeline=pipeline_count)
	# count = 0
	# if count_outsoucing != None:
	# 	for row_count in count_outsoucing:
	# 		count = row_count['passing']
	# 		break
			
	# for i in range(count):	
	# 	pipeline_outsoucing = [
	# 		{
	# 			'$match' : {
	# 				'created_at' : { '$gte' :  startday,'$lt' : endday}
	# 			}
	# 		},
	# 		{
	# 			'$lookup' : {
	# 				'from' : collection_lnjc05,
	# 				'localField' : 'CONTRACTNR',
	# 				'foreignField' : 'account_number',
	# 				'as' : 'lnjc05'
	# 			}
	# 		},
	# 		{
	# 			'$unwind' : {
	# 				'path' : '$lnjc05',
	# 				'preserveNullAndEmptyArrays' : True
	# 			}
	# 		},
	# 		{
	# 			'$lookup' : {
	# 				'from' : collection_listofAccount,
	# 				'localField' : 'CONTRACTNR',
	# 				'foreignField' : 'account_number',
	# 				'as' : 'listofAccount'
	# 			}
	# 		},
	# 		{
	# 			'$unwind' : {
	# 				'path' : '$listofAccount',
	# 				'preserveNullAndEmptyArrays' : True
	# 			}
	# 		}		
	# 	]
	# 	pipeline_outsoucing.append({'$sort' : { '_id' : 1 }})
	# 	pipeline_outsoucing.append({'$skip' : i * 1000})
	# 	pipeline_outsoucing.append({'$limit' : 1000})		
		
	# 	results_outsoucing = mongodb.aggregate_pipeline(MONGO_COLLECTION=collection,aggregate_pipeline=pipeline_outsoucing)
	# 	if results_outsoucing != None:
	# 		for row_result in results_outsoucing:
	# 			firstDayOfMonth = datetime(year, month, 1, 0, 0)	
	# 			if "lnjc05" in row_result:
	# 				due_date = datetime.fromtimestamp(row_result['lnjc05']['due_date'])
	# 				os_balance = row_result['lnjc05']['current_balance']
	# 			elif "listofAccount" in row_result:
	# 				due_date = datetime.fromtimestamp(row_result['listofAccount']['overdue_date'])
	# 				os_balance =  row_result['listofAccount']['cur_bal']
	# 			else : continue
	# 			subdate = firstDayOfMonth - due_date
				
	# 			where = {'partner':row_result['COMPANY'], 'year' : {'$exists' : True, '$eq' : str(year)} }
	# 			if(subdate.days<30):
	# 				modified = mongodb.inc(MONGO_COLLECTION=collection_amount_report,WHERE=where,VALUE={'outsoucing.account.before.l30.T'+str(month) :1, 'outsoucing.amount.before.l30.T'+str(month) : os_balance})
	# 			elif(subdate.days>=30 and subdate.days < 60):
	# 				modified = mongodb.inc(MONGO_COLLECTION=collection_amount_report,WHERE=where,VALUE={'outsoucing.account.before.p30.T'+str(month) :1, 'outsoucing.amount.before.p30.T'+str(month) : os_balance})
	# 			elif(subdate.days>=60 and subdate.days < 90):
	# 				modified = mongodb.inc(MONGO_COLLECTION=collection_amount_report,WHERE=where,VALUE={'outsoucing.account.before.p60.T'+str(month) :1, 'outsoucing.amount.before.p60.T'+str(month) : os_balance})
	# 			elif(subdate.days>=90 and subdate.days < 180):
	# 				modified = mongodb.inc(MONGO_COLLECTION=collection_amount_report,WHERE=where,VALUE={'outsoucing.account.before.p90.T'+str(month) :1, 'outsoucing.amount.before.p90.T'+str(month) : os_balance})
	# 			elif(subdate.days>=180 and subdate.days < 360):
	# 				modified = mongodb.inc(MONGO_COLLECTION=collection_amount_report,WHERE=where,VALUE={'outsoucing.account.before.p180.T'+str(month):1, 'outsoucing.amount.before.p180.T'+str(month) : os_balance})
	# 			else: continue
	# #2.COLLECTED
	# pipeline_count = [
	# 	{
	# 		'$match' : {
	# 			'created_at' : { '$gte' :  startday,'$lt' : endday}
	# 		}
	# 	},
	# 	{
	# 		'$lookup' : {
	# 			'from' : collection_lnjc05,
	# 			'localField' : 'CONTRACTNR',
	# 			'foreignField' : 'account_number',
	# 			'as' : 'lnjc05'
	# 		}
	# 	},
	# 	{
	# 		'$unwind' : {
	# 			'path' : '$lnjc05',
	# 			'preserveNullAndEmptyArrays' : True
	# 		}
	# 	},
	# 	{
	# 		'$lookup' : {
	# 			'from' : collection_listofAccount,
	# 			'localField' : 'CONTRACTNR',
	# 			'foreignField' : 'account_number',
	# 			'as' : 'listofAccount'
	# 		}
	# 	},
	# 	{
	# 		'$unwind' : {
	# 			'path' : '$listofAccount',
	# 			'preserveNullAndEmptyArrays' : True
	# 		}
	# 	}		
	# ]
	# pipeline_count.append({'$count' : 'passing'})
	# count_outsoucing = mongodb.aggregate_pipeline(MONGO_COLLECTION=collection,aggregate_pipeline=pipeline_count)
	# count = 0
	# if count_outsoucing != None:
	# 	for row_count in count_outsoucing:
	# 		count = row_count['passing']
	# 		break
			
	# for i in range(count):	
	# 	pipeline_outsoucing = [
	# 		{
	# 			'$match' : {
	# 				'created_at' : { '$gte' :  startday,'$lt' : endday}
	# 			}
	# 		},
	# 		{
	# 			'$lookup' : {
	# 				'from' : collection_lnjc05,
	# 				'localField' : 'CONTRACTNR',
	# 				'foreignField' : 'account_number',
	# 				'as' : 'lnjc05'
	# 			}
	# 		},
	# 		{
	# 			'$unwind' : {
	# 				'path' : '$lnjc05',
	# 				'preserveNullAndEmptyArrays' : True
	# 			}
	# 		},
	# 		{
	# 			'$lookup' : {
	# 				'from' : collection_listofAccount,
	# 				'localField' : 'CONTRACTNR',
	# 				'foreignField' : 'account_number',
	# 				'as' : 'listofAccount'
	# 			}
	# 		},
	# 		{
	# 			'$unwind' : {
	# 				'path' : '$listofAccount',
	# 				'preserveNullAndEmptyArrays' : True
	# 			}
	# 		}		
	# 	]
	# 	pipeline_outsoucing.append({'$sort' : { '_id' : 1 }})
	# 	pipeline_outsoucing.append({'$skip' : i * 1000})
	# 	pipeline_outsoucing.append({'$limit' : 1000})
		
	# 	results_outsoucing = mongodb.aggregate_pipeline(MONGO_COLLECTION=collection,aggregate_pipeline=pipeline_outsoucing)
	# 	if results_outsoucing != None:
	# 		for row_result in results_outsoucing:
	# 			account_number = row_result['CONTRACTNR']
	# 			firstDayOfMonth = datetime(year, month, 1, 0, 0)	
	# 			if "lnjc05" in row_result:
	# 				due_date = datetime.fromtimestamp(row_result['lnjc05']['due_date'])						
	# 			elif "listofAccount" in row_result:
	# 				due_date = datetime.fromtimestamp(row_result['listofAccount']['overdue_date'])
	# 			else : continue
	# 			subdate = firstDayOfMonth - due_date	
	# 			get_ln3206f = mongodb.getOne(MONGO_COLLECTION=collection_ln3206f, WHERE={'account_number':account_number, 'code':'10', 'created_at' : { '$gte' :  startday,'$lt' : endday}})
	# 			plus = 0
	# 			amount = 0
	# 			if get_ln3206f != None:
	# 				plus = 1
	# 				amount = get_ln3206f['amt']
	# 			else:
	# 				get_gl_2018 = mongodb.get(MONGO_COLLECTION=collection_gl_2018, WHERE={'$and' : [{'created_at' : { '$gte' :  startday,'$lt' : endday}}, {'account_number':account_number}, {'$or' : [{'code': '2000'}, {'code': '2100'}, {'code': '2700'}]}]})
	# 				if get_ln3206f != None:
	# 					code_2000 = False
	# 					code_2100 = False
	# 					code_2700 = False
	# 					code_amount_2000 = 0
	# 					code_amount_2100 = 0
	# 					code_amount_2700 = 0
	# 					for row_code in get_gl_2018:
	# 						if row_code['code'] == '2000':
	# 							code_2000 = True
	# 							code_amount_2000 = row_code['amount']
	# 						if row_code['code'] == '2100':
	# 							code_2100 = True
	# 							code_amount_2100 = row_code['amount']
	# 						if row_code['code'] == '2700':
	# 							code_2700 = True
	# 							code_amount_2700 = row_code['amount']
	# 					if code_2000 and code_2100 and code_2700 and (code_amount_2000+code_amount_2100-code_amount_2700)>0:
	# 						plus = 1
	# 						amount = code_amount_2000+code_amount_2100-code_amount_2700
				
	# 			where = {'partner':row_result['COMPANY'], 'year' : {'$exists' : True, '$eq' : str(year)} }
	# 			if(subdate.days<30):
	# 				modified = mongodb.inc(MONGO_COLLECTION=collection_amount_report,WHERE=where,VALUE={'collected.account.before.l30.T'+str(lastMonth) :plus, 'collected.amount.before.l30.T'+str(lastMonth) : amount})
	# 			elif(subdate.days>=30 and subdate.days < 60):
	# 				modified = mongodb.inc(MONGO_COLLECTION=collection_amount_report,WHERE=where,VALUE={'collected.account.before.p30.T'+str(lastMonth) :plus, 'collected.amount.before.p30.T'+str(lastMonth) : amount})
	# 			elif(subdate.days>=60 and subdate.days < 90):
	# 				modified = mongodb.inc(MONGO_COLLECTION=collection_amount_report,WHERE=where,VALUE={'collected.account.before.p60.T'+str(lastMonth) :plus, 'collected.amount.before.p60.T'+str(lastMonth) : amount})
	# 			elif(subdate.days>=90 and subdate.days < 180):
	# 				modified = mongodb.inc(MONGO_COLLECTION=collection_amount_report,WHERE=where,VALUE={'collected.account.before.p90.T'+str(lastMonth) :plus, 'collected.amount.before.p90.T'+str(lastMonth) : amount})
	# 			elif(subdate.days>=180 and subdate.days < 360):
	# 				modified = mongodb.inc(MONGO_COLLECTION=collection_amount_report,WHERE=where,VALUE={'collected.account.before.p180.T'+str(lastMonth):plus, 'collected.amount.before.p180.T'+str(lastMonth) : amount})
	# 			else: continue

	#SHEET ASSIGNED DPD------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	#1.OUTSOURCING
	# if day == 1 :
	pipeline_count = [
		{
			'$match' : {
				'created_at' : { '$gte' :  startday,'$lt' : endday}
			}
		}	
	]
	pipeline_count.append({'$count' : 'passing'})
	count_outsoucing = mongodb.aggregate_pipeline(MONGO_COLLECTION=collection,aggregate_pipeline=pipeline_count)
	count = 0
	if count_outsoucing != None:
		for row_count in count_outsoucing:
			count = row_count['passing']
			break
			
	for i in range(count):	
		pipeline_outsoucing = [
			{
				'$match' : {
					'created_at' : { '$gte' :  startday,'$lt' : endday}
				}
			}		
		]
		pipeline_outsoucing.append({'$sort' : { '_id' : 1 }})
		pipeline_outsoucing.append({'$skip' : i * 1000})
		pipeline_outsoucing.append({'$limit' : 1000})		
		
		results_outsoucing = mongodb.aggregate_pipeline(MONGO_COLLECTION=collection,aggregate_pipeline=pipeline_outsoucing)
		if results_outsoucing != None:
			for row_result in results_outsoucing:
				where = {'partner':row_result['COMPANY'], 'year' : {'$exists' : True, '$eq' : str(year)} }
				if(row_result['DPD'] == '<30'):
					modified = mongodb.inc(MONGO_COLLECTION=collection_assigned_report,WHERE=where,VALUE={'outsoucing.account.before.l30.T'+str(month) :1, 'outsoucing.amount.before.l30.T'+str(month) : row_result['CURRENT_DEBT'] if row_result['CURRENT_DEBT'] != None else 0})
				elif(row_result['DPD'] == '30+' or row_result['DPD'] == '<60'):
					modified = mongodb.inc(MONGO_COLLECTION=collection_assigned_report,WHERE=where,VALUE={'outsoucing.account.before.p30.T'+str(month) :1, 'outsoucing.amount.before.p30.T'+str(month) : row_result['CURRENT_DEBT'] if row_result['CURRENT_DEBT'] != None else 0})
				elif(row_result['DPD'] == '60+'):
					modified = mongodb.inc(MONGO_COLLECTION=collection_assigned_report,WHERE=where,VALUE={'outsoucing.account.before.p60.T'+str(month) :1, 'outsoucing.amount.before.p60.T'+str(month) : row_result['CURRENT_DEBT'] if row_result['CURRENT_DEBT'] != None else 0})
				elif(row_result['DPD'] == '90+'):
					modified = mongodb.inc(MONGO_COLLECTION=collection_assigned_report,WHERE=where,VALUE={'outsoucing.account.before.p90.T'+str(month) :1, 'outsoucing.amount.before.p90.T'+str(month) : row_result['CURRENT_DEBT'] if row_result['CURRENT_DEBT'] != None else 0})
				elif(row_result['DPD'] == '180+'):
					modified = mongodb.inc(MONGO_COLLECTION=collection_assigned_report,WHERE=where,VALUE={'outsoucing.account.before.p180.T'+str(month):1, 'outsoucing.amount.before.p180.T'+str(month) : row_result['CURRENT_DEBT'] if row_result['CURRENT_DEBT'] != None else 0})
				else: continue
	#2.COLLECTED
	pipeline_count = [
		{
			'$match' : {
				'created_at' : { '$gte' :  startday,'$lt' : endday}
			}
		}	
	]
	pipeline_count.append({'$count' : 'passing'})
	count_outsoucing = mongodb.aggregate_pipeline(MONGO_COLLECTION=collection,aggregate_pipeline=pipeline_count)
	count = 0
	if count_outsoucing != None:
		for row_count in count_outsoucing:
			count = row_count['passing']
			break
			
	for i in range(count):	
		pipeline_outsoucing = [
			{
				'$match' : {
					'created_at' : { '$gte' :  startday,'$lt' : endday}
				}
			}		
		]
		pipeline_outsoucing.append({'$sort' : { '_id' : 1 }})
		pipeline_outsoucing.append({'$skip' : i * 1000})
		pipeline_outsoucing.append({'$limit' : 1000})		
		
		results_outsoucing = mongodb.aggregate_pipeline(MONGO_COLLECTION=collection,aggregate_pipeline=pipeline_outsoucing)
		if results_outsoucing != None:
			for row_result in results_outsoucing:
					account_number = row_result['CONTRACTNR']	
					get_ln3206f = mongodb.getOne(MONGO_COLLECTION=collection_ln3206f, WHERE={'account_number':account_number, 'code':'10', 'created_at' : { '$gte' :  startday,'$lt' : endday}})
					plus = 0
					amount = 0
					if get_ln3206f != None:
						plus = 1
						amount = get_ln3206f['amt']
					else:
						get_gl_2018 = mongodb.get(MONGO_COLLECTION=collection_gl_2018, WHERE={'$and' : [{'created_at' : { '$gte' :  startday,'$lt' : endday}}, {'account_number':account_number}, {'$or' : [{'code': '2000'}, {'code': '2100'}, {'code': '2700'}]}]})
						if get_ln3206f != None:
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
								plus = 1
								amount = code_amount_2000+code_amount_2100-code_amount_2700
					
					where = {'partner':row_result['COMPANY'], 'year' : {'$exists' : True, '$eq' : str(year)} }
					if(row_result['DPD'] == '<30'):
						modified = mongodb.inc(MONGO_COLLECTION=collection_assigned_report,WHERE=where,VALUE={'collected.account.before.l30.T'+str(lastMonth) :plus, 'collected.amount.before.l30.T'+str(lastMonth) : amount})
					elif(row_result['DPD'] == '30+' or row_result['DPD'] == '<60'):
						modified = mongodb.inc(MONGO_COLLECTION=collection_assigned_report,WHERE=where,VALUE={'collected.account.before.p30.T'+str(lastMonth) :plus, 'collected.amount.before.p30.T'+str(lastMonth) : amount})
					elif(row_result['DPD'] == '60+'):
						modified = mongodb.inc(MONGO_COLLECTION=collection_assigned_report,WHERE=where,VALUE={'collected.account.before.p60.T'+str(lastMonth) :plus, 'collected.amount.before.p60.T'+str(lastMonth) : amount})
					elif(row_result['DPD'] == '90+'):
						modified = mongodb.inc(MONGO_COLLECTION=collection_assigned_report,WHERE=where,VALUE={'collected.account.before.p90.T'+str(lastMonth) :plus, 'collected.amount.before.p90.T'+str(lastMonth) : amount})
					elif(row_result['DPD'] == '180+'):
						modified = mongodb.inc(MONGO_COLLECTION=collection_assigned_report,WHERE=where,VALUE={'collected.account.before.p180.T'+str(lastMonth):plus, 'collected.amount.before.p180.T'+str(lastMonth) : amount})
					else: continue	
except Exception as ex :
	pprint(ex)
	log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(ex) + '\n')
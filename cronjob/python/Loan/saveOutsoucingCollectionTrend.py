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
base_url = config.base_url()
log = open(base_url + "cronjob/python/Loan/log/saveOutsoucingCollectionTrend.txt","a")
now = datetime.now()
subUserType = 'LO'
collection = common.getSubUser(subUserType, 'Cus_assigned_partner')
collection_ln3206f = common.getSubUser(subUserType, 'LN3206F')
collection_listofAccount = common.getSubUser(subUserType, 'List_of_account_in_collection')

try :
	today = date.today()
	
	day = today.day
	month = today.month
	year = today.year
	weekday = today.weekday()
	lastDayOfMonth = calendar.monthrange(year, month)[1]

	todayString = today.strftime("%d/%m/%Y")
	todayTimeStamp = int(time.mktime(time.strptime(str(todayString + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
	
	startday = int(time.mktime(time.strptime(str(todayString + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
	endday = int(time.mktime(time.strptime(str(todayString + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))
	
	# print (todayTimeStamp)
	
	if day == 1:
		pipeline = [
			{
				'$match' : {
					'created_at' : { '$gte' :  startday,'$lt' : endday}
				}
			},
			{
				'$group' : {
					'_id' : '$COMPANY'
				}
			},
			{
				'$lookup' : {
					'from' : collection_ln3206f,
					'localField' : 'CONTRACTNR',
					'foreignField' : 'account_number',
					'as' : 'ln3206f'
				}
			},
			{
				'$unwind' : '$ln3206f'
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
				'$unwind' : '$listofAccount'
			},
			{
				'$sort' : {
					'_id' : 1
				}
			}
		]
		# print (json.dumps(pipeline, separators=(',', ':')))
		
		results_outsoucing = mongodb.aggregate_pipeline(MONGO_COLLECTION=collection,aggregate_pipeline=pipeline)
		
		# print (results_outsoucing)
		
		if results_outsoucing != None:
			for row in results_outsoucing:
				
			
	# insertData = []
	# updateData = []
	
	# today = date.today()
	# pageSize = 10
	# count = mongodb.count(MONGO_COLLECTION=collection)
	# for i in list(range(pageSize)):
		# skip = i * pageSize
		# data = mongodb.get(MONGO_COLLECTION=collection, SKIP=skip, TAKE=pageSize)
		# for item in data :
			# print (item)
			
except Exception as ex :
	log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(ex) + '\n')
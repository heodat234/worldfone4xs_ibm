#!/usr/bin/python3.6
# -*- coding: utf-8 -*-

import sys
import os
import time
import ntpath
import json
import calendar
import urllib
from helper.mongod import Mongodb
from datetime import datetime
from datetime import date
from pprint import pprint
from bson import ObjectId
from helper.common import Common
import pandas as pd
from helper.jaccs import Config
import xlsxwriter
import traceback

common      = Common()
base_url    = common.base_url()
wff_env     = common.wff_env(base_url)
mongodb     = Mongodb(MONGODB="worldfone4xs", WFF_ENV=wff_env)
_mongodb    = Mongodb(MONGODB="_worldfone4xs", WFF_ENV=wff_env)

log = open(base_url + "cronjob/python/Loan/log/exportOutsoucingCollectionTrend.txt","a")
now = datetime.now()
subUserType = 'LO'
collection = common.getSubUser(subUserType, 'Cus_assigned_partner')

try :
	year = now.year
	month =  now.month
	day =  now.day
	
	fileOutput = base_url + "upload/loan/export/Outsoucing_Collection_trend_" + now.strftime("%m%Y") + ".xlsx"
	
	# insertTemp = {
		# "Outsoucing" : {
			# "Account" : {
				# "BeforeWriteOff" : {
					
				# },
				# "WriteOff" : {
					
				# }
			# },
			# "Amount" : {
				
			# }
		# },
		# "Collected": {
			
		# }
	# }

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
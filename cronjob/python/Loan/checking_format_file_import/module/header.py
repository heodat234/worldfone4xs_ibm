import ftplib
import calendar
import time
import sys
import os
import json
import csv
from pprint import pprint
from datetime import datetime
from datetime import date
from bson import ObjectId
from helper.mongod import Mongodb
from helper.excel import Excel
from helper.common import Common

excel = Excel()
common = Common()
base_url = common.base_url()
wff_env = common.wff_env(base_url)
mongodb = Mongodb(MONGODB="worldfone4xs", WFF_ENV=wff_env)
_mongodb = Mongodb(MONGODB="_worldfone4xs", WFF_ENV=wff_env)

now = datetime.now()
today = date.today()

day = today.day
month = today.month
year = today.year

todayString = today.strftime("%d/%m/%Y")

def var_dump(datas):
	for data in datas:
		print(data)
#!/usr/bin/python3.6
# -*- coding: utf-8 -*-

import ftplib
import calendar
import time
import sys
import os
import json
import csv
import traceback
from pprint import pprint
from datetime import datetime
from datetime import date
from bson import ObjectId
from helper.ftp import Ftp
from helper.mongod import Mongodb
from helper.excel import Excel
from helper.jaccs import Config
from helper.common import Common

excel = Excel()
config = Config()
ftp = Ftp()
common = Common()
base_url = common.base_url()
wff_env = common.wff_env(base_url)
mongodb = Mongodb(MONGODB="worldfone4xs", WFF_ENV=wff_env)
_mongodb = Mongodb(MONGODB="_worldfone4xs", WFF_ENV=wff_env)
log = open(base_url + "cronjob/python/Loan/log/renameCol.txt","a")
now = datetime.now()
subUserType = 'LO'

try:
    mongodb.dropCollection(MONGO_COLLECTION='LO_LNJC05_yesterday')
    mongodb.dropCollection(MONGO_COLLECTION='LO_List_of_account_in_collection_yesterday')
    mongodb.dropCollection(MONGO_COLLECTION='LO_SBV_yesterday')

    mongodb.renameCollection(old_name='LO_LNJC05', new_name='LO_LNJC05_yesterday')
    mongodb.renameCollection(old_name='LO_List_of_account_in_collection', new_name='LO_List_of_account_in_collection_yesterday')
    mongodb.renameCollection(old_name='LO_SBV', new_name='LO_SBV_yesterday')
    
    mongodb.create_col(COL_NAME='LO_LNJC05')
    mongodb.create_col(COL_NAME='LO_List_of_account_in_collection')
    mongodb.create_col(COL_NAME='LO_SBV')
except Exception as e:
    print(traceback.format_exc())
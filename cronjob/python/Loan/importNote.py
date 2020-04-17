#!/usr/bin/python3.6
# -*- coding: utf-8 -*-

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
from helper.ftp import Ftp
from helper.mongod import Mongodb
from helper.excel import Excel
from helper.jaccs import Config
from helper.common import Common

try:
    excel = Excel()
    common = Common()
    base_url = common.base_url()
    wff_env = common.wff_env(base_url)

    mongodb = Mongodb(MONGODB="worldfone4xs", WFF_ENV=wff_env)
    _mongodb = Mongodb(MONGODB="_worldfone4xs", WFF_ENV=wff_env)
    file_path = '/mnt/nas/upload_file/20191226/CARD_A02_26122019.xlsx'
    subUserType = 'LO'
    collection = common.getSubUser(subUserType, 'Note')

    inputDataRaw = excel.getDataExcel(file_path=file_path, active_sheet="Sheet1", header=None, na_values='')
    inputData = inputDataRaw.to_dict('records')
    skip =0
    insertData = []

    for idx, row in enumerate(inputData):
        if skip == 0: 
            skip = skip + 1
            continue
        # if skip > 10:
        #     break
        temp = {}
        temp['created_by']  = 'import'
        temp['created_at']  = time.time()
        temp["content"]     = row[1]
        temp["foreign_id"]  = row[0]
        insertData.append(temp)

    
    mongodb.batch_insert(MONGO_COLLECTION=collection, insert_data=insertData)

    
except Exception as e:
    pprint(str(e))

#!/usr/bin/python3.6
# -*- coding: utf-8 -*-
log = open("/var/www/html/worldfone4xs_ibm/cronjob/python/Loan/testReadFileNoExt.txt","a")

import ftplib
import calendar
import time
import sys
import os
from pprint import pprint
from datetime import datetime
from datetime import date
from bson import ObjectId
from dateutil import parser
import pandas as pd
import csv
from helper.ftp import Ftp
from helper.mongod import Mongodb
from helper.excel import Excel
from helper.jaccs import Config
from helper.common import Common

try:
    mongodb = Mongodb("worldfone4xs")
    _mongodb = Mongodb("_worldfone4xs")
    excel = Excel()
    config = Config()
    ftp = Ftp()
    common = Common()
    base_url = config.base_url()
    now = datetime.now()
    subUserType = 'LO'
    collection = common.getSubUser(subUserType, 'SBV')

    url = '/var/www/html/worldfone4xs_ibm/cronjob/python/Loan/ZACCF_header.xlsx'
    filename = 'ZACCF_header.xlsx'
    filenameExtension = filename.split('.')
    if(filenameExtension[1] == 'csv'):
        inputDataRaw = excel.getDataCSV(file_path=url, sep=',', header=0, low_memory=False)
    else:
        inputDataRaw = excel.getDataExcel(file_path=url, header=0)
    
    inputData = inputDataRaw.to_dict('records')
    
    importData = []
    for key in inputData[0].keys():
        temp = {}
        temp['index'] = inputData[0][key]
        temp['collection'] = 'LO_ZACCF'
        temp['field'] = key
        temp['title'] = inputData[1][key]
        temp['type'] = 'string'
        temp['sub_type'] = '{"column": ' + inputData[2][key] + '}'
        importData.append(temp)
    
    pprint(importData)
    _mongodb.batch_insert(MONGO_COLLECTION='Model', insert_data=importData)

except Exception as e:
    print(str(e))

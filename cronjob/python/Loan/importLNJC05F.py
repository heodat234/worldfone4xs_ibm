#!/usr/bin/python3.6
# -*- coding: utf-8 -*-
log = open("/var/www/html/worldfone4xs_ibm/cronjob/python/Loan/importLNJC05F.txt","a")

import ftplib
import calendar
import time
import sys
import os
sys.path.insert(1, '/var/www/html/worldfone4xs_ibm/cronjob/python')
from ftp import Ftp
from pprint import pprint
from mongod import Mongodb
from excel import Excel
from datetime import datetime
from datetime import date
from bson import ObjectId
from common import Common
from dateutil import parser

try:
    filename = 'LNJC05F'
    ftpPath = "/var/www/html/worldfone4xs_ibm/upload/loan/ftp/"
    localPath = "/var/www/html/worldfone4xs_ibm/upload/loan/ftp/"
    collection = 'LNJC05'
    mongodb = Mongodb("worldfone4xs")
    _mongodb = Mongodb("_worldfone4xs")
    excel = Excel()
    ftp = Ftp()
    common = Common()
    now = datetime.now()

    modelColumns = []
    modelConverters = {}
    modelFormat = {}
    insertData = []

    ftp.connect()
    ftp.downLoadFile(ftpPath + filename, filename)
    ftp.close()

    modelInfo = _mongodb.get(MONGO_COLLECTION='Model', WHERE={"collection": 'LO_LNJC05','sub_type':{'$exists': 'true'}}, SELECT=['index', 'field','type'], SORT=([('index', 1)]),TAKE=50)
    
    for model in modelInfo:
        modelColumns.append(model['field'])
        if(model['type'] == 'timestamp'):
            modelFormat[model['field']] = "%d/%m/%y"
        else:
            modelFormat[model['field']] = ""
        modelConverters[model['field']] = model['type']
    
    # importLogInfo = {
    #     'collection'    : collection,
    #     'begin_import'  : time.time(),
    #     'file_name'     : filename,
    #     'file_path'     : localPath + filename,
    #     'source'        : 'ftp',
    #     'file_type'     : 'csv',
    #     'status'        : 2,
    #     'created_by'    : 'system'
    # }

    # importLogId = mongodb.insert(MONGO_COLLECTION='LO_Import', insert_data=importLogInfo)

    with open(localPath + filename, 'r', newline='\n', encoding='ISO-8859-1') as fin:
        for line in fin:
            # pprint(line)
            rows = line.split(';')
            temp = {}
            if len(rows) > 1:
                i = 0
                for row in rows:
                    if row ==  "\r\n":
                        continue
                    temp[modelColumns[i]] = row
                    i = i +1

            # temp['import_id'] = str(importLogId)
            temp['created_by'] = 'system'
            temp['created_at'] = time.time()
            insertData.append(temp)
        


    # temp = {}
    # # temp['import_id'] = str(importLogId)
    # temp['created_by'] = 'system'
    # temp['created_at'] = time.time()
    # insertData.append(temp)               
    pprint(insertData)
    # if(len(insertData) > 0):
    #     mongodb.batch_insert(MONGO_COLLECTION=collection, insert_data=insertData)
    #     mongodb.update(MONGO_COLLECTION='LO_Import', WHERE={'_id': importLogId}, VALUE={'status': 1, 'complete_import': time.time()})
    
    
except Exception as e:
    log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')

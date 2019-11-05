#!/usr/bin/python3.6
# -*- coding: utf-8 -*-
log = open("/var/www/html/worldfone4xs_ibm/cronjob/python/Loan/log/importZACCF.txt","a")

import ftplib
import calendar
import time
import sys
import os
import json
sys.path.insert(1, '/var/www/html/worldfone4xs_ibm/cronjob/python')
from ftp import Ftp
from pprint import pprint
from mongod import Mongodb
from excel import Excel
from datetime import datetime
from datetime import date
from bson import ObjectId
from common import Common

try:
    filename = 'ZACCF.csv'
    filepath = '/var/www/html/worldfone4xs_ibm/upload/csv/ftp/'
    collection = 'LO_ZACCF'
    collectionResult = 'LO_ZACCF_result'
    mongodb = Mongodb("worldfone4xs")
    _mongodb = Mongodb("_worldfone4xs")
    excel = Excel()
    ftp = Ftp()
    common = Common()
    now = datetime.now()

    modelColumns = []
    modelConverters = {}
    modelPosition = {}

    ftp.connect()
    ftp.downLoadFile(filepath + filename, filename)
    ftp.close()

    importLogInfo = {
        'collection'    : collection, 
        'begin_import'  : time.time(),
        'file_name'     : filename,
        'file_path'     : filepath + filename, 
        'source'        : 'ftp',
        'file_type'     : 'csv',
        'status'        : 2,
        'created_by'    : 'system'
    }
    importLogId = mongodb.insert(MONGO_COLLECTION='LO_Import', insert_data=importLogInfo) 

    models = _mongodb.get(MONGO_COLLECTION='Model', WHERE={'collection': collection}, SORT=[('index', 1)], SELECT=['index', 'collection', 'field', 'type', 'sub_type'], TAKE=40)
    
    for model in models:
        modelColumns.append(model['field'])
        modelConverters[model['field']] = model['type']
        subtype = json.loads(model['sub_type'])

    filenameExtension = filename.split('.')
    if(filenameExtension[1] == 'csv'):
        inputDataRaw = excel.getDataCSV(file_path=importLogInfo['file_path'], sep=',', header=None, names=modelColumns, na_values='')
    else:
        inputDataRaw = excel.getDataExcel(file_path=importLogInfo['file_path'], header=None, names=modelColumns, na_values='')

    inputData = inputDataRaw.to_dict('records')

    insertData = []
    errorData = []

    temp = {}
    countList = 0
    for idx, row in enumerate(inputData):
        temp = {}
        for cell in row:
            try:
                if(modelConverters[cell] == 'timestamp'):
                    if row[cell] is not None and row[cell] is not '' and row[cell] is not ' ':
                        temp[cell] = common.convertDataType(data=row[cell], datatype=modelConverters[cell], formatType="%d%m%y")
                else:
                    temp[cell] = common.convertDataType(data=row[cell], datatype=modelConverters[cell])
                result = True
            except Exception as errorConvertType:
                temp['error_cell'] = modelPosition[cell] + str(idx + 1)
                temp['type'] = modelConverters[cell]
                temp['error_mesg'] = 'Sai kiểu dữ liệu nhập'
                temp['result'] = 'error'
                result = False
            temp['created_by'] = 'system'
            temp['created_at'] = time.time()
            temp['import_id'] = str(importLogId)

        if(result == False):
            errorData.append(temp)
        else:
            insertData.append(temp)
            temp['result'] = 'success'
            result = True

    if(len(errorData) > 0):
        mongodb.batch_insert(collectionResult, errorData)
    else:
        if len(insertData) > 0:
            mongodb.batch_insert(MONGO_COLLECTION=collection, insert_data=insertData)
            mongodb.batch_insert(collectionResult, insert_data=insertData)
    
    mongodb.update(MONGO_COLLECTION='LO_Import', WHERE={'_id': importLogId}, VALUE={'status': 1, 'complete_import': time.time()})

except Exception as e:
    log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')

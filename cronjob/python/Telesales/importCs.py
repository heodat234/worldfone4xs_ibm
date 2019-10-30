#!/usr/bin/python3.6
# -*- coding: utf-8 -*-
log = open("/var/www/html/worldfone4xs_ibm/cronjob/python/Telesales/importSc.txt","a")

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

try:
    filename = 'DANH SACH SC.csv'
    mongodb = Mongodb("worldfone4xs")
    _mongodb = Mongodb("_worldfone4xs")
    excel = Excel()
    ftp = Ftp()
    now = datetime.now()

    scColumns = []
    scConverters = {}
    insertData = []
    errorData = []

    ftp.connect()
    ftp.downLoadFile("/var/www/html/worldfone4xs_ibm/upload/csv/ftp/" + filename, filename)
    ftp.close()

    path, filename = os.path.split("/var/www/html/worldfone4xs_ibm/upload/csv/ftp/" + filename)

    importLogInfo = {
        'collection'    : "Sc",
        'begin_import'  : time.time(),
        'file_name'     : filename,
        'file_path'     : path + '/' + filename,
        'source'        : 'ftp',
        'status'        : 2,
        'created_by'    : 'system'
    }
    importLogId = mongodb.insert(MONGO_COLLECTION='2_Import', insert_data=importLogInfo)
    # pprint(str(importLogId))

    modelsSc = _mongodb.get(MONGO_COLLECTION='Model', WHERE={'collection': '2_Sc', 'sub_type': {'$ne': None}}, SORT=[('index', 1)])
    for model in modelsSc:
        scColumns.append(model['field'])
        if(model['type'] == 'string'):
            scConverters[model['field']] = str

    filenameExtension = filename.split('.')
    if(filenameExtension[1] == 'csv'):
        scs = excel.getDataCSV(file_path=importLogInfo['file_path'], header=0, names=scColumns, converters=scConverters)
    else:
        scs = excel.getDataExcel(file_path=importLogInfo['file_path'], header=0, names=scColumns, converters=scConverters)
    
    scList = scs.to_dict('records')

    mongodb.remove_document(MONGO_COLLECTION='2_Sc')
    for key, value in enumerate(scList):
        result = True
        if(value['sc_code'] == ''):
            value['error_cell'] = 'B' + (key + 2)
            value['type'] = 'string'
            value['error_mesg'] = 'Thiếu thông tin Mã SC'
            result = False
        if(value['sc_name'] == ''):
            value['error_cell'] = 'C' + (key + 2)
            value['type'] = 'string'
            value['error_mesg'] = 'Thiếu thông tin Tên SC'
            result = False
        value['created_at'] = time.time()
        value['created_by'] = 'system'
        value['import_id'] = str(importLogId)
        if(result == True):
            value['result'] = 'success'
            insertData.append(value)
        else:
            value['result'] = 'error'
            errorData.append(value)
    
    if(len(errorData) > 0):
        mongodb.batch_insert(MONGO_COLLECTION='2_Sc_import_result', insert_data=errorData)
        mongodb.update(MONGO_COLLECTION='2_Import', WHERE={'_id': importLogId}, VALUE={'status': 0})
    else:
        mongodb.batch_insert(MONGO_COLLECTION='2_Sc', insert_data=insertData)
        mongodb.batch_insert(MONGO_COLLECTION='2_Sc_import_result', insert_data=insertData)
        mongodb.update(MONGO_COLLECTION='2_Import', WHERE={'_id': importLogId}, VALUE={'status': 1})

except Exception as e:
    log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')

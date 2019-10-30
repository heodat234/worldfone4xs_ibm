#!/usr/bin/python3.6
# -*- coding: utf-8 -*-
log = open("/var/www/html/worldfone4xs_ibm/cronjob/python/Telesales/importSibs.txt","a")

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
    filename = 'ZACCF full.csv'
    mongodb = Mongodb("worldfone4xs")
    _mongodb = Mongodb("_worldfone4xs")
    excel = Excel()
    ftp = Ftp()
    now = datetime.now()

    sibsColumns = []
    sibsConverters = {}

    ftp.connect()
    ftp.downLoadFile("/var/www/html/worldfone4xs_ibm/upload/csv/ftp/ZACCF full.csv", filename)
    ftp.close()

    path, filename = os.path.split("/var/www/html/worldfone4xs_ibm/upload/csv/ftp/ZACCF full.csv")

    importLogInfo = {
        'collection'    : "Sibs",
        'begin_import'  : time.time(),
        'file_name'     : filename,
        'file_path'     : path + '/' + filename,
        'source'        : 'ftp',
        'file_type'     : 'csv',
        'status'        : 2,
        'created_by'    : 'system'
    }
    importLogId = mongodb.insert(MONGO_COLLECTION='2_Import', insert_data=importLogInfo)

    modelsSibs = _mongodb.get(MONGO_COLLECTION='Model', WHERE={'collection': '2_Sibs'}, SORT=[('index', 1)], SELECT=['index', 'collection', 'field', 'type'])
    for model in modelsSibs:
        sibsColumns.append(model['field'])
        if(model['type'] == 'string'):
            sibsConverters[model['field']] = str

    zaccfs = excel.getDataCSV(file_path=importLogInfo['file_path'], header=0, names=sibsColumns, usecols=[5, 6, 7, 116, 122], converters=sibsConverters)
    
    zaccfList = zaccfs.to_dict('records')

    insertData = []
    updateData = []
    errorData = []

    temp = {}
    countList = 0
    for idx, zaccf in enumerate(zaccfList):
        if zaccf['account_no'] not in (None, '') and zaccf['cif'] not in (None, '') and zaccf['cus_name'] not in (None, ''):
            result = True
            checkSibs = mongodb.getOne(MONGO_COLLECTION='2_Sibs', WHERE={'account_no': zaccf['account_no']}, SELECT=['account_no'])
            zaccf['import_id'] = str(importLogId)
            try:
                zaccf['advance'] = float(zaccf['advance'])
            except Exception as errorConvertDM:
                zaccf['error_cell'] = 'DM' + str(idx + 2)
                zaccf['type'] = 'number'
                zaccf['error_mesg'] = 'Sai kiểu dữ liệu nhập'
                zaccf['result'] = 'error'
                result = False

            try:
                zaccf['current_balance'] = float(zaccf['current_balance'])
            except Exception as errorConvertDS:
                zaccf['error_cell'] = 'DS' + str(idx + 2)
                zaccf['type'] = 'number'
                zaccf['error_mesg'] = 'Sai kiểu dữ liệu nhập'
                zaccf['result'] = 'error'
                result = False
            
            if(result == False):
                errorData.append(zaccf)
            else:
                if(checkSibs is None):
                    insertData.append(zaccf)
                else:
                    updateData.append(zaccf)
                zaccf['result'] = 'success'
                result = True
        else:
            continue

    if(len(errorData) > 0):
        mongodb.batch_insert("2_Sibs_result", errorData)
    else:
        if len(updateData) > 0:
            for upData in updateData:
                mongodb.update(MONGO_COLLECTION='2_Sibs', WHERE={'account_no': upData['account_no']}, VALUE=upData)
            mongodb.batch_insert("2_Sibs_result", updateData)
        if len(insertData) > 0:
            mongodb.batch_insert(MONGO_COLLECTION="2_Sibs", insert_data=insertData)
            mongodb.batch_insert("2_Sibs_result", insert_data=insertData)
    
    mongodb.update(MONGO_COLLECTION='2_Import', WHERE={'_id': importLogId}, VALUE={'status': 1, 'complete_import': time.time()})

except Exception as e:
    log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')

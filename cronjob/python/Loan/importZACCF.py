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
from datetime import date
from bson import ObjectId
from helper.ftp import Ftp
from helper.mongod import Mongodb
from helper.excel import Excel
from helper.jaccs import Config
from helper.common import Common

mongodb = Mongodb("worldfone4xs")
_mongodb = Mongodb("_worldfone4xs")
excel = Excel()
config = Config()
ftp = Ftp()
common = Common()
base_url = config.base_url()
log = open(base_url + "cronjob/python/Loan/log/importZACCF.txt","a")
logRun = open(base_url + "cronjob/python/Loan/log/runImportZACCF.txt","a")
now = datetime.now()
subUserType = 'LO'
collection = common.getSubUser(subUserType, 'ZACCF')

try:
    modelColumns = []
    modelConverters = {}
    modelPosition = {}
    modelFormat = {}
    updateKey = []
    checkNullKey = []

    ftpInfo = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'ftp_config'), WHERE={'collection': collection})
    ftpConfig = config.ftp_config()
    ftpLocalUrl = common.getDownloadFolder() + ftpInfo['filename']

    try:
        sys.argv[1]
        importLogId = str(sys.argv[1])
        importLogInfo = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Import'), WHERE={'_id': ObjectId(sys.argv[1])})
    except Exception as SysArgvError:
        # ftp.connect(host=ftpConfig['host'], username=ftpConfig['username'], password=ftpConfig['password'])
        # ftp.downLoadFile(ftpLocalUrl, ftpInfo['filename'])
        # ftp.close()

        importLogInfo = {
            'collection'    : collection, 
            'begin_import'  : time.time(),
            'file_name'     : ftpInfo['filename'],
            'file_path'     : ftpLocalUrl, 
            'source'        : 'ftp',
            'status'        : 2,
            'command'       : 'python3.6 ' + base_url + "cronjob/python/Loan/importZACCF.py > /dev/null &",
            'created_by'    : 'system'
        }
        importLogId = mongodb.insert(MONGO_COLLECTION=common.getSubUser(subUserType, 'Import'), insert_data=importLogInfo) 

    models = _mongodb.get(MONGO_COLLECTION='Model', WHERE={'collection': collection}, SORT=[('index', 1)], SELECT=['index', 'collection', 'field', 'type', 'sub_type'], TAKE=1000)
    
    for model in models:
        modelColumns.append(model['field'])
        modelConverters[model['field']] = model['type']
        subtype = json.loads(model['sub_type'])
        
        if 'format' in subtype.keys():
            modelFormat[model['field']] = subtype['format']
        else:
            modelFormat[model['field']] = ''
        
        if 'update_key' in subtype.keys() and subtype['update_key'] == 1:
            updateKey.append(model['field'])

        if 'check_null_key' in subtype.keys():
            checkNullKey.append(model['field'])

    filenameExtension = ftpInfo['filename'].split('.')

    if ftpInfo['header'] == 'None':
        header = None
    else:
        header = [ int(x) for x in ftpInfo['header'] ]

    if(filenameExtension[1] == 'csv'):
        inputDataRaw = excel.getDataCSV(file_path=importLogInfo['file_path'], sep=',', header=header, names=modelColumns, encoding='ISO-8859-1', low_memory=False)
    else:
        inputDataRaw = excel.getDataExcel(file_path=importLogInfo['file_path'], header=header, names=modelColumns, na_values='', encoding='ISO-8859-1')

    inputData = inputDataRaw.to_dict('records')
    
    insertData = []
    updateDate = []
    errorData = []

    temp = {}
    countList = 0
    for idx, row in enumerate(inputData):
        temp = {}
        if row['ACC_ID'] not in [None, ''] and row['CUS_ID'] not in [None, '']:
            for cell in row:
                try:
                    temp[cell] = common.convertDataType(data=row[cell], datatype=modelConverters[cell], formatType=modelFormat[cell])
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
                temp['result'] = 'success'
                checkDataInDB = mongodb.getOne(MONGO_COLLECTION=collection, WHERE={'ACC_ID': temp['ACC_ID'], 'CUS_ID': temp['CUS_ID']})
                if checkDataInDB is not None:
                    mongodb.update(MONGO_COLLECTION=collection, WHERE={'ACC_ID': temp['ACC_ID'], 'CUS_ID': temp['CUS_ID']}, VALUE=temp)
                    # updateDate.append(temp)
                else:
                    mongodb.insert(MONGO_COLLECTION=collection, insert_data=temp)
                    # insertData.append(temp)
                result = True
        logRun.write(str(idx))
    if(len(errorData) > 0):
        mongodb.update(MONGO_COLLECTION=common.getSubUser(subUserType, 'Import'), WHERE={'_id': importLogId}, VALUE={'status': 0, 'complete_import': time.time()})
        mongodb.batch_insert(common.getSubUser(subUserType, 'ZACCF_result'), errorData)
    else:
        mongodb.update(MONGO_COLLECTION=common.getSubUser(subUserType, 'Import'), WHERE={'_id': importLogId}, VALUE={'status': 1, 'complete_import': time.time()})
except Exception as e:
    log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')

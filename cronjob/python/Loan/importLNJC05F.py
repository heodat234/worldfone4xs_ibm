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
log = open(base_url + "cronjob/python/Loan/log/importLNJC05F.txt","a")
now = datetime.now()
subUserType = 'LO'
collection = common.getSubUser(subUserType, 'LNJC05')

try:
    modelColumns = []
    modelConverters = {}
    modelPosition = {}
    modelFormat = {}
    updateKey = []
    checkNullKey = []

    ftpConfig = config.ftp_config()
    ftpInfo = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'ftp_config'), WHERE={'collection': collection})
    ftpLocalUrl = base_url + ftpInfo['locallink'] + ftpInfo['filename']

    ftp.connect(host=ftpConfig['host'], username=ftpConfig['username'], password=ftpConfig['password'])
    ftp.downLoadFile(ftpLocalUrl, ftpInfo['filename'])
    ftp.close()

    importLogInfo = {
        'collection'    : collection,
        'begin_import'  : time.time(),
        'file_name'     : ftpInfo['filename'],
        'file_path'     : ftpLocalUrl,
        'source'        : 'ftp',
        'status'        : 2,
        'created_by'    : 'system'
    }
    importLogId = mongodb.insert(MONGO_COLLECTION=common.getSubUser(subUserType, 'Import'), insert_data=importLogInfo)

    models = _mongodb.get(MONGO_COLLECTION='Model', WHERE={'collection': collection,'sub_type':{'$exists': 'true'}}, SORT=[('index', 1)], SELECT=['index', 'collection', 'field', 'type', 'sub_type'], TAKE=1000)

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
    if(filenameExtension[1] == 'csv'):
        inputDataRaw = excel.getDataCSV(file_path=importLogInfo['file_path'], header=None, names=None, encoding='ISO-8859-1')
    else:
        inputDataRaw = excel.getDataExcel(file_path=importLogInfo['file_path'], header=None, names=None, na_values='', encoding='ISO-8859-1')

    inputData = inputDataRaw.to_dict('records')
    insertData = []
    updateDate = []
    errorData = []

    temp = {}
    countList = 0
    for idx, row in enumerate(inputData):
        temp = {}
        if row[2] is not None and row[2] is not '':
            for key,cell in enumerate(modelColumns):
                try:
                    if modelConverters[cell] == 'timestamp':
                        if len(str(row[key])) == 5:
                            row[key] = '0'+str(row[key])
                        
                        date = str(row[key])
                        row[key] = date[0:2]+'/'+date[2:4]+'/'+date[4:6]
                        modelFormat[cell] = "%d/%m/%y"

                    temp[cell] = common.convertDataType(data=row[key], datatype=modelConverters[cell], formatType=modelFormat[cell])
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
                checkDataInDB = mongodb.getOne(MONGO_COLLECTION=collection, WHERE={'account_number': temp['account_number']})
                if checkDataInDB is not None:
                    updateDate.append(temp)
                else:
                    insertData.append(temp)
                result = True
            # break
    if(len(errorData) > 0):
        mongodb.batch_insert(common.getSubUser(subUserType, 'LNJC05_result'), errorData)
    else:
        if len(insertData) > 0:
            mongodb.remove_document(MONGO_COLLECTION=collection)
            mongodb.batch_insert(MONGO_COLLECTION=collection, insert_data=insertData)
            mongodb.batch_insert(common.getSubUser(subUserType, 'LNJC05_result'), insert_data=insertData)

        if len(updateDate) > 0:
            for updateD in updateDate:
                mongodb.update(MONGO_COLLECTION=collection, WHERE={'account_number': updateD['account_number']}, VALUE=updateD)
            mongodb.batch_insert(common.getSubUser(subUserType, 'LNJC05_result'), insert_data=updateDate)

    mongodb.update(MONGO_COLLECTION=common.getSubUser(subUserType, 'Import'), WHERE={'_id': importLogId}, VALUE={'status': 1, 'complete_import': time.time(),'error': errorData})
    print(111)
except Exception as e:
    log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')

#!/usr/bin/python3.6
# -*- coding: utf-8 -*-
log = open("/var/www/html/worldfone4xs_ibm/cronjob/python/Loan/importListOfAccount.txt","a")

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
    filename = 'LIST_OF_ACCOUNT_IN_COLLECTION'
    ftpPath = "/var/www/html/worldfone4xs_ibm/upload/loan/ftp/"
    localPath = "/var/www/html/worldfone4xs_ibm/upload/loan/ftp/"
    collection = 'List_of_account_in_collection'
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
    updateData = []

    ftp.connect()
    ftp.downLoadFile(ftpPath + filename, filename)
    ftp.close()

    modelInfo = _mongodb.get(MONGO_COLLECTION='Model', WHERE={'collection': 'LO_' + collection, 'sub_type': {'$ne': None}}, SORT=[('index', 1)])
    
    for model in modelInfo:
        modelColumns.append(model['field'])
        if(model['type'] == 'timestamp'):
            modelFormat[model['field']] = "%d/%m/%y"
        else:
            modelFormat[model['field']] = ""
        modelConverters[model['field']] = model['type']
    
    importLogInfo = {
        'collection'    : "List_of_account_in_collection",
        'begin_import'  : time.time(),
        'file_name'     : filename,
        'file_path'     : localPath + filename,
        'source'        : 'ftp',
        'file_type'     : 'csv',
        'status'        : 2,
        'created_by'    : 'system'
    }

    importLogId = mongodb.insert(MONGO_COLLECTION='LO_Import', insert_data=importLogInfo)

    with open(localPath + filename, 'r', newline='\n', encoding='ISO-8859-1') as fin:
        # pprint(fin)
        for line in fin:
            row = line.split()
            if len(row) > 1:
                if isinstance(row[1], str) and len(row[1]) > 12 and row[1].isdigit():
                    temp = {}
                    temp[modelColumns[0]] = common.convertDataType(data=row[1], datatype=modelConverters[modelColumns[0]], formatType=modelFormat[modelColumns[0]])
                    temp[modelColumns[1]] = common.convertDataType(data=row[2], datatype=modelConverters[modelColumns[1]], formatType=modelFormat[modelColumns[1]])
                    temp[modelColumns[-1]] = common.convertDataType(data=row[-1], datatype=modelConverters[modelColumns[-1]], formatType=modelFormat[modelColumns[-1]])
                    temp[modelColumns[-2]] = common.convertDataType(data=row[-2], datatype=modelConverters[modelColumns[-2]], formatType=modelFormat[modelColumns[-2]])
                    temp[modelColumns[-3]] = common.convertDataType(data=row[-3], datatype=modelConverters[modelColumns[-3]], formatType=modelFormat[modelColumns[-3]])
                    temp[modelColumns[-4]] = common.convertDataType(data=row[-4], datatype=modelConverters[modelColumns[-4]], formatType=modelFormat[modelColumns[-4]])
                    temp[modelColumns[-5]] = common.convertDataType(data=row[-5], datatype=modelConverters[modelColumns[-5]], formatType=modelFormat[modelColumns[-5]])
                    temp[modelColumns[-6]] = common.convertDataType(data=row[-6], datatype=modelConverters[modelColumns[-6]], formatType=modelFormat[modelColumns[-6]])
                    listName = row[3:-7]
                    temp[modelColumns[2]] = ' '.join(listName)
                    checkAccount = mongodb.getOne(MONGO_COLLECTION='LO_List_of_account_in_collection', WHERE={'account_no': temp['account_no']})
                    if(checkAccount is None):
                        temp['import_id'] = str(importLogId)
                        temp['created_by'] = 'system'
                        temp['created_at'] = time.time()
                        insertData.append(temp)
                    else:
                        temp['updated_import_id'] = str(importLogId)
                        temp['updated_by'] = 'system'
                        temp['updated_at'] = time.time()
                        updateData.append(temp)
    
    # pprint(updateData)
    if(len(insertData) > 0):
        mongodb.batch_insert(MONGO_COLLECTION='LO_List_of_account_in_collection', insert_data=insertData)
        mongodb.batch_insert(MONGO_COLLECTION='LO_List_of_account_in_collection_result', insert_data=insertData)
        mongodb.update(MONGO_COLLECTION='LO_Import', WHERE={'_id': importLogId}, VALUE={'status': 1, 'complete_import': time.time()})
    
    if(len(updateData) > 0):
        for updateD in updateData:
            mongodb.update(MONGO_COLLECTION='LO_List_of_account_in_collection', WHERE={'account_no': updateD['account_no']}, VALUE=updateD)
        mongodb.batch_insert(MONGO_COLLECTION='LO_List_of_account_in_collection_result', insert_data=updateData)
        mongodb.update(MONGO_COLLECTION='LO_Import', WHERE={'_id': importLogId}, VALUE={'status': 1, 'complete_import': time.time()})

except Exception as e:
    log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')

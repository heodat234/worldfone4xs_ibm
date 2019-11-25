#!/usr/bin/python3.6
# -*- coding: utf-8 -*-
log = open("/var/www/html/worldfone4xs_ibm/cronjob/python/Loan/importListOfAccount.txt","a")

import ftplib
import calendar
import time
import sys
import os
import csv
import json
from pprint import pprint
from datetime import datetime
from datetime import date
from bson import ObjectId
from dateutil import parser
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
log = open(base_url + "cronjob/python/Loan/log/importListOfAccountInCollection.txt","a")
now = datetime.now()
subUserType = 'LO'
collection = common.getSubUser(subUserType, 'List_of_account_in_collection')

try:
    modelColumns = []
    modelConverters = {}
    modelFormat = {}
    insertData = []
    updateData = []

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
            'created_by'    : 'system'
        }
        importLogId = mongodb.insert(MONGO_COLLECTION=common.getSubUser(subUserType, 'Import'), insert_data=importLogInfo) 

    models = _mongodb.get(MONGO_COLLECTION='Model', WHERE={'collection': collection, 'sub_type': {'$ne': None}}, SORT=[('index', 1)], SELECT=['index', 'collection', 'field', 'type', 'sub_type'], TAKE=1000)
    for model in models:
        modelColumns.append(model['field'])
        modelConverters[model['field']] = model['type']
        if model['sub_type'] is not None:
            subtype = json.loads(model['sub_type'])
            if 'format' in subtype.keys():
                modelFormat[model['field']] = subtype['format']
            else:
                modelFormat[model['field']] = ''
        else:
            modelFormat[model['field']] = ''
        pprint(modelColumns)

    with open(importLogInfo['file_path'], 'r', newline='\n', encoding='ISO-8859-1') as fin:
        csv_reader = csv.reader(fin, delimiter=',', quotechar='"')
        for row in csv_reader:
            if len(row) > 1:
                if isinstance(row[1], str) and len(row[1]) > 12 and row[1].isdigit():
                    pprint(row)
                    temp = {}
                    temp[modelColumns[0]] = common.convertDataType(data=row[1], datatype=modelConverters[modelColumns[0]], formatType=modelFormat[modelColumns[0]]) if row[1] not in [None, ''] else ''
                    temp[modelColumns[1]] = common.convertDataType(data=row[2], datatype=modelConverters[modelColumns[1]], formatType=modelFormat[modelColumns[1]]) if row[2] not in [None, ''] else ''
                    temp[modelColumns[2]] = common.convertDataType(data=row[3], datatype=modelConverters[modelColumns[2]], formatType=modelFormat[modelColumns[2]]) if row[3] not in [None, ''] else ''
                    temp[modelColumns[3]] = common.convertDataType(data=row[4], datatype=modelConverters[modelColumns[3]], formatType=modelFormat[modelColumns[3]]) if row[4] not in [None, ''] else ''
                    temp[modelColumns[4]] = common.convertDataType(data=row[5], datatype=modelConverters[modelColumns[4]], formatType=modelFormat[modelColumns[4]]) if row[5] not in [None, ''] else ''
                    temp[modelColumns[5]] = common.convertDataType(data=row[6], datatype=modelConverters[modelColumns[5]], formatType=modelFormat[modelColumns[5]]) if row[6] not in [None, ''] else ''
                    temp[modelColumns[6]] = common.convertDataType(data=row[7], datatype=modelConverters[modelColumns[6]], formatType=modelFormat[modelColumns[6]]) if row[7] not in [None, ''] else 0
                    temp[modelColumns[7]] = common.convertDataType(data=row[8], datatype=modelConverters[modelColumns[7]], formatType=modelFormat[modelColumns[7]]) if row[8] not in [None, ''] else 0
                    temp[modelColumns[8]] = common.convertDataType(data=row[9], datatype=modelConverters[modelColumns[8]], formatType=modelFormat[modelColumns[8]]) if row[9] not in [None, ''] else ''
                    
                    checkAccount = mongodb.getOne(MONGO_COLLECTION=collection, WHERE={'account_number': temp['account_number']})
                    if(checkAccount is None):
                        temp['import_id'] = str(importLogId)
                        temp['created_by'] = 'system'
                        temp['created_at'] = int(time.mktime(time.strptime(str('31/10/2019' + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
                        # temp['created_at'] = time.time()
                        insertData.append(temp)
                    else:
                        temp['updated_import_id'] = str(importLogId)
                        temp['updated_by'] = 'system'
                        temp['updated_at'] = time.time()
                        updateData.append(temp)
    
    # pprint(insertData)
    # pprint(insertData)
    if(len(insertData) > 0):
        mongodb.batch_insert(MONGO_COLLECTION=collection, insert_data=insertData)
        mongodb.batch_insert(MONGO_COLLECTION=common.getSubUser(subUserType, 'List_of_account_in_collection_result'), insert_data=insertData)
        mongodb.update(MONGO_COLLECTION=common.getSubUser(subUserType, 'Import'), WHERE={'_id': importLogId}, VALUE={'status': 1, 'complete_import': time.time()})
    
    if(len(updateData) > 0):
        for updateD in updateData:
            mongodb.update(MONGO_COLLECTION=collection, WHERE={'account_no': updateD['account_no']}, VALUE=updateD)
        mongodb.batch_insert(MONGO_COLLECTION=common.getSubUser(subUserType, 'List_of_account_in_collection_result'), insert_data=updateData)
        mongodb.update(MONGO_COLLECTION=common.getSubUser(subUserType, 'Import'), WHERE={'_id': importLogId}, VALUE={'status': 1, 'complete_import': time.time()})

except Exception as e:
    log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')

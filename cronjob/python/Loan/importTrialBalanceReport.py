#!/usr/bin/python3.6
# -*- coding: utf-8 -*-

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
log = open(base_url + "cronjob/python/Loan/log/importTrialBalanceReport.txt","a")
now = datetime.now()
subUserType = 'LO'
collection = common.getSubUser(subUserType, 'Trial_balance_report')

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
            'command'       : 'python3.6 ' + base_url + "cronjob/python/Loan/importTrialBalanceReport.py > /dev/null &",
            'created_by'    : 'system'
        }
        importLogId = mongodb.insert(MONGO_COLLECTION=common.getSubUser(subUserType, 'Import'), insert_data=importLogInfo) 

    modelInfo = _mongodb.get(MONGO_COLLECTION='Model', WHERE={'collection': collection, 'sub_type': {'$ne': None}}, SORT=[('index', 1)])
    
    for model in modelInfo:
        modelColumns.append(model['field'])
        modelConverters[model['field']] = model['type']
        subtype = json.loads(model['sub_type'])
        if 'format' in subtype.keys():
            modelFormat[model['field']] = subtype['format']
        else:
            modelFormat[model['field']] = ''

    with open(importLogInfo['file_path'], 'r', newline='\n', encoding='ISO-8859-1') as fin:
        cardType = ''
        for line in fin:
            row = line.split()
            if len(row) > 1:
                if row[0] == 'CARD' and row[1] == 'TYPE:':
                    cardType = row[2]
                if isinstance(row[0], str) and row[0].isdigit():
                    temp = {}
                    temp['card_type'] = cardType
                    for key, column in enumerate(modelColumns):
                        temp[column] = common.convertDataType(data=row[key], datatype=modelConverters[column], formatType=modelFormat[column])
                    checkAccount = mongodb.getOne(MONGO_COLLECTION=collection, WHERE={'acc_no': temp['acc_no']})
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

    if(len(insertData) > 0):
        mongodb.batch_insert(MONGO_COLLECTION=collection, insert_data=insertData)
        mongodb.batch_insert(MONGO_COLLECTION=common.getSubUser(subUserType, 'Trial_balance_report_result'), insert_data=insertData)
        mongodb.update(MONGO_COLLECTION=common.getSubUser(subUserType, 'Import'), WHERE={'_id': importLogId}, VALUE={'status': 1, 'complete_import': time.time()})
    
    if(len(updateData) > 0):
        for updateD in updateData:
            mongodb.update(MONGO_COLLECTION=collection, WHERE={'acc_no': updateD['acc_no']}, VALUE=updateD)
        mongodb.batch_insert(MONGO_COLLECTION=common.getSubUser(subUserType, 'Trial_balance_report_result'), insert_data=updateData)
        mongodb.update(MONGO_COLLECTION=common.getSubUser(subUserType, 'Import'), WHERE={'_id': importLogId}, VALUE={'status': 1, 'complete_import': time.time()})

except Exception as e:
    log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')

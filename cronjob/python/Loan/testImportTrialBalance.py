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
collection = common.getSubUser(subUserType, 'Trial_balance_report')

try:
    modelColumns = []
    modelConverters = {}
    modelConverters1 = []
    modelPosition = {}
    modelPosition1 = []
    modelFormat = {}
    modelFormat1 = []
    converters = {}
    insertData = []
    errorData = []
    # today = date.today()
    today = datetime.strptime('20/11/2019', "%d/%m/%Y").date()
    day = today.day
    month = today.month
    year = today.year
    # fileName = "LIST_OF_ACCOUNT_IN_COLLECTION_" + str(year) + str(month) + str(day)
    fileName = "Trial_Balance_Report_Telling_Each_Account_Information_20191031.txt"
    sep = ','
    logDbName = "LO_Input_result_" + str(year) + str(month)

    if day == 1:
        mongodb.create_db(DB_NAME=logDbName)
        mongodbresult = Mongodb(logDbName)
    else:
        mongodbresult = Mongodb(logDbName)
    
    ftpLocalUrl = common.getDownloadFolder() + fileName

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
            'file_name'     : fileName,
            'file_path'     : ftpLocalUrl, 
            'source'        : 'ftp',
            'status'        : 2,
            'command'       : 'python3.6 ' + base_url + "cronjob/python/Loan/importListOfAccount.py > /dev/null &",
            'created_by'    : 'system'
        }
        importLogId = mongodb.insert(MONGO_COLLECTION=common.getSubUser(subUserType, 'Import'), insert_data=importLogInfo) 

    models = _mongodb.get(MONGO_COLLECTION='Model', WHERE={'collection': collection}, SORT=[('index', 1)], SELECT=['index', 'collection', 'field', 'type', 'sub_type'])
    for model in models:
        if 'sub_type' in model.keys():
            modelColumns.append(model['field'])
            modelConverters[model['field']] = model['type']
            modelConverters1.append(model['type'])
            subtype = json.loads(model['sub_type'])
            if 'format' in subtype.keys():
                modelFormat[model['field']] = subtype['format']
                modelFormat1.append(subtype['format'])
            else:
                modelFormat[model['field']] = ''
                modelFormat1.append('')

            if 'column' in subtype.keys():
                modelPosition[model['field']] = subtype['column']
                modelPosition1.append(subtype['column'])
            else:
                modelPosition[model['field']] = ''
                modelPosition1.append('')

    mongodb.remove_document(MONGO_COLLECTION=collection)

    with open(importLogInfo['file_path'], 'r', newline='\n', encoding='ISO-8859-1') as fin:
        csv_reader = csv.reader(fin, delimiter=' ', quotechar='"')
        for idx, rowRaw in enumerate(csv_reader):
            if len(rowRaw) > 0:
                row = list(filter(None, rowRaw))
                if len(row) > 1:
                    if isinstance(row[1], str) and len(row[1]) > 12 and row[1].isdigit():
                        result = True
                        temp = {}
                        for keyCell, cell in enumerate(row):
                            try:
                                temp[modelColumns[keyCell]] = common.convertDataType(data=cell, datatype=modelConverters1[keyCell], formatType=modelFormat1[keyCell])
                            except Exception as errorConvertType:
                                temp['type'] = modelConverters1[keyCell]
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
                            result = True
                    
    if(len(errorData) > 0):
        mongodbresult.remove_document(MONGO_COLLECTION=common.getSubUser(subUserType, ('Trial_balance_report_' + str(year) + str(month) + str(day))))
        mongodbresult.batch_insert(common.getSubUser(subUserType, ('Trial_balance_report_' + str(year) + str(month) + str(day))), errorData)
        mongodb.update(MONGO_COLLECTION=common.getSubUser(subUserType, 'Import'), WHERE={'_id': importLogId}, VALUE={'status': 0, 'complete_import': time.time()})
    else:
        if len(insertData) > 0:
            mongodb.batch_insert(MONGO_COLLECTION=collection, insert_data=insertData)
        mongodb.update(MONGO_COLLECTION=common.getSubUser(subUserType, 'Import'), WHERE={'_id': importLogId}, VALUE={'status': 1, 'complete_import': time.time()})
except Exception as e:
    log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
    pprint(str(e))

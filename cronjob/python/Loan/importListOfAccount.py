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

try:
    filename = 'LIST_OF_ACCOUNT_IN_COLLECTION_20190812.csv'
    collection = 'List_of_account_in_collection'
    mongodb = Mongodb("worldfone4xs")
    _mongodb = Mongodb("_worldfone4xs")
    excel = Excel()
    ftp = Ftp()
    common = Common()
    now = datetime.now()

    modelColumns = []
    modelConverters = {}
    insertData = []
    updateData = []

    ftp.connect()
    ftp.downLoadFile("/var/www/html/worldfone4xs_ibm/upload/loan/ftp/" + filename, filename)
    ftp.close()

    modelInfo = _mongodb.get(MONGO_COLLECTION='Model', WHERE={'collection': 'LO_' + collection, 'sub_type': {'$ne': None}}, SORT=[('index', 1)])
    
    for model in modelInfo:
        modelColumns.append(model['field'])
        modelConverters[model['field']] = model['type']
    
    importLogInfo = {
        'collection'    : "List_of_account_in_collection",
        'begin_import'  : time.time(),
        'file_name'     : filename,
        'file_path'     : "/var/www/html/worldfone4xs_ibm/upload/loan/ftp/" + filename,
        'source'        : 'ftp',
        'file_type'     : 'csv',
        'status'        : 2,
        'created_by'    : 'system'
    }

    importLogId = mongodb.insert(MONGO_COLLECTION='2_Import', insert_data=importLogInfo)

    listAccount = excel.testCSV(file_path=importLogInfo['file_path'], header=0, names=['account_no', 'phone', 'cus_name', 'type', 'curr', 'due_date', 'overdue_amt', 'cur_bal', 'overdue_date'], usecols=[1, 2, 3, 4, 5, 6, 7, 8, 9], sep='\t', encoding = "ISO-8859-1")
    listAccountData = listAccount.to_dict('records')
    listResult = []
    
    for accountData in listAccountData:
        if isinstance(accountData['account_no'], str) and len(accountData['account_no']) > 12 and accountData['account_no'].isdigit():
            if isinstance(accountData['due_date'], date):
                pprint(accountData['due_date'])
                date.strftime(accountData['due_date'])
            else:
                pprint(accountData['due_date'])
            # for key in accountData:
            #     if modelConverters[key] == 'timestamp':
            #         accountData[key] = common.convertDataType(data=accountData[key], datatype=modelConverters[key], formatType="%d/%m/%y")
            #     else:
            #         accountData[key] = common.convertDataType(data=accountData[key], datatype=modelConverters[key])
            # # pprint(accountData)
            # checkAccount = mongodb.getOne(MONGO_COLLECTION='2_List_of_account_in_collection', WHERE={'account_no': accountData['account_no']})
            # if(checkAccount == None):
            #     accountData['import_id'] = str(importLogId)
            #     accountData['created_by'] = 'system'
            #     accountData['created_at'] = time.time()
            #     insertData.append(accountData)
            # else:
            #     accountData['updated_import_id'] = str(importLogId)
            #     accountData['updated_by'] = 'system'
            #     accountData['updated_at'] = time.time()
            #     updateData.append(accountData)
    
    # if(len(insertData) > 0):
    #     mongodb.batch_insert(MONGO_COLLECTION='2_List_of_account_in_collection', insert_data=insertData)
    #     mongodb.batch_insert(MONGO_COLLECTION='2_List_of_account_in_collection_result', insert_data=insertData)
    #     mongodb.update(MONGO_COLLECTION='2_Import', WHERE={'_id': importLogId}, VALUE={'status': 1, 'complete_import': time.time()})
    
    # if(len(updateData) > 0):
    #     for updateD in updateData:
    #         mongodb.update(MONGO_COLLECTION='2_List_of_account_in_collection', WHERE={'account_no': updateD['account_no']}, VALUE=updateD)
    #     mongodb.batch_insert(MONGO_COLLECTION='2_List_of_account_in_collection_result', insert_data=insertData)
    #     mongodb.update(MONGO_COLLECTION='2_Import', WHERE={'_id': importLogId}, VALUE={'status': 1, 'complete_import': time.time()})

except Exception as e:
    log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')

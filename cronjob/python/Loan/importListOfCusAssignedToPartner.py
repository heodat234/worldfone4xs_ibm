#!/usr/bin/python3.6
# -*- coding: utf-8 -*-

import re
import ftplib
import calendar
import time
import sys
import os
import json
import traceback
from pprint import pprint
from datetime import datetime, timedelta
from datetime import date
from bson import ObjectId
from helper.ftp import Ftp
from helper.mongod import Mongodb
from helper.excel import Excel
from helper.jaccs import Config
from helper.common import Common

excel = Excel()
config = Config()
ftp = Ftp()
common = Common()
base_url = common.base_url()
wff_env = common.wff_env(base_url)
mongodb = Mongodb(MONGODB="worldfone4xs", WFF_ENV=wff_env)
_mongodb = Mongodb(MONGODB="_worldfone4xs", WFF_ENV=wff_env)
log = open(base_url + "cronjob/python/Loan/log/importListOfCusAssignedToPartner.txt","a")
now = datetime.now()
subUserType = 'LO'
collection = common.getSubUser(subUserType, 'Cus_assigned_partner')

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
    today = date.today()
    today = datetime.strptime('31/03/2020', "%d/%m/%Y").date()
    yesterday = today - timedelta(days=1)
    day = today.day
    month = today.month
    year = today.year
    fileName = "List of customers assigned to partners.xlsx"
    sep = ','
    logDbName = "LO_Input_result_" + str(year) + str(month)
    total = 0
    complete = 0

    if day == 1:
        mongodb.create_db(DB_NAME=logDbName)
        mongodbresult = Mongodb(logDbName, wff_env)
    else:
        mongodbresult = Mongodb(logDbName, wff_env)
    
    ftpLocalUrl = common.getDownloadFolder() + fileName

    try:
        sys.argv[1]
        importLogId = str(sys.argv[1])
        importLogInfo = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Import'), WHERE={'_id': ObjectId(sys.argv[1])})
    except Exception as SysArgvError:
        # ftp.connect(host=ftpConfig['host'], username=ftpConfig['username'], password=ftpConfig['password'])
        # ftp.downLoadFile(ftpLocalUrl, ftpInfo['filename'])
        # ftp.close()

        if not os.path.isfile(ftpLocalUrl):
            sys.exit()

        importLogInfo = {
            'collection'    : collection, 
            'begin_import'  : time.time(),
            'file_name'     : fileName,
            'file_path'     : ftpLocalUrl, 
            'source'        : 'ftp',
            'status'        : 2,
            'command'       : '/usr/local/bin/python3.6 ' + base_url + "cronjob/python/Loan/importListOfCusAssignedToPartner.py > /dev/null &",
            'created_by'    : 'system'
        }
        importLogId = mongodb.insert(MONGO_COLLECTION=common.getSubUser(subUserType, 'Import'), insert_data=importLogInfo) 
    
    models = _mongodb.get(MONGO_COLLECTION='Model', WHERE={'collection': collection}, SORT=[('index', 1)], SELECT=['index', 'collection', 'field', 'type', 'sub_type'], TAKE=1000)

    for model in list(models):
        modelColumns.append(model['field'])
        modelConverters[model['field']] = model['type']
        subtype = json.loads(model['sub_type'])
        if 'format' in subtype.keys():
            modelFormat[model['field']] = subtype['format']
        else:
            modelFormat[model['field']] = ''
    
    filenameExtension = fileName.split('.')

    if(filenameExtension[1] == 'csv'):
        inputDataRaw = excel.getDataCSV(file_path=importLogInfo['file_path'], dtype='object', sep=';', header=0, names=modelColumns)
    else:
        inputDataRaw = excel.getDataExcel(file_path=importLogInfo['file_path'], dtype='object', active_sheet='Sheet1', header=0, names=modelColumns, na_values='')
    
    inputData = inputDataRaw.to_dict('records')
    # pprint(inputData)
    
    insertData = []
    updateDate = []
    errorData = []

    # temp = {}
    countList = 0
    for idx, row in enumerate(inputData):
        total += 1
        temp = {}
        if row['CONTRACTNR'] not in ['', None]:
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
            
            temp['import_id'] = str(importLogId)
            
            if(result == False):
                errorData.append(temp)
            else:
                checkInfo = mongodb.getOne(MONGO_COLLECTION=collection, WHERE={'CONTRACTNR': temp['CONTRACTNR']})
                if checkInfo is not None:
                    temp['updated_by'] = 'system'
                    temp['updated_at'] = time.time()
                    updateDate.append(temp)
                else:
                    temp['created_by'] = 'system'
                    temp['created_at'] = time.time()
                    insertData.append(temp)
                result = True
                complete += 1

    if(len(errorData) > 0):
        mongodbresult.remove_document(MONGO_COLLECTION=common.getSubUser(subUserType, ('Cus_assigned_partner_' + str(year) + str(month) + str(day))))
        mongodbresult.batch_insert(common.getSubUser(subUserType, ('Cus_assigned_partner_' + str(year) + str(month) + str(day))), errorData)
        mongodb.update(MONGO_COLLECTION=common.getSubUser(subUserType, 'Import'), WHERE={'_id': importLogId}, VALUE={'status': 0, 'complete_import': time.time(), 'total': total, 'complete': complete})
    else:
        if len(insertData) > 0:
            mongodb.batch_insert(MONGO_COLLECTION=collection, insert_data=insertData)
        if len(updateDate) > 0:
            for updateD in updateDate:
                mongodb.update(MONGO_COLLECTION=collection, WHERE={'CONTRACTNR': updateD['CONTRACTNR']}, VALUE=updateD)
        mongodb.update(MONGO_COLLECTION=common.getSubUser(subUserType, 'Import'), WHERE={'_id': importLogId}, VALUE={'status': 1, 'complete_import': time.time(), 'total': total, 'complete': complete})
except Exception as e:
    log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
    print(traceback.format_exc())
    pprint(str(e))

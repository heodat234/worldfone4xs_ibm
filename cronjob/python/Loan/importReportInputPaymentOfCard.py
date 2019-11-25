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
log = open(base_url + "cronjob/python/Loan/log/importReportInputPaymentOfCard.txt","a")
logCheckTime = open(base_url + "cronjob/python/Loan/log/importcheckTime.txt","a")
now = datetime.now()
subUserType = 'LO'
collection = common.getSubUser(subUserType, 'Report_input_payment_of_card')

try:
    logCheckTime.write(str(time.time()))
    modelColumns = []
    modelConverters = {}
    modelPosition = {}
    modelFormat = {}
    converters = {}

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
            'command'       : 'python3.6 ' + base_url + "cronjob/python/Loan/importReportInputPaymentOfCard.py > /dev/null &",
            'created_by'    : 'system'
        }
        importLogId = mongodb.insert(MONGO_COLLECTION=common.getSubUser(subUserType, 'Import'), insert_data=importLogInfo) 

    models = _mongodb.get(MONGO_COLLECTION='Model', WHERE={'collection': collection}, SORT=[('index', 1)], SELECT=['index', 'collection', 'field', 'type', 'sub_type'], TAKE=40)

    for model in models:
        modelColumns.append(model['field'])
        modelConverters[model['field']] = model['type']
        subtype = json.loads(model['sub_type'])
        if 'format' in subtype.keys():
            modelFormat[model['field']] = subtype['format']
        else:
            modelFormat[model['field']] = ''

    filenameExtension = importLogInfo['file_name'].split('.')
    if filenameExtension[1] not in ['csv', 'xlsx']:
        os.rename(base_url + 'upload/ftp/loan/' + importLogInfo['file_name'], base_url + 'upload/ftp/loan/' + filenameExtension[0] + '.csv')
        dirs = os.listdir('/var/www/html/worldfone4xs_ibm/upload/ftp/loan/')
    filenameExtension[1] = 'csv'
    localFilePath = importLogInfo['file_path'].replace(importLogInfo['file_name'], filenameExtension[0] + '.' + 'csv')
    importLogInfo['file_path'] = base_url + 'upload/ftp/loan/' + filenameExtension[0] + '.csv'

    if ftpInfo['header'] == 'None':
        header = None
    else:
        header = [ int(x) for x in ftpInfo['header'] ]
    
    if(filenameExtension[1] == 'csv'):
        inputDataRaw = excel.getDataCSV(file_path=importLogInfo['file_path'], dtype=object, sep=ftpInfo['sep'], header=header, names=modelColumns, na_values='')
    else:
        inputDataRaw = excel.getDataExcel(file_path=importLogInfo['file_path'], header=header, names=modelColumns, na_values='')
        
    inputData = inputDataRaw.to_dict('records')
    pprint(inputData)
    
    sys.exit()

    insertData = []
    errorData = []

    temp = {}
    countList = 0
    for idx, row in enumerate(inputData):
        temp = {}
        if row['gl_pair_key'] not in ['', None] and row['ticket'] not in ['', None]:
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
                insertData.append(temp)
                result = True

    pprint(insertData)

    if(len(errorData) > 0):
        mongodb.batch_insert(common.getSubUser(subUserType, 'Report_input_payment_of_card_result'), errorData)
        mongodb.update(MONGO_COLLECTION=common.getSubUser(subUserType, 'Import'), WHERE={'_id': importLogId}, VALUE={'status': 0, 'complete_import': time.time()})
    else:
        if len(insertData) > 0:
            mongodb.batch_insert(MONGO_COLLECTION=collection, insert_data=insertData)
            mongodb.batch_insert(common.getSubUser(subUserType, 'Report_input_payment_of_card_result'), insert_data=insertData)
        mongodb.update(MONGO_COLLECTION=common.getSubUser(subUserType, 'Import'), WHERE={'_id': importLogId}, VALUE={'status': 1, 'complete_import': time.time()})
    logCheckTime.write('\n')
    logCheckTime.write(str(time.time()))
except Exception as e:
    log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')

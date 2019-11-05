#!/usr/bin/python3.6
# -*- coding: utf-8 -*-

import ftplib
import calendar
import time
import sys
import os
from pprint import pprint
from datetime import datetime
from datetime import date
from bson import ObjectId
from helper.ftp import Ftp
from helper.mongod import Mongodb
from helper.excel import Excel
from helper.jaccs import Config
from helper.common import Common

try:
    mongodb = Mongodb("worldfone4xs")
    _mongodb = Mongodb("_worldfone4xs")
    excel = Excel()
    config = Config()
    ftp = Ftp()
    common = Common()
    base_url = config.base_url()
    log = open(base_url + "cronjob/python/Telesales/importSCschedule.txt","a")
    now = datetime.now()
    subUserType = 'TS'
    collection = common.getSubUser(subUserType, 'Dealer')

    dealerColumns = []
    dealerConverters = {}
    insertData = []
    errorData = []

    ftpConfig = config.ftp_config()
    ftpInfo = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'ftp_config'), WHERE={'collection': collection})
    ftpLocalUrl = base_url + ftpInfo['locallink'] + ftpInfo['filename']

    ftp.connect(host=ftpConfig['host'], username=ftpConfig['username'], password=ftpConfig['password'])
    ftp.downLoadFile(ftpLocalUrl, ftpInfo['filename'])
    ftp.close()

    importLogInfo = {
        'collection'    : "Dealer",
        'begin_import'  : time.time(),
        'file_name'     : ftpInfo['filename'],
        'file_path'     : ftpLocalUrl,
        'source'        : 'ftp',
        'file_type'     : 'csv',
        'status'        : 2,
        'created_by'    : 'system'
    }
    importLogId = mongodb.insert(MONGO_COLLECTION=common.getSubUser(subUserType, 'Import'), insert_data=importLogInfo)

    modelsDealer = _mongodb.get(MONGO_COLLECTION='Model', WHERE={'collection': collection, 'sub_type': {'$ne': None}}, SORT=[('index', 1)])
    for model in modelsDealer:
        dealerColumns.append(model['field'])
        if(model['type'] == 'string'):
            dealerConverters[model['field']] = str

    filenameExtension = ftpInfo['filename'].split('.')
    if(filenameExtension[1] == 'csv'):
        dealers = excel.getDataCSV(file_path=importLogInfo['file_path'], header=0, names=dealerColumns, converters=dealerConverters)
    else:
        dealers = excel.getDataExcel(file_path=importLogInfo['file_path'], header=0, names=dealerColumns, converters=dealerConverters)
    
    dealerList = dealers.to_dict('records')

    mongodb.remove_document(MONGO_COLLECTION=collection)
    for key, value in enumerate(dealerList):
        result = True
        if(value['dealer_code'] == ''):
            value['error_cell'] = 'B' + (key + 2)
            value['type'] = 'string'
            value['error_mesg'] = 'Thiếu thông tin Mã quầy'
            result = False
        if(value['dealer_name'] == ''):
            value['error_cell'] = 'C' + (key + 2)
            value['type'] = 'string'
            value['error_mesg'] = 'Thiếu thông tin Tên quầy'
            result = False
        if(value['location'] == ''):
            value['error_cell'] = 'H' + (key + 2)
            value['type'] = 'string'
            value['error_mesg'] = 'Thiếu thông tin khu vực'
            result = False
        value['created_at'] = time.time()
        value['created_by'] = 'system'
        value['import_id'] = str(importLogId)
        if(result == True):
            value['result'] = 'success'
            insertData.append(value)
        else:
            value['result'] = 'error'
            errorData.append(value)
    
    if(len(errorData) > 0):
        mongodb.batch_insert(MONGO_COLLECTION=common.getSubUser(subUserType, 'Dealer_import_result'), insert_data=errorData)
        mongodb.update(MONGO_COLLECTION=common.getSubUser(subUserType, 'Import'), WHERE={'_id': importLogId}, VALUE={'status': 0})
    else:
        mongodb.batch_insert(MONGO_COLLECTION=collection, insert_data=insertData)
        mongodb.batch_insert(MONGO_COLLECTION=common.getSubUser(subUserType, 'Dealer_import_result'), insert_data=insertData)
        mongodb.update(MONGO_COLLECTION=common.getSubUser(subUserType, 'Import'), WHERE={'_id': importLogId}, VALUE={'status': 1})

except Exception as e:
    log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')

#!/usr/bin/python3.6
# -*- coding: utf-8 -*-

import ftplib
import calendar
import time
import sys
import os
import json
import csv
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
log = open(base_url + "cronjob/python/Loan/log/importlnjc05.txt","a")
now = datetime.now()
subUserType = 'LO'
collection = common.getSubUser(subUserType, 'LNJC05')

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
            'command'       : 'python3.6 ' + base_url + "cronjob/python/Loan/importLNJC05.py > /dev/null &",
            'created_by'    : 'system'
        }
        importLogId = mongodb.insert(MONGO_COLLECTION=common.getSubUser(subUserType, 'Import'), insert_data=importLogInfo)

    models = _mongodb.get(MONGO_COLLECTION='Model', WHERE={'collection': collection}, SORT=[('index', 1)], SELECT=['index', 'collection', 'field', 'type', 'sub_type'])
    # pprint(list(models))

    for model in models:
        modelColumns.append(model['field'])
        modelConverters[model['field']] = model['type']
        modelConverters1.append(model['type'])
        if 'sub_type' in model.keys():
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

    filenameExtension = ftpInfo['filename'].split('.')

    if len(filenameExtension) < 2:
        filenameExtension.append('txt')
    
    if ftpInfo['header'] == 'None':
        header = None
    else:
        header = [ int(x) for x in ftpInfo['header'] ]

    # mongodb.remove_document(MONGO_COLLECTION=collection)

    if filenameExtension[1] in ['csv', 'xlsx']:
        if(filenameExtension[1] == 'csv'):
            inputDataRaw = excel.getDataCSV(file_path=importLogInfo['file_path'], dtype=object, sep=ftpInfo['sep'], header=header, names=modelColumns, na_values='')
        else:
            inputDataRaw = excel.getDataExcel(file_path=importLogInfo['file_path'], header=header, names=modelColumns, na_values='')
        inputData = inputDataRaw.to_dict('records')
        temp = {}
        for idx, row in enumerate(inputData):
            temp = {}
            if row['account_number'] not in ['', None]:
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

    else:
        with open(importLogInfo['file_path'], 'r', newline='\n', encoding='ISO-8859-1') as fin:
            csv_reader = csv.reader(fin, delimiter=';', quotechar='"')
            for idx, row in enumerate(csv_reader):
                if len(row) > 5:
                    temp = {}
                    for keyCell, cell in enumerate(row):
                        if keyCell <= len(modelColumns) - 1:
                            try:
                                temp[modelColumns[keyCell]] = common.convertDataType(data=cell, datatype=modelConverters1[keyCell], formatType=modelFormat1[keyCell])
                                result = True
                            except Exception as errorConvertType:
                                temp['error_cell'] = modelPosition1[keyCell] + str(idx + 1)
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
                        temp['result'] = 'success'
                        insertData.append(temp)
                        result = True

    pprint(insertData)

    # if(len(errorData) > 0):
    #     mongodb.batch_insert(common.getSubUser(subUserType, 'LNJC05_result'), errorData)
    #     mongodb.update(MONGO_COLLECTION=common.getSubUser(subUserType, 'Import'), WHERE={'_id': importLogId}, VALUE={'status': 0, 'complete_import': time.time()})
    # else:
    #     if len(insertData) > 0:
    #         mongodb.batch_insert(MONGO_COLLECTION=collection, insert_data=insertData)
    #         mongodb.batch_insert(common.getSubUser(subUserType, 'LNJC05_result'), insert_data=insertData)
    #     mongodb.update(MONGO_COLLECTION=common.getSubUser(subUserType, 'Import'), WHERE={'_id': importLogId}, VALUE={'status': 1, 'complete_import': time.time()})

except Exception as e:
    log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
    pprint(str(e))

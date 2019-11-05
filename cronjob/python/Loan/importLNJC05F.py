#!/usr/bin/python3.6
# -*- coding: utf-8 -*-
# log = open("/var/www/html/worldfone4xs_ibm/cronjob/python/Loan/importLNJC05F.txt","a")

# import ftplib
# import calendar
# import time
# import sys
# import os
# sys.path.insert(1, '/var/www/html/worldfone4xs_ibm/cronjob/python')
# from ftp import Ftp
# from pprint import pprint
# from mongod import Mongodb
# from excel import Excel
# from datetime import datetime
# from datetime import date
# from bson import ObjectId
# from common import Common
# from dateutil.parser import parse
# from xlsxwriter.utility import xl_rowcol_to_cell

# try:
#    filename = 'LNJC05F'
#    ftpPath = "/var/www/html/worldfone4xs_ibm/upload/loan/ftp/"
#    localPath = "/var/www/html/worldfone4xs_ibm/upload/loan/ftp/"
#    collection = 'LO_LNJC05'
#    mongodb = Mongodb("worldfone4xs")
#    _mongodb = Mongodb("_worldfone4xs")
#    excel = Excel()
#    ftp = Ftp()
#    common = Common()
#    now = datetime.now()

#    modelColumns = []
#    insertData = []
#    errorData = []

#    ftp.connect()
#    ftp.downLoadFile(ftpPath + filename, filename)
#    ftp.close()

#    headers = _mongodb.get(MONGO_COLLECTION='Model', WHERE={"collection": collection,'sub_type':{'$exists': 'true'}}, SELECT=['index', 'field','type'], SORT=([('index', 1)]),TAKE=50)

#    importLogInfo = {
#         'collection'    : collection,
#         'begin_import'  : time.time(),
#         'file_name'     : filename,
#         'file_path'     : localPath + filename,
#         'source'        : 'ftp',
#         'file_type'     : 'csv',
#         'status'        : 2,
#         'created_by'    : 'system'
#    }

#    importLogId = mongodb.insert(MONGO_COLLECTION='LO_Import', insert_data=importLogInfo)
#    headers = list(headers)
#    with open(localPath + filename, 'r', newline='\n', encoding='ISO-8859-1') as fin:
#       key = 1
#       for line in fin:
#          rows = line.split(';')
#          temp = {}
#          if len(rows) > 1:
#             for idx,header in enumerate(headers):
#                if rows[idx] ==  "\r\n":
#                   continue
#                if str(rows[idx]) == 'nan':
#                   rows[idx] = ''
#                if header['type'] == 'int':
#                   try:
#                      value = int(rows[idx])
#                   except ValueError:
#                      err = {}
#                      err['cell'] =  xl_rowcol_to_cell(key, idx)
#                      err['type'] = 'int';
#                      errorData.append(err);
#                if header['type'] == 'double':
#                   try:
#                      if rows[idx] == '0':
#                         value = 0
#                      else:
#                         value = float(rows[idx])
#                   except ValueError:
#                      err = {}
#                      err['cell'] =  xl_rowcol_to_cell(key, idx)
#                      err['type'] = 'double';
#                      errorData.append(err);
#                if header['type'] == 'timestamp' and str(rows[idx]) != '':
#                   err = {}
#                   try:
#                      date = rows[idx][0:2] + '/' + rows[idx][2:4] + '/' + rows[idx][4:6]
#                      dt = parse(date)
#                      value = int(dt.timestamp())
#                   except Exception as e:
#                      err['cell'] =  xl_rowcol_to_cell(key, idx)
#                      err['type'] = 'date'
#                      errorData.append(err)
#                if header['type'] == 'timestamp' and str(rows[idx]) == '':
#                   value = ''
#                if header['type'] == 'phone':
#                   value = str(rows[idx])

#                if header['type'] == 'string':
#                   try:
#                      value_int   = int(rows[idx])
#                      value       = str(value_int)
#                   except ValueError:
#                      value       = str(rows[idx])
#                temp[header['field']]   = value

#             temp['created_at']       = int(time.time())
#             temp['created_by']       = 'system'
#             temp['import_id']       = str(importLogId)
#             insertData.append(temp)
#             key = key + 1


#    # pprint(insertData)
#    if(len(errorData) <= 0):

#       status = 1
#    else:
#       status = 0   
#    mongodb.remove_document(collection)
#    mongodb.batch_insert(MONGO_COLLECTION=collection, insert_data=insertData)
#    mongodb.update(MONGO_COLLECTION='LO_Import', WHERE={'_id': ObjectId(importLogId)}, VALUE={'complete_import': time.time(),'status': status,'error': errorData})
#    pprint(1)
# except Exception as e:
#    pprint(e)
#    log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')


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
        inputDataRaw = excel.getDataCSV(file_path=importLogInfo['file_path'], sep=',', header=None, names=modelColumns, encoding='ISO-8859-1', low_memory=False)
    else:
        inputDataRaw = excel.getDataExcel(file_path=importLogInfo['file_path'], header=None, names=modelColumns, na_values='', encoding='ISO-8859-1')

    inputData = inputDataRaw.to_dict('records')
    
    insertData = []
    updateDate = []
    errorData = []

    temp = {}
    countList = 0
    for idx, row in enumerate(inputData):
        temp = {}
        if row['account_number'] is not None and row['account_number'] is not '':
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

    if(len(errorData) > 0):
        mongodb.batch_insert(common.getSubUser(subUserType, 'LNJC05_result'), errorData)
    else:
        if len(insertData) > 0:
            mongodb.remove_document(MONGO_COLLECTION=collection)
            mongodb.batch_insert(MONGO_COLLECTION=collection, insert_data=insertData)
            mongodb.batch_insert(common.getSubUser(subUserType, 'LNJC05_result'), insert_data=insertData)
        
        # if len(updateDate) > 0:
        #     for updateD in updateDate:
        #         mongodb.update(MONGO_COLLECTION=collection, WHERE={'contract_no': updateD['contract_no']}, VALUE=updateD)
        #     mongodb.batch_insert(common.getSubUser(subUserType, 'SBV_result'), insert_data=updateDate)
    
    mongodb.update(MONGO_COLLECTION=common.getSubUser(subUserType, 'Import'), WHERE={'_id': importLogId}, VALUE={'status': 1, 'complete_import': time.time(),'error': errorData})

except Exception as e:
    log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')

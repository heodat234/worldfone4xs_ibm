#!/usr/bin/python3.6
# -*- coding: utf-8 -*-
log = open("/var/www/html/python/importSibs.txt","a")
import calendar
import time
import sys
from pprint import pprint
from mongod import Mongodb
from excel import Excel
from datetime import datetime
from datetime import date
from bson import ObjectId
from common import Common
log.write('14' + '\n')
mongodb = Mongodb("worldfone4xs")
_mongodb = Mongodb("_worldfone4xs")
common = Common()
excel = Excel()
now = datetime.now()

importLogId = sys.argv[1]
sibsColumns = []
sibsConverters = {}

try:
    importLogInfo = mongodb.getOne(MONGO_COLLECTION='2_Import', WHERE={'_id': ObjectId(importLogId)})

    modelsSibs = _mongodb.get(MONGO_COLLECTION='Model', WHERE={'collection': '2_Sibs'}, SORT=[('index', 1)], SELECT=['index', 'collection', 'field', 'type'])
    for model in modelsSibs:
        sibsColumns.append(model['field'])
        if(model['type'] == 'string'):
            sibsConverters[model['field']] = str

    zaccfs = excel.getDataCSV(file_path=importLogInfo['file_path'], header=0, names=sibsColumns, usecols=[5, 6, 7, 116, 122], converters=sibsConverters)
    zaccfList = zaccfs.to_dict('records')

    insertData = []
    updateData = []
    errorData = []

    temp = {}
    countList = 0
    for idx, zaccf in enumerate(zaccfList):
        if zaccf['account_no'] not in (None, '') and zaccf['cif'] not in (None, '') and zaccf['cus_name'] not in (None, ''):
            result = True
            checkSibs = mongodb.getOne(MONGO_COLLECTION='2_Sibs', WHERE={'account_no': zaccf['account_no']}, SELECT=['account_no'])
            zaccf['import_id'] = importLogId
            try:
                zaccf['advance'] = float(zaccf['advance'])
            except Exception as errorConvertDM:
                zaccf['error_cell'] = 'DM' + str(idx + 2)
                zaccf['type'] = 'number'
                zaccf['error_mesg'] = 'Sai kiểu dữ liệu nhập'
                zaccf['result'] = 'error'
                result = False

            try:
                zaccf['current_balance'] = float(zaccf['current_balance'])
            except Exception as errorConvertDS:
                zaccf['error_cell'] = 'DS' + str(idx + 2)
                zaccf['type'] = 'number'
                zaccf['error_mesg'] = 'Sai kiểu dữ liệu nhập'
                zaccf['result'] = 'error'
                result = False
            
            if(result == False):
                errorData.append(zaccf)
            else:
                if(checkSibs is None):
                    insertData.append(zaccf)
                else:
                    updateData.append(zaccf)
                zaccf['result'] = 'success'
                result = True
        else:
            continue

    if(len(errorData) > 0):
        mongodb.batch_insert("2_Sibs_result", errorData)
    else:
        if len(updateData) > 0:
            for upData in updateData:
                mongodb.update(MONGO_COLLECTION='2_Sibs', WHERE={'account_no': upData['account_no']}, VALUE=upData)
            mongodb.batch_insert("2_Sibs_result", updateData)
        if len(insertData) > 0:
            mongodb.batch_insert(MONGO_COLLECTION="2_Sibs", insert_data=insertData)
            mongodb.batch_insert("2_Sibs_result", insert_data=insertData)
    
    mongodb.update(MONGO_COLLECTION='2_Import', WHERE={'_id': ObjectId(importLogId)}, VALUE={'status': 1, 'complete_import': time.time()})
    pprint({'status': 1})
except Exception as e:
    log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
#!/usr/bin/python3.6
# -*- coding: utf-8 -*-
# log = open("/var/www/html/worldfone4xs_ibm/cronjob/python/Loan/importListOfAccount.txt","a")

import ftplib
import calendar
import time
import sys
import os
import csv
import json
from pprint import pprint
from datetime import datetime, timedelta
from datetime import date
from bson import ObjectId
from dateutil import parser
from helper.ftp import Ftp
from helper.mongod import Mongodb
from helper.excel import Excel
from helper.jaccs import Config
from helper.common import Common

try:
    excel = Excel()
    config = Config()
    ftp = Ftp()
    common = Common()
    base_url = common.base_url()
    wff_env = common.wff_env(base_url)
    mongodb = Mongodb(MONGODB="worldfone4xs", WFF_ENV=wff_env)
    _mongodb = Mongodb(MONGODB="_worldfone4xs", WFF_ENV=wff_env)
    log = open(base_url + "cronjob/python/Loan/log/importListOfAccountInCollection.txt","a")
    now = datetime.now()
    subUserType = 'LO'
    collection = common.getSubUser(subUserType, 'List_of_account_in_collection_01032020')

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
    today = datetime.strptime('01/03/2020', "%d/%m/%Y").date()
    yesterday = today - timedelta(days=1)
    day = today.day
    month = today.month
    year = today.year
    fileName = "LIST_OF_ACCOUNT_IN_COLLECTION_" + yesterday.strftime("%Y%m%d")
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
    pprint(ftpLocalUrl)

    try:
        sys.argv[1]
        importLogId = str(sys.argv[1])
        importLogInfo = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Import'), WHERE={'_id': ObjectId(sys.argv[1])})
    except Exception as SysArgvError:
        if not os.path.isfile(ftpLocalUrl):
            user_info = list(_mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'User'), SELECT={'extension'}))
            user = common.array_column(user_info, 'extension')
            notification = {
                'title'     : f'Import {fileName} error',
                'active'    : True,
                'icon'      : 'fa fa-exclamation-triangle',
                'color'     : 'text-warning',
                'content'   : f'Không có file import đầu ngày <b style="font-size: 15px">{ftpLocalUrl}</b>. Xin vui lòng thông báo cho bộ phận IT',
                'link'      : '/manage/data/import_file',
                'to'        : list(user),
                'notifyDate': datetime.utcnow(),
                'createdBy' : 'System',
                'createdAt' : time.time()
            }
            mongodb.insert(MONGO_COLLECTION=common.getSubUser(subUserType, 'Notification'), insert_data=notification)
            sys.exit()

        importLogInfo = {
            'collection'    : collection,
            'begin_import'  : time.time(),
            'file_name'     : fileName,
            'file_path'     : ftpLocalUrl,
            'source'        : 'ftp',
            'status'        : 2,
            'command'       : '/usr/local/bin/python3.6 ' + base_url + "cronjob/python/Loan/importListOfAccount.py > /dev/null &",
            'created_by'    : 'system'
        }
        importLogId = mongodb.insert(MONGO_COLLECTION=common.getSubUser(subUserType, 'Import'), insert_data=importLogInfo)

    models = _mongodb.get(MONGO_COLLECTION='Model', WHERE={'collection': common.getSubUser(subUserType, 'List_of_account_in_collection'), 'sub_type': {'$ne': None}}, SORT=[('index', 1)], SELECT=['index', 'collection', 'field', 'type', 'sub_type'], TAKE=1000)
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

    mongodb.remove_document(MONGO_COLLECTION=collection)

    with open(importLogInfo['file_path'], 'r', newline='\n', encoding='ISO-8859-1') as fin:
        csv_reader = csv.reader(fin, delimiter=' ', quotechar='"')
        result = True
        for rowRaw in csv_reader:
            if len(rowRaw) > 0:
                row = list(filter(None, rowRaw))
                if len(row) > 1:
                    if isinstance(row[1], str) and len(row[1]) > 12 and row[1].isdigit():
                        total += 1
                        temp = {}
                        try:
                            temp[modelColumns[0]] = common.convertDataType(data=row[1], datatype=modelConverters[modelColumns[0]], formatType=modelFormat[modelColumns[0]])
                            temp[modelColumns[1]] = common.convertDataType(data=row[2], datatype=modelConverters[modelColumns[1]], formatType=modelFormat[modelColumns[1]])
                            temp[modelColumns[-1]] = common.convertDataType(data=row[-1].replace("\r\n", ''), datatype=modelConverters[modelColumns[-1]], formatType=modelFormat[modelColumns[-1]])
                            temp[modelColumns[-2]] = common.convertDataType(data=row[-2], datatype=modelConverters[modelColumns[-2]], formatType=modelFormat[modelColumns[-2]])
                            temp[modelColumns[-3]] = common.convertDataType(data=row[-3], datatype=modelConverters[modelColumns[-3]], formatType=modelFormat[modelColumns[-3]])
                            temp[modelColumns[-4]] = common.convertDataType(data=row[-4], datatype=modelConverters[modelColumns[-4]], formatType=modelFormat[modelColumns[-4]])
                            temp[modelColumns[-5]] = common.convertDataType(data=row[-5], datatype=modelConverters[modelColumns[-5]], formatType=modelFormat[modelColumns[-5]])
                            temp[modelColumns[-6]] = common.convertDataType(data=row[-6], datatype=modelConverters[modelColumns[-6]], formatType=modelFormat[modelColumns[-6]])
                        except Exception as errorConvertType:
                            temp['error_mesg'] = 'Sai kiểu dữ liệu nhập'
                            temp['result'] = 'error'
                            result = False
                        listName = row[3:-6]
                        temp[modelColumns[2]] = ' '.join(listName)
                        temp['created_by'] = 'system'
                        temp['created_at'] = time.time()
                        temp['import_id'] = str(importLogId)

                        if result == False:
                            errorData.append(temp)
                        else:
                            insertData.append(temp)
                            result = True
                            complete += 1


    if(len(errorData) > 0):
        mongodbresult.remove_document(MONGO_COLLECTION=common.getSubUser(subUserType, ('LIST_OF_ACCOUNT_IN_COLLECTION_' + str(year) + str(month) + str(day))))
        mongodbresult.batch_insert(common.getSubUser(subUserType, ('LIST_OF_ACCOUNT_IN_COLLECTION_' + str(year) + str(month) + str(day))), errorData)
        mongodb.update(MONGO_COLLECTION=common.getSubUser(subUserType, 'Import'), WHERE={'_id': importLogId}, VALUE={'status': 0, 'complete_import': time.time(), 'total': total, 'complete': complete})
    else:
        if len(insertData) > 0:
            mongodb.batch_insert(MONGO_COLLECTION=collection, insert_data=insertData)
        mongodb.update(MONGO_COLLECTION=common.getSubUser(subUserType, 'Import'), WHERE={'_id': importLogId}, VALUE={'status': 1, 'complete_import': time.time(), 'total': total, 'complete': complete})
except Exception as e:
    log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
    pprint(str(e))

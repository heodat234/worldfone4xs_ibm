#!/usr/bin/python3.6
# -*- coding: utf-8 -*-

import sys
import os
import time
from datetime import datetime
from datetime import date
from xlsxwriter.utility import xl_rowcol_to_cell
from pprint import pprint
from bson import ObjectId
from time import mktime as mktime
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
log = open(base_url + "cronjob/python/Telesales/importSCschedule.txt","a")
now = datetime.now()
subUserType = 'TS'
collection = common.getSubUser(subUserType, 'Sc_schedule')

try:
    importLogId = sys.argv[1]
    insertData = []
    resultData = []
    errorData = []
    countRow = 0

    importLogInfo = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Import'), WHERE={'_id': ObjectId(importLogId)})

    lichLamViecSC = excel.getDataExcel(importLogInfo['file_path'], active_sheet='Sheet1', header=0, skiprows=[1], na_values="0", dtype=str)
    
    ngayTrucs = list(lichLamViecSC.columns.values)

    listLichLamViecSC = lichLamViecSC.values
    
    for key, listLichLamViec in enumerate(listLichLamViecSC):
        for idx, value in enumerate(listLichLamViec):
            temp = {}
            result = True
            countRow = countRow + 1
            if idx == 0:
                continue

            temp['import_id']       = str(importLogInfo['_id'])
            temp['created_by']      = importLogInfo['created_by']
            temp['created_at']      = time.time()
            try:
                temp['from_date']   = int(time.mktime(time.strptime(ngayTrucs[idx], "%d/%m/%Y")))
            except Exception as e:
                temp['result']      = 'error'
                temp['error_cell']  = xl_rowcol_to_cell(0, idx)
                temp['type']        = 'text'
                temp['error_mesg']  = 'Dòng ngày trực xin vui lòng chọn kiểu text'
                result = False

            if result == True:
                temp['dealer_code']     = listLichLamViec[0]
                temp['sc_code']         = list(value.split(';'))
                scField                 = 'sc' + str(temp['from_date'])
                temp['kendoGridField']  = scField
                temp['result']      = 'success'
                temp['from_date']   = int(time.mktime(time.strptime(ngayTrucs[idx], "%d/%m/%Y")))
                insertData.append(temp)
                resultData.append(temp)
            else:
                errorData.append(temp)
                resultData.append(temp)
    if len(errorData) > 0:
        resultImport = mongodb.batch_insert(common.getSubUser(subUserType, 'Sc_schedule_result'), errorData)
        mongodb.update(MONGO_COLLECTION=common.getSubUser(subUserType, 'Import'), WHERE={'_id': ObjectId(importLogId)}, VALUE={'status': 0, 'complete_import': time.time()})
    else:
        resultImport = mongodb.batch_insert(collection, insertData)
        resultImport = mongodb.batch_insert(common.getSubUser(subUserType, 'Sc_schedule_result'), resultData)
        mongodb.update(MONGO_COLLECTION=common.getSubUser(subUserType, 'Import'), WHERE={'_id': ObjectId(importLogId)}, VALUE={'status': 1, 'complete_import': time.time()})
except Exception as e:
    pprint(e)
    log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
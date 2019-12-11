#!/usr/bin/python3.6
# -*- coding: utf-8 -*-

import sys
import time
import os
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
    updateData = []
    countRow = 0
    today = date.today()
    # today = datetime.strptime('20/11/2019', "%d/%m/%Y").date()
    day = today.day
    month = today.month
    year = today.year
    fileName = "Lich_lam_viec_SC_10.xlsx"
    sep = ','
    logDbName = "TS_Input_result_" + str(year) + str(month)

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
        importLogInfo = {
            'collection'    : collection, 
            'begin_import'  : time.time(),
            'file_name'     : fileName,
            'file_path'     : ftpLocalUrl, 
            'source'        : 'ftp',
            'status'        : 2,
            'command'       : 'python3.6 ' + base_url + "cronjob/python/Loan/importSCSchedule_cron.py > /dev/null &",
            'created_by'    : 'system'
        }
        importLogId = mongodb.insert(MONGO_COLLECTION=common.getSubUser(subUserType, 'Import'), insert_data=importLogInfo)

    lichLamViecSC = excel.getDataExcel(file_path=(importLogInfo['file_path']), active_sheet='Sheet1', header=0, skiprows=[1], na_values="0", dtype=str)
    
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

            if result == False:
                errorData.append(temp)
            else:
                temp['dealer_code']     = listLichLamViec[0]
                temp['sc_code']         = list(value.split(';'))
                scField                 = 'sc' + str(temp['from_date'])
                temp['kendoGridField']  = scField
                temp['from_date']       = int(time.mktime(time.strptime(ngayTrucs[idx], "%d/%m/%Y")))
                insertData.append(temp)

    if len(errorData) > 0:
        mongodbresult.remove_document(MONGO_COLLECTION=common.getSubUser(subUserType, ('Sc_schedule_' + str(year) + str(month) + str(day))))
        mongodbresult.batch_insert(common.getSubUser(subUserType, ('Sc_schedule_' + str(year) + str(month) + str(day))), errorData)
        mongodb.update(MONGO_COLLECTION=common.getSubUser(subUserType, 'Import'), WHERE={'_id': importLogId}, VALUE={'status': 0, 'complete_import': time.time()})
    else:
        resultImport = mongodb.batch_insert(collection, insertData)
        mongodb.update(MONGO_COLLECTION=common.getSubUser(subUserType, 'Import'), WHERE={'_id': ObjectId(importLogId)}, VALUE={'status': 1, 'complete_import': time.time()})
except Exception as e:
    pprint(e)
    log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
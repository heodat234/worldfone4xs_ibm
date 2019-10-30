#!/usr/bin/python3.6
# -*- coding: utf-8 -*-

import sys
import time
import os
sys.path.insert(1, '/var/www/html/worldfone4xs_ibm/cronjob/python')
from excel import Excel
from mongod import Mongodb
from ftp import Ftp
from datetime import datetime
from datetime import date
from xlsxwriter.utility import xl_rowcol_to_cell
from pprint import pprint
from bson import ObjectId
from common import Common
from time import mktime as mktime

log = open("/var/www/html/worldfone4xs_ibm/cronjob/python/Telesales/importScSchedule.txt","a")
mongodb = Mongodb("worldfone4xs")
_mongodb = Mongodb("_worldfone4xs")
excel = Excel()
common = Common()
ftp = Ftp()
now = datetime.now()

try:
    filename = 'Lich_lam_viec_SC.xlsx'
    insertData = []
    resultData = []
    errorData = []
    countRow = 0

    ftp.connect()
    ftp.downLoadFile("/var/www/html/worldfone4xs_ibm/upload/csv/ftp/" + filename, filename)
    ftp.close()

    path, filename = os.path.split("/var/www/html/worldfone4xs_ibm/upload/csv/ftp/" + filename)

    importLogInfo = {
        'collection'    : "Sc_schedule",
        'begin_import'  : time.time(),
        'file_name'     : filename,
        'file_path'     : path + '/' + filename,
        'source'        : 'ftp',
        'status'        : 2,
        'created_by'    : 'system'
    }
    importLogId = mongodb.insert(MONGO_COLLECTION='2_Import', insert_data=importLogInfo)

    lichLamViecSC = excel.getDataExcel(file_path=(path + '/' + filename), active_sheet='Sheet1', header=0, skiprows=[1], na_values="0", dtype=str)
    
    ngayTrucs = list(lichLamViecSC.columns.values)

    listLichLamViecSC = lichLamViecSC.values
    
    for key, listLichLamViec in enumerate(listLichLamViecSC):
        for idx, value in enumerate(listLichLamViec):
            temp = {}
            result = True
            countRow = countRow + 1
            if idx == 0:
                continue
            if not isinstance(ngayTrucs[idx], str):
                temp['result']      = 'error'
                temp['error_cell']  = xl_rowcol_to_cell(key, idx)
                temp['type']        = 'text'
                temp['error_mesg']  = 'Dòng ngày trực xin vui lòng chọn kiểu text'
                result = False
            else:
                temp['result']      = 'success'
                temp['from_date']   = int(time.mktime(time.strptime(ngayTrucs[idx], "%d/%m/%Y")))
                result = True
            temp['import_id']       = str(importLogInfo)
            temp['created_by']      = importLogInfo['created_by']
            temp['created_at']      = time.time()
            temp['dealer_code']     = listLichLamViec[0]
            temp['sc_code']         = list(value.split(';'))
            scField                 = 'sc' + str(temp['from_date'])
            temp['kendoGridField']  = scField
            if result == True:
                insertData.append(temp)
                resultData.append(temp)
            else:
                errorData.append(temp)
                resultData.append(temp)
    # pprint(len(insertData))
    if len(errorData) > 0:
        resultImport = mongodb.batch_insert('2_Sc_schedule_result', errorData)
    else:
        resultImport = mongodb.batch_insert('2_Sc_schedule', insertData)
        resultImport = mongodb.batch_insert('2_Sc_schedule_result', resultData)
    mongodb.update(MONGO_COLLECTION='2_Import', WHERE={'_id': importLogId}, VALUE={'status': 1, 'complete_import': time.time()})
    pprint({'status': 1})
    # pprint(countRow)
except Exception as e:
    pprint(e)
    log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
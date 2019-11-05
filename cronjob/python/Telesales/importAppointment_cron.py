#!/usr/bin/python3.6
# -*- coding: utf-8 -*-

import ftplib
import calendar
import time
import sys
import os
from helper.ftp import Ftp
from helper.mongod import Mongodb
from helper.excel import Excel
from helper.jaccs import Config
from helper.common import Common
from pprint import pprint
from datetime import datetime
from datetime import date
from bson import ObjectId

try:
    mongodb = Mongodb("worldfone4xs")
    _mongodb = Mongodb("_worldfone4xs")
    excel = Excel()
    config = Config()
    ftp = Ftp()
    common = Common()
    base_url = config.base_url()
    log = open(base_url + "cronjob/python/Telesales/importAppointment.txt","a")
    now = datetime.now()
    subUserType = 'TS'
    collection = common.getSubUser(subUserType, 'Appointment')
    
    appointmentColumns = []
    appointmentConverters = {}
    insertData = []
    errorData = []
    updateData = []

    ftpConfig = config.ftp_config()
    ftpInfo = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'ftp_config'), WHERE={'collection': collection})
    ftpLocalUrl = base_url + ftpInfo['locallink'] + ftpInfo['filename']

    ftp.connect(host=ftpConfig['host'], username=ftpConfig['username'], password=ftpConfig['password'])
    ftp.downLoadFile(ftpLocalUrl, ftpInfo['filename'])
    ftp.close()

    importLogInfo = {
        'collection'    : "Appointment",
        'begin_import'  : time.time(),
        'file_name'     : ftpInfo['filename'],
        'file_path'     : ftpLocalUrl,
        'source'        : 'ftp',
        'file_type'     : 'csv',
        'status'        : 2,
        'created_by'    : 'system'
    }
    importLogId = mongodb.insert(MONGO_COLLECTION=common.getSubUser(subUserType, 'Import'), insert_data=importLogInfo)

    modelsAppointment = _mongodb.get(MONGO_COLLECTION='Model', WHERE={'collection': common.getSubUser(subUserType, 'Appointment'), 'sub_type': {'$ne': None}}, SORT=[('index', 1)])
    for model in modelsAppointment:
        appointmentColumns.append(model['field'])
        if(model['type'] == 'string'):
            appointmentConverters[model['field']] = str

    filenameExtension = ftpInfo['filename'].split('.')
    if(filenameExtension[1] == 'csv'):
        appointments = excel.getDataCSV(file_path=importLogInfo['file_path'], header=0, names=appointmentColumns, converters=appointmentConverters)
    else:
        appointments = excel.getDataExcel(file_path=importLogInfo['file_path'], header=0, names=appointmentColumns, converters=appointmentConverters)
    
    appointmentList = appointments.to_dict('records')

    for key, value in enumerate(appointmentList):
        result = True
        if(result == True):
            value['result'] = 'success'
            checkAppointment = mongodb.get(MONGO_COLLECTION=collection, WHERE={'cif': value['cif']})
            if(checkAppointment.count() > 0):
                value['updated_at'] = time.time()
                value['updated_by'] = 'system'
                value['update_import_id'] = str(importLogId)
                updateData.append(value)
            else:
                value['created_at'] = time.time()
                value['created_by'] = 'system'
                value['import_id'] = str(importLogId)
                insertData.append(value)
        else:
            value['result'] = 'error'
            errorData.append(value)

    if(len(errorData) > 0):
        mongodb.batch_insert(MONGO_COLLECTION=common.getSubUser(subUserType, 'Appointment_import_result'), insert_data=errorData)
        mongodb.update(MONGO_COLLECTION=common.getSubUser(subUserType, 'Import'), WHERE={'_id': importLogId}, VALUE={'status': 0})
    else:
        if(len(updateData) > 0):
            for updateRow in updateData:
                mongodb.update(MONGO_COLLECTION=collection, WHERE={'cif': updateRow['cif']}, VALUE=updateRow)
            mongodb.batch_insert(MONGO_COLLECTION=common.getSubUser(subUserType, 'Appointment_import_result'), insert_data=updateData)
            mongodb.update(MONGO_COLLECTION=common.getSubUser(subUserType, 'Import'), WHERE={'_id': importLogId}, VALUE={'status': 0})
        
        if(len(insertData) > 0):
            mongodb.batch_insert(MONGO_COLLECTION=collection, insert_data=insertData)
            mongodb.batch_insert(MONGO_COLLECTION=common.getSubUser(subUserType, 'Appointment_import_result'), insert_data=insertData)
            mongodb.update(MONGO_COLLECTION=common.getSubUser(subUserType, 'Import'), WHERE={'_id': importLogId}, VALUE={'status': 1})

except Exception as e:
    log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')

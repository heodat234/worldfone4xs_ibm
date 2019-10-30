#!/usr/bin/python3.6
# -*- coding: utf-8 -*-
log = open("/var/www/html/worldfone4xs_ibm/cronjob/python/Telesales/importAppointment.txt","a")

import ftplib
import calendar
import time
import sys
import os
sys.path.insert(1, '/var/www/html/worldfone4xs_ibm/cronjob/python')
from ftp import Ftp
from pprint import pprint
from mongod import Mongodb
from excel import Excel
from datetime import datetime
from datetime import date
from bson import ObjectId

try:
    filename = 'Appointment.xlsx'
    mongodb = Mongodb("worldfone4xs")
    _mongodb = Mongodb("_worldfone4xs")
    excel = Excel()
    ftp = Ftp()
    now = datetime.now()

    appointmentColumns = []
    appointmentConverters = {}
    insertData = []
    errorData = []
    updateData = []

    ftp.connect()
    ftp.downLoadFile("/var/www/html/worldfone4xs_ibm/upload/csv/ftp/" + filename, filename)
    ftp.close()

    path, filename = os.path.split("/var/www/html/worldfone4xs_ibm/upload/csv/ftp/" + filename)

    importLogInfo = {
        'collection'    : "Appointment",
        'begin_import'  : time.time(),
        'file_name'     : filename,
        'file_path'     : path + '/' + filename,
        'source'        : 'ftp',
        'file_type'     : 'csv',
        'status'        : 2,
        'created_by'    : 'system'
    }
    importLogId = mongodb.insert(MONGO_COLLECTION='2_Import', insert_data=importLogInfo)
    # pprint(str(importLogId))

    modelsAppointment = _mongodb.get(MONGO_COLLECTION='Model', WHERE={'collection': '2_Appointment', 'sub_type': {'$ne': None}}, SORT=[('index', 1)])
    for model in modelsAppointment:
        appointmentColumns.append(model['field'])
        if(model['type'] == 'string'):
            appointmentConverters[model['field']] = str

    filenameExtension = filename.split('.')
    if(filenameExtension[1] == 'csv'):
        appointments = excel.getDataCSV(file_path=importLogInfo['file_path'], header=0, names=appointmentColumns, converters=appointmentConverters)
    else:
        appointments = excel.getDataExcel(file_path=importLogInfo['file_path'], header=0, names=appointmentColumns, converters=appointmentConverters)
    
    appointmentList = appointments.to_dict('records')

    for key, value in enumerate(appointmentList):
        result = True
        if(result == True):
            value['result'] = 'success'
            checkAppointment = mongodb.get(MONGO_COLLECTION='2_Appointment', WHERE={'cif': value['cif']})
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
    
    # pprint(insertData)
    if(len(errorData) > 0):
        mongodb.batch_insert(MONGO_COLLECTION='2_Appointment_import_result', insert_data=errorData)
        mongodb.update(MONGO_COLLECTION='2_Import', WHERE={'_id': importLogId}, VALUE={'status': 0})
    else:
        if(len(updateData) > 0):
            for updateRow in updateData:
                mongodb.update(MONGO_COLLECTION='2_Appointment', WHERE={'cif': updateRow['cif']}, VALUE=updateRow)
            mongodb.batch_insert(MONGO_COLLECTION='2_Appointment_import_result', insert_data=updateData)
            mongodb.update(MONGO_COLLECTION='2_Import', WHERE={'_id': importLogId}, VALUE={'status': 0})
        
        if(len(insertData) > 0):
            mongodb.batch_insert(MONGO_COLLECTION='2_Appointment', insert_data=insertData)
            mongodb.batch_insert(MONGO_COLLECTION='2_Appointment_import_result', insert_data=insertData)
            mongodb.update(MONGO_COLLECTION='2_Import', WHERE={'_id': importLogId}, VALUE={'status': 1})

except Exception as e:
    log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')

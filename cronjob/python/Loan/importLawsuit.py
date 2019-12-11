#!/usr/bin/python3.6
# -*- coding: utf-8 -*-
log         = open("/var/www/html/worldfone4xs_ibm/cronjob/python/Loan/importLawsuit.txt","a")
import sys
import os
sys.path.insert(1, '/var/www/html/worldfone4xs_ibm/cronjob/python')
import calendar
import time
import ntpath
import json
from mongod import Mongodb
from excel import Excel
from datetime import datetime
from datetime import date
from xlsxwriter.utility import xl_rowcol_to_cell
from pprint import pprint
from bson import ObjectId
from common import Common
from helper.jaccs import Config
from dateutil.parser import parse

excel       = Excel()
common      = Common()
config      = Config()
base_url    = common.base_url()
wff_env     = common.wff_env(base_url)
mongodb     = Mongodb(MONGODB="worldfone4xs", WFF_ENV=wff_env)
_mongodb    = Mongodb(MONGODB="_worldfone4xs", WFF_ENV=wff_env)
now         = datetime.now()
subUserType = 'LO'
collection = common.getSubUser(subUserType, 'Lawsuit')

try:
   insertData  = []
   resultData  = []
   errorData   = []

   now         = datetime.now()
   log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': Start Import' + '\n')

   ftpInfo = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'ftp_config'), WHERE={'collection': collection})

   ftpConfig = config.ftp_config()
   ftpLocalUrl = common.getDownloadFolder() + ftpInfo['filename']

   try:
      sys.argv[1]
      importLogId = str(sys.argv[1])
      importLogInfo = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Import'), WHERE={'_id': ObjectId(sys.argv[1])})
   except Exception as SysArgvError:
      importLogInfo = {
         'collection'    : collection, 
         'begin_import'  : time.time(),
         'file_name'     : ftpInfo['filename'],
         'file_path'     : ftpLocalUrl, 
         'source'        : 'ftp',
         'status'        : 2,
         'command'       : 'python3.6 ' + base_url + "cronjob/python/Loan/importLawsuit.py > /dev/null &",
         'created_by'    : 'system'
      }
      importLogId = mongodb.insert(MONGO_COLLECTION=common.getSubUser(subUserType, 'Import'), insert_data=importLogInfo) 
    
   models = _mongodb.get(MONGO_COLLECTION='Model', WHERE={"collection": collection,'sub_type':{'$exists': 'true'}}, SELECT=['index', 'field','type'], SORT=([('index', 1)]),TAKE=50)
   for model in models:
      modelColumns.append(model['field'])
      modelConverters[model['field']] = model['type']
      subtype = json.loads(model['sub_type'])
      if 'format' in subtype.keys():
         modelFormat[model['field']] = subtype['format']
      else:
         modelFormat[model['field']] = ''
    
   filenameExtension = ftpInfo['filename'].split('.')

   if ftpInfo['header'] == 'None':
     header = None
   else:
     header = [ int(x) for x in ftpInfo['header'] ]

   if(filenameExtension[1] == 'csv'):
     inputDataRaw = excel.getDataCSV(file_path=importLogInfo['file_path'], dtype='object', sep=ftpInfo['sep'], header=header, names=modelColumns)
   else:
     inputDataRaw = excel.getDataExcel(file_path=importLogInfo['file_path'], dtype='object', active_sheet=ftpInfo['sheet'], header=header, names=modelColumns, na_values='')

   inputData = inputDataRaw.to_dict('records')
   # pprint(inputData)
    
   insertData = []
   updateDate = []
   errorData = []

   temp = {}
   countList = 0
   for idx, row in enumerate(inputData):
      temp = {}
      if row['LIC_NO'] not in ['', None] and row['ACCTNO'] not in ['', None]:
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
      mongodb.batch_insert(common.getSubUser(subUserType, 'Lawsuit_result'), errorData)
      mongodb.update(MONGO_COLLECTION=common.getSubUser(subUserType, 'Import'), WHERE={'_id': importLogId}, VALUE={'status': 0, 'complete_import': time.time()})
   else:
      if len(insertData) > 0:
         mongodb.batch_insert(MONGO_COLLECTION=collection, insert_data=insertData)
         mongodb.batch_insert(common.getSubUser(subUserType, 'Lawsuit_result'), insert_data=insertData)
      mongodb.update(MONGO_COLLECTION=common.getSubUser(subUserType, 'Import'), WHERE={'_id': importLogId}, VALUE={'status': 1, 'complete_import': time.time()})
   
   now_end         = datetime.now()
   log.write(now_end.strftime("%d/%m/%Y, %H:%M:%S") + ': End Import' + '\n')
   pprint({'status': status})

except Exception as e:
   log.write(datetime.now().strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
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
from dateutil.parser import parse

mongodb     = Mongodb("worldfone4xs")
_mongodb    = Mongodb("_worldfone4xs")
excel       = Excel()
common      = Common()
now         = datetime.now()

try:
   importLogId = sys.argv[1]
   collection  = sys.argv[2]
   extension   = sys.argv[3]
   insertData  = []
   resultData  = []
   errorData   = []

   now         = datetime.now()
   log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': Start Import' + '\n')

   importLogInfo = mongodb.getOne(MONGO_COLLECTION='LO_Import', WHERE={'_id': ObjectId(importLogId)})
   headers = _mongodb.get(MONGO_COLLECTION='Model', WHERE={"collection": collection,'sub_type':{'$exists': 'true'}}, SELECT=['index', 'field','type'], SORT=([('index', 1)]),TAKE=50)
   headers = list(headers)
   headers.insert(0, { 'field': 'stt', 'type': 'int'})
   # print(headers)
   file_path = importLogInfo['file_path']
   data = excel.getDataExcel(file_path=file_path,active_sheet='Sheet2', header=0, skiprows=[1], dtype=str)
   dataLawsuit = data.values
   # print(dataLawsuit)
   for key,listCol in enumerate(dataLawsuit):
      temp = {}
      value = ''
      for idx,header in enumerate(headers):
         if header['field'] == 'stt':
            continue
         if str(dataLawsuit[key][idx]) == 'nan':
            dataLawsuit[key][idx] = ''
         if header['type'] == 'int':
            try:
               value = int(dataLawsuit[key][idx])
            except ValueError:
               err = {}
               err['cell'] =  xl_rowcol_to_cell(key, idx)
               err['type'] = 'int';
               errorData.append(err);
         if header['type'] == 'double':
            try:
               if dataLawsuit[key][idx] == '0':
                  value = 0
               else:
                  value = float(dataLawsuit[key][idx])
            except ValueError:
               err = {}
               err['cell'] =  xl_rowcol_to_cell(key, idx)
               err['type'] = 'double';
               errorData.append(err);
         if header['type'] == 'timestamp' and str(dataLawsuit[key][idx]) != '':
            err = {}
            try:
               dt = parse(dataLawsuit[key][idx])
               value = int(dt.timestamp())
            except Exception as e:
               err['cell'] =  xl_rowcol_to_cell(key, idx)
               err['type'] = 'date'
               errorData.append(err)
         if header['type'] == 'timestamp' and str(dataLawsuit[key][idx]) == '':
            value = ''
         if header['type'] == 'phone':
            value = str(dataLawsuit[key][idx])

         if header['type'] == 'string':
            try:
               value_int   = int(dataLawsuit[key][idx])
               value       = str(value_int)
            except ValueError:
               value       = str(dataLawsuit[key][idx])
            
         
         temp[header['field']]   = value
         temp['import_id']       = importLogId

         temp['created_at']       = int(time.time())
         temp['created_by']       = extension

      insertData.append(temp)
      
   if len(errorData) <= 0:
      # mongodb.remove_document(collection)
      resultImport = mongodb.batch_insert(collection, insertData)
      status = 1
   else:
      status = 0
      log.write( str(errorData) + '\n')
      
   mongodb.update(MONGO_COLLECTION='LO_Import', WHERE={'_id': ObjectId(importLogId)}, VALUE={'complete_import': time.time(),'status': status,'error': errorData})

   now_end         = datetime.now()
   log.write(now_end.strftime("%d/%m/%Y, %H:%M:%S") + ': End Import' + '\n')
   pprint({'status': status})

except Exception as e:
   log.write(datetime.now().strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
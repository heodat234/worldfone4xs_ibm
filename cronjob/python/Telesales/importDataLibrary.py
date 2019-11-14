#!/usr/bin/python3.6
# -*- coding: utf-8 -*-
import sys
import os
# sys.path.insert(1, '/var/www/html/worldfone4xs_ibm/cronjob/python')
import calendar
import time
import ntpath
import json
from datetime import datetime
from datetime import date
from xlsxwriter.utility import xl_rowcol_to_cell
from pprint import pprint
from bson import ObjectId
from helper.common import Common
from helper.mongod import Mongodb
from helper.excel import Excel
from dateutil.parser import parse

mongodb     = Mongodb("worldfone4xs")
_mongodb    = Mongodb("_worldfone4xs")
excel       = Excel()
common      = Common()
now         = datetime.now()
log         = open("/var/www/html/worldfone4xs_ibm/cronjob/python/Telesales/importDataLibrary.txt","a")

try:
   importLogId = sys.argv[1]
   collection  = sys.argv[2]
   extension   = sys.argv[3]
   insertData  = []
   resultData  = []
   errorData   = []

   now         = datetime.now()
   log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': Start Import' + '\n')

   importLogInfo = mongodb.getOne(MONGO_COLLECTION='TS_Import', WHERE={'_id': ObjectId(importLogId)})
   headers = _mongodb.get(MONGO_COLLECTION='Model', WHERE={"collection": collection}, SELECT=['index', 'field','type'], SORT=([('index', 1)]))
   headers = list(headers)

   file_path = importLogInfo['file_path']
   dataLibrary = excel.getDataCSV(file_path=file_path,header=0, names=None, index_col=None, usecols=None, dtype=None, converters=None, skiprows=None, na_values=None, encoding='latin-1')
   listDataLibrary = dataLibrary.values
   for key,listCol in enumerate(listDataLibrary):
      temp = {}
      checkErr = False
      # log.write(str(key) + '\n')
      for idx,header in enumerate(headers):
         if str(listDataLibrary[key][idx]) == 'nan':
            listDataLibrary[key][idx] = ''
         if header['type'] == 'int':
            try:
               value = int(listDataLibrary[key][idx])
            except ValueError:
               err = {}
               err['cell'] =  xl_rowcol_to_cell(key, idx)
               err['type'] = 'int';
               errorData.append(err);
               checkErr = True
         if not isinstance(listDataLibrary[key][idx], float) and header['type'] == 'double':
            err = {}
            err['cell'] =  xl_rowcol_to_cell(key, idx)
            err['type'] = 'double';
            errorData.append(err);
            checkErr = True
         else:
            value = listDataLibrary[key][idx]
         if header['type'] == 'timestamp' and str(listDataLibrary[key][idx]) != '':
            err = {}
            try:
               dt = parse(listDataLibrary[key][idx])
               value = int(dt.timestamp())
            except Exception as e:
               err['cell'] =  xl_rowcol_to_cell(key, idx)
               err['type'] = 'date'
               errorData.append(err)
               checkErr = True
         if header['type'] == 'phone':
            value = str(listDataLibrary[key][idx])

         if header['type'] == 'string':
            try:
               value_int   = int(listDataLibrary[key][idx])
               value       = str(value_int)
            except ValueError:
               value       = str(listDataLibrary[key][idx])
            
         if header['field'] == 'assign' and value != '':
            value = str(listDataLibrary[key][idx])
            temp['createdBy']  = 'Byfixed-Import'
         if header['field'] == 'assign' and value == '':
            temp['createdBy']  = ''

         temp[header['field']]   = value
         temp['id_import']       = importLogId

         temp['createdAt']       = int(time.time())
         temp['updatedAt']       = int(time.time())
         temp['updatedBy']       = extension

      if checkErr == False:
         try:
            mongodb.update(MONGO_COLLECTION=collection, WHERE={'cif':temp['cif']}, VALUE=temp)
         except Exception as e:
            now_log         = datetime.now()
            log.write(now_log.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
      # insertData.append(temp)
      
   if len(errorData) <= 0:
      status = 1
   else:
      status = 0
      log.write( str(errorData) + '\n')
      
   mongodb.update(MONGO_COLLECTION='TS_Import', WHERE={'_id': ObjectId(importLogId)}, VALUE={'complete_import': time.time(),'status': status,'error': errorData})

   now_end         = datetime.now()
   log.write(now_end.strftime("%d/%m/%Y, %H:%M:%S") + ': End Import' + '\n')
   pprint({'status': status})

except Exception as e:
   pprint(e)
   log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
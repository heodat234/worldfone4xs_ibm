#!/usr/bin/python3.6
# -*- coding: utf-8 -*-
import sys
import os
import calendar
import time
import ntpath
import json
from datetime import datetime
from datetime import date
# from xlsxwriter.utility import xl_rowcol_to_cell
from pprint import pprint
from bson import ObjectId
from helper.common import Common
from helper.mongod import Mongodb
from helper.excel import Excel
from helper.jaccs import Config
from dateutil.parser import parse
import os.path
from os import path

mongodb     = Mongodb("worldfone4xs")
_mongodb    = Mongodb("_worldfone4xs")
excel       = Excel()
common      = Common()
config      = Config()
base_url    = config.base_url()
subUserType = 'TS'
collection  = common.getSubUser(subUserType, 'Datalibrary')
now         = datetime.now()
# log         = open(base_url + "cronjob/python/Telesales/importDataLibrary.txt","a")
now         = datetime.now()
# log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': Start Import' + '\n')
try:

   try:
      sys.argv[1]
      importLogId = str(sys.argv[1])
      importLogInfo = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Import'), WHERE={'_id': ObjectId(importLogId)})
   except Exception as SysArgvError:
      ftpInfo     = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'ftp_config'), WHERE={'collection': collection})
      ftpConfig   = config.ftp_config()
      ftpLocalUrl = common.getDownloadFolder() + ftpInfo['filename']

      if path.exists(ftpLocalUrl) == False:
         sys.exit()

      importLogInfo = {
         'collection'    : 'Datalibrary', 
         'begin_import'  : time.time(),
         'file_name'     : ftpInfo['filename'],
         'file_path'     : ftpLocalUrl, 
         'source'        : 'ftp',
         'status'        : 2,
         'command'       : '/usr/local/bin/python3.6 ' + base_url + "cronjob/python/Loan/importDataLibrary.py > /dev/null &",
         'created_by'    : 'system'
      }
      importLogId = mongodb.insert(MONGO_COLLECTION=common.getSubUser(subUserType, 'Import'), insert_data=importLogInfo) 
    
   insertData  = []
   resultData  = []
   errorData   = []

   headers = _mongodb.get(MONGO_COLLECTION='Model', WHERE={"collection": collection}, SELECT=['index', 'field','type'], SORT=([('index', 1)]))
   headers = list(headers)

   file_path = importLogInfo['file_path']
   dataLibrary = excel.getDataCSV(file_path=file_path,header=0, names=None, index_col=None, usecols=None, dtype=object, converters=None, skiprows=None, na_values=None, encoding='latin-1')
   listDataLibrary = dataLibrary.values
   for key,listCol in enumerate(listDataLibrary):
      temp = {}
      checkErr = False
      for idx,header in enumerate(headers):
         err = {}
         if header['index'] == 23:
            continue;
         if str(listDataLibrary[key][idx]) == 'nan':
            listDataLibrary[key][idx] = ''
         if header['type'] == 'int':
            try:
               value = int(listDataLibrary[key][idx])
            except ValueError:
               # err['cell'] =  xl_rowcol_to_cell(key, idx)
               err['cell'] =  str(key) + ' ' + str(idx)
               err['type'] = 'int';
               errorData.append(err);
               checkErr = True
         if header['type'] == 'double':
            try:
               value = float(listDataLibrary[key][idx])
            except Exception as e:
               # err['cell'] =  xl_rowcol_to_cell(key, idx)
               err['cell'] =  str(key) + ' ' + str(idx)
               err['type'] = 'double';
               errorData.append(err);
               checkErr = True

         if header['type'] == 'timestamp' and str(listDataLibrary[key][idx]) != '':
            try:
               dt = parse(listDataLibrary[key][idx])
               value = int(dt.timestamp())
            except Exception as e:
               # err['cell'] =  xl_rowcol_to_cell(key, idx)
               err['cell'] =  str(key) + ' ' + str(idx)
               err['type'] = 'date'
               errorData.append(err)
               checkErr = True

         if header['type'] == 'name' or header['type'] == 'phone':
            value   = str(listDataLibrary[key][idx])

         if header['type'] == 'string':
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
         temp['updatedBy']       = 'system'

      if checkErr == False:
         try:
            mongodb.update(MONGO_COLLECTION=collection, WHERE={'cif':temp['cif']}, VALUE=temp)
         except Exception as e:
            now_log         = datetime.now()
            # log.write(now_log.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(err) + '\n')
      
   if len(errorData) <= 0:
      status = 1
   else:
      status = 0
      # log.write( str(errorData) + '\n')
      
   mongodb.update(MONGO_COLLECTION='TS_Import', WHERE={'_id': ObjectId(importLogId)}, VALUE={'complete_import': time.time(),'status': status,'error': errorData})

   now_end         = datetime.now()
   # log.write(now_end.strftime("%d/%m/%Y, %H:%M:%S") + ': End Import' + '\n')
   pprint({'status': status})

except Exception as e:
   pprint(e)
   # log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
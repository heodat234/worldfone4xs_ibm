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
from xlsxwriter.utility import xl_rowcol_to_cell
from pprint import pprint
from bson import ObjectId
from helper.common import Common
from helper.mongod import Mongodb
from helper.excel import Excel
from helper.jaccs import Config
from dateutil.parser import parse

mongodb     = Mongodb("worldfone4xs")
_mongodb    = Mongodb("_worldfone4xs")
excel       = Excel()
common      = Common()
config      = Config()
base_url    = config.base_url()
subUserType = 'TS'
collection  = common.getSubUser(subUserType, 'Telesalelist')
log         = open("/var/www/html/worldfone4xs_ibm/cronjob/python/Telesales/log/importTelesale.txt","a+")
now         = datetime.now()
log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': Start Import' + '\n')
try:
   

   try:
      sys.argv[1]
      importLogId = str(sys.argv[1])
      importLogInfo = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Import'), WHERE={'_id': ObjectId(importLogId)})
   except Exception as SysArgvError:
      ftpInfo     = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'ftp_config'), WHERE={'collection': collection})
      ftpConfig   = config.ftp_config()
      ftpLocalUrl = common.getDownloadFolder() + ftpInfo['filename']
      importLogInfo = {
         'collection'    : collection, 
         'begin_import'  : time.time(),
         'file_name'     : ftpInfo['filename'],
         'file_path'     : ftpLocalUrl, 
         'source'        : 'ftp',
         'status'        : 2,
         'command'       : 'python3.6 ' + base_url + "cronjob/python/Telesales/importTelesale.py > /dev/null &",
         'created_by'    : 'system'
      }
      importLogId = mongodb.insert(MONGO_COLLECTION=common.getSubUser(subUserType, 'Import'), insert_data=importLogInfo) 
    

   insertData  = []
   resultData  = []
   errorData   = []

   headers = _mongodb.get(MONGO_COLLECTION='Model', WHERE={"collection": collection,'sub_type':{'$exists': 'true'}}, SELECT=['index', 'field','type'], SORT=([('index', 1)]))
   headers = list(headers)

   users    = _mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'User'), WHERE=None, SELECT=['extension', 'agentname'], SORT=([('id', 1)]))
   users    = list(users)
   arr      = {} 
   random   = {}
   for user in users:
      arr[user['extension']] = 0
      random[user['extension']] = 0

   dataLibrary = excel.getDataCSV(file_path=importLogInfo['file_path'],header=0, names=None, index_col=None, usecols=None, dtype=None, converters=None, skiprows=None, na_values=None, encoding='utf-8')
   listDataLibrary = dataLibrary.values
   for key,listCol in enumerate(listDataLibrary):
      temp = {}
      checkErr = False
      for idx,header in enumerate(headers):
         if header['index'] == 26 or header['index'] == 27:
            continue;
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
            value_int   = int(listDataLibrary[key][idx])
            value       = '0'+ str(value_int)

         if header['type'] == 'string' and header['field'] != 'id_no':
            try:
               value_int   = int(listDataLibrary[key][idx])
               value       = str(value_int)
            except ValueError:
               value       = str(listDataLibrary[key][idx])
         if header['type'] == 'string' and header['field'] == 'id_no':
            value_int   = int(listDataLibrary[key][idx])
            value       = '0'+ str(value_int)

         if header['field'] == 'assign' and value != '':
            try:
               value = str(int(listDataLibrary[key][idx]))
               temp['createdBy']  = 'Byfixed-Import'
               checkUser = False
               for user in users:
                  if user['extension'] == value:
                     temp['assign_name']  = user['agentname']
                     arr[user['extension']] = arr[user['extension']] + 1
                     checkUser = True
               if checkUser == False:
                  value = ''
            except ValueError:
               err['cell'] =  xl_rowcol_to_cell(key, idx)
               err['type'] = 'int'
               errorData.append(err)
               checkErr = True
            
         if header['field'] == 'assign' and value == '':
            temp['createdBy']  = ''

         temp[header['field']]   = value
         temp['id_import']       = importLogId

         temp['createdAt']       = int(time.time())
         temp['updatedAt']       = int(time.time())
         temp['updatedBy']       = 'system'

      if checkErr == False:
         try:
            mongodb.update(MONGO_COLLECTION=collection, WHERE={'id_no':temp['id_no']}, VALUE=temp)
         except Exception as e:
            now_log         = datetime.now()
            log.write(now_log.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')

   
   if len(errorData) <= 0:
      # mongodb.insert('2_Assign_log', arrayCMND)
      status = 1
   else:
      status = 0
      log.write( str(errorData) + '\n')

   mongodb.update(MONGO_COLLECTION='TS_Import', WHERE={'_id': ObjectId(importLogId)}, VALUE={'complete_import': time.time(),'status': status,'error': errorData,'count_fixed': arr,'random': random})

   now_end         = datetime.now()
   log.write(now_end.strftime("%d/%m/%Y, %H:%M:%S") + ': End Import' + '\n')
   pprint({'status': status})

except Exception as e:
   print(e)
   log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
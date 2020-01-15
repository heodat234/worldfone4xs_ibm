#!/usr/bin/python3.6
# -*- coding: utf-8 -*-
import sys
import os
import calendar
import time
import ntpath
import json
import traceback
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

common      = Common()
base_url = common.base_url()
wff_env = common.wff_env(base_url)
mongodb     = Mongodb("worldfone4xs", WFF_ENV=wff_env)
_mongodb    = Mongodb("_worldfone4xs", WFF_ENV=wff_env)
excel       = Excel()
config      = Config()
base_url    = config.base_url()
subUserType = 'TS'
collection  = common.getSubUser(subUserType, 'Telesalelist')
# log         = open(base_url + "cronjob/python/Telesales/importTelesales.txt","a")
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
         'collection'    : 'Telesalelist', 
         'begin_import'  : time.time(),
         'file_name'     : ftpInfo['filename'],
         'file_path'     : ftpLocalUrl, 
         'source'        : 'ftp',
         'status'        : 2,
         'command'       : '/usr/local/bin/python3.6 ' + base_url + "cronjob/python/Telesales/importTelesale.py > /dev/null &",
         'created_by'    : 'system'
      }
      importLogId = mongodb.insert(MONGO_COLLECTION=common.getSubUser(subUserType, 'Import'), insert_data=importLogInfo) 
    

   insertData  = []
   resultData  = []
   errorData   = []

   headers = _mongodb.get(MONGO_COLLECTION='Model', WHERE={"collection": collection,'sub_type':{'$exists': 'true'}}, SELECT=['index', 'field', 'type', 'sub_type'], SORT=([('index', 1)]))
   headers = list(headers)

   users    = _mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'User'), WHERE=None, SELECT=['extension', 'agentname'], SORT=([('id', 1)]))
   users    = list(users)
   arr      = {} 
   random   = {}
   for user in users:
      arr[user['extension']] = 0
      random[user['extension']] = 0

   # fileName = importLogInfo['file_name']
   # filenameExtension = fileName.split('.')
   dataLibrary = excel.getDataCSV(file_path=importLogInfo['file_path'],header=0, sep=';', names=None, index_col=None, usecols=None, dtype=object, converters=None, skiprows=None, na_values=None, encoding='ISO-8859-1')
   # if(filenameExtension[1] == 'csv'):
   #    dataLibrary = excel.getDataCSV(file_path=importLogInfo['file_path'], dtype=object, sep='\t', lineterminator='\r', header=None, names=None, na_values='')
   # else:
   #    dataLibrary = excel.getDataExcel(file_path=importLogInfo['file_path'], header=0, names=None, na_values='')

   listDataLibrary = dataLibrary.values
   pprint(listDataLibrary)
   for key,listCol in enumerate(listDataLibrary):
      temp = {}
      checkErr = False
      for idx,header in enumerate(headers):
         # pprint(idx)
         # if header['index'] == 26 or header['index'] == 27:
         #    continue;
         if not header['sub_type'].strip() and 'import' in header['sub_type']:
            continue

         if str(listDataLibrary[key][idx]) == 'nan':
            listDataLibrary[key][idx] = ''
         if header['type'] == 'int':
            try:
               value = int(listDataLibrary[key][idx])
            except ValueError:
               err = {}
               # err['cell'] =  xl_rowcol_to_cell(key, idx+1)
               err['cell'] =  'Hàng: ' + str(key+2) + '; Cột: ' + str(idx+1)
               err['type'] = 'int';
               errorData.append(err);
               checkErr = True
         if header['type'] == 'double' and str(listDataLibrary[key][idx]) != '0':
            try:
               value = float(listDataLibrary[key][idx])
            except Exception as e:
               err = {}
               # err['cell'] =  xl_rowcol_to_cell(key, idx+1)
               err['cell'] =  'Hàng: ' + str(key+2) + '; Cột: ' + str(idx+1)
               err['type'] = 'double';
               errorData.append(err);
               checkErr = True
         if header['type'] == 'double' and str(listDataLibrary[key][idx]) == '0': 
            value = int(listDataLibrary[key][idx])

         if header['type'] == 'timestamp' and str(listDataLibrary[key][idx]) != '':
            err = {}
            try:
               value = int(time.mktime(time.strptime(listDataLibrary[key][idx], "%d/%m/%Y")))
            except Exception as e:
               # err['cell'] =  xl_rowcol_to_cell(key, idx+1)
               err['cell'] =  'Hàng: ' + str(key+2) + '; Cột: ' + str(idx+1)
               err['type'] = 'date'
               errorData.append(err)
               checkErr = True
         if header['type'] == 'phone':
            try:
               value_int   = int(listDataLibrary[key][idx])
               value       = '0'+ str(value_int)
            except Exception as e:
               # err['cell'] =  xl_rowcol_to_cell(key, idx+1)
               err['cell'] =  'Hàng: ' + str(key+2) + '; Cột: ' + str(idx+1)
               err['type'] = 'phone'
               errorData.append(err)
               checkErr = True
         if header['type'] == 'name':
            value   = str(listDataLibrary[key][idx])

         if header['type'] == 'string' and header['field'] != 'id_no':
            try:
               value_int   = int(listDataLibrary[key][idx])
               value       = str(value_int)
            except ValueError:
               value       = str(listDataLibrary[key][idx])
         if header['type'] == 'string' and header['field'] == 'id_no':
            value       = str(listDataLibrary[key][idx])

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
               # err['cell'] =  xl_rowcol_to_cell(key, idx)
               err['cell'] =  'Hàng: ' + str(key+2) + '; Cột: ' + str(idx+1)
               err['type'] = 'int'
               errorData.append(err)
               checkErr = True
            
         if header['field'] == 'assign' and value == '':
            temp['createdBy']  = ''
            temp['assign_name']  = ''

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
            # log.write(now_log.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')

   
   if len(errorData) <= 0:
      # mongodb.insert('2_Assign_log', arrayCMND)
      status = 1
   else:
      status = 0
      # print(errorData)
      # log.write( str(errorData) + '\n')

   mongodb.update(MONGO_COLLECTION='TS_Import', WHERE={'_id': ObjectId(importLogId)}, VALUE={'complete_import': time.time(),'status': status,'error': errorData,'count_fixed': arr,'random': random})

   now_end         = datetime.now()
   # log.write(now_end.strftime("%d/%m/%Y, %H:%M:%S") + ': End Import' + '\n')
   pprint({'status': status})

except Exception as e:
   print(e)
   print(traceback.format_exc())
   # log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
#!/usr/bin/python3.6
# -*- coding: utf-8 -*-

import ftplib
import calendar
import time
import sys
import os
import json
import csv
from pprint import pprint
from datetime import datetime
from datetime import date
from bson import ObjectId
from helper.ftp import Ftp
from helper.mongod import Mongodb
from helper.excel import Excel
from helper.jaccs import Config
from helper.common import Common

excel = Excel()
config = Config()
ftp = Ftp()
common = Common()
base_url = common.base_url()
wff_env = common.wff_env(base_url)
mongodb = Mongodb(MONGODB="worldfone4xs", WFF_ENV=wff_env)
_mongodb = Mongodb(MONGODB="_worldfone4xs", WFF_ENV=wff_env)
log = open(base_url + "cronjob/python/Loan/log/importLawsuit.txt","a")
now = datetime.now()
subUserType = 'LO'
collection = common.getSubUser(subUserType, 'Lawsuit')

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
   total = 0
   complete = 0
   today = date.today()
   # today = datetime.strptime('05/02/2020', "%d/%m/%Y").date()
   day = today.day
   month = today.month
   year = today.year
   fileName = "Lawsuit report.xlsx"
   sep = ';'
   logDbName = "LO_Input_result_" + str(year) + str(month)

   if day == 1:
      mongodb.create_db(DB_NAME=logDbName)
      mongodbresult = Mongodb(logDbName, WFF_ENV=wff_env)
   else:
      mongodbresult = Mongodb(logDbName, WFF_ENV=wff_env)
   
   ftpLocalUrl = common.getDownloadFolder() + fileName

   try:
      sys.argv[1]
      importLogId = str(sys.argv[1])
      importLogInfo = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Import'), WHERE={'_id': ObjectId(sys.argv[1])})
   except Exception as SysArgvError:
      if not os.path.isfile(ftpLocalUrl):
         sys.exit()

      importLogInfo = {
         'collection'    : collection, 
         'begin_import'  : time.time(),
         'file_name'     : fileName,
         'file_path'     : ftpLocalUrl, 
         'source'        : 'ftp',
         'status'        : 2,
         'command'       : '/usr/local/bin/python3.6 ' + base_url + "cronjob/python/Loan/importLawsuit.py > /dev/null &",
         'created_by'    : 'system'
      }
      importLogId = mongodb.insert(MONGO_COLLECTION=common.getSubUser(subUserType, 'Import'), insert_data=importLogInfo)

   models = _mongodb.get(MONGO_COLLECTION='Model', WHERE={'collection': collection}, SORT=[('index', 1)], SELECT=['index', 'collection', 'field', 'type', 'sub_type'])
   
   for model in models:
      if 'sub_type' in model.keys() and model['sub_type'] not in  ['', ' ']:
         modelColumns.append(model['field'])
         modelConverters[model['field']] = model['type']
         modelConverters1.append(model['type'])
         subtype = json.loads(model['sub_type'])
         if 'format' in subtype.keys():
               modelFormat[model['field']] = subtype['format']
               modelFormat1.append(subtype['format'])
         else:
               modelFormat[model['field']] = ''
               modelFormat1.append('')

         if 'column' in subtype.keys():
               modelPosition[model['field']] = subtype['column']
               modelPosition1.append(subtype['column'])
         else:
               modelPosition[model['field']] = ''
               modelPosition1.append('')
   
   # pprint(modelColumns)
   filenameExtension = fileName.split('.')

   if len(filenameExtension) < 2:
      filenameExtension.append('txt')
   
   if filenameExtension[1] in ['csv', 'xlsx']:
      if(filenameExtension[1] == 'csv'):
         inputDataRaw = excel.getDataCSV(file_path=importLogInfo['file_path'], dtype=object, sep=sep, header=0, skiprows=[1], names=modelColumns, na_values='')
      else:
         inputDataRaw = excel.getDataExcel(file_path=importLogInfo['file_path'], active_sheet='Sheet1', skiprows=[1], header=0, names=modelColumns, na_values='')
         
      inputData = inputDataRaw.to_dict('records')
      
      for idx, row in enumerate(inputData):
         total += 1
         temp = {}
         result = True
         if row['so_hopdong'] not in ['', None]:
               # pprint(row)
               # break
               for cell in row:
                  try:
                     if modelConverters[cell] == 'timestamp':
                        row[cell] = str(row[cell]).zfill(10)
                        temp[cell] = common.convertDataType(data=row[cell], datatype='timestamp', formatType="%d/%m/%Y")
                     else:
                        temp[cell] = common.convertDataType(data=row[cell], datatype=modelConverters[cell], formatType=modelFormat[cell])

                     # break
                  except Exception as errorConvertType:
                     temp['error_cell'] = cell + "_" + str(idx + 1)
                     temp['type'] = modelConverters[cell]
                     temp['error_mesg'] = 'Sai kiểu dữ liệu nhập'
                     temp['result'] = 'error'
                     pprint('error')
                     result = False
               temp['created_by'] = 'system'
               temp['created_at'] = time.time()
               temp['import_id'] = str(importLogId)
               if(result == False):
                  errorData.append(temp)
               else:
                  insertData.append(temp)
                  result = True
                  complete += 1
   else:
      with open(importLogInfo['file_path'], 'r', newline='\n', encoding='ISO-8859-1') as fin:
         csv_reader = csv.reader(fin, delimiter=';', quotechar='"')
         for idx, row in enumerate(csv_reader):
               if len(row) > 5:
                  total += 1
                  result = True
                  temp = {}
                  for keyCell, cell in enumerate(row):
                     if keyCell <= len(modelColumns) - 1:
                           try:
                              temp[modelColumns[keyCell]] = common.convertDataType(data=cell, datatype=modelConverters1[keyCell], formatType=modelFormat1[keyCell])
                           except Exception as errorConvertType:
                              pprint(modelColumns[keyCell] + "_" + str(idx + 1))
                              temp['error_cell'] = modelColumns[keyCell] + "_" + str(idx + 1)
                              temp['type'] = modelConverters1[keyCell]
                              temp['error_mesg'] = 'Sai kiểu dữ liệu nhập'
                              temp['result'] = 'error'
                              result = False
                  temp['created_by'] = 'system'
                  temp['created_at'] = time.time()
                  temp['import_id'] = str(importLogId)
                  if(result == False):
                     errorData.append(temp)
                  else:
                     insertData.append(temp)
                     result = True
                     complete += 1
                     
   if(len(errorData) > 0):
      mongodbresult.remove_document(MONGO_COLLECTION=common.getSubUser(subUserType, ('Lawsuit_' + str(year) + str(month) + str(day))))
      mongodbresult.batch_insert(common.getSubUser(subUserType, ('Lawsuit_' + str(year) + str(month) + str(day))), errorData)
      mongodb.update(MONGO_COLLECTION=common.getSubUser(subUserType, 'Import'), WHERE={'_id': importLogId}, VALUE={'status': 0, 'complete_import': time.time(), 'total': total, 'complete': complete})
   else:
      if len(insertData) > 0:
         mongodb.batch_insert(MONGO_COLLECTION=collection, insert_data=insertData)
         pprint("TEST")
      mongodb.update(MONGO_COLLECTION=common.getSubUser(subUserType, 'Import'), WHERE={'_id': importLogId}, VALUE={'status': 1, 'complete_import': time.time(), 'total': total, 'complete': complete})

except Exception as e:
   log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
   pprint(str(e))

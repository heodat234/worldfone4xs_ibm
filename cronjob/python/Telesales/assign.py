#!/usr/bin/python3.6
# -*- coding: utf-8 -*-
log         = open("/var/www/html/worldfone4xs_ibm/cronjob/python/Telesales/Assign_log.txt","a")
import sys
import os
sys.path.insert(1, '/var/www/html/worldfone4xs_ibm/cronjob/python')
import time
import ntpath
import json
from mongod import Mongodb
from datetime import datetime
from pprint import pprint
from bson import ObjectId

mongodb     = Mongodb("worldfone4xs")
_mongodb    = Mongodb("_worldfone4xs")
now         = datetime.now()

try:
   importLogId = sys.argv[1]
   random      = sys.argv[2]
   extensions   = sys.argv[3]
   insertData  = []
   resultData  = []
   errorData   = []

   randoms = random.split(',')
   extensions = extensions.split(',')
   log.write(str(extensions))
   now         = datetime.now()
   log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': Start Log' + '\n')
   mongodb.update(MONGO_COLLECTION='TS_Import', WHERE={'_id': ObjectId(importLogId)}, VALUE={'assign': int(-1)})
   assign_log = mongodb.getOne(MONGO_COLLECTION='TS_Assign_log')
   if assign_log == None:
      users = _mongodb.get(MONGO_COLLECTION='TS_User', WHERE=None, SELECT=['extension', 'agentname'], SORT=([('id', 1)]))
      users = list(users)
      arrayCMND = {}
      assign_log = {}
      for user in users:
         arrayCMND[user['extension']] = []
      assign_log['_id'] = mongodb.insert('TS_Assign_log', arrayCMND)
      array_cmnd = []
   # else:
   #    array_cmnd = assign_log[extension]

   for u,extension in enumerate(extensions):
      random  = randoms[u]
      array_cmnd = assign_log[extension]

      count = int(random)/10000
      du = int(random)%10000
      dem = 0
      for x in range(int(count)):
         users = mongodb.get(MONGO_COLLECTION='TS_Telesalelist', WHERE={'id_import': importLogId,'assign': '','id_no': {'$nin' :array_cmnd}},SELECT=['_id', 'id_no'], SORT=([('id', 1)]),SKIP=0, TAKE=int(10000))
         for idx,user in enumerate(users):
            mongodb.update(MONGO_COLLECTION='TS_Telesalelist', WHERE={'_id': ObjectId(user['_id'])}, VALUE={'updatedAt': int(time.time()),'assign': extension,'assigned_by': 'BySystemRandom'})
            array_cmnd.append(user['id_no'])
            # dem = dem + 1
      
      if int(du) > 0:
         users = mongodb.get(MONGO_COLLECTION='TS_Telesalelist', WHERE={'id_import': importLogId,'assign': '','id_no': {'$nin' :array_cmnd}},SELECT=['_id', 'id_no'], SORT=([('id', 1)]),SKIP=0, TAKE=int(du))
         for idx,user in enumerate(users):
            mongodb.update(MONGO_COLLECTION='TS_Telesalelist', WHERE={'_id': ObjectId(user['_id'])}, VALUE={'updatedAt': int(time.time()),'assign': extension,'assigned_by': 'BySystemRandom'})
            array_cmnd.append(user['id_no'])
            # dem = dem + 1

      mongodb.update(MONGO_COLLECTION='TS_Assign_log', WHERE={'_id': ObjectId(assign_log['_id'])}, VALUE={extension: array_cmnd})
      
      count = mongodb.count(MONGO_COLLECTION='TS_Telesalelist', WHERE={'id_import': importLogId,'assign': extension,'assigned_by': 'BySystemRandom'})
      mongodb.update(MONGO_COLLECTION='TS_Import', WHERE={'_id': ObjectId(importLogId)}, VALUE={'random.'+extension: int(count), 'assign': 0})

   now_end         = datetime.now()
   log.write(now_end.strftime("%d/%m/%Y, %H:%M:%S") + ': End Log' + '\n')
   print(count)
except Exception as e:
    # pprint(e)
    log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
#!/usr/bin/python3.6
# -*- coding: utf-8 -*-
import sys
import os
# sys.path.insert(1, '/var/www/html/worldfone4xs_ibm/cronjob/python')
import time
import ntpath
import json
from helper.mongod import Mongodb
from datetime import datetime
from pprint import pprint
from bson import ObjectId

mongodb     = Mongodb("worldfone4xs")
_mongodb    = Mongodb("_worldfone4xs")
now         = datetime.now()
log         = open("/var/www/html/worldfone4xs_ibm/cronjob/python/Telesales/Assign_log.txt","a")

try:
   importLogId = sys.argv[1]
   random      = sys.argv[2]
   extensions   = sys.argv[3]
   insertData  = []
   resultData  = []
   errorData   = []

   randoms = random.split(',')
   extensions = extensions.split(',')
   # log.write(str(extensions))
   now         = datetime.now()
   log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': Start Log' + '\n')

   mongodb.update(MONGO_COLLECTION='TS_Import', WHERE={'_id': ObjectId(importLogId)}, VALUE={'assign': int(-1)})
   assign_log = mongodb.getOne(MONGO_COLLECTION='TS_Assign_log')
   if assign_log == None:
      users = _mongodb.get(MONGO_COLLECTION='TS_User', WHERE=None, SELECT=['extension', 'agentname'], SORT=([('id', 1)]))
      users = list(users)
      # log.write(str(users))
      arrayCMND = {}
      assign_log = {}
      for user in users:
         arrayCMND[user['extension']] = []
         assign_log[user['extension']] = []
      assign_log['_id'] = mongodb.insert('TS_Assign_log', arrayCMND)
      array_cmnd = []
 
   for u,extension in enumerate(extensions):
      user = _mongodb.getOne(MONGO_COLLECTION='TS_User', WHERE={'extension': str(extension)}, SELECT=['extension', 'agentname'])
      assign_name = user['agentname']
      random  = randoms[u]
      if extension in assign_log.keys():
         array_cmnd = assign_log[extension]
      else:
         array_cmnd = []
      quotient = int(random)/10000
      mod = int(random)%10000
      dem = 0
      if quotient != 0:
         for x in range(int(quotient)):
            users = mongodb.get(MONGO_COLLECTION='TS_Telesalelist', WHERE={'id_import': importLogId,'assign': '','id_no': {'$nin' :array_cmnd}},SELECT=['_id', 'id_no'], SORT=([('id', 1)]),SKIP=0, TAKE=int(10000))
            for idx,user in enumerate(users):
               mongodb.update(MONGO_COLLECTION='TS_Telesalelist', WHERE={'_id': ObjectId(user['_id'])}, VALUE={'updatedAt': int(time.time()),'assign': extension,'assign_name': assign_name,'createdBy': 'BySystemRandom'})
               array_cmnd.append(user['id_no'])
      
      if int(mod) > 0:
         users = mongodb.get(MONGO_COLLECTION='TS_Telesalelist', WHERE={'id_import': importLogId,'assign': '','id_no': {'$nin' :array_cmnd}},SELECT=['_id', 'id_no'], SORT=([('id', 1)]),SKIP=0, TAKE=int(mod))
         for idx,user in enumerate(users):
            mongodb.update(MONGO_COLLECTION='TS_Telesalelist', WHERE={'_id': ObjectId(user['_id'])}, VALUE={'updatedAt': int(time.time()),'assign': extension,'assign_name': assign_name,'createdBy': 'BySystemRandom'})
            array_cmnd.append(user['id_no'])

      mongodb.update(MONGO_COLLECTION='TS_Assign_log', WHERE={'_id': ObjectId(assign_log['_id'])}, VALUE={extension: array_cmnd})
      
      count = mongodb.count(MONGO_COLLECTION='TS_Telesalelist', WHERE={'id_import': importLogId,'assign': extension,'createdBy': 'BySystemRandom'})
      mongodb.update(MONGO_COLLECTION='TS_Import', WHERE={'_id': ObjectId(importLogId)}, VALUE={'random.'+extension: int(count), 'assign': 0})

   now_end         = datetime.now()
   log.write(now_end.strftime("%d/%m/%Y, %H:%M:%S") + ': End Log' + '\n')
   print(count)
except Exception as e:
    # pprint(e)
    log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')

# from multiprocessing.pool import ThreadPool as Pool
# # from multiprocessing import Pool
# from random import randint
# from time import sleep
# from itertools import product
# from functools import partial
# from itertools import repeat

# import re
# import ftplib
# import calendar
# import time
# import sys
# import os
# import csv
# import json
# from pprint import pprint
# from datetime import datetime, timedelta
# from datetime import date
# from bson import ObjectId
# from helper.ftp import Ftp
# from helper.mongod import Mongodb
# from helper.excel import Excel
# from helper.jaccs import Config
# from helper.common import Common

# excel = Excel()
# config = Config()
# ftp = Ftp()
# common = Common()
# base_url = common.base_url()
# wff_env = common.wff_env(base_url)
# mongodb = Mongodb(MONGODB="worldfone4xs", WFF_ENV=wff_env)
# _mongodb = Mongodb(MONGODB="_worldfone4xs", WFF_ENV=wff_env)

# now = datetime.now()
# subUserType = 'LO'
# collection = common.getSubUser(subUserType, 'ZACCF_24032020_test')

# today = datetime.strptime('24/03/2020', "%d/%m/%Y").date()
# todayString = today.strftime("%d/%m/%Y")
# todayTimeStamp = int(time.mktime(time.strptime(str(todayString + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))

# modelColumns = []
# modelConverters = {}
# modelConverters1 = []
# modelPosition = {}
# modelPosition1 = []
# modelFormat = {}
# modelFormat1 = []
# converters = {}
# insertData = []
# errorData = []
# total = 0
# complete = 0

# models = _mongodb.get(MONGO_COLLECTION='Model', WHERE={'collection': common.getSubUser(subUserType, 'ZACCF')}, SORT=[('index', 1)], SELECT=['index', 'collection', 'field', 'type', 'sub_type'], TAKE=1000)

# for model in models:
#     modelColumns.append(model['field'])
#     modelConverters[model['field']] = model['type']
#     modelConverters1.append(model['type'])
#     if 'sub_type' in model.keys():
#         subtype = json.loads(model['sub_type'])
#         if 'format' in subtype.keys():
#             modelFormat[model['field']] = subtype['format']
#             modelFormat1.append(subtype['format'])
#         else:
#             modelFormat[model['field']] = ''
#             modelFormat1.append('')

#         if 'column' in subtype.keys():
#             modelPosition[model['field']] = subtype['column']
#             modelPosition1.append(subtype['column'])
#         else:
#             modelPosition[model['field']] = ''
#             modelPosition1.append('')


# def process_line(data,index):
#     # pprint(index)
#     temp = {}
#     for keyCell, cell in enumerate(data):
#         if keyCell <= len(modelColumns) - 1:
#             temp[modelColumns[keyCell]] = cell

#     temp['created_by'] = 'system'
#     temp['created_at'] = todayTimeStamp

#     if index == total:
#         mongodb.batch_insert(MONGO_COLLECTION=collection, insert_data=insertData)
#         pprint('DONE')
#     else:
#         insertData.append(temp)
#     # mongodb.insert(MONGO_COLLECTION=collection, insert_data=temp)


# def get_next_line(total):
#     with open("/data/upload_file/20200324/ZACCF.txt", 'r', newline='\n', encoding='UTF-8') as f:
#         csv_reader = csv.reader((x.replace('\u0000', '') for x in f), delimiter=';', quotechar='"')
#         for line in csv_reader:
#             total += 1
#             yield line

# f = get_next_line(total)

# t = Pool(processes=32)


# for index,i in enumerate(f):
#     # t.starmap(process_line, product(i, repeat=index))
#     t.map(partial(process_line, index=index), i)

# t.close()
# t.join()

#!/usr/bin/python

import queue
import threading
import time

import re
import ftplib
import calendar
import time
import sys
import os
import csv
import json
from pprint import pprint
from datetime import datetime, timedelta
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

now = datetime.now()
subUserType = 'LO'
collection = common.getSubUser(subUserType, 'ZACCF_31032020_test')

today = datetime.strptime('31/03/2020', "%d/%m/%Y").date()
todayString = today.strftime("%d/%m/%Y")
todayTimeStamp = int(time.mktime(time.strptime(str(todayString + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))

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

models = _mongodb.get(MONGO_COLLECTION='Model', WHERE={'collection': common.getSubUser(subUserType, 'ZACCF')}, SORT=[('index', 1)], SELECT=['index', 'collection', 'field', 'type', 'sub_type'], TAKE=1000)

for model in models:
    modelColumns.append(model['field'])
    modelConverters[model['field']] = model['type']
    modelConverters1.append(model['type'])
    if 'sub_type' in model.keys():
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


exitFlag = 0

class myThread (threading.Thread):
   def __init__(self, threadID, name, q):
      threading.Thread.__init__(self)
      self.threadID = threadID
      self.name = name
      self.q = q
   def run(self):
      print ("Starting " + self.name)
      process_data(self.name, self.q)
      # print ("Exiting " + self.name)

def process_data(threadName, q):
   while not exitFlag:
      queueLock.acquire()
      if not workQueue.empty():
         data = q.get()
         queueLock.release()
         print ("%s processing %s" % (threadName, 'one'))
         # temp = {}
         # for keyCell, cell in enumerate(data):
         #    if keyCell <= len(modelColumns) - 1:
         #       temp[modelColumns[keyCell]] = cell

         data['created_by'] = 'system'
         data['created_at'] = todayTimeStamp

         # mongodb.insert(MONGO_COLLECTION=collection, insert_data=data)
      else:
         queueLock.release()
      time.sleep(1)


def get_next_line():
   with open("/data/upload_file/20200331/ZACCF.txt", 'r', newline='\n', encoding='UTF-8') as f:
      csv_reader = csv.reader((x.replace('\u0000', '') for x in f), delimiter=';', quotechar='"')
      for line in csv_reader:
         temp = {}
         for keyCell, cell in enumerate(line):
            if keyCell <= len(modelColumns) - 1:
               temp[modelColumns[keyCell]] = cell
         yield temp



threadList = ["Thread-1", "Thread-2", "Thread-3", "Thread-4", "Thread-5"]
nameList = ["One", "Two", "Three", "Four", "Five"]
queueLock = threading.Lock()
workQueue = queue.Queue(10)
threads = []
threadID = 1

# Create new threads
for tName in threadList:
   thread = myThread(threadID, tName, workQueue)
   thread.start()
   threads.append(thread)
   threadID += 1

# Fill the queue
queueLock.acquire()
# for word in nameList:
#    workQueue.put(word)

f = get_next_line()
f = list(f)
# f.pop(0)
# i = 0
for i,word in enumerate(f):
   if i == 10:
      workQueue.put(word)
      # print(word)
      break
   # i += 1


queueLock.release()

# Wait for queue to empty
while not workQueue.empty():
   pass

# Notify threads it's time to exit
exitFlag = 1

# Wait for all threads to complete
for t in threads:
   t.join()
print ("Exiting Main Thread")



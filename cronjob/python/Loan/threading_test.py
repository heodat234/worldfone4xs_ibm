
from multiprocessing.pool import ThreadPool as Pool
# from multiprocessing import Pool
from random import randint
from time import sleep
from itertools import product
from functools import partial
from itertools import repeat

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
collection = common.getSubUser(subUserType, 'ZACCF_24032020_test')

today = datetime.strptime('24/03/2020', "%d/%m/%Y").date()
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


def process_line(data,index):
    # pprint(index)
    temp = {}
    for keyCell, cell in enumerate(data):
        if keyCell <= len(modelColumns) - 1:
            temp[modelColumns[keyCell]] = cell
            
    temp['created_by'] = 'system'
    temp['created_at'] = todayTimeStamp

    if index == total:
        mongodb.batch_insert(MONGO_COLLECTION=collection, insert_data=insertData)
        pprint('DONE')
    else:
        insertData.append(temp)
    # mongodb.insert(MONGO_COLLECTION=collection, insert_data=temp)


def get_next_line(total):
    with open("/data/upload_file/20200324/ZACCF.txt", 'r', newline='\n', encoding='UTF-8') as f:
        csv_reader = csv.reader((x.replace('\u0000', '') for x in f), delimiter=';', quotechar='"')
        for line in csv_reader:
            total += 1
            yield line

f = get_next_line(total)

t = Pool(processes=32)


for index,i in enumerate(f):
    # t.starmap(process_line, product(i, repeat=index))
    t.map(partial(process_line, index=index), i)

t.close()
t.join()
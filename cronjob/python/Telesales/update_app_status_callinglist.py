#!/usr/bin/python3.6
# -*- coding: utf-8 -*-

import ftplib
import calendar
import time
import sys
import os
import json
import csv
import traceback
import pandas as pd
import xlsxwriter
import urllib.request
from pprint import pprint
from datetime import datetime
from datetime import date
from bson import ObjectId
from helper.ftp import Ftp
from helper.mongod import Mongodb
from helper.mongod import Mongo_common
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
mongo_common = Mongo_common()
log = open(base_url + "cronjob/python/Telesales/log/exportCallinglist.txt","a")
now = datetime.now()
subUserType = 'TS'
collection = common.getSubUser(subUserType, 'Telesalelist')

try:
    pprint("test")
    # updateLastCallCallinglist()
    listApp = list(mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Appointment')))
    for appointment in listApp:
        if 'cmnd' in appointment.keys():
    #         # mongodb.batch_update(MONGO_COLLECTION=collection, WHERE={'id_no': appointment['cmnd']}, VALUE={'app_status': appointment['status']})
            callingListInfo = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Telesalelist'), WHERE={'id_no': appointment['cmnd']})
            if callingListInfo != None:
    #         #     if '_id' in callingListInfo.keys():
    #         #         pprint(str(callingListInfo['_id']))
    #         #     else:
    #         #         pprint("None")
    #
                # Update thông tin khách hàng và thông tin nhân viên được assign cho appointment
                # updateInfo = {
                #     'assign': callingListInfo['assign'] if 'assign' in callingListInfo.keys() else '',
                #     'assign_name': callingListInfo['assign_name'] if 'assign_name' in callingListInfo.keys() else '',
                #     'tele_name': callingListInfo['name'] if 'name' in callingListInfo.keys() else '',
                #     'tele_phone': callingListInfo['phone'] if 'phone' in callingListInfo.keys() else '',
                #     'tele_note': callingListInfo['note'] if 'note' in callingListInfo.keys() else '',
                #     'tele_id': str(callingListInfo['_id']) if '_id' in callingListInfo.keys() else ''
                # }
                # # pprint(updateInfo)
                # mongodb.batch_update(MONGO_COLLECTION=common.getSubUser(subUserType, 'Appointment'), WHERE={'cmnd': callingListInfo['id_no']}, VALUE=updateInfo)

except Exception as e:
    log.write(traceback.format_exc())
    print(traceback.format_exc())


# UPDATE LAST CALL CALLING LIST
# try:
#     listCallingList = mongodb.get(MONGO_COLLECTION=collection)
#     for callingList in listCallingList:
#         if 'phone' in callingList.keys():
#             cdrInfo = list(mongodb.get(MONGO_COLLECTION='TS_worldfonepbxmanager', WHERE={'customernumber': callingList['phone']}, SELECT={'starttime'}, SORT=[('starttime', -1)], TAKE=1))
#             if len(cdrInfo) > 0:
#                 mongodb.update(MONGO_COLLECTION=collection, WHERE={'_id': ObjectId(callingList['_id'])}, VALUE={'starttime_call': cdrInfo[0]['starttime']})
#                 pprint(cdrInfo)
# except Exception as e:
#     pprint(traceback.format_exc())
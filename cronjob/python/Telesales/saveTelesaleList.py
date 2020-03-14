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
    total = 0
    complete = 0
    today = date.today()

    fileOutput  = base_url + 'upload/telesales/export/Calling_list_'+ today.strftime("%d%m%Y") +'.xlsx'
    model = list(_mongodb.get(MONGO_COLLECTION='Model', WHERE={'collection': common.getSubUser(subUserType, 'Telesalelist'), 'sub_type': {'$ne': None}}, SORT=[('index', 1)]))
    model_field = list(common.array_column(model, 'field'))
    model_field.insert(0, 'starttime_call')
    model_title = list(common.array_column(model, 'title'))
    model_title.insert(0, 'Nearest Call')
    try:
        sys.argv[1]
        exportLogId = str(sys.argv[1])
        exportLogInfo = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Export'), WHERE={'_id': ObjectId(exportLogId)})
        if exportLogInfo['filter'] not in [None, [], "[]"]:
            filter_value = json.loads(exportLogInfo['filter'])
            report_data = list(mongodb.get(MONGO_COLLECTION=collection, WHERE=filter_value[0]['$match']))
        else:
            report_data = list(mongodb.get(MONGO_COLLECTION=collection))
    except Exception as SysArgvError:
        report_data = list(mongodb.get(MONGO_COLLECTION=collection))

    workbook = xlsxwriter.Workbook(fileOutput)
    worksheet = workbook.add_worksheet()

    for header_key, header_value in enumerate(model_title):
        title_value = header_value.replace('@', '')
        worksheet.write(0, header_key, title_value)
        worksheet.set_column(0, header_key, 30)

    for key, value in enumerate(report_data):
        for header_key, header_value in enumerate(model_field):
            if header_value == 'starttime_call':
                call_info = list(mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'worldfonepbxmanager'), WHERE={'customernumber': value['phone']}, SELECT={'starttime'}, SORT=[("$starttime", -1)], TAKE=1))
                if len(call_info) > 0:
                    cell_value = time.strftime("%d/%m/%Y", time.localtime(call_info[0]['starttime']))
                    worksheet.write(key + 1, header_key, cell_value)
                    # worksheet.set_column(key + 1, header_key, len(str(cell_value)))
                    continue

            if header_value == 'exporting_date':
                if 'exporting_date' in value.keys():
                    if isinstance(value['exporting_date'], int):
                        cell_value = time.strftime("%d/%m/%Y", time.localtime(value['exporting_date'])) if 'exporting_date' in value.keys() and value['exporting_date'] != '' else ''
                        worksheet.write(key + 1, header_key, cell_value)
                    else:
                        worksheet.write(key + 1, header_key, value['exporting_date'])
                else:
                    worksheet.write(key + 1, header_key, '')
                # worksheet.set_column(key + 1, header_key, len(str(cell_value)))
                continue

            if header_value == 'date_of_birth':
                if 'date_of_birth' in value.keys():
                    if isinstance(value['date_of_birth'], int):
                        cell_value = time.strftime("%d/%m/%Y", time.localtime(value['date_of_birth'])) if 'date_of_birth' in value.keys() and value['date_of_birth'] != '' else ''
                        worksheet.write(key + 1, header_key, cell_value)
                    else:
                        worksheet.write(key + 1, header_key, value['date_of_birth'])
                else:
                    worksheet.write(key + 1, header_key, '')
                # worksheet.set_column(key + 1, header_key, len(str(cell_value)))
                continue

            if header_value == 'first_due_date':
                if 'first_due_date' in value.keys():
                    if isinstance(value['first_due_date'], int):
                        cell_value = time.strftime("%d/%m/%Y", time.localtime(value['first_due_date'])) if 'first_due_date' in value.keys() and value['first_due_date'] != '' else ''
                        worksheet.write(key + 1, header_key, cell_value)
                    else:
                        worksheet.write(key + 1, header_key, value['first_due_date'])
                else:
                    worksheet.write(key + 1, header_key, '')
                # worksheet.set_column(key + 1, header_key, len(str(cell_value)))
                continue

            if header_value == 'date_send_data':
                if 'date_send_data' in value.keys():
                    if isinstance(value['date_send_data'], int):
                        cell_value = time.strftime("%d/%m/%Y", time.localtime(value['date_send_data'])) if 'date_send_data' in value.keys() and value['date_send_data'] != '' else ''
                        worksheet.write(key + 1, header_key, cell_value)
                    else:
                        worksheet.write(key + 1, header_key, value['date_send_data'])
                else:
                    worksheet.write(key + 1, header_key, '')
                # worksheet.set_column(key + 1, header_key, len(str(cell_value)))
                continue

            if header_value == 'date_receive_data':
                if 'date_send_data' in value.keys():
                    if isinstance(value['date_send_data'], int):
                        cell_value = time.strftime("%d/%m/%Y", time.localtime(value['date_receive_data'])) if 'date_receive_data' in value.keys() and value['date_receive_data'] != '' else ''
                        worksheet.write(key + 1, header_key, cell_value)
                    else:
                        worksheet.write(key + 1, header_key, value['date_receive_data'])
                else:
                    worksheet.write(key + 1, header_key, '')
                # worksheet.set_column(key + 1, header_key, len(str(cell_value)))
                continue

            if header_value == 'createdAt':
                cell_value = time.strftime("%d/%m/%Y", time.localtime(value['createdAt'])) if 'createdAt' in value.keys() and value['createdAt'] != '' else ''
                worksheet.write(key + 1, header_key, cell_value)
                # worksheet.set_column(key + 1, header_key, len(str(cell_value)))
                continue

            if header_value == 'updatedAt':
                cell_value = time.strftime("%d/%m/%Y", time.localtime(value['updatedAt'])) if 'updatedAt' in value.keys() and value['updatedAt'] != '' else ''
                worksheet.write(key + 1, header_key, cell_value)
                # worksheet.set_column(key + 1, header_key, len(str(cell_value)))
                continue

            cell_value = str(value[header_value]) if header_value in value.keys() else ''
            worksheet.write(key + 1, header_key, cell_value)
    workbook.close()
    now_end         = datetime.now()
    if 'exportLogId' in locals():
        mongodb.update(MONGO_COLLECTION=common.getSubUser(subUserType, 'Export'), WHERE={'_id': ObjectId(exportLogId)}, VALUE={'status': 1, 'end': time.time()}),
    # notification = {
    #     'title'         : f'Export Calling list success',
    #     'active'        : True,
    #     'icon'          : 'fa fa-info-circle',
    #     'color'         : 'text-success',
    #     'content'       : f'Xuất excel thành công. Nhấn vào thông báo để tải về.',
    #     'link'          : '/upload/telesales/export/Calling_list'+ today.strftime("%d%m%Y") +'.xlsx',
    #     'to'            : exportLogInfo['created_by'],
    #     'notifyDate'    : datetime.utcnow(),
    #     'is_download'   : True,
    #     'createdBy'     : 'System',
    #     'createdAt'     : time.time()
    # }
    # mongodb.insert(MONGO_COLLECTION=common.getSubUser(subUserType, 'Notification'), insert_data=notification)
except Exception as e:
    log.write(traceback.format_exc())
    print(traceback.format_exc())

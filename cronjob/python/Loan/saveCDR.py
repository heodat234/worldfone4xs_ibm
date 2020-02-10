#!/usr/bin/python3.6
# -*- coding: utf-8 -*-

import re
import ftplib
import calendar
import time
import sys
import os
import json
import traceback
import xlsxwriter
import pandas as pd
from pprint import pprint
from datetime import datetime
from datetime import date, timedelta
from bson import ObjectId
from helper.ftp import Ftp
from helper.mongod import Mongodb
from helper.excel import Excel
from helper.jaccs import Config
from helper.common import Common
from helper.mongodbaggregate import Mongodbaggregate
from pathlib import Path

mongodb = Mongodb("worldfone4xs")
_mongodb = Mongodb("_worldfone4xs")
excel = Excel()
config = Config()
ftp = Ftp()
common = Common()
mongodbaggregate = Mongodbaggregate("worldfone4xs")
base_url = common.base_url()
wff_env = common.wff_env(base_url)
mongodb = Mongodb(MONGODB="worldfone4xs", WFF_ENV=wff_env)
_mongodb = Mongodb(MONGODB="_worldfone4xs", WFF_ENV=wff_env)
log = open(base_url + "cronjob/python/Loan/log/saveDailyProdProdEachUserGroup.txt","a")
now = datetime.now()
subUserType = 'LO'
collection = common.getSubUser(subUserType, 'worldfonepbxmanager')

try:
    today = date.today()
    tomorrow = today + timedelta(days=1)
    day = today.day
    month = today.month
    year = today.year
    todayString = today.strftime("%d/%m/%Y")
    tomorrowString = tomorrow.strftime("%Y%m%d")
    todayTimeStamp = int(time.mktime(time.strptime(str(todayString + " 00:00:00"), "%d/%m/%Y %H:%M:%S"))) # 86,399

    exportPath = f"/data/upload_file/export/{tomorrowString}"
    Path(exportPath).mkdir(parents=True, exist_ok=True)
    fileOutput = exportPath + '/CDR_' + today.strftime("%d%m%Y") + '.xlsx'
    workbook = xlsxwriter.Workbook(fileOutput)
    worksheet = workbook.add_worksheet()
    cell_format = workbook.add_format()
    cell_format.set_border()

    reportModelRaw = list(_mongodb.get(MONGO_COLLECTION="Model", WHERE={'collection': common.getSubUser(subUserType, 'Cdr_report')}, SORT=[('index', 1)]))
    reportModel = {}

    for modelKey, modelValue in enumerate(reportModelRaw):
        if modelValue['field'].find('customer.') != -1:
            continue
        worksheet.write(0, modelKey, modelValue['title'], cell_format)
        worksheet.set_column(0, modelKey, 30)

    cdrInfo = list(mongodb.get(WHERE={'starttime': {'$gte': todayTimeStamp, '$lte': (todayTimeStamp + 86399)}}, MONGO_COLLECTION=collection))

    for cdrkey, cdrValue in enumerate(cdrInfo):
        for modelKey, modelValue in enumerate(reportModelRaw):
            if modelValue['field'].find('customer.') != -1:
                field = modelValue['field'].split('.')
                if 'customer' in cdrValue.keys():
                    cellValue = cdrValue['customer'][field[1]] if field[1] in cdrValue['customer'].keys() else ''
                    # if modelValue['type'] == 'string':
                    #     worksheet.write_string((cdrkey + 1), modelKey, common.convertStr(cellValue), cell_format)
                    # elif modelValue['type'] == 'datetime':
                    #     worksheet.write_string((cdrkey + 1), modelKey, common.convertDatetime(int(cellValue)), cell_format)
                    # else:
                    #     worksheet.write((cdrkey + 1), modelKey, cellValue)
            else:
                cellValue = cdrValue[modelValue['field']] if modelValue['field'] in cdrValue.keys() else ''
                if modelValue['type'] == 'string':
                    worksheet.write_string((cdrkey + 1), modelKey, common.convertStr(cellValue), cell_format)
                elif modelValue['type'] == 'datetime':
                    worksheet.write_string((cdrkey + 1), modelKey, common.convertDatetime(int(cellValue)) if cellValue != '' else '', cell_format)
                else:
                    worksheet.write((cdrkey + 1), modelKey, cellValue, cell_format)

    workbook.close()
    pprint("END")

    # reportData = []
    # for cdr in cdrInfo:
    #     for key, value in enumerate(cdr):
    #         if key != 'customer':

    # df = pd.DataFrame(cdrInfo)
    # writer = pd.ExcelWriter(fileOutput, engine='xlsxwriter')
    # df.to_excel(writer,sheet_name='Sheet1',header=['GROUP','CONTRACTNR','CLIENT_NAME','BIRTH_DATE','CIF','SIGNED_DATE','PRODUCTNAME','ID NO','CREDIT AMOUNT','INSTALLMENT NUMBER','INSTALMENT AMOUNT','DATE_FIRST_DUE','DATE_LAST_DUE','CURRENT_DEBT','CURRENT_DPD','PHONE NUMBER','REFERENCE PHONE','Current_ADDRESS (if any)','District','PROVINCE','PERNAMENT_ADDRESS','District','PROVINCE','PRINCIPAL','INTEREST/ year','DPD','DATE HANDOVER','lICENSE PLATES NO','COMPANY']) 
    # workbook = writer.book
    # worksheet = writer.sheets['Sheet1']

except Exception as e:
    # log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
    # pprint(str(e))
    print(traceback.format_exc())
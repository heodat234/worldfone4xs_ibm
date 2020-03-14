#!/usr/bin/python3.6
# -*- coding: utf-8 -*-
import sys
import os
import time
import ntpath
import json
import calendar
import urllib
from helper.mongod import Mongodb
from datetime import datetime, timedelta
from datetime import date
from pprint import pprint
from bson import ObjectId
from helper.common import Common
import pandas as pd
from helper.jaccs import Config
import xlsxwriter
import traceback

common = Common()
base_url = common.base_url()
wff_env = common.wff_env(base_url)
mongodb = Mongodb(MONGODB="worldfone4xs", WFF_ENV=wff_env)
_mongodb = Mongodb(MONGODB="_worldfone4xs", WFF_ENV=wff_env)
log = open(base_url + "cronjob/python/Loan/log/saveTendencyDelinquent.txt","a")
now = datetime.now()
subUserType = 'LO'
collection = common.getSubUser(subUserType, 'First_time_payment_delinqunecy')
collection_column = common.getSubUser(subUserType, 'First_time_payment_delinqunecy_columns')

try:
    fileOutput  = base_url + 'upload/loan/export/First_time_payment_delinquency_incidence_rate_transition.xlsx'
    today = date.today()
    # today = datetime.strptime('18/01/2020', "%d/%m/%Y").date()
    yesterday = today - timedelta(days=1)
    day = today.day
    month = today.month
    year = today.year
    weekday = today.weekday()
    lastDayOfMonth = calendar.monthrange(year, month)[1]
    insertData = []
    todayString = today.strftime("%d/%m/%Y")
    todayTimeStamp = int(time.mktime(time.strptime(str(todayString + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
    startMonth = int(time.mktime(time.strptime(str('01/' + str(month) + '/' + str(year) + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
    startMonthStarttime = startMonth
    startMonthEndtime = int(time.mktime(time.strptime(str('01/' + str(month) + '/' + str(year) + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))

    row = 2
    col = 3
    # Create a workbook and add a worksheet.
    workbook = xlsxwriter.Workbook(fileOutput)
    worksheet = workbook.add_worksheet(str(year))
    worksheet.write(0, 0, str(year) + '年度')

    products = list(mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Product_group')))
    
    for product in products:
        columns = list(mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'First_time_payment_delinqunecy_columns'), WHERE={'for_year': str(year), 'prod_group_code': product['group_code']})) 
        # Header
        # merge_range: 
        # Parameter:
        # first_row (int) – The first row of the range. (All zero indexed.)
        # first_col (int) – The first column of the range.
        # last_row (int) – The last row of the range.
        # last_col (int) – The last col of the range.
        # data – Cell data to write. Variable types.
        # cell_format (Format) – Optional Format object.
        worksheet.merge_range(0, col, 0, col + 4, product['group_name'] + '\nTotal')
        for column in columns:
            col += 4
            worksheet.merge_range(0, col, 0, col + 4, product['group_name'] + '\nの内' + '\n' '{:.2%}'.format(float(column)) + ' 条件')

    workbook.close()


except Exception as e:
    # log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
    print(traceback.format_exc())
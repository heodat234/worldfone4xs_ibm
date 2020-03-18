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

    row = 0
    col = 4
    # Create a workbook and add a worksheet.
    workbook = xlsxwriter.Workbook(fileOutput)
    worksheet = workbook.add_worksheet(str(year))

    # Set cell format
    def add_format_cell(format_new = {}):
        format_cells = {
            # 'left'          : 1,
            # 'right'         : 1,
            # 'top'           : 1,
            # 'bottom'        : 1,
            'align'         : 'center',
            'valign'        : 'vcenter',
            'num_format'    : '#,##0',
            'text_wrap'     : True,
            'font_color'    : 'black'
        }
        if format_cells != {}:
            format_cells.update(format_new)

        return workbook.add_format(format_cells)

    worksheet.write('B1', str(year) + '年度', add_format_cell({'bold': True}))

    products = list(mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Product_group')))
    
    for product in products:
        if product['group_code'] != '300':
            columns = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'First_time_payment_delinqunecy_columns'), WHERE={'for_year': str(year), 'prod_group_code': product['group_code']})
            data = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'First_time_payment_delinqunecy'), WHERE={'for_year': str(year), 'prod_group_code': product['group_code']}, SORT=[('index', 1)])
            # pprint(product['group_code'])
            # Header
            # merge_range: 
            # Parameter:
            # first_row (int) – The first row of the range. (All zero indexed.)
            # first_col (int) – The first column of the range.
            # last_row (int) – The last row of the range.
            # last_col (int) – The last col of the range.
            # data – Cell data to write. Variable types.
            # cell_format (Format) – Optional Format object.

            worksheet.write('B' + str(row + 3), data['month'] if 'month' in data.keys() else '', add_format_cell({'left': 5, 'top': 5, 'right': 4, 'bottom': 4, 'bold': True}))
            worksheet.write('C' + str(row + 3), data['number'] if 'number' in data.keys() else '', add_format_cell({'left': 4, 'top': 5, 'right': 4, 'bottom': 4, 'bold': True}))
            worksheet.write('D' + str(row + 3), data['cal_name'] if 'cal_name' in data.keys() else '', add_format_cell({'left': 4, 'top': 5, 'right': 5, 'bottom': 4, 'bold': True}))

            if columns != None and 'columns' in columns.keys():
                worksheet.merge_range(row, col, row, col + 3, product['group_name'] + '\nTotal', add_format_cell({'left': 5, 'right': 5, 'top': 5, 'bottom': 1, 'bold': True}))
                worksheet.write(row + 1, col + 0, 'A01', add_format_cell({'left': 5, 'right': 1, 'bold': True}))
                worksheet.write(row + 1, col + 1, 'A02', add_format_cell({'left': 1, 'right': 1, 'bold': True}))
                worksheet.write(row + 1, col + 2, 'A03', add_format_cell({'left': 1, 'right': 1, 'bold': True}))
                worksheet.write(row + 1, col + 3, 'TOTAL', add_format_cell({'left': 1, 'right': 5, 'bold': True}))
                col += 4
                for column in columns['columns']:
                    worksheet.merge_range(row, col, row, col + 3, product['group_name'] + '\n' + 'の内' + '\n' + '{:.2%}'.format(float(column)) + ' 条件', add_format_cell({'left': 5, 'right': 5, 'top': 5, 'bottom': 1, 'bold': True}))
                    worksheet.write(row + 1, col + 0, 'A01', add_format_cell({'left': 5, 'right': 1, 'bold': True}))
                    worksheet.write(row + 1, col + 1, 'A02', add_format_cell({'left': 1, 'right': 1, 'bold': True}))
                    worksheet.write(row + 1, col + 2, 'A03', add_format_cell({'left': 1, 'right': 1, 'bold': True}))
                    worksheet.write(row + 1, col + 3, 'TOTAL', add_format_cell({'left': 1, 'right': 5, 'bold': True}))
                    col += 4

            worksheet.set_row(row, 50)
            row += 62
            col = 4

    pprint("DONE")
    workbook.close()


except Exception as e:
    # log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
    print(traceback.format_exc())
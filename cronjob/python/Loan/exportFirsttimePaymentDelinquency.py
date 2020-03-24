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
from xlsxwriter.utility import xl_rowcol_to_cell
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
    today = date.today()
    # today = datetime.strptime('18/01/2020', "%d/%m/%Y").date()
    yesterday = today - timedelta(days=1)
    day = today.day
    month = today.month
    year = today.year
    year_two_digit = today.strftime("%y")
    weekday = today.weekday()
    lastDayOfMonth = calendar.monthrange(year, month)[1]
    insertData = []
    todayString = today.strftime("%d/%m/%Y")
    todayTimeStamp = int(time.mktime(time.strptime(str(todayString + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
    startMonth = int(time.mktime(time.strptime(str('01/' + str(month) + '/' + str(year) + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
    startMonthStarttime = startMonth
    startMonthEndtime = int(time.mktime(time.strptime(str('01/' + str(month) + '/' + str(year) + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))

    fileOutput  = base_url + 'upload/loan/export/First_time_payment_delinquency_incidence_rate_transition_' + str(year) + '.xlsx'

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
    month_in_year = {'1': 'Jan', '2': 'Feb', '3': 'Mar', '4': 'Apr', '5': 'May', '6': 'Jun', '7': 'Jul', '8': 'Aug', '9': 'Sep', '10': 'Oct', '11': 'Nov', '12': 'Dec'}
    index = [1, 2, 3, 4, 5]
    row = 0
    
    for product in products:
        if product['group_code'] != '300':
            worksheet.set_row(row, 55)
            worksheet.set_column('B:B', 12)
            worksheet.set_column('D:D', 25)

            for mon in month_in_year:
                a01_first_payment_total = '=0'
                a02_first_payment_total = '=0'
                a03_first_payment_total = '=0'

                a01_remain_total = '=0'
                a02_remain_total = '=0'
                a03_remain_total = '=0'

                a01_payday = ''
                a02_payday = ''
                a03_payday = ''

                a01_date = ''
                a02_date = ''
                a03_date = ''

                worksheet.write('B' + str(row + 3), month_in_year[mon] + '-' + str(year_two_digit), add_format_cell({'bold': True, 'left': 5, 'top': 5, 'bottom': 4, 'right': 4}))
                worksheet.write('B' + str(row + 4), '', add_format_cell({'left': 5, 'top': 4, 'bottom': 4, 'right': 4}))
                worksheet.write('B' + str(row + 5), '', add_format_cell({'left': 5, 'top': 4, 'bottom': 4, 'right': 4}))
                worksheet.write('B' + str(row + 6), '', add_format_cell({'left': 5, 'top': 4, 'bottom': 4, 'right': 4}))
                worksheet.write('B' + str(row + 7), '', add_format_cell({'left': 5, 'top': 4, 'bottom': 5, 'right': 4}))

                worksheet.write('C' + str(row + 3), 'Number', add_format_cell({'bold': True, 'left': 4, 'top': 5, 'bottom': 4, 'right': 4}))
                worksheet.write('C' + str(row + 4), '', add_format_cell({'left': 4, 'top': 4, 'bottom': 4, 'right': 4}))
                worksheet.write('C' + str(row + 5), '', add_format_cell({'left': 4, 'top': 4, 'bottom': 4, 'right': 4}))
                worksheet.write('C' + str(row + 6), '', add_format_cell({'left': 4, 'top': 4, 'bottom': 4, 'right': 4}))
                worksheet.write('C' + str(row + 7), '', add_format_cell({'left': 4, 'top': 4, 'bottom': 5, 'right': 4}))

                worksheet.write('D' + str(row + 3), 'Pay day', add_format_cell({'bold': True, 'left': 4, 'top': 5, 'bottom': 4, 'right': 5}))
                worksheet.write('D' + str(row + 4), 'First payment', add_format_cell({'bold': True, 'left': 4, 'top': 4, 'bottom': 4, 'right': 5}))
                worksheet.write('D' + str(row + 5), 'Remaining after 5 days', add_format_cell({'bold': True, 'left': 4, 'top': 4, 'bottom': 4, 'right': 5}))
                worksheet.write('D' + str(row + 6), 'Date', add_format_cell({'bold': True, 'left': 4, 'top': 4, 'bottom': 4, 'right': 5}))
                worksheet.write('D' + str(row + 7), 'Rate', add_format_cell({'bold': True, 'left': 4, 'top': 4, 'bottom': 5, 'right': 5}))

                data = list(mongodb.get(MONGO_COLLECTION=collection, WHERE={'for_month': mon, 'for_year': str(year), 'prod_group_code': product['group_code']}, SORT=[('index', 1)]))
                # pprint(data[0] if len(data) > 0 else [])

                columns = mongodb.getOne(MONGO_COLLECTION=collection_column, WHERE={'for_year': str(year), 'prod_group_code': product['group_code']})
                
                if columns != None and 'columns' in columns.keys():
                    col = 4
                    columns['columns'].insert(0, 'Total')
                    for column in columns['columns']:
                        if mon == '1':
                            if column == 'Total':
                                worksheet.merge_range((row), (col), (row), (col + 3), product['group_name'] + '\n' + 'Total', add_format_cell({'bold': True, 'bg_color': 'FFFF02', 'border': 5, 'bottom': 1}))
                                worksheet.write((row + 1), (col), 'A01', add_format_cell({'bold': True, 'left': 5, 'bottom': 5, 'top': 1, 'right': 1, 'bg_color': 'FFFF02'}))
                                worksheet.write((row + 1), (col + 1), 'A02', add_format_cell({'bold': True, 'left': 1, 'bottom': 5, 'top': 1, 'right': 1, 'bg_color': 'FFFF02'}))
                                worksheet.write((row + 1), (col + 2), 'A03', add_format_cell({'bold': True, 'left': 1, 'bottom': 5, 'top': 1, 'right': 1, 'bg_color': 'FFFF02'}))
                                worksheet.write((row + 1), (col + 3), 'TOTAL', add_format_cell({'bold': True, 'left': 1, 'bottom': 5, 'top': 1, 'right': 5, 'bg_color': 'FFFF02'}))
                            else:
                                worksheet.merge_range((row), (col), (row), (col + 3), product['group_name'] + '\n' + 'の内' + '\n' + str(round(float(column) / 12, 4)) + ' 条件', add_format_cell({'font_color': '000099', 'border': 5, 'bottom': 1}))
                                worksheet.write((row + 1), (col), 'A01', add_format_cell({'bold': True, 'left': 5, 'bottom': 5, 'top': 1, 'right': 1}))
                                worksheet.write((row + 1), (col + 1), 'A02', add_format_cell({'bold': True, 'left': 1, 'bottom': 5, 'top': 1, 'right': 1}))
                                worksheet.write((row + 1), (col + 2), 'A03', add_format_cell({'bold': True, 'left': 1, 'bottom': 5, 'top': 1, 'right': 1}))
                                worksheet.write((row + 1), (col + 3), 'TOTAL', add_format_cell({'bold': True, 'left': 1, 'bottom': 5, 'top': 1, 'right': 5}))

                        column_field = column.replace('.', '')
                        if column != 'Total':
                            worksheet.write((row + 2), (col), data[0]['a01_' + column_field] if len(data) > 0 and 'a01_' + column_field in data[0].keys() else a01_payday, add_format_cell({'bold': True, 'top': 5, 'bottom': 4, 'right': 4}))
                            worksheet.write((row + 2), (col + 1), data[0]['a02_' + column_field] if len(data) > 0 and 'a02_' + column_field in data[0].keys() else a02_payday, add_format_cell({'bold': True, 'top': 5, 'bottom': 4, 'right': 4}))
                            worksheet.write((row + 2), (col + 2), data[0]['a03_' + column_field] if len(data) > 0 and 'a03_' + column_field in data[0].keys() else a03_payday, add_format_cell({'bold': True, 'top': 5, 'bottom': 4, 'right': 4}))
                            worksheet.write((row + 2), (col + 3), '', add_format_cell({'align': 'right', 'top': 5, 'bottom': 4, 'right': 5}))
                            a01_payday = data[0]['a01_' + column_field] if len(data) > 0 and 'a01_' + column_field in data[0].keys() else a01_payday
                            a02_payday = data[0]['a02_' + column_field] if len(data) > 0 and 'a02_' + column_field in data[0].keys() else a02_payday
                            a03_payday = data[0]['a03_' + column_field] if len(data) > 0 and 'a03_' + column_field in data[0].keys() else a03_payday

                            worksheet.write((row + 3), (col), data[1]['a01_' + column_field] if len(data) > 0 and 'a01_' + column_field in data[1].keys() else 0, add_format_cell({'align': 'right', 'bottom': 4, 'right': 4}))
                            worksheet.write((row + 3), (col + 1), data[1]['a02_' + column_field] if len(data) > 0 and 'a02_' + column_field in data[1].keys() else 0, add_format_cell({'align': 'right', 'bottom': 4, 'right': 4}))
                            worksheet.write((row + 3), (col + 2), data[1]['a03_' + column_field] if len(data) > 0 and 'a03_' + column_field in data[1].keys() else 0, add_format_cell({'align': 'right', 'bottom': 4, 'right': 4}))
                            worksheet.write((row + 3), (col + 3), '=' + xl_rowcol_to_cell((row + 3), (col)) + '+' + xl_rowcol_to_cell((row + 3), (col + 1)) + '+' + xl_rowcol_to_cell((row + 3), (col + 2)), add_format_cell({'align': 'right', 'bottom': 4, 'right': 5}))
                            a01_first_payment_total += '+' + xl_rowcol_to_cell((row + 3), (col))
                            a02_first_payment_total += '+' + xl_rowcol_to_cell((row + 3), (col + 1))
                            a03_first_payment_total += '+' + xl_rowcol_to_cell((row + 3), (col + 2))

                            worksheet.write((row + 4), (col), data[2]['a01_' + column_field] if len(data) > 0 and 'a01_' + column_field in data[2].keys() else 0, add_format_cell({'align': 'right', 'bottom': 4, 'right': 4}))
                            worksheet.write((row + 4), (col + 1), data[2]['a02_' + column_field] if len(data) > 0 and 'a02_' + column_field in data[2].keys() else 0, add_format_cell({'align': 'right', 'bottom': 4, 'right': 4}))
                            worksheet.write((row + 4), (col + 2), data[2]['a03_' + column_field] if len(data) > 0 and 'a03_' + column_field in data[2].keys() else 0, add_format_cell({'align': 'right', 'bottom': 4, 'right': 4}))
                            worksheet.write((row + 4), (col + 3), '=' + xl_rowcol_to_cell((row + 4), (col)) + '+' + xl_rowcol_to_cell((row + 4), (col + 1)) + '+' + xl_rowcol_to_cell((row + 4), (col + 2)), add_format_cell({'align': 'right', 'bottom': 4, 'right': 5}))
                            a01_remain_total += '+' + xl_rowcol_to_cell((row + 4), (col))
                            a02_remain_total += '+' + xl_rowcol_to_cell((row + 4), (col + 1))
                            a03_remain_total += '+' + xl_rowcol_to_cell((row + 4), (col + 2))

                            worksheet.write((row + 5), (col), data[3]['a01_' + column_field] if len(data) > 0 and 'a01_' + column_field in data[3].keys() else a01_date, add_format_cell({'bold': True, 'bottom': 4, 'right': 4}))
                            worksheet.write((row + 5), (col + 1), data[3]['a02_' + column_field] if len(data) > 0 and 'a02_' + column_field in data[3].keys() else a02_date, add_format_cell({'bold': True, 'bottom': 4, 'right': 4}))
                            worksheet.write((row + 5), (col + 2), data[3]['a03_' + column_field] if len(data) > 0 and 'a03_' + column_field in data[3].keys() else a03_date, add_format_cell({'bold': True, 'bottom': 4, 'right': 4}))
                            worksheet.write((row + 5), (col + 3), '', add_format_cell({'align': 'right', 'bottom': 4, 'right': 5}))
                            a01_date = data[3]['a01_' + column_field] if len(data) > 0 and 'a01_' + column_field in data[3].keys() else a01_date
                            a02_date = data[3]['a02_' + column_field] if len(data) > 0 and 'a02_' + column_field in data[3].keys() else a02_date
                            a03_date = data[3]['a03_' + column_field] if len(data) > 0 and 'a03_' + column_field in data[3].keys() else a03_date

                            worksheet.write((row + 6), (col), '=' + xl_rowcol_to_cell((row + 4), (col)) + '/' + xl_rowcol_to_cell((row + 3), (col)), add_format_cell({'align': 'center', 'bottom': 5, 'right': 4, 'num_format': '0.00%'}))
                            worksheet.write((row + 6), (col + 1), '=' + xl_rowcol_to_cell((row + 4), (col + 1)) + '/' + xl_rowcol_to_cell((row + 3), (col + 1)), add_format_cell({'align': 'center', 'bottom': 5, 'right': 4, 'num_format': '0.00%'}))
                            worksheet.write((row + 6), (col + 2), '=' + xl_rowcol_to_cell((row + 4), (col + 2)) + '/' + xl_rowcol_to_cell((row + 3), (col + 2)), add_format_cell({'align': 'center', 'bottom': 5, 'right': 4, 'num_format': '0.00%'}))
                            worksheet.write((row + 6), (col + 3), '=' + xl_rowcol_to_cell((row + 4), (col + 3)) + '/' + xl_rowcol_to_cell((row + 3), (col + 3)), add_format_cell({'align': 'center', 'bottom': 5, 'right': 5, 'num_format': '0.00%'}))
                        
                        col += 4

                    worksheet.write('E' + str(row + 3), a01_payday, add_format_cell({'bold': True, 'align': 'center', 'bottom': 4, 'right': 4, 'bg_color': 'FFFF02'}))
                    worksheet.write('E' + str(row + 4), a01_first_payment_total, add_format_cell({'align': 'right', 'bottom': 4, 'right': 4, 'bg_color': 'FFFF02'}))
                    worksheet.write('E' + str(row + 5), a01_remain_total, add_format_cell({'align': 'right', 'bottom': 4, 'right': 4, 'bg_color': 'FFFF02'}))
                    worksheet.write('E' + str(row + 6), a01_date, add_format_cell({'bold': True, 'align': 'center', 'bottom': 4, 'right': 4, 'bg_color': 'FFFF02'}))
                    worksheet.write('E' + str(row + 7), '=' + 'E' + str(row + 5) + '/' + 'E' + str(row + 4), add_format_cell({'align': 'center', 'bottom': 5, 'right': 4, 'num_format': '0.00%', 'bg_color': 'FFFF02'}))

                    worksheet.write('F' + str(row + 3), a02_payday, add_format_cell({'bold': True, 'align': 'center', 'bottom': 4, 'right': 4, 'bg_color': 'FFFF02'}))
                    worksheet.write('F' + str(row + 4), a02_first_payment_total, add_format_cell({'align': 'right', 'bottom': 4, 'right': 4, 'bg_color': 'FFFF02'}))
                    worksheet.write('F' + str(row + 5), a02_remain_total, add_format_cell({'align': 'right', 'bottom': 4, 'right': 4, 'bg_color': 'FFFF02'}))
                    worksheet.write('F' + str(row + 6), a02_date, add_format_cell({'bold': True, 'align': 'center', 'bottom': 4, 'right': 4, 'bg_color': 'FFFF02'}))
                    worksheet.write('F' + str(row + 7), '=' + 'F' + str(row + 5) + '/' + 'F' + str(row + 4), add_format_cell({'align': 'center', 'bottom': 5, 'right': 4, 'num_format': '0.00%', 'bg_color': 'FFFF02'}))

                    worksheet.write('G' + str(row + 3), a03_payday, add_format_cell({'bold': True, 'align': 'center', 'bottom': 4, 'right': 4, 'bg_color': 'FFFF02'}))
                    worksheet.write('G' + str(row + 4), a03_first_payment_total, add_format_cell({'align': 'right', 'bottom': 4, 'right': 4, 'bg_color': 'FFFF02'}))
                    worksheet.write('G' + str(row + 5), a03_remain_total, add_format_cell({'align': 'right', 'bottom': 4, 'right': 4, 'bg_color': 'FFFF02'}))
                    worksheet.write('G' + str(row + 6), a03_date, add_format_cell({'bold': True, 'align': 'center', 'bottom': 4, 'right': 4, 'bg_color': 'FFFF02'}))
                    worksheet.write('G' + str(row + 7), '=' + 'G' + str(row + 5) + '/' + 'G' + str(row + 4), add_format_cell({'align': 'center', 'bottom': 5, 'right': 4, 'num_format': '0.00%', 'bg_color': 'FFFF02'}))

                    worksheet.write('H' + str(row + 3), '', add_format_cell({'bold': True, 'align': 'center', 'bottom': 4, 'right': 5, 'bg_color': 'FFFF02'}))
                    worksheet.write('H' + str(row + 4), '=E' + str(row + 4) + '+F' + str(row + 4) + '+G' + str(row + 4), add_format_cell({'align': 'right', 'bottom': 4, 'right': 5, 'bg_color': 'FFFF02'}))
                    worksheet.write('H' + str(row + 5), '=E' + str(row + 5) + '+F' + str(row + 5) + '+G' + str(row + 5), add_format_cell({'align': 'right', 'bottom': 4, 'right': 5, 'bg_color': 'FFFF02'}))
                    worksheet.write('H' + str(row + 6), '', add_format_cell({'align': 'center', 'bottom': 4, 'right': 5, 'bg_color': 'FFFF02'}))
                    worksheet.write('H' + str(row + 7), '=' + 'H' + str(row + 5) + '/' + 'H' + str(row + 4), add_format_cell({'align': 'center', 'bottom': 5, 'right': 5, 'num_format': '0.00%', 'bg_color': 'FFFF02'}))
                    
                row += 5

            row += 4
                
    pprint("DONE")
    workbook.close()


except Exception as e:
    # log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
    print(traceback.format_exc())
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

# excel = Excel()
# config = Config()
# ftp = Ftp()
common = Common()
base_url = common.base_url()
wff_env = common.wff_env(base_url)
mongodb = Mongodb(MONGODB="worldfone4xs", WFF_ENV=wff_env)
_mongodb = Mongodb(MONGODB="_worldfone4xs", WFF_ENV=wff_env)
# log = open(base_url + "cronjob/python/Loan/log/saveTendencyDelinquent.txt","a")
now = datetime.now()
subUserType = 'LO'
collection = common.getSubUser(subUserType, 'Tendency_delinquent')

try:
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

    fileOutput  = base_url + f'upload/loan/export/Tendency_of_delinquent_loan_occurrence_{str(year)}.xlsx'
    
    month_in_year = {'1': 'JAN', '2': 'FEB', '3': 'MAR', '4': 'APR', '5': 'MAY', '6': 'JUN', '7': 'JUL', '8': 'AUG', '9': 'SEP', '10': 'OCT', '11': 'NOV', '12': 'DEC'}
    due_date_in_month = ['A03', 'A01', 'A02']
    products = ['Bike/PL', 'Card', 'Total']

    row = 4
    col = 1

    #xl_rowcol_to_cell: Convert a zero indexed row and column cell reference to a A1 style string.

    # Create a workbook and add a worksheet.
    workbook = xlsxwriter.Workbook(fileOutput)
    worksheet = workbook.add_worksheet(str(year))

    # Set cell format
    def add_format_cell(format_new = {}):
        format_cells = {
            'left'          : 1,
            'right'         : 1,
            'top'           : 1,
            'bottom'        : 1,
            'align'         : 'center',
            'valign'        : 'vcenter',
            'num_format'    : '#,##0',
            'text_wrap'     : True,
            'font_color'    : 'black'
        }
        if format_cells != {}:
            format_cells.update(format_new)

        return workbook.add_format(format_cells)

    # Common format
    common_format = workbook.add_format({
        'text_wrap'     : True,
        'align'         : 'center',
        'valign'        : 'vcenter',
    })

    worksheet.set_column('A:A', 7, common_format)
    worksheet.set_column('B:AA', 15, common_format)

    worksheet.write('B1', str(year), add_format_cell({'font_color': 'red'}))

    # Header 1
    worksheet.write('E1', '①', add_format_cell(format_new={'font_color': 'red'}))
    worksheet.write('F1', '②', add_format_cell(format_new={'font_color': 'red'}))
    worksheet.write('I1', '③', add_format_cell(format_new={'font_color': 'red'}))
    worksheet.write('L1', '④', add_format_cell(format_new={'font_color': 'red'}))
    
    worksheet.merge_range('M1:N1', 'Unit :  Nunmer', add_format_cell())
    worksheet.merge_range('P1:Q1', '②／①', add_format_cell())
    worksheet.write('S1', '③／①', add_format_cell())
    worksheet.write('T1', '④', add_format_cell())
    worksheet.merge_range('V1:W1', '③／①', add_format_cell())
    worksheet.merge_range('Z1:AA1', '④／①', add_format_cell())

    # Header 2
    worksheet.merge_range('B2:B3', 'GROUP', add_format_cell(format_new={'bg_color': 'FCD5B4'}))
    worksheet.merge_range('C2:C3', '支払日\nPay  day', add_format_cell(format_new={'bg_color': 'FCD5B4'}))
    worksheet.merge_range('D2:D3', '', add_format_cell(format_new={'bg_color': 'FCD5B4'}))
    worksheet.merge_range('E2:E3', '当月請求件数\nRequest  No', add_format_cell(format_new={'bg_color': 'FCD5B4'}))

    worksheet.merge_range('F2:F3', '遅滞発生件数\nNumber of dalays', add_format_cell(format_new={'bg_color': 'DAEEF3'}))
    worksheet.merge_range('G2:G3', '遅滞発生率\nRate of\nnumber', add_format_cell(format_new={'bg_color': 'DAEEF3'}))
    worksheet.merge_range('H2:H3', '', add_format_cell(format_new={'bg_color': 'DAEEF3'}))

    worksheet.merge_range('I2:I3', 'Group 2\nTransition\nnumber', add_format_cell(format_new={'bg_color': 'FFFF99'}))
    worksheet.merge_range('J2:J3', 'Group 2\nRate', add_format_cell(format_new={'bg_color': 'FFFF99'}))
    worksheet.merge_range('K2:K3', '', add_format_cell(format_new={'bg_color': 'FFFF99'}))
    worksheet.merge_range('S2:S3', 'Group 2\nTransition\nrate', add_format_cell(format_new={'bg_color': 'FFFF99'}))
    worksheet.merge_range('V2:V3', 'Group 2\nTransition\nrate', add_format_cell(format_new={'bg_color': 'FFFF99'}))
    worksheet.merge_range('W2:W3', 'Group 2\nTransition\nrate', add_format_cell(format_new={'bg_color': 'FFFF99'}))
    
    worksheet.merge_range('L2:L3', 'Group B\nTransition\nnumber', add_format_cell())
    worksheet.merge_range('M2:M3', 'Group B\nRate', add_format_cell())
    worksheet.merge_range('P2:Q3', 'Rate of\ndalays', add_format_cell())

    worksheet.merge_range('N2:N3', 'Group A\nrate', add_format_cell(format_new={'bg_color': 'CCECFF'}))
    
    worksheet.merge_range('T2:T3', 'Group A\nRecovery\nrate', add_format_cell(format_new={'bg_color': 'FF99FF'}))
    
    worksheet.merge_range('Y2:Y3', 'Group B\nTransition\nrate', add_format_cell(format_new={'bg_color': '9AFFCC'}))
    worksheet.merge_range('Z2:Z3', 'Group B\nTransition\nrate', add_format_cell(format_new={'bg_color': '9AFFCC'}))
    worksheet.merge_range('AA2:AA3', 'Group B\nTransition\nrate', add_format_cell(format_new={'bg_color': '9AFFCC'}))
    
    # Fill data
    for key in month_in_year:
        worksheet.merge_range('A' + str(row) + ':A' + str(row + 8), month_in_year[key], add_format_cell({'border': 1}))
        count_due_date = 0
        for due_date in due_date_in_month:
            worksheet.merge_range('B' + str(row + count_due_date * 3) + ':B' + str(row + count_due_date * 3 + 2), due_date, add_format_cell({'border': 1}))
            for product in products:
                data = mongodb.getOne(MONGO_COLLECTION=collection, WHERE={'for_month': key, 'for_year': str(year), 'debt_group': due_date, 'request_no': {'$ne': None}, 'prod_name': product})
                worksheet.write('D' + str(row), product, add_format_cell({'border': 1}))
                worksheet.write('E' + str(row), data['request_no'] if data != None and 'request_no' in data.keys() else 0, add_format_cell({'align': 'right'}))
                worksheet.write('F' + str(row), data['no_delays'] if data != None and 'no_delays' in data.keys() else 0, add_format_cell({'align': 'right', 'bg_color': 'DAEEF3'}))
                worksheet.write('G' + str(row), '=F' + str(row) + '/E' + str(row), add_format_cell({'num_format': '0.00%', 'bg_color': 'DAEEF3'}))
                worksheet.write('I' + str(row), data['group_2_tran_no'] if data != None and 'group_2_tran_no' in data.keys() else 0, add_format_cell({'align': 'right', 'bg_color': 'FFFFCC'}))
                worksheet.write('J' + str(row), '=I' + str(row) + '/E' + str(row), add_format_cell({'num_format': '0.00%', 'bg_color': 'FFFFCC'}))
                worksheet.write('L' + str(row), data['group_b_trans_no'] if data != None and 'group_b_trans_no' in data.keys() else 0, add_format_cell({'align': 'right'}))
                worksheet.write('M' + str(row), '=L' + str(row) + '/E' + str(row), add_format_cell({'num_format': '0.00%'}))
                worksheet.write('N' + str(row), '=(F' + str(row) + '-L' + str(row) + ')/F' + str(row), add_format_cell({'num_format': '0.00%', 'bg_color': 'CCECFF'}))
                # worksheet.write(row, )
                if product == 'Bike/PL':
                    worksheet.merge_range('C' + str(row) + ':C' + str(row + 2), data['pay_day'] if data != None and 'pay_day' in data.keys() else '', add_format_cell({'border': 1}))
                    worksheet.write('H' + str(row), 'Card', add_format_cell({'font_color': 'red', 'bg_color': 'DAEEF3'}))
                    worksheet.write('K' + str(row), 'Card', add_format_cell({'font_color': 'red', 'bg_color': 'FFFFCC'}))
                    worksheet.write('O' + str(row), due_date)

                if product == 'Card':
                    worksheet.write('H' + str(row), '=G' + str(row), add_format_cell({'font_color': 'red', 'bg_color': 'DAEEF3', 'num_format': '0.00%'}))
                    worksheet.write('K' + str(row), '=J' + str(row), add_format_cell({'font_color': 'red', 'bg_color': 'FFFFCC', 'num_format': '0.00%'}))

                if product == 'Total':
                    worksheet.write('E' + str(row), '=E' + str(row - 2) + '+E' + str(row - 1), add_format_cell({'align': 'right'}))
                    worksheet.write('F' + str(row), '=F' + str(row - 2) + '+F' + str(row - 1), add_format_cell({'align': 'right', 'bg_color': 'DAEEF3'}))
                    worksheet.write('I' + str(row), '=I' + str(row - 2) + '+I' + str(row - 1), add_format_cell({'align': 'right', 'bg_color': 'FFFFCC'}))
                    worksheet.write('L' + str(row), '=L' + str(row - 2) + '+L' + str(row - 1), add_format_cell({'align': 'right'}))
                    worksheet.write('H' + str(row), '', add_format_cell({'align': 'right', 'bg_color': 'DAEEF3'}))
                    worksheet.write('K' + str(row), '', add_format_cell({'align': 'right', 'bg_color': 'FFFFCC'}))

                row += 1

        fix_position = 9 * (int(key) - 1)
        fix_position_three_month = 9 * (int(key) - 3)

        worksheet.merge_range('P' + str(row - 9) + ':P' + str(row - 1), '=(F' + str(6 + fix_position) + '+F' + str(9 + fix_position) + '+F' + str(12 + fix_position) + ')/(E' + str(6 + fix_position) + '+E' + str(9 + fix_position) + '+E' + str(12 + fix_position) + ')', add_format_cell({'num_format': '0.00%'}))
        worksheet.merge_range('S' + str(row - 9) + ':S' + str(row - 1), '=(I' + str(6 + fix_position) + '+I' + str(9 + fix_position) + '+I' + str(12 + fix_position) + ')/(E' + str(6 + fix_position) + '+E' + str(9 + fix_position) + '+E' + str(12 + fix_position) + ')', add_format_cell({'num_format': '0.00%'}))
        worksheet.merge_range('T' + str(row - 9) + ':T' + str(row - 1), '=((F' + str(6 + fix_position) + '+F' + str(9 + fix_position) + '+F' + str(12 + fix_position) + ')-(L' + str(6 + fix_position) + '+L' + str(9 + fix_position) + '+L' + str(12 + fix_position) + '))/(F' + str(6 + fix_position) + '+F' + str(9 + fix_position) + '+F' + str(12 + fix_position) + ')', add_format_cell({'num_format': '0.00%'}))
        worksheet.merge_range('Y' + str(row - 9) + ':Y' + str(row - 1), '=(L' + str(6 + fix_position) + '+L' + str(9 + fix_position) + '+L' + str(12 + fix_position) + ')/(E' + str(6 + fix_position) + '+E' + str(9 + fix_position) + '+E' + str(12 + fix_position) + ')', add_format_cell({'num_format': '0.00%'}))

        if key in ['3', '6', '9', '12']:
            worksheet.merge_range('Q' + str(row - 27) + ':Q' + str(row - 1), '=(F' + str(6 + fix_position_three_month) + '+F' + str(9 + fix_position_three_month) + '+F' + str(12 + fix_position_three_month) + '+F' + str(15 + fix_position_three_month) + '+F' + str(18 + fix_position_three_month) + '+F' + str(21 + fix_position_three_month) + '+F' + str(24 + fix_position_three_month) + '+F' + str(27 + fix_position_three_month) + '+F' + str(30 + fix_position_three_month) + ')/(E' + str(6 + fix_position_three_month) + '+E' + str(9 + fix_position_three_month) + '+E' + str(12 + fix_position_three_month) + '+E' + str(15 + fix_position_three_month) + '+E' + str(18 + fix_position_three_month) + '+E' + str(21 + fix_position_three_month) + '+E' + str(24 + fix_position_three_month) + '+E' + str(27 + fix_position_three_month) + '+E' + str(30 + fix_position_three_month) + ')', add_format_cell({'num_format': '0.00%'}))
            worksheet.merge_range('V' + str(row - 27) + ':V' + str(row - 1), '=(I' + str(6 + fix_position_three_month) + '+I' + str(9 + fix_position_three_month) + '+I' + str(12 + fix_position_three_month) + '+I' + str(15 + fix_position_three_month) + '+I' + str(18 + fix_position_three_month) + '+I' + str(21 + fix_position_three_month) + '+I' + str(24 + fix_position_three_month) + '+I' + str(27 + fix_position_three_month) + '+I' + str(30 + fix_position_three_month) + ')/(E' + str(6 + fix_position_three_month) + '+E' + str(9 + fix_position_three_month) + '+E' + str(12 + fix_position_three_month) + '+E' + str(15 + fix_position_three_month) + '+E' + str(18 + fix_position_three_month) + '+E' + str(21 + fix_position_three_month) + '+E' + str(24 + fix_position_three_month) + '+E' + str(27 + fix_position_three_month) + '+E' + str(30 + fix_position_three_month) + ')', add_format_cell({'num_format': '0.00%'}))
            worksheet.merge_range('Z' + str(row - 27) + ':Z' + str(row - 1), '=(L' + str(6 + fix_position_three_month) + '+L' + str(9 + fix_position_three_month) + '+L' + str(12 + fix_position_three_month) + '+L' + str(15 + fix_position_three_month) + '+L' + str(18 + fix_position_three_month) + '+L' + str(21 + fix_position_three_month) + '+L' + str(24 + fix_position_three_month) + '+L' + str(27 + fix_position_three_month) + '+L' + str(30 + fix_position_three_month) + ')/(E' + str(6 + fix_position_three_month) + '+E' + str(9 + fix_position_three_month) + '+E' + str(12 + fix_position_three_month) + '+E' + str(15 + fix_position_three_month) + '+E' + str(18 + fix_position_three_month) + '+E' + str(21 + fix_position_three_month) + '+E' + str(24 + fix_position_three_month) + '+E' + str(27 + fix_position_three_month) + '+E' + str(30 + fix_position_three_month) + ')', add_format_cell({'num_format': '0.00%'}))

            worksheet.write('W' + str(row - 27), 'Bike.PL')
            worksheet.write('W' + str(row - 11), 'Card')

            worksheet.merge_range('W' + str(row - 26) + ':W' + str(row - 12), '=(I' + str(4 + fix_position_three_month) + '+I' + str(7 + fix_position_three_month) + '+I' + str(10 + fix_position_three_month) + '+I' + str(13 + fix_position_three_month) + '+I' + str(16 + fix_position_three_month) + '+I' + str(19 + fix_position_three_month) + '+I' + str(22 + fix_position_three_month) + '+I' + str(25 + fix_position_three_month) + '+I' + str(28 + fix_position_three_month) + ')/(E' + str(4 + fix_position_three_month) + '+E' + str(7 + fix_position_three_month) + '+E' + str(10 + fix_position_three_month) + '+E' + str(13 + fix_position_three_month) + '+E' + str(16 + fix_position_three_month) + '+E' + str(19 + fix_position_three_month) + '+E' + str(22 + fix_position_three_month) + '+E' + str(25 + fix_position_three_month) + '+E' + str(28 + fix_position_three_month) + ')', add_format_cell({'num_format': '0.00%'}))
            worksheet.merge_range('AA' + str(row - 26) + ':AA' + str(row - 12), '=(L' + str(4 + fix_position_three_month) + '+L' + str(7 + fix_position_three_month) + '+L' + str(10 + fix_position_three_month) + '+L' + str(13 + fix_position_three_month) + '+L' + str(16 + fix_position_three_month) + '+L' + str(19 + fix_position_three_month) + '+L' + str(22 + fix_position_three_month) + '+L' + str(25 + fix_position_three_month) + '+L' + str(28 + fix_position_three_month) + ')/(E' + str(4 + fix_position_three_month) + '+E' + str(7 + fix_position_three_month) + '+E' + str(10 + fix_position_three_month) + '+E' + str(13 + fix_position_three_month) + '+E' + str(16 + fix_position_three_month) + '+E' + str(19 + fix_position_three_month) + '+E' + str(22 + fix_position_three_month) + '+E' + str(25 + fix_position_three_month) + '+E' + str(28 + fix_position_three_month) + ')', add_format_cell({'num_format': '0.00%'}))

            worksheet.merge_range('W' + str(row - 10) + ':W' + str(row - 1), '=(I' + str(11 + fix_position_three_month) + '+I' + str(20 + fix_position_three_month) + '+I' + str(29 + fix_position_three_month) + '+I' + str(5 + fix_position_three_month) + '+I' + str(8 + fix_position_three_month) + '+I' + str(14 + fix_position_three_month) + '+I' + str(17 + fix_position_three_month) + '+I' + str(23 + fix_position_three_month) + '+I' + str(26 + fix_position_three_month) + ')/(E' + str(11 + fix_position_three_month) + '+E' + str(20 + fix_position_three_month) + '+E' + str(29 + fix_position_three_month) + '+E' + str(5 + fix_position_three_month) + '+E' + str(8 + fix_position_three_month) + '+E' + str(14 + fix_position_three_month) + '+E' + str(17 + fix_position_three_month) + '+E' + str(23 + fix_position_three_month) + '+E' + str(26 + fix_position_three_month) + ')', add_format_cell({'num_format': '0.00%'}))
            worksheet.merge_range('AA' + str(row - 10) + ':AA' + str(row - 1), '=(L' + str(11 + fix_position_three_month) + '+L' + str(20 + fix_position_three_month) + '+L' + str(29 + fix_position_three_month) + '+L' + str(5 + fix_position_three_month) + '+L' + str(8 + fix_position_three_month) + '+L' + str(14 + fix_position_three_month) + '+L' + str(17 + fix_position_three_month) + '+L' + str(23 + fix_position_three_month) + '+L' + str(26 + fix_position_three_month) + ')/(E' + str(11 + fix_position_three_month) + '+E' + str(20 + fix_position_three_month) + '+E' + str(29 + fix_position_three_month) + '+E' + str(5 + fix_position_three_month) + '+E' + str(8 + fix_position_three_month) + '+E' + str(14 + fix_position_three_month) + '+E' + str(17 + fix_position_three_month) + '+E' + str(23 + fix_position_three_month) + '+E' + str(26 + fix_position_three_month) + ')', add_format_cell({'num_format': '0.00%'}))

            worksheet.write('AA' + str(row - 27), 'Bike.PL')
            worksheet.write('AA' + str(row - 11), 'Card')

    workbook.close()
    pprint('DONE')

except Exception as e:
    # log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
    print(traceback.format_exc())
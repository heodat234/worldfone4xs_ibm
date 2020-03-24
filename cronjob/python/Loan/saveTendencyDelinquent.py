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
from math import ceil

excel = Excel()
config = Config()
ftp = Ftp()
common = Common()
base_url = common.base_url()
wff_env = common.wff_env(base_url)
mongodb = Mongodb(MONGODB="worldfone4xs", WFF_ENV=wff_env)
_mongodb = Mongodb(MONGODB="_worldfone4xs", WFF_ENV=wff_env)
# log = open(base_url + "cronjob/python/Loan/log/importSBV.txt","a")
now = datetime.now()
subUserType = 'LO'
collection = common.getSubUser(subUserType, 'Tendency_delinquent')
lnjc05_today = 'LNJC05'
list_of_acc_today = 'List_of_account_in_collection'
lnjc05_yesterday = 'LNJC05_yesterday'
list_of_acc_yesterday = 'List_of_account_in_collection_yesterday'
zaccf_col = 'ZACCF_report'
sbv_col = 'SBV'
diallist_detail = 'Diallist_detail'
sbv_store = 'SBV_Stored'
SBV_Stored_yesterday = 'SBV_Stored_Old'

try:
    today = date.today()
    # today = datetime.strptime('23/03/2020', "%d/%m/%Y").date()
    yesterday = today - timedelta(days=1)
    day = today.day
    month = today.month
    year = today.year
    weekday = today.weekday()
    tomorrow = today + timedelta(days=1)
    tomorrow_month = tomorrow.month
    tomorrow_year = tomorrow.year
    lastDayOfMonth = calendar.monthrange(year, month)[1]
    insertData = []
    todayString = today.strftime("%d/%m/%Y")
    yesterdayString = yesterday.strftime("%d/%m/%Y")
    todayTimeStamp = int(time.mktime(time.strptime(str(todayString + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
    startMonth = int(time.mktime(time.strptime(str('01/' + str(month) + '/' + str(year) + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
    startMonthStarttime = startMonth
    startMonthEndtime = int(time.mktime(time.strptime(str('01/' + str(month) + '/' + str(year) + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))
    
    first = today.replace(day=1)
    lastMonthLastDate = first - timedelta(days=1)
    lastMonthMonth = lastMonthLastDate.month
    lastMonthYear = lastMonthLastDate.year
    lastMonthStarttime = int(time.mktime(time.strptime(str('01/' + str(lastMonthMonth) + '/' + str(lastMonthYear) + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
    lastMonthEndtime = int(time.mktime(time.strptime(str(str(lastMonthLastDate.day) + '/' + str(lastMonthMonth) + '/' + str(lastMonthYear) + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))

    starttime = int(time.mktime(time.strptime(str(todayString + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
    endtime = int(time.mktime(time.strptime(str(todayString + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))

    yesterday_starttime = int(time.mktime(time.strptime(str(yesterdayString + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
    yesterday_endtime = int(time.mktime(time.strptime(str(yesterdayString + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))

    products = list(_mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Jsondata'), WHERE={'tags': ['product', 'group']}, SORT=[("code", 1)]))

    dueDates = list(mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_due_date'), WHERE={'for_month': {'$in': [str(lastMonthMonth), str(month)]}, 'for_year': {'$in': [str(year), str(lastMonthYear)]}}))
    
    if day == 1:
        report_month = lastMonthMonth
        report_year = lastMonthYear
    else:
        report_month = month
        report_year = year

    reportDate = {}
    checkReportDate = []
    for dueDate in dueDates:
        if str(dueDate['due_date']) not in reportDate.keys():
            reportDate[str(dueDate['due_date'])] = []

        if str(dueDate['due_date_add_1']) not in reportDate.keys():
            reportDate[str(dueDate['due_date_add_1'])] = []

        if str(dueDate['due_date'] + 864000) not in reportDate.keys():
            reportDate[str(dueDate['due_date'] + 864000)] = []

        reportDate[str(dueDate['due_date'])].append(dueDate)
        reportDate[str(dueDate['due_date_add_1'])].append(dueDate)
        reportDate[str(dueDate['due_date'] + 864000)].append({'createdAt': datetime.now(), 'due_date': dueDate['due_date'], 'for_month': dueDate['for_month'], 'for_year': dueDate['for_year'], 'debt_group': dueDate['debt_group'], 'createdBy': 'system', 'due_date_add_1': dueDate['due_date_add_1'] + 864000, 'is_due_date': False})
        checkReportDate.append(dueDate['due_date'])
        checkReportDate.append(dueDate['due_date_add_1'])
        checkReportDate.append(dueDate['due_date'] + 864000)
    
    # pprint(reportDate[str(todayTimeStamp)])
    # sys.exit()

    if todayTimeStamp not in checkReportDate:
        sys.exit()

    for report_day in list(reportDate[str(todayTimeStamp)]):
        temp_sibs = {
            'for_month'                 : str(month) if report_day['debt_group'] != '03' else str(tomorrow_month),
            'for_month_name'            : today.strftime("%b") if report_day['debt_group'] != '03' else tomorrow.strftime('%b'),
            'for_year'                  : str(year) if report_day['debt_group'] != '03' else str(tomorrow_year),
            'debt_group'                : 'A' + report_day['debt_group'],
            'pay_day'                   : datetime.fromtimestamp(report_day['due_date']).strftime('%d/%m'),
            'due_date'                  : report_day['due_date'],
            'prod_name'                 : 'Bike/PL'
        }

        temp_card = {
            'for_month'                 : str(month) if report_day['debt_group'] != '03' else str(tomorrow_month),
            'for_month_name'            : today.strftime("%b") if report_day['debt_group'] != '03' else tomorrow.strftime('%b'),
            'for_year'                  : str(year) if report_day['debt_group'] != '03' else str(tomorrow_year),
            'debt_group'                : 'A' + report_day['debt_group'],
            'pay_day'                   : datetime.fromtimestamp(report_day['due_date']).strftime('%d/%m'),
            'due_date'                  : report_day['due_date'],
            'prod_name'                 : 'Card'
        }

        temp_sibs_last_month = {}
        temp_card_last_month = {}

        if (report_day['due_date'] + 864000) == todayTimeStamp:
            # Update thong tin 10 ngay sau due date
            temp_sibs_10_days = {}
            temp_card_10_days = {}
            lnjc05_aggregate = [{
                '$match'         : {
                    'group_id'   : 'A' + report_day['debt_group'],
                }
            }]
            lnjc05_info = list(mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, lnjc05_today), aggregate_pipeline=lnjc05_aggregate))
            temp_sibs_10_days['group_2_tran_no'] = len(lnjc05_info)
            mongodb.update(MONGO_COLLECTION=collection, WHERE={'for_month': str(report_day['for_month']), 'for_year': str(report_day['for_year']), 'debt_group': 'A' + report_day['debt_group'], 'prod_name': 'Bike/PL'}, VALUE=temp_sibs_10_days)

            list_acc_info_temp = list(mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, list_of_acc_today), SELECT=['account_number']))
            list_acc_info = list(common.array_column(list_acc_info_temp, 'account_number'))
            check_with_store = list(mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, sbv_store), WHERE={'contract_no': {'$in': list_acc_info}, 'overdue_indicator': 'A', 'kydue': report_day['debt_group']}))
            temp_card_10_days['group_2_tran_no'] = len(check_with_store)
            mongodb.update(MONGO_COLLECTION=collection, WHERE={'for_month': str(report_day['for_month']), 'for_year': str(report_day['for_year']), 'debt_group': 'A' + report_day['debt_group'], 'prod_name': 'Card'}, VALUE=temp_card_10_days)
        else:
            if report_day['due_date'] == todayTimeStamp:
                zaccf_aggregate = [{
                    '$match'                : {
                        'NP_DT8'            : datetime.fromtimestamp(report_day['due_date']).strftime('%d%m%Y')
                    }
                }]
                zaccf_info = list(mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, zaccf_col), aggregate_pipeline=zaccf_aggregate))
                temp_sibs['request_no'] = len(zaccf_info)

                sbv_aggregate = [{
                    '$match'                    : {
                        'statement_date'        : datetime.fromtimestamp(report_day['due_date'] - 1728000).strftime('%d%m%Y'),
                        'minimum_payable_amt'   : {'$ne': 0}
                    }
                }]
                sbv_info = list(mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, sbv_col), aggregate_pipeline=sbv_aggregate))
                temp_card['request_no'] = len(sbv_info)
            
            if report_day['due_date_add_1'] == todayTimeStamp:
                lnjc05_aggregate = [{
                    '$match'                : {
                        # 'due_date'          : report_day['due_date'],
                        'group_id'          : 'A' + report_day['debt_group']
                    }
                }]
                lnjc05_info = list(mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, lnjc05_today), aggregate_pipeline=lnjc05_aggregate))
                temp_sibs['no_delays'] = len(lnjc05_info)

                list_acc_info_temp = list(mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, list_of_acc_today), SELECT=['account_number']))
                accs = list(common.array_column(list_acc_info_temp, 'account_number'))
                list_acc_info = list(mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, sbv_store), WHERE={'contract_no': {'$in': accs}, 'overdue_indicator': 'A', 'kydue': report_day['debt_group']}))
                len(list_acc_info)
                temp_card['no_delays'] = len(list_acc_info)

            # Update thong tin cho thang truoc
            lastMonthDueDate = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Tendency_delinquent'), WHERE={'for_month': str(lastMonthMonth), 'for_year': str(lastMonthYear), 'debt_group': 'A' + report_day['debt_group'], 'prod_name': 'Bike/PL'})
            
            if lastMonthDueDate != None:
                list_acc_due_date_sibs_temp = list(mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, lnjc05_yesterday), WHERE={'group_id': 'A' + report_day['debt_group']}, SELECT=['account_number', 'group_id']))
                list_acc_due_date_sibs = list(common.array_column(list_dict=list_acc_due_date_sibs_temp, value='account_number'))
                checkAccExistedSibs = list(mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, lnjc05_today), WHERE={'account_number': {'$in': list_acc_due_date_sibs}, 'group_id': 'B' + report_day['debt_group']}, SELECT=['account_number']))
                temp_sibs_last_month['group_b_trans_no'] = len(checkAccExistedSibs)

                list_acc_due_date_card_sbv_stored = list(mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, SBV_Stored_yesterday), WHERE={'overdue_indicator': 'A', 'kydue': report_day['debt_group']}, SELECT=['contract_no']))
                list_acc_due_date_card_sbv_stored_contract_no = list(common.array_column(list_acc_due_date_card_sbv_stored, 'contract_no'))
                checkAccExistedCard = list(mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, sbv_store), WHERE={'contract_no': {'$in': list_acc_due_date_card_sbv_stored_contract_no}, 'overdue_indicator': 'B', 'kydue': report_day['debt_group']}))
                temp_card_last_month['group_b_trans_no'] = len(checkAccExistedCard)

                if temp_sibs_last_month != {}:
                    mongodb.update(MONGO_COLLECTION=collection, WHERE={'for_month': str(lastMonthMonth), 'for_year': str(lastMonthYear), 'debt_group': 'A' + report_day['debt_group'], 'prod_name': 'Bike/PL'}, VALUE=temp_sibs_last_month)
            
                if temp_card_last_month != {}:
                    mongodb.update(MONGO_COLLECTION=collection, WHERE={'for_month': str(lastMonthMonth), 'for_year': str(lastMonthYear), 'debt_group': 'A' + report_day['debt_group'], 'prod_name': 'Card'}, VALUE=temp_card_last_month)

        temp_sibs['created_at'] = todayTimeStamp
        temp_card['created_at'] = todayTimeStamp
        mongodb.update(MONGO_COLLECTION=collection, WHERE={'for_month': str(month), 'for_year': str(year), 'debt_group': 'A' + report_day['debt_group'], 'prod_name': 'Bike/PL'}, VALUE=temp_sibs)
        mongodb.update(MONGO_COLLECTION=collection, WHERE={'for_month': str(month), 'for_year': str(year), 'debt_group': 'A' + report_day['debt_group'], 'prod_name': 'Card'}, VALUE=temp_card)

        pprint(todayString)
        pprint("DONE")
    
except Exception as e:
    # log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
    print(traceback.format_exc())

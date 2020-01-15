#!/usr/bin/python3.6
# -*- coding: utf-8 -*-

import re
import ftplib
import calendar
import time
import sys
import os
import json
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
collection = common.getSubUser(subUserType, 'Daily_prod_working_day')
try:
    insertData = []
    updateData = []
    listDebtGroup = []
    dpWorkingdaysdaycol = {'1': 'No. of Overdue accounts', '2': 'No. of Paid accounts end of day', '3': 'No. of Paid accounts Accumulated', '4': 'Collected ratio (account)', '5': 'Overdue outstanding balance', '6': 'Collected amount (end of day)', '7': 'Collected amount Accumulated', '8': 'Collected ratio (amount)'}
    due = {
        '01'    : '12th',
        '02'    : '22nd',
        '03'    : '31st'
    }

    today = date.today()
    # today = datetime.strptime('20/11/2019', "%d/%m/%Y").date()

    day = today.day
    month = today.month
    year = today.year
    weekday = today.weekday()
    lastDayOfMonth = calendar.monthrange(year, month)[1]

    todayString = today.strftime("%d/%m/%Y")
    todayTimeStamp = int(time.mktime(time.strptime(str(todayString + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))

    startMonth = int(time.mktime(time.strptime(str('01/' + str(month) + '/' + str(year) + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
    endMonth = int(time.mktime(time.strptime(str(str(lastDayOfMonth) + '/' + str(month) + '/' + str(year) + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))
    
    holidayOfMonth = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_off_sys'))
    listHoliday = map(lambda offDateRow: {offDateRow['off_date']}, holidayOfMonth)

    dueDateThisMonth = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_due_date'))

    if todayTimeStamp in listHoliday or (weekday == 5) or weekday == 6:
        sys.exit()

    todayString = today.strftime("%d/%m/%Y")
    starttime = int(time.mktime(time.strptime(str(todayString + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
    endtime = int(time.mktime(time.strptime(str(todayString + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))

    yesterday_starttime = starttime - 86400
    yesterday_endtime = endtime - 86400

    mainProduct = {}
    mainProductRaw = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Product'))
    for prod in mainProductRaw:
        mainProduct[prod['code']] = prod['name']

    debtGroup = _mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Jsondata'), WHERE={'tags': ['debt', 'group']})
    dueDate = _mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Jsondata'), WHERE={'tags': ['debt', 'duedate']})

    for group in debtGroup['data']:
        for duedate in dueDate['data']:
            listDebtGroup.append(group['text'] + duedate['text'])

    listDebtGroup = sorted(listDebtGroup)

    listGroupProductRaw = _mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Jsondata'), WHERE={'tags': ['group', 'debt', 'product']})
    listGroupProduct = listGroupProductRaw['data']

    lnjc05 = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'LNJC05'))
    total_lnjc05 = 0
    total_cur_bal_lnjc05 = 0
    for lnjc05_row in lnjc05:
        total_lnjc05 += 1
        total_cur_bal_lnjc05 += lnjc05_row['current_balance']
    
    list_acc = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'List_of_account_in_collection'))
    total_list_acc = 0
    total_cur_bal_list_acc = 0
    for list_acc_row in list_acc:
        total_list_acc += 1
        total_cur_bal_list_acc += list_acc_row['cur_bal']

    for debtGroupCell in list(listDebtGroup):
        if debtGroupCell[0:1] is not 'F':
            dueDayOfMonth = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_due_date'), WHERE={'for_month': str(month), 'debt_group': debtGroupCell[1:3]})
            dueDayLastMonth = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_due_date'), WHERE={'for_month': str(month - 1), 'debt_group': debtGroupCell[1:3]})
            if todayTimeStamp > dueDayOfMonth['due_date_add_1']:
                todayIndex = str(common.countWorkingDaysBetweendate(starttime = dueDayOfMonth['due_date_add_1'], endtime = todayTimeStamp, mongodb=mongodb))
            else:
                todayIndex = str(common.countWorkingDaysBetweendate(starttime = dueDayLastMonth['due_date_add_1'], endtime = todayTimeStamp, mongodb=mongodb))
            
            for groupProductCell in listGroupProduct:
                for key in dpWorkingdaysdaycol:
                    groupInfoByDueDate = list(mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Group'), WHERE={'debt_groups': debtGroupCell, 'name': {"$regex": groupProductCell['text'] + '.*'}}))
                    groupInfoByDueDate.extend([{'name': 'Total'}])
                    for groupCell in groupInfoByDueDate:
                        debtList = []
                        cur_bal = 0
                        if groupProductCell['value'] == 'SIBS':
                            count_acc = total_lnjc05
                            cur_bal = total_cur_bal_lnjc05
                                
                        if groupProductCell['value'] == 'Card':
                            count_acc = total_list_acc
                            cur_bal = total_cur_bal_list_acc

                        no_overdue = count_acc
                        no_paid_acc_accumulayed = 0
                        no_overdue_amt = cur_bal
                        no_paid_acc_accumulayed_amt = 0
                        if(groupCell['name'] != 'Total'):
                            temp = {
                                'group'         : debtGroupCell[0:1] + ' GROUP',
                                'month'         : today.strftime("%b-%y"),
                                'due'           : due[debtGroupCell[1:3]],
                                'product'       : groupProductCell['value'],
                                'day'           : dpWorkingdaysdaycol[key],
                                'day_code'      : key,
                                'team_name'     : groupCell['name'],
                                'team_id'       : str(groupCell['_id']),
                            }
        
                            if todayTimeStamp < dueDayOfMonth['due_date_add_1']:
                                temp['due_date'] = dueDayLastMonth['due_date'] if dueDayLastMonth is not None else ''
                                # #Lay gia tri no vao ngay due date + 1#
                                # incidenceInfo = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Due_date_next_date'), WHERE={'for_month': str(month - 1), 'team_id': str(groupCell['_id'])})
                                # #Lay gia tri no vao ngay due date + 1#
                            else:
                                # incidenceInfo = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Due_date_next_date'), WHERE={'for_month': str(month), 'team_id': str(groupCell['_id'])})
                                temp['due_date'] = dueDayOfMonth['due_date']

                            if key == '2':
                                temp['index_' + todayIndex] = 0

                            if key == '6':
                                temp['index_' + todayIndex] = 0

                            if key == '1':
                                temp['index_' + todayIndex] = no_overdue

                            if key == '3':
                                temp['index_' + todayIndex] = no_paid_acc_accumulayed

                            if key == '5':
                                temp['index_' + todayIndex] = no_overdue_amt

                            if key == '7':
                                temp['index_' + todayIndex] = no_paid_acc_accumulayed_amt

                            # Không cho tính target trong này, phải lấy từ bảng đầu tháng
                            # if todayTimeStamp == dueDayOfMonth['due_date_add_1']:
                            #     temp['target'] = target['target'],
                            #     temp['target_acc'] = (no_overdue * int(temp['target'])) / 100
                            #     temp['target_amt'] = (no_overdue_amt * int(temp['target'])) / 100

                            temp['start_acc'] = 0
                            temp['start_amt'] = 0

                            if key == '4':
                                # temp['col_ratio_acc'] = no_acc_end_date / no_overdue if no_overdue not in [None, 0] else 0
                                temp['index_' + todayIndex] = 0

                            if key == '8':
                                temp['index_' + todayIndex] = 0
                            
                            if todayTimeStamp != dueDayOfMonth['due_date_add_1']:
                                yesterdayData = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Daily_prod_working_day'), WHERE={'team_id': str(groupCell['_id']), 'day_code': key, 'updated_at': {'$gte': yesterday_starttime, '$lte': yesterday_endtime}})
                                no_acc_end_date = 0
                                no_acc_end_date_amt = 0
                                if yesterdayData is not None:
                                    # pprint(temp)
                                    no_acc_end_date = yesterdayData['no_overdue'] - no_overdue
                                    no_acc_end_date_amt = yesterdayData['no_overdue_amt'] - no_overdue_amt
                                    updateDataYesterday = {}
                                    if key == '2':
                                        updateDataYesterday['index_' + (todayIndex - 1)] = no_acc_end_date

                                    if key == '6':
                                        updateDataYesterday['index_' + (todayIndex - 1)] = no_acc_end_date_amt

                                    updateDataYesterday['index_' + todayIndex] = temp['index_' + todayIndex]
                                    updateDataYesterday['updated_at'] = time.time()
                                    # pprint(temp)
                                    mongodb.update(MONGO_COLLECTION=common.getSubUser(subUserType, 'Daily_prod_working_day'), WHERE={'team_id': str(groupCell['_id']), 'day_code': key, 'updated_at': {'$gte': yesterday_starttime, '$lte': yesterday_endtime}}, VALUE=updateDataYesterday)
                                else:
                                    pprint(temp)
                                    mongodb.insert(MONGO_COLLECTION=common.getSubUser(subUserType, 'Daily_prod_working_day'), insert_data=temp)
                                # checkYesterdayExist = 
   
    print('DONE')
except Exception as e:
    # log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
    pprint(str(e))
        
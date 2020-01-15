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

    # today = date.today()
    today = datetime.strptime('13/12/2019', "%d/%m/%Y").date()

    day = today.day
    month = today.month
    year = today.year
    weekday = today.weekday()
    lastDayOfMonth = calendar.monthrange(year, month)[1]

    todayString = today.strftime("%d/%m/%Y")
    todayTimeStamp = int(time.mktime(time.strptime(str(todayString + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))

    startMonth = int(time.mktime(time.strptime(str('01/' + str(month) + '/' + str(year) + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
    endMonth = int(time.mktime(time.strptime(str(str(lastDayOfMonth) + '/' + str(month) + '/' + str(year) + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))
    
    # holidayOfMonth = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_off_sys'))
    # listHoliday = map(lambda offDateRow: {offDateRow['off_date']}, holidayOfMonth)

    dueDateThisMonth = list(mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_due_date')))

    # if todayTimeStamp in listHoliday:
    #     sys.exit()

    todayString = today.strftime("%d/%m/%Y")
    starttime = int(time.mktime(time.strptime(str(todayString + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
    endtime = int(time.mktime(time.strptime(str(todayString + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))

    holidayOfMonth = list(mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_off_sys'), WHERE={'off_date': {'$gte': starttime, '$lte': endtime}}))
    if len(holidayOfMonth) > 0:
        sys.exit()

    # yesterday_starttime = starttime - 86400
    # yesterday_endtime = endtime - 86400
    yesterday_starttime = int(time.mktime(time.strptime(str("24/12/2019 00:00:00"), "%d/%m/%Y %H:%M:%S")))
    yesterday_endtime = int(time.mktime(time.strptime(str("24/12/2019 23:59:59"), "%d/%m/%Y %H:%M:%S")))
    # pprint(yesterday_endtime)
    mainProduct = {}
    mainProductRaw = list(mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Product')))
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

    # lnjc05 = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'LNJC05'))
    # total_lnjc05 = 0
    # total_cur_bal_lnjc05 = 0
    # for lnjc05_row in lnjc05:
    #     total_lnjc05 += 1
    #     total_cur_bal_lnjc05 += lnjc05_row['current_balance']
    
    list_acc = list(mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'List_of_account_in_collection')))
    total_list_acc = 0
    total_cur_bal_list_acc = 0
    for list_acc_row in list_acc:
        total_list_acc += 1
        total_cur_bal_list_acc += list_acc_row['cur_bal']

    for debtGroupCell in list(listDebtGroup):
        if debtGroupCell[0:1] is not 'F':
            dueDayOfMonth = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_due_date'), WHERE={'for_month': str(month), 'debt_group': debtGroupCell[1:3]})
            dueDayLastMonth = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_due_date'), WHERE={'for_month': str(month - 1), 'debt_group': debtGroupCell[1:3]})
            # dueDateData = 0
            # pprint(dueDayOfMonth)
            if todayTimeStamp > dueDayOfMonth['due_date_add_1']:
                todayIndex = int(common.countWorkingDaysBetweendate(starttime = dueDayOfMonth['due_date_add_1'], endtime = todayTimeStamp, mongodb=mongodb))
            else:
                todayIndex = int(common.countWorkingDaysBetweendate(starttime = dueDayLastMonth['due_date_add_1'], endtime = todayTimeStamp, mongodb=mongodb))
            
            total_no_overdue = 0
            total_no_paid_acc_end_day = 0
            total_no_paid_acc_accumulated = 0
            total_collected_acc = 0
            total_overdue_outstanding_bal = 0
            total_collected_amt_and_day = 0
            total_collected_amt_accumulated = 0
            total_collected_ratio_amt = 0

            total_start_acc = 0
            total_start_amt = 0
            total_target_acc = 0
            total_target_amt = 0

            if todayTimeStamp == dueDayOfMonth['due_date_add_1']:
                pprint("DUNG")
                for groupProductCell in listGroupProduct:
                    for key in dpWorkingdaysdaycol:
                        groupInfoByDueDate = list(mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Group_product'), WHERE={'debt_groups': debtGroupCell, 'name': {"$regex": groupProductCell['text'] + '.*'}}))
                        if len(groupInfoByDueDate) > 0:
                            groupInfoByDueDate.extend([{'name': 'Total'}])
                            for groupCell in groupInfoByDueDate:
                                if groupCell['name'] != 'Total':
                                    if 'G2' in groupCell['name'] or 'G3' in groupCell['name']:
                                        continue

                                    groupCell['name'] = groupCell['name'].replace('/G1', '')
                                    dueDateValue = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Due_date_next_date'), WHERE={'due_date_one': {'$gte': starttime, '$lte': starttime}, 'team_id': str(groupCell['_id'])})
                                    target = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Target'), WHERE={'group.id': str(groupCell['_id'])})
                                    temp = {
                                        'group'         : debtGroupCell[0:1] + ' GROUP',
                                        'month'         : today.strftime("%b-%y"),
                                        'due'           : due[debtGroupCell[1:3]],
                                        'product'       : groupProductCell['value'],
                                        'due_date'      : todayTimeStamp - 86400,
                                        'day'           : dpWorkingdaysdaycol[key],
                                        'day_code'      : key,
                                        'team_name'     : groupCell['name'],
                                        'team_id'       : str(groupCell['_id']),
                                        'created_at'    : time.time(),
                                        'created_by'    : 'system',
                                        # 'updated_by'    : 'system',
                                        # 'updated_at'     : yesterday_starttime,
                                        # 'updated_at'    : time.time()
                                    }

                                    temp['start_acc']   = dueDateValue['debt_acc_no'] if dueDateValue['debt_acc_no'] is not None else 0
                                    temp['start_amt']   = dueDateValue['current_balance_total'] if dueDateValue['current_balance_total'] is not None else 0
                                    temp['target_acc']  = (dueDateValue['debt_acc_no'] * int(target['target'])) / 100 if dueDateValue['debt_acc_no'] is not None and target['target'] is not None else 0
                                    temp['target_amt']  = (dueDateValue['current_balance_total'] * int(target['target'])) / 100 if dueDateValue['current_balance_total'] is not None and target['target'] is not None else 0
                                    total_start_acc += temp['start_acc']
                                    total_start_amt += temp['start_amt']
                                    total_target_acc += temp['target_acc']
                                    total_target_amt += temp['target_amt']

                                    if key == '1':
                                        temp['index_1']     = dueDateValue['debt_acc_no'] if dueDateValue['debt_acc_no'] is not None else 0
                                        total_no_overdue += temp['index_1']
                                    
                                    if key == '2':
                                        temp['index_1'] = 0
                                        total_no_paid_acc_end_day += temp['index_1']

                                    # if key == '3': 
                                    #     temp['index_1'] = 0
                                    #     total_no_paid_acc_accumulated += temp['index_1']

                                    if key == '4':
                                        temp['index_1'] = 0
                                        total_collected_acc += temp['index_1']

                                    if key == '5':
                                        temp['index_1'] = dueDateValue['current_balance_total'] if dueDateValue['current_balance_total'] is not None else 0
                                        total_overdue_outstanding_bal += temp['index_1']

                                    if key == '6':
                                        temp['index_1'] = 0
                                        total_collected_amt_and_day += temp['index_1']
                                    
                                    if key == '7':
                                        temp['index_1'] = 0
                                        total_collected_amt_accumulated += temp['index_1']

                                    if key == '8':
                                        temp['index_1'] = 0
                                        total_collected_ratio_amt += temp['index_1']
                                    # pprint(temp)
                                    mongodb.insert(MONGO_COLLECTION=collection, insert_data=temp)
                                else:
                                    temp = {
                                        'group'         : debtGroupCell[0:1] + ' GROUP',
                                        'month'         : today.strftime("%b-%y"),
                                        'due'           : due[debtGroupCell[1:3]],
                                        'product'       : groupProductCell['value'],
                                        'due_date'      : todayTimeStamp - 86400,
                                        'day'           : dpWorkingdaysdaycol[key],
                                        'day_code'      : key,
                                        'team_name'     : 'Total',
                                        'team_id'       : '',
                                        'created_at'    : time.time(),
                                        'created_by'    : 'system',
                                        # 'updated_by'    : 'system',
                                        # 'updated_at'     : yesterday_starttime,
                                        # 'updated_at'    : time.time()
                                    }

                                    if key == '1':
                                        temp['index_1'] = total_no_overdue
                                        temp['start_acc'] = total_start_acc
                                        temp['start_amt'] = total_start_amt
                                        temp['target_acc'] = total_target_acc
                                        temp['target_amt'] = total_target_amt
                                    
                                    if key == '2':
                                        temp['index_1'] = total_no_paid_acc_end_day

                                    if key == '3':
                                        temp['index_1'] = total_no_paid_acc_accumulated

                                    if key == '4':
                                        temp['index_1'] = total_collected_acc

                                    if key == '5':
                                        temp['index_1'] = total_overdue_outstanding_bal

                                    if key == '6':
                                        temp['index_1'] = total_collected_amt_and_day
                                    
                                    if key == '7':
                                        temp['index_1'] = total_collected_amt_accumulated

                                    if key == '8':
                                        temp['index_1'] = total_collected_ratio_amt
                                    mongodb.insert(MONGO_COLLECTION=collection, insert_data=temp)
            else:
                pprint("SAI")
                if todayTimeStamp > dueDayOfMonth['due_date']:
                    dueDateReport = dueDayOfMonth['due_date']
                else:
                    dueDateReport = dueDayLastMonth['due_date_add_1']
                for groupProductCell in listGroupProduct:
                    for key in dpWorkingdaysdaycol:
                        groupInfoByDueDate = list(mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Group_product'), WHERE={'debt_groups': debtGroupCell, 'name': {"$regex": groupProductCell['text'] + '.*'}}))
                        # pprint(groupInfoByDueDate)
                        if len(groupInfoByDueDate) > 0:
                            groupInfoByDueDate.extend([{'name': 'Total'}])
                            total_no_overdue = 0
                            total_no_paid_acc_end_day = 0
                            total_no_paid_acc_accumulated = 0
                            total_collected_acc = 0
                            total_overdue_outstanding_bal = 0
                            total_collected_amt_and_day = 0
                            total_collected_amt_accumulated = 0
                            total_collected_ratio_amt = 0
                            for groupCell in groupInfoByDueDate:
                                # if debtGroupCell == 'B02':
                                #     pprint(groupCell)
                                if 'G2' in groupCell['name'] or 'G3' in groupCell['name']:
                                    continue
                                groupCell['name'] = groupCell['name'].replace('/G1', '')
                                if groupCell['name'] != 'Total':
                                    yesterdayInfoByKey = mongodb.getOne(MONGO_COLLECTION=collection, WHERE={'due_date': dueDateReport, 'team_id': str(groupCell['_id'])})
                                    # pprint(yesterdayInfo)
                                    debtList = []
                                    cur_bal = 0
                                    count_acc = 0

                                    if groupProductCell['value'] == 'SIBS':
                                        lead = ['JIVF00' + groupCell['lead']] if 'lead' in groupCell.keys() else []
                                        member = ('JIVF00' + s for s in groupCell['members']) if 'members' in groupCell.keys() else []
                                        officerIdRaw = list(lead) + list(member)
                                        officerId = list(dict.fromkeys(officerIdRaw))

                                        lnjc05Info = list(mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'LNJC05'), WHERE={'group_id': debtGroupCell, 'officer_id': {'$in': officerId}}))
                                        count_acc = len(lnjc05Info)
                                        cur_bal = sum(lnjc05['current_balance'] for lnjc05 in lnjc05Info)
                                            
                                    if groupProductCell['value'] == 'Card':
                                        diallist_Info = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Diallist'), WHERE={'group_id': str(groupCell['_id']), 'createdAt': {'$gte': starttime, '$lte': endtime}})
                                        if diallist_Info is not None:
                                            diallist_Detail = list(mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Diallist_detail'), WHERE={'diallist_id': diallist_Info['_id']}))
                                            if diallist_Detail is not None:
                                                count_acc = len(diallist_Info)
                                                cur_bal = sum(detail['cur_bal'] for detail in diallist_Detail)

                                    no_overdue = count_acc
                                    no_overdue_amt = cur_bal
                                    # paid_acc_end_date_yesterday = yesterdayInfo['index_' + str(todayIndex - 1)] - no_overdue if yesterdayInfo is not None and 'index_' + str(todayIndex - 1) in yesterdayInfo.keys() else 0
                                    temp = {}
                                    if key == '1': 
                                        temp['index_' + str(todayIndex)] = no_overdue
                                        total_no_overdue += no_overdue

                                    if key == '2':
                                        yesterdayInfoByKey = mongodb.getOne(MONGO_COLLECTION=collection, WHERE={'due_date': dueDateReport, 'team_id': str(groupCell['_id']), 'day_code': '1'})
                                        temp['index_' + str(todayIndex - 1)] = yesterdayInfoByKey['index_' + str(todayIndex - 1)] - no_overdue if yesterdayInfoByKey is not None and 'index_' + str(todayIndex - 1) in yesterdayInfoByKey.keys() else 0
                                        temp['index_' + str(todayIndex)] = 0
                                        total_no_paid_acc_end_day += temp['index_' + str(todayIndex - 1)]

                                    if key == '3':
                                        yesterdayInfoByKey = mongodb.getOne(MONGO_COLLECTION=collection, WHERE={'due_date': dueDateReport, 'team_id': str(groupCell['_id']), 'day_code': key})
                                        temp['index_' + str(todayIndex - 1)] = yesterdayInfoByKey['start_acc'] - no_overdue if yesterdayInfoByKey is not None and 'start_acc' in yesterdayInfoByKey.keys() else 0
                                        temp['index_' + str(todayIndex)] = 0
                                        total_no_paid_acc_accumulated += temp['index_' + str(todayIndex - 1)]
                                        # if (todayIndex) == 3:
                                        #     temp['index_1'] = 

                                    if key == '4':
                                        yesterdayInfoByKey = mongodb.getOne(MONGO_COLLECTION=collection, WHERE={'due_date': dueDateReport, 'team_id': str(groupCell['_id']), 'day_code': key})
                                        temp['index_' + str(todayIndex - 1)] = round(((yesterdayInfoByKey['start_acc'] - no_overdue) / yesterdayInfoByKey['start_acc']), 4) if yesterdayInfoByKey is not None and 'start_acc' in yesterdayInfoByKey.keys() and 'index_' + str(todayIndex - 1) in yesterdayInfoByKey.keys() and yesterdayInfoByKey['start_acc'] != 0 else 0
                                        total_collected_acc += temp['index_' + str(todayIndex - 1)]

                                    if key == '5':
                                        temp['index_' + str(todayIndex)] = no_overdue_amt
                                        total_overdue_outstanding_bal += no_overdue_amt

                                    if key == '6':
                                        yesterdayInfoByKey = mongodb.getOne(MONGO_COLLECTION=collection, WHERE={'due_date': dueDateReport, 'team_id': str(groupCell['_id']), 'day_code': '5'})
                                        temp['index_' + str(todayIndex - 1)] = yesterdayInfoByKey['index_' + str(todayIndex - 1)] - no_overdue_amt if yesterdayInfoByKey is not None and 'index_' + str(todayIndex - 1) in yesterdayInfoByKey.keys() else 0
                                        total_collected_amt_and_day += temp['index_' + str(todayIndex - 1)]

                                    if key == '7':
                                        yesterdayInfoByKey = mongodb.getOne(MONGO_COLLECTION=collection, WHERE={'due_date': dueDateReport, 'team_id': str(groupCell['_id']), 'day_code': key})
                                        temp['index_' + str(todayIndex - 1)] = yesterdayInfoByKey['start_amt'] - no_overdue_amt if yesterdayInfoByKey is not None and 'start_amt' in yesterdayInfoByKey.keys() else 0
                                        total_collected_amt_accumulated += temp['index_' + str(todayIndex - 1)]

                                    if key == '8':
                                        yesterdayInfoByKey = mongodb.getOne(MONGO_COLLECTION=collection, WHERE={'due_date': dueDateReport, 'team_id': str(groupCell['_id']), 'day_code': key})
                                        temp['index_' + str(todayIndex - 1)] = round(((yesterdayInfoByKey['start_amt'] - no_overdue_amt) / yesterdayInfoByKey['start_amt']), 4) if yesterdayInfoByKey is not None and 'start_amt' in yesterdayInfoByKey.keys() and 'index_' + str(todayIndex - 1) in yesterdayInfoByKey.keys() and yesterdayInfoByKey['start_amt'] != 0 else 0
                                        total_collected_ratio_amt += temp['index_' + str(todayIndex - 1)]
                                    
                                    if yesterdayInfoByKey is not None:
                                        # pprint('yesterdayInfo')
                                        mongodb.update(MONGO_COLLECTION=collection, WHERE={'team_id': str(groupCell['_id']), 'day_code': key, 'due_date': dueDateReport}, VALUE=temp)
                                        if key == '2':
                                            pprint(yesterdayInfoByKey)
                                    else:
                                        temp['group']       = debtGroupCell[0:1] + ' GROUP'
                                        temp['month']       = today.strftime("%b-%y")
                                        temp['due']         = due[debtGroupCell[1:3]]
                                        temp['product']     = groupProductCell['value']
                                        temp['due_date']    = dueDateReport
                                        temp['day']         = dpWorkingdaysdaycol[key]
                                        temp['day_code']    = key
                                        temp['team_name']   = groupCell['name']
                                        temp['team_id']     = str(groupCell['_id'])
                                        temp['created_at']  = time.time()
                                        temp['created_by']  = 'system'
                                        temp['updated_by']  = 'system'
                                        temp['updated_at']  = time.time()
                                        # pprint(temp)
                                        mongodb.insert(MONGO_COLLECTION=collection, insert_data=temp)
                                else:
                                    temp = {
                                        'group'         : debtGroupCell[0:1] + ' GROUP',
                                        'month'         : today.strftime("%b-%y"),
                                        'due'           : due[debtGroupCell[1:3]],
                                        'product'       : groupProductCell['value'],
                                        'due_date'      : dueDateReport,
                                        'day'           : dpWorkingdaysdaycol[key],
                                        'day_code'      : key,
                                        'team_name'     : 'Total',
                                        'team_id'       : '',
                                        'created_at'    : time.time(),
                                        'created_by'    : 'system',
                                        'updated_by'    : 'system',
                                        'updated_at'    : time.time()
                                    }

                                    if key == '1':
                                        temp['index_' + str(todayIndex)] = total_no_overdue
                                        temp['start_acc'] = total_start_acc
                                        temp['start_amt'] = total_start_amt
                                        temp['target_acc'] = total_target_acc
                                        temp['target_amt'] = total_target_amt
                                    
                                    if key == '2':
                                        temp['index_' + str(todayIndex - 1)] = total_no_paid_acc_end_day

                                    if key == '3':
                                        temp['index_' + str(todayIndex - 1)] = total_no_paid_acc_accumulated

                                    if key == '4':
                                        temp['index_' + str(todayIndex - 1)] = total_collected_acc 

                                    if key == '5':
                                        temp['index_' + str(todayIndex)] = total_overdue_outstanding_bal

                                    if key == '6':
                                        temp['index_' + str(todayIndex - 1)] = total_collected_amt_and_day
                                    
                                    if key == '7':
                                        temp['index_' + str(todayIndex - 1)] = total_collected_amt_accumulated

                                    if key == '8':
                                        temp['index_' + str(todayIndex - 1)] = total_collected_ratio_amt
                                    # if '_id' not in groupCell.keys():
                                    #     pprint(groupCell)
                                    yesterdayInfoByKey = mongodb.getOne(MONGO_COLLECTION=collection, WHERE={'team_name': 'Total', 'day_code': key, 'group': temp['group'], 'month': temp['month'], 'due': temp['due'], 'product': temp['product'], 'due_date': dueDateReport})
                                    if yesterdayInfoByKey is not None:
                                        mongodb.update(MONGO_COLLECTION=collection, WHERE={'team_name': 'Total', 'day_code': key, 'group': temp['group'], 'month': temp['month'], 'due': temp['due'], 'product': temp['product'], 'due_date': dueDateReport}, VALUE=temp)
                                    else:
                                        mongodb.insert(MONGO_COLLECTION=collection, insert_data=temp)
    print('DONE')
except Exception as e:
    # log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
    # pprint(str(e))
    print(traceback.format_exc())
        
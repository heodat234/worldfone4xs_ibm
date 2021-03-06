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

common      = Common()
base_url    = common.base_url()
wff_env     = common.wff_env(base_url)
mongodb     = Mongodb(MONGODB="worldfone4xs", WFF_ENV=wff_env)
_mongodb    = Mongodb(MONGODB="_worldfone4xs", WFF_ENV=wff_env)

subUserType = 'LO'
collection = common.getSubUser(subUserType, 'Daily_prod_each_user_group')
lnjc05_collection       = common.getSubUser(subUserType, 'LNJC05')
ln3206f_collection      = common.getSubUser(subUserType, 'LN3206F')
account_collection      = common.getSubUser(subUserType, 'List_of_account_in_collection')
gl_collection           = common.getSubUser(subUserType, 'Report_input_payment_of_card')
jsonData_collection     = common.getSubUser(subUserType, 'Jsondata')
group_collection        = common.getSubUser(subUserType, 'Group')
target_collection       = common.getSubUser(subUserType, 'Target_of_report')
report_due_date_collection              = common.getSubUser(subUserType, 'Report_due_date')
due_date_next_date_group_collection     = common.getSubUser(subUserType, 'Due_date_next_date_by_group')
diallist_detail_collection     = common.getSubUser(subUserType, 'Diallist_detail')

log = open(base_url + "cronjob/python/Loan/log/dailyProdEachUserGroup_log.txt","a")
now = datetime.now()
log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': Start Import' + '\n')
try:
    insertData = []
    updateData = []
    listDebtGroup = []

    today = date.today()
    # today = datetime.strptime('03/01/2019', "%d/%m/%Y").date()

    day = today.day
    month = today.month
    year = today.year
    weekday = today.weekday()
    lastDayOfMonth = calendar.monthrange(year, month)[1]

    todayString = today.strftime("%d/%m/%Y")
    todayTimeStamp = int(time.mktime(time.strptime(str(todayString + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
    endTodayTimeStamp = int(time.mktime(time.strptime(str(todayString + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))

    startMonth = int(time.mktime(time.strptime(str('01/' + str(month) + '/' + str(year) + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
    endMonth = int(time.mktime(time.strptime(str(str(lastDayOfMonth) + '/' + str(month) + '/' + str(year) + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))

    holidayOfMonth = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_off_sys'))
    listHoliday = map(lambda offDateRow: {offDateRow['off_date']}, holidayOfMonth)

    if todayTimeStamp in listHoliday:
        sys.exit()

    mongodb.remove_document(MONGO_COLLECTION=collection, WHERE={'createdAt': {'$gte': todayTimeStamp, '$lte': endTodayTimeStamp} })

    debtGroup = _mongodb.getOne(MONGO_COLLECTION=jsonData_collection, WHERE={'tags': ['debt', 'group']})
    dueDate = _mongodb.getOne(MONGO_COLLECTION=jsonData_collection, WHERE={'tags': ['debt', 'duedate']})

    for group in debtGroup['data']:
        for duedate in dueDate['data']:
            listDebtGroup.append(group['text'] + duedate['text'])

    listDebtGroup = sorted(listDebtGroup)

    listGroupProductRaw = _mongodb.getOne(MONGO_COLLECTION=jsonData_collection, WHERE={'tags': ['group', 'debt', 'product']})
    listGroupProduct = listGroupProductRaw['data']

    for debtGroupCell in list(listDebtGroup):
        if debtGroupCell[0:1] == 'A':
            dueDayOfMonth = mongodb.getOne(MONGO_COLLECTION=report_due_date_collection, WHERE={'for_month': str(month), 'debt_group': debtGroupCell[1:3]})
            for groupProduct in list(listGroupProduct):
                groupInfoByDueDate = mongodb.get(MONGO_COLLECTION=group_collection, WHERE={'name': {"$regex": groupProduct['text'] + '/Group ' + debtGroupCell[0:1]} })
                for groupCell in list(groupInfoByDueDate):
                    if 'G2' in groupCell['name'] or 'G3' in groupCell['name']:
                        continue

                    members = []
                    name = groupCell['name']
                    name = name.replace('G1','')
                    groupInfoByName = mongodb.get(MONGO_COLLECTION=group_collection, WHERE={'name': {"$regex": name} })
                    for groupInfo in list(groupInfoByName):
                        if 'members' in groupInfo.keys():
                            members += groupInfo['members']


                    temp = {
                        'col'           : 0,
                        'col_amt'       : 0,
                        'payment_amt'   : 0,
                        'rem'           : 0,
                        'rem_actual'       : 0,
                        'rem_os'        : 0,
                        'flow_rate'     : 0,
                        'flow_rate_actual' : 0,
                        'flow_rate_os' : 0,
                        'col_ratio'     : 0,
                        'col_ratio_actual' : 0,
                        'col_ratio_os' : 0,
                    }
                    col_today = 0
                    cur_bal_today = 0
                    if todayTimeStamp <= dueDayOfMonth['due_date_add_1']:
                        if month == 1:
                            lastMonth = 12
                        else:
                            lastMonth = month - 1
                        dueDayLastMonth = mongodb.getOne(MONGO_COLLECTION=report_due_date_collection, WHERE={'for_month': str(lastMonth), 'debt_group': debtGroupCell[1:3]})
                        temp['due_date'] = dueDayLastMonth['due_date'] if dueDayLastMonth is not None else ''
                        #Lay gia tri no vao ngay due date + 1#
                        incidenceInfo = mongodb.getOne(MONGO_COLLECTION=due_date_next_date_group_collection, WHERE={'for_month': str(lastMonth), "due_date_code" : debtGroupCell[1:3], 'team_id': str(groupCell['_id'])})
                    else:
                        incidenceInfo = mongodb.getOne(MONGO_COLLECTION=due_date_next_date_group_collection, WHERE={'for_month': str(month), "due_date_code" : debtGroupCell[1:3],'team_id': str(groupCell['_id'])})
                        temp['due_date'] = dueDayOfMonth['due_date']

                    pprint(name)
                    temp['debt_group']      = debtGroupCell[0:1]
                    temp['due_date_code']   = debtGroupCell[1:3]
                    temp['product']         = groupProduct['text']
                    temp['team']            = name
                    temp['team_id']         = str(groupCell['_id'])

                    if incidenceInfo is not None:
                        temp['inci']        = incidenceInfo['debt_acc_no'] if 'debt_acc_no' in incidenceInfo.keys() else 0
                        temp['inci_amt']    = round(incidenceInfo['current_balance_total']/1000) if 'current_balance_total' in incidenceInfo.keys() else 0
                        acc_arr             = incidenceInfo['acc_arr'] if 'acc_arr' in incidenceInfo.keys() else []
                    else:
                        temp['inci'] = 0
                        temp['inci_amt'] = 0
                        acc_arr = []


                    


                    due_date_add_2 = temp['due_date'] + 86400*2
                    if groupProduct['value'] == 'SIBS':
                        # lead = ['JIVF00' + groupCell['lead']] if 'lead' in groupCell.keys() else []
                        # member = ('JIVF00' + s for s in members)
                        # officerIdRaw = list(lead) + list(member)
                        # officerId = list(dict.fromkeys(officerIdRaw))
                        aggregate_lnjc05 = [
                            {
                                "$match":
                                {
                                      "created_at": {'$gte': todayTimeStamp, '$lte': endTodayTimeStamp},
                                      "account_number": {'$in': acc_arr},
                                      'group_id': debtGroupCell
                                }
                            },{
                              "$group":
                              {
                                  "_id": 'null',
                                  "count_col": {'$sum': 1},
                                  "cur_bal_total": {'$sum': '$current_balance'}
                              }
                            }
                        ]
                        lnjc05Data = mongodb.aggregate_pipeline(MONGO_COLLECTION=lnjc05_collection,aggregate_pipeline=aggregate_lnjc05)
                        if lnjc05Data != None:
                            for row in lnjc05Data:
                                col_today               = row['count_col']
                                cur_bal_today           = round(row['cur_bal_total']/1000)


                        aggregate_ln3206 = [
                            {
                                "$match":
                                {
                                      "created_at": {'$gte': due_date_add_2, '$lte': endTodayTimeStamp},
                                      "account_number": {'$in': acc_arr},
                                      "code" : '10',
                                      "coNoHayKhong": 'Y'
                                }
                            },{
                              "$group":
                              {
                                  "_id": 'null',
                                  "sum_amount": {'$sum': '$amt'},
                                  "count_col": {'$addToSet': '$account_number'},
                              }
                            }
                        ]
                        ln3206fData = mongodb.aggregate_pipeline(MONGO_COLLECTION=ln3206f_collection,aggregate_pipeline=aggregate_ln3206)
                        if ln3206fData != None:
                            for row in ln3206fData:
                                # temp['col']            = len(row['count_col'] )
                                if debtGroupCell[0:1] == 'A' or debtGroupCell[0:1] == 'B' or debtGroupCell[0:1] == 'C':
                                    temp['payment_amt']         = row['sum_amount']
                                else:
                                    temp['col_amt']             = round(row['sum_amount']/1000)
                            
                        if debtGroupCell[0:1] == 'A' or debtGroupCell[0:1] == 'B' or debtGroupCell[0:1] == 'C':
                            temp['col_amt']             = temp['inci_amt'] - cur_bal_today

                        
                    if groupProduct['value'] == 'Card':
                        member = ( s for s in members)
                        assign = list(dict.fromkeys(list(member)))
                        aggregate_diallist = [
                            {
                                "$match":
                                {
                                    "createdAt": {'$gte': todayTimeStamp, '$lte' : endTodayTimeStamp},
                                    "assign": {'$in' : assign},
                                    "group_id": debtGroupCell[0:1]+'-'+debtGroupCell[1:3]
                                }
                            },{
                                "$group":
                                {
                                      "_id": 'null',
                                      "count_col": {'$sum': 1},
                                      "cur_bal_total": {'$sum': '$cur_bal'}
                                }
                            }
                        ]
                        diallistData = mongodb.aggregate_pipeline(MONGO_COLLECTION=diallist_detail_collection,aggregate_pipeline=aggregate_diallist)
                        if diallistData != None:
                            for row in diallistData:
                                col_today               = row['count_col']
                                cur_bal_today           = round(row['cur_bal_total']/1000)
                                

                        code = ['2000','2100','2700']
                        for row_acc in acc_arr:
                            aggregate_gl = [
                                {
                                    "$match":
                                    {
                                        "created_at": {'$gte': due_date_add_2, '$lte': endTodayTimeStamp},
                                        "account_number": row_acc,
                                        "code" : {'$in' : code},
                                        "coNoHayKhong": 'Y'
                                    }
                                },
                                {
                                    "$project":
                                    {
                                        "account_number" : 1,
                                        "amount" : 1,
                                        "code" : 1,
                                    }
                                }
                            ]
                            glData = mongodb.aggregate_pipeline(MONGO_COLLECTION=gl_collection,aggregate_pipeline=aggregate_gl)
                            code_2000 = 0
                            code_2700 = 0
                            sum_code = 0
                            if glData != None:
                                for row in glData:
                                    if row['code'] == '2000' or row['code'] == '2100':
                                        code_2000 += row['amount']
                                    else:
                                        code_2700 += row['amount']
                                sum_code = code_2000 - code_2700
                                if sum_code > 0:
                                    temp['payment_amt']         += sum_code


                        if debtGroupCell[0:1] == 'A' or debtGroupCell[0:1] == 'B' or debtGroupCell[0:1] == 'C':
                            aggregate_account = [
                                {
                                    "$match":
                                    {
                                          "account_number": {'$in': acc_arr},
                                    }
                                },{
                                  "$group":
                                  {
                                      "_id": 'null',
                                      "sum_amount": {'$sum': '$cur_bal'},
                                  }
                                }
                            ]
                            listAccData  = mongodb.aggregate_pipeline(MONGO_COLLECTION=account_collection,aggregate_pipeline=aggregate_account)
                            col_amount_today   = 0
                            if listAccData != None:
                                for row in listAccData:
                                    col_amount_today           = round(row['sum_amount']/1000)

                            temp['col_amt'] = temp['inci_amt'] - col_amount_today



                    temp['payment_amt']         = round(temp['payment_amt']/1000)
                    temp['col']                 = temp['inci'] - col_today
                    temp['rem']                 = temp['inci'] - temp['col']
                    temp['rem_actual']          = temp['inci_amt'] - temp['payment_amt']
                    temp['rem_os']              = cur_bal_today
                    temp['flow_rate']           = temp['rem'] / temp['inci'] if temp['inci'] != 0 else 0
                    temp['flow_rate_actual']    = temp['rem_actual'] / temp['inci_amt'] if temp['inci_amt'] != 0 else 0
                    temp['flow_rate_os']        = temp['rem_os'] / temp['inci_amt'] if temp['inci_amt'] != 0 else 0
                    temp['col_ratio']           = temp['col'] / temp['inci'] if temp['inci'] != 0 else 0
                    temp['col_ratio_actual']    = temp['payment_amt'] / temp['inci_amt'] if temp['inci_amt'] != 0 else 0
                    temp['col_ratio_os']        = temp['col_amt'] / temp['inci_amt'] if temp['inci_amt'] != 0 else 0

                    
                    if groupProduct['text'] == 'Card':
                        debt_type = 'CARD'
                    else:
                        debt_type = 'SIBS'
                    if debtGroupCell[0:1] == 'A':
                        targetInfo = mongodb.getOne(MONGO_COLLECTION=target_collection, WHERE={ 'duedate_type': str(debtGroupCell), 'debt_type': debt_type })
                    else:
                        targetInfo = mongodb.getOne(MONGO_COLLECTION=target_collection, WHERE={ 'B_plus_duedate_type': debtGroupCell, 'debt_type': debt_type })

                    target = targetInfo['target']
                    temp['tar_amt'] = (target * temp['inci_amt'])/100
                    
                    temp['tar_per'] = target/100
                    if debtGroupCell[0:1] == 'A' or debtGroupCell[0:1] == 'B' or debtGroupCell[0:1] == 'C':
                        temp['tar_gap'] = temp['tar_amt'] - temp['payment_amt']
                    else:
                        temp['tar_gap'] = temp['tar_amt'] - temp['col_amt']

                    temp['createdAt'] = todayTimeStamp
                    temp['createdBy'] = 'system'
                    temp['for_month'] = str(month)
                    mongodb.insert(MONGO_COLLECTION=collection, insert_data=temp)



        if debtGroupCell[0:1] is not 'F' and debtGroupCell[0:1] is not 'A':
            dueDayOfMonth = mongodb.getOne(MONGO_COLLECTION=report_due_date_collection, WHERE={'for_month': str(month), 'debt_group': debtGroupCell[1:3]})
            for groupProduct in list(listGroupProduct):
                groupInfoByDueDate = mongodb.get(MONGO_COLLECTION=group_collection, WHERE={'name': {"$regex": groupProduct['text'] + '/Group ' + debtGroupCell[0:1] + '/' + debtGroupCell} })
                for groupCell in list(groupInfoByDueDate):
                    members = []
                    if 'members' in groupCell.keys():
                        members = groupCell['members']
                    name = groupCell['name']

                    temp = {
                        'col'           : 0,
                        'col_amt'       : 0,
                        'payment_amt'   : 0,
                        'rem'           : 0,
                        'rem_actual'       : 0,
                        'rem_os'        : 0,
                        'flow_rate'     : 0,
                        'flow_rate_actual' : 0,
                        'flow_rate_os' : 0,
                        'col_ratio'     : 0,
                        'col_ratio_actual' : 0,
                        'col_ratio_os' : 0,
                    }
                    col_today = 0
                    cur_bal_today = 0
                    if todayTimeStamp <= dueDayOfMonth['due_date_add_1']:
                        if month == 1:
                            lastMonth = 12
                        else:
                            lastMonth = month - 1
                        dueDayLastMonth = mongodb.getOne(MONGO_COLLECTION=report_due_date_collection, WHERE={'for_month': str(lastMonth), 'debt_group': debtGroupCell[1:3]})
                        temp['due_date'] = dueDayLastMonth['due_date'] if dueDayLastMonth is not None else ''
                        #Lay gia tri no vao ngay due date + 1#
                        incidenceInfo = mongodb.getOne(MONGO_COLLECTION=due_date_next_date_group_collection, WHERE={'for_month': str(lastMonth), "due_date_code" : debtGroupCell[1:3], 'team_id': str(groupCell['_id'])})
                    else:
                        incidenceInfo = mongodb.getOne(MONGO_COLLECTION=due_date_next_date_group_collection, WHERE={'for_month': str(month), "due_date_code" : debtGroupCell[1:3], 'team_id': str(groupCell['_id'])})
                        temp['due_date'] = dueDayOfMonth['due_date']

                    pprint(name)
                    temp['debt_group']      = debtGroupCell[0:1]
                    temp['due_date_code']   = debtGroupCell[1:3]
                    temp['product']         = groupProduct['text']
                    temp['team']            = name
                    temp['team_id']         = str(groupCell['_id'])

                    if incidenceInfo is not None:
                        temp['inci']        = incidenceInfo['debt_acc_no'] if 'debt_acc_no' in incidenceInfo.keys() else 0
                        temp['inci_amt']    = round(incidenceInfo['current_balance_total']/1000) if 'current_balance_total' in incidenceInfo.keys() else 0
                        acc_arr             = incidenceInfo['acc_arr'] if 'acc_arr' in incidenceInfo.keys() else []
                    else:
                        temp['inci'] = 0
                        temp['inci_amt'] = 0
                        acc_arr = []


                    


                    due_date_add_2 = temp['due_date'] + 86400*2
                    if groupProduct['value'] == 'SIBS':
                        # lead = ['JIVF00' + groupCell['lead']] if 'lead' in groupCell.keys() else []
                        # member = ('JIVF00' + s for s in members)
                        # officerIdRaw = list(lead) + list(member)
                        # officerId = list(dict.fromkeys(officerIdRaw))
                        aggregate_lnjc05 = [
                            {
                                "$match":
                                {
                                      "created_at": {'$gte': todayTimeStamp, '$lte': endTodayTimeStamp},
                                      "account_number": {'$in': acc_arr},
                                      'group_id': debtGroupCell
                                }
                            },{
                              "$group":
                              {
                                  "_id": 'null',
                                  "count_col": {'$sum': 1},
                                  "cur_bal_total": {'$sum': '$current_balance'}
                              }
                            }
                        ]
                        lnjc05Data = mongodb.aggregate_pipeline(MONGO_COLLECTION=lnjc05_collection,aggregate_pipeline=aggregate_lnjc05)
                        if lnjc05Data != None:
                            for row in lnjc05Data:
                                col_today               = row['count_col']
                                cur_bal_today           = round(row['cur_bal_total']/1000)



                        aggregate_ln3206 = [
                            {
                                "$match":
                                {
                                      "created_at": {'$gte': due_date_add_2, '$lte': endTodayTimeStamp},
                                      "account_number": {'$in': acc_arr},
                                      "code" : '10',
                                      "coNoHayKhong": 'Y'
                                }
                            },{
                              "$group":
                              {
                                  "_id": 'null',
                                  "sum_amount": {'$sum': '$amt'},
                                  "count_col": {'$addToSet': '$account_number'},
                              }
                            }
                        ]
                        ln3206fData = mongodb.aggregate_pipeline(MONGO_COLLECTION=ln3206f_collection,aggregate_pipeline=aggregate_ln3206)
                        if ln3206fData != None:
                            for row in ln3206fData:
                                # temp['col']            = len(row['count_col'] )
                                if debtGroupCell[0:1] == 'A' or debtGroupCell[0:1] == 'B' or debtGroupCell[0:1] == 'C':
                                    temp['payment_amt']         = row['sum_amount']
                                else:
                                    temp['col_amt']             = round(row['sum_amount']/1000)

                        if debtGroupCell[0:1] == 'A' or debtGroupCell[0:1] == 'B' or debtGroupCell[0:1] == 'C':
                            temp['col_amt']             = temp['inci_amt'] - cur_bal_today


                        
                    if groupProduct['value'] == 'Card':
                        member = ( s for s in members)
                        assign = list(dict.fromkeys(list(member)))
                        aggregate_diallist = [
                            {
                                "$match":
                                {
                                    "createdAt": {'$gte': todayTimeStamp, '$lte' : endTodayTimeStamp},
                                    "assign": {'$in' : assign},
                                    "group_id": debtGroupCell[0:1]+'-'+debtGroupCell[1:3]
                                }
                            },{
                                "$group":
                                {
                                      "_id": 'null',
                                      "count_col": {'$sum': 1},
                                      "cur_bal_total": {'$sum': '$cur_bal'}
                                }
                            }
                        ]
                        diallistData = mongodb.aggregate_pipeline(MONGO_COLLECTION=diallist_detail_collection,aggregate_pipeline=aggregate_diallist)
                        if diallistData != None:
                            for row in diallistData:
                                col_today               = row['count_col']
                                cur_bal_today           = round(row['cur_bal_total']/1000)
                                

                        code = ['2000','2100','2700']
                        for row_acc in acc_arr:
                            aggregate_gl = [
                                {
                                    "$match":
                                    {
                                        "created_at": {'$gte': due_date_add_2, '$lte': endTodayTimeStamp},
                                        "account_number": row_acc,
                                        "code" : {'$in' : code},
                                        "coNoHayKhong": 'Y'
                                    }
                                },
                                {
                                    "$project":
                                    {
                                        "account_number" : 1,
                                        "amount" : 1,
                                        "code" : 1,
                                    }
                                }
                            ]
                            glData = mongodb.aggregate_pipeline(MONGO_COLLECTION=gl_collection,aggregate_pipeline=aggregate_gl)
                            code_2000 = 0
                            code_2700 = 0
                            sum_code = 0
                            if glData != None:
                                for row in glData:
                                    if row['code'] == '2000' or row['code'] == '2100':
                                        code_2000 += row['amount']
                                    else:
                                        code_2700 += row['amount']
                                sum_code = code_2000 - code_2700
                                if sum_code > 0:
                                    if debtGroupCell[0:1] == 'B' or debtGroupCell[0:1] == 'C':
                                        temp['payment_amt']         += sum_code
                                    else:
                                        temp['col_amt']             += sum_code

                        temp['col_amt'] = round(temp['col_amt']/1000)

                        if debtGroupCell[0:1] == 'A' or debtGroupCell[0:1] == 'B' or debtGroupCell[0:1] == 'C':
                            aggregate_account = [
                                {
                                    "$match":
                                    {
                                          "account_number": {'$in': acc_arr},
                                    }
                                },{
                                  "$group":
                                  {
                                      "_id": 'null',
                                      "sum_amount": {'$sum': '$cur_bal'},
                                  }
                                }
                            ]
                            listAccData  = mongodb.aggregate_pipeline(MONGO_COLLECTION=account_collection,aggregate_pipeline=aggregate_account)
                            col_amount_today   = 0
                            if listAccData != None:
                                for row in listAccData:
                                    col_amount_today           = round(row['sum_amount']/1000)

                            temp['col_amt'] = temp['inci_amt'] - col_amount_today



                    temp['payment_amt']         = round(temp['payment_amt']/1000)
                    temp['col']                 = temp['inci'] - col_today
                    temp['rem']                 = temp['inci'] - temp['col']
                    temp['rem_actual']          = temp['inci_amt'] - temp['payment_amt']
                    temp['rem_os']              = cur_bal_today
                    temp['flow_rate']           = temp['rem'] / temp['inci'] if temp['inci'] != 0 else 0
                    temp['flow_rate_actual']    = temp['rem_actual'] / temp['inci_amt'] if temp['inci_amt'] != 0 else 0
                    temp['flow_rate_os']        = temp['rem_os'] / temp['inci_amt'] if temp['inci_amt'] != 0 else 0
                    temp['col_ratio']           = temp['col'] / temp['inci'] if temp['inci'] != 0 else 0
                    temp['col_ratio_actual']    = temp['payment_amt'] / temp['inci_amt'] if temp['inci_amt'] != 0 else 0
                    temp['col_ratio_os']        = temp['col_amt'] / temp['inci_amt'] if temp['inci_amt'] != 0 else 0

                    
                    if groupProduct['text'] == 'Card':
                        debt_type = 'CARD'
                    else:
                        debt_type = 'SIBS'
                    if debtGroupCell[0:1] == 'A':
                        targetInfo = mongodb.getOne(MONGO_COLLECTION=target_collection, WHERE={ 'duedate_type': str(debtGroupCell), 'debt_type': debt_type })
                    else:
                        targetInfo = mongodb.getOne(MONGO_COLLECTION=target_collection, WHERE={ 'B_plus_duedate_type': debtGroupCell, 'debt_type': debt_type })

                    target = targetInfo['target']
                    temp['tar_amt'] = (target * temp['inci_amt'])/100
                    # temp['tar_gap'] = temp['tar_amt'] - temp['col_amt']
                    temp['tar_per'] = target/100
                    if debtGroupCell[0:1] == 'A' or debtGroupCell[0:1] == 'B' or debtGroupCell[0:1] == 'C':
                        temp['tar_gap'] = temp['tar_amt'] - temp['payment_amt']
                    else:
                        temp['tar_gap'] = temp['tar_amt'] - temp['col_amt']

                    temp['createdAt'] = todayTimeStamp
                    temp['createdBy'] = 'system'
                    temp['for_month'] = str(month)
                    mongodb.insert(MONGO_COLLECTION=collection, insert_data=temp)




    # # wo
    # groupInfo = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Group'), WHERE={'name': {"$regex": 'WO'},'debt_groups' : {'$exists': 'true'}})
    # for groupCell in groupInfo:
    #     temp = {
    #         'col'           : 0,
    #         'col_amt'       : 0,
    #         'payment_amt'   : 0,
    #         'rem'           : 0,
    #         'rem_actual'       : 0,
    #         'rem_os'        : 0,
    #         'flow_rate'     : 0,
    #         'flow_rate_actual' : 0,
    #         'flow_rate_os' : 0,
    #         'col_ratio'     : 0,
    #         'col_ratio_actual' : 0,
    #         'col_ratio_os' : 0,
    #     }
    #     temp['due_date_code']   = '1'
    #     temp['debt_group']      = 'F'
    #     temp['product']         = 'SIBS - CARD'
    #     temp['team']            = groupCell['name']
    #     temp['team_id']         = str(groupCell['_id'])
    #     incidenceInfo = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Due_date_next_date'), WHERE={'for_month': str(month), 'team_id': str(groupCell['_id'])})
    #     temp['due_date'] = incidenceInfo['due_date']

    #     if incidenceInfo is not None:
    #         temp['inci']        = incidenceInfo['debt_acc_no'] if 'debt_acc_no' in incidenceInfo.keys() else 0
    #         temp['inci_amt']    = incidenceInfo['current_balance_total'] if 'current_balance_total' in incidenceInfo.keys() else 0
    #     else:
    #         temp['inci']        = 0
    #         temp['inci_amt']    = 0

    #     aggregate_payment = [
    #         # {
    #         #     "$match":
    #         #     {
    #         #         "createdAt": {'$gte': temp['due_date'],'$lte' : todayTimeStamp},
    #         #     }
    #         # },
    #         {
    #             "$group":
    #             {
    #                 "_id": 'null',
    #                 "acc_arr": {'$addToSet' : '$account_number'},
    #             }
    #         }
    #     ]
    #     woPayment = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'Wo_payment'),aggregate_pipeline=aggregate_payment)
    #     acc_payment = []
    #     for payment in woPayment:
    #         temp['col']         = len(payment['acc_arr'])
    #         acc_payment         = payment['acc_arr']

    #     count_wo = mongodb.count(MONGO_COLLECTION=common.getSubUser(subUserType, 'WO_monthly'))
    #     if count_wo != None or count_wo != 0:
    #         aggregate_payment_prod = [
    #             {
    #                 "$match":
    #                 {
    #                     'ACCTNO': {'$in' : acc_payment },
    #                 }
    #             },{
    #                 '$project':
    #                 {
    #                    'pay_payment': {'$sum' : [ '$WO9711', '$WO9712' ,'$WO9713'] },
    #                 }
    #             },{
    #                 "$group":
    #                 {
    #                     "_id": 'null',
    #                     "total_amt": {'$sum': '$pay_payment'},
    #                     "total_acc": {'$sum': 1},
    #                 }
    #             }
    #         ]
    #         woMonthlyProd = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'WO_monthly'),aggregate_pipeline=aggregate_payment_prod)
    #         if woMonthlyProd != None:
    #             for woRowProd in woMonthlyProd:
    #                 temp['col_amt'] = woRowProd['total_amt']

    #     else:
    #         aggregate_allProd = [
    #             {
    #                 "$match":
    #                 {
    #                     'ACCTNO': {'$in' : acc_payment },
    #                 }
    #             },{
    #                 '$project':
    #                 {
    #                    'pay_payment': {'$sum' : [ '$WOAMT', '$WO_INT' ,'$WO_LC'] },
    #                 }
    #             },{
    #                 "$group":
    #                 {
    #                     "_id": 'null',
    #                     "total_amt": {'$sum': '$pay_payment'},
    #                 }
    #             }
    #         ]
    #         woAllProd = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'Wo_all_prod'),aggregate_pipeline=aggregate_allProd)
    #         if woAllProd != None:
    #             for woRow in woAllProd:
    #                 temp['col_amt'] = woRow['total_amt']

    #     temp['rem']         = temp['inci'] - temp['col']
    #     temp['rem_os']     = temp['inci_amt'] - temp['col_amt']
    #     temp['flow_rate']   = temp['rem'] / temp['inci'] if temp['inci'] != 0 else 0
    #     temp['flow_rate_os']   = temp['rem_os'] / temp['inci_amt'] if temp['inci_amt'] != 0 else 0
    #     temp['col_ratio']       = temp['col'] / temp['inci'] if temp['inci'] != 0 else 0
    #     temp['col_ratio_os']   = temp['col_amt'] / temp['inci_amt'] if temp['inci_amt'] != 0 else 0

    #     targetInfo = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Target'), WHERE={ 'group.id': str(groupCell['_id'])})
    #     target = int(targetInfo['target'])
    #     temp['tar_amt'] = (target * temp['inci_amt'])/100
    #     temp['tar_gap'] = temp['tar_amt'] - temp['rem_os']
    #     temp['tar_per'] = temp['tar_gap']/temp['tar_amt'] if temp['tar_amt'] != 0 else 0

    #     temp['createdAt'] = time.time()
    #     temp['createdBy'] = 'system'
    #     temp['for_month'] = str(month)
    #     mongodb.insert(MONGO_COLLECTION=collection, insert_data=temp)

    now_end         = datetime.now()
    log.write(now_end.strftime("%d/%m/%Y, %H:%M:%S") + ': End Log' + '\n')
    print('DONE')
except Exception as e:
    log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
    pprint(str(e))

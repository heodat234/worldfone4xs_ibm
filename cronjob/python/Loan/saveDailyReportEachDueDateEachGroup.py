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
collection = common.getSubUser(subUserType, 'Daily_prod_each_group')
log = open(base_url + "cronjob/python/Loan/log/dailyEachDueDateEachGroup_log.txt","a")
now = datetime.now()
log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': Start Import' + '\n')
try:
    insertData = []
    insertDataTotal = []
    listDebtGroup = []

    today = date.today()
    # today = datetime.strptime('13/12/2019', "%d/%m/%Y").date()

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

    if todayTimeStamp in listHoliday:
        sys.exit()

    todayString = today.strftime("%d/%m/%Y")
    starttime = int(time.mktime(time.strptime(str(todayString + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
    endtime = int(time.mktime(time.strptime(str(todayString + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))

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

    for debtGroupCell in list(listDebtGroup):
        if debtGroupCell[0:1] is not 'F' :
            # if debtGroupCell[0:1] == 'A' and debtGroupCell[1:3] == '01':
            #     dueDayOfMonth = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_due_date'), WHERE={'for_month': str(month), 'debt_group': debtGroupCell[1:3]})
            #     for groupProduct in list(listGroupProduct):
            #         temp = {
            #             'inci'           : 0,
            #             'inci_amt'       : 0,
            #             'inci_ob_principal'       : 0,
            #             'col'           : 0,
            #             'col_amt'       : 0,
            #             'col_prici'      : 0,
            #             'amt'            : 0,
            #             'rem'            : 0,
            #             'rem_amt'        : 0,
            #             'flow_rate'      : 0,
            #             'flow_rate_amt'  : 0,
            #             'col_rate'       : 0,
            #             'princi_ratio'       : 0,
            #             'actual_ratio'    : 0,
            #             'amt_ratio'       : 0,
            #         }
            #         col_today = 0
            #         amt_today = 0
            #         ob_principal_today = 0
            #         if todayTimeStamp < dueDayOfMonth['due_date_add_1']:
            #             if month == 1:
            #                 lastMonth = 12
            #             else:
            #                 lastMonth = month - 1
            #             dueDayLastMonth = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_due_date'), WHERE={'for_month': str(lastMonth), 'debt_group': debtGroupCell[1:3]})
            #             temp['due_date'] = dueDayLastMonth['due_date'] if dueDayLastMonth is not None else ''
            #             #Lay gia tri no vao ngay due date + 1#
            #             incidenceInfo = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Due_date_next_date'), WHERE={'for_month': str(lastMonth),  'debt_group': debtGroupCell[0:1],'due_date_code': debtGroupCell[1:3],'product':groupProduct['text']})
            #             #Lay gia tri no vao ngay due date + 1#
            #         else:
            #             temp['due_date'] = dueDayOfMonth['due_date']
            #             incidenceInfo = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Due_date_next_date'), WHERE={'for_month': str(month), 'debt_group': debtGroupCell[0:1],'due_date_code': debtGroupCell[1:3],'product':groupProduct['text']})

            #         temp['debt_group'] = debtGroupCell[0:1]
            #         temp['due_date_code'] = int(debtGroupCell[1:3])
            #         temp['product'] = groupProduct['text']
            #         # temp['team_id'] = str(groupCell['_id'])
            #         acc_arr = []
            #         if incidenceInfo is not None:
            #             for inc in incidenceInfo:
            #                 temp['inci'] += inc['debt_acc_no']
            #                 temp['inci_amt'] += inc['current_balance_total']
            #                 temp['inci_ob_principal'] += inc['ob_principal_total']
            #                 acc_arr_1 = inc['acc_arr'] if 'acc_arr' in inc.keys() else []
            #                 acc_arr += acc_arr_1


            #         if groupProduct['value'] == 'SIBS':
            #             # dueDateOneData = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Due_date_next_date_SIBS'), WHERE={'debt_group': debtGroupCell[0:1], 'due_date_code': debtGroupCell[1:3], 'for_month': str(month)})
            #             # acc_arr = []
            #             lnjc05Info = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'LNJC05'), WHERE={'group_id': debtGroupCell  })
            #             for lnjc05 in lnjc05Info:
            #                 col_today += 1
            #                 amt_today += lnjc05['current_balance']
            #                 ob_principal_today += lnjc05['outstanding_principal']
            #                 # acc_arr.append(lnjc05['account_number'])

            #             aggregate_ln3206 = [
            #                 {
            #                     "$match":
            #                     {
            #                         "created_at": {'$gte': temp['due_date'],'$lte': todayTimeStamp},
            #                         "account_number": {'$in' : acc_arr}
            #                     }
            #                 },{
            #                     "$group":
            #                     {
            #                         "_id": 'null',
            #                         "total_amt": {'$sum': '$amt'},
            #                     }
            #                 }
            #             ]
            #             ln3206fInfo = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'LN3206F'),aggregate_pipeline=aggregate_ln3206)
            #             if ln3206fInfo is not None:
            #                 for ln3206 in ln3206fInfo:
            #                     temp['amt'] = ln3206['total_amt']

            #             temp['col']         = temp['inci'] - col_today
            #             temp['col_amt']     = temp['inci_amt'] - amt_today
            #             temp['col_prici']   = temp['inci_ob_principal'] - ob_principal_today
            #             temp['rem']         = temp['inci'] - temp['col']
            #             temp['rem_amt']     = temp['inci_amt'] - temp['col_amt']

            #             temp['flow_rate']       = temp['rem'] / temp['inci'] if temp['inci'] != 0 else 0
            #             temp['flow_rate_amt']   = temp['rem_amt'] / temp['inci_amt'] if temp['inci_amt'] != 0 else 0
            #             temp['col_rate']        = temp['col'] / temp['inci'] if temp['inci'] != 0 else 0

            #             temp['actual_ratio']    = temp['amt'] / temp['inci_amt'] if temp['inci_amt'] != 0 else 0
            #             temp['princi_ratio']    = temp['col_prici'] / temp['inci_ob_principal'] if temp['inci_ob_principal'] != 0 else 0
            #             temp['amt_ratio']       = temp['col_amt'] / temp['inci_amt'] if temp['inci_amt'] != 0 else 0

            #         if groupProduct['value'] == 'Card':
            #             aggregate_group = [
            #                 {
            #                     "$match":
            #                     {
            #                         "due_date": {'$gte': todayTimeStamp},
            #                         "group": debtGroupCell
            #                     }
            #                 },{
            #                     "$group":
            #                     {
            #                         "_id": 'null',
            #                         "total_acc": {'$sum': 1},
            #                         "acc_arr": {'$push' : '$account_number'}
            #                     }
            #                 }
            #             ]
            #             accountByGroup = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'Group_card'),aggregate_pipeline=aggregate_group)
            #             if accountByGroup is not None:
            #                 for account in accountByGroup:
            #                     acc_arr = account['acc_arr']
            #             else:
            #                 acc_arr : []

            #             listOfAccount = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'List_of_account_in_collection'), WHERE={ 'account_number': {'$in': acc_arr}})
            #             for account in listOfAccount:
            #                 col_today += 1
            #                 amt_today += account['cur_bal']

            #             aggregate_sbv = [
            #                 {
            #                     "$match":
            #                     {
            #                         "created_at": {'$gte': temp['due_date'], '$lte': todayTimeStamp},
            #                         "contract_no": {'$in' : acc_arr }
            #                     }
            #                 },{
            #                     "$group":
            #                     {
            #                         "_id": 'null',
            #                         "sale_total": {'$sum': 'ob_principal_sale'},
            #                         "cash_total": {'$sum': 'ob_principal_cash'},
            #                     }
            #                 }
            #             ]
            #             sbvInfo = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'SBV'),aggregate_pipeline=aggregate_sbv)
            #             if sbvInfo is not None:
            #                 for sbv in sbvInfo:
            #                     ob_principal_today = float(sbv['sale_total']) + float(sbv['cash_total'])

            #             aggregate_gl = [
            #                 {
            #                     "$match":
            #                     {
            #                         "created_at": {'$gte': temp['due_date'],'$lte': todayTimeStamp},
            #                         "account_number": {'$in' : acc_arr}
            #                     }
            #                 },{
            #                     "$group":
            #                     {
            #                         "_id": 'null',
            #                         "total_amt": {'$sum': '$amount'},
            #                     }
            #                 }
            #             ]
            #             glInfo = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_input_payment_of_card'),aggregate_pipeline=aggregate_gl)
            #             if glInfo is not None:
            #                 for gl in glInfo:
            #                     temp['amt'] = gl['total_amt']


            #             temp['col']         = temp['inci'] - col_today
            #             temp['col_amt']     = temp['inci_amt'] - amt_today
            #             temp['col_prici']   = temp['inci_ob_principal'] - ob_principal_today
            #             temp['rem']         = temp['inci'] - temp['col']
            #             temp['rem_amt']     = temp['inci_amt'] - temp['col_amt']

            #             temp['flow_rate']       = temp['rem'] / temp['inci'] if temp['inci'] != 0 else 0
            #             temp['flow_rate_amt']   = temp['rem_amt'] / temp['inci_amt'] if temp['inci_amt'] != 0 else 0
            #             temp['col_rate']        = temp['col'] / temp['inci'] if temp['inci'] != 0 else 0

            #             temp['actual_ratio']    = temp['amt'] / temp['inci_amt'] if temp['inci_amt'] != 0 else 0
            #             temp['princi_ratio']    = temp['col_prici'] / temp['inci_ob_principal'] if temp['inci_ob_principal'] != 0 else 0
            #             temp['amt_ratio']       = temp['col_amt'] / temp['inci_amt'] if temp['inci_amt'] != 0 else 0

            #         temp['createdAt'] = time.time()
            #         temp['createdBy'] = 'system'
            #         temp['for_month'] = str(month)
            #         insertData.append(temp)
            #         mongodb.insert(MONGO_COLLECTION=collection, insert_data=temp)
            #         # pprint(temp)
            #     #     break
            #     # break

            # if debtGroupCell[0:1] != 'A':
            dueDayOfMonth = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_due_date'), WHERE={'for_month': str(month), 'debt_group': debtGroupCell[1:3]})
            for groupProduct in list(listGroupProduct):
                temp = {
                    'inci'           : 0,
                    'inci_amt'       : 0,
                    'inci_ob_principal'       : 0,
                    'col'           : 0,
                    'col_amt'       : 0,
                    'col_prici'      : 0,
                    'amt'            : 0,
                    'rem'            : 0,
                    'rem_amt'        : 0,
                    'flow_rate'      : 0,
                    'flow_rate_amt'  : 0,
                    'col_rate'       : 0,
                    'princi_ratio'       : 0,
                    'actual_ratio'    : 0,
                    'amt_ratio'       : 0,
                }
                col_today = 0
                amt_today = 0
                ob_principal_today = 0
                if todayTimeStamp < dueDayOfMonth['due_date_add_1']:
                    if month == 1:
                        lastMonth = 12
                    else:
                        lastMonth = month - 1
                    dueDayLastMonth = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_due_date'), WHERE={'for_month': str(lastMonth), 'debt_group': debtGroupCell[1:3]})
                    temp['due_date'] = dueDayLastMonth['due_date'] if dueDayLastMonth is not None else ''
                    #Lay gia tri no vao ngay due date + 1#
                    incidenceInfo = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Due_date_next_date'), WHERE={'for_month': str(lastMonth),  'debt_group': debtGroupCell[0:1],'due_date_code': debtGroupCell[1:3],'product':groupProduct['text']})
                    #Lay gia tri no vao ngay due date + 1#
                else:
                    temp['due_date'] = dueDayOfMonth['due_date']
                    incidenceInfo = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Due_date_next_date'), WHERE={'for_month': str(month), 'debt_group': debtGroupCell[0:1],'due_date_code': debtGroupCell[1:3],'product':groupProduct['text']})

                temp['debt_group']    = debtGroupCell[0:1]
                temp['due_date_code'] = int(debtGroupCell[1:3])
                temp['product']       = groupProduct['text']
                # temp['team_id'] = str(groupCell['_id'])
                acc_arr = []
                if incidenceInfo is not None:
                    for inc in incidenceInfo:
                        temp['inci']        += inc['debt_acc_no']
                        temp['inci_amt']    += inc['current_balance_total']
                        temp['inci_ob_principal'] += inc['ob_principal_total']
                        acc_arr_1   = inc['acc_arr'] if 'acc_arr' in inc.keys() else []
                        acc_arr     += acc_arr_1


                if groupProduct['value'] == 'SIBS':
                    # dueDateOneData = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Due_date_next_date_SIBS'), WHERE={'debt_group': debtGroupCell[0:1], 'due_date_code': debtGroupCell[1:3], 'for_month': str(month)})
                    # acc_arr = []
                    lnjc05Info = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'LNJC05'), WHERE={'group_id': debtGroupCell  })
                    for lnjc05 in lnjc05Info:
                        col_today += 1
                        amt_today += lnjc05['current_balance']
                        ob_principal_today += lnjc05['outstanding_principal']
                        # acc_arr.append(lnjc05['account_number'])

                    aggregate_ln3206 = [
                        {
                            "$match":
                            {
                                "created_at": {'$gte': temp['due_date'],'$lte': todayTimeStamp},
                                "account_number": {'$in' : acc_arr},
                                "code" : '10'
                            }
                        },{
                            "$group":
                            {
                                "_id": 'null',
                                "count_data" : {'$addToSet' : 'account_number'},
                                "total_amt": {'$sum': '$amt'},
                            }
                        }
                    ]
                    ln3206fInfo = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'LN3206F'),aggregate_pipeline=aggregate_ln3206)
                    if ln3206fInfo is not None:
                        for ln3206 in ln3206fInfo:
                            temp['col'] = len(ln3206['count_data'])
                            temp['amt'] = ln3206['total_amt']

                    # temp['col']         = temp['inci'] - col_today
                    temp['col_prici']   = temp['inci_ob_principal'] - ob_principal_today
                    temp['col_amt']     = temp['inci_amt'] - amt_today

                    temp['rem']         = temp['inci'] - temp['col']
                    temp['rem_amt']     = temp['inci_amt'] - temp['col_amt']

                    temp['flow_rate']       = temp['rem'] / temp['inci'] if temp['inci'] != 0 else 0
                    temp['flow_rate_amt']   = temp['rem_amt'] / temp['inci_amt'] if temp['inci_amt'] != 0 else 0
                    temp['col_rate']        = temp['col'] / temp['inci'] if temp['inci'] != 0 else 0

                    temp['actual_ratio']    = temp['amt'] / temp['inci_amt'] if temp['inci_amt'] != 0 else 0
                    temp['princi_ratio']    = temp['col_prici'] / temp['inci_ob_principal'] if temp['inci_ob_principal'] != 0 else 0
                    temp['amt_ratio']       = temp['col_amt'] / temp['inci_amt'] if temp['inci_amt'] != 0 else 0

                if groupProduct['value'] == 'Card':
                    aggregate_group = [
                        {
                            "$match":
                            {
                                "due_date": {'$gte': todayTimeStamp},
                                "group": debtGroupCell
                            }
                        },{
                            "$group":
                            {
                                "_id": 'null',
                                "total_acc": {'$sum': 1},
                                "acc_arr": {'$push' : '$account_number'}
                            }
                        }
                    ]
                    accountByGroup = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'Group_card'),aggregate_pipeline=aggregate_group)
                    if accountByGroup is not None:
                        for account in accountByGroup:
                            acc_arr = account['acc_arr']
                    else:
                        acc_arr : []

                    listOfAccount = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'List_of_account_in_collection'), WHERE={ 'account_number': {'$in': acc_arr}})
                    for account in listOfAccount:
                        col_today += 1
                        amt_today += account['cur_bal']

                    aggregate_sbv = [
                        {
                            "$match":
                            {
                                "created_at": {'$gte': temp['due_date'], '$lte': todayTimeStamp},
                                "contract_no": {'$in' : acc_arr }
                            }
                        },{
                            "$group":
                            {
                                "_id": 'null',
                                "sale_total": {'$sum': 'ob_principal_sale'},
                                "cash_total": {'$sum': 'ob_principal_cash'},
                            }
                        }
                    ]
                    sbvInfo = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'SBV'),aggregate_pipeline=aggregate_sbv)
                    if sbvInfo is not None:
                        for sbv in sbvInfo:
                            ob_principal_today = float(sbv['sale_total']) + float(sbv['cash_total'])

                    code = ['2000','2100','2700']
                    aggregate_gl = [
                        {
                            "$match":
                            {
                                "created_at": {'$gte': temp['due_date'],'$lte': todayTimeStamp},
                                "account_number": {'$in' : acc_arr},
                                'code' : {'$in' : code}
                            }
                        },{
                            "$group":
                            {
                                "_id": 'null',
                                "count_data" : {'$addToSet' : 'account_number'},
                                "total_amt": {'$sum': '$amount'},
                            }
                        }
                    ]
                    glInfo = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_input_payment_of_card'),aggregate_pipeline=aggregate_gl)
                    if glInfo is not None:
                        for gl in glInfo:
                            temp['col'] = len(gl['count_data'])
                            temp['amt'] = gl['total_amt']


                    # temp['col']         = temp['inci'] - col_today
                    temp['col_prici']   = temp['inci_ob_principal'] - ob_principal_today
                    temp['col_amt']     = temp['inci_amt'] - amt_today

                    temp['rem']         = temp['inci'] - temp['col']
                    temp['rem_amt']     = temp['inci_amt'] - temp['col_amt']

                    temp['flow_rate']       = temp['rem'] / temp['inci'] if temp['inci'] != 0 else 0
                    temp['flow_rate_amt']   = temp['rem_amt'] / temp['inci_amt'] if temp['inci_amt'] != 0 else 0
                    temp['col_rate']        = temp['col'] / temp['inci'] if temp['inci'] != 0 else 0

                    temp['actual_ratio']    = temp['amt'] / temp['inci_amt'] if temp['inci_amt'] != 0 else 0
                    temp['princi_ratio']    = temp['col_prici'] / temp['inci_ob_principal'] if temp['inci_ob_principal'] != 0 else 0
                    temp['amt_ratio']       = temp['col_amt'] / temp['inci_amt'] if temp['inci_amt'] != 0 else 0

                temp['createdAt'] = time.time()
                temp['createdBy'] = 'system'
                temp['for_month'] = str(month)
                insertData.append(temp)
                mongodb.insert(MONGO_COLLECTION=collection, insert_data=temp)
                # pprint(temp)
            #     break
            # break



    # # wo
    # temp = {
    #     'due_date'       : 0,
    #     'debt_group'     : 'F',
    #     'due_date_code'  : 99,
    #     'product'        : 'F- Total',
    #     'inci'           : 0,
    #     'inci_amt'       : 0,
    #     'inci_ob_principal'       : 0,
    #     'col'           : 0,
    #     'col_amt'       : 0,
    #     'col_prici'      : 0,
    #     'amt'            : 0,
    #     'rem'            : 0,
    #     'rem_amt'        : 0,
    #     'flow_rate'      : 0,
    #     'flow_rate_amt'  : 0,
    #     'col_rate'       : 0,
    #     'princi_ratio'       : 0,
    #     'actual_ratio'    : 0,
    #     'amt_ratio'       : 0,
    # }
    # col_today = 0
    # amt_today = 0
    # ob_principal_today = 0

    # incidenceInfo = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Due_date_next_date'), WHERE={'for_month': str(month), 'debt_group': 'F','product':'WO'})

    # acc_arr = []
    # if incidenceInfo is not None:
    #     for inc in incidenceInfo:
    #         temp['inci'] += inc['debt_acc_no']
    #         temp['inci_amt'] += inc['current_balance_total']
    #         temp['inci_ob_principal'] += inc['ob_principal_total']
    #         temp['due_date'] = inc['due_date']
    #         acc_arr_1 = inc['acc_arr'] if 'acc_arr' in inc.keys() else []
    #         acc_arr += acc_arr_1

    # count_wo = mongodb.count(MONGO_COLLECTION=common.getSubUser(subUserType, 'WO_monthly'))
    # arr_acc_payment = []
    # aggregate_payment = [
    #     {
    #         "$match":
    #         {
    #             "created_at": {'$gte': temp['due_date']},
    #             # "account_number": {'$in': acc_arr}
    #         }
    #     },{
    #         '$project':
    #         {
    #            'account_number' : 1,
    #            'pay_payment': {'$sum' : [ '$pay_9711', '$pay_9712' ,'$late_charge_9713'] }
    #         }
    #     },{
    #         "$group":
    #         {
    #             "_id": 'null',
    #             "total_amt": {'$sum': '$pay_payment'},
    #             "acc_arr": {'$addToSet': '$account_number'},
    #         }
    #     }
    # ]
    # woPayment = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'Wo_payment'),aggregate_pipeline=aggregate_payment)
    # if woPayment != None:
    #     for wo_row in woPayment:
    #         col_today = len(wo_row['acc_arr'])
    #         temp['amt'] = wo_row['total_amt']
    #         arr_acc_payment = wo_row['acc_arr']

    # if count_wo != None or count_wo != 0:
    #     aggregate_monthly = [
    #         {
    #             "$match":
    #             {
    #                 "ACCTNO": {'$in': arr_acc_payment}
    #             }
    #         },{
    #             '$project':
    #             {
    #                'WO9711': 1,
    #                'pay_payment': {'$sum' : [ '$WO9711', '$WO9712' ,'$WO9713'] }
    #             }
    #         },{
    #             "$group":
    #             {
    #                 "_id": 'null',
    #                 "total_amt": {'$sum': '$pay_payment'},
    #                 "ob_principal_total": {'$sum': '$WO9711'},
    #             }
    #         }
    #     ]
    #     woMonthly = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'WO_monthly'),aggregate_pipeline=aggregate_monthly)
    #     if woMonthly != None:
    #         for wo_row in woMonthly:
    #             amt_today           = wo_row['total_amt']
    #             ob_principal_today  = wo_row['ob_principal_total']

    # else:
    #     # wo all product
    #     aggregate_all_prod = [
    #         {
    #             "$match":
    #             {
    #                 "ACCTNO": {'$in': arr_acc_payment}
    #             }
    #         },{
    #             '$project':
    #             {
    #                'WOAMT': 1,
    #                'pay_payment': {'$sum' : [ '$OFF_OSTD', '$OFF_RECEIVE_INT' ,'$OFF_LATE_CHARGE'] }
    #             }
    #         },{
    #             "$group":
    #             {
    #                 "_id": 'null',
    #                 "total_amt": {'$sum': '$pay_payment'},
    #                 "ob_principal_total": {'$sum': '$WOAMT'},
    #             }
    #         }
    #     ]
    #     woAllProd = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'Wo_all_prod'),aggregate_pipeline=aggregate_all_prod)
    #     if woAllProd is not None:
    #         for wo_row in woAllProd:
    #             amt_today           = wo_row['total_amt']
    #             ob_principal_today  = wo_row['ob_principal_total']




    # temp['col']         = temp['inci'] - col_today
    # temp['col_amt']     = temp['inci_amt'] - amt_today
    # temp['col_prici']   = temp['inci_ob_principal'] - ob_principal_today
    # temp['rem']         = temp['inci'] - temp['col']
    # temp['rem_amt']     = temp['inci_amt'] - temp['col_amt']

    # temp['flow_rate']       = temp['rem'] / temp['inci'] if temp['inci'] != 0 else 0
    # temp['flow_rate_amt']   = temp['rem_amt'] / temp['inci_amt'] if temp['inci_amt'] != 0 else 0
    # temp['col_rate']        = temp['col'] / temp['inci'] if temp['inci'] != 0 else 0

    # temp['actual_ratio']    = temp['amt'] / temp['inci_amt'] if temp['inci_amt'] != 0 else 0
    # temp['princi_ratio']    = temp['col_prici'] / temp['inci_ob_principal'] if temp['inci_ob_principal'] != 0 else 0
    # temp['amt_ratio']       = temp['col_amt'] / temp['inci_amt'] if temp['inci_amt'] != 0 else 0


    # temp['createdAt'] = time.time()
    # temp['createdBy'] = 'system'
    # temp['for_month'] = str(month)
    # insertData.append(temp)
    # mongodb.insert(MONGO_COLLECTION=collection, insert_data=temp)


    for group in debtGroup['data']:
        if group['text'] != 'F':
            tempTotalSibs = {
                'debt_group'     : group['text'],
                'due_date_code'  : 99,
                'product'        : 'SIBS Total',
                'due_date'       : 0,
                'inci'           : 0,
                'inci_amt'       : 0,
                'inci_ob_principal'       : 0,
                'col'           : 0,
                'col_amt'       : 0,
                'col_prici'      : 0,
                'amt'            : 0,
                'rem'            : 0,
                'rem_amt'        : 0,
                'flow_rate'      : 0,
                'flow_rate_amt'  : 0,
                'col_rate'       : 0,
                'princi_ratio'       : 0,
                'actual_ratio'    : 0,
                'amt_ratio'       : 0,
            }
            tempTotalCard = {
                'debt_group'     : group['text'],
                'due_date_code'  : 99,
                'product'        : 'Card Total',
                'due_date'       : 0,
                'inci'           : 0,
                'inci_amt'       : 0,
                'inci_ob_principal'       : 0,
                'col'           : 0,
                'col_amt'       : 0,
                'col_prici'      : 0,
                'amt'            : 0,
                'rem'            : 0,
                'rem_amt'        : 0,
                'flow_rate'      : 0,
                'flow_rate_amt'  : 0,
                'col_rate'       : 0,
                'princi_ratio'       : 0,
                'actual_ratio'    : 0,
                'amt_ratio'       : 0,
            }
            tempTotal = {
                'debt_group'     : group['text'],
                'due_date_code'  : 100,
                'product'        : group['text']+' - Total',
                'due_date'       : 0,
                'inci'           : 0,
                'inci_amt'       : 0,
                'inci_ob_principal'       : 0,
                'col'           : 0,
                'col_amt'       : 0,
                'col_prici'      : 0,
                'amt'            : 0,
                'rem'            : 0,
                'rem_amt'        : 0,
                'flow_rate'      : 0,
                'flow_rate_amt'  : 0,
                'col_rate'       : 0,
                'princi_ratio'       : 0,
                'actual_ratio'    : 0,
                'amt_ratio'       : 0,
            }
            for row in insertData:
                if row['debt_group'] == group['text'] and row['product'] == 'SIBS':
                    tempTotalSibs['inci']               += row['inci']
                    tempTotalSibs['inci_amt']           += row['inci_amt']
                    tempTotalSibs['inci_ob_principal']  += row['inci_ob_principal']
                    tempTotalSibs['amt']                += row['amt']
                    tempTotalSibs['col_amt']            += row['col_amt']
                    tempTotalSibs['col_prici']          += row['col_prici']
                    tempTotalSibs['rem']                += row['rem']
                    tempTotalSibs['rem_amt']            += row['rem_amt']
                    tempTotalSibs['flow_rate']          += row['flow_rate']
                    tempTotalSibs['flow_rate_amt']      += row['flow_rate_amt']
                    tempTotalSibs['col_rate']           += row['col_rate']
                    tempTotalSibs['actual_ratio']       += row['actual_ratio']
                    tempTotalSibs['princi_ratio']       += row['princi_ratio']
                    tempTotalSibs['amt_ratio']          += row['amt_ratio']

                if row['debt_group'] == group['text'] and row['product'] == 'Card':
                    tempTotalCard['inci']               += row['inci']
                    tempTotalCard['inci_amt']           += row['inci_amt']
                    tempTotalCard['inci_ob_principal']  += row['inci_ob_principal']
                    tempTotalCard['amt']                += row['amt']
                    tempTotalCard['col_amt']            += row['col_amt']
                    tempTotalCard['col_prici']          += row['col_prici']
                    tempTotalCard['rem']                += row['rem']
                    tempTotalCard['rem_amt']            += row['rem_amt']
                    tempTotalCard['flow_rate']          += row['flow_rate']
                    tempTotalCard['flow_rate_amt']      += row['flow_rate_amt']
                    tempTotalCard['col_rate']           += row['col_rate']
                    tempTotalCard['actual_ratio']       += row['actual_ratio']
                    tempTotalCard['princi_ratio']       += row['princi_ratio']
                    tempTotalCard['amt_ratio']          += row['amt_ratio']

                if row['debt_group'] == group['text']:
                    tempTotal['inci']               += row['inci']
                    tempTotal['inci_amt']           += row['inci_amt']
                    tempTotal['inci_ob_principal']  += row['inci_ob_principal']
                    tempTotal['amt']                += row['amt']
                    tempTotal['col_amt']            += row['col_amt']
                    tempTotal['col_prici']          += row['col_prici']
                    tempTotal['rem']                += row['rem']
                    tempTotal['rem_amt']            += row['rem_amt']
                    tempTotal['flow_rate']          += row['flow_rate']
                    tempTotal['flow_rate_amt']      += row['flow_rate_amt']
                    tempTotal['col_rate']           += row['col_rate']
                    tempTotal['actual_ratio']       += row['actual_ratio']
                    tempTotal['princi_ratio']       += row['princi_ratio']
                    tempTotal['amt_ratio']          += row['amt_ratio']


            tempTotalSibs['createdAt'] = time.time()
            tempTotalSibs['createdBy'] = 'system'
            tempTotalSibs['for_month'] = str(month)
            tempTotalCard['createdAt'] = time.time()
            tempTotalCard['createdBy'] = 'system'
            tempTotalCard['for_month'] = str(month)
            tempTotal['createdAt'] = time.time()
            tempTotal['createdBy'] = 'system'
            tempTotal['for_month'] = str(month)

            mongodb.insert(MONGO_COLLECTION=collection, insert_data=tempTotalSibs)
            mongodb.insert(MONGO_COLLECTION=collection, insert_data=tempTotalCard)
            mongodb.insert(MONGO_COLLECTION=collection, insert_data=tempTotal)


    now_end         = datetime.now()
    log.write(now_end.strftime("%d/%m/%Y, %H:%M:%S") + ': End Log' + '\n')
    print('DONE')
except Exception as e:
    log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
    pprint(str(e))

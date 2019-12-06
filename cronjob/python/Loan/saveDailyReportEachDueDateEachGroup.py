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
base_url = config.base_url()
log = open(base_url + "cronjob/python/Loan/log/saveDailyProdEachGroup.txt","a")
now = datetime.now()
subUserType = 'LO'
collection = common.getSubUser(subUserType, 'Daily_prod_each_group')
try:
    insertData = []
    updateData = []
    listDebtGroup = []

    # today = date.today()
    today = datetime.strptime('20/11/2019', "%d/%m/%Y").date()

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

    # if todayTimeStamp in listHoliday or (weekday == 5) or weekday == 6:
    #     sys.exit()

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
        tempTotalSibs = {
            'debt_group'     : debtGroupCell[0:1],
            'due_date_code'  : debtGroupCell[1:3],
            'product'        : 'SIBS',
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
        tempTotalCard = tempTotalSibs
        tempTotal = tempTotalSibs
        tempTotalCard['product'] = 'Card'
        tempTotal['product'] = debtGroupCell[0:1] + ' - Total'

        if debtGroupCell[0:1] is not 'F':
            dueDayOfMonth = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_due_date'), WHERE={'for_month': str(month), 'debt_group': debtGroupCell[1:3]})
            for groupProduct in list(listGroupProduct):
                # groupInfoByDueDate = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Group'), WHERE={'debt_groups': debtGroupCell, 'name': {"$regex": groupProduct['text'] + '.*'}})
                # for groupCell in list(groupInfoByDueDate):
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
                    dueDayLastMonth = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_due_date'), WHERE={'for_month': str(month - 1), 'debt_group': debtGroupCell[1:3]})
                    temp['due_date'] = dueDayLastMonth['due_date'] if dueDayLastMonth is not None else ''
                    #Lay gia tri no vao ngay due date + 1#
                    incidenceInfo = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Due_date_next_date'), WHERE={'for_month': str(month - 1),  'debt_group': debtGroupCell[0:1],'due_date_code': debtGroupCell[1:3]})
                    #Lay gia tri no vao ngay due date + 1#
                else:
                    temp['due_date'] = dueDayOfMonth['due_date']
                    incidenceInfo = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Due_date_next_date'), WHERE={'for_month': str(month), 'debt_group': debtGroupCell[0:1],'due_date_code': debtGroupCell[1:3]})

                temp['debt_group'] = debtGroupCell[0:1]
                temp['due_date_code'] = debtGroupCell[1:3]
                temp['product'] = groupProduct['text']
                # temp['team_id'] = str(groupCell['_id'])
                acc_arr = []
                if incidenceInfo is not None:
                    for inc in incidenceInfo:
                        temp['inci'] += inc['debt_acc_no']
                        temp['inci_amt'] += inc['current_balance_total']
                        temp['inci_ob_principal'] += inc['ob_principal_total']
                        acc_arr_1 = inc['acc_arr'] if 'acc_arr' in inc.keys() else []
                        acc_arr += acc_arr_1


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
                                "account_number": {'$in' : acc_arr}
                            }
                        },{
                            "$group":
                            {
                                "_id": 'null',
                                "total_amt": {'$sum': '$amt'},
                            }
                        }
                    ]
                    ln3206fInfo = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'LN3206F'),aggregate_pipeline=aggregate_ln3206)
                    if ln3206fInfo is not None:
                        for ln3206 in ln3206fInfo:
                            temp['amt'] = ln3206['total_amt']

                    temp['col']         = temp['inci'] - col_today
                    temp['col_amt']     = temp['inci_amt'] - amt_today
                    temp['col_prici']   = temp['inci_ob_principal'] - ob_principal_today
                    temp['rem']         = temp['inci'] - temp['col']
                    temp['rem_amt']     = temp['inci_amt'] - temp['col_amt']

                    temp['flow_rate']       = temp['rem'] / temp['inci'] if temp['inci'] != 0 else 0
                    temp['flow_rate_amt']   = temp['rem_amt'] / temp['inci_amt'] if temp['inci_amt'] != 0 else 0
                    temp['col_rate']        = temp['col'] / temp['inci'] if temp['inci'] != 0 else 0

                    temp['actual_ratio']    = temp['amt'] / temp['inci_amt'] if temp['inci_amt'] != 0 else 0
                    temp['princi_ratio']    = temp['col_prici'] / temp['inci_ob_principal'] if temp['inci_ob_principal'] != 0 else 0
                    temp['amt_ratio']       = temp['col_amt'] / temp['inci_amt'] if temp['inci_amt'] != 0 else 0

                    tempTotalSibs['inci']               += temp['inci']
                    tempTotalSibs['inci_amt']           += temp['inci_amt']
                    tempTotalSibs['inci_ob_principal']  += temp['inci_ob_principal']
                    tempTotalSibs['amt']                += temp['amt']
                    tempTotalSibs['col_amt']            += temp['col_amt']
                    tempTotalSibs['col_prici']          += temp['col_prici']
                    tempTotalSibs['rem']                += temp['rem']
                    tempTotalSibs['rem_amt']            += temp['rem_amt']
                    tempTotalSibs['flow_rate']          += temp['flow_rate']
                    tempTotalSibs['flow_rate_amt']      += temp['flow_rate_amt']
                    tempTotalSibs['col_rate']           += temp['col_rate']
                    tempTotalSibs['actual_ratio']       += temp['actual_ratio']
                    tempTotalSibs['princi_ratio']       += temp['princi_ratio']
                    tempTotalSibs['amt_ratio']          += temp['amt_ratio']


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

                    aggregate_gl = [
                        {
                            "$match":
                            {
                                "created_at": {'$gte': temp['due_date'],'$lte': todayTimeStamp},
                                "account_number": {'$in' : acc_arr}
                            }
                        },{
                            "$group":
                            {
                                "_id": 'null',
                                "total_amt": {'$sum': '$amount'},
                            }
                        }
                    ]
                    glInfo = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_input_payment_of_card'),aggregate_pipeline=aggregate_gl)
                    if glInfo is not None:
                        for gl in glInfo:
                            temp['amt'] = gl['total_amt']


                    temp['col']         = temp['inci'] - col_today
                    temp['col_amt']     = temp['inci_amt'] - amt_today
                    temp['col_prici']   = temp['inci_ob_principal'] - ob_principal_today
                    temp['rem']         = temp['inci'] - temp['col']
                    temp['rem_amt']     = temp['inci_amt'] - temp['col_amt']

                    temp['flow_rate']       = temp['rem'] / temp['inci'] if temp['inci'] != 0 else 0
                    temp['flow_rate_amt']   = temp['rem_amt'] / temp['inci_amt'] if temp['inci_amt'] != 0 else 0
                    temp['col_rate']        = temp['col'] / temp['inci'] if temp['inci'] != 0 else 0

                    temp['actual_ratio']    = temp['amt'] / temp['inci_amt'] if temp['inci_amt'] != 0 else 0
                    temp['princi_ratio']    = temp['col_prici'] / temp['inci_ob_principal'] if temp['inci_ob_principal'] != 0 else 0
                    temp['amt_ratio']       = temp['col_amt'] / temp['inci_amt'] if temp['inci_amt'] != 0 else 0

                    tempTotalCard['inci']               += temp['inci']
                    tempTotalCard['inci_amt']           += temp['inci_amt']
                    tempTotalCard['inci_ob_principal']  += temp['inci_ob_principal']
                    tempTotalCard['amt']                += temp['amt']
                    tempTotalCard['col_amt']            += temp['col_amt']
                    tempTotalCard['col_prici']          += temp['col_prici']
                    tempTotalCard['rem']                += temp['rem']
                    tempTotalCard['rem_amt']            += temp['rem_amt']
                    tempTotalCard['flow_rate']          += temp['flow_rate']
                    tempTotalCard['flow_rate_amt']      += temp['flow_rate_amt']
                    tempTotalCard['col_rate']           += temp['col_rate']
                    tempTotalCard['actual_ratio']       += temp['actual_ratio']
                    tempTotalCard['princi_ratio']       += temp['princi_ratio']
                    tempTotalCard['amt_ratio']          += temp['amt_ratio']


                tempTotal['inci']               += temp['inci']
                tempTotal['inci_amt']           += temp['inci_amt']
                tempTotal['inci_ob_principal']  += temp['inci_ob_principal']
                tempTotal['amt']                += temp['amt']
                tempTotal['col_amt']            += temp['col_amt']
                tempTotal['col_prici']          += temp['col_prici']
                tempTotal['rem']                += temp['rem']
                tempTotal['rem_amt']            += temp['rem_amt']
                tempTotal['flow_rate']          += temp['flow_rate']
                tempTotal['flow_rate_amt']      += temp['flow_rate_amt']
                tempTotal['col_rate']           += temp['col_rate']
                tempTotal['actual_ratio']       += temp['actual_ratio']
                tempTotal['princi_ratio']       += temp['princi_ratio']
                tempTotal['amt_ratio']          += temp['amt_ratio']


                temp['createdAt'] = time.time()
                temp['createdBy'] = 'system'
                temp['for_month'] = str(month)
                tempTotalSibs['createdAt'] = time.time()
                tempTotalSibs['createdBy'] = 'system'
                tempTotalSibs['for_month'] = str(month)
                tempTotalCard['createdAt'] = time.time()
                tempTotalCard['createdBy'] = 'system'
                tempTotalCard['for_month'] = str(month)
                tempTotal['createdAt'] = time.time()
                tempTotal['createdBy'] = 'system'
                tempTotal['for_month'] = str(month)
                mongodb.insert(MONGO_COLLECTION=collection, insert_data=temp)
                # log.write(json.dumps(temp))
                # pprint(temp)

        # mongodb.insert(MONGO_COLLECTION=collection, insert_data=tempTotalSibs)
        # mongodb.insert(MONGO_COLLECTION=collection, insert_data=tempTotalCard)
        # mongodb.insert(MONGO_COLLECTION=collection, insert_data=tempTotal)
    # wo
    # groupInfo = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Group'), WHERE={'name': {"$regex": 'WO'},'debt_groups' : {'$exists': 'true'}})
    # for groupCell in groupInfo:
    #     temp = {
    #         'col'           : 0,
    #         'col_amt'       : 0,
    #         'rem'           : 0,
    #         'rem_amt'       : 0,
    #         'flow_rate'     : 0,
    #         'flow_rate_amt' : 0
    #     }
    #     temp['due_date_code']   = '1'
    #     temp['debt_group']      = 'F'
    #     temp['product']         = 'WO'
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

    #     for key, value in mainProduct.items():
    #         temp['col_' + key]  = 0
    #         temp['col_amt_' + key] = 0

    #         if incidenceInfo is not None:
    #             temp['inci_' + key] = incidenceInfo['debt_acc_' + key] if ('debt_acc_' + key) in incidenceInfo.keys() else 0
    #             temp['inci_amt_' + key] = incidenceInfo['current_balance_' + key] if ('current_balance_' + key) in incidenceInfo.keys() else 0
    #         else:
    #             temp['inci_' + key] = 0
    #             temp['inci_amt_' + key] = 0

    #         temp['rem_' + key] = 0
    #         temp['rem_amt_' + key] = 0
    #         temp['flow_rate_' + key] = 0
    #         temp['flow_rate_amt_' + key] = 0

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
    #                 "acc_arr": {'$addToSet' : '$account_number'}
    #             }
    #         }
    #     ]
    #     woPayment = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'Wo_payment'),aggregate_pipeline=aggregate_payment)
    #     acc_payment = []
    #     for payment in woPayment:
    #         temp['col'] = len(payment['acc_arr'])
    #         acc_payment = payment['acc_arr']

    #     aggregate_payment_prod = [
    #         {
    #             "$match":
    #             {
    #                 'ACCTNO': {'$in' : acc_payment },
    #             }
    #         },{
    #             '$project':
    #             {
    #                'pay_payment': {'$sum' : [ '$WO9711', '$WO9712' ,'$WO9713'] },
    #             }
    #         },{
    #             "$group":
    #             {
    #                 "_id": 'null',
    #                 "total_amt": {'$sum': '$pay_payment'},
    #                 "total_acc": {'$sum': 1},
    #             }
    #         }
    #     ]
    #     woMonthlyProd = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'WO_monthly'),aggregate_pipeline=aggregate_payment_prod)
    #     if woMonthlyProd != None:
    #         for woRowProd in woMonthlyProd:
    #             temp['col_amt'] = woRowProd['total_amt']

    #     temp['rem'] = temp['inci'] - temp['col']
    #     temp['rem_amt'] = temp['inci_amt'] - temp['col_amt']
    #     temp['flow_rate'] = temp['rem'] / temp['inci'] if temp['inci'] != 0 else 0
    #     temp['flow_rate_amt'] = temp['rem_amt'] / temp['inci_amt'] if temp['inci_amt'] != 0 else 0

    #     aggregate_payment_prod = [
    #         {
    #             "$match":
    #             {
    #                 'ACCTNO': {'$in' : acc_payment },
    #             }
    #         },{
    #             '$project':
    #             {
    #                 'PROD_ID' : 1,
    #                 'pay_payment': {'$sum' : [ '$WO9711', '$WO9712' ,'$WO9713'] },
    #             }
    #         },{
    #             "$group":
    #             {
    #                 "_id": '$PROD_ID',
    #                 "total_amt": {'$sum': '$pay_payment'},
    #                 "total_acc": {'$sum': 1},
    #             }
    #         }
    #     ]
    #     woMonthlyProd1 = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'WO_monthly'),aggregate_pipeline=aggregate_payment_prod)
    #     if woMonthlyProd1 != None:
    #         for woRowProd in woMonthlyProd1:
    #             temp['col_' + woRowProd['_id']] = woRowProd['total_acc']
    #             temp['col_amt_' + woRowProd['_id']] = woRowProd['total_amt']

    #             temp['rem_' + woRowProd['_id']] = temp['inci_' + woRowProd['_id']] - temp['col_' + woRowProd['_id']]
    #             temp['rem_amt_' + woRowProd['_id']] = temp['inci_amt_' + woRowProd['_id']] - temp['col_amt_' + woRowProd['_id']]
    #             temp['flow_rate_' + woRowProd['_id']] = temp['rem_' + woRowProd['_id']] / temp['inci_' + woRowProd['_id']] if temp['inci_' + woRowProd['_id']] != 0 else 0
    #             temp['flow_rate_amt_' + woRowProd['_id']] = temp['rem_amt_' + woRowProd['_id']] / temp['inci_amt_' + woRowProd['_id']] if temp['inci_amt_' + woRowProd['_id']] != 0 else 0

    #     targetInfo = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Target'), WHERE={ 'group.id': str(groupCell['_id'])})
    #     target = int(targetInfo['target'])
    #     temp['tar_amt'] = (target * temp['inci_amt'])/100
    #     temp['tar_gap'] = temp['tar_amt'] - temp['rem_amt']
    #     temp['tar_per'] = temp['tar_gap']/temp['tar_amt'] if temp['tar_amt'] != 0 else 0
    #     mongodb.insert(MONGO_COLLECTION=collection, insert_data=temp)
    print('DONE')

except Exception as e:
    # log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
    pprint(str(e))

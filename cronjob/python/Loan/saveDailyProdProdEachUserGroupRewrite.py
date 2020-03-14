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

common      = Common()
base_url    = common.base_url()
wff_env     = common.wff_env(base_url)
mongodb     = Mongodb(MONGODB="worldfone4xs", WFF_ENV=wff_env)
_mongodb    = Mongodb(MONGODB="_worldfone4xs", WFF_ENV=wff_env)

subUserType = 'LO'
collection              = common.getSubUser(subUserType, 'Daily_prod_prod_each_user_group')
lnjc05_collection       = common.getSubUser(subUserType, 'LNJC05')
ln3206f_collection      = common.getSubUser(subUserType, 'LN3206F')
zaccf_collection        = common.getSubUser(subUserType, 'ZACCF_yesterday')
account_collection      = common.getSubUser(subUserType, 'List_of_account_in_collection')
gl_collection           = common.getSubUser(subUserType, 'Report_input_payment_of_card')
jsonData_collection     = common.getSubUser(subUserType, 'Jsondata')
product_collection      = common.getSubUser(subUserType, 'Product')
group_collection        = common.getSubUser(subUserType, 'Group')
target_collection       = common.getSubUser(subUserType, 'Target_of_report')
report_due_date_collection              = common.getSubUser(subUserType, 'Report_due_date')
due_date_next_date_group_collection     = common.getSubUser(subUserType, 'Due_date_next_date_by_group')
diallist_detail_collection     = common.getSubUser(subUserType, 'Diallist_detail')

log = open(base_url + "cronjob/python/Loan/log/saveDailyProdProdEachUserGroup.txt","a")
now = datetime.now()
log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': Start Import' + '\n')
try:
    insertData = []
    updateData = []
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
    endTodayTimeStamp = int(time.mktime(time.strptime(str(todayString + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))

    startMonth = int(time.mktime(time.strptime(str('01/' + str(month) + '/' + str(year) + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
    endMonth = int(time.mktime(time.strptime(str(str(lastDayOfMonth) + '/' + str(month) + '/' + str(year) + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))

    holidayOfMonth = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_off_sys'))
    listHoliday = map(lambda offDateRow: {offDateRow['off_date']}, holidayOfMonth)

    if todayTimeStamp in listHoliday:
        sys.exit()

    mainProduct = {}
    mainProductRaw = mongodb.get(MONGO_COLLECTION=product_collection)
    for prod in mainProductRaw:
        mainProduct[prod['code']] = prod['name']

    debtGroup = _mongodb.getOne(MONGO_COLLECTION=jsonData_collection, WHERE={'tags': ['debt', 'group']})
    dueDate = _mongodb.getOne(MONGO_COLLECTION=jsonData_collection, WHERE={'tags': ['debt', 'duedate']})

    for group in debtGroup['data']:
        for duedate in dueDate['data']:
            listDebtGroup.append(group['text'] + duedate['text'])

    listDebtGroup = sorted(listDebtGroup)

    listGroupProductRaw = _mongodb.getOne(MONGO_COLLECTION=jsonData_collection, WHERE={'tags': ['group', 'debt', 'product']})
    listGroupProduct = listGroupProductRaw['data']

    for debtGroupCell in list(listDebtGroup):
        if debtGroupCell[0:1] != 'F':
            dueDayOfMonth = mongodb.getOne(MONGO_COLLECTION=report_due_date_collection, WHERE={'for_month': str(month), 'debt_group': debtGroupCell[1:3]})
            for groupProduct in list(listGroupProduct):
                groupInfoByDueDate = mongodb.get(MONGO_COLLECTION=group_collection, WHERE={ 'name': {"$regex": groupProduct['text'] + '/Group ' + debtGroupCell[0:1]} })
                for groupCell in list(groupInfoByDueDate):
                    if 'G2' in groupCell['name'] or 'G3' in groupCell['name']:
                        continue
                    # print(debtGroupCell[1:3])
                    # print(groupCell['name'])

                    temp = {
                        'col'           : 0,
                        'col_amt'       : 0,
                        'rem'           : 0,
                        'rem_amt'       : 0,
                        'flow_rate'     : 0,
                        'flow_rate_amt' : 0,
                        'col_ratio'     : 0,
                        'col_ratio_amt' : 0,
                    }
                    col_today = 0
                    col_amt_today = 0
                    if todayTimeStamp < dueDayOfMonth['due_date_add_1']:
                        if month == 1:
                            lastMonth = 12
                        else:
                            lastMonth = month - 1
                        dueDayLastMonth = mongodb.getOne(MONGO_COLLECTION=report_due_date_collection, WHERE={'for_month': str(lastMonth), 'debt_group': debtGroupCell[1:3]})
                        temp['due_date'] = dueDayLastMonth['due_date'] if dueDayLastMonth is not None else ''
                        #Lay gia tri no vao ngay due date + 1#
                        incidenceInfo = mongodb.getOne(MONGO_COLLECTION=due_date_next_date_group_collection, WHERE={'for_month': str(lastMonth), 'team_id': str(groupCell['_id'])})
                    else:
                        incidenceInfo = mongodb.getOne(MONGO_COLLECTION=due_date_next_date_group_collection, WHERE={'for_month': str(month), 'team_id': str(groupCell['_id'])})
                        temp['due_date'] = dueDayOfMonth['due_date']


                    temp['debt_group'] = debtGroupCell[0:1]
                    temp['due_date_code'] = debtGroupCell[1:3]
                    temp['product'] = groupProduct['text']
                    temp['team'] = groupCell['name']
                    temp['team_id'] = str(groupCell['_id'])

                    if incidenceInfo is not None:
                        temp['inci'] = incidenceInfo['debt_acc_no'] if 'debt_acc_no' in incidenceInfo.keys() else 0
                        temp['inci_amt'] = round(incidenceInfo['current_balance_total']/1000) if 'current_balance_total' in incidenceInfo.keys() else 0
                        acc_arr = incidenceInfo['acc_arr'] if 'acc_arr' in incidenceInfo.keys() else []
                    else:
                        temp['inci'] = 0
                        temp['inci_amt'] = 0
                        acc_arr = []

                    for key, value in mainProduct.items():
                        temp['col_' + key]          = 0
                        temp['col_amt_' + key]      = 0

                        if incidenceInfo is not None:
                            temp['inci_' + key]     = incidenceInfo['debt_acc_' + key] if ('debt_acc_' + key) in incidenceInfo.keys() else 0
                            temp['inci_amt_' + key] = round(incidenceInfo['current_balance_' + key]/1000) if ('current_balance_' + key) in incidenceInfo.keys() else 0
                        else:
                            temp['inci_' + key]     = 0
                            temp['inci_amt_' + key] = 0

                        # temp['rem_' + key]          = 0
                        # temp['rem_amt_' + key]      = 0
                        # temp['flow_rate_' + key]    = 0
                        # temp['flow_rate_amt_' + key] = 0

                    aggregate_diallist = [
                        {
                            "$match":
                            {
                                  "createdAt": {'$gte': todayTimeStamp, '$lte': endTodayTimeStamp},
                                  "account_number": {'$in': acc_arr},
                            }
                        },{
                          "$group":
                          {
                              "_id": 'null',
                              "count_col": {'$sum': 1},
                          }
                        }
                    ]
                    diallistData = mongodb.aggregate_pipeline(MONGO_COLLECTION=diallist_detail_collection,aggregate_pipeline=aggregate_diallist)
                    if diallistData != None:
                        for row in diallistData:
                            col_today            = row['count_col']

                    due_date_add_2 = temp['due_date'] + 86400*2
                    if groupProduct['value'] == 'SIBS':

                        aggregate_ln3206 = [
                            {
                                "$match":
                                {
                                      "created_at": {'$gte': due_date_add_2, '$lte': endTodayTimeStamp},
                                      "account_number": {'$in': acc_arr},
                                      "code" : '10'
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
                                temp['col_amt']        = round(row['sum_amount']/1000)


                        for key, value in mainProduct.items():
                            col_product_today = 0
                            aggregate_diallist = [
                                {
                                    "$match":
                                    {
                                          "createdAt": {'$gte': todayTimeStamp, '$lte': endTodayTimeStamp},
                                          "account_number": {'$in': acc_arr},
                                          "PRODGRP_ID" : key
                                    }
                                },{
                                  "$group":
                                  {
                                      "_id": 'null',
                                      "count_col": {'$sum': 1},
                                  }
                                }
                            ]
                            diallistInfo = mongodb.aggregate_pipeline(MONGO_COLLECTION=diallist_detail_collection,aggregate_pipeline=aggregate_diallist)
                            if diallistData != None:
                                for row in diallistData:
                                    col_product_today            = row['count_col']

                            temp['col_' + key]  = temp['inci_' + key]  - col_product_today


                            aggregate_zaccf = [
                                {
                                    "$match":
                                    {
                                          "account_number": {'$in': acc_arr},
                                          "PRODGRP_ID" : key
                                    }
                                },{
                                  "$group":
                                  {
                                      "_id": 'null',
                                      "count_acc": {'$push': '$account_number'},
                                  }
                                }
                            ]
                            zaccfData = mongodb.aggregate_pipeline(MONGO_COLLECTION=zaccf_collection,aggregate_pipeline=aggregate_zaccf)
                            acc_by_code = []
                            if zaccfData != None:
                                for row in zaccfData:
                                    acc_by_code            = row['count_acc']


                            aggregate_ln3206 = [
                                {
                                    "$match":
                                    {
                                          "created_at": {'$gte': due_date_add_2, '$lte': endTodayTimeStamp},
                                          "account_number": {'$in': acc_by_code},
                                          "code" : '10'
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
                                    # temp['col_' + key]          = len(row['count_col'])
                                    temp['col_amt_' + key]      = round(row['sum_amount']/1000)

                        

                        temp['col']             = temp['inci'] - col_today
                        temp['rem']             = temp['inci'] - temp['col']
                        temp['col_ratio']       = temp['col'] / temp['inci'] if temp['inci'] != 0 else 0
                        temp['col_ratio_amt']   = temp['col_amt'] / temp['inci_amt'] if temp['inci_amt'] != 0 else 0
                        temp['rem_amt']         = temp['inci_amt'] - temp['col_amt']
                        temp['flow_rate']       = temp['rem'] / temp['inci'] if temp['inci'] != 0 else 0
                        temp['flow_rate_amt']   = temp['rem_amt'] / temp['inci_amt'] if temp['inci_amt'] != 0 else 0

                    

                    if groupProduct['value'] == 'Card':

                        code = ['2000','2100','2700']
                        for row_acc in acc_arr:
                            aggregate_gl = [
                                {
                                    "$match":
                                    {
                                        "created_at": {'$gte': due_date_add_2, '$lte': endTodayTimeStamp},
                                        "account_number": row_acc,
                                        "code" : {'$in' : code},
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
                                    # temp['col']             += 1
                                    temp['col_amt']         += sum_code
                                    # temp['col_301']         += 1
                                    temp['col_amt_301']     += sum_code


                        temp['col']             = temp['inci'] - col_today
                        temp['col_301']         = temp['col']
                        temp['col_amt']         = round(temp['col_amt']/1000)
                        temp['col_amt_301']     = round(temp['col_amt_301']/1000)
                        temp['rem']             = temp['inci'] - temp['col']
                        temp['rem_amt']         = temp['inci_amt'] - temp['col_amt']
                        temp['col_ratio']       = temp['col'] / temp['inci'] if temp['inci'] != 0 else 0
                        temp['col_ratio_amt']   = temp['col_amt'] / temp['inci_amt'] if temp['inci_amt'] != 0 else 0
                        temp['flow_rate']       = temp['rem'] / temp['inci'] if temp['inci'] != 0 else 0
                        temp['flow_rate_amt']   = temp['rem_amt'] / temp['inci_amt'] if temp['inci_amt'] != 0 else 0
                        


                    if groupProduct['text'] == 'Card':
                        debt_type = 'CARD'
                    else:
                        debt_type = 'SIBS'
                    if debtGroupCell[0:1] == 'A':
                        targetInfo = mongodb.getOne(MONGO_COLLECTION=target_collection, WHERE={ 'duedate_type': str(debtGroupCell), 'debt_type': debt_type })
                    else:
                        targetInfo = mongodb.getOne(MONGO_COLLECTION=target_collection, WHERE={ 'B_plus_duedate_type': debtGroupCell, 'debt_type': debt_type })

                    if targetInfo != None:
                        target = targetInfo['target']
                        temp['tar_amt'] = (target * temp['inci_amt'])/100
                        temp['tar_gap'] = temp['tar_amt'] - temp['col_amt']
                        temp['tar_per'] = target/100

                    temp['createdAt'] = todayTimeStamp
                    temp['createdBy'] = 'system'
                    mongodb.insert(MONGO_COLLECTION=collection, insert_data=temp)
                    # pprint(temp)
                # break








    # # wo
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
    #         {
    #             "$match":
    #             {
    #                 "created_at": {'$gte': temp['due_date'],'$lte' : todayTimeStamp},
    #             }
    #         },
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

    #         temp['rem'] = temp['inci'] - temp['col']
    #         temp['rem_amt'] = temp['inci_amt'] - temp['col_amt']
    #         temp['col_ratio'] = temp['col'] - temp['inci']
    #         temp['col_ratio_amt'] = temp['col_amt'] - temp['inci_amt']
    #         temp['flow_rate'] = temp['rem'] / temp['inci'] if temp['inci'] != 0 else 0
    #         temp['flow_rate_amt'] = temp['rem_amt'] / temp['inci_amt'] if temp['inci_amt'] != 0 else 0

    #         aggregate_payment_prod = [
    #             {
    #                 "$match":
    #                 {
    #                     'ACCTNO': {'$in' : acc_payment },
    #                 }
    #             },{
    #                 '$project':
    #                 {
    #                     'PROD_ID' : 1,
    #                     'pay_payment': {'$sum' : [ '$WO9711', '$WO9712' ,'$WO9713'] },
    #                 }
    #             },{
    #                 "$group":
    #                 {
    #                     "_id": '$PROD_ID',
    #                     "total_amt": {'$sum': '$pay_payment'},
    #                     "total_acc": {'$sum': 1},
    #                 }
    #             }
    #         ]
    #         woMonthlyProd1 = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'WO_monthly'),aggregate_pipeline=aggregate_payment_prod)
    #         if woMonthlyProd1 != None:
    #             for woRowProd in woMonthlyProd1:
    #                 temp['col_' + woRowProd['_id']] = woRowProd['total_acc']
    #                 temp['col_amt_' + woRowProd['_id']] = woRowProd['total_amt']

    #                 # temp['rem_' + woRowProd['_id']] = temp['inci_' + woRowProd['_id']] - temp['col_' + woRowProd['_id']]
    #                 # temp['rem_amt_' + woRowProd['_id']] = temp['inci_amt_' + woRowProd['_id']] - temp['col_amt_' + woRowProd['_id']]
    #                 # temp['flow_rate_' + woRowProd['_id']] = temp['rem_' + woRowProd['_id']] / temp['inci_' + woRowProd['_id']] if temp['inci_' + woRowProd['_id']] != 0 else 0
    #                 # temp['flow_rate_amt_' + woRowProd['_id']] = temp['rem_amt_' + woRowProd['_id']] / temp['inci_amt_' + woRowProd['_id']] if temp['inci_amt_' + woRowProd['_id']] != 0 else 0

    #     else:
    #         aggregate_all_prod = [
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
    #         woAllProd = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'Wo_all_prod'),aggregate_pipeline=aggregate_all_prod)
    #         if woAllProd != None:
    #             for woRowProd in woAllProd:
    #                 temp['col_amt'] = woRowProd['total_amt']

    #         temp['rem'] = temp['inci'] - temp['col']
    #         temp['rem_amt'] = temp['inci_amt'] - temp['col_amt']
    #         temp['flow_rate'] = temp['rem'] / temp['inci'] if temp['inci'] != 0 else 0
    #         temp['flow_rate_amt'] = temp['rem_amt'] / temp['inci_amt'] if temp['inci_amt'] != 0 else 0

    #         aggregate_all_prod_1 = [
    #             {
    #                 "$match":
    #                 {
    #                     'ACCTNO': {'$in' : acc_payment },
    #                 }
    #             },{
    #                 '$project':
    #                 {
    #                     'PRODUCT' : 1,
    #                     'pay_payment': {'$sum' : [ '$WOAMT', '$WO_INT' ,'$WO_LC'] },
    #                 }
    #             },{
    #                 "$group":
    #                 {
    #                     "_id": '$PRODUCT',
    #                     "total_amt": {'$sum': '$pay_payment'},
    #                     "total_acc": {'$sum': 1},
    #                 }
    #             }
    #         ]
    #         woAllProd1 = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'Wo_all_prod'),aggregate_pipeline=aggregate_all_prod_1)
    #         if woAllProd1 != None:
    #             for woRowProd in woAllProd1:
    #                 temp['col_' + woRowProd['_id']] = woRowProd['total_acc']
    #                 temp['col_amt_' + woRowProd['_id']] = woRowProd['total_amt']

    #                 # temp['rem_' + woRowProd['_id']] = temp['inci_' + woRowProd['_id']] - temp['col_' + woRowProd['_id']]
    #                 # temp['rem_amt_' + woRowProd['_id']] = temp['inci_amt_' + woRowProd['_id']] - temp['col_amt_' + woRowProd['_id']]
    #                 # temp['flow_rate_' + woRowProd['_id']] = temp['rem_' + woRowProd['_id']] / temp['inci_' + woRowProd['_id']] if temp['inci_' + woRowProd['_id']] != 0 else 0
    #                 # temp['flow_rate_amt_' + woRowProd['_id']] = temp['rem_amt_' + woRowProd['_id']] / temp['inci_amt_' + woRowProd['_id']] if temp['inci_amt_' + woRowProd['_id']] != 0 else 0


    #     targetInfo = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Target'), WHERE={ 'group.id': str(groupCell['_id'])})
    #     target = int(targetInfo['target'])
    #     temp['tar_amt'] = (target * temp['inci_amt'])/100
    #     temp['tar_gap'] = temp['tar_amt'] - temp['rem_amt']
    #     temp['tar_per'] = temp['tar_gap']/temp['tar_amt'] if temp['tar_amt'] != 0 else 0

    #     temp['createdAt'] = time.time()
    #     temp['createdBy'] = 'system'
    #     mongodb.insert(MONGO_COLLECTION=collection, insert_data=temp)
    print('DONE')
    now_end         = datetime.now()
    log.write(now_end.strftime("%d/%m/%Y, %H:%M:%S") + ': End Log' + '\n')
except Exception as e:
    log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
    print(traceback.format_exc())

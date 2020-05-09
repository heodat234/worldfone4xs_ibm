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
excel = Excel()
config = Config()
ftp = Ftp()
log = open(base_url + "cronjob/python/Loan/log/calDueDateValue.txt","a")
now = datetime.now()
subUserType = 'LO'
collection           = common.getSubUser(subUserType, 'Due_date_next_date_by_group')
zaccf_collection     = common.getSubUser(subUserType, 'ZACCF_report')
sbv_collection       = common.getSubUser(subUserType, 'SBV')
diallist_collection  = common.getSubUser(subUserType, 'Diallist_detail')
group_collection     = common.getSubUser(subUserType, 'Group')
report_due_date_collection     = common.getSubUser(subUserType, 'Report_due_date')
jsonData_collection  = common.getSubUser(subUserType, 'Jsondata')
product_collection   = common.getSubUser(subUserType, 'Product')
cus_assigned_partner_collection   = common.getSubUser(subUserType, 'Cus_assigned_partner')
lnjc05_collection       = common.getSubUser(subUserType, 'LNJC05')

try:
    insertData = []
    updateData = []
    listDebtGroup = []

    today = date.today()
    # today = datetime.strptime('24/03/2020', "%d/%m/%Y").date()

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

    todayString = today.strftime("%d/%m/%Y")
    starttime = int(time.mktime(time.strptime(str(todayString + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
    endtime = int(time.mktime(time.strptime(str(todayString + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))

    collectionName = 'LO_input_report_' + str(year) + str(month)

    if day == 1:
        mongodb.create_db(collectionName)

    mongodb.remove_document(MONGO_COLLECTION=collection, WHERE={'created_at': {'$gte': todayTimeStamp, '$lte': endTodayTimeStamp} })

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
        if debtGroupCell[0:1] == 'A':
            dueDayOfMonth = mongodb.getOne(MONGO_COLLECTION=report_due_date_collection, WHERE={'due_date_add_1': todayTimeStamp, 'debt_group': debtGroupCell[1:3]})
            if dueDayOfMonth != None:
                for groupProduct in list(listGroupProduct):
                    groupInfoByDueDate = mongodb.get(MONGO_COLLECTION=group_collection, WHERE={'name': {"$regex": groupProduct['text'] + '/Group ' + debtGroupCell[0:1]} })
                    for groupCell in list(groupInfoByDueDate):
                        if 'G2' in groupCell['name'] or 'G3' in groupCell['name']:
                            continue
                        if debtGroupCell[1:3] == '03':
                            if month == 1:
                                lastmonth = 12
                            else:
                                lastmonth = month - 1
                        else:
                            lastmonth = month


                        members = []
                        name = groupCell['name']
                        name = name.replace('G1','')
                        groupInfoByName = mongodb.get(MONGO_COLLECTION=group_collection, WHERE={'name': {"$regex": name} })
                        for groupInfo in list(groupInfoByName):
                            if 'members' in groupInfo.keys():
                                members += groupInfo['members']

                        temp = {
                            'due_date'              : todayTimeStamp - 86400,
                            'due_date_one'          : todayTimeStamp,
                            'product'               : groupProduct['value'],
                            'debt_group'            : debtGroupCell[0:1],
                            'due_date_code'         : debtGroupCell[1:3],
                            'team_id'               : str(groupCell['_id']),
                            'team_name'             : name,
                            'for_month'             : str(lastmonth),
                            'debt_acc_no'           : 0,
                            'current_balance_total' : 0,
                            'ob_principal_total'    : 0,
                            'acc_arr'               : []
                        }

                        for key, value in mainProduct.items():
                            temp['debt_acc_' + key] = 0
                            temp['current_balance_' + key] = 0

                        if groupProduct['value'] == 'SIBS':
                            lead = ['JIVF00' + groupCell['lead']] if 'lead' in groupCell.keys() else []
                            # member = ('JIVF00' + s for s in members)
                            officerIdRaw = list(lead) 
                            officerId = list(dict.fromkeys(officerIdRaw))
                            lnjc05Detail = list(mongodb.get(MONGO_COLLECTION=lnjc05_collection, WHERE={'group_id': debtGroupCell, 'officer_id': {'$in': officerId}, "created_at": {'$gte': todayTimeStamp, '$lte' : endTodayTimeStamp} }))
                            for lnjc05 in lnjc05Detail:
                                partnerInfo = mongodb.getOne(MONGO_COLLECTION=cus_assigned_partner_collection, WHERE={'CONTRACTNR': lnjc05['account_number']})
                                if partnerInfo == None:
                                    zaccfInfo = mongodb.getOne(MONGO_COLLECTION=zaccf_collection, WHERE={'account_number': lnjc05['account_number']})
                                    temp['debt_acc_no']            += 1
                                    temp['current_balance_total']  += float(lnjc05['current_balance'])
                                    temp['ob_principal_total']     += float(lnjc05['outstanding_principal'])
                                    temp['acc_arr'].append(lnjc05['account_number'])
                                    if zaccfInfo is not None:
                                        temp['debt_acc_' + zaccfInfo['PRODGRP_ID']]        += 1
                                        temp['current_balance_' + zaccfInfo['PRODGRP_ID']] += float(lnjc05['current_balance'])


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
                                        "total_amt": {'$sum': '$cur_bal'},
                                        "total_acc": {'$sum': 1},
                                        "acc_arr": {'$push' : '$account_number'}
                                    }
                                }
                            ]
                            diallistDetail = mongodb.aggregate_pipeline(MONGO_COLLECTION=diallist_collection,aggregate_pipeline=aggregate_diallist)
                            if diallistDetail is not None:
                                for diallist in diallistDetail:
                                    temp['debt_acc_no']           = diallist['total_acc']
                                    temp['current_balance_total'] = diallist['total_amt']
                                    # temp['debt_acc_301']          = diallist['total_acc']
                                    # temp['current_balance_301']   = diallist['total_amt']
                                    temp['acc_arr']               = diallist['acc_arr']

                            aggregate_sbv = [
                                {
                                    "$match":
                                    {
                                        "contract_no": {'$in' : temp['acc_arr']}
                                    }
                                },{
                                    "$group":
                                    {
                                        "_id": 'null',
                                        "sale_total": {'$sum': '$ob_principal_sale'},
                                        "cash_total": {'$sum': '$ob_principal_cash'},
                                    }
                                }
                            ]
                            sbvInfo = mongodb.aggregate_pipeline(MONGO_COLLECTION=sbv_collection,aggregate_pipeline=aggregate_sbv)
                            if sbvInfo != None:
                                for sbv in sbvInfo:
                                    temp['ob_principal_total'] = sbv['sale_total'] + sbv['cash_total']


                            aggregate_diallist = [
                                {
                                    "$match":
                                    {
                                        "createdAt": {'$gte': todayTimeStamp, '$lte' : endTodayTimeStamp},
                                        "assign": {'$in' : assign},
                                        "group_id": debtGroupCell[0:1]+'-'+debtGroupCell[1:3]
                                    }
                                },{
                                       "$lookup":
                                       {
                                           "from": sbv_collection,
                                           "localField": "account_number",
                                           "foreignField": "contract_no",
                                           "as": "detail"
                                       }
                                }
                            ]
                            diallistDetail = mongodb.aggregate_pipeline(MONGO_COLLECTION=diallist_collection,aggregate_pipeline=aggregate_diallist)
                            if diallistDetail is not None:
                                for diallist in diallistDetail:
                                    card_type = 0
                                    for sbv in diallist['detail']:
                                        card_type = int(sbv['card_type'])
                                    if card_type > 1 and card_type <= 99:                                    
                                        temp['debt_acc_301']          += 1
                                        temp['current_balance_301']   += diallist['cur_bal']
                                    else:
                                        temp['debt_acc_302']          += 1
                                        temp['current_balance_302']   += diallist['cur_bal']

                        temp['created_at'] = time.time()
                        temp['created_by'] = 'system'
                        # pprint(temp)
                        # break
                        mongodb.insert(MONGO_COLLECTION=collection, insert_data=temp)


        if debtGroupCell[0:1] is not 'A' and debtGroupCell[0:1] is not 'F':
            dueDayOfMonth = mongodb.getOne(MONGO_COLLECTION=report_due_date_collection, WHERE={'due_date_add_1': todayTimeStamp, 'debt_group': debtGroupCell[1:3]})
            if dueDayOfMonth != None:
                for groupProduct in list(listGroupProduct):
                    groupInfoByDueDate = mongodb.get(MONGO_COLLECTION=group_collection, WHERE={'name': {"$regex": groupProduct['text'] + '/Group ' + debtGroupCell[0:1] + '/' + debtGroupCell} })
                    for groupCell in list(groupInfoByDueDate):
                        
                        if debtGroupCell[1:3] == '03':
                            if month == 1:
                                lastmonth = 12
                            else:
                                lastmonth = month - 1
                        else:
                            lastmonth = month

                        members = []
                        if 'members' in groupCell.keys():
                            members = groupCell['members']
                        name = groupCell['name']

                        temp = {
                            'due_date'              : todayTimeStamp - 86400,
                            'due_date_one'          : todayTimeStamp,
                            'product'               : groupProduct['value'],
                            'debt_group'            : debtGroupCell[0:1],
                            'due_date_code'         : debtGroupCell[1:3],
                            'team_id'               : str(groupCell['_id']),
                            'team_name'             : name,
                            'for_month'             : str(lastmonth),
                            'debt_acc_no'           : 0,
                            'current_balance_total' : 0,
                            'ob_principal_total'    : 0,
                            'acc_arr'               : []
                        }

                        for key, value in mainProduct.items():
                            temp['debt_acc_' + key] = 0
                            temp['current_balance_' + key] = 0

                        if groupProduct['value'] == 'SIBS':
                            lead = ['JIVF00' + groupCell['lead']] if 'lead' in groupCell.keys() else []
                            member = ('JIVF00' + s for s in members)
                            officerIdRaw = list(lead) + list(member)
                            officerId = list(dict.fromkeys(officerIdRaw))
                            lnjc05Detail = list(mongodb.get(MONGO_COLLECTION=lnjc05_collection, WHERE={'group_id': debtGroupCell, "created_at": {'$gte': todayTimeStamp, '$lte' : endTodayTimeStamp} }))
                            for lnjc05 in lnjc05Detail:
                                partnerInfo = mongodb.getOne(MONGO_COLLECTION=cus_assigned_partner_collection, WHERE={'CONTRACTNR': lnjc05['account_number']})
                                if partnerInfo == None:
                                    zaccfInfo = mongodb.getOne(MONGO_COLLECTION=zaccf_collection, WHERE={'account_number': lnjc05['account_number']})
                                    temp['debt_acc_no']            += 1
                                    temp['current_balance_total']  += float(lnjc05['current_balance'])
                                    temp['ob_principal_total']     += float(lnjc05['outstanding_principal'])
                                    temp['acc_arr'].append(lnjc05['account_number'])
                                    if zaccfInfo is not None:
                                        temp['debt_acc_' + zaccfInfo['PRODGRP_ID']]        += 1
                                        temp['current_balance_' + zaccfInfo['PRODGRP_ID']] += float(lnjc05['current_balance'])

                        if groupProduct['value'] == 'Card':
                            lead = [groupCell['lead']] if 'lead' in groupCell.keys() else []
                            member = ( s for s in members)
                            officerIdRaw = list(lead) + list(member)
                            assign = list(dict.fromkeys(list(officerIdRaw)))
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
                                        "total_amt": {'$sum': '$cur_bal'},
                                        "total_acc": {'$sum': 1},
                                        "acc_arr": {'$push' : '$account_number'}
                                    }
                                }
                            ]
                            diallistDetail = mongodb.aggregate_pipeline(MONGO_COLLECTION=diallist_collection,aggregate_pipeline=aggregate_diallist)
                            if diallistDetail is not None:
                                for diallist in diallistDetail:
                                    temp['debt_acc_no']           = diallist['total_acc']
                                    temp['current_balance_total'] = diallist['total_amt']
                                    # temp['debt_acc_301']          = diallist['total_acc']
                                    # temp['current_balance_301']   = diallist['total_amt']
                                    temp['acc_arr']               = diallist['acc_arr']

                            aggregate_sbv = [
                                {
                                    "$match":
                                    {
                                        "contract_no": {'$in' : temp['acc_arr']}
                                    }
                                },{
                                    "$group":
                                    {
                                        "_id": 'null',
                                        "sale_total": {'$sum': '$ob_principal_sale'},
                                        "cash_total": {'$sum': '$ob_principal_cash'},
                                    }
                                }
                            ]
                            sbvInfo = mongodb.aggregate_pipeline(MONGO_COLLECTION=sbv_collection,aggregate_pipeline=aggregate_sbv)
                            if sbvInfo != None:
                                for sbv in sbvInfo:
                                    temp['ob_principal_total'] = sbv['sale_total'] + sbv['cash_total']


                            aggregate_diallist = [
                                {
                                    "$match":
                                    {
                                        "createdAt": {'$gte': todayTimeStamp, '$lte' : endTodayTimeStamp},
                                        "assign": {'$in' : assign},
                                        "group_id": debtGroupCell[0:1]+'-'+debtGroupCell[1:3]
                                    }
                                },{
                                       "$lookup":
                                       {
                                           "from": sbv_collection,
                                           "localField": "account_number",
                                           "foreignField": "contract_no",
                                           "as": "detail"
                                       }
                                }
                            ]
                            diallistDetail = mongodb.aggregate_pipeline(MONGO_COLLECTION=diallist_collection,aggregate_pipeline=aggregate_diallist)
                            if diallistDetail is not None:
                                for diallist in diallistDetail:
                                    card_type = 0
                                    for sbv in diallist['detail']:
                                        card_type = int(sbv['card_type'])
                                    if card_type > 1 and card_type <= 99:                                    
                                        temp['debt_acc_301']          += 1
                                        temp['current_balance_301']   += diallist['cur_bal']
                                    else:
                                        temp['debt_acc_302']          += 1
                                        temp['current_balance_302']   += diallist['cur_bal']

                        temp['created_at'] = time.time()
                        temp['created_by'] = 'system'
                        # pprint(temp)
                        # break
                        mongodb.insert(MONGO_COLLECTION=collection, insert_data=temp)






    # dueDayOfMonth = mongodb.getOne(MONGO_COLLECTION=report_due_date_collection, WHERE={'due_date_add_1': todayTimeStamp})
    # if dueDayOfMonth != None:
    #     groupInfo = mongodb.get(MONGO_COLLECTION=group_collection, WHERE={'name': {"$regex": 'WO'},'debt_groups' : {'$exists': 'true'}})
    #     if groupInfo is not None:
    #         for groupCell in groupInfo:
    #             temp = {
    #                 'due_date'              : 0,
    #                 'product'               : 'WO',
    #                 'debt_group'            : 'F',
    #                 'due_date_code'         : 1,
    #                 'team_id'               : str(groupCell['_id']),
    #                 'debt_acc_no'           : 0,
    #                 'current_balance_total' : 0,
    #                 'ob_principal_total'    : 0,
    #                 'acc_arr'               : []
    #             }
    #             count_wo = mongodb.count(MONGO_COLLECTION=common.getSubUser(subUserType, 'WO_monthly'))
    #             if count_wo != None or count_wo != 0:
    #                 aggregate_monthly = [
    #                     {
    #                         '$project':
    #                         {
    #                            'ACCTNO' : 1,
    #                            'created_at' : 1,
    #                            'WO9711': 1,
    #                            'pay_payment': {'$sum' : [ '$WO9711', '$WO9712' ,'$WO9713'] }
    #                         }
    #                     },{
    #                         "$group":
    #                         {
    #                             "_id": 'null',
    #                             "total_amt": {'$sum': '$pay_payment'},
    #                             "ob_principal_total": {'$sum': '$WO9711'},
    #                             "total_acc": {'$sum': 1},
    #                             "acc_arr": {'$push' : '$ACCTNO'},
    #                             "created_at": {'$last' : '$created_at'}
    #                         }
    #                     }
    #                 ]
    #                 woMonthly = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'WO_monthly'),aggregate_pipeline=aggregate_monthly)
    #                 if woMonthly != None:
    #                     for wo_row in woMonthly:
    #                         temp['due_date']                = wo_row['created_at']
    #                         temp['debt_acc_no']             = wo_row['total_acc']
    #                         temp['current_balance_total']   = wo_row['total_amt']
    #                         temp['ob_principal_total']      = wo_row['ob_principal_total']
    #                         temp['acc_arr']                 = wo_row['acc_arr']

    #                 for key, value in mainProduct.items():
    #                     temp['debt_acc_' + key] = 0
    #                     temp['current_balance_' + key] = 0


    #                 aggregate_payment_prod = [
    #                     {
    #                         '$project':
    #                         {
    #                             'PROD_ID' : 1,
    #                             'pay_payment': {'$sum' : [ '$WO9711', '$WO9712' ,'$WO9713'] },
    #                         }
    #                     },{
    #                         "$group":
    #                         {
    #                             "_id": '$PROD_ID',
    #                             "total_amt": {'$sum': '$pay_payment'},
    #                             "total_acc": {'$sum': 1},
    #                         }
    #                     }
    #                 ]
    #                 woMonthlyProd = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'WO_monthly'),aggregate_pipeline=aggregate_payment_prod)
    #                 if woMonthlyProd != None:
    #                     for woRowProd in woMonthlyProd:
    #                         temp['debt_acc_' + woRowProd['_id']]        = woRowProd['total_acc']
    #                         temp['current_balance_' + woRowProd['_id']] = float(woRowProd['total_amt'])

    #             else:
    #                 # wo all product
    #                 aggregate_all_prod = [
    #                     {
    #                         '$project':
    #                         {
    #                            'ACCTNO' : 1,
    #                            'created_at' : 1,
    #                            'WOAMT': 1,
    #                            'pay_payment': {'$sum' : [ '$OFF_OSTD', '$OFF_RECEIVE_INT' ,'$OFF_LATE_CHARGE'] }
    #                         }
    #                     },{
    #                         "$group":
    #                         {
    #                             "_id": 'null',
    #                             "total_amt": {'$sum': '$pay_payment'},
    #                             "ob_principal_total": {'$sum': '$WOAMT'},
    #                             "total_acc": {'$sum': 1},
    #                             "acc_arr": {'$push' : '$ACCTNO'},
    #                             "created_at": {'$last' : '$created_at'}
    #                         }
    #                     }
    #                 ]
    #                 woAllProd = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'Wo_all_prod'),aggregate_pipeline=aggregate_all_prod)
    #                 if woAllProd is not None:
    #                     for wo_row in woAllProd:
    #                         temp['due_date']                = wo_row['created_at']
    #                         temp['debt_acc_no']             = wo_row['total_acc']
    #                         temp['current_balance_total']   = wo_row['total_amt']
    #                         temp['ob_principal_total']      = wo_row['ob_principal_total']
    #                         temp['acc_arr']                 = wo_row['acc_arr']

    #                 for key, value in mainProduct.items():
    #                     temp['debt_acc_' + key] = 0
    #                     temp['current_balance_' + key] = 0


    #                 aggregate_each_prod = [
    #                     {
    #                         '$project':
    #                         {
    #                             'PRODUCT' : 1,
    #                             'pay_payment': {'$sum' : [ '$OFF_OSTD', '$OFF_RECEIVE_INT' ,'$OFF_LATE_CHARGE'] }
    #                         }
    #                     },{
    #                         "$group":
    #                         {
    #                             "_id": '$PRODUCT',
    #                             "total_amt": {'$sum': '$pay_payment'},
    #                             "total_acc": {'$sum': 1},
    #                         }
    #                     }
    #                 ]
    #                 woEachProd = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'Wo_all_prod'),aggregate_pipeline=aggregate_each_prod)
    #                 if woEachProd != None:
    #                     for woRowProd in woEachProd:
    #                         temp['debt_acc_' + woRowProd['_id']]        = woRowProd['total_acc']
    #                         temp['current_balance_' + woRowProd['_id']] = float(woRowProd['total_amt'])




    #             temp['created_at'] = time.time()
    #             temp['created_by'] = 'system'
    #             if month == 1:
    #                 lastmonth = 12
    #             else:
    #                 lastmonth = month - 1
    #             temp['for_month'] = str(lastmonth)
    #             mongodb.insert(MONGO_COLLECTION=collection, insert_data=temp)

    pprint("DONE")
except Exception as e:
    log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
    print(traceback.format_exc())

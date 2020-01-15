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
log = open(base_url + "cronjob/python/Loan/log/saveDailyProdWorkingDays.txt","a")
now = datetime.now()
subUserType = 'LO'
collection = common.getSubUser(subUserType, 'Daily_prod_working_days_report')
try:
    insertData = []
    updateData = []
    listDebtGroup = []
    
    # today = date.today()
    today = datetime.strptime('21/11/2019', "%d/%m/%Y").date()

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

    if int(month) != 1:
        oldMonth = int(month) - 1
    else:
        oldMonth = 12

    for debtGroupCell in list(listDebtGroup):
        if debtGroupCell[0:1] is not 'F':
            dueDayOfMonth = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_due_date'), WHERE={'for_month': str(month), 'debt_group': debtGroupCell[1:3]})
            

            for groupProduct in list(listGroupProduct):
                groupInfoByDueDate = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Group'), WHERE={'debt_groups': debtGroupCell, 'name': {"$regex": groupProduct['text'] + '.*'}})
                tempTotal = {
                    'col'           : 0,
                    'col_end_day'   : 0,
                    'paid_acc'      : 0,
                    'ratio_acc'     : 0,
                    'total_amt'     : 0,
                    'amt_end_day'   : 0,
                    'paid_amt'      : 0,
                    'ratio_amt'     : 0,
                    'pay_amt'       : 0,
                    'month'         : str(month),
                    'tar_acc'       : 0,
                    'tar_amt'       : 0,
                    'debt_group'    : debtGroupCell[0:1],
                    'due_date_code' : debtGroupCell[1:3],
                    'product'       : groupProduct['text'],
                    'team'          : 'TOTAL',
                    'team_id'       : 'total'
                }
                ytd = {
                    'amt_end_day'   : 0,
                    'col_end_day'   : 0
                }
                tempOldTotal = {
                    'col'           : 0,
                    'col_end_day'   : 0,
                    'paid_acc'      : 0,
                    'ratio_acc'     : 0,
                    'total_amt'     : 0,
                    'amt_end_day'   : 0,
                    'paid_amt'      : 0,
                    'ratio_amt'     : 0,
                    'pay_amt'       : 0,
                    'month'         : str(oldMonth),
                    'tar_acc'       : 0,
                    'tar_amt'       : 0,
                    'debt_group'    : debtGroupCell[0:1],
                    'due_date_code' : debtGroupCell[1:3],
                    'product'       : groupProduct['text'],
                    'team'          : 'TOTAL',
                    'team_id'       : 'total'
                }
                checkOldReport = 'false'
                for groupCell in list(groupInfoByDueDate):
                    temp = {
                        'col'           : 0,
                        'col_end_day'   : 0,
                        'paid_acc'      : 0,
                        'ratio_acc'     : 0,
                        'total_amt'     : 0,
                        'amt_end_day'   : 0,
                        'paid_amt'      : 0,
                        'ratio_amt'     : 0,
                        'pay_amt'       : 0,
                        'month'         : str(month),
                        'tar_acc'       : 0,
                        'tar_amt'       : 0,
                    }
                    # temp ={}
                    if todayTimeStamp < dueDayOfMonth['due_date_add_1']:
                        dueDayLastMonth = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_due_date'), WHERE={'for_month': str(month - 1), 'debt_group': debtGroupCell[1:3]})
                        temp['due_date'] = dueDayLastMonth['due_date'] if dueDayLastMonth is not None else ''
                        #Lay gia tri no vao ngay due date + 1#
                        # incidenceInfo = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Due_date_next_date'), WHERE={'for_month': str(month - 1), 'team_id': str(groupCell['_id'])})
                        #Lay gia tri no vao ngay due date + 1#
                    else:
                        # incidenceInfo = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Due_date_next_date'), WHERE={'for_month': str(month), 'team_id': str(groupCell['_id'])})
                        temp['due_date'] = dueDayOfMonth['due_date']

                    temp['debt_group']      = debtGroupCell[0:1]
                    temp['due_date_code']   = debtGroupCell[1:3]
                    temp['product']         = groupProduct['text']
                    temp['team']            = groupCell['name']
                    temp['team_id']         = str(groupCell['_id'])
                    
                    yesterdayReportData = mongodb.get(MONGO_COLLECTION=collection, WHERE={'team_id': str(groupCell['_id']),'due_date_code':debtGroupCell[1:3],'month': str(month)},SORT=[('createdAt',-1)],SKIP=0,TAKE=1)
                    #print(yesterdayReportData)
                    if yesterdayReportData != None:
                        for yesterday in yesterdayReportData:
                            temp['tar_acc'] = yesterday['tar_acc']
                            temp['tar_amt'] = yesterday['tar_amt']
                    else:
                        # diallist = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Diallist'), WHERE={'group_id':str(groupCell['_id']),'createdAt':{'$gte':temp['due_date']}})
                        diallist = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Diallist'), WHERE={'group_id':str(groupCell['_id'])})
                        if diallist != None:
                            temp['target'] = diallist['target']
                            temp['tar_acc'] = (diallist['target']*diallist['count_detail'])/100 if 'count_detail' in diallist.keys() else 0
                            if groupProduct['text'] == 'SIBS':
                                aggregate_pipeline = [
                                    {
                                        "$match":
                                        {
                                            "diallist_id": ObjectId(diallist['_id']),
                                        }
                                    },{
                                        "$group":
                                        {
                                            "_id": 'null',
                                            "total_amt": {'$sum': '$current_balance'},
                                        }
                                    }
                                ]
                            else:
                                aggregate_pipeline = [
                                    {
                                        "$match":
                                        {
                                            "diallist_id": ObjectId(diallist['_id']),
                                        }
                                    },{
                                        "$group":
                                        {
                                            "_id": 'null',
                                            "total_amt": {'$sum': '$cur_bal'},
                                        }
                                    }
                                ]
                            diallist_detail =  mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'Diallist_detail'),aggregate_pipeline=aggregate_pipeline)
                            for detail in diallist_detail:
                                temp['tar_amt'] = (diallist['target']*detail['total_amt'])/100

                        # oldReport
                        oldYesterdayReportData = mongodb.get(MONGO_COLLECTION=collection, WHERE={'team_id': str(groupCell['_id']),'due_date_code':debtGroupCell[1:3],'month': str(oldMonth)},SORT=[('createdAt',-1)],SKIP=0,TAKE=1)
                        if oldYesterdayReportData != None:
                            checkOldReport = 'true'
                            for oldReport in oldYesterdayReportData:
                                temp_old = {
                                    'col'           : 0,
                                    'col_end_day'   : 0,
                                    'paid_acc'      : 0,
                                    'ratio_acc'     : 0,
                                    'total_amt'     : 0,
                                    'amt_end_day'   : 0,
                                    'paid_amt'      : 0,
                                    'ratio_amt'     : 0,
                                    'pay_amt'       : 0,
                                    'month'         : str(oldMonth),
                                    'tar_acc'       : oldReport['tar_acc'],
                                    'tar_amt'       : oldReport['tar_amt'],
                                    'debt_group'    : oldReport['debt_group'],
                                    'due_date_code' : oldReport['due_date_code'],
                                    'product'       : oldReport['product'],
                                    'team'          : oldReport['team'],
                                    'team_id'       : oldReport['team_id']
                                }
                                if groupProduct['value'] == 'SIBS':
                                    aggregate_jc05_old = [
                                        {
                                            "$match":
                                            {
                                                "account_number": {'$in' : oldReport['acc_arr']}
                                            }
                                        },{
                                            "$group":
                                            {
                                                "_id": 'null',
                                                "total_amt": {'$sum': '$current_balance'},
                                                "total_acc": {'$sum': 1},
                                            }
                                        }
                                    ]
                                    lnjc05Old = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'LNJC05'),aggregate_pipeline=aggregate_jc05_old)
                                    for lnjc05 in lnjc05Old:
                                        temp_old['col']         = lnjc05['total_acc']
                                        temp_old['total_amt']   = lnjc05['total_amt']
                                        

                                temp_old['paid_acc']     = temp_old['tar_acc'] - temp_old['col']
                                temp_old['ratio_acc']    = 1 - (temp_old['paid_acc']/temp_old['tar_acc'] if temp_old['tar_acc'] != 0 else 0)
                                temp_old['paid_amt']     = temp_old['tar_amt'] - temp_old['total_amt']
                                temp_old['ratio_amt']    = 1 - (temp_old['paid_amt']/temp_old['tar_amt'] if temp_old['tar_amt'] != 0 else 0)

                                oldReport['col_end_day']  = oldReport['col'] - temp_old['col']
                                oldReport['amt_end_day']  = oldReport['total_amt'] - temp_old['total_amt']
                                mongodb.update(MONGO_COLLECTION=collection, WHERE={'_id': ObjectId(oldReport['_id'])},VALUE=oldReport)
                                
                                oldReportData = mongodb.get(MONGO_COLLECTION=collection, WHERE={'team_id': str(groupCell['_id']),'due_date_code':debtGroupCell[1:3],'month': str(oldMonth)})
                                if oldReportData != None:
                                    for oldReportDt in oldReportData:
                                        temp_old['pay_amt'] += oldReportDt['pay_amt']

                                temp_old['createdAt'] = time.time()
                                temp_old['createdBy'] = 'system'
                                mongodb.insert(MONGO_COLLECTION=collection, insert_data=temp_old)

                                tempOldTotal['tar_acc']    += temp_old['tar_acc']
                                tempOldTotal['tar_amt']    += temp_old['tar_amt']
                                tempOldTotal['col']        += temp_old['col']
                                tempOldTotal['total_amt']  += temp_old['total_amt']
                                tempOldTotal['paid_acc']   += temp_old['paid_acc']
                                tempOldTotal['paid_amt']   += temp_old['paid_amt']
                                tempOldTotal['pay_amt']    += temp_old['pay_amt']


                    
                    if groupProduct['value'] == 'SIBS':
                        lead = ['JIVF00' + groupCell['lead']] if 'lead' in groupCell.keys() else []
                        member = ('JIVF00' + s for s in groupCell['members']) if 'members' in groupCell.keys() else []
                        officerIdRaw = list(lead) + list(member)
                        officerId = list(dict.fromkeys(officerIdRaw))
                        aggregate_jc05 = [
                            {
                                "$match":
                                {
                                    "group_id": debtGroupCell,
                                    "officer_id": {'$in' : officerId}
                                }
                            },{
                                "$group":
                                {
                                    "_id": 'null',
                                    "total_amt": {'$sum': '$current_balance'},
                                    "total_acc": {'$sum': 1},
                                    "acc_arr": {'$push' : '$account_number'}
                                }
                            }
                        ]
                        lnjc05Info = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'LNJC05'),aggregate_pipeline=aggregate_jc05)
                        for lnjc05 in lnjc05Info:
                            temp['col']         = lnjc05['total_acc']
                            temp['total_amt']   = lnjc05['total_amt']
                            temp['acc_arr']     = lnjc05['acc_arr']

                        temp['paid_acc']     = temp['tar_acc'] - temp['col']
                        temp['ratio_acc']    = temp['paid_acc']/temp['tar_acc'] if temp['tar_acc'] != 0 else 0
                        temp['paid_amt']     = temp['tar_amt'] - temp['total_amt']
                        temp['ratio_amt']    = temp['paid_amt']/temp['tar_amt'] if temp['tar_amt'] != 0 else 0

                        if yesterdayReportData != None:
                            for yesterday in yesterdayReportData:
                                yesterday['col_end_day']  = yesterday['col'] - temp['col']
                                yesterday['amt_end_day']  = yesterday['total_amt'] - temp['total_amt']

                                ytd['col_end_day'] += yesterday['col_end_day']
                                ytd['amt_end_day'] += yesterday['amt_end_day']
                                mongodb.update(MONGO_COLLECTION=collection, WHERE={'_id': ObjectId(yesterday['_id'])},VALUE=yesterday)
                                aggregate_3206 = [
                                    {
                                        "$match":
                                        {
                                            "account_number": {'$in' : yesterday['acc_arr']}
                                        }
                                    },{
                                        "$group":
                                        {
                                            "_id": 'null',
                                            "pay_amt": {'$sum': '$amt'},
                                        }
                                    }
                                ]
                                ln3206fInfo = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'LN3206F'),aggregate_pipeline=aggregate_3206)
                                for ln3206 in ln3206fInfo:
                                    temp['pay_amt']         = lnjc05['pay_amt']

                        tempTotal['tar_acc']    += temp['tar_acc']
                        tempTotal['tar_amt']    += temp['tar_amt']
                        tempTotal['col']        += temp['col']
                        tempTotal['total_amt']  += temp['total_amt']
                        tempTotal['paid_acc']   += temp['paid_acc']
                        tempTotal['paid_amt']   += temp['paid_amt']
                        tempTotal['pay_amt']    += temp['pay_amt']
                            
                    temp['createdAt'] = time.time()
                    temp['createdBy'] = 'system'
                    insertData.append(temp)
                    # print(repr(temp))
                    # mongodb.insert(MONGO_COLLECTION=collection, insert_data=temp)

                tempTotal['ratio_acc']   = tempTotal['paid_acc']/tempTotal['tar_acc'] if tempTotal['tar_acc'] != 0 else 0
                tempTotal['ratio_amt']   = tempTotal['paid_amt']/tempTotal['tar_amt'] if tempTotal['tar_amt'] != 0 else 0
                insertData.append(tempTotal)
                yesterdayTotalData = mongodb.get(MONGO_COLLECTION=collection, WHERE={'team_id': 'total','product': groupProduct['text'],'due_date_code':debtGroupCell[1:3],'month': str(month)},SORT=[('createdAt',-1)],SKIP=0,TAKE=1)
                if yesterdayTotalData != None:
                    for yesterdayTotal in yesterdayTotalData: 
                        mongodb.update(MONGO_COLLECTION=collection, WHERE={'_id': ObjectId(yesterdayTotal['_id'])},VALUE=ytd)

                if checkOldReport == 'true':
                    tempOldTotal['col_end_day'] = tempOldTotal['col']
                    tempOldTotal['amt_end_day'] = tempOldTotal['amt_end_day']
                    tempOldTotal['ratio_acc']   = tempOldTotal['paid_acc']/tempOldTotal['tar_acc'] if tempOldTotal['tar_acc'] != 0 else 0
                    tempOldTotal['ratio_amt']   = tempOldTotal['paid_amt']/tempOldTotal['tar_amt'] if tempOldTotal['tar_amt'] != 0 else 0
                    mongodb.insert(MONGO_COLLECTION=collection, insert_data=tempOldTotal)
                # break    
            # break
        # break 
                  
    print(insertData)
    # for groupProduct in list(listGroupProduct):
    #     if groupProduct == 'SIBS':
    #         for debtGroupCell in list(listDebtGroup):
    #             dueDayOfMonth = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_due_date'), WHERE={'for_month': str(month), 'debt_group': debtGroupCell[1:3]})
    #             groupInfoByDueDate = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Group'), WHERE={'debt_groups': debtGroupCell, 'name': {"$regex": groupProduct['text'] + '.*'}})
    #             for groupCell in list(groupInfoByDueDate):
    #                 temp = {}
    #                 if todayTimeStamp < dueDayOfMonth['due_date_add_1']:
    #                     dueDayLastMonth = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_due_date'), WHERE={'for_month': str(month - 1), 'debt_group': debtGroupCell[1:3]})
    #                     temp['due_date'] = dueDayLastMonth['due_date'] if dueDayLastMonth is not None else ''
    #                     pprint(dueDayLastMonth)
    #                 else:
    #                     temp['due_date'] = dueDayOfMonth['due_date']
    #                 temp['debt_group'] = debtGroupCell[0:1]
    #                 temp['due_date_code'] = debtGroupCell[1:3]
    #                 temp['product'] = groupProduct['text']
    #                 temp['team'] = groupCell['name']
    #                 temp['team_id'] = str(groupCell['_id'])
    #                 temp['inci'] = 0
    #                 temp['inci_amt'] = 0
    #                 temp['col'] = 0
    #                 temp['col_amt'] = 0
    #                 temp['today_rem'] = 0
    #                 temp['today_rem_amt'] = 0

    #                 for key, value in mainProduct.items():
    #                     temp['inci_' + key] = 0
    #                     temp['inci_amt_' + key] = 0
    #                     temp['col_' + key] = 0
    #                     temp['col_amt_' + key] = 0
    #                     temp['today_rem_' + key] = 0
    #                     temp['today_rem_amt_' + key] = 0

    #                 lead = ['JIVF00' + groupCell['lead']] if 'lead' in groupCell.keys() else []
    #                 member = ('JIVF00' + s for s in groupCell['members']) if 'members' in groupCell.keys() else []
    #                 officerIdRaw = list(lead) + list(member)
    #                 officerId = list(dict.fromkeys(officerIdRaw))

    #                 lnjc05Info = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'LNJC05'), WHERE={'createdAt': {'$gte': starttime, '$lte': endtime}, 'group_id': debtGroupCell, 'officer_id': {'$in': officerId}})
    #                 lnjc05InfoYesterday = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'LNJC05'), WHERE={'createdAt': {'$gte': (starttime - 86400), '$lte': (endtime - 86400)}, 'group_id': debtGroupCell, 'officer_id': officerId})
    #                 lnjc05InfoYesterday = dict(lnjc05InfoYesterday)               
    #                 for lnjc05 in lnjc05Info:
    #                     zaccfInfo = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'ZACCF'), WHERE={'account_number': lnjc05['account_number']})
    #                     if zaccfInfo is not None:
    #                         temp['today_rem'] += 1
    #                         temp['today_rem_amt'] += float(lnjc05['current_balance'])
    #                         temp['today_rem_' + zaccfInfo['PRODGRP_ID']] += 1
    #                         temp['today_rem_amt_' + zaccfInfo['PRODGRP_ID']] += float(lnjc05['current_balance'])
                            
    #                 if todayTimeStamp == dueDayOfMonth['due_date_add_1']:
    #                     temp['inci'] = temp['today_rem']
    #                     temp['inci_amt'] = temp['today_rem_amt']
    #                     temp['col'] = temp['inci'] - temp['today_rem']
    #                     temp['col_amt'] = temp['inci_amt'] - temp['today_rem_amt']
    #                     for key, value in mainProduct.items():
    #                         temp['inci_' + key] = temp['today_rem_' + key]
    #                         temp['inci_amt_' + key] = temp['today_rem_amt_' + key]
    #                         temp['col_' + key] = temp['inci_' + key] - temp['today_rem_' + key]
    #                         temp['col_amt_' + key] = temp['inci_amt_' + key] - temp['today_rem_amt_' + key]
    #                 else:
    #                     temp['inci'] = lnjc05InfoYesterday['inci'] if 'inci' in lnjc05InfoYesterday.keys() else 0
    #                     temp['inci_amt'] = lnjc05InfoYesterday['inci_amt'] if 'inci_amt' in lnjc05InfoYesterday.keys() else 0
    #                     temp['col'] = temp['inci'] - temp['today_rem']
    #                     temp['col_amt'] = temp['inci_amt'] - temp['today_rem_amt']
    #                     for key, value in mainProduct.items():
    #                         temp['inci_' + key] = lnjc05InfoYesterday['inci_' + key] if ('inci_' + key) in lnjc05InfoYesterday.keys() else 0
    #                         temp['inci_amt_' + key] = lnjc05InfoYesterday['inci_amt_' + key] if ('inci_amt_' + key) in lnjc05InfoYesterday.keys() else 0
    #                         temp['col_' + key] = temp['inci_' + key] - temp['today_rem_' + key]
    #                         temp['col_amt_' + key] = temp['inci_amt_' + key] - temp['today_rem_amt_' + key]
                    
    #                 temp['flow_rate'] = temp['today_rem'] / temp['inci'] if temp['inci'] != 0 else 0
    #                 temp['col_rate'] = temp['today_rem'] / temp['col'] if temp['col'] != 0 else 0
    #                 temp['flow_rate_amt'] = temp['today_rem_amt'] / temp['inci_amt'] if temp['inci_amt'] != 0 else 0
    #                 temp['col_rate_amt'] = temp['today_rem_amt'] / temp['col_amt'] if temp['col_amt'] != 0 else 0
    #                 temp['created_at'] = todayTimeStamp
    #                 temp['created_by'] = 'system'
    #                 pprint(temp)
                    # mongodb.insert(MONGO_COLLECTION=collection, insert_data=temp)
                    # log.write(json.dumps(temp))
                    # log.write('\n')
                    # log.write('\n')
                    
        
        # else:
        #     listAcc = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Account'), WHERE={'updated_at': {'$gte': starttime, '$lte': endtime}})
        #     for acc in listAcc:
        #         pprint(acc)

    # tempWO = {
    #     'debt_group'        : 'F',
    #     'due_date_code'     : '',
    #     'product'           : '',
    #     'team'              : '',
    #     'team_id'           : ''
    # }
    
    # if todayTimeStamp < dueDayOfMonth['due_date_add_1']:
    #     dueDayLastMonth = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_due_date'), WHERE={'for_month': str(month - 1), 'debt_group': debtGroupCell[1:3]})
    #     tempWO['due_date'] = dueDayLastMonth['due_date'] if dueDayLastMonth is not None else ''
    # else:
    #     tempWO['due_date'] = dueDayOfMonth['due_date']

    # woMonthlyInfo = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'WO_monthly'), WHERE={'created_at': {'$gte': starttime, '$lte': endtime}})
    # temp['inci'] = len(list(woMonthlyInfo))


    # listAccount = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Account'), WHERE={'overdue': todayString})
    # for account in listAccount:
    #     temp = {}
    #     groupInfo = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Group_card'), WHERE={'account_no': account['account_number']})
    if len(insertData) > 0:
        mongodb.batch_insert(MONGO_COLLECTION=collection, insert_data=insertData)   
        # pprint(dict(groupInfo)
except Exception as e:
    # log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
    pprint(str(e))
        
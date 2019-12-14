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
collection = common.getSubUser(subUserType, 'Daily_all_user_report')
log = open(base_url + "cronjob/python/Loan/log/dailyProdAllUser_log.txt","a")
now = datetime.now()
log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': Start Import' + '\n')
try:
    insertData = []
    insertDataTotal = []
    listDebtGroup = []

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

    if todayTimeStamp in listHoliday or weekday == 6:
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

    users = _mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'User'), SELECT=['extension','agentname'],SORT=([('_id', -1)]),SKIP=0, TAKE=500)

    checkGroupA = 'false'
    for debtGroupCell in list(listDebtGroup):
        if debtGroupCell[0:1] == 'A' and checkGroupA == 'false':
            i = 1
            dueDayOfMonth = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_due_date'), WHERE={'for_month': str(month), 'debt_group': debtGroupCell[1:3]})
            for groupProduct in list(listGroupProduct):
                teams = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Group'), WHERE={'name' : {'$regex' : groupProduct['value']} , 'debt_groups': debtGroupCell[0:3]})
                if teams != None:
                    for team in teams:
                        temp = {
                            'name'           : team['name'],
                            'group'          : debtGroupCell[0:1],
                            'team'           : i,
                            'date'           : todayTimeStamp,
                            'extension'      : team['lead'],
                            'team_lead'      : 'true',
                            'count_data'     : 0,
                            'unwork'            : 0,
                            'talk_time'         : 0,
                            'total_call'        : 0,
                            'total_amount'      : 0,
                            'count_spin'        : 0,
                            'spin_amount'       : 0,
                            'count_conn'        : 0,
                            'conn_amount'       : 0,
                            'count_paid'        : 0,
                            'paid_amount'       : 0,
                            'ptp_amount'        : 0,
                            'count_ptp'         : 0,
                            'paid_amount_promise'       : 0,
                            'count_paid_promise'        : 0,
                        }

                        if todayTimeStamp < dueDayOfMonth['due_date_add_1']:
                            dueDayLastMonth = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_due_date'), WHERE={'for_month': str(month - 1), 'debt_group': debtGroupCell[1:3]})
                            temp['due_date'] = dueDayLastMonth['due_date'] if dueDayLastMonth is not None else ''
                            #Lay gia tri no vao ngay due date + 1#
                            incidenceInfo = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Due_date_next_date'), WHERE={'for_month': str(month - 1),  'debt_group': debtGroupCell[0:1],'due_date_code': debtGroupCell[1:3],'product':groupProduct['text']})
                            #Lay gia tri no vao ngay due date + 1#
                        else:
                            temp['due_date'] = dueDayOfMonth['due_date']
                            incidenceInfo = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Due_date_next_date'), WHERE={'for_month': str(month), 'debt_group': debtGroupCell[0:1],'due_date_code': debtGroupCell[1:3],'product':groupProduct['text']})

                        acc_arr = []
                        if incidenceInfo is not None:
                            for inc in incidenceInfo:
                                temp['count_data'] += inc['debt_acc_no']
                                # temp['inci_amt'] += inc['current_balance_total']
                                # temp['inci_ob_principal'] += inc['ob_principal_total']
                                acc_arr_1 = inc['acc_arr'] if 'acc_arr' in inc.keys() else []
                                acc_arr += acc_arr_1

                        aggregate_unwork = [
                            {
                                "$match":
                                {
                                    "account_number": {'$gte': temp['due_date']},
                                    "userextension": team['lead'],
                                    "disposition" : {'$ne' : 'ANSWERED'}
                                }
                            },{
                                "$group":
                                {
                                    "_id": 'null',
                                    "count_unwork": {'$sum': 1},
                                    # "phone_ans_arr": {'$addToSet': '$customernumber'},
                                }
                            }
                        ]
                        unworkData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'worldfonepbxmanager'),aggregate_pipeline=aggregate_unwork)
                        if unworkData != None:
                            for row in unworkData:
                                temp['unwork']            = row['count_unwork']


                        aggregate_ptp = [
                            {
                                "$match":
                                {
                                    "created_at": {'$gte': temp['due_date']},
                                    "account_number": {'$in': acc_arr},
                                    '$or' : [ { 'action_code' :  'BPTP'}, {'action_code' :  'PTP Today'}]
                                }
                            },{
                                "$group":
                                {
                                    "_id": 'null',
                                    "acc_arr": {'$addToSet': '$account_number'},
                                }
                            }
                        ]
                        ptpData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'Action_code'),aggregate_pipeline=aggregate_ptp)
                        account_ptp_arr = []
                        if ptpData != None:
                            for row in ptpData:
                                account_ptp_arr           = row['acc_arr']

                        
                        if groupProduct['value'] == 'SIBS':
                            aggregate_ptp_amt = [
                                {
                                    "$match":
                                    {
                                        "account_number": {'$in': account_ptp_arr},
                                    }
                                },{
                                    "$group":
                                    {
                                        "_id": 'null',
                                        "ptp_amount": {'$sum': '$current_balance'},
                                    }
                                }
                            ]
                            ptpAmtData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'LNJC05'),aggregate_pipeline=aggregate_ptp_amt)
                            if ptpAmtData != None:
                                for row in ptpAmtData:
                                    temp['count_ptp']            = len(account_ptp_arr)
                                    temp['ptp_amount']           = row['ptp_amount']

                            aggregate_paid_promise = [
                                {
                                    "$match":
                                    {
                                        "created_at": {'$gte': temp['due_date']},
                                        "account_number": {'$in': account_ptp_arr},
                                    }
                                },{
                                    "$group":
                                    {
                                        "_id": 'null',
                                        "paid_amount_promise": {'$sum': '$amt'},
                                        "count_paid_promise": {'$addToSet': '$account_number'},
                                    }
                                }
                            ]
                            paidPromiseData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'LN3206F'),aggregate_pipeline=aggregate_paid_promise)
                            if paidPromiseData != None:
                                for row in paidPromiseData:
                                    temp['count_paid_promise']            = len(row['count_paid_promise'] )
                                    temp['paid_amount_promise']           = row['paid_amount_promise']     

                            # paid
                            aggregate_paid = [
                                {
                                    "$match":
                                    {
                                        "created_at": {'$gte': temp['due_date']},
                                        "account_number": {'$in': acc_arr},
                                    }
                                },{
                                    "$group":
                                    {
                                        "_id": 'null',
                                        "paid_amount": {'$sum': '$amt'},
                                        "count_paid": {'$addToSet': '$account_number'},
                                    }
                                }
                            ]
                            paidPromiseData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'LN3206F'),aggregate_pipeline=aggregate_paid)
                            if paidPromiseData != None:
                                for row in paidPromiseData:
                                    temp['count_paid']            = len(row['count_paid'] )
                                    temp['paid_amount']           = row['paid_amount']     


                        if groupProduct['value'] == 'Card':
                            aggregate_ptp_amt = [
                                {
                                    "$match":
                                    {
                                        "account_number": {'$in': account_ptp_arr},
                                    }
                                },{
                                    "$group":
                                    {
                                        "_id": 'null',
                                        "ptp_amount": {'$sum': '$cur_bal'},
                                    }
                                }
                            ]
                            ptpAmtData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'List_of_account_in_collection'),aggregate_pipeline=aggregate_ptp_amt)
                            if ptpAmtData != None:
                                for row in ptpAmtData:
                                    temp['count_ptp']            = len(account_ptp_arr)
                                    temp['ptp_amount']           = row['ptp_amount']

                            aggregate_paid_promise = [
                                {
                                    "$match":
                                    {
                                        "created_at": {'$gte': temp['due_date']},
                                        "account_number": {'$in': account_ptp_arr},
                                    }
                                },{
                                    "$group":
                                    {
                                        "_id": 'null',
                                        "paid_amount_promise": {'$sum': '$amount'},
                                        "count_paid_promise": {'$addToSet': '$account_number'},
                                    }
                                }
                            ]
                            paidPromiseData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_input_payment_of_card'),aggregate_pipeline=aggregate_paid_promise)
                            if paidPromiseData != None:
                                for row in paidPromiseData:
                                    temp['count_paid_promise']            = len(row['count_paid_promise'] )
                                    temp['paid_amount_promise']           = row['paid_amount_promise']    

                            # paid
                            aggregate_paid = [
                                {
                                    "$match":
                                    {
                                        "created_at": {'$gte': temp['due_date']},
                                        "account_number": {'$in': acc_arr},
                                    }
                                },{
                                    "$group":
                                    {
                                        "_id": 'null',
                                        "paid_amount": {'$sum': '$amount'},
                                        "count_paid": {'$addToSet': '$account_number'},
                                    }
                                }
                            ]
                            paidPromiseData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_input_payment_of_card'),aggregate_pipeline=aggregate_paid)
                            if paidPromiseData != None:
                                for row in paidPromiseData:
                                    temp['count_paid']            = len(row['count_paid'] )
                                    temp['paid_amount']           = row['paid_amount']     


                        
                        # members
                        member_arr = []
                        count_member = len(team['members'])
                        for member in list(team['members']):
                            temp_member = {
                                'name'           : '',
                                'group'          : debtGroupCell[0:1],
                                'team'           : i,
                                'date'           : todayTimeStamp,
                                'extension'      : member,
                                'count_data'     : 0,
                                'unwork'            : 0,
                                'talk_time'         : 0,
                                'total_call'        : 0,
                                'total_amount'      : 0,
                                'count_spin'        : 0,
                                'spin_amount'       : 0,
                                'count_conn'        : 0,
                                'conn_amount'       : 0,
                                'count_paid'        : 0,
                                'paid_amount'       : 0,
                                'ptp_amount'        : 0,
                                'count_ptp'         : 0,
                                'paid_amount_promise'       : 0,
                                'count_paid_promise'        : 0,
                            }
                            users = _mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'User'),WHERE={'extension': str(member)}, SELECT=['extension','agentname'])
                            if users != None:
                                temp_member['name'] = users['agentname']
                            
                            aggregate_cdr = [
                                {
                                    "$match":
                                    {
                                        "starttime": {'$gte': temp['due_date']},
                                        "userextension": member,
                                    }
                                },{
                                    "$group":
                                    {
                                        "_id": 'null',
                                        "talk_time": {'$sum': '$billduration'},
                                        "total_call": {'$sum': 1},
                                        "customernumber_arr": {'$addToSet': '$customernumber'},
                                        "phone_arr": {'$push': '$customernumber'},
                                    }
                                }
                            ]
                            cdrData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'worldfonepbxmanager'),aggregate_pipeline=aggregate_cdr)
                            customernumber_arr = []
                            phone_arr = []
                            disposition_arr = []
                            if cdrData != None:
                                for row in cdrData:
                                    temp_member['talk_time']            = row['talk_time']
                                    temp_member['total_call']           = row['total_call'] 
                                    phone_arr                           = row['phone_arr'] 

                            # connected
                            aggregate_cdr_ans = [
                                {
                                    "$match":
                                    {
                                        "starttime": {'$gte': temp['due_date']},
                                        "userextension": member,
                                        "disposition" : 'ANSWERED'
                                    }
                                },{
                                    "$group":
                                    {
                                        "_id": 'null',
                                        "count_conn": {'$sum': 1},
                                        "phone_ans_arr": {'$addToSet': '$customernumber'},
                                    }
                                }
                            ]
                            cdrAnsData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'worldfonepbxmanager'),aggregate_pipeline=aggregate_cdr_ans)
                            phone_ans_arr = []
                            if cdrAnsData != None:
                                for row in cdrAnsData:
                                    phone_ans_arr            = row['phone_ans_arr']
                                    temp_member['count_conn'] = row['count_conn']


                            if groupProduct['value'] == 'SIBS':
                                aggregate_cdr_amt = [
                                    {
                                        "$match":
                                        {
                                            "mobile_num": {'$in': customernumber_arr},
                                        }
                                    },{
                                        "$group":
                                        {
                                            "_id": 'null',
                                            "total_amount": {'$sum': '$current_balance'},

                                        }
                                    }
                                ]
                                cdrAmountData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'LNJC05'),aggregate_pipeline=aggregate_cdr_amt)
                                if cdrAmountData != None:
                                    for row in cdrAmountData:
                                        temp_member['total_amount']            = row['total_amount']

                                # spin
                                count_spin = 0
                                phone_spin_arr = []
                                for phone in phone_arr:
                                    if phone_arr.count(phone) > 1:
                                        phone_spin_arr.append(phone)
                                        count_spin += 1

                                aggregate_spin = [
                                    {
                                        "$match":
                                        {
                                            "mobile_num": {'$in': phone_spin_arr},
                                        }
                                    },{
                                        "$group":
                                        {
                                            "_id": 'null',
                                            "spin_amount": {'$sum': '$current_balance'},

                                        }
                                    }
                                ]
                                spinData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'LNJC05'),aggregate_pipeline=aggregate_spin)
                                if spinData != None:
                                    for row in spinData:
                                        temp_member['spin_amount']            = row['spin_amount']
                                        temp_member['count_spin']             = count_spin

                                aggregate_amt_ans = [
                                    {
                                        "$match":
                                        {
                                            "mobile_num": {'$in': phone_ans_arr},
                                        }
                                    },{
                                        "$group":
                                        {
                                            "_id": 'null',
                                            "conn_amount": {'$sum': '$current_balance'},
                                        }
                                    }
                                ]
                                amtAnsData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'LNJC05'),aggregate_pipeline=aggregate_amt_ans)
                                if amtAnsData != None:
                                    for row in amtAnsData:
                                        temp_member['conn_amount']            = row['conn_amount']


                            if groupProduct['value'] == 'Card':
                                aggregate_cdr_amt = [
                                    {
                                        "$match":
                                        {
                                            "phone": {'$in': customernumber_arr},
                                        }
                                    },{
                                        "$group":
                                        {
                                            "_id": 'null',
                                            "total_amount": {'$sum': '$cur_bal'},

                                        }
                                    }
                                ]
                                cdrAmountData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'List_of_account_in_collection'),aggregate_pipeline=aggregate_cdr_amt)
                                if cdrAmountData != None:
                                    for row in cdrAmountData:
                                        temp_member['total_amount']            = row['total_amount']

                                # spin
                                count_spin = 0
                                phone_spin_arr = []
                                for phone in phone_arr:
                                    if phone_arr.count(phone) > 1:
                                        phone_spin_arr.append(phone)
                                        count_spin += 1

                                aggregate_spin = [
                                    {
                                        "$match":
                                        {
                                            "phone": {'$in': phone_spin_arr},
                                        }
                                    },{
                                        "$group":
                                        {
                                            "_id": 'null',
                                            "spin_amount": {'$sum': '$cur_bal'},

                                        }
                                    }
                                ]
                                spinData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'List_of_account_in_collection'),aggregate_pipeline=aggregate_spin)
                                if spinData != None:
                                    for row in spinData:
                                        temp_member['spin_amount']            = row['spin_amount']
                                        temp_member['count_spin']             = count_spin

                                aggregate_amt_ans = [
                                    {
                                        "$match":
                                        {
                                            "phone": {'$in': phone_ans_arr},
                                        }
                                    },{
                                        "$group":
                                        {
                                            "_id": 'null',
                                            "conn_amount": {'$sum': '$cur_bal'},
                                        }
                                    }
                                ]
                                amtAnsData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'List_of_account_in_collection'),aggregate_pipeline=aggregate_amt_ans)
                                if amtAnsData != None:
                                    for row in amtAnsData:
                                        temp_member['conn_amount']            = row['conn_amount']



                            temp['talk_time']      += temp_member['talk_time'];
                            temp['total_call']     += temp_member['total_call'];
                            temp['total_amount']   += temp_member['total_amount'];
                            temp['conn_amount']    += temp_member['conn_amount'];
                            temp['count_conn']     += temp_member['count_conn'];
                            temp['spin_amount']    += temp_member['spin_amount'];
                            temp['count_spin']     += temp_member['count_spin'];

                            temp_member['count_data']   = temp['count_data']/count_member
                            temp_member['unwork']       = temp['unwork']/count_member
                            temp_member['count_data']    = temp['count_data']/count_member
                            temp_member['count_paid']    = temp['count_paid']/count_member
                            temp_member['paid_amount']   = temp['paid_amount']/count_member
                            temp_member['count_ptp']     = temp['count_ptp']/count_member
                            temp_member['ptp_amount']    = temp['ptp_amount']/count_member
                            temp_member['count_paid_promise']       = temp['count_paid_promise']/count_member
                            temp_member['paid_amount_promise']      = temp['paid_amount_promise']/count_member

                            temp_member['spin_rate']     = temp_member['count_spin']/temp_member['total_call'] if temp_member['total_call'] != 0 else 0
                            temp_member['ptp_rate_acc']  = temp_member['count_ptp']/temp_member['total_call'] if temp_member['total_call'] != 0 else 0
                            temp_member['ptp_rate_amt']  = temp_member['ptp_amount']/temp_member['total_amount'] if temp_member['total_amount'] != 0  else 0
                            temp_member['paid_rate_acc'] = temp_member['count_paid_promise']/temp_member['count_ptp'] if temp_member['count_ptp'] != 0 else 0
                            temp_member['paid_rate_amt'] = temp_member['paid_amount_promise']/temp_member['ptp_amount'] if temp_member['ptp_amount'] != 0 else 0
                            temp_member['conn_rate']     = temp_member['count_conn']/temp_member['total_call'] if temp_member['total_call'] != 0 else 0
                            temp_member['collect_ratio_acc'] = temp_member['count_paid']/temp_member['total_call'] if temp_member['total_call'] != 0 else 0
                            temp_member['collect_ratio_amt'] = temp_member['paid_amount']/temp_member['total_amount'] if temp_member['total_amount'] != 0 else 0
                            temp_member['createdAt'] = time.time()
                            temp_member['createdBy'] = 'system'
                            temp_member['for_month'] = month

                            member_arr.append(temp_member)

                        temp['spin_rate']     = temp['count_spin']/temp['total_call']  if temp['total_call'] != 0 else 0
                        temp['ptp_rate_acc']  = temp['count_ptp']/temp['total_call']  if temp['total_call'] != 0 else 0
                        temp['ptp_rate_amt']  = temp['ptp_amount']/temp['total_amount']  if temp['total_amount'] != 0 else 0
                        temp['paid_rate_acc'] = temp['count_paid_promise']/temp['count_ptp']  if temp['count_ptp'] != 0 else 0
                        temp['paid_rate_amt'] = temp['paid_amount_promise']/temp['ptp_amount']  if temp['ptp_amount'] != 0 else 0
                        temp['conn_rate']     = temp['count_conn']/temp['total_call'] if temp['total_call'] != 0 else 0
                        temp['collect_ratio_acc'] = temp['count_paid']/temp['total_call']  if temp['total_call'] != 0 else 0
                        temp['collect_ratio_amt'] = temp['paid_amount']/temp['total_amount']  if temp['total_amount'] != 0 else 0
                        temp['createdAt'] = time.time()
                        temp['createdBy'] = 'system'
                        temp['for_month'] = month

                        insertData.append(temp)
                        insertData += member_arr;

                        i += 1

                
            checkGroupA = 'true'

        if debtGroupCell[0:1] != 'A' and debtGroupCell[0:1] != 'F':
            i = 1
            dueDayOfMonth = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_due_date'), WHERE={'for_month': str(month), 'debt_group': debtGroupCell[1:3]})
            for groupProduct in list(listGroupProduct):
                teams = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Group'), WHERE={'name' : {'$regex' : groupProduct['value']} , 'debt_groups': debtGroupCell[0:3]})
                # print(debtGroupCell[0:3])
                if teams != None:
                    temp = {
                        'name'           : teams['name'],
                        'group'          : debtGroupCell[0:1],
                        'team'           : i,
                        'date'           : todayTimeStamp,
                        'extension'      : teams['lead'],
                        'team_lead'      : 'true',
                        'count_data'     : 0,
                        'unwork'            : 0,
                        'talk_time'         : 0,
                        'total_call'        : 0,
                        'total_amount'      : 0,
                        'count_spin'        : 0,
                        'spin_amount'       : 0,
                        'count_conn'        : 0,
                        'conn_amount'       : 0,
                        'count_paid'        : 0,
                        'paid_amount'       : 0,
                        'ptp_amount'        : 0,
                        'count_ptp'         : 0,
                        'paid_amount_promise'       : 0,
                        'count_paid_promise'        : 0,
                    }

                    
                    # members
                    member_arr = []
                    count_member = len(teams['members'])
                    for member in list(teams['members']):
                        temp_member = {
                            'name'           : '',
                            'group'          : debtGroupCell[0:1],
                            'team'           : i,
                            'date'           : todayTimeStamp,
                            'extension'      : member,
                            'count_data'     : 0,
                            'unwork'            : 0,
                            'talk_time'         : 0,
                            'total_call'        : 0,
                            'total_amount'      : 0,
                            'count_spin'        : 0,
                            'spin_amount'       : 0,
                            'count_conn'        : 0,
                            'conn_amount'       : 0,
                            'count_paid'        : 0,
                            'paid_amount'       : 0,
                            'ptp_amount'        : 0,
                            'count_ptp'         : 0,
                            'paid_amount_promise'       : 0,
                            'count_paid_promise'        : 0,
                        }
                        users = _mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'User'),WHERE={'extension': str(member)}, SELECT=['extension','agentname'])
                        if users != None:
                            temp_member['name'] = users['agentname']
                        
                        if todayTimeStamp < dueDayOfMonth['due_date_add_1']:
                            dueDayLastMonth = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_due_date'), WHERE={'for_month': str(month - 1), 'debt_group': debtGroupCell[1:3]})
                            temp['due_date'] = dueDayLastMonth['due_date'] if dueDayLastMonth is not None else ''
                            #Lay gia tri no vao ngay due date + 1#
                            incidenceInfo = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Due_date_next_date'), WHERE={'for_month': str(month - 1),  'debt_group': debtGroupCell[0:1],'due_date_code': debtGroupCell[1:3],'product':groupProduct['text']})
                            #Lay gia tri no vao ngay due date + 1#
                        else:
                            temp['due_date'] = dueDayOfMonth['due_date']
                            incidenceInfo = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Due_date_next_date'), WHERE={'for_month': str(month), 'debt_group': debtGroupCell[0:1],'due_date_code': debtGroupCell[1:3],'product':groupProduct['text']})

                        acc_arr = []
                        if incidenceInfo is not None:
                            for inc in incidenceInfo:
                                temp_member['count_data'] += inc['debt_acc_no']
                                # temp['inci_amt'] += inc['current_balance_total']
                                # temp['inci_ob_principal'] += inc['ob_principal_total']
                                acc_arr_1 = inc['acc_arr'] if 'acc_arr' in inc.keys() else []
                                acc_arr += acc_arr_1

                        aggregate_unwork = [
                            {
                                "$match":
                                {
                                    "account_number": {'$gte': temp['due_date']},
                                    "userextension": member,
                                    "disposition" : {'$ne' : 'ANSWERED'}
                                }
                            },{
                                "$group":
                                {
                                    "_id": 'null',
                                    "count_unwork": {'$sum': 1},
                                }
                            }
                        ]
                        unworkData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'worldfonepbxmanager'),aggregate_pipeline=aggregate_unwork)
                        if unworkData != None:
                            for row in unworkData:
                                temp_member['unwork']            = row['count_unwork']

                        aggregate_ptp = [
                            {
                                "$match":
                                {
                                    "created_at": {'$gte': temp['due_date']},
                                    "account_number": {'$in': acc_arr},
                                    '$or' : [ { 'action_code' :  'BPTP'}, {'action_code' :  'PTP Today'}]
                                }
                            },{
                                "$group":
                                {
                                    "_id": 'null',
                                    "acc_arr": {'$addToSet': '$account_number'},
                                }
                            }
                        ]
                        ptpData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'Action_code'),aggregate_pipeline=aggregate_ptp)
                        account_ptp_arr = []
                        if ptpData != None:
                            for row in ptpData:
                                account_ptp_arr           = row['acc_arr']

                        aggregate_cdr = [
                            {
                                "$match":
                                {
                                    "starttime": {'$gte': temp['due_date']},
                                    "userextension": member,
                                }
                            },{
                                "$group":
                                {
                                    "_id": 'null',
                                    "talk_time": {'$sum': '$billduration'},
                                    "total_call": {'$sum': 1},
                                    "customernumber_arr": {'$addToSet': '$customernumber'},
                                    "phone_arr": {'$push': '$customernumber'},
                                }
                            }
                        ]
                        cdrData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'worldfonepbxmanager'),aggregate_pipeline=aggregate_cdr)
                        customernumber_arr = []
                        phone_arr = []
                        disposition_arr = []
                        if cdrData != None:
                            for row in cdrData:
                                temp_member['talk_time']            = row['talk_time']
                                temp_member['total_call']           = row['total_call'] 
                                phone_arr                           = row['phone_arr'] 

                         # connected
                        aggregate_cdr_ans = [
                            {
                                "$match":
                                {
                                    "starttime": {'$gte': temp['due_date']},
                                    "userextension": member,
                                    "disposition" : 'ANSWERED'
                                }
                            },{
                                "$group":
                                {
                                    "_id": 'null',
                                    "count_conn": {'$sum': 1},
                                    "phone_ans_arr": {'$addToSet': '$customernumber'},
                                }
                            }
                        ]
                        cdrAnsData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'worldfonepbxmanager'),aggregate_pipeline=aggregate_cdr_ans)
                        phone_ans_arr = []
                        if cdrAnsData != None:
                            for row in cdrAnsData:
                                phone_ans_arr            = row['phone_ans_arr']
                                temp_member['count_conn'] = row['count_conn']


                        if groupProduct['value'] == 'SIBS':
                            aggregate_ptp_amt = [
                                {
                                    "$match":
                                    {
                                        "account_number": {'$in': account_ptp_arr},
                                    }
                                },{
                                    "$group":
                                    {
                                        "_id": 'null',
                                        "ptp_amount": {'$sum': '$current_balance'},
                                    }
                                }
                            ]
                            ptpAmtData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'LNJC05'),aggregate_pipeline=aggregate_ptp_amt)
                            if ptpAmtData != None:
                                for row in ptpAmtData:
                                    temp_member['count_ptp']            = len(account_ptp_arr)
                                    temp_member['ptp_amount']           = row['ptp_amount']

                            aggregate_paid_promise = [
                                {
                                    "$match":
                                    {
                                        "created_at": {'$gte': temp['due_date']},
                                        "account_number": {'$in': account_ptp_arr},
                                    }
                                },{
                                    "$group":
                                    {
                                        "_id": 'null',
                                        "paid_amount_promise": {'$sum': '$amt'},
                                        "count_paid_promise": {'$addToSet': '$account_number'},
                                    }
                                }
                            ]
                            paidPromiseData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'LN3206F'),aggregate_pipeline=aggregate_paid_promise)
                            if paidPromiseData != None:
                                for row in paidPromiseData:
                                    temp_member['count_paid_promise']            = len(row['count_paid_promise'] )
                                    temp_member['paid_amount_promise']           = row['paid_amount_promise']     


                            # paid
                            aggregate_paid = [
                                {
                                    "$match":
                                    {
                                        "created_at": {'$gte': temp['due_date']},
                                        "account_number": {'$in': acc_arr},
                                    }
                                },{
                                    "$group":
                                    {
                                        "_id": 'null',
                                        "paid_amount": {'$sum': '$amt'},
                                        "count_paid": {'$addToSet': '$account_number'},
                                    }
                                }
                            ]
                            paidPromiseData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'LN3206F'),aggregate_pipeline=aggregate_paid)
                            if paidPromiseData != None:
                                for row in paidPromiseData:
                                    temp_member['count_paid']            = len(row['count_paid'] )
                                    temp_member['paid_amount']           = row['paid_amount']     

                            aggregate_cdr_amt = [
                                {
                                    "$match":
                                    {
                                        "mobile_num": {'$in': customernumber_arr},
                                    }
                                },{
                                    "$group":
                                    {
                                        "_id": 'null',
                                        "total_amount": {'$sum': '$current_balance'},

                                    }
                                }
                            ]
                            cdrAmountData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'LNJC05'),aggregate_pipeline=aggregate_cdr_amt)
                            if cdrAmountData != None:
                                for row in cdrAmountData:
                                    temp_member['total_amount']            = row['total_amount']

                            # spin
                            count_spin = 0
                            phone_spin_arr = []
                            for phone in phone_arr:
                                if phone_arr.count(phone) > 1:
                                    phone_spin_arr.append(phone)
                                    count_spin += 1

                            aggregate_spin = [
                                {
                                    "$match":
                                    {
                                        "mobile_num": {'$in': phone_spin_arr},
                                    }
                                },{
                                    "$group":
                                    {
                                        "_id": 'null',
                                        "spin_amount": {'$sum': '$current_balance'},

                                    }
                                }
                            ]
                            spinData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'LNJC05'),aggregate_pipeline=aggregate_spin)
                            if spinData != None:
                                for row in spinData:
                                    temp_member['spin_amount']            = row['spin_amount']
                                    temp_member['count_spin']             = count_spin

                            aggregate_amt_ans = [
                                {
                                    "$match":
                                    {
                                        "mobile_num": {'$in': phone_ans_arr},
                                    }
                                },{
                                    "$group":
                                    {
                                        "_id": 'null',
                                        "conn_amount": {'$sum': '$current_balance'},
                                    }
                                }
                            ]
                            amtAnsData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'LNJC05'),aggregate_pipeline=aggregate_amt_ans)
                            if amtAnsData != None:
                                for row in amtAnsData:
                                    temp_member['conn_amount']            = row['conn_amount']

                        if groupProduct['value'] == 'Card':
                            aggregate_ptp_amt = [
                                {
                                    "$match":
                                    {
                                        "account_number": {'$in': account_ptp_arr},
                                    }
                                },{
                                    "$group":
                                    {
                                        "_id": 'null',
                                        "ptp_amount": {'$sum': '$cur_bal'},
                                    }
                                }
                            ]
                            ptpAmtData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'List_of_account_in_collection'),aggregate_pipeline=aggregate_ptp_amt)
                            if ptpAmtData != None:
                                for row in ptpAmtData:
                                    temp_member['count_ptp']            = len(account_ptp_arr)
                                    temp_member['ptp_amount']           = row['ptp_amount']

                            aggregate_paid_promise = [
                                {
                                    "$match":
                                    {
                                        "created_at": {'$gte': temp['due_date']},
                                        "account_number": {'$in': account_ptp_arr},
                                    }
                                },{
                                    "$group":
                                    {
                                        "_id": 'null',
                                        "paid_amount_promise": {'$sum': '$amount'},
                                        "count_paid_promise": {'$addToSet': '$account_number'},
                                    }
                                }
                            ]
                            paidPromiseData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_input_payment_of_card'),aggregate_pipeline=aggregate_paid_promise)
                            if paidPromiseData != None:
                                for row in paidPromiseData:
                                    temp_member['count_paid_promise']            = len(row['count_paid_promise'] )
                                    temp_member['paid_amount_promise']           = row['paid_amount_promise']     


                            # paid
                            aggregate_paid = [
                                {
                                    "$match":
                                    {
                                        "created_at": {'$gte': temp['due_date']},
                                        "account_number": {'$in': acc_arr},
                                    }
                                },{
                                    "$group":
                                    {
                                        "_id": 'null',
                                        "paid_amount": {'$sum': '$amount'},
                                        "count_paid": {'$addToSet': '$account_number'},
                                    }
                                }
                            ]
                            paidPromiseData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_input_payment_of_card'),aggregate_pipeline=aggregate_paid)
                            if paidPromiseData != None:
                                for row in paidPromiseData:
                                    temp_member['count_paid']            = len(row['count_paid'] )
                                    temp_member['paid_amount']           = row['paid_amount']     

                            aggregate_cdr_amt = [
                                {
                                    "$match":
                                    {
                                        "phone": {'$in': customernumber_arr},
                                    }
                                },{
                                    "$group":
                                    {
                                        "_id": 'null',
                                        "total_amount": {'$sum': '$cur_bal'},
                                    }
                                }
                            ]
                            cdrAmountData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'List_of_account_in_collection'),aggregate_pipeline=aggregate_cdr_amt)
                            if cdrAmountData != None:
                                for row in cdrAmountData:
                                    temp_member['total_amount']            = row['total_amount']

                            # spin
                            count_spin = 0
                            phone_spin_arr = []
                            for phone in phone_arr:
                                if phone_arr.count(phone) > 1:
                                    phone_spin_arr.append(phone)
                                    count_spin += 1

                            aggregate_spin = [
                                {
                                    "$match":
                                    {
                                        "phone": {'$in': phone_spin_arr},
                                    }
                                },{
                                    "$group":
                                    {
                                        "_id": 'null',
                                        "spin_amount": {'$sum': '$cur_bal'},

                                    }
                                }
                            ]
                            spinData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'List_of_account_in_collection'),aggregate_pipeline=aggregate_spin)
                            if spinData != None:
                                for row in spinData:
                                    temp_member['spin_amount']            = row['spin_amount']
                                    temp_member['count_spin']             = count_spin

                            aggregate_amt_ans = [
                                {
                                    "$match":
                                    {
                                        "phone": {'$in': phone_ans_arr},
                                    }
                                },{
                                    "$group":
                                    {
                                        "_id": 'null',
                                        "conn_amount": {'$sum': '$cur_bal'},
                                    }
                                }
                            ]
                            amtAnsData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'List_of_account_in_collection'),aggregate_pipeline=aggregate_amt_ans)
                            if amtAnsData != None:
                                for row in amtAnsData:
                                    temp_member['conn_amount']            = row['conn_amount']


                        temp['count_data']      += temp_member['count_data'];
                        temp['unwork']          += temp_member['unwork'];
                        temp['talk_time']      += temp_member['talk_time'];
                        temp['total_call']     += temp_member['total_call'];
                        temp['total_amount']   += temp_member['total_amount'];
                        temp['conn_amount']    += temp_member['conn_amount'];
                        temp['count_conn']     += temp_member['count_conn'];
                        temp['spin_amount']    += temp_member['spin_amount'];
                        temp['count_spin']     += temp_member['count_spin'];
                        temp['count_ptp']      += temp_member['count_ptp'];
                        temp['ptp_amount']     += temp_member['ptp_amount'];
                        temp['count_paid_promise']      += temp_member['count_paid_promise'];
                        temp['paid_amount_promise']     += temp_member['count_paid_promise'];
                        

                        temp_member['spin_rate']     = temp_member['count_spin']/temp_member['total_call'] if temp_member['total_call'] != 0 else 0
                        temp_member['ptp_rate_acc']  = temp_member['count_ptp']/temp_member['total_call'] if temp_member['total_call'] != 0 else 0
                        temp_member['ptp_rate_amt']  = temp_member['ptp_amount']/temp_member['total_amount'] if temp_member['total_amount'] != 0  else 0
                        temp_member['paid_rate_acc'] = temp_member['count_paid_promise']/temp_member['count_ptp'] if temp_member['count_ptp'] != 0 else 0
                        temp_member['paid_rate_amt'] = temp_member['paid_amount_promise']/temp_member['ptp_amount'] if temp_member['ptp_amount'] != 0 else 0
                        temp_member['conn_rate']     = temp_member['count_conn']/temp_member['total_call'] if temp_member['total_call'] != 0 else 0
                        temp_member['collect_ratio_acc'] = temp_member['count_paid']/temp_member['total_call'] if temp_member['total_call'] != 0 else 0
                        temp_member['collect_ratio_amt'] = temp_member['paid_amount']/temp_member['total_amount'] if temp_member['total_amount'] != 0 else 0
                        temp_member['createdAt'] = time.time()
                        temp_member['createdBy'] = 'system'
                        temp_member['for_month'] = month

                        member_arr.append(temp_member)

                    temp['spin_rate']     = temp['count_spin']/temp['total_call']  if temp['total_call'] != 0 else 0
                    temp['ptp_rate_acc']  = temp['count_ptp']/temp['total_call']  if temp['total_call'] != 0 else 0
                    temp['ptp_rate_amt']  = temp['ptp_amount']/temp['total_amount']  if temp['total_amount'] != 0 else 0
                    temp['paid_rate_acc'] = temp['count_paid_promise']/temp['count_ptp']  if temp['count_ptp'] != 0 else 0
                    temp['paid_rate_amt'] = temp['paid_amount_promise']/temp['ptp_amount']  if temp['ptp_amount'] != 0 else 0
                    temp['conn_rate']     = temp['count_conn']/temp['total_call'] if temp['total_call'] != 0 else 0
                    temp['collect_ratio_acc'] = temp['count_paid']/temp['total_call']  if temp['total_call'] != 0 else 0
                    temp['collect_ratio_amt'] = temp['paid_amount']/temp['total_amount']  if temp['total_amount'] != 0 else 0
                    temp['createdAt'] = time.time()
                    temp['createdBy'] = 'system'
                    temp['for_month'] = month

                    insertData.append(temp)
                    insertData += member_arr;

                    i += 1

                
    # WO
    
    teams = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Group'), WHERE={'name' : {'$regex' : 'WO'} , 'debt_groups': 'F01'})
    if teams != None:
        # temp = {
        #     'name'           : teams['name'],
        #     'group'          : debtGroupCell[0:1],
        #     'team'           : i,
        #     'date'           : todayTimeStamp,
        #     'extension'      : teams['lead'],
        #     'team_lead'      : 'true',
        #     'count_data'     : 0,
        #     'unwork'            : 0,
        #     'talk_time'         : 0,
        #     'total_call'        : 0,
        #     'total_amount'      : 0,
        #     'count_spin'        : 0,
        #     'spin_amount'       : 0,
        #     'count_conn'        : 0,
        #     'conn_amount'       : 0,
        #     'count_paid'        : 0,
        #     'paid_amount'       : 0,
        #     'ptp_amount'        : 0,
        #     'count_ptp'         : 0,
        #     'paid_amount_promise'       : 0,
        #     'count_paid_promise'        : 0,
        # }

        
        # members
        i = 1
        member_arr = []
        for member in list(teams['members']):
            temp_member = {
                'name'           : '',
                'group'          : 'F',
                'team'           : i,
                'date'           : todayTimeStamp,
                'extension'      : member,
                'count_data'     : 0,
                'unwork'            : 0,
                'talk_time'         : 0,
                'total_call'        : 0,
                'total_amount'      : 0,
                'count_spin'        : 0,
                'spin_amount'       : 0,
                'count_conn'        : 0,
                'conn_amount'       : 0,
                'count_paid'        : 0,
                'paid_amount'       : 0,
                'ptp_amount'        : 0,
                'count_ptp'         : 0,
                'paid_amount_promise'       : 0,
                'count_paid_promise'        : 0,
            }
            users = _mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'User'),WHERE={'extension': str(member)}, SELECT=['extension','agentname'])
            if users != None:
                temp_member['name'] = users['agentname']
            
            incidenceInfo = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Due_date_next_date'), WHERE={'for_month': str(month), 'debt_group': 'F','product':'WO'}) 

            acc_arr = []
            if incidenceInfo is not None:
                for inc in incidenceInfo:
                    temp_member['count_data'] += inc['debt_acc_no']
                    acc_arr_1 = inc['acc_arr'] if 'acc_arr' in inc.keys() else []
                    acc_arr += acc_arr_1
                    temp_member['due_date'] = inc['created_at']

            aggregate_unwork = [
                {
                    "$match":
                    {
                        "account_number": {'$gte': temp_member['due_date']},
                        "userextension": member,
                        "disposition" : {'$ne' : 'ANSWERED'}
                    }
                },{
                    "$group":
                    {
                        "_id": 'null',
                        "count_unwork": {'$sum': 1},
                    }
                }
            ]
            unworkData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'worldfonepbxmanager'),aggregate_pipeline=aggregate_unwork)
            if unworkData != None:
                for row in unworkData:
                    temp_member['unwork']            = row['count_unwork']

            aggregate_ptp = [
                {
                    "$match":
                    {
                        "created_at": {'$gte': temp_member['due_date']},
                        "account_number": {'$in': acc_arr},
                        '$or' : [ { 'action_code' :  'BPTP'}, {'action_code' :  'PTP Today'}]
                    }
                },{
                    "$group":
                    {
                        "_id": 'null',
                        "acc_arr": {'$addToSet': '$account_number'},
                    }
                }
            ]
            ptpData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'Action_code'),aggregate_pipeline=aggregate_ptp)
            account_ptp_arr = []
            if ptpData != None:
                for row in ptpData:
                    account_ptp_arr           = row['acc_arr']

            aggregate_cdr = [
                {
                    "$match":
                    {
                        "starttime": {'$gte': temp_member['due_date']},
                        "userextension": member,
                    }
                },{
                    "$group":
                    {
                        "_id": 'null',
                        "talk_time": {'$sum': '$billduration'},
                        "total_call": {'$sum': 1},
                        "customernumber_arr": {'$addToSet': '$customernumber'},
                        "phone_arr": {'$push': '$customernumber'},
                    }
                }
            ]
            cdrData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'worldfonepbxmanager'),aggregate_pipeline=aggregate_cdr)
            customernumber_arr = []
            phone_arr = []
            disposition_arr = []
            if cdrData != None:
                for row in cdrData:
                    temp_member['talk_time']            = row['talk_time']
                    temp_member['total_call']           = row['total_call'] 
                    phone_arr                           = row['phone_arr'] 

             # connected
            aggregate_cdr_ans = [
                {
                    "$match":
                    {
                        "starttime": {'$gte': temp_member['due_date']},
                        "userextension": member,
                        "disposition" : 'ANSWERED'
                    }
                },{
                    "$group":
                    {
                        "_id": 'null',
                        "count_conn": {'$sum': 1},
                        "phone_ans_arr": {'$addToSet': '$customernumber'},
                    }
                }
            ]
            cdrAnsData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'worldfonepbxmanager'),aggregate_pipeline=aggregate_cdr_ans)
            phone_ans_arr = []
            if cdrAnsData != None:
                for row in cdrAnsData:
                    phone_ans_arr            = row['phone_ans_arr']
                    temp_member['count_conn'] = row['count_conn']


            # if groupProduct['value'] == 'SIBS':
            aggregate_ptp_amt = [
                {
                    "$match":
                    {
                        "ACCTNO": {'$in': account_ptp_arr},
                    }
                },{
                    '$project': 
                    {
                        'pay_payment': {'$sum' : [ '$WO9711', '$WO9712' ,'$WO9713'] }
                    }
                },{
                    "$group":
                    {
                        "_id": 'null',
                        "ptp_amount": {'$sum': '$pay_payment'},
                    }
                }
            ]
            ptpAmtData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'WO_monthly'),aggregate_pipeline=aggregate_ptp_amt)
            if ptpAmtData != None:
                for row in ptpAmtData:
                    temp_member['count_ptp']            = len(account_ptp_arr)
                    temp_member['ptp_amount']           = row['ptp_amount']

            aggregate_paid_promise = [
                {
                    "$match":
                    {
                        "created_at": {'$gte': temp_member['due_date']},
                        "account_number": {'$in': account_ptp_arr},
                    }
                },{
                    '$project': 
                    {
                        'account_number' : 1,
                        'pay_payment': {'$sum' : [ '$pay_9711', '$pay_9712' ,'$late_charge_9713'] }
                    }
                },{
                    "$group":
                    {
                        "_id": 'null',
                        "paid_amount_promise": {'$sum': '$pay_payment'},
                        "count_paid_promise": {'$addToSet': '$account_number'},
                    }
                }
            ]
            paidPromiseData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'Wo_payment'),aggregate_pipeline=aggregate_paid_promise)
            if paidPromiseData != None:
                for row in paidPromiseData:
                    temp_member['count_paid_promise']            = len(row['count_paid_promise'] )
                    temp_member['paid_amount_promise']           = row['paid_amount_promise']     


            # paid
            aggregate_paid = [
                {
                    "$match":
                    {
                        "created_at": {'$gte': temp_member['due_date']},
                        "account_number": {'$in': acc_arr},
                    }
                },{
                    '$project': 
                    {
                        'account_number' : 1,
                        'pay_payment': {'$sum' : [ '$pay_9711', '$pay_9712' ,'$late_charge_9713'] }
                    }
                },{
                    "$group":
                    {
                        "_id": 'null',
                        "paid_amount": {'$sum': '$pay_payment'},
                        "count_paid": {'$addToSet': '$account_number'},
                    }
                }
            ]
            paidPromiseData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'Wo_payment'),aggregate_pipeline=aggregate_paid)
            if paidPromiseData != None:
                for row in paidPromiseData:
                    temp_member['count_paid']            = len(row['count_paid'] )
                    temp_member['paid_amount']           = row['paid_amount']     

            aggregate_cdr_amt = [
                {
                    "$match":
                    {
                        "PHONE": {'$in': customernumber_arr},
                    }
                },{
                    '$project': 
                    {
                        'pay_payment': {'$sum' : [ '$WO9711', '$WO9712' ,'$WO9713'] }
                    }
                },{
                    "$group":
                    {
                        "_id": 'null',
                        "total_amount": {'$sum': '$pay_payment'},

                    }
                }
            ]
            cdrAmountData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'WO_monthly'),aggregate_pipeline=aggregate_cdr_amt)
            if cdrAmountData != None:
                for row in cdrAmountData:
                    temp_member['total_amount']            = row['total_amount']

            # spin
            count_spin = 0
            phone_spin_arr = []
            for phone in phone_arr:
                if phone_arr.count(phone) > 1:
                    phone_spin_arr.append(phone)
                    count_spin += 1

            aggregate_spin = [
                {
                    "$match":
                    {
                        "PHONE": {'$in': phone_spin_arr},
                    }
                },{
                    '$project': 
                    {
                        'pay_payment': {'$sum' : [ '$WO9711', '$WO9712' ,'$WO9713'] }
                    }
                },{
                    "$group":
                    {
                        "_id": 'null',
                        "spin_amount": {'$sum': '$pay_payment'},

                    }
                }
            ]
            spinData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'WO_monthly'),aggregate_pipeline=aggregate_spin)
            if spinData != None:
                for row in spinData:
                    temp_member['spin_amount']            = row['spin_amount']
                    temp_member['count_spin']             = count_spin

            aggregate_amt_ans = [
                {
                    "$match":
                    {
                        "PHONE": {'$in': phone_ans_arr},
                    }
                },{
                    '$project': 
                    {
                        'pay_payment': {'$sum' : [ '$WO9711', '$WO9712' ,'$WO9713'] }
                    }
                },{
                    "$group":
                    {
                        "_id": 'null',
                        "conn_amount": {'$sum': '$current_balance'},
                    }
                }
            ]
            amtAnsData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'WO_monthly'),aggregate_pipeline=aggregate_amt_ans)
            if amtAnsData != None:
                for row in amtAnsData:
                    temp_member['conn_amount']            = row['conn_amount']

            
            # temp['count_data']      += temp_member['count_data'];
            # temp['unwork']          += temp_member['unwork'];
            # temp['talk_time']      += temp_member['talk_time'];
            # temp['total_call']     += temp_member['total_call'];
            # temp['total_amount']   += temp_member['total_amount'];
            # temp['conn_amount']    += temp_member['conn_amount'];
            # temp['count_conn']     += temp_member['count_conn'];
            # temp['spin_amount']    += temp_member['spin_amount'];
            # temp['count_spin']     += temp_member['count_spin'];
            # temp['count_ptp']      += temp_member['count_ptp'];
            # temp['ptp_amount']     += temp_member['ptp_amount'];
            # temp['count_paid_promise']      += temp_member['count_paid_promise'];
            # temp['paid_amount_promise']     += temp_member['count_paid_promise'];
            

            temp_member['spin_rate']     = temp_member['count_spin']/temp_member['total_call'] if temp_member['total_call'] != 0 else 0
            temp_member['ptp_rate_acc']  = temp_member['count_ptp']/temp_member['total_call'] if temp_member['total_call'] != 0 else 0
            temp_member['ptp_rate_amt']  = temp_member['ptp_amount']/temp_member['total_amount'] if temp_member['total_amount'] != 0  else 0
            temp_member['paid_rate_acc'] = temp_member['count_paid_promise']/temp_member['count_ptp'] if temp_member['count_ptp'] != 0 else 0
            temp_member['paid_rate_amt'] = temp_member['paid_amount_promise']/temp_member['ptp_amount'] if temp_member['ptp_amount'] != 0 else 0
            temp_member['conn_rate']     = temp_member['count_conn']/temp_member['total_call'] if temp_member['total_call'] != 0 else 0
            temp_member['collect_ratio_acc'] = temp_member['count_paid']/temp_member['total_call'] if temp_member['total_call'] != 0 else 0
            temp_member['collect_ratio_amt'] = temp_member['paid_amount']/temp_member['total_amount'] if temp_member['total_amount'] != 0 else 0
            temp_member['createdAt'] = time.time()
            temp_member['createdBy'] = 'system'
            temp_member['for_month'] = month

            insertData.append(temp_member)

        # temp['spin_rate']     = temp['count_spin']/temp['total_call']  if temp['total_call'] != 0 else 0
        # temp['ptp_rate_acc']  = temp['count_ptp']/temp['total_call']  if temp['total_call'] != 0 else 0
        # temp['ptp_rate_amt']  = temp['ptp_amount']/temp['total_amount']  if temp['total_amount'] != 0 else 0
        # temp['paid_rate_acc'] = temp['count_paid_promise']/temp['count_ptp']  if temp['count_ptp'] != 0 else 0
        # temp['paid_rate_amt'] = temp['paid_amount_promise']/temp['ptp_amount']  if temp['ptp_amount'] != 0 else 0
        # temp['conn_rate']     = temp['count_conn']/temp['total_call'] if temp['total_call'] != 0 else 0
        # temp['collect_ratio_acc'] = temp['count_paid']/temp['total_call']  if temp['total_call'] != 0 else 0
        # temp['collect_ratio_amt'] = temp['paid_amount']/temp['total_amount']  if temp['total_amount'] != 0 else 0
        # temp['createdAt'] = time.time()
        # temp['createdBy'] = 'system'
        # temp['for_month'] = month

        # insertData.append(temp)
        # insertData += member_arr;

            i += 1

    

    if len(insertData) > 0:
        mongodb.batch_insert(MONGO_COLLECTION=collection, insert_data=insertData)

    now_end         = datetime.now()
    log.write(now_end.strftime("%d/%m/%Y, %H:%M:%S") + ': End Log' + '\n')
    print('DONE')
except Exception as e:
    log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
    pprint(str(e))

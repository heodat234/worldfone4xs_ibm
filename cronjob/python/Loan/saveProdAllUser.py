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
collection          = common.getSubUser(subUserType, 'Daily_all_user_report_temp')
group_collection    = common.getSubUser(subUserType, 'Group_product')
product_collection  = common.getSubUser(subUserType, 'Product')
report_due_date_collection  = common.getSubUser(subUserType, 'Report_due_date')
user_collection         = common.getSubUser(subUserType, 'User_product')
jsondata_collection     = common.getSubUser(subUserType, 'Jsondata')
diallist_detail_collection      = common.getSubUser(subUserType, 'Diallist_detail')
cdr_collection                  = common.getSubUser(subUserType, 'worldfonepbxmanager')
action_code_collection          = common.getSubUser(subUserType, 'Action_code')
lnjc05_yesterday_collection     = common.getSubUser(subUserType, 'LNJC05_yesterday')
list_of_account_yesterday_collection     = common.getSubUser(subUserType, 'List_of_account_in_collection_yesterday')
ln3206f_collection      = common.getSubUser(subUserType, 'LN3206F')
gl_collection           = common.getSubUser(subUserType, 'Report_input_payment_of_card')



log = open(base_url + "cronjob/python/Loan/log/dailyProdAllUser_log.txt","a")
now = datetime.now()
log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': Start Import' + '\n')
try:
    insertData = []
    insertDataTotal = []
    listDebtGroup = []

    # today = date.today()
    today = datetime.strptime('06/02/2020', "%d/%m/%Y").date()

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


    yesterday = today - timedelta(days=1)
    yesterdayTimeStamp = int(time.mktime(time.strptime(str(yesterday + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
    endYesterdayTimeStamp = int(time.mktime(time.strptime(str(yesterday + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))

    mainProduct = {}
    mainProductRaw = mongodb.get(MONGO_COLLECTION=product_collection)
    for prod in mainProductRaw:
        mainProduct[prod['code']] = prod['name']

    debtGroup = _mongodb.getOne(MONGO_COLLECTION=jsondata_collection, WHERE={'tags': ['debt', 'group']})
    dueDate = _mongodb.getOne(MONGO_COLLECTION=jsondata_collection, WHERE={'tags': ['debt', 'duedate']})

    for group in debtGroup['data']:
        for duedate in dueDate['data']:
            listDebtGroup.append(group['text'] + duedate['text'])

    listDebtGroup = sorted(listDebtGroup)

    listGroupProductRaw = _mongodb.getOne(MONGO_COLLECTION=jsondata_collection, WHERE={'tags': ['group', 'debt', 'product']})
    listGroupProduct = listGroupProductRaw['data']

    # users = _mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'User'), SELECT=['extension','agentname'],SORT=([('_id', -1)]),SKIP=0, TAKE=500)
    
    checkGroupA = 'false'
    for debtGroupCell in list(listDebtGroup):
        if debtGroupCell[0:1] == 'A' and checkGroupA == 'false':
            i = 1
            dueDayOfMonth = mongodb.getOne(MONGO_COLLECTION=report_due_date_collection, WHERE={'for_month': str(month), 'debt_group': debtGroupCell[1:3]})
            for groupProduct in list(listGroupProduct):
                leaders = []
                groups = mongodb.get(MONGO_COLLECTION=group_collection, WHERE={'name' : {'$regex' : groupProduct['value']},'active': 'true' , 'debt_groups': debtGroupCell[0:3]},SELECT=['lead'])
                for group in groups:
                    leaders.append(group['lead']) if 'lead' in group.keys() else ''

                list_set = set(leaders) 
                unique_leaders = (list(list_set)) 

                for lead in unique_leaders:
                    teams = mongodb.get(MONGO_COLLECTION=group_collection, WHERE={'lead': lead ,'name' : {'$regex' : groupProduct['value']} , 'debt_groups': debtGroupCell[0:3]})
                    if teams != None:
                        groupTeam = []
                        name = ''
                        for team in teams:
                            name1 =  team['name']
                            groupTeam += team['members']

                        list_members = set(groupTeam) 
                        unique_members = (list(list_members))

                        name = name1.replace("/G1", "")
                        name = name.replace("/G2", "")
                        name = name.replace("/G3", "")
                        temp = {
                            'name'           : name,
                            'group'          : debtGroupCell[0:1],
                            'team'           : i,
                            'date'           : todayTimeStamp,
                            'extension'      : lead,
                            'team_lead'      : 'true',
                            'count_data'     : 0,
                            'unwork'            : 0,
                            'work'              : 0,
                            'talk_time'         : 0,
                            'count_contacted'   : 0,
                            'contacted_amount'  : 0,
                            'number_of_call'    : 0,
                            'total_call'        : 0,
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
                            dueDayLastMonth = mongodb.getOne(MONGO_COLLECTION=report_due_date_collection, WHERE={'for_month': str(month - 1), 'debt_group': debtGroupCell[1:3]})
                            temp['due_date'] = dueDayLastMonth['due_date'] if dueDayLastMonth is not None else ''
                        else:
                            temp['due_date'] = dueDayOfMonth['due_date']

                        # members
                        member_arr = []
                        count_member = len(unique_members)
                        for member in list(unique_members):
                            temp_member = {
                                'name'           : '',
                                'group'          : debtGroupCell[0:1],
                                'team'           : i,
                                'date'           : todayTimeStamp,
                                'extension'      : member,
                                'count_data'     : 0,
                                'unwork'            : 0,
                                'work'              : 0,
                                'talk_time'         : 0,
                                'count_contacted'   : 0,
                                'contacted_amount'  : 0,
                                'number_of_call'    : 0,
                                'total_call'        : 0,
                                'count_conn'        : 0,
                                'conn_amount'       : 0,
                                'count_paid'        : 0,
                                'paid_amount'       : 0,
                                'ptp_amount'        : 0,
                                'count_ptp'         : 0,
                                'paid_amount_promise'       : 0,
                                'count_paid_promise'        : 0,
                            }
                            users = _mongodb.getOne(MONGO_COLLECTION=user_collection,WHERE={'extension': str(member)}, SELECT=['extension','agentname'])
                            if users != None:
                                temp_member['name'] = users['agentname']

                            # account assign
                            aggregate_diallist = [
                                {
                                    "$match":
                                    {
                                        "createdAt": {'$gte': yesterdayTimeStamp, '$lte': endYesterdayTimeStamp},
                                        "assign": str(member),
                                    }
                                },{
                                    "$group":
                                    {
                                        "_id": 'null',
                                        "acc_arr": {'$addToSet': '$account_number'},
                                        "count_data": {'$sum': 1}
                                    }
                                }
                            ]
                            diallistData = mongodb.aggregate_pipeline(MONGO_COLLECTION=diallist_detail_collection,aggregate_pipeline=aggregate_diallist)
                            account_assign_arr = []
                            if diallistData != None:
                                for row in diallistData:
                                    temp_member['count_data']   = row['count_data']
                                    account_assign_arr          = row['acc_arr']

                            temp['account_assign_arr'] = account_assign_arr
                            
                            # unwork
                            aggregate_unwork = [
                                {
                                    "$match":
                                    {
                                        "createdAt": {'$gte': yesterdayTimeStamp, '$lte': endYesterdayTimeStamp},
                                        "assign": str(member),
                                        "tryCount" : {'$exists' : 'false'}
                                    }
                                },{
                                    "$group":
                                    {
                                        "_id": 'null',
                                        "count_unwork": {'$sum': 1},
                                    }
                                }
                            ]
                            unworkData = mongodb.aggregate_pipeline(MONGO_COLLECTION=diallist_detail_collection,aggregate_pipeline=aggregate_unwork)
                            if unworkData != None:
                                for row in unworkData:
                                    temp_member['unwork']            = row['count_unwork']

                            temp_member['work']            = float(temp_member['count_data']) - float(temp_member['unwork'])


                            # talk time
                            aggregate_cdr = [
                                {
                                    "$match":
                                    {
                                        "starttime" : {'$gte': yesterdayTimeStamp, '$lte': endYesterdayTimeStamp},
                                        "direction" : "outbound",
                                        "userextension": str(member),
                                    }
                                },{
                                    "$group":
                                    {
                                        "_id": 'null',
                                        "talk_time": {'$sum': '$billduration'},
                                    }
                                }
                            ]
                            cdrData = mongodb.aggregate_pipeline(MONGO_COLLECTION=cdr_collection,aggregate_pipeline=aggregate_cdr)
                            
                            disposition_arr = []
                            if cdrData != None:
                                for row in cdrData:
                                    temp_member['talk_time']            = row['talk_time']

                            # contacted
                            count_contacted = 0
                            customernumber_arr = []
                            for acc_assign in account_assign_arr:
                                diallistInfo = mongodb.getOne(MONGO_COLLECTION=diallist_detail_collection, WHERE={'account_number': str(acc_assign), "createdAt": {'$gte': yesterdayTimeStamp, '$lte': endYesterdayTimeStamp} },
                                                SELECT=['mobile_num','phone','other_phones'])

                                phone = []
                                cdrInfo = 0
                                if diallistInfo != None:
                                  for detail in diallistInfo:
                                    if 'mobile_num' in detail.keys():
                                      phone.append(detail['mobile_num'])
                                    if 'phone' in detail.keys():
                                      phone.append(detail['phone'])
                                    if 'other_phones' in detail.keys():
                                      phone += detail['other_phones']

                                    cdrInfo = mongodb.count(MONGO_COLLECTION=cdr_collection, WHERE={'customernumber': {'$in': phone}, "direction" : "outbound", "userextension": str(member), "starttime": {'$gte': yesterdayTimeStamp, '$lte': endYesterdayTimeStamp}} )
                                    if cdrInfo > 0:
                                      count_contacted += 1
                                      customernumber_arr.append(acc_assign)

                            temp_member['count_contacted']           = count_contacted

                            # call made
                            calMade = mongodb.count(MONGO_COLLECTION=cdr_collection, WHERE={'disposition': 'ANSWERED', "direction" : "outbound", "userextension": str(member), "starttime": {'$gte': yesterdayTimeStamp, '$lte': endYesterdayTimeStamp}} )
                            if calMade > 0:
                              temp_member['number_of_call'] = calMade

                            totalCall = mongodb.count(MONGO_COLLECTION=cdr_collection, WHERE={ "direction" : "outbound", "userextension": str(member), "starttime": {'$gte': yesterdayTimeStamp, '$lte': endYesterdayTimeStamp}} )
                            if totalCall > 0:
                              temp_member['total_call'] = totalCall


                            # connected
                            action_code = ['PTP', 'CHECK', 'LM', 'PTP Today']
                            aggregate_cdr_ans = [
                                {
                                    "$match":
                                    {
                                        "starttime": {'$gte': yesterdayTimeStamp, '$lte': endYesterdayTimeStamp},
                                        "userextension": str(member),
                                        "disposition" : 'ANSWERED',
                                        '$or' : [ { 'action_code' :  {'$in' : action_code}}, {'customer.action_code' :  {'$in' : action_code}}]
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
                            cdrAnsData = mongodb.aggregate_pipeline(MONGO_COLLECTION=cdr_collection,aggregate_pipeline=aggregate_cdr_ans)
                            phone_ans_arr = []
                            if cdrAnsData != None:
                                for row in cdrAnsData:
                                    acc_ans_arr            = row['acc_ans_arr']
                                    temp_member['count_conn'] = row['count_conn']

                            aggregate_cdr_ans_1 = [
                                {
                                    "$match":
                                    {
                                        "createdAt": {'$gte': yesterdayTimeStamp, '$lte': endYesterdayTimeStamp},
                                        "assign" : str(member)
                                        '$or' : [ { "mobile_num" : {'$in' : acc_ans_arr} }, {"phone" : {'$in' : acc_ans_arr} }, {"other_phones" : {'$in' : acc_ans_arr}}]
                                    }
                                },{
                                    "$group":
                                    {
                                        "_id": 'null',
                                        "account_ans_arr": {'$addToSet': '$customernumber'},
                                    }
                                }
                            ]
                            accountData = mongodb.aggregate_pipeline(MONGO_COLLECTION=diallist_detail_collection,aggregate_pipeline=aggregate_cdr_ans_1)
                            acc_ans_arr = []
                            if accountData != None:
                                for row in accountData:
                                    acc_ans_arr            = row['acc_ans_arr']



                            # PTP
                            aggregate_ptp = [
                                {
                                    "$match":
                                    {
                                        "createdAt": {'$gte': yesterdayTimeStamp, '$lte': endYesterdayTimeStamp},
                                        "account_number": {'$in': account_assign_arr},
                                        '$or' : [ { 'action_code' :  'PTP'}, {'action_code' :  'PTP Today'}]
                                    }
                                },{
                                    "$group":
                                    {
                                        "_id": 'null',
                                        "acc_arr": {'$addToSet': '$account_number'},
                                        "count_data": {'$sum': 1}
                                    }
                                }
                            ]
                            ptpData = mongodb.aggregate_pipeline(MONGO_COLLECTION=action_code_collection,aggregate_pipeline=aggregate_ptp)
                            account_ptp_arr = []
                            if ptpData != None:
                                for row in ptpData:
                                    account_ptp_arr                     = row['acc_arr']
                                    temp_member['count_ptp']            = row['count_data']

                            temp_member['account_ptp_today_arr'] = account_ptp_arr

                            

                            if groupProduct['value'] == 'SIBS':
                                aggregate_cdr_amt = [
                                    {
                                        "$match":
                                        {
                                            "account_number": {'$in': customernumber_arr},
                                        }
                                    },{
                                        "$group":
                                        {
                                            "_id": 'null',
                                            "contacted_amount": {'$sum': '$current_balance'},

                                        }
                                    }
                                ]
                                cdrAmountData = mongodb.aggregate_pipeline(MONGO_COLLECTION=lnjc05_yesterday_collection,aggregate_pipeline=aggregate_cdr_amt)
                                if cdrAmountData != None:
                                    for row in cdrAmountData:
                                        temp_member['contacted_amount']            = row['contacted_amount']

                                # amount of connected
                                aggregate_amt_ans = [
                                    {
                                        "$match":
                                        {
                                            "account_number": {'$in': acc_ans_arr},
                                        }
                                    },{
                                        "$group":
                                        {
                                            "_id": 'null',
                                            "conn_amount": {'$sum': '$current_balance'},
                                        }
                                    }
                                ]
                                amtAnsData = mongodb.aggregate_pipeline(MONGO_COLLECTION=lnjc05_yesterday_collection,aggregate_pipeline=aggregate_amt_ans)
                                if amtAnsData != None:
                                    for row in amtAnsData:
                                        temp_member['conn_amount']            = row['conn_amount']

                                # PTP amount
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
                                ptpAmtData = mongodb.aggregate_pipeline(MONGO_COLLECTION=lnjc05_yesterday_collection,aggregate_pipeline=aggregate_ptp_amt)
                                if ptpAmtData != None:
                                    for row in ptpAmtData:
                                        temp_member['ptp_amount']           = row['ptp_amount']


                                # PTP paid 
                                aggregate_paid_promise = [
                                    {
                                        "$match":
                                        {
                                            "created_at": {'$gte': todayTimeStamp, '$lte': endTodayTimeStamp},
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
                                paidPromiseData = mongodb.aggregate_pipeline(MONGO_COLLECTION=ln3206f_collection,aggregate_pipeline=aggregate_paid_promise)
                                if paidPromiseData != None:
                                    for row in paidPromiseData:
                                        temp_member['count_paid_promise']            = len(row['count_paid_promise'] )
                                        temp_member['paid_amount_promise']           = row['paid_amount_promise']



                                # paid
                                aggregate_paid = [
                                    {
                                        "$match":
                                        {
                                            "created_at": {'$gte': todayTimeStamp, '$lte': endTodayTimeStamp},
                                            "account_number": {'$in': account_assign_arr},
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
                                paidPromiseData = mongodb.aggregate_pipeline(MONGO_COLLECTION=ln3206f_collection,aggregate_pipeline=aggregate_paid)
                                if paidPromiseData != None:
                                    for row in paidPromiseData:
                                        temp_member['count_paid']            = len(row['count_paid'] )
                                        temp_member['paid_amount']           = row['paid_amount']









                            if groupProduct['value'] == 'Card':
                                aggregate_cdr_amt = [
                                    {
                                        "$match":
                                        {
                                            "account_number": {'$in': customernumber_arr},
                                        }
                                    },{
                                        "$group":
                                        {
                                            "_id": 'null',
                                            "contacted_amount": {'$sum': '$cur_bal'},

                                        }
                                    }
                                ]
                                cdrAmountData = mongodb.aggregate_pipeline(MONGO_COLLECTION=list_of_account_yesterday_collection,aggregate_pipeline=aggregate_cdr_amt)
                                if cdrAmountData != None:
                                    for row in cdrAmountData:
                                        temp_member['contacted_amount']            = row['contacted_amount']

                               
                                # amount of connected
                                aggregate_amt_ans = [
                                    {
                                        "$match":
                                        {
                                            "account_number": {'$in': acc_ans_arr},
                                        }
                                    },{
                                        "$group":
                                        {
                                            "_id": 'null',
                                            "conn_amount": {'$sum': '$cur_bal'},
                                        }
                                    }
                                ]
                                amtAnsData = mongodb.aggregate_pipeline(MONGO_COLLECTION=list_of_account_yesterday_collection,aggregate_pipeline=aggregate_amt_ans)
                                if amtAnsData != None:
                                    for row in amtAnsData:
                                        temp_member['conn_amount']            = row['conn_amount']


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
                                ptpAmtData = mongodb.aggregate_pipeline(MONGO_COLLECTION=list_of_account_yesterday_collection,aggregate_pipeline=aggregate_ptp_amt)
                                if ptpAmtData != None:
                                    for row in ptpAmtData:
                                        temp_member['ptp_amount']           = row['ptp_amount']

                                aggregate_paid_promise = [
                                    {
                                        "$match":
                                        {
                                            "created_at": {'$gte': todayTimeStamp, '$lte': endTodayTimeStamp},
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
                                paidPromiseData = mongodb.aggregate_pipeline(MONGO_COLLECTION=gl_collection,aggregate_pipeline=aggregate_paid_promise)
                                if paidPromiseData != None:
                                    for row in paidPromiseData:
                                        temp_member['count_paid_promise']            = len(row['count_paid_promise'] )
                                        temp_member['paid_amount_promise']           = row['paid_amount_promise']

                                # paid
                                aggregate_paid = [
                                    {
                                        "$match":
                                        {
                                            "created_at": {'$gte': todayTimeStamp, '$lte': endTodayTimeStamp},
                                            "account_number": {'$in': account_assign_arr},
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
                                paidPromiseData = mongodb.aggregate_pipeline(MONGO_COLLECTION=gl_collection,aggregate_pipeline=aggregate_paid)
                                if paidPromiseData != None:
                                    for row in paidPromiseData:
                                        temp_member['count_paid']            = len(row['count_paid'] )
                                        temp_member['paid_amount']           = row['paid_amount']


                            temp['count_data']      += temp_member['count_data'];
                            temp['unwork']          += temp_member['unwork'];
                            temp['work']            += temp_member['work'];
                            temp['talk_time']       += temp_member['talk_time'];
                            temp['count_contacted'] += temp_member['count_contacted'];
                            temp['contacted_amount']    += temp_member['contacted_amount'];
                            temp['conn_amount']     += temp_member['conn_amount'];
                            temp['count_conn']      += temp_member['count_conn'];
                            temp['number_of_call']  += temp_member['number_of_call'];
                            temp['total_call']      += temp_member['total_call'];
                            temp['count_ptp']       += temp_member['count_ptp'];
                            temp['ptp_amount']      += temp_member['ptp_amount'];
                            temp['count_paid']      += temp_member['count_paid'];
                            temp['paid_amount']     += temp_member['paid_amount'];
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
                            temp_member['createdAt'] = todayTimeStamp
                            temp_member['createdBy'] = 'system'
                            temp_member['for_month'] = month

                            pprint(temp_member)
                            member_arr.append(temp_member)

                        temp['spin_rate']     = temp['count_spin']/temp['total_call']  if temp['total_call'] != 0 else 0
                        temp['ptp_rate_acc']  = temp['count_ptp']/temp['total_call']  if temp['total_call'] != 0 else 0
                        temp['ptp_rate_amt']  = temp['ptp_amount']/temp['total_amount']  if temp['total_amount'] != 0 else 0
                        temp['paid_rate_acc'] = temp['count_paid_promise']/temp['count_ptp']  if temp['count_ptp'] != 0 else 0
                        temp['paid_rate_amt'] = temp['paid_amount_promise']/temp['ptp_amount']  if temp['ptp_amount'] != 0 else 0
                        temp['conn_rate']     = temp['count_conn']/temp['total_call'] if temp['total_call'] != 0 else 0
                        temp['collect_ratio_acc'] = temp['count_paid']/temp['total_call']  if temp['total_call'] != 0 else 0
                        temp['collect_ratio_amt'] = temp['paid_amount']/temp['total_amount']  if temp['total_amount'] != 0 else 0
                        temp['createdAt'] = todayTimeStamp
                        temp['createdBy'] = 'system'
                        temp['for_month'] = month


                        insertData.append(temp)
                        insertData += member_arr;

                        i += 1


            # checkGroupA = 'true'


                checkGroupA = 'true'
                # break
        

        # if debtGroupCell[0:1] != 'A' and debtGroupCell[0:1] != 'F':
        #     i = 1
        #     dueDayOfMonth = mongodb.getOne(MONGO_COLLECTION=report_due_date_collection, WHERE={'for_month': str(month), 'debt_group': debtGroupCell[1:3]})
        #     for groupProduct in list(listGroupProduct):
        #         teams = mongodb.getOne(MONGO_COLLECTION=group_collection, WHERE={'name' : {'$regex' : groupProduct['value']} , 'debt_groups': debtGroupCell[0:3]})
        #         # print(debtGroupCell[0:3])
        #         if teams != None:
        #             temp = {
        #                 'name'           : teams['name'],
        #                 'group'          : debtGroupCell[0:1],
        #                 'team'           : i,
        #                 'date'           : todayTimeStamp,
        #                 'extension'      : teams['lead'],
        #                 'team_lead'      : 'true',
        #                 'count_data'     : 0,
        #                 'unwork'            : 0,
        #                 'talk_time'         : 0,
        #                 'total_call'        : 0,
        #                 'total_amount'      : 0,
        #                 'count_spin'        : 0,
        #                 'spin_amount'       : 0,
        #                 'count_conn'        : 0,
        #                 'conn_amount'       : 0,
        #                 'count_paid'        : 0,
        #                 'paid_amount'       : 0,
        #                 'ptp_amount'        : 0,
        #                 'count_ptp'         : 0,
        #                 'paid_amount_promise'       : 0,
        #                 'count_paid_promise'        : 0,
        #             }
        #             if todayTimeStamp < dueDayOfMonth['due_date_add_1']:
        #                 dueDayLastMonth = mongodb.getOne(MONGO_COLLECTION=report_due_date_collection, WHERE={'for_month': str(month - 1), 'debt_group': debtGroupCell[1:3]})
        #                 temp['due_date'] = dueDayLastMonth['due_date'] if dueDayLastMonth is not None else ''
        #                 #Lay gia tri no vao ngay due date + 1#
        #                 # incidenceInfo = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Due_date_next_date'), WHERE={'for_month': str(month - 1),  'debt_group': debtGroupCell[0:1],'due_date_code': debtGroupCell[1:3],'product':groupProduct['text']})
        #                 #Lay gia tri no vao ngay due date + 1#
        #             else:
        #                 temp['due_date'] = dueDayOfMonth['due_date']
        #                 # incidenceInfo = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Due_date_next_date'), WHERE={'for_month': str(month), 'debt_group': debtGroupCell[0:1],'due_date_code': debtGroupCell[1:3],'product':groupProduct['text']})


        #             # members
        #             member_arr = []
        #             count_member = len(teams['members'])
        #             for member in list(teams['members']):
        #                 temp_member = {
        #                     'name'           : '',
        #                     'group'          : debtGroupCell[0:1],
        #                     'team'           : i,
        #                     'date'           : todayTimeStamp,
        #                     'extension'      : member,
        #                     'count_data'     : 0,
        #                     'unwork'            : 0,
        #                     'talk_time'         : 0,
        #                     'total_call'        : 0,
        #                     'total_amount'      : 0,
        #                     'count_spin'        : 0,
        #                     'spin_amount'       : 0,
        #                     'count_conn'        : 0,
        #                     'conn_amount'       : 0,
        #                     'count_paid'        : 0,
        #                     'paid_amount'       : 0,
        #                     'ptp_amount'        : 0,
        #                     'count_ptp'         : 0,
        #                     'paid_amount_promise'       : 0,
        #                     'count_paid_promise'        : 0,
        #                 }
        #                 users = _mongodb.getOne(MONGO_COLLECTION=user_collection,WHERE={'extension': str(member)}, SELECT=['extension','agentname'])
        #                 if users != None:
        #                     temp_member['name'] = users['agentname']

                        
        #                 aggregate_diallist = [
        #                     {
        #                         "$match":
        #                         {
        #                             "createdAt": {'$gte': todayTimeStamp, '$lte': endTodayTimeStamp},
        #                             "assign": str(member),
        #                         }
        #                     },{
        #                         "$group":
        #                         {
        #                             "_id": 'null',
        #                             "acc_arr": {'$addToSet': '$account_number'},
        #                             "count_data": {'$sum': 1}
        #                         }
        #                     }
        #                 ]
        #                 diallistData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'Diallist_detail'),aggregate_pipeline=aggregate_diallist)
        #                 acc_arr = []
        #                 if diallistData != None:
        #                     for row in diallistData:
        #                         temp_member['count_data']   = row['count_data']
        #                         acc_arr          = row['acc_arr']
                       
        #                 aggregate_unwork = [
        #                     {
        #                         "$match":
        #                         {
        #                             "createdAt": {'$gte': todayTimeStamp, '$lte': endTodayTimeStamp},
        #                             "assign": str(member),
        #                             "tryCount" : {'$exists' : 'false'}
        #                         }
        #                     },{
        #                         "$group":
        #                         {
        #                             "_id": 'null',
        #                             "count_unwork": {'$sum': 1},
        #                         }
        #                     }
        #                 ]
        #                 unworkData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'Diallist_detail'),aggregate_pipeline=aggregate_unwork)
        #                 if unworkData != None:
        #                     for row in unworkData:
        #                         temp_member['unwork']            = row['count_unwork']

        #                 aggregate_ptp = [
        #                     {
        #                         "$match":
        #                         {
        #                             "createdAt": {'$gte': todayTimeStamp, '$lte': endTodayTimeStamp},
        #                             "account_number": {'$in': acc_arr},
        #                             '$or' : [ { 'action_code' :  'PTP'}, {'action_code' :  'PTP Today'}]
        #                         }
        #                     },{
        #                         "$group":
        #                         {
        #                             "_id": 'null',
        #                             "acc_arr": {'$addToSet': '$account_number'},
        #                             "count_data": {'$sum': 1}
        #                         }
        #                     }
        #                 ]
        #                 ptpData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'Action_code'),aggregate_pipeline=aggregate_ptp)
        #                 account_ptp_arr = []
        #                 if ptpData != None:
        #                     for row in ptpData:
        #                         account_ptp_arr           = row['acc_arr']
        #                         temp_member['count_ptp']  = row['count_data']


        #                 aggregate_cdr = [
        #                     {
        #                         "$match":
        #                         {
        #                             "starttime": {'$gte': todayTimeStamp, '$lte': endTodayTimeStamp},
        #                             "userextension": str(member),
        #                         }
        #                     },{
        #                         "$group":
        #                         {
        #                             "_id": 'null',
        #                             "talk_time": {'$sum': '$billduration'},
        #                             "total_call": {'$sum': 1},
        #                             "customernumber_arr": {'$addToSet': '$customernumber'},
        #                             "phone_arr": {'$push': '$customernumber'},
        #                         }
        #                     }
        #                 ]
        #                 cdrData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'worldfonepbxmanager'),aggregate_pipeline=aggregate_cdr)
        #                 customernumber_arr = []
        #                 phone_arr = []
        #                 disposition_arr = []
        #                 if cdrData != None:
        #                     for row in cdrData:
        #                         temp_member['talk_time']            = row['talk_time']
        #                         temp_member['total_call']           = row['total_call']
        #                         customernumber_arr                  = row['customernumber_arr']
        #                         phone_arr                           = row['phone_arr']


        #                 # spin
        #                 count_spin = 0
        #                 account_spin_arr = []
        #                 for account in acc_arr:
        #                     spinData = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Diallist_detail'),WHERE={"createdAt": {'$gte': todayTimeStamp, '$lte': endTodayTimeStamp}, "account_number": str(account), "callResult": {'$exists' :  'true'}},SELECT=['callResult'])
        #                     call_number = []
        #                     if spinData != None:
        #                         for call in list(spinData['callResult']):
        #                             call_number.append(call['customernumber'])
        #                         list_call = set(call_number) 
        #                         unique_call = list(list_call)
        #                         if len(unique_call) > 1:
        #                             count_spin += 1
        #                             account_spin_arr.append(account)
                        
        #                 temp_member['count_spin'] = count_spin



        #                  # connected
        #                 aggregate_cdr_ans = [
        #                     {
        #                         "$match":
        #                         {
        #                             "starttime": {'$gte': todayTimeStamp, '$lte': endTodayTimeStamp},
        #                             "userextension": str(member),
        #                             "disposition" : 'ANSWERED'
        #                         }
        #                     },{
        #                         "$group":
        #                         {
        #                             "_id": 'null',
        #                             "count_conn": {'$sum': 1},
        #                             "phone_ans_arr": {'$addToSet': '$customernumber'},
        #                         }
        #                     }
        #                 ]
        #                 cdrAnsData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'worldfonepbxmanager'),aggregate_pipeline=aggregate_cdr_ans)
        #                 phone_ans_arr = []
        #                 if cdrAnsData != None:
        #                     for row in cdrAnsData:
        #                         phone_ans_arr            = row['phone_ans_arr']
        #                         temp_member['count_conn'] = row['count_conn']


        #                 temp_member['unwork'] = len(set(customernumber_arr)^set(phone_ans_arr))

        #                 if groupProduct['value'] == 'SIBS':
        #                     aggregate_ptp_amt = [
        #                         {
        #                             "$match":
        #                             {
        #                                 "account_number": {'$in': account_ptp_arr},
        #                             }
        #                         },{
        #                             "$group":
        #                             {
        #                                 "_id": 'null',
        #                                 "ptp_amount": {'$sum': '$current_balance'},
        #                             }
        #                         }
        #                     ]
        #                     ptpAmtData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'LNJC05'),aggregate_pipeline=aggregate_ptp_amt)
        #                     if ptpAmtData != None:
        #                         for row in ptpAmtData:
        #                             # temp_member['count_ptp']            = len(account_ptp_arr)
        #                             temp_member['ptp_amount']           = row['ptp_amount']

        #                     aggregate_paid_promise = [
        #                         {
        #                             "$match":
        #                             {
        #                                 "created_at": {'$gte': todayTimeStamp, '$lte': endTodayTimeStamp},
        #                                 "account_number": {'$in': account_ptp_arr},
        #                             }
        #                         },{
        #                             "$group":
        #                             {
        #                                 "_id": 'null',
        #                                 "paid_amount_promise": {'$sum': '$amt'},
        #                                 "count_paid_promise": {'$addToSet': '$account_number'},
        #                             }
        #                         }
        #                     ]
        #                     paidPromiseData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'LN3206F'),aggregate_pipeline=aggregate_paid_promise)
        #                     if paidPromiseData != None:
        #                         for row in paidPromiseData:
        #                             temp_member['count_paid_promise']            = len(row['count_paid_promise'] )
        #                             temp_member['paid_amount_promise']           = row['paid_amount_promise']


        #                     # paid
        #                     aggregate_paid = [
        #                         {
        #                             "$match":
        #                             {
        #                                 "created_at": {'$gte': todayTimeStamp, '$lte': endTodayTimeStamp},
        #                                 "account_number": {'$in': acc_arr},
        #                             }
        #                         },{
        #                             "$group":
        #                             {
        #                                 "_id": 'null',
        #                                 "paid_amount": {'$sum': '$amt'},
        #                                 "count_paid": {'$addToSet': '$account_number'},
        #                             }
        #                         }
        #                     ]
        #                     paidPromiseData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'LN3206F'),aggregate_pipeline=aggregate_paid)
        #                     if paidPromiseData != None:
        #                         for row in paidPromiseData:
        #                             temp_member['count_paid']            = len(row['count_paid'] )
        #                             temp_member['paid_amount']           = row['paid_amount']

        #                     aggregate_cdr_amt = [
        #                         {
        #                             "$match":
        #                             {
        #                                 "mobile_num": {'$in': customernumber_arr},
        #                             }
        #                         },{
        #                             "$group":
        #                             {
        #                                 "_id": 'null',
        #                                 "total_amount": {'$sum': '$current_balance'},

        #                             }
        #                         }
        #                     ]
        #                     cdrAmountData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'LNJC05'),aggregate_pipeline=aggregate_cdr_amt)
        #                     if cdrAmountData != None:
        #                         for row in cdrAmountData:
        #                             temp_member['total_amount']            = row['total_amount']

        #                     # spin
        #                     aggregate_spin = [
        #                         {
        #                             "$match":
        #                             {
        #                                 "account_number": {'$in': account_spin_arr},
        #                             }
        #                         },{
        #                             "$group":
        #                             {
        #                                 "_id": 'null',
        #                                 "spin_amount": {'$sum': '$current_balance'},

        #                             }
        #                         }
        #                     ]
        #                     spinData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'LNJC05'),aggregate_pipeline=aggregate_spin)
        #                     if spinData != None:
        #                         for row in spinData:
        #                             temp_member['spin_amount']            = row['spin_amount']

        #                     aggregate_amt_ans = [
        #                         {
        #                             "$match":
        #                             {
        #                                 "mobile_num": {'$in': phone_ans_arr},
        #                             }
        #                         },{
        #                             "$group":
        #                             {
        #                                 "_id": 'null',
        #                                 "conn_amount": {'$sum': '$current_balance'},
        #                             }
        #                         }
        #                     ]
        #                     amtAnsData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'LNJC05'),aggregate_pipeline=aggregate_amt_ans)
        #                     if amtAnsData != None:
        #                         for row in amtAnsData:
        #                             temp_member['conn_amount']            = row['conn_amount']

        #                 if groupProduct['value'] == 'Card':
        #                     aggregate_ptp_amt = [
        #                         {
        #                             "$match":
        #                             {
        #                                 "account_number": {'$in': account_ptp_arr},
        #                             }
        #                         },{
        #                             "$group":
        #                             {
        #                                 "_id": 'null',
        #                                 "ptp_amount": {'$sum': '$cur_bal'},
        #                             }
        #                         }
        #                     ]
        #                     ptpAmtData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'List_of_account_in_collection'),aggregate_pipeline=aggregate_ptp_amt)
        #                     if ptpAmtData != None:
        #                         for row in ptpAmtData:
        #                             # temp_member['count_ptp']            = len(account_ptp_arr)
        #                             temp_member['ptp_amount']           = row['ptp_amount']

        #                     aggregate_paid_promise = [
        #                         {
        #                             "$match":
        #                             {
        #                                 "created_at": {'$gte': todayTimeStamp, '$lte': endTodayTimeStamp},
        #                                 "account_number": {'$in': account_ptp_arr},
        #                             }
        #                         },{
        #                             "$group":
        #                             {
        #                                 "_id": 'null',
        #                                 "paid_amount_promise": {'$sum': '$amount'},
        #                                 "count_paid_promise": {'$addToSet': '$account_number'},
        #                             }
        #                         }
        #                     ]
        #                     paidPromiseData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_input_payment_of_card'),aggregate_pipeline=aggregate_paid_promise)
        #                     if paidPromiseData != None:
        #                         for row in paidPromiseData:
        #                             temp_member['count_paid_promise']            = len(row['count_paid_promise'] )
        #                             temp_member['paid_amount_promise']           = row['paid_amount_promise']


        #                     # paid
        #                     aggregate_paid = [
        #                         {
        #                             "$match":
        #                             {
        #                                 "created_at": {'$gte': todayTimeStamp, '$lte': endTodayTimeStamp},
        #                                 "account_number": {'$in': acc_arr},
        #                             }
        #                         },{
        #                             "$group":
        #                             {
        #                                 "_id": 'null',
        #                                 "paid_amount": {'$sum': '$amount'},
        #                                 "count_paid": {'$addToSet': '$account_number'},
        #                             }
        #                         }
        #                     ]
        #                     paidPromiseData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_input_payment_of_card'),aggregate_pipeline=aggregate_paid)
        #                     if paidPromiseData != None:
        #                         for row in paidPromiseData:
        #                             temp_member['count_paid']            = len(row['count_paid'] )
        #                             temp_member['paid_amount']           = row['paid_amount']

        #                     aggregate_cdr_amt = [
        #                         {
        #                             "$match":
        #                             {
        #                                 "phone": {'$in': customernumber_arr},
        #                             }
        #                         },{
        #                             "$group":
        #                             {
        #                                 "_id": 'null',
        #                                 "total_amount": {'$sum': '$cur_bal'},
        #                             }
        #                         }
        #                     ]
        #                     cdrAmountData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'List_of_account_in_collection'),aggregate_pipeline=aggregate_cdr_amt)
        #                     if cdrAmountData != None:
        #                         for row in cdrAmountData:
        #                             temp_member['total_amount']            = row['total_amount']

        #                     # spin
        #                     aggregate_spin = [
        #                         {
        #                             "$match":
        #                             {
        #                                 "account_number": {'$in': account_spin_arr},
        #                             }
        #                         },{
        #                             "$group":
        #                             {
        #                                 "_id": 'null',
        #                                 "spin_amount": {'$sum': '$cur_bal'},

        #                             }
        #                         }
        #                     ]
        #                     spinData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'List_of_account_in_collection'),aggregate_pipeline=aggregate_spin)
        #                     if spinData != None:
        #                         for row in spinData:
        #                             temp_member['spin_amount']            = row['spin_amount']

        #                     aggregate_amt_ans = [
        #                         {
        #                             "$match":
        #                             {
        #                                 "phone": {'$in': phone_ans_arr},
        #                             }
        #                         },{
        #                             "$group":
        #                             {
        #                                 "_id": 'null',
        #                                 "conn_amount": {'$sum': '$cur_bal'},
        #                             }
        #                         }
        #                     ]
        #                     amtAnsData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'List_of_account_in_collection'),aggregate_pipeline=aggregate_amt_ans)
        #                     if amtAnsData != None:
        #                         for row in amtAnsData:
        #                             temp_member['conn_amount']            = row['conn_amount']


        #                 temp['count_data']      += temp_member['count_data'];
        #                 temp['unwork']          += temp_member['unwork'];
        #                 temp['talk_time']       += temp_member['talk_time'];
        #                 temp['total_call']      += temp_member['total_call'];
        #                 temp['total_amount']    += temp_member['total_amount'];
        #                 temp['conn_amount']     += temp_member['conn_amount'];
        #                 temp['count_conn']      += temp_member['count_conn'];
        #                 temp['spin_amount']     += temp_member['spin_amount'];
        #                 temp['count_spin']      += temp_member['count_spin'];
        #                 temp['count_ptp']       += temp_member['count_ptp'];
        #                 temp['ptp_amount']      += temp_member['ptp_amount'];
        #                 temp['count_paid']      += temp_member['count_paid'];
        #                 temp['paid_amount']     += temp_member['paid_amount'];
        #                 temp['count_paid_promise']      += temp_member['count_paid_promise'];
        #                 temp['paid_amount_promise']     += temp_member['count_paid_promise'];


        #                 temp_member['spin_rate']     = temp_member['count_spin']/temp_member['total_call'] if temp_member['total_call'] != 0 else 0
        #                 temp_member['ptp_rate_acc']  = temp_member['count_ptp']/temp_member['total_call'] if temp_member['total_call'] != 0 else 0
        #                 temp_member['ptp_rate_amt']  = temp_member['ptp_amount']/temp_member['total_amount'] if temp_member['total_amount'] != 0  else 0
        #                 temp_member['paid_rate_acc'] = temp_member['count_paid_promise']/temp_member['count_ptp'] if temp_member['count_ptp'] != 0 else 0
        #                 temp_member['paid_rate_amt'] = temp_member['paid_amount_promise']/temp_member['ptp_amount'] if temp_member['ptp_amount'] != 0 else 0
        #                 temp_member['conn_rate']     = temp_member['count_conn']/temp_member['total_call'] if temp_member['total_call'] != 0 else 0
        #                 temp_member['collect_ratio_acc'] = temp_member['count_paid']/temp_member['total_call'] if temp_member['total_call'] != 0 else 0
        #                 temp_member['collect_ratio_amt'] = temp_member['paid_amount']/temp_member['total_amount'] if temp_member['total_amount'] != 0 else 0
        #                 temp_member['createdAt'] = todayTimeStamp
        #                 temp_member['createdBy'] = 'system'
        #                 temp_member['for_month'] = month

        #                 member_arr.append(temp_member)

        #             temp['spin_rate']     = temp['count_spin']/temp['total_call']  if temp['total_call'] != 0 else 0
        #             temp['ptp_rate_acc']  = temp['count_ptp']/temp['total_call']  if temp['total_call'] != 0 else 0
        #             temp['ptp_rate_amt']  = temp['ptp_amount']/temp['total_amount']  if temp['total_amount'] != 0 else 0
        #             temp['paid_rate_acc'] = temp['count_paid_promise']/temp['count_ptp']  if temp['count_ptp'] != 0 else 0
        #             temp['paid_rate_amt'] = temp['paid_amount_promise']/temp['ptp_amount']  if temp['ptp_amount'] != 0 else 0
        #             temp['conn_rate']     = temp['count_conn']/temp['total_call'] if temp['total_call'] != 0 else 0
        #             temp['collect_ratio_acc'] = temp['count_paid']/temp['total_call']  if temp['total_call'] != 0 else 0
        #             temp['collect_ratio_amt'] = temp['paid_amount']/temp['total_amount']  if temp['total_amount'] != 0 else 0
        #             temp['createdAt'] = todayTimeStamp
        #             temp['createdBy'] = 'system'
        #             temp['for_month'] = month

        #             insertData.append(temp)
        #             insertData += member_arr;

        #             i += 1


    


    # # WO

    # teams = mongodb.getOne(MONGO_COLLECTION=group_collection, WHERE={'name' : {'$regex' : 'WO'} , 'debt_groups': 'F01'})
    # if teams != None:
    #     # temp = {
    #     #     'name'           : teams['name'],
    #     #     'group'          : debtGroupCell[0:1],
    #     #     'team'           : i,
    #     #     'date'           : todayTimeStamp,
    #     #     'extension'      : teams['lead'],
    #     #     'team_lead'      : 'true',
    #     #     'count_data'     : 0,
    #     #     'unwork'            : 0,
    #     #     'talk_time'         : 0,
    #     #     'total_call'        : 0,
    #     #     'total_amount'      : 0,
    #     #     'count_spin'        : 0,
    #     #     'spin_amount'       : 0,
    #     #     'count_conn'        : 0,
    #     #     'conn_amount'       : 0,
    #     #     'count_paid'        : 0,
    #     #     'paid_amount'       : 0,
    #     #     'ptp_amount'        : 0,
    #     #     'count_ptp'         : 0,
    #     #     'paid_amount_promise'       : 0,
    #     #     'count_paid_promise'        : 0,
    #     # }


    #     # members
    #     i = 1
    #     member_arr = []
    #     for member in list(teams['members']):
    #         temp_member = {
    #             'name'           : '',
    #             'group'          : 'F',
    #             'team'           : i,
    #             'date'           : todayTimeStamp,
    #             'extension'      : member,
    #             'count_data'     : 0,
    #             'unwork'            : 0,
    #             'talk_time'         : 0,
    #             'total_call'        : 0,
    #             'total_amount'      : 0,
    #             'count_spin'        : 0,
    #             'spin_amount'       : 0,
    #             'count_conn'        : 0,
    #             'conn_amount'       : 0,
    #             'count_paid'        : 0,
    #             'paid_amount'       : 0,
    #             'ptp_amount'        : 0,
    #             'count_ptp'         : 0,
    #             'paid_amount_promise'       : 0,
    #             'count_paid_promise'        : 0,
    #         }
    #         users = _mongodb.getOne(MONGO_COLLECTION=user_collection,WHERE={'extension': str(member)}, SELECT=['extension','agentname'])
    #         if users != None:
    #             temp_member['name'] = users['agentname']

    #         # incidenceInfo = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Due_date_next_date'), WHERE={'for_month': str(month), 'debt_group': 'F','product':'WO'})

    #         acc_arr = []
    #         # if incidenceInfo is not None:
    #         #     for inc in incidenceInfo:
    #         #         temp_member['count_data'] += inc['debt_acc_no']
    #         #         acc_arr_1 = inc['acc_arr'] if 'acc_arr' in inc.keys() else []
    #         #         acc_arr += acc_arr_1
    #         #         temp_member['due_date'] = inc['created_at']

    #         aggregate_unwork = [
    #             {
    #                 "$match":
    #                 {
    #                     "createdAt": {'$gte': todayTimeStamp, '$lte': endTodayTimeStamp},
    #                     "assign": str(member),
    #                     "tryCount" : {'$exists' : 'false'}
    #                 }
    #             },{
    #                 "$group":
    #                 {
    #                     "_id": 'null',
    #                     "count_unwork": {'$sum': 1},
    #                 }
    #             }
    #         ]
    #         unworkData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'Diallist_detail'),aggregate_pipeline=aggregate_unwork)
    #         if unworkData != None:
    #             for row in unworkData:
    #                 temp_member['unwork']            = row['count_unwork']

    #         aggregate_ptp = [
    #             {
    #                 "$match":
    #                 {
    #                     "createdAt": {'$gte': todayTimeStamp, '$lte': endTodayTimeStamp},
    #                     "account_number": {'$in': acc_arr},
    #                     '$or' : [ { 'action_code' :  'PTP'}, {'action_code' :  'PTP Today'}]
    #                 }
    #             },{
    #                 "$group":
    #                 {
    #                     "_id": 'null',
    #                     "acc_arr": {'$addToSet': '$account_number'},
    #                 }
    #             }
    #         ]
    #         ptpData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'Action_code'),aggregate_pipeline=aggregate_ptp)
    #         account_ptp_arr = []
    #         if ptpData != None:
    #             for row in ptpData:
    #                 account_ptp_arr           = row['acc_arr']

    #         aggregate_cdr = [
    #             {
    #                 "$match":
    #                 {
    #                     "starttime": {'$gte': todayTimeStamp, '$lte': endTodayTimeStamp},
    #                     "userextension": str(member),
    #                 }
    #             },{
    #                 "$group":
    #                 {
    #                     "_id": 'null',
    #                     "talk_time": {'$sum': '$billduration'},
    #                     "total_call": {'$sum': 1},
    #                     "customernumber_arr": {'$addToSet': '$customernumber'},
    #                     "phone_arr": {'$push': '$customernumber'},
    #                 }
    #             }
    #         ]
    #         cdrData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'worldfonepbxmanager'),aggregate_pipeline=aggregate_cdr)
    #         customernumber_arr = []
    #         phone_arr = []
    #         disposition_arr = []
    #         if cdrData != None:
    #             for row in cdrData:
    #                 temp_member['talk_time']            = row['talk_time']
    #                 temp_member['total_call']           = row['total_call']
    #                 phone_arr                           = row['phone_arr']

    #          # connected
    #         aggregate_cdr_ans = [
    #             {
    #                 "$match":
    #                 {
    #                     "starttime": {'$gte': todayTimeStamp, '$lte': endTodayTimeStamp},
    #                     "userextension": str(member),
    #                     "disposition" : 'ANSWERED'
    #                 }
    #             },{
    #                 "$group":
    #                 {
    #                     "_id": 'null',
    #                     "count_conn": {'$sum': 1},
    #                     "phone_ans_arr": {'$addToSet': '$customernumber'},
    #                 }
    #             }
    #         ]
    #         cdrAnsData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'worldfonepbxmanager'),aggregate_pipeline=aggregate_cdr_ans)
    #         phone_ans_arr = []
    #         if cdrAnsData != None:
    #             for row in cdrAnsData:
    #                 phone_ans_arr            = row['phone_ans_arr']
    #                 temp_member['count_conn'] = row['count_conn']


    #         # if groupProduct['value'] == 'SIBS':
    #         aggregate_ptp_amt = [
    #             {
    #                 "$match":
    #                 {
    #                     "ACCTNO": {'$in': account_ptp_arr},
    #                 }
    #             },{
    #                 '$project':
    #                 {
    #                     'pay_payment': {'$sum' : [ '$WO9711', '$WO9712' ,'$WO9713'] }
    #                 }
    #             },{
    #                 "$group":
    #                 {
    #                     "_id": 'null',
    #                     "ptp_amount": {'$sum': '$pay_payment'},
    #                 }
    #             }
    #         ]
    #         ptpAmtData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'WO_monthly'),aggregate_pipeline=aggregate_ptp_amt)
    #         if ptpAmtData != None:
    #             for row in ptpAmtData:
    #                 temp_member['count_ptp']            = len(account_ptp_arr)
    #                 temp_member['ptp_amount']           = row['ptp_amount']

    #         aggregate_paid_promise = [
    #             {
    #                 "$match":
    #                 {
    #                     "created_at": {'$gte': todayTimeStamp, '$lte': endTodayTimeStamp},
    #                     "account_number": {'$in': account_ptp_arr},
    #                 }
    #             },{
    #                 '$project':
    #                 {
    #                     'account_number' : 1,
    #                     'pay_payment': {'$sum' : [ '$pay_9711', '$pay_9712' ,'$late_charge_9713'] }
    #                 }
    #             },{
    #                 "$group":
    #                 {
    #                     "_id": 'null',
    #                     "paid_amount_promise": {'$sum': '$pay_payment'},
    #                     "count_paid_promise": {'$addToSet': '$account_number'},
    #                 }
    #             }
    #         ]
    #         paidPromiseData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'Wo_payment'),aggregate_pipeline=aggregate_paid_promise)
    #         if paidPromiseData != None:
    #             for row in paidPromiseData:
    #                 temp_member['count_paid_promise']            = len(row['count_paid_promise'] )
    #                 temp_member['paid_amount_promise']           = row['paid_amount_promise']


    #         # paid
    #         aggregate_paid = [
    #             {
    #                 "$match":
    #                 {
    #                     "created_at": {'$gte': todayTimeStamp, '$lte': endTodayTimeStamp},
    #                     "account_number": {'$in': acc_arr},
    #                 }
    #             },{
    #                 '$project':
    #                 {
    #                     'account_number' : 1,
    #                     'pay_payment': {'$sum' : [ '$pay_9711', '$pay_9712' ,'$late_charge_9713'] }
    #                 }
    #             },{
    #                 "$group":
    #                 {
    #                     "_id": 'null',
    #                     "paid_amount": {'$sum': '$pay_payment'},
    #                     "count_paid": {'$addToSet': '$account_number'},
    #                 }
    #             }
    #         ]
    #         paidPromiseData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'Wo_payment'),aggregate_pipeline=aggregate_paid)
    #         if paidPromiseData != None:
    #             for row in paidPromiseData:
    #                 temp_member['count_paid']            = len(row['count_paid'] )
    #                 temp_member['paid_amount']           = row['paid_amount']

    #         aggregate_cdr_amt = [
    #             {
    #                 "$match":
    #                 {
    #                     "PHONE": {'$in': customernumber_arr},
    #                 }
    #             },{
    #                 '$project':
    #                 {
    #                     'pay_payment': {'$sum' : [ '$WO9711', '$WO9712' ,'$WO9713'] }
    #                 }
    #             },{
    #                 "$group":
    #                 {
    #                     "_id": 'null',
    #                     "total_amount": {'$sum': '$pay_payment'},

    #                 }
    #             }
    #         ]
    #         cdrAmountData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'WO_monthly'),aggregate_pipeline=aggregate_cdr_amt)
    #         if cdrAmountData != None:
    #             for row in cdrAmountData:
    #                 temp_member['total_amount']            = row['total_amount']

    #         # spin
    #         count_spin = 0
    #         account_spin_arr = []
    #         for account in account_assign_arr:
    #             spinData = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Diallist_detail'),WHERE={"createdAt": {'$gte': todayTimeStamp, '$lte': endTodayTimeStamp}, "account_number": str(account), "callResult": {'$exists' :  'true'}},SELECT=['callResult'])
    #             call_number = []
    #             if spinData != None:
    #                 for call in list(spinData['callResult']):
    #                     call_number.append(call['customernumber'])
    #                 list_call = set(call_number) 
    #                 unique_call = list(list_call)
    #                 if len(unique_call) > 1:
    #                     count_spin += 1
    #                     account_spin_arr.append(account)
            
    #         temp_member['count_spin'] = count_spin

    #         aggregate_spin = [
    #             {
    #                 "$match":
    #                 {
    #                     "PHONE": {'$in': account_spin_arr},
    #                 }
    #             },{
    #                 '$project':
    #                 {
    #                     'pay_payment': {'$sum' : [ '$WO9711', '$WO9712' ,'$WO9713'] }
    #                 }
    #             },{
    #                 "$group":
    #                 {
    #                     "_id": 'null',
    #                     "spin_amount": {'$sum': '$pay_payment'},

    #                 }
    #             }
    #         ]
    #         spinData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'WO_monthly'),aggregate_pipeline=aggregate_spin)
    #         if spinData != None:
    #             for row in spinData:
    #                 temp_member['spin_amount']            = row['spin_amount']
    #                 temp_member['count_spin']             = count_spin

    #         aggregate_amt_ans = [
    #             {
    #                 "$match":
    #                 {
    #                     "PHONE": {'$in': phone_ans_arr},
    #                 }
    #             },{
    #                 '$project':
    #                 {
    #                     'pay_payment': {'$sum' : [ '$WO9711', '$WO9712' ,'$WO9713'] }
    #                 }
    #             },{
    #                 "$group":
    #                 {
    #                     "_id": 'null',
    #                     "conn_amount": {'$sum': '$current_balance'},
    #                 }
    #             }
    #         ]
    #         amtAnsData = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'WO_monthly'),aggregate_pipeline=aggregate_amt_ans)
    #         if amtAnsData != None:
    #             for row in amtAnsData:
    #                 temp_member['conn_amount']            = row['conn_amount']


    #         # temp['count_data']      += temp_member['count_data'];
    #         # temp['unwork']          += temp_member['unwork'];
    #         # temp['talk_time']      += temp_member['talk_time'];
    #         # temp['total_call']     += temp_member['total_call'];
    #         # temp['total_amount']   += temp_member['total_amount'];
    #         # temp['conn_amount']    += temp_member['conn_amount'];
    #         # temp['count_conn']     += temp_member['count_conn'];
    #         # temp['spin_amount']    += temp_member['spin_amount'];
    #         # temp['count_spin']     += temp_member['count_spin'];
    #         # temp['count_ptp']      += temp_member['count_ptp'];
    #         # temp['ptp_amount']     += temp_member['ptp_amount'];
    #         # temp['count_paid_promise']      += temp_member['count_paid_promise'];
    #         # temp['paid_amount_promise']     += temp_member['count_paid_promise'];


    #         temp_member['spin_rate']     = temp_member['count_spin']/temp_member['total_call'] if temp_member['total_call'] != 0 else 0
    #         temp_member['ptp_rate_acc']  = temp_member['count_ptp']/temp_member['total_call'] if temp_member['total_call'] != 0 else 0
    #         temp_member['ptp_rate_amt']  = temp_member['ptp_amount']/temp_member['total_amount'] if temp_member['total_amount'] != 0  else 0
    #         temp_member['paid_rate_acc'] = temp_member['count_paid_promise']/temp_member['count_ptp'] if temp_member['count_ptp'] != 0 else 0
    #         temp_member['paid_rate_amt'] = temp_member['paid_amount_promise']/temp_member['ptp_amount'] if temp_member['ptp_amount'] != 0 else 0
    #         temp_member['conn_rate']     = temp_member['count_conn']/temp_member['total_call'] if temp_member['total_call'] != 0 else 0
    #         temp_member['collect_ratio_acc'] = temp_member['count_paid']/temp_member['total_call'] if temp_member['total_call'] != 0 else 0
    #         temp_member['collect_ratio_amt'] = temp_member['paid_amount']/temp_member['total_amount'] if temp_member['total_amount'] != 0 else 0
    #         temp_member['createdAt'] = todayTimeStamp
    #         temp_member['createdBy'] = 'system'
    #         temp_member['for_month'] = month

    #         insertData.append(temp_member)

    #     # temp['spin_rate']     = temp['count_spin']/temp['total_call']  if temp['total_call'] != 0 else 0
    #     # temp['ptp_rate_acc']  = temp['count_ptp']/temp['total_call']  if temp['total_call'] != 0 else 0
    #     # temp['ptp_rate_amt']  = temp['ptp_amount']/temp['total_amount']  if temp['total_amount'] != 0 else 0
    #     # temp['paid_rate_acc'] = temp['count_paid_promise']/temp['count_ptp']  if temp['count_ptp'] != 0 else 0
    #     # temp['paid_rate_amt'] = temp['paid_amount_promise']/temp['ptp_amount']  if temp['ptp_amount'] != 0 else 0
    #     # temp['conn_rate']     = temp['count_conn']/temp['total_call'] if temp['total_call'] != 0 else 0
    #     # temp['collect_ratio_acc'] = temp['count_paid']/temp['total_call']  if temp['total_call'] != 0 else 0
    #     # temp['collect_ratio_amt'] = temp['paid_amount']/temp['total_amount']  if temp['total_amount'] != 0 else 0
    #     # temp['createdAt'] = todayTimeStamp
    #     # temp['createdBy'] = 'system'
    #     # temp['for_month'] = month

    #     # insertData.append(temp)
    #     # insertData += member_arr;

    #         i += 1



    # if len(insertData) > 0:
        # mongodb.batch_insert(MONGO_COLLECTION=collection, insert_data=insertData)

    now_end         = datetime.now()
    log.write(now_end.strftime("%d/%m/%Y, %H:%M:%S") + ': End Log' + '\n')
    print('DONE')
except Exception as e:
    log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
    print(traceback.format_exc())

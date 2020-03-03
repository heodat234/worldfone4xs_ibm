#!/usr/bin/python3.6
# -*- coding: utf-8 -*-
# LE THANH HUNG 23/02/2020
import sys
import os
import time
import ntpath
import json
import calendar
import traceback
from helper.mongod import Mongodb
from datetime import datetime
from datetime import date,timedelta
from pprint import pprint
from bson import ObjectId
from helper.common import Common
from helper.jaccs import Config

common      = Common()
base_url    = common.base_url()
wff_env     = common.wff_env(base_url)
mongodb     = Mongodb(MONGODB="worldfone4xs", WFF_ENV=wff_env)
_mongodb    = Mongodb(MONGODB="_worldfone4xs", WFF_ENV=wff_env)
now         = datetime.now()
subUserType = 'LO'
collection                  = common.getSubUser(subUserType, 'Os_balance_group_report')
lnjc05_collection           = common.getSubUser(subUserType, 'LNJC05')
listOfAccount_collection    = common.getSubUser(subUserType, 'List_of_account_in_collection')
target_collection           = common.getSubUser(subUserType, 'Target_of_report')
report_due_date_collection          = common.getSubUser(subUserType, 'Report_due_date')
lnjc05_yesterday_collection         = common.getSubUser(subUserType, 'LNJC05_yesterday')
listAcc_yesterday_collection        = common.getSubUser(subUserType, 'List_of_account_in_collection_yesterday')
stored_collection           = common.getSubUser(subUserType, 'SBV_Stored')
diallist_collection         = common.getSubUser(subUserType, 'Diallist_detail')
sbv_collection              = common.getSubUser(subUserType, 'SBV')

log         = open(base_url + "cronjob/python/Loan/log/Os_balance_group_log.txt","a")


try:
    insertData = []
    updateData = []
    listDebtGroup = []
    lnjc05ByGroup = {}

    today = date.today()
    # today = datetime.strptime('27/02/2020', "%d/%m/%Y").date()

    day = today.day
    month = today.month
    year = today.year
    weekday = today.weekday()
    lastDayOfMonth = calendar.monthrange(year, month)[1]

    todayString = today.strftime("%d/%m/%Y")
    todayTimeStamp = int(time.mktime(time.strptime(str(todayString + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))

    starttime = int(time.mktime(time.strptime(str(todayString + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
    endtime = int(time.mktime(time.strptime(str(todayString + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))

    startMonth = int(time.mktime(time.strptime(str('01/' + str(month) + '/' + str(year) + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
    endMonth = int(time.mktime(time.strptime(str(str(lastDayOfMonth) + '/' + str(month) + '/' + str(year) + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))


    yesterday = today - timedelta(days=1)
    yesterdayString = yesterday.strftime("%d/%m/%Y")
    yesterdayTimeStamp = int(time.mktime(time.strptime(str(yesterdayString + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
    endYesterdayTimeStamp = int(time.mktime(time.strptime(str(yesterdayString + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))

    targetInfo = mongodb.get(MONGO_COLLECTION=target_collection)
    for targetGroup in targetInfo:
        if targetGroup['show_B_plus_duedate_type'] == False:
            duedate_type = targetGroup['duedate_type']
        else:
            duedate_type = targetGroup['B_plus_duedate_type']

        dueDayOfMonth = mongodb.getOne(MONGO_COLLECTION=report_due_date_collection, WHERE={'for_month': str(month), 'debt_group': duedate_type[1:3]})
        temp = {
            'day'           : day,
            'for_month'     : month,
            'year'          : year,
            'type'          : targetGroup['debt_type'],
            'debt_group'    : duedate_type,
            'target'        : targetGroup['target'],
            'start_os_bl'   : 0,
            'start_no'      : 0,
            'target_of_col_os_bl'        : 0,
            'target_of_col_no'           : 0,
            'daily_os_bl'               : 0,
            'daily_no'                  : 0,
        }
        temp_final = {}

        if todayTimeStamp == dueDayOfMonth['due_date_add_1']:
            temp['due_date'] = dueDayOfMonth['due_date']
            temp['check_due_date'] = 'True'

            if targetGroup['debt_type'] == 'SIBS':
                # print(duedate_type)
                aggregate_lnjc05 = [
                    {
                      "$match":
                      {
                          "group_id": duedate_type,
                      }
                    },{
                      "$group":
                      {
                          "_id": 'null',
                          "sum_balance": {'$sum': '$current_balance'},
                          "count_data": {'$sum': 1}
                      }
                    }
                ]
                lnjc05Data = mongodb.aggregate_pipeline(MONGO_COLLECTION=lnjc05_collection,aggregate_pipeline=aggregate_lnjc05)
                if lnjc05Data != None:
                    for row in lnjc05Data:
                        temp['start_os_bl']         = row['sum_balance']
                        temp['start_no']            = row['count_data']

                # Final No
                aggregate_lnjc05_yesterday = [
                    {
                      "$match":
                      {
                          "group_id": duedate_type,
                      }
                    },{
                      "$group":
                      {
                          "_id": 'null',
                          "acc_yesterday": {'$push': '$account_number'},
                      }
                    }
                ]
                lnjc05YesterdayData = mongodb.aggregate_pipeline(MONGO_COLLECTION=lnjc05_yesterday_collection,aggregate_pipeline=aggregate_lnjc05_yesterday)
                acc_yesterday = []
                if lnjc05YesterdayData != None:
                    for row in lnjc05YesterdayData:
                        acc_yesterday         = row['acc_yesterday']


                aggregate_lnjc05 = [
                    {
                      "$match":
                      {
                          "group_id": duedate_type,
                          "account_number" : {'$in' : acc_yesterday}
                      }
                    },{
                      "$group":
                      {
                          "_id": 'null',
                          "sum_balance": {'$sum': '$current_balance'},
                          "sum_principal": {'$sum': '$outstanding_principal'},
                          "count_data": {'$sum': 1}
                      }
                    }
                ]
                lnjc05Data = mongodb.aggregate_pipeline(MONGO_COLLECTION=lnjc05_collection,aggregate_pipeline=aggregate_lnjc05)
                if lnjc05Data != None:
                    for row in lnjc05Data:
                        temp_final['final_os_bl']             = row['sum_balance']
                        temp_final['final_principal']         = row['sum_principal']
                        temp_final['final_no']                = row['count_data']

            else:
                aggregate_listAccount = [
                    {
                        "$lookup":
                        {
                           "from": stored_collection,
                           "localField": "account_number",
                           "foreignField": "contract_no",
                           "as": "detail"
                        }
                    },{
                      "$match":
                      {
                          "detail.overdue_indicator": duedate_type[0:1],
                          "detail.kydue": duedate_type[1:3],
                      }
                    },{
                      "$group":
                      {
                          "_id": 'null',
                          "sum_balance": {'$sum': '$cur_bal'},
                          "count_data": {'$sum': 1}
                      }
                    }
                ]
                listAccountData = mongodb.aggregate_pipeline(MONGO_COLLECTION=listOfAccount_collection,aggregate_pipeline=aggregate_listAccount)
                if listAccountData != None:
                    for row in listAccountData:
                        temp['start_os_bl']         = row['sum_balance']
                        temp['start_no']            = row['count_data']




                # Final No
                aggregate_listAcc_yesterday = [
                    {
                      "$group":
                      {
                          "_id": 'null',
                          "acc_yesterday": {'$push': '$account_number'},
                      }
                    }
                ]
                listAccYesterdayData = mongodb.aggregate_pipeline(MONGO_COLLECTION=listAcc_yesterday_collection,aggregate_pipeline=aggregate_listAcc_yesterday)
                acc_yesterday = []
                if listAccYesterdayData != None:
                    for row in listAccYesterdayData:
                        acc_yesterday         = row['acc_yesterday']

                aggregate_diallist_yesterday = [
                    {
                        "$match":
                        {
                            "createdAt" : {'$gte' : yesterdayTimeStamp,'$lte' : endYesterdayTimeStamp},
                            "account_number" : {'$in' : acc_yesterday},
                            "group_id": duedate_type[0:1]+'-'+duedate_type[1:3],
                        }
                    },{
                      "$group":
                      {
                          "_id": 'null',
                          "acc_yesterday": {'$push': '$account_number'},
                      }
                    }
                ]
                diallistYesterdayData = mongodb.aggregate_pipeline(MONGO_COLLECTION=diallist_collection,aggregate_pipeline=aggregate_diallist_yesterday)
                diallist_yesterday = []
                if diallistYesterdayData != None:
                    for row in diallistYesterdayData:
                        diallist_yesterday         = row['acc_yesterday']



                aggregate_listAcc = [
                    {
                      "$match":
                      {
                          "account_number" : {'$in' : diallist_yesterday},
                      }
                    },{
                      "$group":
                      {
                          "_id": 'null',
                          "sum_balance": {'$sum': '$cur_bal'},
                          "count_data": {'$sum': 1}
                      }
                    }
                ]
                lnjc05Data = mongodb.aggregate_pipeline(MONGO_COLLECTION=listOfAccount_collection,aggregate_pipeline=aggregate_listAcc)
                if lnjc05Data != None:
                    for row in lnjc05Data:
                        temp_final['final_os_bl']             = row['sum_balance']
                        temp_final['final_no']                = row['count_data']


                aggregate_sbv = [
                    {
                      "$match":
                      {
                          "contract_no" : {'$in' : diallist_yesterday},
                      }
                    },{
                      "$group":
                      {
                          "_id": 'null',
                          "sum_principal_sale": {'$sum': '$ob_principal_sale'},
                          "sum_principal_cash": {'$sum': '$ob_principal_cash'},
                      }
                    }
                ]
                sbvData = mongodb.aggregate_pipeline(MONGO_COLLECTION=sbv_collection,aggregate_pipeline=aggregate_sbv)
                if sbvData != None:
                    for row in sbvData:
                        temp_final['final_principal']         = row['sum_principal_sale'] + row['sum_principal_cash']




            temp['target_of_col_os_bl']          = round(temp['start_os_bl'] * targetGroup['target']/100)
            temp['target_of_col_no']             = round(temp['start_no'] * targetGroup['target']/100)

            temp['daily_os_bl']                 = temp['start_os_bl']
            temp['daily_no']                    = temp['start_no']


            checkYesterDay = mongodb.count(MONGO_COLLECTION=collection, WHERE={'createdAt': dueDayOfMonth['due_date'], 'debt_group': duedate_type, 'type': targetGroup['debt_type']})
            if checkYesterDay > 0 and len(temp_final) > 0 :
                mongodb.update(MONGO_COLLECTION=collection, WHERE={'createdAt': dueDayOfMonth['due_date'], 'debt_group': duedate_type, 'type': targetGroup['debt_type']}, VALUE=temp_final)


        # if todayTimeStamp > dueDayOfMonth['due_date_add_1']:
            # temp['due_date'] = dueDayOfMonth['due_date']
        else:
            if targetGroup['debt_type'] == 'SIBS':
                aggregate_lnjc05 = [
                    {
                      "$match":
                      {
                          "group_id": duedate_type,
                      }
                    },{
                      "$group":
                      {
                          "_id": 'null',
                          "sum_balance": {'$sum': '$current_balance'},
                          "count_data": {'$sum': 1}
                      }
                    }
                ]
                lnjc05Data = mongodb.aggregate_pipeline(MONGO_COLLECTION=lnjc05_collection,aggregate_pipeline=aggregate_lnjc05)
                if lnjc05Data != None:
                    for row in lnjc05Data:
                        temp['daily_os_bl']         = row['sum_balance']
                        temp['daily_no']            = row['count_data']


            else:
                aggregate_listAccount = [
                    {
                        "$lookup":
                        {
                           "from": stored_collection,
                           "localField": "account_number",
                           "foreignField": "contract_no",
                           "as": "detail"
                        }
                    },{
                      "$match":
                      {
                          "detail.overdue_indicator": duedate_type[0:1],
                          "detail.kydue": duedate_type[1:3],
                      }
                    },{
                      "$group":
                      {
                          "_id": 'null',
                          "sum_balance": {'$sum': '$cur_bal'},
                          "count_data": {'$sum': 1}
                      }
                    }
                ]
                listAccountData = mongodb.aggregate_pipeline(MONGO_COLLECTION=listOfAccount_collection,aggregate_pipeline=aggregate_listAccount)
                if listAccountData != None:
                    for row in listAccountData:
                        temp['daily_os_bl']         = row['sum_balance']
                        temp['daily_no']            = row['count_data']



            checkDueDateAdd1 = mongodb.getOne(MONGO_COLLECTION=collection, WHERE={'createdAt': dueDayOfMonth['due_date_add_1'], 'debt_group': duedate_type, 'check_due_date': 'True', 'type': targetGroup['debt_type']})
            if checkDueDateAdd1 != None:
                temp['start_os_bl']         = checkDueDateAdd1['start_os_bl']
                temp['start_no']            = checkDueDateAdd1['start_no']
                temp['target_of_col_os_bl']         = checkDueDateAdd1['target_of_col_os_bl']
                temp['target_of_col_no']            = checkDueDateAdd1['target_of_col_no']
                
        temp['createdAt'] = todayTimeStamp
        temp['created_at'] = todayTimeStamp
        temp['createdBy'] = 'system'
        insertData.append(temp)
        # print(temp)


    if len(insertData) > 0:
        mongodb.batch_insert(MONGO_COLLECTION=collection, insert_data=insertData)


    print('DONE')
except Exception as e:
    print(traceback.format_exc())
    log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')

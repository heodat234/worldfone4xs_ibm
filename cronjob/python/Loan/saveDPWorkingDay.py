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
base_url = common.base_url()
wff_env = common.wff_env(base_url)
pprint(wff_env)
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

    dueDateThisMonth = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_due_date'))

    # if todayTimeStamp in listHoliday or (weekday == 5) or weekday == 6:
    #     sys.exit()

    todayString = today.strftime("%d/%m/%Y")
    starttime = int(time.mktime(time.strptime(str(todayString + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
    endtime = int(time.mktime(time.strptime(str(todayString + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))

    yesterday_starttime = starttime - 86400
    yesterday_endtime = endtime - 86400

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

    lnjc05 = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'LNJC05'))
    total_lnjc05 = 0
    total_cur_bal_lnjc05 = 0
    for lnjc05_row in lnjc05:
        total_lnjc05 += 1
        total_cur_bal_lnjc05 += lnjc05_row['current_balance']
    
    list_acc = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'List_of_account_in_collection'))
    total_list_acc = 0
    total_cur_bal_list_acc = 0
    for list_acc_row in list_acc:
        total_list_acc += 1
        total_cur_bal_list_acc += list_acc_row['cur_bal']

    for debtGroupCell in list(listDebtGroup):
        if debtGroupCell[0:1] is not 'F':
            dueDayOfMonth = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_due_date'), WHERE={'for_month': str(month), 'debt_group': debtGroupCell[1:3]})
            dueDayLastMonth = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_due_date'), WHERE={'for_month': str(month - 1), 'debt_group': debtGroupCell[1:3]})
            if todayTimeStamp > dueDayOfMonth['due_date_add_1']:
                todayIndex = str(common.countWorkingDaysBetweendate(starttime = dueDayOfMonth['due_date_add_1'], endtime = todayTimeStamp, mongodb=mongodb))
            else:
                todayIndex = str(common.countWorkingDaysBetweendate(starttime = dueDayLastMonth['due_date_add_1'], endtime = todayTimeStamp, mongodb=mongodb))
            for groupProductCell in list(listGroupProduct):
                for key, dpWorkingdaysdaycolcell in enumerate(dpWorkingdaysdaycol):
                    groupInfoByDueDate = list(mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Group'), WHERE={'debt_groups': debtGroupCell, 'name': {"$regex": groupProductCell['text'] + '.*'}}))
                    groupInfoByDueDate.extend([{'name': 'Total'}])
                    for groupCell in groupInfoByDueDate:
                        debtList = []
                        cur_bal = 0
                        if groupProductCell['value'] == 'SIBS':
                            count_acc = total_lnjc05
                            cur_bal = total_cur_bal_lnjc05
                                
                        if groupProductCell['value'] == 'Card':
                            count_acc = total_list_acc
                            cur_bal = total_cur_bal_list_acc

                        no_overdue = count_acc
                        no_paid_acc_accumulayed = 0
                        no_overdue_amt = cur_bal
                        no_paid_acc_accumulayed_amt = 0
                        if(groupCell['name'] != 'Total'):
                            yesterdayData = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Daily_prod_working_day'), WHERE={'team_id': str(groupCell['_id']), 'day_code': key, '$or': [{'created_at': {'$gte': yesterday_starttime, '$lte': yesterday_endtime}}, {'updated_at': {'$gte': yesterday_starttime, '$lte': yesterday_endtime}}]})
                            no_acc_end_date = 0
                            no_acc_end_date_amt = 0
                            if yesterdayData is not None:
                                no_acc_end_date = yesterdayData['no_overdue'] - no_overdue
                                no_acc_end_date_amt = yesterdayData['no_overdue_amt'] - no_overdue_amt
                                updateDataYesterday = {}
                                if key == '2':
                                    updateDataYesterday[todayIndex] = no_acc_end_date

                                if key == '6':
                                    updateDataYesterday[todayIndex] = no_acc_end_date_amt
                                mongodb.update(MONGO_COLLECTION=common.getSubUser(subUserType, 'Daily_prod_working_day'), WHERE={'team_id': str(groupCell['_id']), 'day_code': key, '$or': [{'created_at': {'$gte': yesterday_starttime, '$lte': yesterday_endtime}}]}, VALUE={})
                            
                            target = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Target'), WHERE={'group.id': str(groupCell['_id'])})
                            pprint(target)
                            #     temp = {
                            #         'group'         : debtGroupCell[0:1] + ' GROUP',
                            #         'month'         : today.strftime("%b-%y"),
                            #         'due'           : due[debtGroupCell[1:3]],
                            #         'product'       : groupProductCell,
                            #         'day'           : dpWorkingdaysdaycolcell,
                            #         'day_code'      : key,
                            #         'group'         : groupCell['name'],
                            #         'team_id'       : str(groupCell['_id']),
                            #     }

                            #     if todayTimeStamp < dueDayOfMonth['due_date_add_1']:
                            #         temp['due_date'] = dueDayLastMonth['due_date'] if dueDayLastMonth is not None else ''
                            #         # #Lay gia tri no vao ngay due date + 1#
                            #         # incidenceInfo = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Due_date_next_date'), WHERE={'for_month': str(month - 1), 'team_id': str(groupCell['_id'])})
                            #         # #Lay gia tri no vao ngay due date + 1#
                            #     else:
                            #         # incidenceInfo = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Due_date_next_date'), WHERE={'for_month': str(month), 'team_id': str(groupCell['_id'])})
                            #         temp['due_date'] = dueDayOfMonth['due_date']

                            #     if key == '2':
                            #         temp[todayIndex] = 0
                            #         mongodb.update(MONGO_COLLECTION=common.getSubUser(subUserType, 'Daily_prod_working_day'), WHERE={'team_id': str(groupCell['_id']), 'day_code': key}, VALUE={'no_acc_end_date': no_acc_end_date})

                            #     if key == '6':
                            #         temp[todayIndex] = 0
                            #         mongodb.update(MONGO_COLLECTION=common.getSubUser(subUserType, 'Daily_prod_working_day'), WHERE={'team_id': str(groupCell['_id']), 'day_code': key}, VALUE={'no_acc_end_date_amt': no_acc_end_date_amt})
                                
                            #     if key == '1':
                            #         temp[todayIndex] = no_overdue

                            #     if key == '3':
                            #         temp[todayIndex] = no_paid_acc_accumulayed

                            #     if key == '5':
                            #         temp[todayIndex] = no_overdue_amt

                            #     if key == '7':
                            #         temp[todayIndex] = no_paid_acc_accumulayed_amt

                            #     if todayTimeStamp == dueDayOfMonth['due_date_add_1']:
                            #         temp['target'] = target['target'],
                            #         temp['target_acc'] = (no_overdue * int(temp['target'])) / 100
                            #         temp['target_amt'] = (no_overdue_amt * int(temp['target'])) / 100

                            #     temp['start_acc'] = 0
                            #     temp['start_amt'] = 0

                            #     if key == '4':
                            #         # temp['col_ratio_acc'] = no_acc_end_date / no_overdue if no_overdue not in [None, 0] else 0
                            #         temp[todayIndex] = 0

                            #     if key == '8':
                            #         temp[todayIndex] = 0

                            #     pprint(temp)

    # for debtGroupCell in list(listDebtGroup):
    #     if debtGroupCell[0:1] is not 'F':
    #         dueDayOfMonth = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_due_date'), WHERE={'for_month': str(month), 'debt_group': debtGroupCell[1:3]})
    #         for groupProduct in list(listGroupProduct):
    #             groupInfoByDueDate = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Group'), WHERE={'debt_groups': debtGroupCell, 'name': {"$regex": groupProduct['text'] + '.*'}})
    #             for groupCell in list(groupInfoByDueDate):
    #                 temp = {
    #                     'col'           : 0,
    #                     'col_amt'       : 0,
    #                     'payment_amt'   : 0,
    #                     'rem'           : 0,
    #                     'rem_actual'       : 0,
    #                     'rem_os'        : 0,
    #                     'flow_rate'     : 0,
    #                     'flow_rate_actual' : 0,
    #                     'flow_rate_os' : 0,
    #                     'col_ratio'     : 0,
    #                     'col_ratio_actual' : 0,
    #                     'col_ratio_os' : 0,
    #                 }
    #                 col_today = 0
    #                 col_amt_today = 0
    #                 if todayTimeStamp < dueDayOfMonth['due_date_add_1']:
    #                     dueDayLastMonth = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_due_date'), WHERE={'for_month': str(month - 1), 'debt_group': debtGroupCell[1:3]})
    #                     temp['due_date'] = dueDayLastMonth['due_date'] if dueDayLastMonth is not None else ''
    #                     #Lay gia tri no vao ngay due date + 1#
    #                     incidenceInfo = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Due_date_next_date'), WHERE={'for_month': str(month - 1), 'team_id': str(groupCell['_id'])})
    #                     #Lay gia tri no vao ngay due date + 1#
    #                 else:
    #                     incidenceInfo = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Due_date_next_date'), WHERE={'for_month': str(month), 'team_id': str(groupCell['_id'])})
    #                     temp['due_date'] = dueDayOfMonth['due_date']

    #                 temp['debt_group'] = debtGroupCell[0:1]
    #                 temp['due_date_code'] = debtGroupCell[1:3]
    #                 temp['product'] = groupProduct['text']
    #                 temp['team'] = groupCell['name']
    #                 temp['team_id'] = str(groupCell['_id'])
                    
    #                 if incidenceInfo is not None:
    #                     temp['inci'] = incidenceInfo['debt_acc_no'] if 'debt_acc_no' in incidenceInfo.keys() else 0
    #                     temp['inci_amt'] = incidenceInfo['current_balance_total'] if 'current_balance_total' in incidenceInfo.keys() else 0
    #                     acc_arr = incidenceInfo['acc_arr'] if 'acc_arr' in incidenceInfo.keys() else []
    #                 else:
    #                     temp['inci'] = 0
    #                     temp['inci_amt'] = 0
    #                     acc_arr = []
                    

    #                 if groupProduct['value'] == 'SIBS':
    #                     yesterdayReportData = mongodb.getOne(MONGO_COLLECTION=collection, WHERE={'team_id': str(groupCell['_id']), 'created_at': {'$gte': (starttime - 86400), '$lte': (endtime - 86400)}})
                        
    #                     dueDateOneData = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'Due_date_next_date_SIBS'), WHERE={'debt_group': debtGroupCell[0:1], 'due_date_code': debtGroupCell[1:3], 'for_month': str(month)})

    #                     lead = ['JIVF00' + groupCell['lead']] if 'lead' in groupCell.keys() else []
    #                     member = ('JIVF00' + s for s in groupCell['members']) if 'members' in groupCell.keys() else []
    #                     officerIdRaw = list(lead) + list(member)
    #                     officerId = list(dict.fromkeys(officerIdRaw))

    #                     lnjc05Info = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'LNJC05'), WHERE={'group_id': debtGroupCell, 'officer_id': {'$in': officerId}})
    #                     for lnjc05 in lnjc05Info:
    #                         col_today += 1
    #                         col_amt_today += lnjc05['current_balance']
    
    #                     aggregate_ln3206 = [
    #                         {
    #                             "$match":
    #                             {
    #                                 "created_at": {'$gte': temp['due_date']},
    #                                 "account_number": {'$in' : acc_arr}
    #                             }
    #                         },{
    #                             "$group":
    #                             {
    #                                 "_id": 'null',
    #                                 "total_amt": {'$sum': '$amt'},
    #                             }
    #                         }
    #                     ]
    #                     ln3206fInfo = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'LN3206F'),aggregate_pipeline=aggregate_ln3206)
    #                     if ln3206fInfo is not None:
    #                         for ln3206 in ln3206fInfo:
    #                             temp['payment_amt'] = ln3206['total_amt']
                            
    #                 if groupProduct['value'] == 'Card':
    #                     yesterdayReportData = mongodb.getOne(MONGO_COLLECTION=collection, WHERE={'team_id': str(groupCell['_id']), 'created_at': {'$gte': (starttime - 86400), '$lte': (endtime - 86400)}})

    #                     listOfAccount = mongodb.get(MONGO_COLLECTION=common.getSubUser(subUserType, 'List_of_account_in_collection'), WHERE={ 'account_number': {'$in': acc_arr}})
    #                     for account in listOfAccount:
    #                         col_today += 1
    #                         col_amt_today += account['cur_bal']
                               
    #                     aggregate_gl = [
    #                         {
    #                             "$match":
    #                             {
    #                                 "created_at": {'$gte': temp['due_date']},
    #                                 "account_number": {'$in' : acc_arr}
    #                             }
    #                         },{
    #                             "$group":
    #                             {
    #                                 "_id": 'null',
    #                                 "total_amt": {'$sum': '$amount'},
    #                             }
    #                         }
    #                     ]
    #                     glInfo = mongodb.aggregate_pipeline(MONGO_COLLECTION=common.getSubUser(subUserType, 'Report_input_payment_of_card'),aggregate_pipeline=aggregate_gl)
    #                     if glInfo is not None:
    #                         for gl in glInfo:
    #                             temp['payment_amt'] = gl['total_amt']   
                    
    #                 temp['col']         = temp['inci'] - col_today
    #                 temp['col_amt']     = temp['inci_amt'] - col_amt_today
    #                 temp['rem']         = temp['inci'] - temp['col']
    #                 temp['rem_actual']     = temp['inci_amt'] - temp['payment_amt']
    #                 temp['rem_os']     = temp['inci_amt'] - temp['col_amt']
    #                 temp['flow_rate']   = temp['rem'] / temp['inci'] if temp['inci'] != 0 else 0
    #                 temp['flow_rate_actual']   = temp['rem_actual'] / temp['inci_amt'] if temp['inci_amt'] != 0 else 0
    #                 temp['flow_rate_os']   = temp['rem_os'] / temp['inci_amt'] if temp['inci_amt'] != 0 else 0
    #                 temp['col_ratio']       = temp['col'] / temp['inci'] if temp['inci'] != 0 else 0
    #                 temp['col_ratio_actual']   = temp['payment_amt'] / temp['inci_amt'] if temp['inci_amt'] != 0 else 0
    #                 temp['col_ratio_os']   = temp['col_amt'] / temp['inci_amt'] if temp['inci_amt'] != 0 else 0

    #                 targetInfo = mongodb.getOne(MONGO_COLLECTION=common.getSubUser(subUserType, 'Target'), WHERE={ 'group.id': str(groupCell['_id'])})
    #                 target = int(targetInfo['target'])
    #                 temp['tar_amt'] = (target * temp['inci_amt'])/100
    #                 temp['tar_gap'] = temp['tar_amt'] - temp['rem_os']
    #                 temp['tar_per'] = temp['tar_gap']/temp['tar_amt'] if temp['tar_amt'] != 0 else 0

    #                 temp['createdAt'] = time.time()
    #                 temp['createdBy'] = 'system'
    #                 temp['for_month'] = str(month)
    #                 mongodb.insert(MONGO_COLLECTION=collection, insert_data=temp)
                    # log.write(json.dumps(temp))
                    # pprint(temp)
        

    # wo
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
    #     # print(temp)
   
    print('DONE')
except Exception as e:
    # log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
    pprint(str(e))
        
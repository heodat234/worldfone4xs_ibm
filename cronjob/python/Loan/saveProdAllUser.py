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
collection                      = common.getSubUser(subUserType, 'Daily_all_user_report')
group_collection                = common.getSubUser(subUserType, 'Group')
product_collection              = common.getSubUser(subUserType, 'Product')
report_due_date_collection      = common.getSubUser(subUserType, 'Report_due_date')
user_collection                 = common.getSubUser(subUserType, 'User')
jsondata_collection             = common.getSubUser(subUserType, 'Jsondata')
diallist_collection             = common.getSubUser(subUserType, 'Diallist')
diallist_detail_collection      = common.getSubUser(subUserType, 'Diallist_detail')
cdr_collection                  = common.getSubUser(subUserType, 'worldfonepbxmanager')
action_code_collection          = common.getSubUser(subUserType, 'Action_code')
lnjc05_yesterday_collection     = common.getSubUser(subUserType, 'LNJC05_yesterday')
list_of_account_yesterday_collection     = common.getSubUser(subUserType, 'List_of_account_in_collection_yesterday')
ln3206f_collection              = common.getSubUser(subUserType, 'LN3206F')
gl_collection                   = common.getSubUser(subUserType, 'Report_input_payment_of_card')



log = open(base_url + "cronjob/python/Loan/log/dailyProdAllUser_log.txt","a")
now = datetime.now()
log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': Start Import' + '\n')
try:
    insertData = []
    insertDataTotal = []
    listDebtGroup = []
    code = ['2000','2100','2700']

    today = date.today()
    # today = datetime.strptime('13/02/2020', "%d/%m/%Y").date()

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
    yesterdayString = yesterday.strftime("%d/%m/%Y")
    yesterdayTimeStamp = int(time.mktime(time.strptime(str(yesterdayString + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
    endYesterdayTimeStamp = int(time.mktime(time.strptime(str(yesterdayString + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))

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

    
    checkGroupA = 'false'
    for debtGroupCell in list(listDebtGroup):

        if debtGroupCell[0:1] == 'A' and checkGroupA == 'false':
          i = 1
          for groupProduct in list(listGroupProduct):
              leaders = []
              groups = mongodb.get(MONGO_COLLECTION=group_collection, WHERE={'name' : {'$regex' : groupProduct['value']} },SELECT=['name','lead'])
              for group in groups:
                if group['name'].find('A') != -1:
                  leaders.append(group['lead']) if 'lead' in group.keys() else ''

              list_set = set(leaders) 
              unique_leaders = (list(list_set)) 

              for lead in unique_leaders:
                  teams = mongodb.get(MONGO_COLLECTION=group_collection, 
                    WHERE={'lead': lead ,'name' : {'$regex' : groupProduct['text'] + '/Group ' + debtGroupCell[0:1]} } )
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

                      print(name)
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
                          'count_ptp_all_days'        : 0,
                          'paid_amount_all_days'      : 0,
                      }

                      # members
                      count_member = len(unique_members)
                      member_arr = []
                      member_arr_sort = []
                      for member in list(unique_members):
                          temp_member = [member]
                          users = _mongodb.getOne(MONGO_COLLECTION=user_collection,WHERE={'extension': str(member)}, SELECT=['extension','agentname'])
                          if users != None:
                              temp_member.append(users['agentname'])

                          member_arr_sort.append(temp_member)

                      sorted_l = sorted(member_arr_sort, key=lambda x: x[1])
                      for members in list(sorted_l):
                          member = members[0]
                          temp_member = {
                              'name'           : members[1],
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
                              'count_ptp_all_days'        : 0,
                              'paid_amount_all_days'      : 0,
                          }
                          
                          # account assign
                          if groupProduct['value'] == 'SIBS':
                            aggregate_diallist = [
                                {
                                    "$match":
                                    {
                                        "createdAt": {'$gte': yesterdayTimeStamp, '$lte': endYesterdayTimeStamp},
                                        "assign": str(member),
                                        # "Donotcall": "N", 
                                        # "DonotcallBy": {'$exists': 'true'}, 
                                        "$or": [{"Donotcall": "N"}, {"Donotcall": "Y", "DonotcallBy": {'$exists':'true'}}]
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
                          else:
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
                          

                          # unwork
                          aggregate_contacted = [
                              {
                                  "$match":
                                  {
                                      "createdAt": {'$gte': yesterdayTimeStamp, '$lte': endYesterdayTimeStamp},
                                      "assign": str(member),
                                      "callResult" : {'$exists': 'true'},
                                      "callResult.userextension": str(member)
                                  }
                              },{
                                  "$group":
                                  {
                                      "_id": 'null',
                                      "count_work": {'$sum': 1},
                                  }
                              }
                          ]
                          contactData = mongodb.aggregate_pipeline(MONGO_COLLECTION=diallist_detail_collection,aggregate_pipeline=aggregate_contacted)
                          count_work = 0
                          if contactData != None:
                              for row in contactData:
                                  count_work                  = row['count_work']

                          temp_member['work']               = count_work
                          temp_member['unwork']             = temp_member['count_data'] - count_work

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
                                      "talk_time": {'$sum': '$totalduration'},
                                  }
                              }
                          ]
                          cdrData = mongodb.aggregate_pipeline(MONGO_COLLECTION=cdr_collection,aggregate_pipeline=aggregate_cdr)
                          
                          disposition_arr = []
                          if cdrData != None:
                              for row in cdrData:
                                  temp_member['talk_time']            = row['talk_time']

                          # contacted
                          aggregate_contacted = [
                              {
                                  "$match":
                                  {
                                      "starttime": {'$gte': yesterdayTimeStamp, '$lte': endYesterdayTimeStamp},
                                      "userextension": str(member),
                                      "direction" : "outbound"
                                  }
                              }
                          ]
                          contactData = mongodb.aggregate_pipeline(MONGO_COLLECTION=cdr_collection,aggregate_pipeline=aggregate_contacted)
                          acc_contact_arr_temp = []
                          if contactData != None:
                              for cdr in contactData:
                                diallistInfo = None
                                if 'dialid' in cdr.keys():
                                  if len(str(cdr['dialid'])) == 24:
                                    diallistInfo = mongodb.getOne(MONGO_COLLECTION=diallist_detail_collection, WHERE={'_id': ObjectId(str(cdr['dialid'])),  "createdAt" : {'$gte' : yesterdayTimeStamp,'$lte' : endYesterdayTimeStamp}}) 
                                  else:
                                    diallistInfo = mongodb.getOne(MONGO_COLLECTION=diallist_detail_collection, WHERE={'$or' : [{ 'mobile_num' : str(cdr['customernumber'])}, { 'phone' : str(cdr['customernumber'])},  { 'other_phones' :str(cdr['customernumber'])} ],  "createdAt" : {'$gte' : yesterdayTimeStamp,'$lte' : endYesterdayTimeStamp} }) 

                                if 'dialid' not in cdr.keys():
                                  diallistInfo = mongodb.getOne(MONGO_COLLECTION=diallist_detail_collection, WHERE={'$or' : [{ 'mobile_num' : str(cdr['customernumber'])}, { 'phone' : str(cdr['customernumber'])},  { 'other_phones' :str(cdr['customernumber'])} ],  "createdAt" : {'$gte' : yesterdayTimeStamp,'$lte' : endYesterdayTimeStamp} }) 

                                if diallistInfo != None:
                                  acc_contact_arr_temp.append(diallistInfo['account_number'])

                          set_acc_contact = set(acc_contact_arr_temp) 
                          acc_contact_arr = list(set_acc_contact)
                          temp_member['count_contacted']      = len(acc_contact_arr)

                          # call made
                          calMade = mongodb.count(MONGO_COLLECTION=cdr_collection, WHERE={'disposition': 'ANSWERED', "direction" : "outbound", "userextension": str(member), "starttime": {'$gte': yesterdayTimeStamp, '$lte': endYesterdayTimeStamp}} )
                          if calMade > 0:
                            temp_member['number_of_call'] = calMade

                          totalCall = mongodb.count(MONGO_COLLECTION=cdr_collection, WHERE={ "direction" : "outbound", "userextension": str(member), "starttime": {'$gte': yesterdayTimeStamp, '$lte': endYesterdayTimeStamp}} )
                          if totalCall > 0:
                            temp_member['total_call'] = totalCall


                          # connected
                          action_code = ['PTP', 'CHECK', 'LM', 'PTP Today','Spaid']
                          aggregate_cdr_ans = [
                              {
                                  "$match":
                                  {
                                      "starttime": {'$gte': yesterdayTimeStamp, '$lte': endYesterdayTimeStamp},
                                      "userextension": str(member),
                                      "disposition" : "ANSWERED",
                                      "direction" : "outbound"
                                  }
                              }
                          ]
                          cdrAnsData = mongodb.aggregate_pipeline(MONGO_COLLECTION=cdr_collection,aggregate_pipeline=aggregate_cdr_ans)
                          acc_ans_arr = []
                          if cdrAnsData != None:
                            for cdr in cdrAnsData:
                              aggregate_pipeline_action = [
                                {
                                     "$match":
                                     {
                                         "calluuid" : cdr['calluuid'],
                                         "action_code" : {'$in': action_code}
                                     }
                                },{
                                     "$project":
                                     {
                                         "_id": 0,
                                         # "LIC_NO": 0,
                                         "account_number": 1,
                                     }
                                }
                                      
                              ]
                              accountData = mongodb.aggregate_pipeline(MONGO_COLLECTION=action_code_collection,aggregate_pipeline=aggregate_pipeline_action)
                              if accountData != None:
                                for row in accountData:
                                  temp_member['count_conn'] += 1
                                  acc_ans_arr.append(row['account_number'])


                          # PTP
                          account_ptp_arr = []
                          for acc in acc_contact_arr:
                            where = {
                                        "createdAt": {'$gte': yesterdayTimeStamp, '$lte': endYesterdayTimeStamp},
                                        "account_number": acc,
                                        'action_code' :  {'$in': ['PTP','PTP Today', 'CHECK']}
                            }
                            actionCodeData = mongodb.get(MONGO_COLLECTION=action_code_collection, WHERE=where,SELECT=['account_number','createdBy'],SORT=[("_id", -1)], SKIP=0, TAKE=1)
                            if actionCodeData != None:
                              for row in actionCodeData:
                                if row['createdBy'] == str(member):
                                  account_ptp_arr.append(row['account_number'])
                                  temp_member['count_ptp']            += 1


                          # PTP all days
                          account_ptp_all_days_arr = []
                          for acc in acc_contact_arr:
                            where = {
                                        "account_number": acc,
                                        'action_code' :  {'$in': ['PTP','PTP Today', 'CHECK']}
                            }
                            actionCodeData = mongodb.get(MONGO_COLLECTION=action_code_collection, WHERE=where,SELECT=['account_number','createdBy'],SORT=[("_id", -1)], SKIP=0, TAKE=1)
                            if actionCodeData != None:
                              for row in actionCodeData:
                                if row['createdBy'] == str(member):
                                  account_ptp_all_days_arr.append(row['account_number'])


                          if groupProduct['value'] == 'SIBS':
                              aggregate_cdr_amt = [
                                  {
                                      "$match":
                                      {
                                          "account_number": {'$in': acc_contact_arr},
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
                                          "code" : '10'
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

                              # print(account_ptp_arr)
                              # PTP all days
                              aggregate_paid_all_days = [
                                  {
                                      "$match":
                                      {
                                          "created_at": {'$gte': todayTimeStamp, '$lte': endTodayTimeStamp},
                                          "account_number": {'$in': account_ptp_all_days_arr},
                                          "code" : '10'
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
                              paidAllDaysData = mongodb.aggregate_pipeline(MONGO_COLLECTION=ln3206f_collection,aggregate_pipeline=aggregate_paid_all_days)
                              if paidAllDaysData != None:
                                  for row in paidAllDaysData:
                                      temp_member['count_ptp_all_days']            = len(row['count_paid_promise'] )
                                      temp_member['paid_amount_all_days']           = row['paid_amount_promise']



                              # paid
                              aggregate_paid = [
                                  {
                                      "$match":
                                      {
                                          "created_at": {'$gte': todayTimeStamp, '$lte': endTodayTimeStamp},
                                          "account_number": {'$in': account_assign_arr},
                                          "code" : '10'
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
                                          "account_number": {'$in': acc_contact_arr},
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

                              
                              temp_member['count_paid_promise']  = 0
                              temp_member['paid_amount_promise'] = 0
                              for acc_ptp in account_ptp_arr:
                                aggregate_paid_ptp = [
                                    {
                                        "$match":
                                        {
                                            "created_at": {'$gte': todayTimeStamp, '$lte': endTodayTimeStamp},
                                            "account_number": acc_ptp,
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
                                paidPTPData = mongodb.aggregate_pipeline(MONGO_COLLECTION=gl_collection,aggregate_pipeline=aggregate_paid_ptp)
                                code_2000 = 0
                                code_2700 = 0
                                sum_code = 0
                                if paidPTPData != None:
                                    for row in paidPTPData:
                                      if row['code'] == '2000' or row['code'] == '2100':
                                        code_2000 += row['amount']
                                      else:
                                        code_2700 += row['amount']
                                    sum_code = code_2000 - code_2700
                                    if sum_code > 0:
                                      temp_member['count_paid_promise'] += 1
                                      temp_member['paid_amount_promise'] += sum_code



                              # PTP all days
                              temp_member['count_ptp_all_days']  = 0
                              temp_member['paid_amount_all_days'] = 0
                              for acc_ptp_all_day in account_ptp_all_days_arr:
                                aggregate_paid_all_days = [
                                    {
                                        "$match":
                                        {
                                            "created_at": {'$gte': todayTimeStamp, '$lte': endTodayTimeStamp},
                                            "account_number": acc_ptp_all_day,
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
                                paidPTPAllDayData = mongodb.aggregate_pipeline(MONGO_COLLECTION=gl_collection,aggregate_pipeline=aggregate_paid_all_days)
                                code_2000 = 0
                                code_2700 = 0
                                sum_code = 0
                                if paidPTPAllDayData != None:
                                    for row in paidPTPAllDayData:
                                      if row['code'] == '2000' or row['code'] == '2100':
                                        code_2000 += row['amount']
                                      else:
                                        code_2700 += row['amount']
                                    sum_code = code_2000 - code_2700
                                    if sum_code > 0:
                                      temp_member['count_ptp_all_days'] += 1
                                      temp_member['paid_amount_all_days'] += sum_code



                              # paid
                              temp_member['count_paid']  = 0
                              temp_member['paid_amount'] = 0
                              for acc_assign in account_assign_arr:
                                aggregate_paid = [
                                    {
                                        "$match":
                                        {
                                            "created_at": {'$gte': todayTimeStamp, '$lte': endTodayTimeStamp},
                                            "account_number": acc_assign,
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
                                paidAssignData = mongodb.aggregate_pipeline(MONGO_COLLECTION=gl_collection,aggregate_pipeline=aggregate_paid)
                                code_2000 = 0
                                code_2700 = 0
                                sum_code = 0
                                if paidAssignData != None:
                                    for row in paidAssignData:
                                      if row['code'] == '2000' or row['code'] == '2100':
                                        code_2000 += row['amount']
                                      else:
                                        code_2700 += row['amount']
                                    sum_code = code_2000 - code_2700
                                    if sum_code > 0:
                                      temp_member['count_paid'] += 1
                                      temp_member['paid_amount'] += sum_code



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
                          temp['paid_amount_promise']     += temp_member['paid_amount_promise'];
                          temp['count_ptp_all_days']      += temp_member['count_ptp_all_days']
                          temp['paid_amount_all_days']     += temp_member['paid_amount_all_days']


                          temp_member['call_rate']     = temp_member['number_of_call']/temp_member['count_contacted'] if temp_member['count_contacted'] != 0 else 0
                          temp_member['ptp_rate_acc']  = temp_member['count_ptp']/temp_member['count_contacted'] if temp_member['count_contacted'] != 0 else 0
                          temp_member['ptp_rate_amt']  = temp_member['ptp_amount']/temp_member['contacted_amount'] if temp_member['contacted_amount'] != 0  else 0
                          temp_member['paid_rate_acc'] = temp_member['count_paid_promise']/temp_member['count_ptp'] if temp_member['count_ptp'] != 0 else 0
                          temp_member['paid_rate_amt'] = temp_member['paid_amount_promise']/temp_member['ptp_amount'] if temp_member['ptp_amount'] != 0 else 0
                          temp_member['conn_rate']     = temp_member['count_conn']/temp_member['count_contacted'] if temp_member['count_contacted'] != 0 else 0
                          temp_member['collect_ratio_acc'] = temp_member['count_paid']/temp_member['count_contacted'] if temp_member['count_contacted'] != 0 else 0
                          temp_member['collect_ratio_amt'] = temp_member['paid_amount']/temp_member['contacted_amount'] if temp_member['contacted_amount'] != 0 else 0
                          temp_member['createdAt'] = todayTimeStamp
                          temp_member['createdBy'] = 'system'
                          temp_member['for_month'] = month

                          # pprint(temp_member)
                          member_arr.append(temp_member)

                      temp['call_rate']     = temp['number_of_call']/temp['count_contacted']  if temp['count_contacted'] != 0 else 0
                      temp['ptp_rate_acc']  = temp['count_ptp']/temp['count_contacted']  if temp['count_contacted'] != 0 else 0
                      temp['ptp_rate_amt']  = temp['ptp_amount']/temp['contacted_amount']  if temp['contacted_amount'] != 0 else 0
                      temp['paid_rate_acc'] = temp['count_paid_promise']/temp['count_ptp']  if temp['count_ptp'] != 0 else 0
                      temp['paid_rate_amt'] = temp['paid_amount_promise']/temp['ptp_amount'] if temp['ptp_amount'] != 0 else 0
                      temp['conn_rate']     = temp['count_conn']/temp['count_contacted'] if temp['count_contacted'] != 0 else 0
                      temp['collect_ratio_acc'] = temp['count_paid']/temp['count_contacted']  if temp['count_contacted'] != 0 else 0
                      temp['collect_ratio_amt'] = temp['paid_amount']/temp['contacted_amount']  if temp['contacted_amount'] != 0 else 0
                      temp['createdAt'] = todayTimeStamp
                      temp['createdBy'] = 'system'
                      temp['for_month'] = month


                      insertData.append(temp)
                      insertData += member_arr;

                      i += 1

          checkGroupA = 'true'


        if debtGroupCell[0:1] != 'A' and debtGroupCell[0:1] != 'F':
            i = 1
            for groupProduct in list(listGroupProduct):
                teams = mongodb.getOne(MONGO_COLLECTION=diallist_collection, WHERE={'group_name' : {'$regex' : groupProduct['text'] + '/Group ' + debtGroupCell[0:1] + '/'+ debtGroupCell}, "createdAt": {'$gte': yesterdayTimeStamp, '$lte': endYesterdayTimeStamp} } )
                groupInfo = mongodb.getOne(MONGO_COLLECTION=group_collection, WHERE={'name' : {'$regex' : groupProduct['text'] + '/Group ' + debtGroupCell[0:1] + '/'+ debtGroupCell} } )
                if teams != None:
                      groupTeam = []
                      name = ''

                      print(teams['name'])
                      temp = {
                          'name'           : teams['group_name'],
                          'group'          : debtGroupCell[0:1],
                          'team'           : i,
                          'date'           : todayTimeStamp,
                          'extension'      : groupInfo['lead'],
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
                          'count_ptp_all_days'        : 0,
                          'paid_amount_all_days'      : 0,
                      }

                      # members
                      member_arr = []
                      list_members = teams['members']
                      member_arr_sort = []
                      for member in list_members:
                          temp_member = [member]
                          users = _mongodb.getOne(MONGO_COLLECTION=user_collection,WHERE={'extension': str(member)}, SELECT=['extension','agentname'])
                          if users != None:
                              temp_member.append(users['agentname'])

                          member_arr_sort.append(temp_member)

                      sorted_l = sorted(member_arr_sort, key=lambda x: x[1])

                      for members in sorted_l:
                          member = members[0]
                          temp_member = {
                              'name'           : members[1],
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
                              'count_ptp_all_days'        : 0,
                              'paid_amount_all_days'      : 0,
                          }
                          
                          # account assign
                          aggregate_diallist = [
                              {
                                  "$match":
                                  {
                                      "createdAt": {'$gte': yesterdayTimeStamp, '$lte': endYesterdayTimeStamp},
                                      "assign": str(member),
                                      "diallist_id": teams['_id']
                                  }
                              },{
                                  "$group":
                                  {
                                      "_id": 'null',
                                      "acc_arr": {'$addToSet': '$account_number'},
                                      "count_data": {'$sum': 1},
                                  }
                              }
                          ]
                          diallistData = mongodb.aggregate_pipeline(MONGO_COLLECTION=diallist_detail_collection,aggregate_pipeline=aggregate_diallist)
                          account_assign_arr = []
                          if diallistData != None:
                              for row in diallistData:
                                  temp_member['count_data']   = row['count_data']
                                  account_assign_arr          = row['acc_arr']
                          
                         
                          # unwork
                          aggregate_contacted = [
                              {
                                  "$match":
                                  {
                                      "createdAt": {'$gte': yesterdayTimeStamp, '$lte': endYesterdayTimeStamp},
                                      "assign": str(member),
                                      # "diallist_id" : teams['_id'],
                                      "callResult" : {'$exists': 'true'},
                                      "callResult.userextension": str(member)
                                  }
                              },{
                                  "$group":
                                  {
                                      "_id": 'null',
                                      "count_work": {'$sum': 1},
                                  }
                              }
                          ]
                          contactData = mongodb.aggregate_pipeline(MONGO_COLLECTION=diallist_detail_collection,aggregate_pipeline=aggregate_contacted)
                          count_work = 0
                          if contactData != None:
                              for row in contactData:
                                  count_work                  = row['count_work']

                          temp_member['work']               = count_work
                          temp_member['unwork']             = temp_member['count_data'] - count_work

                          # talk time
                          aggregate_cdr = [
                              {
                                  "$match":
                                  {
                                      "starttime" : {'$gte': yesterdayTimeStamp, '$lte': endYesterdayTimeStamp},
                                      "direction" : "outbound",
                                      "userextension": str(member),
                                      # "diallist_id" : teams['_id'],
                                  }
                              },{
                                  "$group":
                                  {
                                      "_id": 'null',
                                      "talk_time": {'$sum': '$totalduration'},
                                  }
                              }
                          ]
                          cdrData = mongodb.aggregate_pipeline(MONGO_COLLECTION=cdr_collection,aggregate_pipeline=aggregate_cdr)
                          
                          disposition_arr = []
                          if cdrData != None:
                              for row in cdrData:
                                  temp_member['talk_time']            = row['talk_time']

                          # contacted
                          aggregate_contacted = [
                              {
                                  "$match":
                                  {
                                      "starttime": {'$gte': yesterdayTimeStamp, '$lte': endYesterdayTimeStamp},
                                      "userextension": str(member),
                                      "direction" : "outbound",
                                      # "diallist_id" : teams['_id'],
                                  }
                              }
                              # ,{
                              #     "$group":
                              #     {
                              #         "_id": 'null',
                              #         "phone_contact_arr": {'$addToSet': '$customernumber'},
                              #         "dialid_arr": {'$addToSet': '$dialid'},
                              #     }
                              # }
                          ]
                          contactData = mongodb.aggregate_pipeline(MONGO_COLLECTION=cdr_collection,aggregate_pipeline=aggregate_contacted)
                          acc_contact_arr_temp = []
                          if contactData != None:
                              for cdr in contactData:
                                diallistInfo = None
                                if 'dialid' in cdr.keys():
                                  if len(str(cdr['dialid'])) == 24:
                                    diallistInfo = mongodb.getOne(MONGO_COLLECTION=diallist_detail_collection, WHERE={'_id': ObjectId(str(cdr['dialid'])),  "createdAt" : {'$gte' : yesterdayTimeStamp,'$lte' : endYesterdayTimeStamp}}) 
                                  else:
                                    diallistInfo = mongodb.getOne(MONGO_COLLECTION=diallist_detail_collection, WHERE={'$or' : [{ 'mobile_num' : str(cdr['customernumber'])}, { 'phone' : str(cdr['customernumber'])},  { 'other_phones' :str(cdr['customernumber'])} ],  "createdAt" : {'$gte' : yesterdayTimeStamp,'$lte' : endYesterdayTimeStamp} }) 

                                if 'dialid' not in cdr.keys():
                                  diallistInfo = mongodb.getOne(MONGO_COLLECTION=diallist_detail_collection, WHERE={'$or' : [{ 'mobile_num' : str(cdr['customernumber'])}, { 'phone' : str(cdr['customernumber'])},  { 'other_phones' :str(cdr['customernumber'])} ],  "createdAt" : {'$gte' : yesterdayTimeStamp,'$lte' : endYesterdayTimeStamp} }) 

                                if diallistInfo != None:
                                  acc_contact_arr_temp.append(diallistInfo['account_number'])

                          set_acc_contact = set(acc_contact_arr_temp) 
                          acc_contact_arr = list(set_acc_contact)
                          temp_member['count_contacted']      = len(acc_contact_arr)
                                  


                          # call made
                          calMade = mongodb.count(MONGO_COLLECTION=cdr_collection, WHERE={'disposition': 'ANSWERED', "direction" : "outbound", "userextension": str(member),  "starttime": {'$gte': yesterdayTimeStamp, '$lte': endYesterdayTimeStamp}} )
                          if calMade > 0:
                            temp_member['number_of_call'] = calMade

                          totalCall = mongodb.count(MONGO_COLLECTION=cdr_collection, WHERE={ "direction" : "outbound", "userextension": str(member), "starttime": {'$gte': yesterdayTimeStamp, '$lte': endYesterdayTimeStamp}} )
                          if totalCall > 0:
                            temp_member['total_call'] = totalCall


                          # connected
                          action_code = ['PTP', 'CHECK', 'LM', 'PTP Today','Spaid']
                          aggregate_cdr_ans = [
                              {
                                  "$match":
                                  {
                                      "starttime": {'$gte': yesterdayTimeStamp, '$lte': endYesterdayTimeStamp},
                                      "userextension": str(member),
                                      "disposition" : "ANSWERED",
                                      "direction" : "outbound",
                                      # "diallist_id" : teams['_id'],
                                  }
                              }
                          ]
                          cdrAnsData = mongodb.aggregate_pipeline(MONGO_COLLECTION=cdr_collection,aggregate_pipeline=aggregate_cdr_ans)
                          acc_ans_arr = []
                          if cdrAnsData != None:
                            for cdr in cdrAnsData:
                              aggregate_pipeline_action = [
                                {
                                     "$match":
                                     {
                                         "calluuid" : cdr['calluuid'],
                                         "action_code" : {'$in': action_code}
                                     }
                                },{
                                     "$project":
                                     {
                                         "_id": 0,
                                         # "LIC_NO": 0,
                                         "account_number": 1,
                                     }
                                }
                                      
                              ]
                              accountData = mongodb.aggregate_pipeline(MONGO_COLLECTION=action_code_collection,aggregate_pipeline=aggregate_pipeline_action)
                              if accountData != None:
                                for row in accountData:
                                  temp_member['count_conn'] += 1
                                  acc_ans_arr.append(row['account_number'])

                          

                          # PTP
                          account_ptp_arr = []
                          for acc in acc_contact_arr:
                            where = {
                                        "createdAt": {'$gte': yesterdayTimeStamp, '$lte': endYesterdayTimeStamp},
                                        "account_number": acc,
                                        'action_code' :  {'$in': ['PTP','PTP Today', 'CHECK']}
                            }
                            actionCodeData = mongodb.get(MONGO_COLLECTION=action_code_collection, WHERE=where,SELECT=['account_number','createdBy'],SORT=[("_id", -1)], SKIP=0, TAKE=1)
                            if actionCodeData != None:
                              for row in actionCodeData:
                                if row['createdBy'] == str(member):
                                  account_ptp_arr.append(row['account_number'])
                                  temp_member['count_ptp']            += 1


                          # PTP all days
                          account_ptp_all_days_arr = []
                          for acc in acc_contact_arr:
                            where = {
                                        "account_number": acc,
                                        'action_code' :  {'$in': ['PTP','PTP Today', 'CHECK']}
                            }
                            actionCodeData = mongodb.get(MONGO_COLLECTION=action_code_collection, WHERE=where,SELECT=['account_number','createdBy'],SORT=[("_id", -1)], SKIP=0, TAKE=1)
                            if actionCodeData != None:
                              for row in actionCodeData:
                                if row['createdBy'] == str(member):
                                  account_ptp_all_days_arr.append(row['account_number'])
                          

                          if groupProduct['value'] == 'SIBS':
                              aggregate_cdr_amt = [
                                  {
                                      "$match":
                                      {
                                          "account_number": {'$in': acc_contact_arr},
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
                                          "code" : '10'
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

                              
                              # PTP all days
                              aggregate_paid_all_days = [
                                  {
                                      "$match":
                                      {
                                          "created_at": {'$gte': todayTimeStamp, '$lte': endTodayTimeStamp},
                                          "account_number": {'$in': account_ptp_all_days_arr},
                                          "code" : '10'
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
                              paidAllDaysData = mongodb.aggregate_pipeline(MONGO_COLLECTION=ln3206f_collection,aggregate_pipeline=aggregate_paid_all_days)
                              if paidAllDaysData != None:
                                  for row in paidAllDaysData:
                                      temp_member['count_ptp_all_days']            = len(row['count_paid_promise'] )
                                      temp_member['paid_amount_all_days']           = row['paid_amount_promise']



                              # paid
                              aggregate_paid = [
                                  {
                                      "$match":
                                      {
                                          "created_at": {'$gte': todayTimeStamp, '$lte': endTodayTimeStamp},
                                          "account_number": {'$in': account_assign_arr},
                                          "code" : '10'
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
                                          "account_number": {'$in': acc_contact_arr},
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

                              

                              temp_member['count_paid_promise']  = 0
                              temp_member['paid_amount_promise'] = 0
                              for acc_ptp in account_ptp_arr:
                                aggregate_paid_ptp = [
                                    {
                                        "$match":
                                        {
                                            "created_at": {'$gte': todayTimeStamp, '$lte': endTodayTimeStamp},
                                            "account_number": acc_ptp,
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
                                paidPTPData = mongodb.aggregate_pipeline(MONGO_COLLECTION=gl_collection,aggregate_pipeline=aggregate_paid_ptp)
                                code_2000 = 0
                                code_2700 = 0
                                sum_code = 0
                                if paidPTPData != None:
                                    for row in paidPTPData:
                                      if row['code'] == '2000' or row['code'] == '2100':
                                        code_2000 += row['amount']
                                      else:
                                        code_2700 += row['amount']
                                    sum_code = code_2000 - code_2700
                                    if sum_code > 0:
                                      temp_member['count_paid_promise'] += 1
                                      temp_member['paid_amount_promise'] += sum_code



                              # PTP all days
                              temp_member['count_ptp_all_days']  = 0
                              temp_member['paid_amount_all_days'] = 0
                              for acc_ptp_all_day in account_ptp_all_days_arr:
                                aggregate_paid_all_days = [
                                    {
                                        "$match":
                                        {
                                            "created_at": {'$gte': todayTimeStamp, '$lte': endTodayTimeStamp},
                                            "account_number": acc_ptp_all_day,
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
                                paidPTPAllDayData = mongodb.aggregate_pipeline(MONGO_COLLECTION=gl_collection,aggregate_pipeline=aggregate_paid_all_days)
                                code_2000 = 0
                                code_2700 = 0
                                sum_code = 0
                                if paidPTPAllDayData != None:
                                    for row in paidPTPAllDayData:
                                      if row['code'] == '2000' or row['code'] == '2100':
                                        code_2000 += row['amount']
                                      else:
                                        code_2700 += row['amount']
                                    sum_code = code_2000 - code_2700
                                    if sum_code > 0:
                                      temp_member['count_ptp_all_days'] += 1
                                      temp_member['paid_amount_all_days'] += sum_code



                              # paid
                              temp_member['count_paid']  = 0
                              temp_member['paid_amount'] = 0
                              for acc_assign in account_assign_arr:
                                aggregate_paid = [
                                    {
                                        "$match":
                                        {
                                            "created_at": {'$gte': todayTimeStamp, '$lte': endTodayTimeStamp},
                                            "account_number": acc_assign,
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
                                paidAssignData = mongodb.aggregate_pipeline(MONGO_COLLECTION=gl_collection,aggregate_pipeline=aggregate_paid)
                                code_2000 = 0
                                code_2700 = 0
                                sum_code = 0
                                if paidAssignData != None:
                                    for row in paidAssignData:
                                      if row['code'] == '2000' or row['code'] == '2100':
                                        code_2000 += row['amount']
                                      else:
                                        code_2700 += row['amount']
                                    sum_code = code_2000 - code_2700
                                    if sum_code > 0:
                                      temp_member['count_paid'] += 1
                                      temp_member['paid_amount'] += sum_code


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
                          temp['paid_amount_promise']     += temp_member['paid_amount_promise'];
                          temp['count_ptp_all_days']      += temp_member['count_ptp_all_days']
                          temp['paid_amount_all_days']    += temp_member['paid_amount_all_days']


                          temp_member['call_rate']     = temp_member['number_of_call']/temp_member['count_contacted'] if temp_member['count_contacted'] != 0 else 0
                          temp_member['ptp_rate_acc']  = temp_member['count_ptp']/temp_member['count_contacted'] if temp_member['count_contacted'] != 0 else 0
                          temp_member['ptp_rate_amt']  = temp_member['ptp_amount']/temp_member['contacted_amount'] if temp_member['contacted_amount'] != 0  else 0
                          temp_member['paid_rate_acc'] = temp_member['count_paid_promise']/temp_member['count_ptp'] if temp_member['count_ptp'] != 0 else 0
                          temp_member['paid_rate_amt'] = temp_member['paid_amount_promise']/temp_member['ptp_amount'] if temp_member['ptp_amount'] != 0 else 0
                          temp_member['conn_rate']     = temp_member['count_conn']/temp_member['count_contacted'] if temp_member['count_contacted'] != 0 else 0
                          temp_member['collect_ratio_acc'] = temp_member['count_paid']/temp_member['count_contacted'] if temp_member['count_contacted'] != 0 else 0
                          temp_member['collect_ratio_amt'] = temp_member['paid_amount']/temp_member['contacted_amount'] if temp_member['contacted_amount'] != 0 else 0
                          temp_member['createdAt'] = todayTimeStamp
                          temp_member['createdBy'] = 'system'
                          temp_member['for_month'] = month

                          # pprint(temp_member)
                          member_arr.append(temp_member)

                      temp['call_rate']     = temp['number_of_call']/temp['count_contacted']  if temp['count_contacted'] != 0 else 0
                      temp['ptp_rate_acc']  = temp['count_ptp']/temp['count_contacted']  if temp['count_contacted'] != 0 else 0
                      temp['ptp_rate_amt']  = temp['ptp_amount']/temp['contacted_amount']  if temp['contacted_amount'] != 0 else 0
                      temp['paid_rate_acc'] = temp['count_paid_promise']/temp['count_ptp']  if temp['count_ptp'] != 0 else 0
                      temp['paid_rate_amt'] = temp['paid_amount_promise']/temp['ptp_amount']  if temp['ptp_amount'] != 0 else 0
                      temp['conn_rate']     = temp['count_conn']/temp['count_contacted'] if temp['count_contacted'] != 0 else 0
                      temp['collect_ratio_acc'] = temp['count_paid']/temp['count_contacted']  if temp['count_contacted'] != 0 else 0
                      temp['collect_ratio_amt'] = temp['paid_amount']/temp['contacted_amount']  if temp['contacted_amount'] != 0 else 0
                      temp['createdAt'] = todayTimeStamp
                      temp['createdBy'] = 'system'
                      temp['for_month'] = month


                      insertData.append(temp)
                      insertData += member_arr;

                      i += 1



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



    if len(insertData) > 0:
        mongodb.batch_insert(MONGO_COLLECTION=collection, insert_data=insertData)

    now_end         = datetime.now()
    log.write(now_end.strftime("%d/%m/%Y, %H:%M:%S") + ': End Log' + '\n')
    print('DONE')
except Exception as e:
    log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
    print(traceback.format_exc())

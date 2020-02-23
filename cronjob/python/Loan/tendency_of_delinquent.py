#!/usr/bin/python3.6
# -*- coding: utf-8 -*-
import sys
import os
import time
import ntpath
import json
import calendar
import traceback
import math
from helper.mongod import Mongodb
from datetime import datetime
from datetime import date
from pprint import pprint
from bson import ObjectId
from helper.common import Common
from helper.jaccs import Config
# help round down
def round_down(n, decimals=0):
    multiplier = 10 ** decimals
    return math.floor(n * multiplier) / multiplier
#help
common      = Common()
base_url    = common.base_url()
wff_env     = common.wff_env(base_url)
mongodb     = Mongodb(MONGODB="worldfone4xs", WFF_ENV=wff_env)
_mongodb    = Mongodb(MONGODB="_worldfone4xs", WFF_ENV=wff_env)
now         = datetime.now()
subUserType = 'LO'

collection           = common.getSubUser(subUserType, 'Thu_hoi_xe')


action_collection     = common.getSubUser(subUserType, 'Action_code')
zaccf_collection     = common.getSubUser(subUserType, 'ZACCF')


investigation_collection  = common.getSubUser(subUserType, 'Investigation_file')

product_collection   = common.getSubUser(subUserType, 'Product')
wo_monthly_collection     = common.getSubUser(subUserType, 'WO_monthly')
diallist_collection       = common.getSubUser(subUserType, 'Diallist_detail')
user_collection           = common.getSubUser(subUserType, 'User')

log         = open(base_url + "cronjob/python/Loan/log/Thuhoixe_log.txt","a")
log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': Start Import' + '\n')
try:
   data        = []
   cardData        = []
   insertData  = []
   resultData  = []
   errorData   = []

   today = date.today()
   # today = datetime.strptime('12/10/2019', "%d/%m/%Y").date()

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

   users = _mongodb.get(MONGO_COLLECTION=user_collection, SELECT=['extension','agentname'],SORT=([('_id', -1)]),SKIP=0, TAKE=200)


   # thuhoixe
   aggregate_pipeline = [
      
       {
           "$project":
           {
            #    col field
               "account_number": 1,
               "ngay_thu": 1, 
               "ngay_ban": 1,
               "action_code": 1,
               "raaStatus": 1,
               "note": 1,
               "gia_ban": 1,
               'nguoi_thuhoi': 1,
               'ngayguithu_thongbaohoantat_thuhoi_ts': 1,
               'ngay_gui_thu_thong_bao': 1,
               'ngay_guithuthongbao_hoantat_vaban_taisan': 1,
               'hinhthuc_xuly_ts': 1,
               'ngayguithu_thongbao_xulydaugia': 1,
               'ngay_dau_gia': 1,
               'chiphi_thamdinhgia': 1,
               'chiphi_daugia': 1,
               'chiphi_khac': 1,
               'sum_tien_con_lai_chuyen_ve_tkkh': 1,
               'ngaytienve_tkkh_dot1': 1,
               'ngaytrutien_dethanhtoanquahan': 1,
               'ngaytienve_tkkh_dotcuoi': 1,
               'ngay_trutien_giamdunogoc': 1,
               'ngayyeucau_itxoabill': 1,
               'sotien_kybill_cuoicung': 1,
               'ngaydenhan_kybill_cuoicung': 1
               
           }
       }
   ]
   data = mongodb.aggregate_pipeline(MONGO_COLLECTION=action_collection,aggregate_pipeline=aggregate_pipeline)
   count = 0
   for row in data:
      if 'account_number' in row.keys() and row['action_code'] == "RAA":
        zaccf = mongodb.getOne(MONGO_COLLECTION=zaccf_collection, WHERE={'account_number': str(row['account_number'])},
            SELECT=['name','ODIND_FG','PRODGRP_ID'])
        if zaccf != None:
            invest = mongodb.getOne(MONGO_COLLECTION=investigation_collection, WHERE={'contract_no': str(row['account_number'])},
                SELECT=['brand','license_plates_no'])     
            product = mongodb.getOne(MONGO_COLLECTION=product_collection, WHERE={'code': str(zaccf['PRODGRP_ID'])},
                SELECT=['name'])     

            if invest != None and product != None:
                count += 1
                temp = {}
               
                temp['createdAt'] = time.time()   
                temp['createdBy'] = 'system'   
                insertData.append(temp)

 

   if len(insertData) > 0:
    #   mongodb.remove_document(MONGO_COLLECTION=collection)
      mongodb.batch_insert(MONGO_COLLECTION=collection, insert_data=insertData)
   now_end         = datetime.now()
   log.write(now_end.strftime("%d/%m/%Y, %H:%M:%S") + ': End Log' + '\n')
   print('DONE')
except Exception as e:
    pprint(e)
    traceback.print_exc()
    log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
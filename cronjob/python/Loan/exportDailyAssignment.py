#!/usr/bin/python3.6
# -*- coding: utf-8 -*-
import sys
import os
import time
import ntpath
import json
import calendar
import urllib
from helper.mongod import Mongodb
from datetime import datetime
from datetime import date
from pprint import pprint
from bson import ObjectId
from helper.common import Common
import pandas as pd
from helper.jaccs import Config
import xlsxwriter
import traceback

common      = Common()
base_url    = common.base_url()
wff_env     = common.wff_env(base_url)
mongodb     = Mongodb(MONGODB="worldfone4xs", WFF_ENV=wff_env)
_mongodb    = Mongodb(MONGODB="_worldfone4xs", WFF_ENV=wff_env)

now         = datetime.now()
subUserType = 'LO'
collection         = common.getSubUser(subUserType, 'Daily_assignment_report')
log         = open(base_url + "cronjob/python/Loan/log/exportDailyAssignment_log.txt","a")
log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': Start Import' + '\n')


# fileOutput = "C:\\Users\\DELL\\Desktop\\temp-excel.xlsx"
try:
   data        = []
   insertData  = []
   resultData  = []
   errorData   = []

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

   fileOutput  = base_url + 'upload/loan/export/DailyAssignment_'+ today.strftime("%d%m%Y") +'.xlsx'

   try:
      date = sys.argv[1]
      today = datetime.strptime(date, "%d/%m/%Y").date()
      todayString = today.strftime("%d/%m/%Y")
      todayTimeStamp = int(time.mktime(time.strptime(str(todayString + " 00:00:00"), "%d/%m/%Y %H:%M:%S")))
      endTodayTimeStamp = int(time.mktime(time.strptime(str(todayString + " 23:59:59"), "%d/%m/%Y %H:%M:%S")))
      aggregate_acc = [
         {
             "$match":
             {
                 "createdAt" : {'$gte' : todayTimeStamp,'$lte' : endTodayTimeStamp},
             }
         },
         {
            "$project":
             {
                 "_id": 0,
             }
         }
      ]

   except Exception as SysArgvError:
      aggregate_acc = [
         {
             "$match":
             {
                 "createdAt" : {'$gte' : todayTimeStamp,'$lte' : endTodayTimeStamp},
             }
         },
         {
            "$project":
             {
                 "_id": 0,
             }
         }
      ]

   data = mongodb.aggregate_pipeline(MONGO_COLLECTION=collection,aggregate_pipeline=aggregate_acc)
   dataReport = []
   for row in data:
      temp = row
      # if 'loan_overdue_amount' in row.keys():
      #    temp['loan_overdue_amount']      = '{:,.2f}'.format(float(row['loan_overdue_amount']))

      # if 'current_balance' in row.keys():
      #    temp['current_balance']      = '{:,.2f}'.format(float(row['current_balance']))

      # if 'outstanding_principal' in row.keys():
      #    try:
      #       temp['outstanding_principal']      = '{:,.2f}'.format(float(row['outstanding_principal']))
      #    except Exception as e:
      #       temp['outstanding_principal']      = row['outstanding_principal']


      try:
         if 'overdue_date' in row.keys():
            date_time = datetime.fromtimestamp(int(row['overdue_date']))
            temp['overdue_date']      = date_time.strftime('%d-%m-%Y')
      except Exception as e:
         temp['overdue_date']      = row['overdue_date']
      # if 'overdue_date' in row.keys():
      #    date_time = datetime.fromtimestamp(int(row['overdue_date']))
      #    temp['overdue_date']      = date_time.strftime('%d-%m-%Y')

      if 'created_date' in row.keys():
         if row['created_date'] != None:
            date_time = datetime.fromtimestamp(row['created_date'])
            temp['created_date']      = date_time.strftime('%d-%m-%Y')
         else:
            temp['created_date']      = ''

      if 'ngay_guifc' in row.keys():
         if row['ngay_guifc'] != None:
            date_time = datetime.fromtimestamp(row['ngay_guifc'])
            temp['ngay_guifc']      = date_time.strftime('%d-%m-%Y')

      if 'ngay_nopdon_submitingdate' in row.keys():
         if row['ngay_nopdon_submitingdate'] != None:
            date_time = datetime.fromtimestamp(row['ngay_nopdon_submitingdate'])
            temp['ngay_nopdon_submitingdate']      = date_time.strftime('%d-%m-%Y')

      if 'ngay_nop_tuap' in row.keys():
         if row['ngay_nop_tuap'] != None:
            date_time = datetime.fromtimestamp(row['ngay_nop_tuap'])
            temp['ngay_nop_tuap']      = date_time.strftime('%d-%m-%Y')

      if 'ngay_nhanthongbao_thuly' in row.keys():
         if row['ngay_nhanthongbao_thuly'] != None:
            date_time = datetime.fromtimestamp(row['ngay_nhanthongbao_thuly'])
            temp['ngay_nhanthongbao_thuly']      = date_time.strftime('%d-%m-%Y')

      if 'ngay_hoagiai1' in row.keys():
         if row['ngay_hoagiai1'] != None:
            date_time = datetime.fromtimestamp(row['ngay_hoagiai1'])
            temp['ngay_hoagiai1']      = date_time.strftime('%d-%m-%Y')

      if 'ngay_hoagiai2' in row.keys():
         if row['ngay_hoagiai2'] != None:
            date_time = datetime.fromtimestamp(row['ngay_hoagiai2'])
            temp['ngay_hoagiai2']      = date_time.strftime('%d-%m-%Y')

      if 'ngay_hoagiai3' in row.keys():
         if row['ngay_hoagiai3'] != None:
            date_time = datetime.fromtimestamp(row['ngay_hoagiai3'])
            temp['ngay_hoagiai3']      = date_time.strftime('%d-%m-%Y')

      if 'ngay_xetxu_sotham' in row.keys():
         if row['ngay_xetxu_sotham'] != None:
            date_time = datetime.fromtimestamp(row['ngay_xetxu_sotham'])
            temp['ngay_xetxu_sotham']      = date_time.strftime('%d-%m-%Y')

      if 'ngay_xetxu_phuctham' in row.keys():
         if row['ngay_xetxu_phuctham'] != None:
            date_time = datetime.fromtimestamp(row['ngay_xetxu_phuctham'])
            temp['ngay_xetxu_phuctham']      = date_time.strftime('%d-%m-%Y')

      if 'promised_date' in row.keys():
         if row['promised_date'] != None:
            date_time = datetime.fromtimestamp(row['promised_date'])
            temp['promised_date']      = date_time.strftime('%d-%m-%Y')
         else:
            temp['promised_date']      = ''
         # break

      if 'ngay_gui_thu_thong_bao' in row.keys():
         if row['ngay_gui_thu_thong_bao'] != None:
            date_time = datetime.fromtimestamp(row['ngay_gui_thu_thong_bao'])
            temp['ngay_gui_thu_thong_bao']      = date_time.strftime('%d-%m-%Y')

      if 'ngay_dau_gia' in row.keys():
         if row['ngay_dau_gia'] != None:
            date_time = datetime.fromtimestamp(row['ngay_dau_gia'])
            temp['ngay_dau_gia']      = date_time.strftime('%d-%m-%Y')

      if 'ngay_trutien_giamdunogoc' in row.keys():
         if row['ngay_trutien_giamdunogoc'] != None:
            date_time = datetime.fromtimestamp(row['ngay_trutien_giamdunogoc'])
            temp['ngay_trutien_giamdunogoc']      = date_time.strftime('%d-%m-%Y')

      if 'ngay_guithuthongbao_hoantat_vaban_taisan' in row.keys():
         if row['ngay_guithuthongbao_hoantat_vaban_taisan'] != None:
            date_time = datetime.fromtimestamp(row['ngay_guithuthongbao_hoantat_vaban_taisan'])
            temp['ngay_guithuthongbao_hoantat_vaban_taisan']      = date_time.strftime('%d-%m-%Y')

      if 'ngaytienve_tkkh_dot1' in row.keys():
         if row['ngaytienve_tkkh_dot1'] != None:
            date_time = datetime.fromtimestamp(row['ngaytienve_tkkh_dot1'])
            temp['ngaytienve_tkkh_dot1']      = date_time.strftime('%d-%m-%Y')

      if 'ngayyeucau_itxoabill' in row.keys():
         if row['ngayyeucau_itxoabill'] != None:
            date_time = datetime.fromtimestamp(row['ngayyeucau_itxoabill'])
            temp['ngayyeucau_itxoabill']      = date_time.strftime('%d-%m-%Y')

      if 'ngaytrutien_dethanhtoanquahan' in row.keys():
         if row['ngaytrutien_dethanhtoanquahan'] != None:
            date_time = datetime.fromtimestamp(row['ngaytrutien_dethanhtoanquahan'])
            temp['ngaytrutien_dethanhtoanquahan']      = date_time.strftime('%d-%m-%Y')

      if 'ngayguithu_thongbaohoantat_thuhoi_ts' in row.keys():
         if row['ngayguithu_thongbaohoantat_thuhoi_ts'] != None:
            date_time = datetime.fromtimestamp(row['ngayguithu_thongbaohoantat_thuhoi_ts'])
            temp['ngayguithu_thongbaohoantat_thuhoi_ts']      = date_time.strftime('%d-%m-%Y')

      if 'ngayguithu_thongbao_xulydaugia' in row.keys():
         if row['ngayguithu_thongbao_xulydaugia'] != None:
            date_time = datetime.fromtimestamp(row['ngayguithu_thongbao_xulydaugia'])
            temp['ngayguithu_thongbao_xulydaugia']      = date_time.strftime('%d-%m-%Y')

      if 'ngaytienve_tkkh_dotcuoi' in row.keys():
         if row['ngaytienve_tkkh_dotcuoi'] != None:
            date_time = datetime.fromtimestamp(row['ngaytienve_tkkh_dotcuoi'])
            temp['ngaytienve_tkkh_dotcuoi']      = date_time.strftime('%d-%m-%Y')

      if 'ngaydenhan_kybill_cuoicung' in row.keys():
         if row['ngaydenhan_kybill_cuoicung'] != None:
            date_time = datetime.fromtimestamp(row['ngaydenhan_kybill_cuoicung'])
            temp['ngaydenhan_kybill_cuoicung']      = date_time.strftime('%d-%m-%Y')

      if 'payment_date' in row.keys():
         if row['payment_date'] != None:
            date_time = datetime.fromtimestamp(row['payment_date'])
            temp['payment_date']      = date_time.strftime('%d-%m-%Y')

      dataReport.append(temp)

   
   df = pd.DataFrame(dataReport, columns= ['export_date','index','group_id','account_number','name','overdue_date','loan_overdue_amount','current_balance','outstanding_principal','assign','chief','contacted','connected','product_id','action_code','note','created_date','so_hopdong','ten_khachhang','quanhuyen','tinhthanh','nguoiduoc_uyquyen','nogoc_hopdong','lai_hopdong','tongcong_hopdong','tienhangthang_hopdong','nogoc_sotiendathanhtoan','lai_sotiendathanhtoan','phat_sotiendathanhtoan','tongcong_dunokhoikien','nogoc_dunokhoikien','lai_dunokhoikien','phat_dunokhoikien','phitattoan_dunokhoikien','tongcong_dunokhoikien','ten_cuahang','diachi_cuahang','tamung_anphi','phuongthuc_nop','ngay_guifc','ngay_nopdon_submitingdate','ngay_nop_tuap','ngay_nhanthongbao_thuly','ngay_hoagiai1','ngay_hoagiai2','ngay_hoagiai3','ngay_xetxu_sotham','khangcao','ngay_xetxu_phuctham','theo_doi','phuonghuong_giaiquyet','tinhtrang_khoikien','ngay_tamung_anphi','sotien_tamung','chuaduoc_hoantra_anphi_saukhirutdon','daduoc_hoantra_tienanphi','ngay_duochoantra_anphi','thamphan','ngay_guihosovenha_kh','sobill_guihosovenha_kh','ngay_nopdon','sobill_nopdon','promised_amount','promised_person','reason_nonpayment','promised_date','raaStatus','ngay_gui_thu_thong_bao','ngay_dau_gia','sum_tien_con_lai_chuyen_ve_tkkh','ngay_trutien_giamdunogoc','gia_ban','ngay_guithuthongbao_hoantat_vaban_taisan','chiphi_thamdinhgia','ngaytienve_tkkh_dot1','ngayyeucau_itxoabill','nguoi_thuhoi','hinhthuc_xuly_ts','chiphi_daugia','ngaytrutien_dethanhtoanquahan','sotien_kybill_cuoicung','ngayguithu_thongbaohoantat_thuhoi_ts','ngayguithu_thongbao_xulydaugia','chiphi_khac','ngaytienve_tkkh_dotcuoi','ngaydenhan_kybill_cuoicung','death_info','contact_person','reason_die','contact_person_phone','payment_amount','payment_date','payment_person','channel','promised_person_phone','fc_name'])

   # Create a Pandas Excel writer using XlsxWriter as the engine.
   writer = pd.ExcelWriter(fileOutput, engine='xlsxwriter')

   # Convert the dataframe to an XlsxWriter Excel object.
   df.to_excel(writer,sheet_name='Sheet1',index=False,header=['Export Date','No','GROUP','Account Number','Customer name','Overdue date','Overdue amount','Outstanding balance','Outstanding principal','Staff in charge','Team leader','Contacted number','Connected number','Product type','Action code','Note','Tháng','Số hợp đồng','Tên Khách hàng','Địa điểm (Quận/Huyện)','Địa điểm (Tỉnh/Thành)','Người được ủy quyền','Nợ gốc Hợp đồng','Lãi Hợp đồng','Tổng cộng Hợp đồng','Tiền hàng tháng Hợp đồng','Nợ gốc Số tiền đã thanh toán','Lãi Số tiền đã thanh toán','Phạt Số tiền đã thanh toán','Tổng cộng Số tiền đã thanh toán','Nợ gốc Dư nợ khởi kiện','Lãi Dư nợ khởi kiện','Phạt Dư nợ khởi kiện','Phí tất toán Dư nợ khởi kiện','Tổng cộng Dư nợ khởi kiện','Tên cửa hàng','Địa chỉ cửa hàng','Tạm ứng án phí (dự tính)','Phương thức nộp','Ngày gởi FC','Ngày nộp đơn (Submiting date)','Ngày nộp TƯAP','Ngày nhận thông báo thụ lý','Ngày hòa giải lần 1','Ngày hòa giải lần 2','Ngày hòa giải lần 3','Ngày xét xử sơ thẩm','Kháng cáo','Ngày xét xử phúc thẩm','Theo dõi','Phương hướng giải quyết','Tình trạng khởi kiện','Ngày tạm ứng án phí','Số tiền tạm ứng','Chưa được hoàn trả án phí sau khi rút đơn','Đã được hoàn trả tiền án phí','Ngày được hoàn trả án phí','Thẩm phán','Ngày gửi HS về nhà KH','Số bill gửi HS về nhà KH','Ngày nộp đơn','Số bill nộp đơn','Số tiền hứa trả','Người hứa trả','Nguyên nhân không trả','Ngày hứa trả','Status','Ngày gửi thư thông báo định giá tài sản (nếu có)','Ngày đấu giá','Tổng số tiền còn lại chuyển về TK KH','Ngày trừ tiền để giảm dư nợ gốc sau khi xử lý tài sản','Giá bán','Ngày gửi thư thông báo hoàn tất xử lý và bán lại Tài sản thu hồi','Chi phí thẩm định giá','Ngày tiền về TK KH đợt 1','Ngày yêu cầu IT xóa các bill và giữ lại 1 kỳ bill cuối','Người thu hồi','Hình thức xử lý TS','Chi phí đấu giá','Ngày trừ tiền để thanh toán quá hạn sau khi xử lý tài sản','Số tiền kỳ bill cuối cùng','Ngày gửi thư thông báo hoàn tất thu hồi TS (nếu có)','Ngày gửi thư thông báo xử lý TS thông qua đấu giá','Chi phí khác','Ngày tiền về TK KH đợt cuối (nếu có)','Ngày đến hạn của kỳ bill cuối cùng','Giấy báo tử - Ngày cấp','Người liên hệ','Nguyên nhân chết','Số ĐT người thân','Payment amount','Payment date','Payment person','Channel','Promised person phone','FC name'])

   # Get the xlsxwriter workbook and worksheet objects.
   workbook  = writer.book
   worksheet = writer.sheets['Sheet1']

   # Add some cell formats.
   format1 = workbook.add_format({'num_format': '#,##0', 'bottom':1, 'top':1, 'left':1, 'right':1})
   # format2 = workbook.add_format({'num_format': '0%'})
   border_fmt = workbook.add_format({'bottom':1, 'top':1, 'left':1, 'right':1})


   # Set the column width and format.
   worksheet.set_column('A:CR', 20, border_fmt)

   worksheet.set_column('G:G', 20, format1)
   worksheet.set_column('H:H', 20, format1)
   worksheet.set_column('I:I', 20, format1)

   writer.save()

   now_end         = datetime.now()
   log.write(now_end.strftime("%d/%m/%Y, %H:%M:%S") + ': End Log' + '\n')
   print('DONE')
except Exception as e:
   print(traceback.format_exc())
   log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
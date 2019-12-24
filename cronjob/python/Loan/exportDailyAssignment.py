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

# fileOutput  = base_url + 'upload/loan/export/DailyAssignment.xlsx'
fileOutput = "C:\\Users\\Bala\\output\\temp-excel.xlsx"
try:
   data        = []
   insertData  = []
   resultData  = []
   errorData   = []

   today = date.today()
   # today = datetime.strptime('21/12/2019', "%d/%m/%Y").date()

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
                 "createdAt": {'$gte' : todayTimeStamp},
                 "createdAt": {'$lte' : endTodayTimeStamp},
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
                 "createdAt": {'$gte' : todayTimeStamp},
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

   df = pd.DataFrame(data, columns= ['export_date','index','group_id','account_number','name','overdue_date','loan_overdue_amount','current_balance','outstanding_principal','assign','chief','contacted','product_id','action_code','note','created_date','so_hopdong','ten_khachhang','quanhuyen','tinhthanh','nguoiduoc_uyquyen','nogoc_hopdong','lai_hopdong','tongcong_hopdong','tienhangthang_hopdong','nogoc_sotiendathanhtoan','lai_sotiendathanhtoan','phat_sotiendathanhtoan','tongcong_dunokhoikien','nogoc_dunokhoikien','lai_dunokhoikien','phat_dunokhoikien','phitattoan_dunokhoikien','tongcong_dunokhoikien','ten_cuahang','diachi_cuahang','tamung_anphi','phuongthuc_nop','ngay_guifc','ngay_nopdon_submitingdate','ngay_nop_tuap','ngay_nhanthongbao_thuly','ngay_hoagiai1','ngay_hoagiai2','ngay_hoagiai3','ngay_xetxu_sotham','khangcao','ngay_xetxu_phuctham','theo_doi','phuonghuong_giaiquyet','tinhtrang_khoikien','ngay_tamung_anphi','sotien_tamung','chuaduoc_hoantra_anphi_saukhirutdon','daduoc_hoantra_tienanphi','ngay_duochoantra_anphi','thamphan','ngay_guihosovenha_kh','sobill_guihosovenha_kh','ngay_nopdon','sobill_nopdon','promised_amount','promised_person','reason_nonpayment','promised_date','raaStatus','ngay_gui_thu_thong_bao','ngay_dau_gia','sum_tien_con_lai_chuyen_ve_tkkh','ngay_trutien_giamdunogoc','gia_ban','ngay_guithuthongbao_hoantat_vaban_taisan','chiphi_thamdinhgia','ngaytienve_tkkh_dot1','ngayyeucau_itxoabill','nguoi_thuhoi','hinhthuc_xuly_ts','chiphi_daugia','ngaytrutien_dethanhtoanquahan','sotien_kybill_cuoicung','ngayguithu_thongbaohoantat_thuhoi_ts','ngayguithu_thongbao_xulydaugia','chiphi_khac','ngaytienve_tkkh_dotcuoi','ngaydenhan_kybill_cuoicung','death_info','contact_person','reason_die','contact_person_phone','payment_amount','payment_date','payment_person','channel','promised_person_phone','fc_name'])
   # df.to_excel(writer,sheet_name='Daily',header=['CONTRACTNR','CLIENT_NAME','BIRTH_DATE','CIF','SIGNED_DATE','PRODUCTNAME','ID NO','CREDIT AMOUNT','INSTALLMENT NUMBER','INSTALMENT AMOUNT','DATE_FIRST_DUE','DATE_LAST_DUE','CURRENT_DEBT','CURRENT_DPD','PHONE NUMBER','REFERENCE PHONE','Current_ADDRESS (if any)','District','PROVINCE','PERNAMENT_ADDRESS','District','PROVINCE','PRINCIPAL','INTEREST/ year','DPD','DATE HANDOVER','lICENSE PLATES NO','COMPANY'])

   writer = pd.ExcelWriter(fileOutput, engine='xlsxwriter')
   df.to_excel(writer,sheet_name='Sheet1',index=False,header=['Export Date','No','GROUP','Account Number','Customer name','Overdue date','Overdue amount','Outstanding balance','Outstanding principal','Staff in charge','Chief','Contacted number','Product type','Action code','Note','Tháng','Số hợp đồng','Tên Khách hàng','Địa điểm (Quận/Huyện)','Địa điểm (Tỉnh/Thành)','Người được ủy quyền','Nợ gốc Hợp đồng','Lãi Hợp đồng','Tổng cộng Hợp đồng','Tiền hàng tháng Hợp đồng','Nợ gốc Số tiền đã thanh toán','Lãi Số tiền đã thanh toán','Phạt Số tiền đã thanh toán','Tổng cộng Số tiền đã thanh toán','Nợ gốc Dư nợ khởi kiện','Lãi Dư nợ khởi kiện','Phạt Dư nợ khởi kiện','Phí tất toán Dư nợ khởi kiện','Tổng cộng Dư nợ khởi kiện','Tên cửa hàng','Địa chỉ cửa hàng','Tạm ứng án phí (dự tính)','Phương thức nộp','Ngày gởi FC','Ngày nộp đơn (Submiting date)','Ngày nộp TƯAP','Ngày nhận thông báo thụ lý','Ngày hòa giải lần 1','Ngày hòa giải lần 2','Ngày hòa giải lần 3','Ngày xét xử sơ thẩm','Kháng cáo','Ngày xét xử phúc thẩm','Theo dõi','Phương hướng giải quyết','Tình trạng khởi kiện','Ngày tạm ứng án phí','Số tiền tạm ứng','Chưa được hoàn trả án phí sau khi rút đơn','Đã được hoàn trả tiền án phí','Ngày được hoàn trả án phí','Thẩm phán','Ngày gửi HS về nhà KH','Số bill gửi HS về nhà KH','Ngày nộp đơn','Số bill nộp đơn','Số tiền hứa trả','Người hứa trả','Nguyên nhân không trả','Ngày hứa trả','Status','Ngày gửi thư thông báo định giá tài sản (nếu có)','Ngày đấu giá','Tổng số tiền còn lại chuyển về TK KH','Ngày trừ tiền để giảm dư nợ gốc sau khi xử lý tài sản','Giá bán','Ngày gửi thư thông báo hoàn tất xử lý và bán lại Tài sản thu hồi','Chi phí thẩm định giá','Ngày tiền về TK KH đợt 1','Ngày yêu cầu IT xóa các bill và giữ lại 1 kỳ bill cuối','Người thu hồi','Hình thức xử lý TS','Chi phí đấu giá','Ngày trừ tiền để thanh toán quá hạn sau khi xử lý tài sản','Số tiền kỳ bill cuối cùng','Ngày gửi thư thông báo hoàn tất thu hồi TS (nếu có)','Ngày gửi thư thông báo xử lý TS thông qua đấu giá','Chi phí khác','Ngày tiền về TK KH đợt cuối (nếu có)','Ngày đến hạn của kỳ bill cuối cùng','Giấy báo tử - Ngày cấp','Người liên hệ','Nguyên nhân chết','Số ĐT người thân','Payment amount','Payment date','Payment person','Channel','Promised person phone','FC name'])
   workbook = writer.book
   worksheet = writer.sheets['Sheet1']

   border_fmt = workbook.add_format({'bottom':1, 'top':1, 'left':1, 'right':1})
   worksheet.conditional_format(xlsxwriter.utility.xl_range(0, 0, len(df), len(df.columns)), {'type': 'no_errors', 'format': border_fmt})
   # for i, col in enumerate(df.columns):
   #    # find length of column i
   #    column_len = df[col].astype(str).str.len().max()
   #    # Setting the length if the column header is larger
   #    # than the max column value length
   #    column_len = max(column_len, len(col))
   #    # set the column length
   #    worksheet.set_column(i, i, column_len)

   writer.save()

   now_end         = datetime.now()
   log.write(now_end.strftime("%d/%m/%Y, %H:%M:%S") + ': End Log' + '\n')
   print('DONE')
except Exception as e:
   pprint(e)
   log.write(now.strftime("%d/%m/%Y, %H:%M:%S") + ': ' + str(e) + '\n')
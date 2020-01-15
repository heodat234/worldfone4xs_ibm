  <div id="page-content">
      <!-- Table Styles Header -->
      <ul class="breadcrumb breadcrumb-top">
          <li>@Report@</li>
          <li>Daily Assignment Report</li>
          <li class="pull-right none-breakcrumb" id="top-row">
              <div class="btn-group btn-group-sm">
                  <a role="button" class="btn btn-sm" onclick="saveAsExcel()"><i class="fa fa-file-excel-o"></i> <b>@Export@</b></a>
              </div>
          </li>
      </ul>
      <!-- END Table Styles Header -->
      <div class="container-fluid mvvm" style="padding-top: 20px; padding-bottom: 10px">
          <div class="row form-horizontal">
              <div class="form-group col-sm-4">
                <label class="control-label col-xs-4">@Date@</label>
                <div class="col-xs-8">
                    <input id="start-date" data-role="datepicker" data-format="dd/MM/yyyy" name="fromDateTime" data-bind="value: fromDateTime">
                </div>
              </div>
              <!-- <div class="form-group col-sm-4">
                <label class="control-label col-xs-4">@To date@</label>
                <div class="col-xs-8">
                    <input id="end-date" data-role="datepicker" data-format="dd/MM/yyyy H:mm:ss" name="toDateTime" data-bind="value: toDateTime, events: {change: endDate}">
                </div>
              </div> -->
              <div class="form-group col-sm-4 text-center">
                  <button class="k-button" data-bind="click: search">@Search@</button>
              </div>
          </div>
          <div class="row chart-page"  style="background-color: white">

              <div class="col-sm-12">
                  <div id="grid"></div>
              </div>
          </div>
          <div class="row" data-bind="visible: visibleNoData">
              <h3 class="text-center">@NO DATA@</h3>
          </div>
      </div>
      <div id="action-menu">
          <ul>

          </ul>
      </div>
      <script>
        var Table = function() {
          return {
                dataSource: {},
                grid: {},
                init: function() {
                    var dataSource = this.dataSource = new kendo.data.DataSource({
                      serverPaging: true,
                      serverFiltering: true,
                      pageSize: 10,
                      transport: {
                          read: ENV.reportApi + "loan/daily_assignment_report",
                          parameterMap: parameterMap
                      },
                      schema: {
                          data: "data",
                          total: "total",
                          model: {
                              id: "id",
                              // fields: {
                              //     overdue_date: {type: "date"},

                              // }
                          },
                          parse: function (response) {
                              response.data.map(function(doc) {
                                  if ($.isNumeric(doc.overdue_date)) {
                                    doc.overdue_date = doc.overdue_date ? new Date(doc.overdue_date * 1000) : null;
                                  }
                                  // doc.overdue_date = doc.overdue_date ? new Date(doc.overdue_date * 1000) : null;
                                  doc.created_date = doc.created_date ? new Date(doc.created_date * 1000) : null;
                                  doc.ngay_guifc = doc.ngay_guifc ? new Date(doc.ngay_guifc * 1000) : null;
                                  doc.ngay_nopdon_submitingdate = doc.ngay_nopdon_submitingdate ? new Date(doc.ngay_nopdon_submitingdate * 1000) : null;
                                  doc.ngay_nop_tuap = doc.ngay_nop_tuap ? new Date(doc.ngay_nop_tuap * 1000) : null;
                                  doc.ngay_nhanthongbao_thuly = doc.ngay_nhanthongbao_thuly ? new Date(doc.ngay_nhanthongbao_thuly * 1000) : null;
                                  doc.ngay_hoagiai1 = doc.ngay_hoagiai1 ? new Date(doc.ngay_hoagiai1 * 1000) : null;
                                  doc.ngay_hoagiai2 = doc.ngay_hoagiai2 ? new Date(doc.ngay_hoagiai2 * 1000) : null;
                                  doc.ngay_hoagiai3 = doc.ngay_hoagiai3 ? new Date(doc.ngay_hoagiai3 * 1000) : null;
                                  doc.ngay_xetxu_sotham = doc.ngay_xetxu_sotham ? new Date(doc.ngay_xetxu_sotham * 1000) : null;
                                  doc.ngay_xetxu_phuctham = doc.ngay_xetxu_phuctham ? new Date(doc.ngay_xetxu_phuctham * 1000) : null;
                                  doc.promised_date = doc.promised_date ? new Date(doc.promised_date * 1000) : null;
                                  doc.ngay_gui_thu_thong_bao = doc.ngay_gui_thu_thong_bao ? new Date(doc.ngay_gui_thu_thong_bao * 1000) : null;
                                  doc.ngay_dau_gia = doc.ngay_dau_gia ? new Date(doc.ngay_dau_gia * 1000) : null;
                                  doc.ngay_trutien_giamdunogoc = doc.ngay_trutien_giamdunogoc ? new Date(doc.ngay_trutien_giamdunogoc * 1000) : null;
                                  doc.ngay_guithuthongbao_hoantat_vaban_taisan = doc.ngay_guithuthongbao_hoantat_vaban_taisan ? new Date(doc.ngay_guithuthongbao_hoantat_vaban_taisan * 1000) : null;
                                  doc.ngaytienve_tkkh_dot1 = doc.ngaytienve_tkkh_dot1 ? new Date(doc.ngaytienve_tkkh_dot1 * 1000) : null;
                                  doc.ngayyeucau_itxoabill = doc.ngayyeucau_itxoabill ? new Date(doc.ngayyeucau_itxoabill * 1000) : null;
                                  doc.ngaytrutien_dethanhtoanquahan = doc.ngaytrutien_dethanhtoanquahan ? new Date(doc.ngaytrutien_dethanhtoanquahan * 1000) : null;
                                  doc.ngayguithu_thongbaohoantat_thuhoi_ts = doc.ngayguithu_thongbaohoantat_thuhoi_ts ? new Date(doc.ngayguithu_thongbaohoantat_thuhoi_ts * 1000) : null;
                                  doc.ngayguithu_thongbao_xulydaugia = doc.ngayguithu_thongbao_xulydaugia ? new Date(doc.ngayguithu_thongbao_xulydaugia * 1000) : null;
                                  doc.ngaytienve_tkkh_dotcuoi = doc.ngaytienve_tkkh_dotcuoi ? new Date(doc.ngaytienve_tkkh_dotcuoi * 1000) : null;
                                  doc.ngaydenhan_kybill_cuoicung = doc.ngaydenhan_kybill_cuoicung ? new Date(doc.ngaydenhan_kybill_cuoicung * 1000) : null;
                                  doc.payment_date = doc.payment_date ? new Date(doc.payment_date * 1000) : null;
                                  return doc;
                              })
                              return response;
                          },
                        }
                      });

                    var grid = this.grid = $("#grid").kendoGrid({
                      dataSource: dataSource,
                      excel: {
                          fileName: "Daily assignment report.xlsx",
                          filterable: true,
                          allPages: true,
                      },
                      // excel: {allPages: true, fileName: "Daily assignment report.xlsx"},
                      // excelExport: function(e) {
                      //    var sheet = e.workbook.sheets[0];
                      //    for (var rowIndex = 1; rowIndex < sheet.rows.length; rowIndex++) {
                      //      var row = sheet.rows[rowIndex];
                      //      for (var cellIndex = 0; cellIndex < row.cells.length; cellIndex ++) {
                      //          if(row.cells[cellIndex].value instanceof Date) {
                      //              row.cells[cellIndex].format = "dd-MM-yy hh:mm:ss"
                      //          }
                      //      }
                      //    }
                      // },
                      resizable: true,
                      pageable: {
                          refresh: true,
                          pageSizes: [5, 10, 20, 50, 100],
                          input: true,
                          messages: KENDO.pageableMessages ? KENDO.pageableMessages : {}
                      },
                      sortable: true,
                      scrollable: true,
                      columns: [
                            {
                                field: "export_date",
                                title: "Report date",
                                width: 150,
                            },{
                                field: "index",
                                title: "No",
                                width: 150,

                            },{
                                field: "group_id",
                                title: "Group",
                                width: 120,
                            },
                            {
                                field: "account_number",
                                title: "Account number",
                                width: 150,
                            },
                            {
                                field: "name",
                                title: "Customer name",
                                width: 150,
                            },
                            {
                                field: "overdue_date",
                                title: "Overdue date",
                                width: 150,
                                template: dataItem => gridDate(dataItem.overdue_date)
                            },
                            {
                                field: "loan_overdue_amount",
                                title: "Overdue amount",
                                width: 150,
                            },
                            {
                                field: "current_balance",
                                title: "Outstanding balance",
                                width: 150,
                            },
                            {
                                field: "outstanding_principal",
                                title: "Outstanding principal",
                                width: 150,
                            },
                            {
                                field: "assign",
                                title: "Staff in charge",
                                width: 150,
                            },
                            {
                                field: "chief",
                                title: "Team Lead",
                                width: 150,
                            },
                            {
                                field: "contacted",
                                title: "Contacted number",
                                width: 150,
                            },
                            {
                                field: "connected",
                                title: "Connected number",
                                width: 150,
                            },
                            {
                                field: "product_id",
                                title: "Product type",
                                width: 150,
                            },
                            {
                                field: "action_code",
                                title: "Action code",
                                width: 150,
                            },
                            {
                                field: "note",
                                title: "Note",
                                width: 150,
                            },
                            {
                              title : "Tháng",
                              width : 150,
                              field : "created_date",
                              template: dataItem => gridDate(dataItem.created_date)
                            },
                            {
                              title : "Số hợp đồng",
                              width : 150,
                              field : "so_hopdong"
                            },
                            {
                              title : "Tên Khách hàng",
                              width : 150,
                              field : "ten_khachhang"
                            },
                            {
                              title : "Địa điểm (Quận/Huyện)",
                              width : 150,
                              field : "quanhuyen"
                            },
                            {
                              title : "Địa điểm (Tỉnh/Thành)",
                              width : 150,
                              field : "tinhthanh"
                            },
                            {
                              title : "Người được ủy quyền",
                              width : 150,
                              field : "nguoiduoc_uyquyen"
                            },
                            {
                              title : "Nợ gốc Hợp đồng",
                              width : 150,
                              field : "nogoc_hopdong"
                            },
                            {
                              title : "Lãi Hợp đồng",
                              width : 150,
                              field : "lai_hopdong"
                            },
                            {
                              title : "Tổng cộng Hợp đồng",
                              width : 150,
                              field : "tongcong_hopdong"
                            },
                            {
                              title : "Tiền hàng tháng Hợp đồng",
                              width : 150,
                              field : "tienhangthang_hopdong"
                            },
                            {
                              title : "Nợ gốc Số tiền đã thanh toán",
                              width : 150,
                              field : "nogoc_sotiendathanhtoan"
                            },
                            {
                              title : "Lãi Số tiền đã thanh toán",
                              width : 150,
                              field : "lai_sotiendathanhtoan"
                            },
                            {
                              title : "Phạt Số tiền đã thanh toán",
                              width : 150,
                              field : "phat_sotiendathanhtoan"
                            },
                            {
                              width : 150,
                              field : "tongcong_dunokhoikien",
                              title : "Tổng cộng Số tiền đã thanh toán"
                            },
                            {
                              width : 150,
                              field : "nogoc_dunokhoikien",
                              title : "Nợ gốc Dư nợ khởi kiện"
                            },
                            {
                              width : 150,
                              field : "lai_dunokhoikien",
                              title : "Lãi Dư nợ khởi kiện"
                            },
                            {
                              width : 150,
                              title : "Phạt Dư nợ khởi kiện",
                              field : "phat_dunokhoikien"
                            },
                            {
                              width : 150,
                              field : "phitattoan_dunokhoikien",
                              title : "Phí tất toán Dư nợ khởi kiện"
                            },
                            {
                              width : 150,
                              title : "Tổng cộng Dư nợ khởi kiện",
                              field : "tongcong_dunokhoikien"
                            },
                            {
                              width : 150,
                              title : "Tên cửa hàng",
                              field : "ten_cuahang"
                            },
                            {
                              width : 150,
                              title : "Địa chỉ cửa hàng",
                              field : "diachi_cuahang"
                            },
                            {
                              width : 150,
                              title : "Tạm ứng án phí (dự tính)",
                              field : "tamung_anphi"
                            },
                            {
                              width : 150,
                              title : "Phương thức nộp",
                              field : "phuongthuc_nop"
                            },
                            {
                              width : 150,
                              title : "Ngày gởi FC",
                              field : "ngay_guifc",
                              template: dataItem => gridDate(dataItem.ngay_guifc)
                            },
                            {
                              width : 150,
                              title : "Ngày nộp đơn (Submiting date)",
                              field : "ngay_nopdon_submitingdate",
                              template: dataItem => gridDate(dataItem.ngay_nopdon_submitingdate)
                            },
                            {
                              width : 150,
                              title : "Ngày nộp TƯAP",
                              field : "ngay_nop_tuap",
                              template: dataItem => gridDate(dataItem.ngay_nop_tuap)
                            },
                            {
                              width : 150,
                              title : "Ngày nhận thông báo thụ lý",
                              field : "ngay_nhanthongbao_thuly",
                              template: dataItem => gridDate(dataItem.ngay_nhanthongbao_thuly)
                            },
                            {
                              width : 150,
                              title : "Ngày hòa giải lần 1",
                              field : "ngay_hoagiai1",
                              template: dataItem => gridDate(dataItem.ngay_hoagiai1)
                            },
                            {
                              width : 150,
                              title : "Ngày hòa giải lần 2",
                              field : "ngay_hoagiai2",
                              template: dataItem => gridDate(dataItem.ngay_hoagiai2)
                            },
                            {
                              title : "Ngày hòa giải lần 3",
                              width : 150,
                              field : "ngay_hoagiai3",
                              template: dataItem => gridDate(dataItem.ngay_hoagiai3)
                            },
                            {
                              title : "Ngày xét xử sơ thẩm",
                              width : 150,
                              field : "ngay_xetxu_sotham",
                              template: dataItem => gridDate(dataItem.ngay_xetxu_sotham)
                            },
                            {
                              title : "Kháng cáo",
                              width : 150,
                              field : "khangcao"
                            },
                            {
                              title : "Ngày xét xử phúc thẩm",
                              width : 150,
                              field : "ngay_xetxu_phuctham",
                              template: dataItem => gridDate(dataItem.ngay_xetxu_phuctham)
                            },
                            {
                              title : "Theo dõi",
                              width : 150,
                              field : "theo_doi"
                            },
                            {
                              field : "phuonghuong_giaiquyet",
                              title : "Phương hướng giải quyết",
                              width : 150,
                            },
                            {
                              field : "tinhtrang_khoikien",
                              title : "Tình trạng khởi kiện",
                              width : 150,
                            },
                            {
                              field : "ngay_tamung_anphi",
                              title : "Ngày tạm ứng án phí",
                              width : 150,
                            },
                            {
                              title : "Số tiền tạm ứng",
                              field : "sotien_tamung",
                              width : 150,
                            },
                            {
                              title : "Chưa được hoàn trả án phí sau khi rút đơn",
                              width : 150,
                              field : "chuaduoc_hoantra_anphi_saukhirutdon"
                            },
                            {
                              title : "Đã được hoàn trả tiền án phí",
                              width : 150,
                              field : "daduoc_hoantra_tienanphi"
                            },
                            {
                              title : "Ngày được hoàn trả án phí",
                              width : 150,
                              field : "ngay_duochoantra_anphi"
                            },
                            {
                              field : "thamphan",
                              title : "Thẩm phán",
                              width : 150,
                            },
                            {
                              title : "Ngày gửi HS về nhà KH",
                              width : 150,
                              field : "ngay_guihosovenha_kh"
                            },
                            {
                              title : "Số bill gửi HS về nhà KH",
                              width : 150,
                              field : "sobill_guihosovenha_kh"
                            },
                            {
                              title : "Ngày nộp đơn",
                              width : 150,
                              field : "ngay_nopdon"
                            },
                            {
                              title : "Số bill nộp đơn",
                              width : 150,
                              field : "sobill_nopdon"
                            },
                            {
                              title : "Số tiền hứa trả",
                              width : 150,
                              field : "promised_amount"
                            },
                            {
                              title : "Người hứa trả",
                              width : 150,
                              field : "promised_person"
                            },
                            {
                              title : "Nguyên nhân không trả",
                              width : 150,
                              field : "reason_nonpayment"
                            },
                            {
                              field : "promised_date",
                              title : "Ngày hứa trả",
                              width : 150,
                              template: dataItem => gridDate(dataItem.promised_date)
                            },
                            {
                              field : "raaStatus",
                              title : "@Status@",
                              width : 150,
                            },
                            {
                              field : "ngay_gui_thu_thong_bao",
                              title : "Ngày gửi thư thông báo định giá tài sản (nếu có)",
                              width : 150,
                              template: dataItem => gridDate(dataItem.ngay_gui_thu_thong_bao)
                            },
                            {
                              field : "ngay_dau_gia",
                              title : "Ngày đấu giá",
                              width : 150,
                              template: dataItem => gridDate(dataItem.ngay_dau_gia)
                            },
                            {
                              field : "sum_tien_con_lai_chuyen_ve_tkkh",
                              title : "Tổng số tiền còn lại chuyển về TK KH",
                              width : 150,
                            },
                            {
                              field : "ngay_trutien_giamdunogoc",
                              title : "Ngày trừ tiền để giảm dư nợ gốc sau khi xử lý tài sản",
                              width : 150,
                              template: dataItem => gridDate(dataItem.ngay_trutien_giamdunogoc)
                            },
                            {
                              field : "gia_ban",
                              title : "Giá bán",
                              width : 150,
                            },
                            {
                              title : "Ngày gửi thư thông báo hoàn tất xử lý và bán lại Tài sản thu hồi",
                              width : 150,
                              field : "ngay_guithuthongbao_hoantat_vaban_taisan",
                              template: dataItem => gridDate(dataItem.ngay_guithuthongbao_hoantat_vaban_taisan)
                            },
                            {
                              title : "Chi phí thẩm định giá",
                              width : 150,
                              field : "chiphi_thamdinhgia"
                            },
                            {
                              title : "Ngày tiền về TK KH đợt 1",
                              width : 150,
                              field : "ngaytienve_tkkh_dot1",
                              template: dataItem => gridDate(dataItem.ngaytienve_tkkh_dot1)
                            },
                            {
                              title : "Ngày yêu cầu IT xóa các bill và giữ lại 1 kỳ bill cuối",
                              width : 150,
                              field : "ngayyeucau_itxoabill",
                              template: dataItem => gridDate(dataItem.ngayyeucau_itxoabill)
                            },
                            {
                              title : "Người thu hồi",
                              width : 150,
                              field : "nguoi_thuhoi"
                            },
                            {
                              field : "hinhthuc_xuly_ts",
                              title : "Hình thức xử lý TS",
                              width : 150,
                            },
                            {
                              field : "chiphi_daugia",
                              title : "Chi phí đấu giá",
                              width : 150,
                            },
                            {
                              field : "ngaytrutien_dethanhtoanquahan",
                              title : "Ngày trừ tiền để thanh toán quá hạn sau khi xử lý tài sản",
                              width : 150,
                              template: dataItem => gridDate(dataItem.ngaytrutien_dethanhtoanquahan)
                            },
                            {
                              title : "Số tiền kỳ bill cuối cùng",
                              width : 150,
                              field : "sotien_kybill_cuoicung"
                            },
                            {
                              title : "Ngày gửi thư thông báo hoàn tất thu hồi TS (nếu có)",
                              width : 150,
                              field : "ngayguithu_thongbaohoantat_thuhoi_ts",
                              template: dataItem => gridDate(dataItem.ngayguithu_thongbaohoantat_thuhoi_ts)
                            },
                            {
                              title : "Ngày gửi thư thông báo xử lý TS thông qua đấu giá",
                              width : 150,
                              field : "ngayguithu_thongbao_xulydaugia",
                              template: dataItem => gridDate(dataItem.ngayguithu_thongbao_xulydaugia)
                            },
                            {
                              title : "Chi phí khác",
                              width : 150,
                              field : "chiphi_khac"
                            },
                            {
                              title : "Ngày tiền về TK KH đợt cuối (nếu có)",
                              width : 150,
                              field : "ngaytienve_tkkh_dotcuoi",
                              template: dataItem => gridDate(dataItem.ngaytienve_tkkh_dotcuoi)
                            },
                            {
                              field : "ngaydenhan_kybill_cuoicung",
                              title : "Ngày đến hạn của kỳ bill cuối cùng",
                              width : 150,
                              template: dataItem => gridDate(dataItem.ngaydenhan_kybill_cuoicung)
                            },
                            {
                              field : "death_info",
                              title : "Giấy báo tử - Ngày cấp",
                              width : 150
                            },
                            {
                              field : "contact_person",
                              title : "Người liên hệ",
                              width : 150
                            },
                            {
                              field : "reason_die",
                              title : "Nguyên nhân chết",
                              width : 150
                            },
                            {
                              field : "contact_person_phone",
                              title : "Số ĐT người thân",
                              width : 150
                            },
                            {
                              field : "payment_amount",
                              title : "Payment amount",
                              width : 150
                            },
                            {
                              field : "payment_date",
                              title : "Payment date",
                              width : 150,
                              template: dataItem => gridDate(dataItem.payment_date)
                            },
                            {
                              field : "payment_person",
                              title : "Payment person",
                              width : 150
                            },
                            {
                              field : "channel",
                              title : "Channel",
                              width : 150
                            },
                            {
                              field : "promised_person_phone",
                              title : "Promised person phone",
                              width : 150
                            },
                            {
                              field : "fc_name",
                              title : "FC name",
                              width : 150
                            },


                      ],
                        noRecords: {
                            template: `<h2 class='text-danger'>${KENDO.noRecords}</h2>`
                        }
                    }).data("kendoGrid");

                    grid.selectedKeyNames = function() {
                        var items = this.select(),
                            that = this,
                            checkedIds = [];
                        $.each(items, function(){
                            if(that.dataItem(this))
                                checkedIds.push(that.dataItem(this).uid);
                        })
                        return checkedIds;
                    }


                }
            }
        }();
        window.onload = function() {
          Table.init();
          var dateRange = 30;
          var nowDate = new Date();
          var date =  new Date();
          // date.setDate(nowDate.getDate() - 1);
          var timeZoneOffset = date.getTimezoneOffset() * kendo.date.MS_PER_MINUTE;
          date.setHours(- timeZoneOffset / kendo.date.MS_PER_HOUR, 0, 0 ,0);

          // var fromDate = new Date(date.getTime() + timeZoneOffset - (dateRange - 1) * 86400000);
          var fromDate = new Date(date.getTime() + timeZoneOffset);
          var toDate = new Date(date.getTime() + timeZoneOffset + kendo.date.MS_PER_DAY -1)
          var observable = kendo.observable({
              trueVar: true,
              loading: false,
              visibleReport: false,
              visibleNoData: false,
              fromDateTime: fromDate,
              toDateTime: toDate,
              filterField: "",
              fromDate: kendo.toString(fromDate, "dd/MM/yyyy H:mm"),
              toDate: kendo.toString(toDate, "dd/MM/yyyy H:mm"),

              startDate: function(e) {
                var start = e.sender,
                    startDate = start.value(),
                    end = $("#end-date").data("kendoDatePicker"),
                      endDate = end.value();

                    if (startDate) {
                        startDate = new Date(startDate);
                        startDate.setDate(startDate.getDate());
                        end.min(startDate);
                    } else if (endDate) {
                        start.max(new Date(endDate));
                    } else {
                        endDate = new Date();
                        start.max(endDate);
                        end.min(endDate);
                    }
              },
              endDate: function(e) {
                var end = e.sender,
                    endDate = end.value(),
                    start = $("#start-date").data("kendoDatePicker"),
                    startDate = start.value();

                  if (endDate) {
                      endDate = new Date(endDate);
                      endDate.setDate(endDate.getDate());
                      start.max(endDate);
                  } else if (startDate) {
                      end.min(new Date(startDate));
                  } else {
                      endDate = new Date();
                      start.max(endDate);
                      end.min(endDate);
                  }
              },
              search: function() {
                this.set("fromDate", kendo.toString(this.get("fromDateTime"), "dd/MM/yyyy H:mm"));
                this.set("toDate", kendo.toString(this.get("toDateTime"), "dd/MM/yyyy H:mm"));
                this.asyncSearch();
              },
              asyncSearch: async function() {
                var field = "createdAt";
                var fromDateTime = new Date(this.fromDateTime.getTime()).toISOString();
                var toDateTime = new Date(this.fromDateTime.getTime() + kendo.date.MS_PER_DAY -1).toISOString();

                var filter = {
                    logic: "and",
                    filters: [
                        {field: field, operator: "gte", value: fromDateTime},
                        {field: field, operator: "lte", value: toDateTime}
                    ]
                };

                Table.dataSource.filter(filter);

              },
          })
          kendo.bind($(".mvvm"), observable);
        };


      </script>
      <script>
          function saveAsExcel() {
              $.ajax({
                url: ENV.reportApi + "loan/daily_assignment_report/downloadExcel",
                type: 'POST',
                dataType: 'json',
                data: {date: $('#start-date').val()},
                timeout: 30000
              })
              .done(function(response) {
                if (response.status == 1) {
                  window.location = response.data
                }
              })
              .fail(function() {
                console.log("error");
              });

          }
      </script>
  </div>


  <script id="detail-dropdown-template" type="text/x-kendo-template">
    <li data-bind="css: {dropdown-header: active}"><a data-bind="click: goTo, text: name, attr: {href: url}"></a></li>
  </script>
  <script type="text/x-kendo-template" id="diallist-detail-field-template">
    <div class="item">
          <span style="margin-left: 10px" data-bind="text: title"></span>
          <i class="fa fa-arrow-circle-o-right text-success" style="float: right; margin-top: 10px"></i>
      </div>
  </script>
  <script type="text/x-kendo-template" id="data-field-template">
    <div class="item">
        <span class="handler text-center"><i class="fa fa-arrows-v"></i></span>
          <span data-bind="text: field"></span>
      </div>
  </script>

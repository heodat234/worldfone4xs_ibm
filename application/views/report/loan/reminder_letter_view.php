<div id="page-content">
    <!-- Table Styles Header -->
    <ul class="breadcrumb breadcrumb-top">
        <li>@Report@</li>
        <li>Reminder Letter Report</li>
        <li class="pull-right none-breakcrumb" id="top-row">
            <div class="btn-group btn-group-sm">
                <a role="button" class="btn btn-sm" onclick="Table.grid.saveAsExcel()"><i class="fa fa-file-excel-o"></i> <b>@Export@</b></a>
            </div>
        </li>
    </ul>
    <!-- END Table Styles Header -->
    <div class="container-fluid mvvm" style="padding-top: 20px; padding-bottom: 10px">
        <div class="row form-horizontal">
            <div class="form-group col-sm-4">
               <label class="control-label col-xs-4">@Date@</label>
               <div class="col-xs-8">
                  <input id="start-date" data-role="datepicker" data-format="dd/MM/yyyy" name="fromDateTime" data-bind="value: fromDateTime" disabled="">
               </div>
            </div>
            <!-- <div class="form-group col-sm-4">
               <label class="control-label col-xs-4">@To date@</label>
               <div class="col-xs-8">
                  <input id="end-date" data-role="datepicker" data-format="dd/MM/yyyy H:mm:ss" name="toDateTime" data-bind="value: toDateTime, events: {change: endDate}">
               </div>
            </div>
            <div class="form-group col-sm-4 text-center">
                <button class="k-button" data-bind="click: search">@Search@</button>
            </div> -->
        </div>
        <div class="row chart-page"  style="background-color: white">

            <div class="col-sm-12">
                <div id="grid"></div>
            </div>
        </div>
        
    </div>
    
    <script>
      function girdBoolean(data) {
        return '<input type="checkbox"'+ ( data ? 'checked="checked"' : "" )+ 'class="chkbx" disabled />';
      }
      var Config = {
          crudApi: `${ENV.reportApi}`,
          templateApi: `${ENV.templateApi}`,
          collection: "reminder_letter_report",
          observable: {
              
          },
          model: {
              id: "id",
              fields: {
                  
              }
          },
          parse: function (response) {
              response.data.map(function(doc) {
                  doc.createdAt = doc.createdAt ? new Date(doc.createdAt * 1000) : undefined;
                  return doc;
              })
              return response;
          },
          columns: [{
              field: 'index',
              title: "No",
              width: 70,
          }, {
              field: 'account_number',
              title: "Account number",
              width: 150,
          }, {
              field: 'name',
              title: "Customer name",
              width: 160,
          },{
              field: 'address',
              title: "Customer address",
              width: 200,
          },{
              field: 'contract_date',
              title: "Signed contract date",
              width: 120,
          },{
              field: 'day',
              title: "Day",
              width: 80,
          },{
              field: 'month',
              title: "Month",
              width: 80,
          },{
              field: 'year',
              title: "Year",
              width: 80,
          },{
              field: 'approved_amt',
              title: "Approved amount",
              width: 150,
          },{
              field: 'cur_bal',
              title: "Current balance",
              width: 150,
          },{
              field: 'overdue_amt',
              title: "Overdue amount",
              width: 150,
          },{
              field: 'phone',
              title: "Phone number",
              width: 150,
          },{
              field: 'createdAt',
              title: "Sending date (dd/mm/yyyy)",
              width: 150,
              template: dataItem => gridTimestamp(dataItem.createdAt,"dd/MM/yyyy"),
          },{
              field: 'due_date',
              title: "Due date",
              width: 150,
          },{
              field: 'overdue_date',
              title: "Overdue date",
              width: 150,
          },{
              field: 'group',
              title: "Group",
              width: 150,
          },{
              field: 'product_code',
              title: "Product code",
              width: 150,
          },{
              field: 'outstanding_bal',
              title: "Outstanding balance",
              width: 150,
          },{
              field: 'pic',
              title: "PIC",
              width: 150,
          },{
              field: 'product_name',
              title: "Product name",
              width: 150,
          },{
              field: 'dealer_name',
              title: "Dealer name",
              width: 150,
          },{
              field: 'brand',
              title: "Brand",
              width: 150,
          },{
              field: 'model',
              title: "Kiểu xe",
              width: 150,
          },{
              field: 'engine_no',
              title: "Số máy",
              width: 150,
          },{
              field: 'chassis_no',
              title: "Số khung",
              width: 100,
          }, {
              field: 'color',
              title: "Màu xe",
              width: 100,
          },  {
              field: 'license_plates',
              title: "license plates",
              width: 100,
          }, {
              field: 'production_time',
              title: "Năm sản xuất",
              width: 100,
              // template: dataItem => gridTimestamp(dataItem.createdAt,"dd/MM/yyyy"),
          }],
          filterable: KENDO.filterable
      };
      var Table = function() {
         return {
              dataSource: {},
              grid: {},
              init: function() {
                  var dataSource = this.dataSource = new kendo.data.DataSource({
                     serverPaging: true,
                     serverFiltering: true,
                     serverSorting: true,
                     pageSize: 10,
                     sort: { field: "index", dir: "asc" },
                     transport: {
                        read: {
                            url: Config.crudApi + 'loan/' + Config.collection + '/read'
                        },
                        parameterMap: parameterMap
                     },
                     schema: {
                        data: "data",
                        total: "total",
                        
                     }
                  });

                  var grid = this.grid = $("#grid").kendoGrid({
                     dataSource: dataSource,
                     excel: {fileName: "Reminder Letter Export.xlsx",allPages: true},
                     excelExport: function(e) {
                        var sheet = e.workbook.sheets[0];
                        for (var rowIndex = 1; rowIndex < sheet.rows.length; rowIndex++) {
                          var row = sheet.rows[rowIndex];
                          for (var cellIndex = 0; cellIndex < row.cells.length; cellIndex ++) {
                              if (cellIndex == 12) {
                                row.cells[cellIndex].value = gridTimestamp(row.cells[12].value,"dd/MM/yyyy");
                              }
                              if(row.cells[cellIndex].value instanceof Date) {
                                  row.cells[cellIndex].format = "dd-MM-yy hh:mm:ss"
                              }
                          }
                        }
                     },
                     resizable: true,
                     pageable: true,
                     sortable: true,
                     scrollable: true,
                     columns: Config.columns,
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
         var date =  new Date(),
               timeZoneOffset = date.getTimezoneOffset() * kendo.date.MS_PER_MINUTE;
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
               var field = "created_at";
               var fromDateTime = new Date(this.fromDateTime.getTime() - timeZoneOffset).toISOString();
                var toDateTime = new Date(this.toDateTime.getTime() - timeZoneOffset).toISOString();

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
              url: Config.crudApi + 'loan/' + Config.collection+ "/exportExcel",
              type: 'POST',
              dataType: 'json',
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

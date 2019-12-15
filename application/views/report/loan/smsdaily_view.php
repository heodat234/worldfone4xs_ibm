<div id="page-content">
    <!-- Table Styles Header -->
    <ul class="breadcrumb breadcrumb-top">
        <li>@Report@</li>
        <li>SMS Daily SMS Report</li>
        <li class="pull-right none-breakcrumb" id="top-row">
            <div class="btn-group btn-group-sm">
                <a role="button" class="btn btn-sm" onclick="Table.grid.saveAsExcel()"><i class="fa fa-file-excel-o"></i> <b>@Export@ SIBS</b></a>
                <a role="button" class="btn btn-sm" onclick="Table_1.grid.saveAsExcel()"><i class="fa fa-file-excel-o"></i> <b>@Export@ Card</b></a>
            </div>
        </li>
    </ul>
    <!-- END Table Styles Header -->
    <div class="container-fluid mvvm" style="padding-top: 20px; padding-bottom: 10px">
        <div class="row form-horizontal">
            <div class="form-group col-sm-4">
               <label class="control-label col-xs-4">@Date@</label>
               <div class="col-xs-8">
                  <input id="start-date" data-role="datepicker" data-format="dd/MM/yyyy H:mm:ss" name="fromDateTime" data-bind="value: fromDateTime, events: {change: startDate}" disabled="">
               </div>
            </div>
        </div>
        <div class="row chart-page"  style="background-color: white">
            <div data-role="tabstrip">
                <ul>
                    <li class="k-state-active">
                        SMS SIBS
                    </li>
                    <li>
                        SMS CARD
                    </li>
                </ul>
                <div>
                  <div class="container-fluid">
                    <div class="col-sm-12">
                        <div id="grid"></div>
                    </div>
                  </div>
                </div>
                <div>
                    <div class="container-fluid">
                      <div class="col-sm-12">
                        <div id="grid_1"></div>
                      </div>
                    </div>
                </div>
              </div>
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
              columns: [],
              init: function() {
                  var dataSource = this.dataSource = new kendo.data.DataSource({
                     serverPaging: true,
                     serverFiltering: true,
                     pageSize: 5,
                     transport: {
                        read: ENV.reportApi + "loan/smsdaily_report/sibs",
                        parameterMap: parameterMap
                     },
                     schema: {
                        data: "data",
                        total: "total",
                     }
                  });

                  var grid = this.grid = $("#grid").kendoGrid({
                     dataSource: dataSource,
                     excel: {allPages: true},
                     excelExport: function(e) {
                        var sheet = e.workbook.sheets[0];
                        for (var rowIndex = 1; rowIndex < sheet.rows.length; rowIndex++) {
                          var row = sheet.rows[rowIndex];
                          for (var cellIndex = 0; cellIndex < row.cells.length; cellIndex ++) {
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
                     columns: this.columns,
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
        var Table_1 = function() {
            return {
              dataSource: {},
              grid: {},
              columns: [],
              init: function() {
                  var dataSource = this.dataSource = new kendo.data.DataSource({
                     serverPaging: true,
                     serverFiltering: true,
                     pageSize: 5,
                     transport: {
                        read: ENV.reportApi + "loan/smsdaily_report/card",
                        parameterMap: parameterMap
                     },
                     schema: {
                        data: "data",
                        total: "total",
                     }
                  });

                  var grid = this.grid = $("#grid_1").kendoGrid({
                     dataSource: dataSource,
                     excel: {allPages: true},
                     excelExport: function(e) {
                        var sheet = e.workbook.sheets[0];
                        for (var rowIndex = 1; rowIndex < sheet.rows.length; rowIndex++) {
                          var row = sheet.rows[rowIndex];
                          for (var cellIndex = 0; cellIndex < row.cells.length; cellIndex ++) {
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
                     columns: this.columns,
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
            var sibsFields = new kendo.data.DataSource({
                serverPaging: true,
                serverFiltering: true,
                serverSorting: true,
                transport: {
                    read: {
                      url: `${ENV.vApi}model/read`,
                      data:  {
                          skip: 0,
                          take: 50
                      }
                    },
                    parameterMap: parameterMap
                },
                schema: {
                    data: "data",
                    parse: function(response) {
                      response.data = response.data.filter(function(doc) {
                        if(doc.sub_type)
                          doc.subType = JSON.parse(doc.sub_type);
                        else doc.subType = {};
                        return doc.subType.sibs;               
                      })
                      return response;
                    }

                },
                filter: {
                    field: "collection",
                    operator: "eq",
                    value: (ENV.type ? ENV.type + "_" : "") + "Sms_daily_report"
                },
                page: 1,
                sort: {field: "index", dir: "asc"}
              })
            sibsFields.read().then(function(){
                  var columns = sibsFields.data().toJSON();
                  columns.map(col => {
                     col.width = 120;
                      switch (col.type) {
                          case "name":
                              col.template = (dataItem) => gridName(dataItem[col.field]);
                              break;
                          // case "phone": case "arrayPhone":
                          //     col.template = (dataItem) => gridPhone(dataItem[col.field]);
                          //     break;
                          case "array":
                              col.template = (dataItem) => gridArray(dataItem[col.field]);
                              break;
                          case "timestamp":
                              col.template = (dataItem) => gridDate(dataItem[col.field] != '' ? new Date(dataItem[col.field] * 1000) : null);
                              break;
                          default:
                              break;
                      }
                  });
                  
                  Table.columns = columns;
                  Table.init();
            });

            var cardFields = new kendo.data.DataSource({
                serverPaging: true,
                serverFiltering: true,
                serverSorting: true,
                transport: {
                    read: {
                      url: `${ENV.vApi}model/read`,
                      data:  {
                          skip: 0,
                          take: 50
                      }
                    },
                    parameterMap: parameterMap
                },
                schema: {
                    data: "data",
                    parse: function(response) {
                      response.data = response.data.filter(function(doc) {
                        if(doc.sub_type)
                          doc.subType = JSON.parse(doc.sub_type);
                        else doc.subType = {};
                        return doc.subType.card;
                      })
                      return response;
                    }

                },
                filter: {
                    field: "collection",
                    operator: "eq",
                    value: (ENV.type ? ENV.type + "_" : "") + "Sms_daily_report"
                },
                page: 1,
                sort: {field: "index", dir: "asc"}
            })
            cardFields.read().then(function(){
                var columns = cardFields.data().toJSON();
                columns.map(col => {
                    col.width = 120;
                    switch (col.type) {
                      case "name":
                          col.template = (dataItem) => gridName(dataItem[col.field]);
                          break;
                      // case "phone": case "arrayPhone":
                      //     col.template = (dataItem) => gridPhone(dataItem[col.field]);
                      //     break;
                      case "array":
                          col.template = (dataItem) => gridArray(dataItem[col.field]);
                          break;
                      case "timestamp":
                          col.template = (dataItem) => gridDate(dataItem[col.field] != '' ? new Date(dataItem[col.field] * 1000) : null);
                          break;
                      default:
                          break;
                  }
              });
              
              Table_1.columns = columns;
              Table_1.init();
            });
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

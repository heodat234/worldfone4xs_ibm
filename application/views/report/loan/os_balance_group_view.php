<div id="page-content">
    <!-- Table Styles Header -->
    <ul class="breadcrumb breadcrumb-top">
        <li>@Report@</li>
        <li>Daily Report Of OS Balance Of Group BCDE</li>
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
               <label class="control-label col-xs-4">Month</label>
               <div class="col-xs-8">
                  <input id="start-date" data-role="datepicker" data-format="dd/MM/yyyy" name="fromDateTime" data-bind="value: fromDateTime" disabled="">
               </div>
            </div>
        </div>
        <div class="row chart-page"  style="background-color: white">
            <div data-role="tabstrip">
                <ul>
                    <li class="k-state-active">
                        SIBS
                    </li>
                    <li>
                        CARD
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
      function girdBoolean(data) {
        return '<input type="checkbox"'+ ( data ? 'checked="checked"' : "" )+ 'class="chkbx" disabled />';
      }
      var Config = {
          crudApi: `${ENV.reportApi}`,
          templateApi: `${ENV.templateApi}`,
          collection: "daily_os_balance_group_report",
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
          columns: [
            {
              field: 'type',
              title: "Type",
              width: 80,
              
            },{
                field: 'debt_group',
                title: "Group",
                width: 150,
            },  {
                title: "Ngày",
                width: 100,
                template: dataItem => gridTimestamp(dataItem.createdAt,"dd/MM/yyyy"),
            },{
              title: 'START',
              columns: [{
                  field: 'start_os_bl',
                  title: 'OS BL',
                  width: 120
              }, {
                  field: 'start_no',
                  title: 'No.',
                  width: 120
              }]
            },{
              title: 'TARGET OF COLLECTION',
              columns: [{
                  field: 'target_of_col_os_bl',
                  title: 'OS BL',
                  width: 120
              }, {
                  field: 'target_of_col_no',
                  title: 'No.',
                  width: 120
              }]
            },{
              title: 'DAILY',
              columns: [{
                  field: 'daily_os_bl',
                  title: 'OS BL',
                  width: 120
              }, {
                  field: 'daily_no',
                  title: 'No.',
                  width: 120
              }]
            }
          ],
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
                     pageSize: 10,

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
      var Table_1 = function() {
         return {
              dataSource: {},
              grid: {},
              init: function() {
                  var dataSource = this.dataSource = new kendo.data.DataSource({
                     serverPaging: true,
                     serverFiltering: true,
                     pageSize: 10,

                     transport: {
                        read: {
                            url: Config.crudApi + 'loan/' + Config.collection + '/readCard'
                        },
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
                     columns: Config.columns,
                      noRecords: {
                          template: `<h2 class='text-danger'>${KENDO.noRecords}</h2>`
                      }
                  }).data("kendoGrid");

                 
              }
          }
      }();
      window.onload = function() {
         Table.init();
         Table_1.init();
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

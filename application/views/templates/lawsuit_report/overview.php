<div id="page-content">
    <!-- END Table Styles Header -->
    <div class="container-fluid mvvm" style="padding-top: 20px; padding-bottom: 10px">
        <div class="row form-horizontal">
            <div class="form-group col-sm-4">
               <label class="control-label col-xs-4">@From date@</label>
               <div class="col-xs-8">
                  <input id="start-date" data-role="datepicker" data-format="dd/MM/yyyy H:mm:ss" name="fromDateTime" data-bind="value: fromDateTime">
               </div>
            </div>
            <div class="form-group col-sm-4">
               <label class="control-label col-xs-4">@To date@</label>
               <div class="col-xs-8">
                  <input id="end-date" data-role="datepicker" data-format="dd/MM/yyyy H:mm:ss" name="toDateTime" data-bind="value: toDateTime">
               </div>
            </div>
            <div class="form-group col-sm-4">
              <div class="form-group col-sm-11 text-center ">
                <button class="k-button" data-bind="click: search">@Search@</button>
              </div>
              <div class="form-group col-sm-1 text-right ">
                <div class="btn-group btn-group-sm">
                    <a role="button" class="btn btn-sm" onclick="saveAsExcel()"><i class="fa fa-file-excel-o"></i> <b>@Export@</b></a>
                </div>
              </div>
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
                        read: ENV.reportApi + "loan/lawsuit_report",
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
      var lawsuitFields = new kendo.data.DataSource({
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
                  return doc.subType.import;
                })
                return response;
              }
             
          },
          filter: {
              field: "collection",
              operator: "eq",
              value: (ENV.type ? ENV.type + "_" : "") + "Lawsuit"
          },
          page: 1,
          sort: {field: "index", dir: "asc"}
      })
      lawsuitFields.read().then(function(){
          var columns = lawsuitFields.data().toJSON();
          columns.map(col => {
              if (col.field == 'lawsuit_status') {
                col.width = 400;
              }else{
                col.width = 120;
              }
              switch (col.type) {
                  case "name":
                      col.template = (dataItem) => gridName(dataItem[col.field]);
                      break;
                  case "phone": case "arrayPhone":
                      col.template = (dataItem) => gridPhone(dataItem[col.field]);
                      break;
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
          columns.unshift({
              selectable: true,
              width: 32,
              locked: true
          });

          Table.columns = columns;
          Table.init();
      });
         
      function saveAsExcel() {
        var startDate = $('#start-date').val();
        var endDate = $('#end-date').val();
        $.ajax({
          url: ENV.reportApi + "loan/lawsuit_report/saveAsExcel",
          type: 'POST',
          dataType: 'json',
          data: {startDate: startDate, endDate: endDate},
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

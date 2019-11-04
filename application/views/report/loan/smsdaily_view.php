<div id="page-content">
    <!-- Table Styles Header -->
    <ul class="breadcrumb breadcrumb-top">
        <li>@Report@</li>
        <li>@Lawsuit Report@</li>
        <li class="pull-right none-breakcrumb" id="top-row">
            <div class="btn-group btn-group-sm">
                <!-- <button href="#/" class="btn btn-alt btn-default active" >@Overview@</button> -->
                <a role="button" class="btn btn-sm" onclick="saveAsExcel()"><i class="fa fa-file-excel-o"></i> <b>@Export@</b></a>
            </div>
        </li>
    </ul>
    <!-- END Table Styles Header -->
    <div class="container-fluid mvvm" style="padding-top: 20px; padding-bottom: 10px">
        <div class="row form-horizontal">
            <div class="form-group col-sm-4">
               <label class="control-label col-xs-4">Date</label>
               <div class="col-xs-8">
                  <input id="start-date" data-role="datepicker" data-format="dd/MM/yyyy" name="fromDateTime" disabled="" data-bind="value: fromDateTime, events: {change: startDate}">
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
                     pageSize: 20,
                     transport: {
                        read: ENV.reportApi + "loan/smsdaily_report",
                        parameterMap: parameterMap
                     },
                     schema: {
                        data: "data",
                        total: "total",
                        parse: function (response) {
                           i = 1;
                           response.data.map(function(doc) {
                              doc.date = new Date().toLocaleDateString();
                              doc.stt = i;
                              i = i + 1;
                                 return doc;
                           })
                             return response;
                        },
                     }
                  });

                  var grid = this.grid = $("#grid").kendoGrid({
                     dataSource: dataSource,
                     zable: true,
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
                    return doc.subType.detailShow;
                  })
                  return response;
                }

            },
            filter: {
                field: "collection",
                operator: "eq",
                value: (ENV.type ? ENV.type + "_" : "") + "LNJC05"
            },
            page: 1,
            sort: {field: "index", dir: "asc"}
        })
        lawsuitFields.read().then(function(){
            var columns = lawsuitFields.data().toJSON();
            columns.map(col => {
               col.width = 150;
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
               field: "stt",
               title: "No",
               width: 50
            });
            columns.push({
               field: "date",
               title: "SENDING DATE",
               width: 150
            });

            Table.columns = columns;
            Table.init();
            // Table.grid.bind("change", grid_change);
        });
         var dateRange = 30;
         var nowDate = new Date();
         var date =  new Date(),
               timeZoneOffset = date.getTimezoneOffset() * kendo.date.MS_PER_MINUTE;
               date.setHours(- timeZoneOffset / kendo.date.MS_PER_HOUR, 0, 0 ,0);
         var fromDate = new Date(date.getTime() + timeZoneOffset );
         var observable = kendo.observable({
            fromDateTime: fromDate,
            fromDate: kendo.toString(fromDate, "dd/MM/yyyy H:mm"),
         })
         kendo.bind($(".mvvm"), observable);
      };

      function saveAsExcel() {
        $.ajax({
          url: ENV.reportApi + "loan/smsdaily_report/saveAsExcel",
          type: 'POST',
          dataType: 'json',
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

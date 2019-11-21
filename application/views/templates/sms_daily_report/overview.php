<div id="page-content">
    <!-- END Table Styles Header -->
    <div class="container-fluid mvvm" style="padding-top: 20px; padding-bottom: 10px">
        <div class="row form-horizontal">
            <div class="form-group col-sm-3">
               <label class="control-label col-xs-4">Date</label>
               <div class="col-xs-8">
                  <input id="start-date" data-role="datepicker" data-format="dd/MM/yyyy" name="fromDateTime" disabled="" data-bind="value: fromDateTime">
               </div>
            </div>
            <div class="form-group col-sm-9 text-right ">
              <div class="btn-group btn-group-sm">
                  <a role="button" class="btn btn-sm" onclick="saveAsExcel()"><i class="fa fa-file-excel-o"></i> <b>@Export@</b></a>
              </div>
            </div>
        </div>
        <div class="row chart-page"  >
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
              grid_name: '',
              source_name: '',
              init: function() {
                  var dataSource = this.dataSource = new kendo.data.DataSource({
                     serverPaging: true,
                     serverFiltering: true,
                     pageSize: 20,
                     transport: {
                        read: ENV.reportApi + "loan/smsdaily_report/" + this.source_name,
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

                  var grid = this.grid = $("#"+this.grid_name).kendoGrid({
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
      // window.onload = function() {
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
        sibsFields.read().then(function(){
            var columns = sibsFields.data().toJSON();
            columns.map(col => {
               col.width = 150;
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
            Table.grid_name = 'grid';
            Table.source_name = 'sibs';
            Table.columns = columns;
            Table.init();
            // Table.grid.bind("change", grid_change);
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
                    return doc.subType.smsreport;
                  })
                  return response;
                }

            },
            filter: {
                field: "collection",
                operator: "eq",
                value: (ENV.type ? ENV.type + "_" : "") + "Account"
            },
            page: 1,
            sort: {field: "index", dir: "asc"}
        })
        cardFields.read().then(function(){
            var columns = cardFields.data().toJSON();
            columns.map(col => {
               col.width = 150;
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
            columns.unshift({
               field: "group",
               title: "GROUP",
               width: 100
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
            Table.grid_name = 'grid_1';
            Table.source_name = 'card';
            Table.columns = columns;
            Table.init();
            // Table.grid.bind("change", grid_change);
        });
         
      // };

      function saveAsExcel() {
        $.ajax({
          url: ENV.reportApi + "loan/smsdaily_report/saveAsExcel",
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

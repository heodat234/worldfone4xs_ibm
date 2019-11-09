<div id="page-content">
    <!-- END Table Styles Header -->
    <div class="container-fluid " style="padding-top: 20px; padding-bottom: 10px">
        <div class="row form-horizontal mvvm-date">
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

        <h3 class="col-sm-12 text-center" style="margin-bottom: 20px;color: #27ae60">DAILY PAYMENT REPORT</h3>
        <div class="row chart-page" >
          <div class="col-sm-12">
              <div id="grid"></div>
          </div>
        </div>
    </div>
    <script>
      var Config = Object.assign(Config, {
          grid_name: 'grid',
          source_name: 'index',
          columns: [
              {
                  field: "stt",
                  title: "No",
                  width: 50
              },{
                  field: "account",
                  title: "ACCOUNT NUMBER",
                  width: 100
              },{
                  field: "name",
                  title: "NAME",
                  width: 130
              },{
                  field: "due_date",
                  title: "OVERDUE DATE",
                  // template: dataItem => gridTimestamp(dataItem.due_date),
                  width: 130
              },{
                  field: "payment_date",
                  title: "PAYMENT DATE",
                  // template: dataItem => gridTimestamp(dataItem.payment_date),
                  width: 130
              },{
                  field: "amt",
                  title: "AMOUNT",
                  width: 130
              },{
                  field: "paid_principal",
                  title: "PAID PRINCIPAL",
                  width: 130
              },{
                  field: "paid_interest",
                  title: "PAID INTEREST",
                  width: 130
              },{
                  field: "RPY_FEE",
                  title: "PAID LATE CHARGE & FEE",
                  width: 130
              },{
                  field: "group",
                  title: "GROUP",
                  width: 130
              },{
                  field: "num_of_overdue_day",
                  title: "NUMBER OF OVERDUE DAYS",
                  width: 130
              },{
                  field: "pic",
                  title: "PIC",
                  width: 130
              },{
                  field: "product_name",
                  title: "PRODUCT",
                  width: 130
              },{
                  field: "note",
                  title: "NOTE",
                  width: 130
              }
          ]
      });
    </script>
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
                     pageSize: 10,
                     transport: {
                        read: ENV.reportApi + "loan/daily_payment_report/" + this.source_name,
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

      Table.grid_name = Config.grid_name;
      Table.source_name = Config.source_name;
      Table.columns = Config.columns;
      Table.init();

      function saveAsExcel() {
        $.ajax({
          url: ENV.reportApi + "loan/daily_payment_report/downloadExcel",
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

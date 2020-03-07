  <div id="page-content">
      <!-- Table Styles Header -->
      <ul class="breadcrumb breadcrumb-top">
          <li>@Report@</li>
          <li>Write Of Expectation Report</li>
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
                    <input id="start-date" data-role="datepicker" data-format="MM/yyyy" data-start="year" data-depth="year" name="fromDateTime" data-bind="value: fromDateTime, events: {change: onChangeDate}">
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
                fromDate: 0,
                toDate: 0,
                init: function() {
                    var dataSource = this.dataSource = new kendo.data.DataSource({
                      serverPaging: true,
                      serverFiltering: true,
                      pageSize: 10,
                      filter: {
                          logic: "and",
                          filters: [
                              {field: 'createdAt', operator: "gte", value: this.fromDate},
                              {field: 'createdAt', operator: "lte", value: this.toDate}
                          ]
                      },
                      transport: {
                          read: ENV.reportApi + "loan/write_of_expectation/write_of",
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
                                  doc.Due_date = doc.Due_date ? new Date(doc.Due_date * 1000) : undefined;
                                  doc.Release_amount = parseFloat('0'+doc.Release_amount)
                                  doc.Off_balance = parseFloat('0'+doc.Off_balance)
                                  doc.Current_balance = parseInt(doc.Current_balance)
                                  doc.Interest_rate = parseFloat('0'+doc.Interest_rate)
                                  return doc;
                              })
                              return response;
                          },
                        }
                      });

                    var grid = this.grid = $("#grid").kendoGrid({
                      dataSource: dataSource,
                      excel: {
                          fileName: "Write Off expectation report.xlsx",
                          filterable: true,
                          allPages: true,
                      },
                      excelExport: function(e) {
                        var sheet = e.workbook.sheets[0];
                        for (var rowIndex = 1; rowIndex < sheet.rows.length; rowIndex++) {
                          var row = sheet.rows[rowIndex];
                          for (var cellIndex = 0; cellIndex < row.cells.length; cellIndex ++) {
                            //whatever you want to do with cells
                          }
                          row.cells[5].format = "#,##0";
                          row.cells[6].format = "0%";
                          row.cells[8].format = "#,##0";
                          row.cells[29].format = "#,##0";
                        }
                      },
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
                              field: "Group",
                              title: "Group",
                              width: 80
                          },
                          {
                              field: "Account_number",
                              title: "AC NUMBER",
                              width: 140
                          },{
                              field: "Name",
                              title: "Name",
                              width: 130
                          },
                          {
                              field: "Due_date",
                              title: "Due date",
                              template: data => gridDate(data.Due_date, 'dd/MM/yyyy'),
                              width: 130
                          },
                          {
                              field: "Release_date",
                              title: "Release date",
                            
                              width: 130
                          },
                          {
                              field: "Release_amount",
                              title: "Release amount",
                              width: 130
                          },
                          {
                              field: "Interest_rate",
                              title: "Interest rate",
                              width: 130
                          },
                          {
                              field: "Loan_Term",
                              title: "Loan Term",
                              width: 130
                          },
                          {
                              field: "Off_balance",
                              title: "Off balance Principal amount as of dated",
                              width: 130
                          },
                          {
                              field: "Actual_payment",
                              title: "Actual payment (term)",
                              width: 130
                          },
                          {
                              field:"Profession",
                              title:"Profession",
                              width: 130
                          },
                          {
                              field:"MRC",
                              title:"MRC",
                              width: 130
                          },
                          {
                              field:"Reason_of_uncollected",
                              title:"Reason of uncollected",
                              width: 130
                          },
                          {
                              field:"If_bike_is_defined",
                              title:"If bike is defined",
                              width: 130
                          },
                          {
                              field:"If_site_visit_made",
                              title:"If site visit made",
                              width: 130
                          },
                          {
                              field:"If_there_is_fielder_in_location",
                              title:"If there is fielder in location",
                              width: 130
                          },
                          {
                              field:"Last_date_made_field_visit",
                              title:"Last date made field visit",
                              width: 130
                          },
                          {
                              field:"No_of_site_visit_made",
                              title:"No. of site visit made",
                              width: 130
                          },
                          {
                              field:"Last_date_made_collections_call",
                              title:"Last date made collections call",
                              width: 130
                          },
                          {
                              field:"If_still_collectable",
                              title:"If still collectable",
                              width: 130
                          },
                          {
                              field:"SMS_sent",
                              title:"SMS sent",
                              width: 130
                          },
                          {
                              field:"Call",
                              title:"Call",
                              width: 130
                          },
                          {
                              field:"Send_reminder_letter",
                              title:"Send reminder letter",
                              width: 130
                          },
                          {
                              field:"Litigation",
                              title:"Litigation",
                              width: 130
                          },
                          {
                              field:"Note",
                              title:"Note",
                              width: 130
                          },
                          {
                              field:"Cus_ID",
                              title:"Customer's ID",
                              width: 130
                          },
                          {
                              field:"Product_code",
                              title:"Product code",
                              width: 130
                          },
                          {
                              field:"Partner_name_company",
                              title:"Tên cty partner",
                              width: 130
                          },
                          {
                              field:"Phone",
                              title:"Mobile phone",
                              width: 130
                          },
                          {
                              field:"Current_balance",
                              title:"Outstanding balance",
                              width: 130
                          },
                          {
                              field:"Dealer_code",
                              title:"Dealer code",
                              width: 130
                          },
                          {
                              field:"Dealer_name",
                              title:"Dealer name",
                              width: 130
                          }
                         

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
          $("#start-date").kendoDatePicker({
            // disableDates: function (date) {
            //     var disabled = [6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27];
            //     if (date && disabled.indexOf(date.getDate()) > -1 ) {
            //         return true;
            //     } else {
            //         return false;
            //     }
            // }
        });
          
          var dateRange = 30;
          var nowDate = new Date();
          var date =  new Date();
          var timeZoneOffset = date.getTimezoneOffset() * kendo.date.MS_PER_MINUTE;
          date.setHours(- timeZoneOffset / kendo.date.MS_PER_HOUR, 0, 0 ,0);

          var fromDate = new Date(date.getTime() + timeZoneOffset);

          fromDate.setDate(1);
          Table.fromDate = fromDate.getTime() / 1000;
          var lastDateOfMonth = kendo.date.lastDayOfMonth(fromDate);
          Table.toDate = lastDateOfMonth.getTime() / 1000;
          Table.init();

          var observable = kendo.observable({
              trueVar: true,
              loading: false,
              visibleReport: false,
              visibleNoData: false,
              fromDateTime: fromDate,
              filterField: "",
              fromDate: kendo.toString(fromDate, "dd/MM/yyyy H:mm"),

              onChangeDate: function() {
                  var date =  this.fromDateTime;
                  date.setDate(1);
                  var fromDate = date.getTime() / 1000;
                  var lastDateOfMonth = kendo.date.lastDayOfMonth(this.fromDateTime);
                  var toDate = lastDateOfMonth.getTime() / 1000;
                  var filter = [{
                          field: 'createdAt',
                          operator: 'gte',
                          value: fromDate
                      }, {
                          field: 'createdAt',
                          operator: 'lte',
                          value: toDate
                      }];
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
                data: { start : $("#start-date").val()},
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

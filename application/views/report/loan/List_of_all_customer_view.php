<div id="page-content">
    <!-- Table Styles Header -->
    <ul class="breadcrumb breadcrumb-top">
        <li>@Report@</li>
        <li>List of all customer by Loan Group</li>
    
        <li class="pull-right none-breakcrumb" id="top-row">
            <div class="btn-group btn-group-sm">
                <a role="button" class="btn btn-sm" onclick="saveAsExcel()"><i class="fa fa-file-excel-o"></i> <b>@Export@ All Loan Group</b></a>
                
            </div>
            <div class="btn-group btn-group-sm">
                <a role="button" class="btn btn-sm" style="color:#1bbae1" onclick="reloadReport()"><i class="fa fa-refresh"></i> <b>Reload Report</b></a>

            </div>
        </li>
    </ul>
    <!-- END Table Styles Header -->
    <div class="container-fluid mvvm" style="padding-top: 20px; padding-bottom: 10px">
        <div class="row form-horizontal">
            <div class="form-group col-sm-4">
               <label class="control-label col-xs-4">@Month@</label>
               <div class="col-xs-8">
               <input id="start-date" data-role="datepicker" data-format="MM/yyyy" name="toDateTime" data-start="year" data-depth="year" data-bind="value: toDateTime" >
               </div>
            </div>
        </div>
        <div class="row chart-page"  style="background-color: white">
            <div data-role="tabstrip">
                <ul>
                    <li class="k-state-active">
                        ALL LOAN GROUP
                    </li>
                    <li>
                        Summary report
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
                     pageSize: 20,
                    //  filter: {
                    //       logic: "and",
                    //       filters: [
                    //           {field: 'ngay_thu_hoi', operator: "gte", value: this.fromDate},
                    //           {field: 'ngay_thu_hoi', operator: "lte", value: this.toDate}
                    //       ]
                    //  },
                     transport: {
                        read: ENV.reportApi + "/loan/List_of_all_customer_report/all_loan_group",
                        parameterMap: parameterMap
                     },
                     schema: {
                        data: "data",
                        total: "total",
                        parse: function (response) {
                          response.data.map(function(doc, index) {
                            doc.no = index
                            return doc;
                          })
                          return response;
                        }
                     },
                     sort: {field: "no", dir: "asc"}
                     
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
      var Table1 = function() {
         return {
              dataSource: {},
              grid: {},
              columns: [],
              model:{},
              
              init: function() {
                  var dataSource = this.dataSource = new kendo.data.DataSource({
                     serverPaging: true,
                     serverFiltering: true,
                     pageSize: 20,
                   
                     transport: {
                        read: ENV.reportApi + "/loan/List_of_all_customer_report/data",
                        parameterMap: parameterMap
                     },
                     schema: {
                        data: "data",
                        total: "total",
                        model:this.model,
                        parse: function (response) {
                          response.data.map(function(doc, index) {
                            doc.no = index
                            return doc;
                          })
                          return response;
                        }
                     },
                     sort: {field: "index", dir: "asc"}
                     
                  });
                    var d = new Date();
                    var date = d.getDate();
                    var month = d.getMonth() + 1; // Since getMonth() returns month from 0-11 not 1-12
                    var year = d.getFullYear();
                    var dateStr = date + "-" + month + "-" + year;
                  var grid = this.grid = $("#grid_1").kendoGrid({
                     toolbar: ["excel"],
                     dataSource: dataSource,
                     excel: {
                        fileName: "Summary report "+dateStr+".xlsx", 
                        allPages: true,
                        filterable: true
                    },
                     excelExport: function(e) {
                        var sheet = e.workbook.sheets[0];
                        var row = sheet.rows[0];
                        //Excel output - create a header row
                        
                        for (var rowIndex = 0; rowIndex < sheet.rows.length; rowIndex++) {
                          var row = sheet.rows[rowIndex];   
                            
                          for (var cellIndex = 0; cellIndex < row.cells.length; cellIndex ++) {
                                row.cells[cellIndex].borderRight = "3"
                                row.cells[cellIndex].borderBottom = "3"
                                row.cells[cellIndex].borderBottom = "3"
                                row.cells[cellIndex].borderTop = "3"
                            if (cellIndex ==0){
                                row.cells[cellIndex].bold = true
                            }
                            if (rowIndex !=0 && rowIndex !=1 && cellIndex!= 0 || rowIndex >=(sheet.rows.length-3)){
                                row.cells[cellIndex].format = "[Black]#,##0_);[Red]0.0);0"
                              }
                             if (rowIndex == (sheet.rows.length-3)){
                                row.cells[cellIndex].background = "#e2c9c9";
                             }
                             if (rowIndex == (sheet.rows.length-2)){
                                row.cells[cellIndex].background = "#e4fc5f";
                             }
                             if (rowIndex == (sheet.rows.length-1)){
                                row.cells[cellIndex].background = "#f7d631";
                             }
                                  
                             if (rowIndex == (sheet.rows.length-3)&&row.cells[cellIndex].value!='G2'){
                                var value = parseFloat(row.cells[cellIndex].value)/100;
                               
                                row.cells[cellIndex].value = value;
                                row.cells[cellIndex].format = "#0.00%;";
                             }
                             if (rowIndex == (sheet.rows.length-2)&&row.cells[cellIndex].value!='G2~'){
                                var value = parseFloat(row.cells[cellIndex].value)/100;
                               
                                row.cells[cellIndex].value = value;
                                row.cells[cellIndex].format = "#0.00%;";
                             }
                             if (rowIndex == (sheet.rows.length-1)&&row.cells[cellIndex].value!='G3~'){
                                var value = parseFloat(row.cells[cellIndex].value)/100;
                               
                                row.cells[cellIndex].value = value;
                                row.cells[cellIndex].format = "#0.00%;";
                             }
                              if (rowIndex ==0 || rowIndex ==1){
                                row.cells[cellIndex].background = "#008738";
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
        var dateRange = 30;
        var nowDate = new Date();
        var date =  new Date(),
             timeZoneOffset = date.getTimezoneOffset() * kendo.date.MS_PER_MINUTE;
             date.setHours(- timeZoneOffset / kendo.date.MS_PER_HOUR, 0, 0 ,0);

        var fromDate = new Date(date.getTime() + timeZoneOffset - (dateRange - 1) * 86400000);
        // var fromDate = new Date(date.getTime() + timeZoneOffset);
        var toDate = new Date(date.getTime() + timeZoneOffset + kendo.date.MS_PER_DAY -1)
            var listAllFields = new kendo.data.DataSource({
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
                      return doc.subType.column;
                    })
                    return response;
                  }
                 
              },
              filter: {
                  field: "collection",
                  operator: "eq",
                  value: (ENV.type ? ENV.type + "_" : "") + "List_of_all_customer_report"
              },
              page: 1,
              sort: {field: "index", dir: "asc"}
          })
          listAllFields.read().then(function(){
              var columns = listAllFields.data().toJSON();
              columns.map(col => {
                  switch(col.field){
                       
                        case "DT_TX":
                            col.width = 100;
                            // col.template = (dataItem) => gridDate(dataItem[col.field] != '' ? new Date(dataItem[col.field] * 1000) : null,"dd/MM/yyyy");
                            break;    
                        case "ACC_ID":
                            col.width = 150;
                            break;    
                        case "W_ORG":
                            col.width = 150;
                            col.template = (dataItem) => gridInterger(dataItem[col.field]);
                            break;
                        
                        case "PRODGRP_ID":  
                            col.width = 150;
                            col.template = (dataItem) => dataItem[col.field];
                        case "interest_rate":  
                            col.width = 150;
                            col.template = (dataItem) =>  kendo.toString(dataItem[col.field], "p");
                        default:
                            col.width = 120;
                            break;
                  }
                  switch (col.type) {
                      
                      case "timestamp":
                          col.template = (dataItem) => gridDate(dataItem[col.field] != '' ? new Date(dataItem[col.field] * 1000) : null);
                          break;
                      default:
                          break;
                  }
              });
              var fromDateTime = new Date(fromDate.getTime() - timeZoneOffset).toISOString();
              
              var toDateTime = new Date(toDate.getTime() - timeZoneOffset).toISOString();
              Table.columns = columns;
              Table.fromDate = fromDateTime
              Table.toDate = toDateTime
              Table.init();
          });
         
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
               var field = "DT_TX";
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
            var productFields = new kendo.data.DataSource({
              serverPaging: true,
              serverFiltering: true,
              serverSorting: true,
              transport: {
                  read: {
                    url: ENV.reportApi + "/loan/List_of_all_customer_report/product",    
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
                      
                    // response.data = response.data.filter(function(doc) {
                    //   if(doc.sub_type) 
                    //     doc.subType = JSON.parse(doc.sub_type);
                    //   else doc.subType = {};
                    //   return doc.subType.column;
                    // })
                    return response;
                  }
                 
              },
            //   filter: {
            //       field: "collection",
            //       operator: "eq",
            //       value: (ENV.type ? ENV.type + "_" : "") + "List_of_all_customer_report"
            //   },
              page: 1,
              sort: {field: "index", dir: "asc"}
            })
            var totalFields = new kendo.data.DataSource({
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
                      return doc.subType.column;
                    })
                    return response;
                  }
                 
              },
              filter: {
                  field: "collection",
                  operator: "eq",
                  value: (ENV.type ? ENV.type + "_" : "") + "List_of_all_customer_report"
              },
              page: 1,
              sort: {field: "index", dir: "asc"}
            })

            productFields.read().then(function(){
                
                
                
                var columns = [{
                            field: "group",
                            title: "Group",
                            width: 100
                           
                        }];
                
                var temp = productFields.data().toJSON();
                temp.map(col => {
                 columns.push({
                    title: col.name,
                    columns:[
                        {
                        field: "g"+col.code,
                        title: "NO of accounts",
                        format: "{0:n0}",   
                        width: 150
                        },
                        {
                        field: "a"+col.code,
                        title: "Amount",
                        width: 150,
                        format: "{0:n0}"
                        },
                        
                ],   
                 });
                
               
               
              });
              columns.push({
                title: "TOTAL",
                    columns:[
                        {
                        field: "t_g",
                        title: "NO of accounts",
                        format: "{0:n0}",   
                        width: 150
                        },
                        {
                        field: "t_a",
                        title: "Amount",
                        width: 150,
                        format: "{0:n0}"
                        },
                ],   
              });
              var fromDateTime = new Date(fromDate.getTime() - timeZoneOffset).toISOString();
              
              var toDateTime = new Date(toDate.getTime() - timeZoneOffset).toISOString();
              Table1.columns = columns;
              Table1.fromDate = fromDateTime
              Table1.toDate = toDateTime
              Table1.init();
          });
         
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
               var field = "DT_TX";
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
              url: ENV.reportApi + "loan/List_of_all_customer_report/downloadExcel",
              data:{month : $("#start-date").val()},
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

        function exportedExcel(){
             $.ajax({
                url: ENV.reportApi + "loan/List_of_all_customer_report/exportExcel",
                data:{month : '1/'+$("#start-date").val(),export:$("#start-date").val()},
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
        
        function reloadReport() {
            $.ajax({
              url: ENV.reportApi + "loan/List_of_all_customer_report/saveReport",
              type: 'POST',
              dataType: 'json',
              timeout: 30000,
            })
            .done(function(response) {
              if (response.status == 1) {
                notification.show('@Xin vui lòng đợi trong ít phút@', 'success');     
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

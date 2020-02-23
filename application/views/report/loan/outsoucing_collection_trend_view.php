<style>
    .k-tabstrip-items .k-item.k-state-active {
        background: #f1f1f1;
    }
</style>
<div id="page-content">
    <!-- Table Styles Header -->
    <ul class="breadcrumb breadcrumb-top">
        <li>@Report@</li>
        <li>Outsoucing Collection Trend</li>
    </ul>
    <!-- END Table Styles Header -->
    <div class="container-fluid mvvm" style="padding-top: 20px; padding-bottom: 10px">
        <div class="row form-horizontal">
            <div class="form-group col-sm-4">
               <label class="control-label col-xs-4">@Year@</label>
               <div class="col-xs-8">
               <input id="year" name="year" data-role="datepicker" data-format="yyyy" data-start="decade" data-depth="decade" data-bind="value: year" />
               </div>
            </div>
            <div class="form-group col-sm-4">
               <label class="control-label col-xs-4">@Partner@</label>
               <div class="col-xs-8">
                   <input data-role="combobox"
                        data-placeholder="Select Partner"
                        data-value-primitive="true"
                        data-text-field="ProductName"
                        data-value-field="ProductID"
                        data-bind="value: selectedPartner, source: partners, events: { change: partnerChange }" />
                </div>
            </div>
            <div class="pull-right" style="margin-right:20px">
                <div class="btn-group btn-group-sm">
                    <a role="button" class="btn btn-sm" onclick="saveAsExcel()"><i class="fa fa-file-excel-o"></i> <b>@Export@</b></a>
                    <!-- <a role="button" class="btn btn-sm" onclick="Table_1.grid.saveAsExcel()"><i class="fa fa-file-excel-o"></i> <b>@Export@ Card</b></a> -->
                </div>
            </div>
        </div>
        <div class="row chart-page" style="background-color: white">
            <h4 class="text-center widget-content"><a href="javascript:void(0)" style="cursor: none;text-decoration: none;"><strong>OUTSOURCING COLLECTION TREND</strong></a></h4>
            <div data-role="tabstrip">
                <ul style="width: 300px; margin: 0 auto;">
                    <li class="k-state-active" style="width: 145px; text-align: center; border: 1px solid thistle; margin: 1px;">
                        Amount
                    </li>
                    <li style="width: 145px; text-align: center; border: 1px solid thistle; margin: 1px;">
                        Assigned DPD
                    </li>
                </ul>
                <div style="border:0">
                  <div class="container-fluid">
                    <div class="col-sm-12">
                        <div id="grid_amount"></div>
                    </div>
                  </div>
                </div>
                <div style="border:0">
                    <div class="container-fluid">
                      <div class="col-sm-12">
                        <div id="grid_assigned_dpd"></div>
                      </div>
                    </div>
                </div>
              </div>
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
                        pageSize: 20,
                        transport: {
                            read: ENV.reportApi + "loan/outsoucing_collection_trend_report/read",
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
                        group : { field : "COMPANY" },
                        sort: {field: "no", dir: "asc"}                   
                    });

                    var gridAmount = this.grid = $("#grid_amount").kendoGrid({
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
                        // dataBound: function(e){
                        //     console.log(this.dataSource.group())
                        //     if (this.dataSource.group().length == 0){
                        //         var columns = $("#grid_amount .k-grid-header .k-link");
                        //         var fields = [];
                        //         var newButtonText = [];
                            
                        //         $(e.groups).each(function (index) { //for each grouped field...
                        //             fields.push(e.groups[index].field); //gather fields to be grouped...
                        //         });
                            
                        //         $(columns).each(function (i) { //for each column header in the Demand Report grid...
                        //             var column = columns[i];
                        //             $(fields).each(function (index) { //for each field
                        //                 if (column.innerHTML.indexOf(fields[index]) !== -1) { //see if column's innerHTML contains fieldname (workaround)
                        //                     newButtonText.push(column.text); //matched, let's store the column's header for the button text
                        //                 }
                        //             });
                        //         });
                            
                        //         var groupingColumns = $(".k-grouping-header a.k-link");
                        //         $(groupingColumns).each(function (index) { //for each group button
                        //             $(this).text(newButtonText[index]); //update button text with dragged column's text
                        //         });
                        //     }
                        // },
                        resizable: true,
                        pageable: true,
                        sortable: true,
                        scrollable: true,
                        columns: this.columns,
                        noRecords: {
                            template: `<h2 class='text-danger'>${KENDO.noRecords}</h2>`
                        }
                    }).data("kendoGrid");

                    gridAmount.selectedKeyNames = function() {
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
            var date =  new Date(),
                timeZoneOffset = date.getTimezoneOffset() * kendo.date.MS_PER_MINUTE;
                date.setHours(- timeZoneOffset / kendo.date.MS_PER_HOUR, 0, 0 ,0);
                var fromDate = new Date(date.getYear(), 1, 1, 0, 0, 0);
                var toDate = new Date(date.getTime() + timeZoneOffset + kendo.date.MS_PER_DAY -1)
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
                            return doc.subType.column;
                            })
                            return response;
                        }
                        
                    },
                    filter: {
                        field: "collection",
                        operator: "eq",
                        value: (ENV.type ? ENV.type + "_" : "") + "Cus_assigned_partner"
                    },
                    page: 1,
                    sort: {field: "index", dir: "asc"}
                })
                lawsuitFields.read().then(function(){
                    var columns = lawsuitFields.data().toJSON();
                    columns.map(col => {
                        switch(col.field){
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
        function mergeGridRows(gridId, colTitle) {
            $('#' + gridId + '>.k-grid-content>table').each(function (index, item) {
                var dimension_col = 1;
                // First, scan first row of headers for the "Dimensions" column.
                $('#' + gridId + '>.k-grid-header>.k-grid-header-wrap>table').find('th').each(function () {
                    if ($(this).text() == colTitle) {

                        // first_instance holds the first instance of identical td
                        var first_instance = null;

                        $(item).find('tr').each(function () {

                            // find the td of the correct column (determined by the colTitle)
                            var dimension_td = $(this).find('td:nth-child(' + dimension_col + ')');

                            if (first_instance == null) {
                                first_instance = dimension_td;
                            } else if (dimension_td.text() == first_instance.text()) {
                                // if current td is identical to the previous
                                // then remove the current td
                                dimension_td.remove();
                                // increment the rowspan attribute of the first instance
                                first_instance.attr('rowspan', typeof first_instance.attr('rowspan') == "undefined" ? 2 : first_instance.attr('rowspan') + 1);
                            } else {
                                // this cell is different from the last
                                first_instance = dimension_td;
                            }
                        });
                        return;
                    }
                    dimension_col++;
                });
            });
        }

        function saveAsExcel() {
            $.ajax({
                url: ENV.reportApi + "loan/Outsoucing_collection_trend_report/downloadExcel",
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
<div id="page-content">
<!-- Table Styles Header -->
<ul class="breadcrumb breadcrumb-top">
    <li>@Report@</li>
    <li>Loan Group Report</li>
    <li class="pull-right none-breakcrumb" id="top-row">
        <div class="btn-group btn-group-sm">
            <a role="button" class="btn btn-sm" onclick="saveAsExcel()"><i class="fa fa-file-excel-o"></i> <b>@Export@</b></a>
        </div>
        <div class="btn-group btn-group-sm">
            <a role="button" class="btn btn-sm" style="color:#1bbae1" onclick="reloadReport()"><i class="fa fa-refresh"></i> <b> Reload Report</b></a>
        </div>
    </li>
</ul>
<!-- END Table Styles Header -->
<div class="container-fluid mvvm" style="padding-top: 20px; padding-bottom: 10px">
    <div class="row form-horizontal">
        <div class="form-group col-sm-4">
            <label class="control-label col-xs-4">@Date@</label>
            <div class="col-xs-8">
                <input id="start-date" data-role="datepicker" data-format="MM/yyyy" name="fromDateTime" data-bind="value: fromDateTime, events: {change: startDate}" disabled="">
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
            init: function() {
                var dataSource = this.dataSource = new kendo.data.DataSource({
                    serverPaging: true,
                    serverFiltering: true,
                    pageSize: 6,
                    transport: {
                    read: ENV.reportApi + "loan/last_past_year_report",
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
                    columns: [
                        {
                            field: "type",
                            title: "Product",
                            width: 100,
                        },{
                            field: "name",
                            title: "The Last months",
                            width: 150,
                        },{
                            field: "sales_period",
                            title: "Sales period　売上期間 （月）",
                            width: 150,
                        },{
                            title: "Total",
                            columns: [{
                                field: 'total_w_org',
                                title: 'Total W_ORG',
                                width: 130
                            }, {
                                field: 'total_account',
                                title: 'No.of Account',
                                width: 80
                            }]
                        },{
                            title: "Group 2",
                            columns: [{
                                field: 'w_org_group_b',
                                title: 'Total W_ORG',
                                width: 130
                            }, {
                                field: 'account_group_b',
                                title: 'No.of Account',
                                width: 80
                            }, {
                                field: 'group_b_ratio',
                                title: 'Overdue retio',
                                width: 80
                            }]
                        },{
                            title: "Group 3",
                            columns: [{
                                field: 'w_org_group_c',
                                title: 'Total W_ORG',
                                width: 130
                            }, {
                                field: 'account_group_c',
                                title: 'No.of Account',
                                width: 80
                            }, {
                                field: 'group_c_ratio',
                                title: 'Overdue retio',
                                width: 80
                            }]
                        },{
                            title: "Group 3 over",
                            columns: [{
                                field: 'w_org_group_c_over',
                                title: 'Total W_ORG',
                                width: 130
                            }, {
                                field: 'account_group_c_over',
                                title: 'No.of Account',
                                width: 80
                            }, {
                                field: 'group_c_over_ratio',
                                title: 'Overdue retio',
                                width: 80
                            }]
                        },

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
        Table.init();
        var dateRange = 30;
        var nowDate = new Date();
        var date =  new Date();
        // date.setDate(nowDate.getDate() - 1);
        var timeZoneOffset = date.getTimezoneOffset() * kendo.date.MS_PER_MINUTE;
        date.setHours(- timeZoneOffset / kendo.date.MS_PER_HOUR, 0, 0 ,0);

        var fromDate = new Date(date.getTime() + timeZoneOffset - (dateRange - 1) * 86400000);
        // var fromDate = new Date(date.getTime() + timeZoneOffset);
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
            url: ENV.reportApi + "loan/last_past_year_report/downloadExcel",
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
          url: ENV.reportApi + "loan/last_past_year_report/saveReport",
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

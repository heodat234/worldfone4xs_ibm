    <div id="page-content">
        <!-- Table Styles Header -->
        <ul class="breadcrumb breadcrumb-top">
            <li>@Report@</li>
            <li>Daily All User Report</li>
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
                    <label class="control-label col-xs-4">@Date@</label>
                    <div class="col-xs-8">
                        <input id="start-date" data-role="datepicker" data-format="dd/MM/yyyy" name="fromDateTime" data-bind="value: fromDateTime" >
                    </div>
                </div>
                <!-- <div class="form-group col-sm-4">
                <label class="control-label col-xs-4">@To date@</label>
                <div class="col-xs-8">
                    <input id="end-date" data-role="datepicker" data-format="dd/MM/yyyy H:mm:ss" name="toDateTime" data-bind="value: toDateTime, events: {change: endDate}">
                </div>
                </div> -->
                <div class="form-group col-sm-4 text-center">
                    <button class="k-button" data-bind="click: search">@Search@</button>
                </div>
            </div>
            <div class="row chart-page"  style="background-color: white">

                <div class="col-sm-12">
                    <div id="grid"></div>
                </div>
            </div>
            <!-- <div class="row" data-bind="visible: visibleNoData">
                <h3 class="text-center">@NO DATA@</h3>
            </div> -->
        </div>
        <div id="action-menu">
            <ul>
                
            </ul>
        </div>
        <script>
        var Config = {
            crudApi: `${ENV.reportApi}`,
            templateApi: `${ENV.templateApi}`,
            collection: "daily_all_user_report",
            observable: {
                
            },
            model: {
                id: "id",
                fields: {
                    
                }
            },
            parse: function (response) {
                response.data.map(function(doc) {
                    // doc.due_date = new date(doc.due_date * 1000)
                    doc.due_date = doc.due_date ? new Date(doc.due_date * 1000) : undefined;
                    return doc;
                })
                return response;
            },
            filterable: KENDO.filterable
        };
        </script>
        <script>
        var Table = function() {
            return {
                dataSource: {},
                grid: {},
                formDate: 0,
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
                        columns: [
                            {
                                field: "name",
                                title: "Group",
                                width: 150,
                            },{
                                field: "count_data",
                                title: "Total handled accounts",
                                width: 100,

                            },{
                                field: "unwork",
                                title: "Unwork accounts",
                                width: 100,
                            },{
                                field: "work",
                                title: "Work accounts",
                                width: 100,
                            },
                            {
                                field: "talk_time",
                                title: "Talk time (minutes)",
                                width: 100,
                            },
                            {
                                title: "Contacted",
                                columns: [{
                                    field: 'count_contacted',
                                    title: 'No.of accounts',
                                    width: 80
                                }, {
                                    field: 'contacted_amount',
                                    title: 'No.of amount',
                                    width: 80
                                }]
                            },
                            {
                                field: "number_of_call",
                                title: "Số HĐ đã gọi hôm qua",
                                width: 80,
                            },
                            {
                                field: 'total_call',
                                title: 'Total call made include drop call',
                                width: 80,
                            },
                            
                            {
                                title: "Promise to pay",
                                columns: [{
                                    field: 'count_ptp',
                                    title: 'No.of accounts',
                                    width: 80
                                }, {
                                    field: 'ptp_amount',
                                    title: 'No.of amount',
                                    width: 80
                                }]
                            },
                            {
                                title: "Connected",
                                columns: [{
                                    field: 'count_conn',
                                    title: 'No.of accounts',
                                    width: 80
                                }, {
                                    field: 'conn_amount',
                                    title: 'No.of amount',
                                    width: 80
                                }]
                            },
                            {
                                title: "Paid",
                                columns: [{
                                    field: 'count_paid',
                                    title: 'No.of accounts',
                                    width: 80
                                }, {
                                    field: 'paid_amount',
                                    title: 'Actual Amount received',
                                    width: 80
                                }, {
                                    field: 'count_paid_promise',
                                    title: 'No.of accounts (keep promise to pay today)',
                                    width: 80
                                }, {
                                    field: 'paid_amount_promise',
                                    title: 'Actual Amount received (keep promise to pay today)',
                                    width: 80
                                }, {
                                    field: 'count_ptp_all_days',
                                    title: 'No.of accounts (keep promise to pay)',
                                    width: 80
                                }, {
                                    field: 'paid_amount_all_days',
                                    title: 'Actual Amount received (keep promise to pay)',
                                    width: 80
                                }]
                            },
                            {
                                title: "Call made rate",
                                columns: [{
                                    field: 'call_rate',
                                    title: 'Account',
                                    width: 80
                                }]
                            },
                            {
                                title: "PTP rate",
                                columns: [{
                                    field: 'ptp_rate_acc',
                                    title: 'PTP rate (total paid accounts)',
                                    width: 80
                                }, {
                                    field: 'ptp_rate_amt',
                                    title: 'PTP rate (total paid amount)',
                                    width: 80
                                }, {
                                    field: 'paid_rate_acc',
                                    title: 'PTP rate (Promised accounts)',
                                    width: 80
                                }, {
                                    field: 'paid_rate_amt',
                                    title: 'PTP rate (PromisedAmount)',
                                    width: 80
                                }]
                            },
                            {
                                title: "Connected rate",
                                columns: [{
                                    field: 'conn_rate',
                                    title: 'Account',
                                    width: 80
                                }]
                            },
                            {
                                title: "Collected ratio",
                                columns: [{
                                    field: 'collect_ratio_acc',
                                    title: 'Account',
                                    width: 100
                                }, {
                                    field: 'collect_ratio_amt',
                                    title: 'Amount',
                                    width: 80
                                }]
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
            var dateRange = 30;
            var nowDate = new Date();
            var date =  new Date();
            date.setDate(nowDate.getDate() - 1);
            var timeZoneOffset = date.getTimezoneOffset() * kendo.date.MS_PER_MINUTE;
            date.setHours(- timeZoneOffset / kendo.date.MS_PER_HOUR, 0, 0 ,0);

            // var fromDate = new Date(date.getTime() + timeZoneOffset -  86400000);
            var fromDate = new Date(date.getTime() + timeZoneOffset);
            var toDate = new Date(date.getTime() + timeZoneOffset + kendo.date.MS_PER_DAY -1);

            Table.fromDate = fromDate.getTime() / 1000 + 86400;
            Table.toDate = Table.fromDate + 86400 - 1;
            Table.init();

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
                    var field = "createdAt";
                    // var fromDateTime = new Date(this.fromDateTime.getTime() - timeZoneOffset).toISOString();
                    // var toDateTime = new Date(this.toDateTime.getTime() - timeZoneOffset).toISOString();
                    var fromDateTime = this.fromDateTime.getTime() / 1000 + 86400;
                    var toDateTime = fromDateTime + 86400 - 1;
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
                url: Config.crudApi + 'loan/' + Config.collection + "/exportExcel",
                type: 'POST',
                dataType: 'json',
                data: {date: $('#start-date').val()},
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

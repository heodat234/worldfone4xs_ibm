<div id="page-content">
<!-- Table Styles Header -->
<ul class="breadcrumb breadcrumb-top">
    <li>@Report@</li>
    <li>Delinquent occurance transaction</li>
    <li class="pull-right none-breakcrumb" id="top-row">
        <div class="btn-group btn-group-sm">
            <a role="button" class="btn btn-sm" onclick="saveAsExcel()"><i class="fa fa-file-excel-o"></i> <b>@Export@</b></a>
        </div>
    </li>
</ul>
<!-- END Table Styles Header -->
<div class="container-fluid mvvm" style="padding-top: 20px; padding-bottom: 10px; background-color: #ffffff">
    <h3 class="col-sm-12 text-center" style="margin-bottom: 20px;color: #27ae60">Delinquency occurrence transition table by loan terms  (Amount of money)</h3>
    <h4 class="col-sm-12 text-center" style="margin-bottom: 20px;color: #27ae60">貸付条件別滞納発生推移表（金額)</h4>
    <div class="col-sm-12 grid-data"></div>
    <div class="row chart-page"  style="background-color: white">
    <div class="col-sm-12">
        <h3>JIVF  TOTAL</h3>
    </div>
    <div class="col-sm-12">
        <div id="total-grid"></div>
    </div>
    <!-- <div class="row chart-page"  style="background-color: white">
        <div class="col-sm-12">
            <h3>TOTAL</h3>
        </div>
        <div class="col-sm-12">
            <div id="grid_total"></div>
        </div>
    </div>
    <div class="row" data-bind="visible: visibleNoData">
        <h3 class="text-center">@NO DATA@</h3>
    </div> -->
</div>
<div id="action-menu">
    <ul>
        
    </ul>
</div>
<script>
    var Table = function() {
        return {
            dataSource: {},
            dataSource_total: {},
            dataSource_detail: {},
            grid: {},
            grid_total: {},
            init: function() {
                var date =  new Date();
                date.setHours(0, 0 ,0, 0);
                date.setDate(1);
                var fromDate = date.getTime() / 1000;
                var lastDateOfMonth = kendo.date.lastDayOfMonth(new Date());
                var toDate = lastDateOfMonth.getTime() / 1000;
                $.ajax({
                    url: ENV.reportApi + 'loan/monthly_delinquent_occurence_transaction/getListProductGroup',
                    type: 'POST',
                    success: function(response) {
                        response.map(async function(doc) {
                            var columns = [{
                                field: 'int_rate_name',
                                title: doc.group_name,
                                width: 200
                            }, {
                                title: '1/' + date.getFullYear(),
                                columns: [{
                                    title: 'Total',
                                    columns: [{
                                        field: 'total_w_org_1_' + date.getFullYear(),
                                        title: 'Total W-ORG',
                                        width: 200
                                    }, {
                                        field: 'total_acc_count_1_' + date.getFullYear(),
                                        title: 'No.of Account',
                                        width: 200
                                    }]
                                }, {
                                    title: 'Group 2',
                                    columns: [{
                                        field: 'group_2_w_org_1_' + date.getFullYear(),
                                        title: 'Overdue W-ORG',
                                        width: 200
                                    }, {
                                        field: 'group_2_acc_count_1_' + date.getFullYear(),
                                        title: 'No.of Account',
                                        width: 200
                                    }, {
                                        field: 'group_2_overdue_ratio_1_' + date.getFullYear(),
                                        title: 'Overdue ratio',
                                        width: 200
                                    }]
                                }, {
                                    title: 'Group 3 and over',
                                    columns: [{
                                        field: 'group_3_over_w_org_1_' + date.getFullYear(),
                                        title: 'Overdue W-ORG',
                                        width: 200
                                    }, {
                                        field: 'group_3_over_acc_count_1_' + date.getFullYear(),
                                        title: 'No.of Account',
                                        width: 200
                                    }, {
                                        field: 'group_3_over_overdue_ratio_1_' + date.getFullYear(),
                                        title: 'Overdue ratio',
                                        width: 200
                                    }]
                                }]
                            }, {
                                title: '2/' + date.getFullYear(),
                                columns: [{
                                    title: 'Total',
                                    columns: [{
                                        field: 'total_w_org_2_' + date.getFullYear(),
                                        title: 'Total W-ORG',
                                        width: 200
                                    }, {
                                        field: 'total_acc_count_2_' + date.getFullYear(),
                                        title: 'No.of Account',
                                        width: 200
                                    }]
                                }, {
                                    title: 'Group 2',
                                    columns: [{
                                        field: 'group_2_w_org_2_' + date.getFullYear(),
                                        title: 'Overdue W-ORG',
                                        width: 200
                                    }, {
                                        field: 'group_2_acc_count_2_' + date.getFullYear(),
                                        title: 'No.of Account',
                                        width: 200
                                    }, {
                                        field: 'group_2_overdue_ratio_2_' + date.getFullYear(),
                                        title: 'Overdue ratio',
                                        width: 200
                                    }]
                                }, {
                                    title: 'Group 3 and over',
                                    columns: [{
                                        field: 'group_3_over_w_org_2_' + date.getFullYear(),
                                        title: 'Overdue W-ORG',
                                        width: 200
                                    }, {
                                        field: 'group_3_over_acc_count_2_' + date.getFullYear(),
                                        title: 'No.of Account',
                                        width: 200
                                    }, {
                                        field: 'group_3_over_overdue_ratio_2_' + date.getFullYear(),
                                        title: 'Overdue ratio',
                                        width: 200
                                    }]
                                }]
                            }, {
                                title: '3/' + date.getFullYear(),
                                columns: [{
                                    title: 'Total',
                                    columns: [{
                                        field: 'total_w_org_3_' + date.getFullYear(),
                                        title: 'Total W-ORG',
                                        width: 200
                                    }, {
                                        field: 'total_acc_count_3_' + date.getFullYear(),
                                        title: 'No.of Account',
                                        width: 200
                                    }]
                                }, {
                                    title: 'Group 2',
                                    columns: [{
                                        field: 'group_2_w_org_3_' + date.getFullYear(),
                                        title: 'Overdue W-ORG',
                                        width: 200
                                    }, {
                                        field: 'group_2_acc_count_3_' + date.getFullYear(),
                                        title: 'No.of Account',
                                        width: 200
                                    }, {
                                        field: 'group_2_overdue_ratio_3_' + date.getFullYear(),
                                        title: 'Overdue ratio',
                                        width: 200
                                    }]
                                }, {
                                    title: 'Group 3 and over',
                                    columns: [{
                                        field: 'group_3_over_w_org_3_' + date.getFullYear(),
                                        title: 'Overdue W-ORG',
                                        width: 200
                                    }, {
                                        field: 'group_3_over_acc_count_3_' + date.getFullYear(),
                                        title: 'No.of Account',
                                        width: 200
                                    }, {
                                        field: 'group_3_over_overdue_ratio_3_' + date.getFullYear(),
                                        title: 'Overdue ratio',
                                        width: 200
                                    }]
                                }]
                            }, {
                                title: '4/' + date.getFullYear(),
                                columns: [{
                                    title: 'Total',
                                    columns: [{
                                        field: 'total_w_org_4_' + date.getFullYear(),
                                        title: 'Total W-ORG',
                                        width: 200
                                    }, {
                                        field: 'total_acc_count_4_' + date.getFullYear(),
                                        title: 'No.of Account',
                                        width: 200
                                    }]
                                }, {
                                    title: 'Group 2',
                                    columns: [{
                                        field: 'group_2_w_org_4_' + date.getFullYear(),
                                        title: 'Overdue W-ORG',
                                        width: 200
                                    }, {
                                        field: 'group_2_acc_count_4_' + date.getFullYear(),
                                        title: 'No.of Account',
                                        width: 200
                                    }, {
                                        field: 'group_2_overdue_ratio_4_' + date.getFullYear(),
                                        title: 'Overdue ratio',
                                        width: 200
                                    }]
                                }, {
                                    title: 'Group 3 and over',
                                    columns: [{
                                        field: 'group_3_over_w_org_4_' + date.getFullYear(),
                                        title: 'Overdue W-ORG',
                                        width: 200
                                    }, {
                                        field: 'group_3_over_acc_count_4_' + date.getFullYear(),
                                        title: 'No.of Account',
                                        width: 200
                                    }, {
                                        field: 'group_3_over_overdue_ratio_4_' + date.getFullYear(),
                                        title: 'Overdue ratio',
                                        width: 200
                                    }]
                                }]
                            }, {
                                title: '5/' + date.getFullYear(),
                                columns: [{
                                    title: 'Total',
                                    columns: [{
                                        field: 'total_w_org_5_' + date.getFullYear(),
                                        title: 'Total W-ORG',
                                        width: 200
                                    }, {
                                        field: 'total_acc_count_5_' + date.getFullYear(),
                                        title: 'No.of Account',
                                        width: 200
                                    }]
                                }, {
                                    title: 'Group 2',
                                    columns: [{
                                        field: 'group_2_w_org_5_' + date.getFullYear(),
                                        title: 'Overdue W-ORG',
                                        width: 200
                                    }, {
                                        field: 'group_2_acc_count_5_' + date.getFullYear(),
                                        title: 'No.of Account',
                                        width: 200
                                    }, {
                                        field: 'group_2_overdue_ratio_5_' + date.getFullYear(),
                                        title: 'Overdue ratio',
                                        width: 200
                                    }]
                                }, {
                                    title: 'Group 3 and over',
                                    columns: [{
                                        field: 'group_3_over_w_org_5_' + date.getFullYear(),
                                        title: 'Overdue W-ORG',
                                        width: 200
                                    }, {
                                        field: 'group_3_over_acc_count_5_' + date.getFullYear(),
                                        title: 'No.of Account',
                                        width: 200
                                    }, {
                                        field: 'group_3_over_overdue_ratio_5_' + date.getFullYear(),
                                        title: 'Overdue ratio',
                                        width: 200
                                    }]
                                }]
                            }, {
                                title: '6/' + date.getFullYear(),
                                columns: [{
                                    title: 'Total',
                                    columns: [{
                                        field: 'total_w_org_6_' + date.getFullYear(),
                                        title: 'Total W-ORG',
                                        width: 200
                                    }, {
                                        field: 'total_acc_count_6_' + date.getFullYear(),
                                        title: 'No.of Account',
                                        width: 200
                                    }]
                                }, {
                                    title: 'Group 2',
                                    columns: [{
                                        field: 'group_2_w_org_6_' + date.getFullYear(),
                                        title: 'Overdue W-ORG',
                                        width: 200
                                    }, {
                                        field: 'group_2_acc_count_6_' + date.getFullYear(),
                                        title: 'No.of Account',
                                        width: 200
                                    }, {
                                        field: 'group_2_overdue_ratio_6_' + date.getFullYear(),
                                        title: 'Overdue ratio',
                                        width: 200
                                    }]
                                }, {
                                    title: 'Group 3 and over',
                                    columns: [{
                                        field: 'group_3_over_w_org_6_' + date.getFullYear(),
                                        title: 'Overdue W-ORG',
                                        width: 200
                                    }, {
                                        field: 'group_3_over_acc_count_6_' + date.getFullYear(),
                                        title: 'No.of Account',
                                        width: 200
                                    }, {
                                        field: 'group_3_over_overdue_ratio_6_' + date.getFullYear(),
                                        title: 'Overdue ratio',
                                        width: 200
                                    }]
                                }]
                            }, {
                                title: '7/' + date.getFullYear(),
                                columns: [{
                                    title: 'Total',
                                    columns: [{
                                        field: 'total_w_org_7_' + date.getFullYear(),
                                        title: 'Total W-ORG',
                                        width: 200
                                    }, {
                                        field: 'total_acc_count_7_' + date.getFullYear(),
                                        title: 'No.of Account',
                                        width: 200
                                    }]
                                }, {
                                    title: 'Group 2',
                                    columns: [{
                                        field: 'group_2_w_org_7_' + date.getFullYear(),
                                        title: 'Overdue W-ORG',
                                        width: 200
                                    }, {
                                        field: 'group_2_acc_count_7_' + date.getFullYear(),
                                        title: 'No.of Account',
                                        width: 200
                                    }, {
                                        field: 'group_2_overdue_ratio_7_' + date.getFullYear(),
                                        title: 'Overdue ratio',
                                        width: 200
                                    }]
                                }, {
                                    title: 'Group 3 and over',
                                    columns: [{
                                        field: 'group_3_over_w_org_7_' + date.getFullYear(),
                                        title: 'Overdue W-ORG',
                                        width: 200
                                    }, {
                                        field: 'group_3_over_acc_count_7_' + date.getFullYear(),
                                        title: 'No.of Account',
                                        width: 200
                                    }, {
                                        field: 'group_3_over_overdue_ratio_7_' + date.getFullYear(),
                                        title: 'Overdue ratio',
                                        width: 200
                                    }]
                                }]
                            }, {
                                title: '8/' + date.getFullYear(),
                                columns: [{
                                    title: 'Total',
                                    columns: [{
                                        field: 'total_w_org_8_' + date.getFullYear(),
                                        title: 'Total W-ORG',
                                        width: 200
                                    }, {
                                        field: 'total_acc_count_8_' + date.getFullYear(),
                                        title: 'No.of Account',
                                        width: 200
                                    }]
                                }, {
                                    title: 'Group 2',
                                    columns: [{
                                        field: 'group_2_w_org_8_' + date.getFullYear(),
                                        title: 'Overdue W-ORG',
                                        width: 200
                                    }, {
                                        field: 'group_2_acc_count_8_' + date.getFullYear(),
                                        title: 'No.of Account',
                                        width: 200
                                    }, {
                                        field: 'group_2_overdue_ratio_8_' + date.getFullYear(),
                                        title: 'Overdue ratio',
                                        width: 200
                                    }]
                                }, {
                                    title: 'Group 3 and over',
                                    columns: [{
                                        field: 'group_3_over_w_org_8_' + date.getFullYear(),
                                        title: 'Overdue W-ORG',
                                        width: 200
                                    }, {
                                        field: 'group_3_over_acc_count_8_' + date.getFullYear(),
                                        title: 'No.of Account',
                                        width: 200
                                    }, {
                                        field: 'group_3_over_overdue_ratio_8_' + date.getFullYear(),
                                        title: 'Overdue ratio',
                                        width: 200
                                    }]
                                }]
                            }, {
                                title: '9/' + date.getFullYear(),
                                columns: [{
                                    title: 'Total',
                                    columns: [{
                                        field: 'total_w_org_9_' + date.getFullYear(),
                                        title: 'Total W-ORG',
                                        width: 200
                                    }, {
                                        field: 'total_acc_count_9_' + date.getFullYear(),
                                        title: 'No.of Account',
                                        width: 200
                                    }]
                                }, {
                                    title: 'Group 2',
                                    columns: [{
                                        field: 'group_2_w_org_9_' + date.getFullYear(),
                                        title: 'Overdue W-ORG',
                                        width: 200
                                    }, {
                                        field: 'group_2_acc_count_9_' + date.getFullYear(),
                                        title: 'No.of Account',
                                        width: 200
                                    }, {
                                        field: 'group_2_overdue_ratio_9_' + date.getFullYear(),
                                        title: 'Overdue ratio',
                                        width: 200
                                    }]
                                }, {
                                    title: 'Group 3 and over',
                                    columns: [{
                                        field: 'group_3_over_w_org_9_' + date.getFullYear(),
                                        title: 'Overdue W-ORG',
                                        width: 200
                                    }, {
                                        field: 'group_3_over_acc_count_9_' + date.getFullYear(),
                                        title: 'No.of Account',
                                        width: 200
                                    }, {
                                        field: 'group_3_over_overdue_ratio_9_' + date.getFullYear(),
                                        title: 'Overdue ratio',
                                        width: 200
                                    }]
                                }]
                            }, {
                                title: '10/' + date.getFullYear(),
                                columns: [{
                                    title: 'Total',
                                    columns: [{
                                        field: 'total_w_org_10_' + date.getFullYear(),
                                        title: 'Total W-ORG',
                                        width: 200
                                    }, {
                                        field: 'total_acc_count_10_' + date.getFullYear(),
                                        title: 'No.of Account',
                                        width: 200
                                    }]
                                }, {
                                    title: 'Group 2',
                                    columns: [{
                                        field: 'group_2_w_org_10_' + date.getFullYear(),
                                        title: 'Overdue W-ORG',
                                        width: 200
                                    }, {
                                        field: 'group_2_acc_count_10_' + date.getFullYear(),
                                        title: 'No.of Account',
                                        width: 200
                                    }, {
                                        field: 'group_2_overdue_ratio_10_' + date.getFullYear(),
                                        title: 'Overdue ratio',
                                        width: 200
                                    }]
                                }, {
                                    title: 'Group 3 and over',
                                    columns: [{
                                        field: 'group_3_over_w_org_10_' + date.getFullYear(),
                                        title: 'Overdue W-ORG',
                                        width: 200
                                    }, {
                                        field: 'group_3_over_acc_count_10_' + date.getFullYear(),
                                        title: 'No.of Account',
                                        width: 200
                                    }, {
                                        field: 'group_3_over_overdue_ratio_10_' + date.getFullYear(),
                                        title: 'Overdue ratio',
                                        width: 200
                                    }]
                                }]
                            }, {
                                title: '11/' + date.getFullYear(),
                                columns: [{
                                    title: 'Total',
                                    columns: [{
                                        field: 'total_w_org_11_' + date.getFullYear(),
                                        title: 'Total W-ORG',
                                        width: 200
                                    }, {
                                        field: 'total_acc_count_11_' + date.getFullYear(),
                                        title: 'No.of Account',
                                        width: 200
                                    }]
                                }, {
                                    title: 'Group 2',
                                    columns: [{
                                        field: 'group_2_w_org_11_' + date.getFullYear(),
                                        title: 'Overdue W-ORG',
                                        width: 200
                                    }, {
                                        field: 'group_2_acc_count_11_' + date.getFullYear(),
                                        title: 'No.of Account',
                                        width: 200
                                    }, {
                                        field: 'group_2_overdue_ratio_11_' + date.getFullYear(),
                                        title: 'Overdue ratio',
                                        width: 200
                                    }]
                                }, {
                                    title: 'Group 3 and over',
                                    columns: [{
                                        field: 'group_3_over_w_org_11_' + date.getFullYear(),
                                        title: 'Overdue W-ORG',
                                        width: 200
                                    }, {
                                        field: 'group_3_over_acc_count_11_' + date.getFullYear(),
                                        title: 'No.of Account',
                                        width: 200
                                    }, {
                                        field: 'group_3_over_overdue_ratio_11_' + date.getFullYear(),
                                        title: 'Overdue ratio',
                                        width: 200
                                    }]
                                }]
                            }, {
                                title: '12/' + date.getFullYear(),
                                columns: [{
                                    title: 'Total',
                                    columns: [{
                                        field: 'total_w_org_12_' + date.getFullYear(),
                                        title: 'Total W-ORG',
                                        width: 200
                                    }, {
                                        field: 'total_acc_count_12_' + date.getFullYear(),
                                        title: 'No.of Account',
                                        width: 200
                                    }]
                                }, {
                                    title: 'Group 2',
                                    columns: [{
                                        field: 'group_2_w_org_12_' + date.getFullYear(),
                                        title: 'Overdue W-ORG',
                                        width: 200
                                    }, {
                                        field: 'group_2_acc_count_12_' + date.getFullYear(),
                                        title: 'No.of Account',
                                        width: 200
                                    }, {
                                        field: 'group_2_overdue_ratio_12_' + date.getFullYear(),
                                        title: 'Overdue ratio',
                                        width: 200
                                    }]
                                }, {
                                    title: 'Group 3 and over',
                                    columns: [{
                                        field: 'group_3_over_w_org_12_' + date.getFullYear(),
                                        title: 'Overdue W-ORG',
                                        width: 200
                                    }, {
                                        field: 'group_3_over_acc_count_12_' + date.getFullYear(),
                                        title: 'No.of Account',
                                        width: 200
                                    }, {
                                        field: 'group_3_over_overdue_ratio_12_' + date.getFullYear(),
                                        title: 'Overdue ratio',
                                        width: 200
                                    }]
                                }]
                            }, ]
                            
                            await $('.grid-data').append(`<div class="row chart-page" style="background-color: white">
                                                            <div class="col-sm-12">
                                                                <h3>${doc.group_name}</h3>
                                                            </div>
                                                            <div class="col-sm-12">
                                                                <div id="grid_${doc.group_code}"></div>
                                                            </div>
                                                        </div>`);

                            if(typeof fromDate != 'undefined' && typeof toDate != 'undefined') {
                                var dataSource = this.dataSource = new kendo.data.DataSource({
                                    serverPaging: true,
                                    serverFiltering: true,
                                    serverSorting: true,
                                    sort: [{field: 'int_rate', dir: 'asc'}],
                                    filter: [
                                        {
                                            field: 'year',
                                            operator: 'eq',
                                            value: date.getFullYear()
                                        }, 
                                        {
                                            field: 'group_code',
                                            operator: 'eq',
                                            value: doc.group_code
                                        }
                                    ],
                                    pageSize: 10,
                                    transport: {
                                        read: ENV.reportApi + "loan/monthly_delinquent_occurence_transaction/read",
                                        parameterMap: parameterMap
                                    },
                                    schema: {
                                    data: "data",
                                    total: "total",
                                    parse: function (response) {
                                        // i = 1;
                                        // response.data.map(function(doc) {
                                        //     doc.date = new Date().toLocaleDateString();
                                        //     doc.stt = i;
                                        //     i = i + 1;
                                        //     doc.last_month = gridInterger(doc.last_month)
                                        //     doc.this_month = gridInterger(doc.this_month)
                                        //     return doc;
                                        // })
                                        return response;
                                    },
                                    }
                                });

                                var grid = this.grid = $("#grid_" + doc.group_code).kendoGrid({
                                    dataSource: dataSource,
                                    toolbar: ["excel"],
                                    excel: {
                                        allPages: true,
                                        fileName: doc.group_name + '.xlsx'
                                    },
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
                                    columns: columns,
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

                                /*
                                * Right Click Menu
                                */
                                var menu = $("#action-menu");
                                if(!menu.length) return;

                                $("html").on("click", function() {menu.hide()});

                                $(document).on("click", "#grid tr[role=row] a.btn-action", function(e){
                                    let btna = $(e.target);
                                    let row = $(e.target).closest("tr");
                                    e.pageX -= 20;
                                    showMenu(e, row, btna);
                                });

                                function showMenu(e, that,btna) {
                                    //hide menu if already shown
                                    menu.hide();

                                    //Get id value of document
                                    var uid = $(that).data('uid');
                                    var fltnumber = btna.data('flt');
                                    var date = btna.data('date');
                                    if(uid)
                                    {
                                        menu.find("a").data('uid',uid);
                                        menu.find("a").data('fltnumber',fltnumber);
                                        menu.find("a").data('date',date);
                                        menu.find("a").data('dpt',btna.data('dpt'));
                                        menu.find("a").data('arv',btna.data('arv'));
                                        //get x and y values of the click event
                                        var pageX = e.pageX;
                                        var pageY = e.pageY;

                                        //position menu div near mouse cliked area
                                        menu.css({top: pageY , left: pageX});

                                        var mwidth = menu.width();
                                        var mheight = menu.height();
                                        var screenWidth = $(window).width();
                                        var screenHeight = $(window).height();

                                        //if window is scrolled
                                        var scrTop = $(window).scrollTop();

                                        //if the menu is close to right edge of the window
                                        if(pageX+mwidth > screenWidth){
                                        menu.css({left:pageX-mwidth});
                                        }
                                        //if the menu is close to bottom edge of the window
                                        if(pageY+mheight > screenHeight+scrTop){
                                        menu.css({top:pageY-mheight});
                                        }
                                        //finally show the menu
                                        menu.show();
                                    }
                                }
                            }
                            else {
                                setTimeout(this.init, 100);
                            }
                        })
                    },
                    error: errorDataSource
                });
                var dataSource_total = this.dataSource_total = new kendo.data.DataSource({
                    serverPaging: true,
                    serverFiltering: true,
                    serverSorting: true,
                    sort: [{field: 'int_rate', dir: 'asc'}],
                    filter: [
                        {
                            field: 'year',
                            operator: 'eq',
                            value: date.getFullYear()
                        },
                    ],
                    pageSize: 10,
                    transport: {
                        read: ENV.reportApi + "loan/monthly_delinquent_occurence_transaction/read_total",
                        parameterMap: parameterMap
                    },
                    schema: {
                    data: "data",
                    total: "total",
                    parse: function (response) {
                        // i = 1;
                        // response.data.map(function(doc) {
                        //     doc.date = new Date().toLocaleDateString();
                        //     doc.stt = i;
                        //     i = i + 1;
                        //     doc.last_month = gridInterger(doc.last_month)
                        //     doc.this_month = gridInterger(doc.this_month)
                        //     return doc;
                        // })
                        return response;
                    },
                    }
                });

                var grid = this.grid_total = $("#total-grid").kendoGrid({
                    dataSource: dataSource_total,
                    toolbar: ["excel"],
                    excel: {
                        allPages: true,
                        fileName: 'JIVF  TOTAL.xlsx'
                    },
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
                    columns: [{
                        field: 'int_rate_name',
                        title: 'JIVF  TOTAL',
                        width: 200
                    }, {
                        title: '1/' + date.getFullYear(),
                        columns: [{
                            title: 'Total',
                            columns: [{
                                field: 'total_w_org_1_' + date.getFullYear(),
                                title: 'Total W-ORG',
                                width: 200
                            }, {
                                field: 'total_acc_count_1_' + date.getFullYear(),
                                title: 'No.of Account',
                                width: 200
                            }]
                        }, {
                            title: 'Group 2',
                            columns: [{
                                field: 'group_2_w_org_1_' + date.getFullYear(),
                                title: 'Overdue W-ORG',
                                width: 200
                            }, {
                                field: 'group_2_acc_count_1_' + date.getFullYear(),
                                title: 'No.of Account',
                                width: 200
                            }, {
                                field: 'group_2_overdue_ratio_1_' + date.getFullYear(),
                                title: 'Overdue ratio',
                                width: 200
                            }]
                        }, {
                            title: 'Group 3 and over',
                            columns: [{
                                field: 'group_3_over_w_org_1_' + date.getFullYear(),
                                title: 'Overdue W-ORG',
                                width: 200
                            }, {
                                field: 'group_3_over_acc_count_1_' + date.getFullYear(),
                                title: 'No.of Account',
                                width: 200
                            }, {
                                field: 'group_3_over_overdue_ratio_1_' + date.getFullYear(),
                                title: 'Overdue ratio',
                                width: 200
                            }]
                        }]
                    }, {
                        title: '2/' + date.getFullYear(),
                        columns: [{
                            title: 'Total',
                            columns: [{
                                field: 'total_w_org_2_' + date.getFullYear(),
                                title: 'Total W-ORG',
                                width: 200
                            }, {
                                field: 'total_acc_count_2_' + date.getFullYear(),
                                title: 'No.of Account',
                                width: 200
                            }]
                        }, {
                            title: 'Group 2',
                            columns: [{
                                field: 'group_2_w_org_2_' + date.getFullYear(),
                                title: 'Overdue W-ORG',
                                width: 200
                            }, {
                                field: 'group_2_acc_count_2_' + date.getFullYear(),
                                title: 'No.of Account',
                                width: 200
                            }, {
                                field: 'group_2_overdue_ratio_2_' + date.getFullYear(),
                                title: 'Overdue ratio',
                                width: 200
                            }]
                        }, {
                            title: 'Group 3 and over',
                            columns: [{
                                field: 'group_3_over_w_org_2_' + date.getFullYear(),
                                title: 'Overdue W-ORG',
                                width: 200
                            }, {
                                field: 'group_3_over_acc_count_2_' + date.getFullYear(),
                                title: 'No.of Account',
                                width: 200
                            }, {
                                field: 'group_3_over_overdue_ratio_2_' + date.getFullYear(),
                                title: 'Overdue ratio',
                                width: 200
                            }]
                        }]
                    }, {
                        title: '3/' + date.getFullYear(),
                        columns: [{
                            title: 'Total',
                            columns: [{
                                field: 'total_w_org_3_' + date.getFullYear(),
                                title: 'Total W-ORG',
                                width: 200
                            }, {
                                field: 'total_acc_count_3_' + date.getFullYear(),
                                title: 'No.of Account',
                                width: 200
                            }]
                        }, {
                            title: 'Group 2',
                            columns: [{
                                field: 'group_2_w_org_3_' + date.getFullYear(),
                                title: 'Overdue W-ORG',
                                width: 200
                            }, {
                                field: 'group_2_acc_count_3_' + date.getFullYear(),
                                title: 'No.of Account',
                                width: 200
                            }, {
                                field: 'group_2_overdue_ratio_3_' + date.getFullYear(),
                                title: 'Overdue ratio',
                                width: 200
                            }]
                        }, {
                            title: 'Group 3 and over',
                            columns: [{
                                field: 'group_3_over_w_org_3_' + date.getFullYear(),
                                title: 'Overdue W-ORG',
                                width: 200
                            }, {
                                field: 'group_3_over_acc_count_3_' + date.getFullYear(),
                                title: 'No.of Account',
                                width: 200
                            }, {
                                field: 'group_3_over_overdue_ratio_3_' + date.getFullYear(),
                                title: 'Overdue ratio',
                                width: 200
                            }]
                        }]
                    }, {
                        title: '4/' + date.getFullYear(),
                        columns: [{
                            title: 'Total',
                            columns: [{
                                field: 'total_w_org_4_' + date.getFullYear(),
                                title: 'Total W-ORG',
                                width: 200
                            }, {
                                field: 'total_acc_count_4_' + date.getFullYear(),
                                title: 'No.of Account',
                                width: 200
                            }]
                        }, {
                            title: 'Group 2',
                            columns: [{
                                field: 'group_2_w_org_4_' + date.getFullYear(),
                                title: 'Overdue W-ORG',
                                width: 200
                            }, {
                                field: 'group_2_acc_count_4_' + date.getFullYear(),
                                title: 'No.of Account',
                                width: 200
                            }, {
                                field: 'group_2_overdue_ratio_4_' + date.getFullYear(),
                                title: 'Overdue ratio',
                                width: 200
                            }]
                        }, {
                            title: 'Group 3 and over',
                            columns: [{
                                field: 'group_3_over_w_org_4_' + date.getFullYear(),
                                title: 'Overdue W-ORG',
                                width: 200
                            }, {
                                field: 'group_3_over_acc_count_4_' + date.getFullYear(),
                                title: 'No.of Account',
                                width: 200
                            }, {
                                field: 'group_3_over_overdue_ratio_4_' + date.getFullYear(),
                                title: 'Overdue ratio',
                                width: 200
                            }]
                        }]
                    }, {
                        title: '5/' + date.getFullYear(),
                        columns: [{
                            title: 'Total',
                            columns: [{
                                field: 'total_w_org_5_' + date.getFullYear(),
                                title: 'Total W-ORG',
                                width: 200
                            }, {
                                field: 'total_acc_count_5_' + date.getFullYear(),
                                title: 'No.of Account',
                                width: 200
                            }]
                        }, {
                            title: 'Group 2',
                            columns: [{
                                field: 'group_2_w_org_5_' + date.getFullYear(),
                                title: 'Overdue W-ORG',
                                width: 200
                            }, {
                                field: 'group_2_acc_count_5_' + date.getFullYear(),
                                title: 'No.of Account',
                                width: 200
                            }, {
                                field: 'group_2_overdue_ratio_5_' + date.getFullYear(),
                                title: 'Overdue ratio',
                                width: 200
                            }]
                        }, {
                            title: 'Group 3 and over',
                            columns: [{
                                field: 'group_3_over_w_org_5_' + date.getFullYear(),
                                title: 'Overdue W-ORG',
                                width: 200
                            }, {
                                field: 'group_3_over_acc_count_5_' + date.getFullYear(),
                                title: 'No.of Account',
                                width: 200
                            }, {
                                field: 'group_3_over_overdue_ratio_5_' + date.getFullYear(),
                                title: 'Overdue ratio',
                                width: 200
                            }]
                        }]
                    }, {
                        title: '6/' + date.getFullYear(),
                        columns: [{
                            title: 'Total',
                            columns: [{
                                field: 'total_w_org_6_' + date.getFullYear(),
                                title: 'Total W-ORG',
                                width: 200
                            }, {
                                field: 'total_acc_count_6_' + date.getFullYear(),
                                title: 'No.of Account',
                                width: 200
                            }]
                        }, {
                            title: 'Group 2',
                            columns: [{
                                field: 'group_2_w_org_6_' + date.getFullYear(),
                                title: 'Overdue W-ORG',
                                width: 200
                            }, {
                                field: 'group_2_acc_count_6_' + date.getFullYear(),
                                title: 'No.of Account',
                                width: 200
                            }, {
                                field: 'group_2_overdue_ratio_6_' + date.getFullYear(),
                                title: 'Overdue ratio',
                                width: 200
                            }]
                        }, {
                            title: 'Group 3 and over',
                            columns: [{
                                field: 'group_3_over_w_org_6_' + date.getFullYear(),
                                title: 'Overdue W-ORG',
                                width: 200
                            }, {
                                field: 'group_3_over_acc_count_6_' + date.getFullYear(),
                                title: 'No.of Account',
                                width: 200
                            }, {
                                field: 'group_3_over_overdue_ratio_6_' + date.getFullYear(),
                                title: 'Overdue ratio',
                                width: 200
                            }]
                        }]
                    }, {
                        title: '7/' + date.getFullYear(),
                        columns: [{
                            title: 'Total',
                            columns: [{
                                field: 'total_w_org_7_' + date.getFullYear(),
                                title: 'Total W-ORG',
                                width: 200
                            }, {
                                field: 'total_acc_count_7_' + date.getFullYear(),
                                title: 'No.of Account',
                                width: 200
                            }]
                        }, {
                            title: 'Group 2',
                            columns: [{
                                field: 'group_2_w_org_7_' + date.getFullYear(),
                                title: 'Overdue W-ORG',
                                width: 200
                            }, {
                                field: 'group_2_acc_count_7_' + date.getFullYear(),
                                title: 'No.of Account',
                                width: 200
                            }, {
                                field: 'group_2_overdue_ratio_7_' + date.getFullYear(),
                                title: 'Overdue ratio',
                                width: 200
                            }]
                        }, {
                            title: 'Group 3 and over',
                            columns: [{
                                field: 'group_3_over_w_org_7_' + date.getFullYear(),
                                title: 'Overdue W-ORG',
                                width: 200
                            }, {
                                field: 'group_3_over_acc_count_7_' + date.getFullYear(),
                                title: 'No.of Account',
                                width: 200
                            }, {
                                field: 'group_3_over_overdue_ratio_7_' + date.getFullYear(),
                                title: 'Overdue ratio',
                                width: 200
                            }]
                        }]
                    }, {
                        title: '8/' + date.getFullYear(),
                        columns: [{
                            title: 'Total',
                            columns: [{
                                field: 'total_w_org_8_' + date.getFullYear(),
                                title: 'Total W-ORG',
                                width: 200
                            }, {
                                field: 'total_acc_count_8_' + date.getFullYear(),
                                title: 'No.of Account',
                                width: 200
                            }]
                        }, {
                            title: 'Group 2',
                            columns: [{
                                field: 'group_2_w_org_8_' + date.getFullYear(),
                                title: 'Overdue W-ORG',
                                width: 200
                            }, {
                                field: 'group_2_acc_count_8_' + date.getFullYear(),
                                title: 'No.of Account',
                                width: 200
                            }, {
                                field: 'group_2_overdue_ratio_8_' + date.getFullYear(),
                                title: 'Overdue ratio',
                                width: 200
                            }]
                        }, {
                            title: 'Group 3 and over',
                            columns: [{
                                field: 'group_3_over_w_org_8_' + date.getFullYear(),
                                title: 'Overdue W-ORG',
                                width: 200
                            }, {
                                field: 'group_3_over_acc_count_8_' + date.getFullYear(),
                                title: 'No.of Account',
                                width: 200
                            }, {
                                field: 'group_3_over_overdue_ratio_8_' + date.getFullYear(),
                                title: 'Overdue ratio',
                                width: 200
                            }]
                        }]
                    }, {
                        title: '9/' + date.getFullYear(),
                        columns: [{
                            title: 'Total',
                            columns: [{
                                field: 'total_w_org_9_' + date.getFullYear(),
                                title: 'Total W-ORG',
                                width: 200
                            }, {
                                field: 'total_acc_count_9_' + date.getFullYear(),
                                title: 'No.of Account',
                                width: 200
                            }]
                        }, {
                            title: 'Group 2',
                            columns: [{
                                field: 'group_2_w_org_9_' + date.getFullYear(),
                                title: 'Overdue W-ORG',
                                width: 200
                            }, {
                                field: 'group_2_acc_count_9_' + date.getFullYear(),
                                title: 'No.of Account',
                                width: 200
                            }, {
                                field: 'group_2_overdue_ratio_9_' + date.getFullYear(),
                                title: 'Overdue ratio',
                                width: 200
                            }]
                        }, {
                            title: 'Group 3 and over',
                            columns: [{
                                field: 'group_3_over_w_org_9_' + date.getFullYear(),
                                title: 'Overdue W-ORG',
                                width: 200
                            }, {
                                field: 'group_3_over_acc_count_9_' + date.getFullYear(),
                                title: 'No.of Account',
                                width: 200
                            }, {
                                field: 'group_3_over_overdue_ratio_9_' + date.getFullYear(),
                                title: 'Overdue ratio',
                                width: 200
                            }]
                        }]
                    }, {
                        title: '10/' + date.getFullYear(),
                        columns: [{
                            title: 'Total',
                            columns: [{
                                field: 'total_w_org_10_' + date.getFullYear(),
                                title: 'Total W-ORG',
                                width: 200
                            }, {
                                field: 'total_acc_count_10_' + date.getFullYear(),
                                title: 'No.of Account',
                                width: 200
                            }]
                        }, {
                            title: 'Group 2',
                            columns: [{
                                field: 'group_2_w_org_10_' + date.getFullYear(),
                                title: 'Overdue W-ORG',
                                width: 200
                            }, {
                                field: 'group_2_acc_count_10_' + date.getFullYear(),
                                title: 'No.of Account',
                                width: 200
                            }, {
                                field: 'group_2_overdue_ratio_10_' + date.getFullYear(),
                                title: 'Overdue ratio',
                                width: 200
                            }]
                        }, {
                            title: 'Group 3 and over',
                            columns: [{
                                field: 'group_3_over_w_org_10_' + date.getFullYear(),
                                title: 'Overdue W-ORG',
                                width: 200
                            }, {
                                field: 'group_3_over_acc_count_10_' + date.getFullYear(),
                                title: 'No.of Account',
                                width: 200
                            }, {
                                field: 'group_3_over_overdue_ratio_10_' + date.getFullYear(),
                                title: 'Overdue ratio',
                                width: 200
                            }]
                        }]
                    }, {
                        title: '11/' + date.getFullYear(),
                        columns: [{
                            title: 'Total',
                            columns: [{
                                field: 'total_w_org_11_' + date.getFullYear(),
                                title: 'Total W-ORG',
                                width: 200
                            }, {
                                field: 'total_acc_count_11_' + date.getFullYear(),
                                title: 'No.of Account',
                                width: 200
                            }]
                        }, {
                            title: 'Group 2',
                            columns: [{
                                field: 'group_2_w_org_11_' + date.getFullYear(),
                                title: 'Overdue W-ORG',
                                width: 200
                            }, {
                                field: 'group_2_acc_count_11_' + date.getFullYear(),
                                title: 'No.of Account',
                                width: 200
                            }, {
                                field: 'group_2_overdue_ratio_11_' + date.getFullYear(),
                                title: 'Overdue ratio',
                                width: 200
                            }]
                        }, {
                            title: 'Group 3 and over',
                            columns: [{
                                field: 'group_3_over_w_org_11_' + date.getFullYear(),
                                title: 'Overdue W-ORG',
                                width: 200
                            }, {
                                field: 'group_3_over_acc_count_11_' + date.getFullYear(),
                                title: 'No.of Account',
                                width: 200
                            }, {
                                field: 'group_3_over_overdue_ratio_11_' + date.getFullYear(),
                                title: 'Overdue ratio',
                                width: 200
                            }]
                        }]
                    }, {
                        title: '12/' + date.getFullYear(),
                        columns: [{
                            title: 'Total',
                            columns: [{
                                field: 'total_w_org_12_' + date.getFullYear(),
                                title: 'Total W-ORG',
                                width: 200
                            }, {
                                field: 'total_acc_count_12_' + date.getFullYear(),
                                title: 'No.of Account',
                                width: 200
                            }]
                        }, {
                            title: 'Group 2',
                            columns: [{
                                field: 'group_2_w_org_12_' + date.getFullYear(),
                                title: 'Overdue W-ORG',
                                width: 200
                            }, {
                                field: 'group_2_acc_count_12_' + date.getFullYear(),
                                title: 'No.of Account',
                                width: 200
                            }, {
                                field: 'group_2_overdue_ratio_12_' + date.getFullYear(),
                                title: 'Overdue ratio',
                                width: 200
                            }]
                        }, {
                            title: 'Group 3 and over',
                            columns: [{
                                field: 'group_3_over_w_org_12_' + date.getFullYear(),
                                title: 'Overdue W-ORG',
                                width: 200
                            }, {
                                field: 'group_3_over_acc_count_12_' + date.getFullYear(),
                                title: 'No.of Account',
                                width: 200
                            }, {
                                field: 'group_3_over_overdue_ratio_12_' + date.getFullYear(),
                                title: 'Overdue ratio',
                                width: 200
                            }]
                        }]
                    }, ],
                    noRecords: {
                        template: `<h2 class='text-danger'>${KENDO.noRecords}</h2>`
                    }
                }).data("kendoGrid");
            }
        }
    }();
    window.onload = function() {
        Table.init();
        var dateRange = 30;
        var nowDate = new Date();
        var date =  new Date(),
            timeZoneOffset = date.getTimezoneOffset() * kendo.date.MS_PER_MINUTE;
            date.setHours(- timeZoneOffset / kendo.date.MS_PER_HOUR, 0, 0 ,0);

        // var fromDate = new Date(date.getTime() + timeZoneOffset - (dateRange - 1) * 86400000);
        var observable = kendo.observable({
            trueVar: true,
            loading: false,
            visibleReport: false,
            visibleNoData: false, 
            filterField: "",
            cif:"",
            loanContract: "",
            nationalID: "",
            onChangeDate: function() {
                var date =  this.fromDateTime;
                date.setDate(1);
                var fromDate = date.getTime() / 1000;
                var lastDateOfMonth = kendo.date.lastDayOfMonth(this.fromDateTime);
                var toDate = lastDateOfMonth.getTime() / 1000;
                filter = [{
                        field: 'created_at',
                        operator: 'gte',
                        value: fromDate
                    }, {
                        field: 'created_at',
                        operator: 'lte',
                        value: toDate
                    }];
                Table.dataSource_total.filter(filter);
                Table.dataSource_detail.filter(filter);
            },
        });
        kendo.bind($(".mvvm"), observable);
    };

    
</script>
<script>
    function saveAsExcel() {
        $.ajax({
            url: ENV.reportApi + "loan/Monthly_delinquent_occurence_transaction/exportExcel",
            type: 'POST',
            dataType: 'json',
            timeout: 30000
        })
        .done(function(response) {
            if (response.status == 1) {
                // console.log(ENV)
                window.location = ENV.baseUrl + response.data
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

<style>
.k-grid  .k-grid-header  .k-header  .k-link {
    height: auto;
}
  
.k-grid  .k-grid-header  .k-header {
    white-space: normal;
}
</style>

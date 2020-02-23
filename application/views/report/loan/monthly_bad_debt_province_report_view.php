<div id="page-content">
<!-- Table Styles Header -->
<ul class="breadcrumb breadcrumb-top">
    <li>@Report@</li>
    <li>Monthly bad debt report</li>
    <li class="pull-right none-breakcrumb" id="top-row">
        <div class="btn-group btn-group-sm">
            <a role="button" class="btn btn-sm" onclick="Table.grid.saveAsExcel()"><i class="fa fa-file-excel-o"></i> <b>@Export@</b></a>
        </div>
    </li>
</ul>
<!-- END Table Styles Header -->
<div class="container-fluid mvvm" style="padding-top: 20px; padding-bottom: 10px">
    <div class="row form-horizontal">
        <div class="form-group col-sm-3">
            <label class="control-label col-xs-3">@Date@</label>
            <div class="col-xs-8">
                <input id="start-date" data-role="datepicker" data-format="MM/yyyy" data-start="year" data-depth="year" name="fromDateTime" data-bind="value: fromDateTime, events: {change: onChangeDate}">
            </div>
        </div>
        <!-- <div class="form-group col-sm-4">
            <label class="control-label col-xs-4">@To date@</label>
            <div class="col-xs-8">
                <input id="end-date" data-role="datepicker" data-format="dd/MM/yyyy H:mm:ss" name="toDateTime" data-bind="value: toDateTime, events: {change: endDate}">
            </div>
        </div>
        <div class="form-group col-sm-4 text-center">
            <button class="k-button" data-bind="click: search">@Search@</button>
        </div> -->
    </div>
    <!-- <div class="row form-horizontal">
        <div class="col-sm-3">
            <label class="control-label col-xs-2" style="line-height: 2;">CIF</label>
            <div class="col-xs-10">
                <input class="k-textbox" style="width: 100%" id="cif_id" name="cif" data-bind="value: cif">
            </div>
        </div>
        <div class="col-sm-5" style="padding-left: 21px">
            <label class="control-label col-xs-4" style="line-height: 2;">LOAN CONTRACT</label>
            <div class="col-xs-7">
                <input class="k-textbox" style="width: 95%" id="loan_id" name="loanContract" data-bind="value: loanContract}">
            </div>
        </div>
        <div class="col-sm-4">
            <label class="control-label col-xs-4" style="line-height: 2;">National ID</label>
            <div class="col-xs-8">
                <input class="k-textbox" style="width: 100%" id="national" name="nationalID" data-bind="value: nationalID">
            </div>
        </div>
        <div class="col-sm-12 text-center">
            <button style="margin-top: 10px; margin-bottom: 10px" data-role="button" data-bind="click: search">@Search@</button>
        </div>
    </div> -->
    <h3 class="col-sm-12 text-center" style="margin-bottom: 20px;color: #27ae60">MONTHLY BAD DEBT AT PROVINCES</h3>
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
                var date =  new Date();
                date.setHours(0, 0 ,0, 0);
                date.setDate(1);
                var fromDate = date.getTime() / 1000;
                var lastDateOfMonth = kendo.date.lastDayOfMonth(new Date());
                var toDate = lastDateOfMonth.getTime() / 1000;
                if(typeof fromDate != 'undefined' && typeof toDate != 'undefined') {
                    var dataSource = this.dataSource = new kendo.data.DataSource({
                        serverPaging: true,
                        serverFiltering: true,
                        serverSorting: true,
                        filter: [{
                            field: 'created_at',
                            operator: 'gte',
                            value: fromDate
                        }, {
                            field: 'created_at',
                            operator: 'lte',
                            value: toDate
                        }],
                        sort: [{field: 'province_name', dir: 'asc'}],
                        pageSize: 10,
                        transport: {
                            read: ENV.reportApi + "loan/Monthly_bad_debt_province_report",
                            parameterMap: parameterMap
                        },
                        schema: {
                        data: "data",
                        total: "total",
                        model: {
                            fields: {
                                total_acc: {
                                    type: 'number'
                                },
                                total_amt: {
                                    type: 'number'
                                },
                                total_acc_no_sibs: {
                                    type: 'number'
                                },
                                total_amt_sibs: {
                                    type: 'number'
                                },
                                total_acc_no_card: {
                                    type: 'number'
                                },
                                total_amt_card: {
                                    type: 'number'
                                },
                                release_acc_no_sibs: {
                                    type: 'number'
                                },
                                release_amt_sibs: {
                                    type: 'number'
                                },
                                release_acc_no_card: {
                                    type: 'number'
                                },
                                release_amt_card: {
                                    type: 'number'
                                },
                                group_two_acc_sibs: {
                                    type: 'number'
                                },
                                group_two_w_org_sibs: {
                                    type: 'number'
                                },
                                group_two_acc_card: {
                                    type: 'number'
                                },
                                group_two_w_org_card: {
                                    type: 'number'
                                },
                                group_two_plus_acc_sibs: {
                                    type: 'number'
                                },
                                group_two_plus_w_org_sibs: {
                                    type: 'number'
                                },
                                group_two_plus_acc_card: {
                                    type: 'number'
                                },
                                group_two_plus_w_org_card: {
                                    type: 'number'
                                },
                                bad_debt_ratio_sibs: {
                                    type: 'number'
                                },
                                bad_debt_ratio_card: {
                                    type: 'number'
                                },
                                bad_debt_ratio: {
                                    type: 'number'
                                },
                            }
                        },
                        parse: function (response) {
                            i = 1;
                            response.data.map(function(doc) {
                                // doc.date = new Date().toLocaleDateString();
                                // doc.stt = i;
                                // i = i + 1;
                                // doc.total_acc = gridInterger(doc.total_acc)
                                // doc.total_amt = gridInterger(doc.total_amt)
                                // doc.total_acc_no_sibs = gridInterger(doc.total_acc_no_sibs)
                                // doc.total_amt_sibs = gridInterger(doc.total_amt_sibs)
                                // doc.total_acc_no_card = gridInterger(doc.total_acc_no_card)
                                // doc.total_amt_card = gridInterger(doc.total_amt_card)
                                // doc.release_acc_no_sibs = gridInterger(doc.release_acc_no_sibs)
                                // doc.release_amt_sibs = gridInterger(doc.release_amt_sibs)
                                // doc.release_acc_no_card = gridInterger(doc.release_acc_no_card)
                                // doc.release_amt_card = gridInterger(doc.release_amt_card)
                                // doc.group_two_acc_sibs = gridInterger(doc.group_two_acc_sibs)
                                // doc.group_two_w_org_sibs = gridInterger(doc.group_two_w_org_sibs)
                                // doc.group_two_acc_card = gridInterger(doc.group_two_acc_card)
                                // doc.group_two_w_org_card = gridInterger(doc.group_two_w_org_card)
                                // doc.group_two_plus_acc_sibs = gridInterger(doc.group_two_plus_acc_sibs)
                                // doc.group_two_plus_w_org_sibs = gridInterger(doc.group_two_plus_w_org_sibs)
                                // doc.group_two_plus_acc_card = gridInterger(doc.group_two_plus_acc_card)
                                // doc.group_two_plus_w_org_card = gridInterger(doc.group_two_plus_w_org_card)
                                // doc.bad_debt_ratio_sibs = gridInterger(doc.bad_debt_ratio_sibs)

                                // doc.bad_debt_ratio_card = gridInterger(doc.bad_debt_ratio_card)
                                // doc.bad_debt_ratio = gridInterger(doc.bad_debt_ratio)
                                return doc;
                            })
                                return response;
                        },
                        }
                    });
                    var d = new Date();
                    var date = d.getDate();
                    var month = d.getMonth() + 1; // Since getMonth() returns month from 0-11 not 1-12
                    var year = d.getFullYear();
                    var dateStr = date + "-" + month + "-" + year;
                    var grid = this.grid = $("#grid").kendoGrid({
                        dataSource: dataSource,
                        excel: {
                            allPages: true,
                            fileName: "Monthly bad debt at provinces "+dateStr+".xlsx", 
                            filterable: true
                            },
                        excelExport: function(e) {   
                        var sheet = e.workbook.sheets[0];
                        var row = sheet.rows[0];
                        for (var rowIndex = 0; rowIndex < sheet.rows.length; rowIndex++) {
                          var row = sheet.rows[rowIndex];   
                            
                          for (var cellIndex = 0; cellIndex < row.cells.length; cellIndex ++) {
                                row.cells[cellIndex].borderRight = "3"
                                row.cells[cellIndex].borderBottom = "3"
                                row.cells[cellIndex].borderBottom = "3"
                                row.cells[cellIndex].borderTop = "3"
                            if (rowIndex ==0){
                                row.cells[cellIndex].bold = true
                            }
                            if (rowIndex !=0 && rowIndex !=1 && cellIndex!= 0 && cellIndex<row.cells.length-3){
                                row.cells[cellIndex].format = "[Black]#,##0_);[Red]0.0);0"
                              }
                            if (cellIndex >= row.cells.length-3 && rowIndex!=1 &&rowIndex !=0) {
                                row.cells[cellIndex].format ="#0.00%;" 
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
                        columns: [
                            {
                                field: "province_name",
                                title: "PROVINCE",
                                width: 140
                            },{
                                title: "",
                                columns: [{
                                    title: 'No of accounts',
                                    field: 'total_acc',
                                    width: 140
                                }, {
                                    title: 'w_org (VND)',
                                    field: 'total_amt',
                                    width: 200,
                                }]
                            },{
                                title: "TOTAL",
                                columns: [{
                                    title: 'No of accounts SIBS',
                                    field: 'total_acc_no_sibs',
                                    width: 140
                                }, {
                                    title: 'w_org (VND) SIBS',
                                    field: 'total_amt_sibs',
                                    width: 200
                                }, {
                                    title: 'No of accounts CARD',
                                    field: 'total_acc_no_card',
                                    width: 140
                                }, {
                                    title: 'w_org (VND) CARD',
                                    field: 'total_amt_card',
                                    width: 200
                                }]
                            },{
                                title: "Release",
                                columns: [{
                                    title: 'No.accts released SIBS',
                                    field: 'release_acc_no_sibs',
                                    width: 140
                                }, {
                                    title: 'Release (Amount) SIBS',
                                    field: 'release_amt_sibs',
                                    width: 200
                                }, {
                                    title: 'No.accts released CARD',
                                    field: 'release_acc_no_card',
                                    width: 140
                                }, {
                                    title: 'Release (Amount) CARD',
                                    field: 'release_amt_card',
                                    width: 200
                                }]
                            },{
                                title: "GROUP 2",
                                columns: [{
                                    title: 'No of accounts SIBS',
                                    field: 'group_two_acc_sibs',
                                    width: 140
                                }, {
                                    title: 'w_org (VND) SIBS',
                                    field: 'group_two_w_org_sibs',
                                    width: 200
                                }, {
                                    title: 'No of accounts CARD',
                                    field: 'group_two_acc_card',
                                    width: 140
                                }, {
                                    title: 'w_org (VND) CARD',
                                    field: 'group_two_w_org_card',
                                    width: 200
                                }]
                            },{
                                title: "GROUP 3 OVER",
                                columns: [{
                                    title: 'No of accounts SIBS',
                                    field: 'group_two_plus_acc_sibs',
                                    width: 140
                                }, {
                                    title: 'w_org (VND) SIBS',
                                    field: 'group_two_plus_w_org_sibs',
                                    width: 200
                                }, {
                                    title: 'No of accounts CARD',
                                    field: 'group_two_plus_acc_card',
                                    width: 140
                                }, {
                                    title: 'w_org (VND) CARD',
                                    field: 'group_two_plus_w_org_card',
                                    width: 200
                                }, {
                                    title: 'Bad debt ratio SIBS',
                                    field: 'bad_debt_ratio_sibs',
                                    width: 140,
                                    format: "{0:p}"
                                }, {
                                    title: 'Bad debt ratio CARD',
                                    field: 'bad_debt_ratio_card',
                                    width: 140,
                                    format: "{0:p}"
                                }, {
                                    title: 'Bad debt ratio',
                                    field: 'bad_debt_ratio',
                                    width: 140,
                                    format: "{0:p}"
                                }]
                            }],
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
            }
        }
    }();
    window.onload = function() {
        Table.init();
        var dateRange = 30;
        var nowDate = new Date();
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
                Table.dataSource.filter(filter);
            }
        })
        kendo.bind($(".mvvm"), observable);
    };

    
</script>
<script>
    function saveAsExcel() {
        Table.grid.saveAsExcel();
        // $.ajax({
        //     url: ENV.reportApi + "loan/master_data_report/downloadExcel",
        //     type: 'POST',
        //     dataType: 'json',
        //     timeout: 30000
        // })
        // .done(function(response) {
        //     if (response.status == 1) {
        //     window.location = response.data
        //     }
        // })
        // .fail(function() {
        //     console.log("error");
        // });

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

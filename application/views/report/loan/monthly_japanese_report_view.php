<div id="page-content">
<!-- Table Styles Header -->
<ul class="breadcrumb breadcrumb-top">
    <li>@Report@</li>
    <li>Monthly Japanese report</li>
    <!-- <li class="pull-right none-breakcrumb" id="top-row">
        <div class="btn-group btn-group-sm">
            <a role="button" class="btn btn-sm" onclick="Table.grid.saveAsExcel()"><i class="fa fa-file-excel-o"></i> <b>@Export@</b></a>
        </div>
    </li> -->
</ul>
<!-- END Table Styles Header -->
<div class="container-fluid mvvm" style="padding-top: 20px; padding-bottom: 10px">
    <div class="row form-horizontal">
        <div class="form-group col-sm-3">
            <label class="control-label col-xs-3">@Date@</label>
            <div class="col-xs-8">
                <input id="start-date" data-role="datepicker" data-format="dd/MM/yyyy" name="fromDateTime" data-bind="value: fromDateTime, events: {change: startDate}" disabled="">
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
    <h3 class="col-sm-12 text-center" style="margin-bottom: 20px;color: #27ae60">MONTHLY JAPANESE REPORT</h3>
    <div class="row chart-page"  style="background-color: white">
        <div class="col-sm-12">
            <h3>TOTAL</h3>
        </div>
        <div class="col-sm-12">
            <div id="grid_total"></div>
        </div>
    </div>
    <div class="row chart-page"  style="background-color: white">
        <div class="col-sm-12">
            <h3>BY GROUP</h3>
        </div>
        <div class="col-sm-12">
            <div id="grid_detail"></div>
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
            grid_total: {},
            grid_detail: {},
            init: function() {
                var dataSource_total = this.dataSource = new kendo.data.DataSource({
                    serverPaging: true,
                    serverFiltering: true,
                    serverSorting: true,
                    sort: [{field: 'detail', dir: 'asc'}, {field: 'index', dir: 'asc'}, {field: 'product_code', dir: 'asc'}],
                    pageSize: 10,
                    transport: {
                    read: ENV.reportApi + "loan/monthly_japanese_report/read_total",
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
                            doc.last_month = gridInterger(doc.last_month)
                            doc.this_month = gridInterger(doc.this_month)
                            return doc;
                        })
                            return response;
                    },
                    }
                });

                var dataSource_detail = this.dataSource = new kendo.data.DataSource({
                    serverPaging: true,
                    serverFiltering: true,
                    serverSorting: true,
                    sort: [{field: 'index', dir: 'asc'}, {field: 'product_code', dir: 'asc'}],
                    pageSize: 10,
                    transport: {
                    read: ENV.reportApi + "loan/monthly_japanese_report/read_detail",
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
                            doc.this_month_acc = gridInterger(doc.this_month_acc);
                            doc.this_month_amt = gridInterger(doc.this_month_amt);
                            doc.last_month_acc = gridInterger(doc.last_month_acc);
                            doc.last_month_amt = gridInterger(doc.last_month_amt);
                            return doc;
                        })
                            return response;
                    },
                    }
                });

                var grid_total = this.grid = $("#grid_total").kendoGrid({
                    dataSource: dataSource_total,
                    toolbar: ["excel"],
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
                    columns: [{
                        field: 'detail_name',
                        title: ' '
                    }, {
                        field: 'product_name',
                        title: ' '
                    }, {
                        field: 'last_month',
                        title: 'Last month'
                    }, {
                        field: 'this_month',
                        title: 'This month'
                    }],
                    noRecords: {
                        template: `<h2 class='text-danger'>${KENDO.noRecords}</h2>`
                    }
                }).data("kendoGrid");

                var grid_detail = this.grid = $("#grid_detail").kendoGrid({
                    dataSource: dataSource_detail,
                    toolbar: ["excel"],
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
                    columns: [{
                        field: 'group_name',
                        title: ' '
                    }, {
                        field: 'product_name',
                        title: ' '
                    }, {
                        title: 'Last month',
                        columns: [{
                            field: 'last_month_acc',
                            title: ' '
                        }, {
                            field: 'last_month_amt',
                            title: ' '
                        }]
                    }, {
                        title: 'This month',
                        columns: [{
                            field: 'this_month_acc',
                            title: ' '
                        }, {
                            field: 'this_month_amt',
                            title: ' '
                        }]
                    }],
                    noRecords: {
                        template: `<h2 class='text-danger'>${KENDO.noRecords}</h2>`
                    }
                }).data("kendoGrid");

                grid_total.selectedKeyNames = function() {
                    var items = this.select(),
                        that = this,
                        checkedIds = [];
                    $.each(items, function(){
                        if(that.dataItem(this))
                            checkedIds.push(that.dataItem(this).uid);
                    })
                    return checkedIds;
                }

                grid_detail.selectedKeyNames = function() {
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
        var fromDate = new Date(date.getTime() + timeZoneOffset);
        var toDate = new Date(date.getTime() + timeZoneOffset + kendo.date.MS_PER_DAY -1)
        var observable = kendo.observable({
            trueVar: true,
            loading: false,
            visibleReport: false,
            visibleNoData: false,
        fromDateTime: fromDate,
        toDateTime: toDate,
        filterField: "",
        cif:"",
        loanContract: "",
        nationalID: "",
        fromDate: kendo.toString(fromDate, "dd/MM/yyyy H:mm"),
        toDate: kendo.toString(toDate, "dd/MM/yyyy H:mm"),

        search: function() {
            this.set("fromDate", kendo.toString(this.get("fromDateTime"), "dd/MM/yyyy H:mm"));
            this.set("toDate", kendo.toString(this.get("toDateTime"), "dd/MM/yyyy H:mm"));
            this.asyncSearch();
        },
            asyncSearch: async function() {
            var field = "created_at";
            var fromDateTime = new Date(this.fromDateTime.getTime() - timeZoneOffset).toISOString();
            var toDateTime = new Date(this.toDateTime.getTime() - timeZoneOffset).toISOString();
            var cif = this.cif;
            var loanContract = this.loanContract;
            var nationalID = this.nationalID;
            var field_1 = 'CUS_ID';
            var field_2 = 'account_number';
            var field_3 = 'LIC_NO';

            if (cif != '') {
                filter_1 = {field: field_1, operator: "eq", value: cif};
            }else{
                filter_1 = {field: field_1, operator: "neq", value: cif};
            }
            if (loanContract != '') {
                filter_2 = {field: field_2, operator: "eq", value: loanContract};
            }else{
                filter_2 = {field: field_2, operator: "neq", value: loanContract};
            }
            if (nationalID != '') {
                filter_3 = {field: field_3, operator: "eq", value: nationalID};
            }else{
                filter_3 = {field: field_3, operator: "neq", value: nationalID};
            }
            var filter = {
                logic: "and",
                filters: [
                    filter_1,filter_2,filter_3
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
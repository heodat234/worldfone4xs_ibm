<div id="page-content">
<!-- Table Styles Header -->
<ul class="breadcrumb breadcrumb-top">
    <li>@Report@</li>
    <li>Tendency of delinquent loan occurence</li>
    <li class="pull-right none-breakcrumb" id="top-row">
        <div class="btn-group btn-group-sm">
            <a role="button" class="btn btn-sm" onclick="saveAsExcel()"><i class="fa fa-file-excel-o"></i> <b>@Export@</b></a>
        </div>
    </li>
</ul>
<!-- END Table Styles Header -->
<div class="container-fluid mvvm" style="padding-top: 20px; padding-bottom: 10px">
    <div class="row form-horizontal">
        <div class="form-group col-sm-3">
            <label class="control-label col-xs-3">@Year@</label>
            <div class="col-xs-8">
                <input id="start-date" data-role="datepicker" data-format="yyyy" data-start="decade" data-depth="decade" name="fromDateTime" data-bind="value: fromDateTime, events: {change: onChangeDate}">
            </div>
        </div>
    </div>
    <h3 class="col-sm-12 text-center" style="margin-bottom: 20px;color: #27ae60">Tendency of delinquent loan occurence</h3>
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
                var dataSource = this.dataSource = new kendo.data.DataSource({
                    serverPaging: true,
                    serverFiltering: true,
                    serverSorting: true,
                    // sort: [{field: 'index', dir: 'asc'}, {field: 'product_code', dir: 'asc'}],
                    filter: [{
                        field: 'for_year',
                        operator: 'eq',
                        value: date.getFullYear().toString()
                    }, {
                        field: 'request_no',
                        operator: 'isnotnull'
                    }],
                    pageSize: 10,
                    transport: {
                    read: ENV.reportApi + "loan/Tendency_delinquent/read",
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
                            //     doc.this_month_acc = gridInterger(doc.this_month_acc);
                            //     doc.this_month_amt = gridInterger(doc.this_month_amt);
                            //     doc.last_month_acc = gridInterger(doc.last_month_acc);
                            //     doc.last_month_amt = gridInterger(doc.last_month_amt);
                            //     return doc;
                            // })
                            return response;
                        },
                    }
                });

                var grid = this.grid = $("#grid").kendoGrid({
                    dataSource: dataSource,
                    resizable: true,
                    pageable: true,
                    sortable: true,
                    scrollable: true,
                    columns: [{
                        field: 'for_month_name',
                        title: ' ',
                        width: 30,
                    }, {
                        field: 'debt_group',
                        title: 'GROUP',
                        width: 50
                    }, {
                        field: 'pay_day',
                        title: '支払日 </br>Pay  day',
                        width: 50,
                        encoded: false
                    }, {
                        field: 'prod_name',
                        title: ' ',
                        width: 50
                    }, {
                        field: 'request_no',
                        title: '当月請求件数 <br/>Request No',
                        width: 50,
                        encoded: false
                    }, {
                        field: 'group_2_tran_no',
                        title: 'Group 2 Transition number',
                        width: 50
                    }, {
                        field: 'group_b_trans_no',
                        title: 'Group B Transition number',
                        width: 50
                    }],
                    noRecords: {
                        template: `<h2 class='text-danger'>${KENDO.noRecords}</h2>`
                    }
                }).data("kendoGrid");

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
                    field: 'for_year',
                    operator: 'eq',
                    value: date.getFullYear().toString()
                }, {
                    field: 'request_no',
                    operator: 'isnotnull'
                }];
                Table.dataSource.filter(filter);
            },
        });
        kendo.bind($(".mvvm"), observable);
    };

    
</script>
<script>
    function saveAsExcel() {
        $.ajax({
            url: ENV.reportApi + "loan/tendency_delinquent/exportExcel",
            type: 'POST',
            dataType: 'json',
            // timeout: 30000
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

<div id="page-content">
<!-- Table Styles Header -->
<ul class="breadcrumb breadcrumb-top">
    <li>@Report@</li>
    <li>First-time payment delinquency incidence rate transition</li>
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
                <input class="k-textbox" style="width: 200%" id="cif_id" name="cif" data-bind="value: cif">
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
                <input class="k-textbox" style="width: 200%" id="national" name="nationalID" data-bind="value: nationalID">
            </div>
        </div>
        <div class="col-sm-12 text-center">
            <button style="margin-top: 10px; margin-bottom: 10px" data-role="button" data-bind="click: search">@Search@</button>
        </div>
    </div> -->
    <h3 class="col-sm-12 text-center" style="margin-bottom: 20px;color: #27ae60">First-time payment delinquency incidence rate transition</h3>
    <div class="col-sm-12 grid-data"></div>
    <div class="row chart-page"  style="background-color: white">
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
            grid_total: {},
            init: function() {
                var date =  new Date();
                var grids_columns = function getGridData() {
                    var tmp = null;
                    $.ajax({
                        async: false,
                        global: false,
                        url: ENV.reportApi + 'loan/Monthly_first_time_payment_delinquency/getColumns/' + date.getFullYear(),
                        type: 'GET',
                        contentType: 'application/json',
                        success: function (response) {
                            tmp = response;
                        },
                    });
                    return tmp;
                }();
                $.ajax({
                    url: ENV.reportApi + 'loan/Monthly_first_time_payment_delinquency/getListProductGroup',
                    type: 'POST',
                    success: function(response) {
                        response.map(async function(doc) {
                            var detailColumns = []
                            if(typeof grids_columns[doc.group_code] != 'undefined') {
                                grids_columns[doc.group_code].map(function(columnDetail) {
                                    var int_rate = '0' + columnDetail
                                    var field_name = columnDetail.replace('.', '');
                                    detailColumns.push({
                                        title: doc.group_name + ' の内 ' + parseFloat(int_rate).toFixed(5) + ' 条件',
                                        columns: [{
                                            field: 'a01_' + field_name,
                                            title: 'A01',
                                            width: 70
                                        }, {
                                            field: 'a02_' + field_name,
                                            title: 'A02',
                                            width: 70
                                        }, {
                                            field: 'a03_' + field_name,
                                            title: 'A03',
                                            width: 70
                                        }, {
                                            field: 'total_' + field_name,
                                            title: 'TOTAL',
                                            width: 70
                                        }]
                                    })
                                });
                            }
                            var columns = [{
                                field: 'month',
                                title: '',
                                width: 70
                            }, {
                                field: 'number',
                                title: '',
                                width: 70
                            }, {
                                field: 'cal_name',
                                title: '',
                                width: 200
                            }, {
                                title: doc.group_name + ' Total',
                                columns: [{
                                    field: 'a01_total',
                                    title: 'A01',
                                    width: 70
                                }, {
                                    field: 'a02_total',
                                    title: 'A02',
                                    width: 70
                                }, {
                                    field: 'a03_total',
                                    title: 'A03',
                                    width: 70
                                }, {
                                    field: 'total_total',
                                    title: 'TOTAL',
                                    width: 70
                                }]
                            }]
                            columns = columns.concat(detailColumns)
                            
                            $('.grid-data').append(`<div class="row chart-page" style="background-color: white">
                                                    <div class="col-sm-12">
                                                        <h3>${doc.group_name}</h3>
                                                    </div>
                                                    <div class="col-sm-12">
                                                        <div id="grid_${doc.group_code}"></div>
                                                    </div>
                                                </div>`);

                            var dataSource = this.dataSource = new kendo.data.DataSource({
                                serverPaging: true,
                                serverFiltering: true,
                                serverSorting: true,
                                sort: [{field: 'index', dir: 'asc'}],
                                filter: [
                                    {
                                        field: 'for_year',
                                        operator: 'eq',
                                        value: date.getFullYear().toString()
                                    }, {
                                        field: 'prod_group_code',
                                        operator: 'eq',
                                        value: doc.group_code
                                    }
                                ],
                                pageSize: 10,
                                transport: {
                                    read: ENV.reportApi + "loan/Monthly_first_time_payment_delinquency/read",
                                    parameterMap: parameterMap
                                },
                                schema: {
                                    data: "data",
                                    total: "total",
                                    parse: function (response) {
                                        // i = 1;
                                        var list_sum = ['first_payment', 'remaining_after_5_days', 'rate'];
                                        response.data.map(function(doc) {
                                            if(list_sum.includes(doc.cal_value)) {
                                                doc.a01_total = typeof doc.a01_total != 'undefined' ? doc.a01_total : 0
                                                doc.a02_total = typeof doc.a02_total != 'undefined' ? doc.a02_total : 0
                                                doc.a03_total = typeof doc.a03_total != 'undefined' ? doc.a03_total : 0
                                                doc.total_total = doc.a01_total + doc.a02_total + doc.a03_total;
                                            }
                                            return doc;
                                        })
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
                        })
                    },
                    error: errorDataSource
                });

            }
        }
    }();
    window.onload = function() {
        Table.init();
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
                filter = [{
                        field: 'for_year',
                        operator: 'eq',
                        value: date.getFullYear().toString()
                    }];
                Table.dataSource.filter(filter);
                // Table.dataSource_detail.filter(filter);
            },
        });
        kendo.bind($(".mvvm"), observable);
    };

    
</script>
<script>
    function saveAsExcel() {
        // Table.grid.saveAsExcel();
        $.ajax({
            url: ENV.reportApi + "loan/Monthly_first_time_payment_delinquency/exportExcel",
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

<style>
.k-grid  .k-grid-header  .k-header  .k-link {
    height: auto;
}
  
.k-grid  .k-grid-header  .k-header {
    white-space: normal;
}
</style>

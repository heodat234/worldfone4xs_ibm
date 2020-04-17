    <div id="page-content">
        <!-- Table Styles Header -->
        <ul class="breadcrumb breadcrumb-top">
            <li>@Report@</li>
            <li>Daily Payment Report</li>
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
            <h3 class="col-sm-12 text-center" style="margin-bottom: 20px;color: #27ae60">DAILY PAYMENT REPORT</h3>
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
                init: function() {
                    var dataSource = this.dataSource = new kendo.data.DataSource({
                        serverPaging: true,
                        serverFiltering: true,
                        serverSorting: true,
                        pageSize: 10,
                        filter: {
                              logic: "and",
                              filters: [
                                  {field: 'createdAt', operator: "gte", value: this.fromDate},
                              ]
                        },
                        
                        transport: {
                            read: ENV.reportApi + "loan/daily_payment_report",
                            parameterMap: parameterMap
                        },
                        schema: {
                            data: "data",
                            total: "total",
                          //   parse: function (response) {
                          //     response.data.map(function(doc) {
                          //         doc.due_date = doc.due_date ? new Date(doc.due_date * 1000) : undefined;
                          //         doc.payment_date = doc.payment_date ? new Date(doc.payment_date * 1000) : undefined;
                          //         return doc;
                          //     })
                          //     return response;
                          // },
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
                            field: "account_number",
                            title: "ACCOUNT NUMBER",
                            width: 150
                        },{
                            field: "name",
                            title: "NAME",
                            width: 130
                        },{
                            field: "due_date",
                            title: "OVERDUE DATE",
                            template: dataItem => gridTimestamp(dataItem.due_date),
                            width: 130
                        },{
                            field: "payment_date",
                            title: "PAYMENT DATE",
                            template: dataItem => gridTimestamp(dataItem.payment_date),
                            width: 130
                        },{
                            field: "amt",
                            title: "AMOUNT",
                            width: 130
                        },{
                            field: "paid_principal",
                            title: "PAID PRINCIPAL",
                            width: 130
                        },{
                            field: "paid_interest",
                            title: "PAID INTEREST",
                            width: 130
                        },{
                            field: "RPY_FEE",
                            title: "PAID LATE CHARGE & FEE",
                            width: 130
                        },{
                            field: "group",
                            title: "GROUP",
                            width: 130
                        },{
                            field: "num_of_overdue_day",
                            title: "NUMBER OF OVERDUE DAYS",
                            width: 130
                        },{
                            field: "pic",
                            title: "PIC",
                            width: 130
                        },{
                            field: "product_name",
                            title: "PRODUCT",
                            width: 130
                        },{
                            field: "note",
                            title: "NOTE",
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
            
            var dateRange = 30;
            var nowDate = new Date();
            var date =  new Date();
            date.setDate(nowDate.getDate() - 1);
            var timeZoneOffset = date.getTimezoneOffset() * kendo.date.MS_PER_MINUTE;
            date.setHours(- timeZoneOffset / kendo.date.MS_PER_HOUR, 0, 0 ,0);

            var fromDate = new Date(date.getTime() + timeZoneOffset );
            var toDate = new Date(date.getTime() + timeZoneOffset + kendo.date.MS_PER_DAY -1)

            Table.fromDate = fromDate.getTime() / 1000;
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
                    var fromDateTime = this.fromDateTime.getTime() / 1000;
                    var toDateTime = fromDateTime + 86000;

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
                url: ENV.reportApi + "loan/daily_payment_report/downloadExcel",
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
            function reloadReport() {
                $.ajax({
                  url: ENV.reportApi + "loan/daily_payment_report/saveReport",
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

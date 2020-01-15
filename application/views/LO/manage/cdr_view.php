<script>
var Config = {
    crudApi: `${ENV.vApi}`,
    templateApi: `${ENV.templateApi}`,
    collection: "cdr",
    scrollable: true,
    observable: {
    },
    model: {
        id: "id",
        fields: {
            starttime: {type: "date"},
            totalduration: {type: "number"},
            callduration: {type: "number"},
            show_popup: {type: "boolean"}
        }
    },
    parse: function (response) {
        response.data.map(function(doc) {
            doc.starttime = new Date(doc.starttime * 1000);
            return doc;
        })
        return response;
    },
    columns: [{
        field: "direction",
        title: "@Direction@",
        template: dataItem => templateDirection(dataItem),
        filterable: {
            ui: directionFilter,
        },
        width: 100
    },{
        field: "dialtype",
        title: "@Dial type@",
        width: 100,
        hidden: true
    },{
        field: "starttime",
        title: "@Time@",
        format: "{0: dd/MM/yy HH:mm}",
        width: 100
    },{
        field: "userextension",
        title: "@Extension@",
        width: 60
    },{
        field: "agentname",
        title: "@Agent name@",
        width: 100
    },{
        field: "group_name",
        title: "@Group@",
        width: 100
    },{
        field: "customer.name",
        title: "@Customer name@",
        template: function(dataItem) {
            var result = '';
            if(dataItem.customer) {
                if(dataItem.customer.length) {
                    result = dataItem.customer.map(doc => `<a href="${ENV.baseUrl}manage/customer/#/detail/${doc.id}" target="_blank" class="grid-name" data-id="${doc.id}" title="@View detail@">${(doc.name || '').toString()}</a>`).join(" <i class='text-danger'>OR</i> ");
                    result += `<br><a href="javascript:void(0)" onclick="defineCustomerCdr(this)" class="text-danger"><i>@Define@ @customer@ @of@ @this call@</i></a>`
                } else {
                    result = `<a href="${ENV.baseUrl}api/v1/redirect/fromFieldToCustomerDetail/LIC_NO/${dataItem.customer.LIC_NO}" target="_blank" class="grid-name" data-id="${dataItem.customer.id}" title="@View detail@">${(dataItem.customer.name || '').toString()}</a>`;
                }
            }
            return result
        },
        width: 200
    },{
        field: "customernumber",
        title: "@Phone number@",
        template: dataItem => gridPhoneDialId(dataItem.customernumber, dataItem.dialid, "manual"),
        width: 100
    },{
        field: "disposition",
        title: "@Result@",
        filterable: {
            ui: dispostionFilter,
        },
        template: dataItem => templateDisposition(dataItem),
        width: 120
    },{
        field: "action_code",
        title: "@Action code@",
        width: 100
    },{
        field: "totalduration",
        title: "@Total duration@",
        width: 120
    },{
        field: "callduration",
        title: "@Call duration@",
        width: 120
    },{
        // Use uid to fix bug data-uid of row undefined
        template: '<a role="button" class="btn btn-sm btn-circle btn-action btn-primary" data-uid="#: uid #"><i class="fa fa-ellipsis-v"></i></a>',
        width: 36
    }],
    filterable: KENDO.filterable,
    reorderable: true
}; 

function defineCustomerCdr(ele) {
    var uid = $(ele).closest("tr").data("uid"),
        dataItem = Table.dataSource.getByUid(uid),
        customers = dataItem.customer,
        calluuid = dataItem.calluuid;

    var buttons = {cancel: true};
    for (var i = 0; i < customers.length; i++) {
        buttons[i] = {text: customers[i].name};
    }

    var type = swal({
        title: "@Choose one@.",
        text: `@Greater than one customer have this number@.`,
        icon: "warning",
        buttons: buttons
    }).then(index => {
        if(index !== null && index !== false) {
            var customer = customers[index].toJSON();
            $.ajax({
                url: ENV.vApi + "cdr/update/" + calluuid,
                data: JSON.stringify({customer: customer}),
                type: "POST",
                contentType: "application/json; charset=utf-8",
                success: function(res) {
                    if(res.status) {
                        notification.show("@Success@", "success");
                        Table.dataSource.read();
                    } else notification.show("@No success@", "error");
                },
                error: errorDataSource
            })
        }
    })
}

function playAction(ele) {
    var uid = $(ele).data("uid"),
        dataItem = Table.dataSource.getByUid(uid),
        calluuid = dataItem.calluuid,
        callduration = dataItem.callduration;
    if(callduration) 
        play(calluuid);
    else notification.show("No recording", "warning");
}

function downloadAction(ele) {
    var uid = $(ele).data("uid");
        dataItem = Table.dataSource.getByUid(uid),
        calluuid = dataItem.calluuid,
        callduration = dataItem.callduration;
    if(callduration) 
        downloadRecord(calluuid);
    else notification.show("No recording", "warning");
}

function directionFilter(element) {
    element.kendoDropDownList({
        dataSource: {
            data: [
                {direction:"inbound", text: "Inbound"},
                {direction:"outbound", text: "Outbound"},
            ]
        },
        dataTextField: "text",
        dataValueField: "direction",
        optionLabel: "-- Chọn --"
    });
}

function dispostionFilter(element) {
    element.kendoDropDownList({
        dataSource: {
            data: [
                {disposition:"ANSWERED", text: "ANSWERED"},
                {disposition:"NO ANSWER", text: "NO ANSWER"},
                {disposition:"BUSY", text: "BUSY"},
                {disposition:"FAILED", text: "FAILED"},
                {disposition:"TIMEOUT", text: "TIMEOUT"},
            ]
        },
        dataTextField: "text",
        dataValueField: "disposition",
        optionLabel: "-- Chọn --"
    });
}

function repopupAction(ele) {
    var uid = $(ele).data("uid");
    var calluuid = Table.dataSource.getByUid(uid).calluuid;
    rePopup(calluuid);
}

$(document).on("click", ".grid-name", function() {
    var id = $(this).data("id"),
        url = ENV.baseUrl + "manage/customer/#/detail/" + id;
    window.open(url,'_blank','noopener');
})

function detailInit(e) {
    var detailRow = e.detailRow;
    detailRow.find(".detail").kendoGrid({
        pageable: {refresh: true},
        columns: [
            {field: "account_type", title: "@Type@"},
            {field: "account_number", title: "@Account number@"},
            {field: "action_code", title: "@Action code@"},
            {field: "promised_date", title: "@PTP Date@", template: data => gridTimestamp(data.promised_date, "dd/MM/yyyy")},
            {field: "promised_amount", title: "@PTP Amount@"},
            {field: "note", title: "@Note@"},
        ],
        dataSource: {
            serverFiltering: true,
            serverSorting: true,
            serverPaging: true,
            pageSize: 5,
            filter: [
                {field: "calluuid", operator: "eq", value: e.data.calluuid}
            ],
            transport: {
                read: ENV.restApi + "action_code",
                parameterMap: parameterMap
            },
            schema: {
                data: "data",
                total: "total",
                model: {}
            },
            error: errorDataSource
        },
        sortable: true,
        scrollable: true,
        filterable: true
    })
}

var Table = function() {
    var columnsStorage = JSON.parse(sessionStorage.getItem("columns_" + ENV.currentUri));
    if(columnsStorage) {
        Config.columns.map((col, idx) => {
            col.hidden = columnsStorage[idx].hidden;
        })
    }
    var pageStorage = Number(sessionStorage.getItem("page_" + ENV.currentUri));
    if(pageStorage) {
        Config.page = pageStorage;
    }
    var sortStorage = JSON.parse(sessionStorage.getItem("sort_" + ENV.currentUri));
    if(sortStorage) {
        Config.sort = sortStorage;
    }
    var filterStorage = JSON.parse(sessionStorage.getItem("filter_" + ENV.currentUri))
    if(filterStorage) {
        Config.filter = filterStorage;
    } else {
        let today = new Date(),
            tomorrow = new Date();
            today.setHours(0,0,0,0);
            tomorrow.setHours(24,0,0,0);
        Config.filter = {
            logic: "and",
            filters: [
                {field: "starttime", operator: "gte", value: today},
                {field: "starttime", operator: "lte", value: tomorrow}
            ]
        };
    }
    var columnsStorage = JSON.parse(sessionStorage.getItem("columns_" + ENV.currentUri));
    if(columnsStorage) {
        var fieldToIndex = {};
        var fieldToWidth = {};
        columnsStorage.forEach((col, idx) => {
            if(col.field) {
                fieldToIndex[col.field] = idx;
            }
        });
        Config.columns.sort(function(a, b) {
            if(a.field && b.field) {
                return fieldToIndex[a.field] - fieldToIndex[b.field];
            } return -1;
        });
    }
    return {
        dataSource: {},
        grid: {},
        columns: Config.columns,
        gridOptions: {},
        init: function() {
            var dataSource = this.dataSource = new kendo.data.DataSource({
                serverFiltering: true,
                serverPaging: true,
                serverSorting: true,
                serverGrouping: false,
                filter: Config.filter ? Config.filter : null,
                sort: Config.sort ? Config.sort : null,
                page: Config.page ? Config.page : null,
                pageSize: 10,
                batch: false,
                schema: {
                    data: "data",
                    total: "total",
                    groups: "groups",
                    model: Config.model,
                    parse: Config.parse ? Config.parse : res => res
                },
                transport: {
                    read: {
                        url: Config.crudApi + Config.collection
                    },
                    update: {
                        url: function(data) {
                            return Config.crudApi + Config.collection + "/" + data.id;
                        },
                        type: "PUT",
                        contentType: "application/json; charset=utf-8"
                    },
                    create: {
                        url: Config.crudApi + Config.collection,
                        type: "POST",
                        contentType: "application/json; charset=utf-8"
                    },
                    destroy: {
                        url: function(data) {
                            return Config.crudApi + Config.collection + "/" + data.id;
                        },
                        type: "DELETE",
                    },
                    parameterMap: parameterMap
                },
                sync: syncDataSource,
                error: errorDataSource
            });

            this.gridOptions = Object.assign({
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
                pageable: {
                    refresh: true,
                    pageSizes: [5, 10, 20, 50, 100],
                    input: true,
                    messages: KENDO.pageableMessages ? KENDO.pageableMessages : {}
                },
                sortable: true,
                reorderable: Boolean(Config.reorderable),
                scrollable: Boolean(Config.scrollable),
                columns: this.columns,
                filterable: Config.filterable ? Config.filterable : true,
                editable: false,
                noRecords: {
                    template: `<h2 class='text-danger'>${KENDO.noRecords}</h2>`
                },
                page: function(e) {
                    sessionStorage.setItem("page_" + ENV.currentUri, e.page);
                },
                sort: function(e) {
                    sessionStorage.setItem("sort_" + ENV.currentUri, JSON.stringify(e.sort));
                },
                filter: function(e) {
                    sessionStorage.setItem("filter_" + ENV.currentUri, JSON.stringify(e.filter));
                },
                columnReorder: function(e) {
                    setTimeout(() => {
                        sessionStorage.setItem("columns_" + ENV.currentUri, JSON.stringify(e.sender.columns));
                    }, 100);
                },
                dataBinding: function() {
                    record = (dataSource.page() -1) * dataSource.pageSize();
                },
                detailTemplate: `<div class="detail"></div>`,
                detailInit: detailInit,
            }, Config.gridOptions);

            var grid = this.grid = $("#grid").kendoGrid(this.gridOptions).data("kendoGrid");

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
                let row = $(e.target).closest("tr");
                e.pageX -= 20;
                showMenu(e, row);
            });

            function showMenu(e, that) {
                //hide menu if already shown
                menu.hide(); 

                //Get id value of document
                var uid = $(that).data('uid');
                if(uid)
                {
                    menu.find("a").data('uid',uid);

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
    <?php if(!empty($filter)) { ?>
        Config.filter = <?= $filter ?>;
    <?php } ?>
    Table.init();
}
</script>

<!-- Table Styles Header -->
<ul class="breadcrumb breadcrumb-top">
    <li>@Manage@</li>
    <li>@CDR@</li>
    <li class="pull-right none-breakcrumb">
        <a role="button" class="btn btn-sm" data-field="starttime" onclick="customFilter(this, Table.dataSource)"><i class="fa fa-filter"></i> <b>@Custom Filter@</b></a>
        <div class="input-group-btn column-widget">
            <a role="button" class="btn btn-sm dropdown-toggle" data-toggle="dropdown" onclick="editColumns(this)"><i class="fa fa-calculator"></i> <b>@Edit Columns@</b></a>
            <ul class="dropdown-menu dropdown-menu-right" style="width: 300px">
                <li class="dropdown-header text-center">@Choose columns will show@</li>
                <li class="filter-container" style="padding-bottom: 15px">
                    <div class="form-horizontal" data-bind="source: columns" data-template="column-template"/>
                </li>
            </ul>
        </div>
    </li>
</ul>
<!-- END Table Styles Header -->

<div class="container-fluid">
    <div class="row filter-mvvm" style="display: none; margin: 10px 0">
    </div>
    <div class="row">
        <div class="col-sm-12" style="padding: 0">
            <!-- Table Styles Content -->
            <div id="grid"></div>
            <!-- END Table Styles Content -->
        </div>
    </div>
</div>

<div id="action-menu">
    <ul>
        <a href="javascript:void(0)" data-type="action/play" onclick="playAction(this)"><li><i class="fa fa-play text-info" style="padding-left: 3px"></i><span>@Play@ @recording@</span></li></a>
        <a href="javascript:void(0)" data-type="action/download" onclick="downloadAction(this)"><li><i class="fa fa-cloud-download text-danger"></i><span>@Download@ @recording@</span></li></a>
        <a href="javascript:void(0)" data-type="action/repopup" onclick="repopupAction(this)"><li><i class="hi hi-new_window text-warning"></i><span>@Repopup@</span></li></a>
        <a href="javascript:void(0)" data-type="action/detail" class="hidden"><li><i class="fa fa-exclamation-circle text-info"></i><span>@Detail@</span></li></a>
    </ul>
</div>
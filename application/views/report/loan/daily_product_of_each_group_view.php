<script>
var currentDate = new Date();
var currentMonth = currentDate.getMonth();
currentDate.setHours(0, 0, 0, 0);
defaultStartDate = new Date();
defaultStartDate.setDate(21);
if(currentMonth == 1) {
    defaultStartDate.setMonth(12);
}
else {
    defaultStartDate.setMonth(currentMonth - 1);
}
defaultEndDate = new Date();
defaultEndDate.setDate(20);
Date.prototype.addDays = function(days) {
    var dat = new Date(this.valueOf());
    dat.setDate(dat.getDate() + days);
    return dat;
};

async function createColumn() {
    var grid_columns = [{
        field: 'debt_group',
        title: "Group",
        width: 80,
    }, {
        field: 'due_date_code',
        title: "Group",
        width: 80,
    }, {
        field: 'product',
        title: "Product",
        width: 150,
    }, {
        field: 'due_date',
        title: "Due date",
        template: data => gridDate(data.due_date, 'dd/MM/yyyy'),
        width: 100,
    },{
        title: 'Number',
        columns: [{
            field: 'inci',
            title: 'Incidence',
            width: 80
        }, {
            field: 'col',
            title: 'Collected',
            width: 80
        }, {
            field: 'rem',
            title: "Remaining",
            width: 80
        }, {
            field: 'flow_rate',
            title: "Flow rate",
            width: 80
        }, {
            field: 'col_rate',
            title: "Collected rate",
            width: 80
        }]
    },{
        title: 'Outstanding Balance',
        columns: [{
            field: 'inci_amt',
            title: 'Incidence (outstanding balance at due date)',
            width: 80
        }, {
            field: 'inci_ob_principal',
            title: 'Incidence (outstanding principal)',
            width: 80
        }, {
            field: 'amt',
            title: "Actual collected amount (based on oustanding balance at due date)",
            width: 80
        }, {
            field: 'col_prici',
            title: "Collected principal amount",
            width: 80
        }, {
            field: 'col_amt',
            title: "Collected  amount (OS at current - OS at due date)",
            width: 80
        }, {
            field: 'rem_amt',
            title: "Remaining (OS at current - OS at due date)",
            width: 80
        }, {
            field: 'flow_rate_amt',
            title: "Flow rate (OS at current - OS at due date)",
            width: 80
        }, {
            field: 'actual_ratio',
            title: "Collected ratio (Actual collected amount)",
            width: 80
        }, {
            field: 'princi_ratio',
            title: "Collected ratio (Principal amount)",
            width: 80
        }, {
            field: 'amt_ratio',
            title: "Collected ratio (OS at current - OS due date)",
            width: 80
        }]
    }];


    

    return grid_columns;
}

var Config = {
    crudApi: `${ENV.reportApi}`,
    templateApi: `${ENV.templateApi}`,
    collection: "daily_prod_each_group_report",
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

var Table = function() {
    return {
        dataSource: {},
        grid: {},
        init: async function() {
            var dataSource = this.dataSource = new kendo.data.DataSource({
                serverFiltering: true,
                serverPaging: true,
                serverSorting: true,
                serverGrouping: false,
                pageSize: 9,
                batch: false,
                sort: [{
                    field: "debt_group", dir: "asc"
                }, {
                    field: "due_date_code", dir: "asc"
                }, {
                    field: "product", dir: "desc"
                }, {
                    field: "team", dir: "asc"
                }],
                schema: {
                    data: "data",
                    total: "total",
                    groups: "groups",
                    model: Config.model,
                    parse: Config.parse ? Config.parse : res => res
                },
                transport: {
                    read: {
                        url: Config.crudApi + 'loan/' + Config.collection + '/read'
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
            var columnsGrid = await createColumn();
            var grid = this.grid = $("#grid").kendoGrid({
                dataSource: dataSource,
                resizable: true,
                pageable: {
                    refresh: true
                },
                sortable: true,
                scrollable: false,
                columns: columnsGrid,
                filterable: Config.filterable ? Config.filterable : true,
                editable: false,
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

function functionFalse() {
    return false;
}

$(document).on("click", ".grid-name", function() {
    var id = $(this).data("id"),
        url = ENV.baseUrl + "manage/customer/#/detail/" + id;
    window.open(url,'_blank','noopener');
})

$(document).on("ready", function() {
    Table.init();
})

function saveAsExcel() {
    $.ajax({
        url: ENV.reportApi + "loan/"+Config.collection+"/exportExcel",
        type: 'POST',
        dataType: 'json',
    })
    .done(function(response) {
        if (response.status == 1) {
        window.location = response.data
        }
    })
}
</script>

<script type="text/x-kendo-template" id="statusTemplate">
    <span class="#if(typeof value != 'undefined' && value == '0'){##='text-muted'##}#">#if(typeof value != 'undefined'){##: text ##}#</span>
</script>

<!-- Page content -->
<div id="page-content">
    <!-- Table Styles Header -->
    <ul class="breadcrumb breadcrumb-top">
        <li>@Report@</li>
        <li>Daily productivity report -each user and group</li>
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
            <a role="button" class="btn btn-sm" onclick="saveAsExcel()"><i class="fa fa-file-excel-o"></i> <b>@Export@</b></a>
        </li>
    </ul>
    <!-- END Table Styles Header -->

    <div class="container-fluid">
        <div class="row filter-mvvm" style="display: none; margin: 10px 0">
        </div>
        <div class="row">
            <div class="col-sm-12" style="height: 80vh; overflow-y: auto; padding: 0">
                <!-- Table Styles Content -->
                <div id="grid"></div>
                <!-- END Table Styles Content -->
            </div>
        </div>
    </div>

    <div id="action-menu">
        <ul>
            <a href="javascript:void(0)" data-type="action/play" onclick="playAction(this)"><li><i class="fa fa-play text-info" style="padding-left: 3px"></i><span>@Play@</span></li></a>
            <a href="javascript:void(0)" data-type="action/download" onclick="downloadAction(this)"><li><i class="fa fa-cloud-download text-danger"></i><span>@Download@</span></li></a>
            <a href="javascript:void(0)" data-type="action/evaluate" onclick="evaluateAction(this)"><li><i class="gi gi-pen text-danger"></i><span>@Evaluate@</span></li></a>
        </ul>
    </div>
</div>

<style>
    #grid {
        overflow: scroll;
    }
</style>
<!-- END Page Content -->
<script>
var Config = {
    crudApi: `${ENV.restApi}`,
    templateApi: `${ENV.templateApi}`,
    collection: "cdr",
    observable: {
        item: {
            qcstatus: null,
            qcdata: [],
            initPoint: 0,
            totalPoint: 0,
            endPoint: 0,
        },
        qcdataCellClose: function(e) {
            var data = e.sender.dataSource.data().toJSON();
            this.set("item.qcdata", data);
            this.calculatePoint(data);
        },
        qcdataDataBinding: function(e) {
            if(e.action == "rebind" || e.action == "remove") {
                this.set("item.qcstatus", (this.get("item.qcstatus") || "0").toString());
                var data = e.sender.dataSource.data().toJSON();
                this.calculatePoint(data);
            } else {
                this.set("item.qcstatus", "1");
            }
        },
        calculatePoint(data) {
            var totalPoint = 0;
            data.forEach(doc => {
                totalPoint += doc.point;
            })
            this.set("item.totalPoint", totalPoint);
            this.set("item.endPoint", this.get("item.initPoint") + totalPoint);
        },
        playRecording: function(e) {
            play(this.item.calluuid);
        },
        qcstatusOption: () => dataSourceJsonData(["Qc","cdr","status"]),
        qcstatusSelect: function(e) {
            if(e.dataItem.value == "0"){
                e.preventDefault();
            }
        }
    },
    model: {
        id: "id",
        fields: {
            starttime: {type: "date"},
            billduration: {type: "number"}
        }
    },
    parse: function (response) {
        response.data.map(function(doc) {
            doc.starttime = new Date(doc.starttime * 1000);
            doc.qcstatus = (doc.qcstatus || "0").toString();
            return doc;
        })
        return response;
    },
    columns: [{
        field: "calluuid",
        title: "Call ID",
        width: 100
    },{
        title: "Call Source",
        template: dataItem => templateCallSource(dataItem),
        width: 80
    },{
        field: "service",
        title: "@Service@",
    },{
        field: "userextension",
        title: "@Extension@",
        width: 100
    },{
        field: "starttime",
        title: "@Time@",
        template: function(dataItem) {
            return (kendo.toString(dataItem.starttime, "dd/MM/yy H:mm:ss") ||  "").toString();
        },
        width: 100
    },{
        field: "customernumber",
        title: "@Phone number@",
        template: function(dataItem) {
            var phone = (dataItem.customernumber || '').toString();
            return phone ? `<b href="javascript:void(0)" class="copy-item text-info">${phone}</b>` : ``;
        },
        width: 100
    },{
        field: "billduration",
        title: "@Bill duration@",
        width: 100
    },{
        field: "callduration",
        title: "@Call duration@",
        width: 100
    },{
        field: "qcstatus",
        title: "@Status@",
        width: 120
    },{
        field: "endPoint",
        title: "@End@ @point@",
        width: 80
    },{
        field: "qcnote",
        title: "@Note@",
        width: 80
    },{
        // Use uid to fix bug data-uid of row undefined
        template: '<a role="button" class="btn btn-sm btn-circle btn-action" data-uid="#: uid #"><i class="fa fa-ellipsis-v"></i></a>',
        width: 20
    }],
    filterable: KENDO.filterable
}; 

function ftPoint(data) {
    return data.point ? data.point.sum : 0;
}

var Table = function() {
    return {
        dataSource: {},
        grid: {},
        columns: Config.columns,
        init: async function() {
            var statusValues = await $.ajax({
                url: ENV.vApi + "select/jsondata",
                data: {tags: ["Qc", "cdr", "status"]}
            });

            Config.columns.map(doc => {
                if(doc.field == "qcstatus")
                    doc.values = statusValues.data;
            })
            var configType = await $.get(ENV.vApi + "configtype/detail/" + ENV.type);
            if( !Config.observable.item.initPoint ) 
                Config.observable.item.initPoint = configType.call_init_point;
            var dataSource = this.dataSource = new kendo.data.DataSource({
                filter: {field: "disposition", operator: "eq", value: "ANSWERED"},
                serverFiltering: true,
                serverPaging: true,
                serverSorting: true,
                serverGrouping: false,
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

            var grid = this.grid = $("#grid").kendoGrid({
                dataSource: dataSource,
                resizable: true,
                pageable: {
                    refresh: true
                },
                sortable: true,
                scrollable: false,
                columns: this.columns,
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

function templateCallSource(dataItem) {
    if(dataItem.dialtype) {
        return dataItem.dialtype;
    } else {
        return (dataItem.direction == "outbound") ? "Call out" : "Call in";
    }
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

async function evaluateAction(ele) {
    var uid = $(ele).data("uid");
        dataItem = Table.dataSource.getByUid(uid),
        dataItemFull = await $.ajax({
            url: `${ENV.restApi+Config.collection}/${dataItem.id}`,
            error: errorDataSource
        }),
        formHtml = await $.ajax({
            url: Config.templateApi + "qc/" + Config.collection + "_form",
            error: errorDataSource
        });

    openForm({title: "@Evaluate@ @call@ "+dataItemFull.calluuid+ " @of@ "+dataItemFull.userextension, width: 1000});
    dataItemFull.qcdata = new kendo.data.DataSource({
        data: dataItemFull.qcdata,
        aggregate: [
            { field: 'point', aggregate: 'sum' },
        ]
    })
    var model = kendo.observable(Object.assign(Config.observable, {
        item: Object.assign(Config.observable.item, dataItemFull),
        save: function() {
            var qcdata = (this.item.qcdata instanceof kendo.data.DataSource) ? this.item.qcdata.data().toJSON() : this.item.toJSON().qcdata;
            $.ajax({
                url: `${ENV.restApi+Config.collection}/${this.item.id}`,
                data: JSON.stringify({
                    qcstatus    : this.item.qcstatus,
                    qcdata      : qcdata,
                    qcnote      : this.item.qcnote,
                    initPoint   : this.item.initPoint,
                    totalPoint  : this.item.totalPoint,
                    endPoint    : this.item.endPoint
                }),
                error: errorDataSource,
                type: "PUT",
                contentType: "application/json; charset=utf-8",
                success: function() {
                    Table.dataSource.read()
                }
            })
        }
    }));
    kendo.destroy($("#right-form"));
    $("#right-form").empty();
    var kendoView = new kendo.View(formHtml, { wrap: false, model: model, evalTemplate: false });
    kendoView.render($("#right-form"));
}

function gridSelectCode(container, options) {
    let field = options.field;
    var select = $('<input name="'+field+'"/>')
        .appendTo(container)
        .kendoDropDownList({
            valuePrimitive: true,
            dataTextField: 'code', 
            dataValueField: 'code',
            filter: "startswith",
            dataSource: dataSourceDropDownList("Qc_code", ["code", "point", "content"], {type: "Call"}, parse = res => res, pageSize = 200),
            select: function(e) {
                options.model.set("point", e.dataItem.point);
                options.model.set("content", e.dataItem.content);
            }
        }).data("kendoDropDownList");
    select.open();
}; 


$(document).on("click", ".grid-name", function() {
    var id = $(this).data("id"),
        url = ENV.baseUrl + "manage/customer/#/detail/" + id;
    window.open(url,'_blank','noopener');
})

$(document).on("ready", function() {
    Table.init();
})
</script>

<script type="text/x-kendo-template" id="statusTemplate">
    <span class="#if(typeof value != 'undefined' && value == '0'){##='text-muted'##}#">#if(typeof value != 'undefined'){##: text ##}#</span>
</script>

<!-- Page content -->
<div id="page-content">
    <!-- Table Styles Header -->
    <ul class="breadcrumb breadcrumb-top">
        <li>@Quality control@</li>
        <li>CDR</li>
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
<!-- END Page Content -->
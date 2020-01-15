<?php $id = $this->input->get("id") ?>
<div class="col-sm-12 statistic-view">
    <div class="row">
        <div class="col-sm-3" style="margin-top: 10px">
            <div class="alert alert-success" style="cursor: pointer; margin-bottom: 0px;">
                <h4>@Total@</h4>
                <a class="pull-left" data-bind="click: toggleAutoRefresh"><i class="fa fa-refresh" data-bind="css: {fa-spin: autoRefresh}"></i>&nbsp;
                    <i data-bind="visible: autoRefresh">@Auto refresh@ 10s</i>
                    <i data-bind="invisible: autoRefresh">@Manual refresh@</i>
                </a>
                <p class="text-right">
                    <span data-bind="text: diallist.total"></span>
                </p>
            </div>
        </div>
        <div class="col-sm-3" style="margin-top: 10px">
            <div class="alert alert-info" style="cursor: pointer; margin-bottom: 0px;">
                <h4>@Group name@</h4>
                <p class="text-right">
                    <span data-bind="text: diallist.group_name"></span>
                </p>
            </div>
        </div>
        <div class="col-sm-3" style="margin-top: 10px">
            <div class="alert alert-success" style="cursor: pointer; margin-bottom: 0px;">
                <h4>@Campaign target@</h4>
                <div class="pull-left">@Called@: <span data-bind="html: diallist.called"></span></div>
                <p class="text-right"><span data-bind="text: diallist.target"></span>%</p>
            </div>
        </div>
        <div class="col-sm-3" style="margin-top: 10px" data-bind="visible: diallist.is_auto">
            <div class="alert alert-info" style="height: 80px; cursor: pointer; margin-bottom: 0px;">
                <h4>
                    <i class="fa fa-pause" aria-hidden="true" data-bind="invisible: diallist.runStatus"></i>
                    <i class="fa fa-cog fa-spin" aria-hidden="true" data-bind="visible: diallist.runStatus"></i>
                    @Status@
                </h4>
                <div class="pull-left">Queue: <span data-bind="html: diallist.queuesHTML"></span></div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
            <span>@Total OutBal@ @of@ @you@: <b id="total-outstanding-balance" class="text-danger"></b> VND</span>
        </div>
        <div class="col-sm-4">
            <button class="k-button btn-primary" data-bind="click: exportExcel" style="padding: 0 4px; font-size: 12px"><i class="fa fa-file-excel-o"></i>&nbsp;@Export@ excel</button>
        </div>
        <div class="col-sm-4" id="do-no-call-filter">
            <label style="line-height: 1.5">
                <input class="custom-checkbox" type="checkbox" data-bind="events: {change: filterOnlyCall}">
                <span></span>
                <span>@Only@ @case@ @need@ @calling@</span>
            </label>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            @Members@: <span data-template="member-template" data-bind="source: diallist.queueMembers"></span>
        </div>
    </div>
    <div class="row" style="padding-bottom: 10px">
        <div class="col-sm-12" data-template="calling-phone" data-bind="source: dialInProcessDataSource"></div>
    </div>
</div>
<div class="col-sm-12" style="overflow-y: auto; padding: 0">
    <div id="grid-<?= $id ?>"></div>
</div>
<div id="detail-action-menu" class="action-menu">
    <ul>
        <a href="javascript:void(0)" data-type="detail" onclick="openForm({title: '@View@ @case@', width: 900}); viewForm(this)"><li><i class="fa fa-television text-info"></i><span>@View@</span></li></a>
        <li class="devide"></li>
        <a href="javascript:void(0)" data-type="detail" onclick="viewPopup(this)"><li><i class="fa fa-newspaper-o text-danger"></i><span>@View@ popup</span></li></a>
    </ul>
</div>
<script id="member-template" type="text/x-kendo-template">
    <div class="member-element" style="min-width"><span style="background-image: url('/api/v1/avatar/agent/#: data.extension #')"></span><b class="# switch (data.statuscode) { 
                    case 1: # 
                        text-success
                    # break;
                    case 2: #
                        text-info
                    # break;
                    case 3: # 
                        text-danger
                    # break;
                    case 4: # 
                        text-warning
                    # break;
                    default: #
                        text-muted
                    # break; 
                } #">#: data.extension || '' # (#: data.agentname || '' #)</b> <i class=""></i></div>
</script>
<script id="calling-phone" type="text/x-kendo-template">
    <span class="label label-danger animation-pulse"><b data-bind="text: phone"></b> - <i data-bind="text: timeSince, attr: {data-date-time: createdAt}" class="time-interval"></i></span>
</script>
<script>
function gridCallResult(data) {
    var htmlArr = [];
    if(data) {
        data.forEach(doc => {
            htmlArr.push(`<a href="javascript:void(0)" class="label label-${(doc.disposition == "ANSWERED")?'success':'warning'}" 
                title="${kendo.toString(new Date(doc.starttime * 1000), "dd/MM/yy H:mm:ss")} | ${doc.userextension} - ${doc.customernumber}">${doc.disposition}</a>`);
        })
    }
    return htmlArr.join("<br>");
}
var Config = {
    id: '<?= $id ?>',
    crudApi: `${ENV.restApi}`,
    templateApi: `${ENV.templateApi}`,
    collection: "my_diallist_detail",
    observable: {
    },
    model: {
        id: "id",
        fields: {
            index: {type: "number"}
        }
    },
    columns: [{
            field: "index",
            title: "#",
            width: 40,
            locked: true
        },{
            field: "phone",
            title: "@Main phone@",
            template: data => gridPhoneDialId(data.phone, data.id, "manual"),
            width: 110,
            locked: true
        },{
            field: "other_phones",
            title: "@Other phones@",
            template: data => gridPhoneDialId(data.other_phones, data.id, "manual"),
            width: 110,
            locked: true
        },{
            field: "action_code",
            title: "@Call code@",
            width: 80,
            locked: true
        },{
            field: "callResult",
            title: "@Calls@",
            template: diallistDetail => gridCallResult(diallistDetail.callResult),
            width: 110,
            locked: true
        },{
            // Use uid to fix bug data-uid of row undefined
            title: ``,
            template: '<a role="button" class="btn btn-sm btn-circle btn-action" title="#: id #" data-uid="#: uid #"><i class="fa fa-ellipsis-v"></i></a>',
            width: 36,
            locked: true
        }]
}; 

var detailTable = function() {
    var page = null;
    var sort = [{field: "priority", dir: "asc"}, {field: "index", dir: "asc"}];
    var pageStorage = Number(sessionStorage.getItem("page_" + ENV.currentUri + location.hash));
    if(pageStorage) {
        page = pageStorage;
    }
    var sortStorage = JSON.parse(sessionStorage.getItem("sort_" + ENV.currentUri + location.hash));
    if(sortStorage) {
        sort = sortStorage;
    }
    return {
        dataSource: {},
        grid: {},
        columns: Config.columns,
        init: async function() {
            var dataSource = this.dataSource = new kendo.data.DataSource({
                serverFiltering: true,
                serverPaging: true,
                serverSorting: true,
                serverGrouping: false,
                filter: {field: "diallist_id", operator: "eq", value: Config.id},
                sort: sort,
                page: page,
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
                        url: Config.crudApi + Config.collection,
                        data: {diallist_id: Config.id},
                        global: false
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

            var this_diallist = await $.get(`${ENV.restApi}diallist/${Config.id}`);
            var sub_type = "1";
            if(this_diallist['team'] != undefined){
                if(this_diallist['team'] == 'CARD'){
                    sub_type = "2";
                }
                if(this_diallist['team'] != 'SIBS') {
                    $("#do-no-call-filter").remove();
                }
            }
            var diallistDetailModel = this.diallist = await $.get(`${ENV.restApi}model`, {q: JSON.stringify({
                filter: {
                    logic: "and",
                    filters: [
                    {field: "collection", operator: "eq", value: ENV.type + "_Diallist_detail"},
                    {field: "sub_type", operator: "isnotempty", value: ""},
                    {field: "sub_type", operator: "isnotnull", value: ""},
                    {field: "sub_type", operator: "eq", value: sub_type},
                    ]
                },
                sort: [{field: "index", dir: "asc"}]
            })});

            diallistDetailColumns = diallistDetailModel.data;

            diallistDetailColumns.map((col, idx) => {
                if(col.field == 'assign'){
                    col.template = data => convertExtensionToAgentname[data[col.field]];
                }else{
                   switch(col.type) {
                    case "array": case "arrayPhone":
                    col.template = data => gridArray(data[col.field]);
                    break;
                    case "timestamp":
                    col.template = data => gridTimestamp(data[col.field]);
                    break;
                    case "currency":
                    col.template = data => gridCurrency(data[col.field]);
                    break;
                    case "int": case "double":
                    col.template = data => gridInterger(data[col.field]);
                    break;
                    default:
                    col.template = data => gridLongText(data[col.field], 25);
                    break;
                }

                }
                if(idx == 0)
                    col.width = 120;
                else
                    col.width = 100;

            });

            this.columns = this.columns.concat(diallistDetailColumns);

            var grid = this.grid = $(`#grid-${Config.id}`).kendoGrid({
                dataSource: dataSource,
                excel: {allPages: true, fileName: this_diallist.name + "_assign_" + ENV.extension + ".xlsx"},
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
                    pageSizes: true,
                    input: true
                },
                sortable: true,
                scrollable: true,
                height: '80vh',
                columns: this.columns,
                filterable: KENDO.filterable,
                editable: false,
                page: function(e) {
                    sessionStorage.setItem("page_" + ENV.currentUri + location.hash, e.page);
                },
                sort: function(e) {
                    sessionStorage.setItem("sort_" + ENV.currentUri + location.hash, JSON.stringify(e.sort));
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
            var menu = $("#detail-action-menu");
            if(!menu.length) return;
            
            $("html").on("click", function() {menu.hide()});

            $(document).on("click", `#grid-${Config.id} tr[role=row] a.btn-action`, function(e){
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

async function viewForm(ele) {
    var dataItem = detailTable.dataSource.getByUid($(ele).data("uid")),
        formHtml = await $.ajax({
            url: Config.templateApi + Config.collection + "/view",
            data: {dataFields: JSON.stringify(detailTable.diallist.columns), id: dataItem.id},
            error: errorDataSource
        });
    $("#right-form").empty();
    var kendoView = new kendo.View(formHtml);
    kendoView.render($("#right-form"));
}

function viewPopup(ele) {
    var dataItem = detailTable.dataSource.getByUid($(ele).data("uid"));
    startPopup({dialid:dataItem.id,customernumber:dataItem.phone,dialtype:"manual",direction:"view",starttime:Date.now()/1000});
}

detailTable.init();
updateStatistic();

$.get(ENV.vApi + "diallist/getTotalData/" + Config.id + "?extension=" + ENV.extension, function(res) {
    $("#total-outstanding-balance").text(gridCurrency(res.totalOutBal));
})

async function updateStatistic() {
    var diallist = await $.get({
            url: ENV.vApi + "diallist/getStatistic/" + Config.id,
            global: false
        });
        diallist.statusText = diallist.status ? "@Running@" : "@Stop@";
        diallist.is_auto = Boolean(diallist.mode == "auto");
        diallist.queueMembers = await $.get({
            url: ENV.vApi + "group/getMembersOfGroupId/" + diallist.group_id,
            global: false
        });
        if(diallist.is_auto) {
            diallist.queues = await $.get({
                url: ENV.vApi + "group/getQueuesLinkToGroupId/" + diallist.group_id,
                global: false
            });
            diallist.queuesHTML = gridArray(diallist.queues);
        }
    var statisticObservable = window.statisticObservable = kendo.observable({
        diallist: diallist,
        dialInProcessDataSource: diallist.is_auto && (window.statisticObservable ? window.statisticObservable.autoRefresh : false) ? new kendo.data.DataSource({
            serverFiltering: true,
            serverSorting: true,
            filter: {field: "diallistId", operator: "eq", value: Config.id},
            sort: [{field: "createdAt", dir: "asc"}],
            transport: {
                read: {
                    url: ENV.restApi + "dial_in_process",
                    global: false
                },
                parameterMap: parameterMap
            },
            schema: {
                data: "data",
                total: "total",
                parse: function(res) {
                    res.data.map(doc => {
                        doc.timeSince = time_since( new Date(doc.createdAt) );
                    })
                    return res;
                }
            }
        }) : [],
        autoRefresh: window.statisticObservable ? window.statisticObservable.autoRefresh : false,
        toggleAutoRefresh: function(e) {
            clearTimeout(window.timeoutAutoRefresh);
            let autoRefresh = this.get("autoRefresh");
            this.set("autoRefresh", !autoRefresh);
            if(!autoRefresh) updateStatistic();
        },
        filterOnlyCall: function(e) {
            let filter = {};
            if(e.currentTarget.checked) {
                filter = {field:"Donotcall",operator:"eq",value:"N"};
            }
            detailTable.dataSource.filter(filter);
        },
        exportExcel: function(e) {
            detailTable.grid.saveAsExcel();
        },
    });
    kendo.bind($(".statistic-view"), statisticObservable);
    window.timeoutAutoRefresh = setTimeout(() => {
        if(window.statisticObservable.autoRefresh) {
            updateStatistic();
            detailTable.dataSource.read();
        }
    }, 10000);
}

if(typeof window.intervalCurrentCallInQueue == "undefined") {
    window.intervalCurrentCallInQueue = setInterval(() => {
        var $select = $(".time-interval[data-date-time]");
        if($select.length) {
            for (var i = 0; i < $select.length; i++) {
                var dateTime = $select[i].dataset.dateTime,
                    timeText = time_since( new Date(dateTime) );
                $select[i].innerText = timeText;
            }
        } 
    }, 1000);
}

</script>

<style type="text/css">
    #run-status-switch {width: 110px}
    #run-status-switch .onoffswitch-inner:before {content: "RUNNING";}
    #run-status-switch .onoffswitch-inner:after {content: "STOP";}
</style>
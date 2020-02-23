<?php $id = $diallist["id"] ?>
<ul class="breadcrumb breadcrumb-top">
    <li>@Manage@</li>
    <li>@Diallist@</li>
    <li><?= $diallist["name"] ?></li>
</ul>

<div class="container-fluid">
    <div class="row" id="bottom-row">
    	<div class="col-sm-12 statistic-view">
		    <div class="row">
		        <div class="col-sm-3" style="margin-top: 10px">
		            <div class="alert alert-success" style="cursor: pointer;">
		                <h4>@Total@</h4>
		                <p class="text-right">
		                    <span data-bind="text: diallist.total"></span>
		                </p>
		            </div>
		        </div>
		        <div class="col-sm-3" style="margin-top: 10px">
		            <div class="alert alert-info">
		                <h4>@Group name@</h4>
		                <p class="text-right">
		                    <span data-bind="text: diallist.group_name"></span>
		                </p>
		            </div>
		        </div>
		        <div class="col-sm-3" style="margin-top: 10px">
		            <div class="alert alert-success">
		                <h4>@Campaign target@</h4>
		                <p class="text-right"><span data-bind="text: diallist.target"></span>%</p>
		            </div>
		        </div>
		        <div class="col-sm-3" style="margin-top: 10px" data-bind="visible: diallist.is_auto">
		            <div class="alert alert-info" style="height: 80px">
		                <h4>
		                    <i class="fa fa-pause" aria-hidden="true" data-bind="invisible: diallist.runStatus"></i>
		                    <i class="fa fa-cog fa-spin" aria-hidden="true" data-bind="visible: diallist.runStatus"></i>
		                    @Status@
		                </h4>
		                <div class="pull-right" style="margin-top: -5px">
		                    <div class="onoffswitch" id="run-status-switch">
		                        <input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" id="run-status" data-bind="checked: diallist.runStatus, events: {change: runStatusChange}">
		                        <label class="onoffswitch-label" for="run-status">
		                            <span class="onoffswitch-inner"></span>
		                            <span class="onoffswitch-switch"></span>
		                        </label>
		                    </div>
		                </div>
		            </div>
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
		    </ul>
		</div>
    </div>
</div>
<div id="action-menu">
    <ul>
    	<a href="javascript:void(0)" data-type="detail" onclick="detailData(this)"><li><i class="fa fa-exclamation-circle text-info"></i><span>Detail</span></li></a>
    </ul>
</div>

<script id="calling-phone" type="text/x-kendo-template">
    <span class="label label-danger animation-pulse"><b data-bind="text: phone"></b> - <i data-bind="text: timeSince, attr: {data-date-time: createdAt}" class="time-interval"></i></span>
</script>
<script id="detail-dropdown-template" type="text/x-kendo-template">
	<li data-bind="css: {dropdown-header: active}"><a data-bind="click: goTo, text: name, attr: {href: url}"></a></li>
</script>
<script id="column-template" type="text/x-kendo-template">
    <div class="form-group">
		<label class="control-label col-sm-4"><span data-bind="text: field"></span></label>
		<div class="col-sm-7">
			<input class="k-textbox" style="width: 100%" data-bind="value: title">
		</div>
	</div>
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
    collection: "diallist_detail",
    observable: {
    },
    model: {
        id: "id",
        fields: {
            index: {type: "number"}
        }
    },
    columns: [{
            selectable: true,
            width: 32,
            locked: true
        },{
            field: "index",
            title: "#",
            width: 50,
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
            width: 110,
            locked: true
        },{
            field: "callResult",
            title: "@Calls@",
            template: diallistDetail => gridCallResult(diallistDetail.callResult),
            width: 120,
            locked: true
        },{
            // Use uid to fix bug data-uid of row undefined
            title: `<a class='btn btn-sm btn-circle btn-action' onclick='return deleteDataItemChecked();'><i class='fa fa-times-circle'></i></a>`,
            template: '<a role="button" class="btn btn-sm btn-circle btn-action" title="#: id #" data-uid="#: uid #"><i class="fa fa-ellipsis-v"></i></a>',
            width: 36,
            locked: true
        }]
}; 

var detailTable = function() {
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
                sort: [{field: "priority", dir: "asc"}, {field: "index", dir: "asc"}],
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

            var diallistDetailModel = this.diallist = await $.get(`${ENV.restApi}model`, {q: JSON.stringify({
                filter: {
                    logic: "and",
                    filters: [
                        {field: "collection", operator: "eq", value: ENV.type + "_Diallist_detail"},
                        {field: "sub_type", operator: "isnotempty", value: ""},
                        {field: "sub_type", operator: "isnotnull", value: ""}
                    ]
                },
                sort: [{field: "index", dir: "asc"}]
            })});

            diallistDetailColumns = diallistDetailModel.data;

            diallistDetailColumns.map((col, idx) => {
                col.width = 150;
                switch(col.type) {
                    case "array": case "arrayPhone":
                        col.template = data => gridArray(data[col.field]);
                        break;
                    case "timestamp":
                        col.template = data => gridTimestamp(data[col.field]);
                        break;
                    case "int": case "double":
                        col.template = data => gridInterger(data[col.field]);
                        break;
                    default:
                        col.template = data => gridLongText(data[col.field], 20);
                        break;
                }
            });

            this.columns = this.columns.concat(diallistDetailColumns);

            this.columns.push({
                field: "assign",
                title: "@Assign@",
                template: data => (convertExtensionToAgentname[data.assign] || ""),
                filterable: {
                    ui: function(element) {
                        let dataSource = new kendo.data.DataSource({
                            transport: {
                                read: ENV.restApi + "diallist/" + Config.id,
                            },
                            schema: {
                                parse: function(res) {
                                    var memberOption = [];
                                    res.members.forEach(extension => {
                                        memberOption.push({text: convertExtensionToAgentname[extension], value: extension});
                                    })
                                    return memberOption;
                                }
                            }
                        });
                        element.kendoDropDownList({
                            dataSource: dataSource,
                            filter: "contains",
                            dataTextField: "text",
                            dataValueField: "value",
                            optionLabel: "-- @Select@ --"
                        });
                    },
                    operators: {
                      string: {
                        eq: '@Equal to@',
                      }
                    }
                },
                width: 110
            })

            var grid = this.grid = $(`#grid-${Config.id}`).kendoGrid({
                dataSource: dataSource,
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
                editable: false
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

function customerDetail(ele) {
    var dataItem = detailTable.dataSource.getByUid($(ele).data("uid"));
    window.open(ENV.vApi + "redirect/fromPhoneToCustomerDetail/" + dataItem.phone);
}

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

async function editForm(ele) {
	var dataItem = detailTable.dataSource.getByUid($(ele).data("uid")),
	    formHtml = await $.ajax({
    	    url: Config.templateApi + Config.collection + "/form?id=" + dataItem.id,
            data: {dataFields: JSON.stringify(detailTable.diallist.columns)},
    	    error: errorDataSource
    	});
	kendo.destroy($("#right-form"));
	$("#right-form").html(formHtml);
}

function deleteDataItem(ele) {
	swal({
	    title: "Are you sure?",
	    text: "Once deleted, you will not be able to recover this document!",
	    icon: "warning",
	    buttons: true,
	    dangerMode: true,
    })
    .then((willDelete) => {
		if (willDelete) {
			var uid = $(ele).data('uid');
			var dataItem = detailTable.dataSource.getByUid(uid);
		    detailTable.dataSource.remove(dataItem);
		    detailTable.dataSource.sync();
		}
    });
}

function deleteDataItemChecked() {
    var checkIds = detailTable.grid.selectedKeyNames();
    if(checkIds.length) {
        swal({
            title: "Are you sure?",
            text: "Once deleted, you will not be able to recover these documents!",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        })
        .then((willDelete) => {
            if (willDelete) {
                checkIds.forEach(uid => {
                    var dataItem = detailTable.dataSource.getByUid(uid);
                    detailTable.dataSource.remove(dataItem);
                    detailTable.dataSource.sync();
                })
            }
        });
    } else {
        swal({
            title: "No row is checked!",
            text: "Please check least one row to remove",
            icon: "error"
        });
    }
}

async function updateStatistic() {
    var diallist = await $.get(ENV.vApi + "diallist/getStatistic/" + Config.id);
        diallist.statusText = diallist.status ? "@Running@" : "@Stop@";
        diallist.is_auto = Boolean(diallist.mode == "auto");
    var statisticObservable = kendo.observable({
        diallist: diallist,
        dialInProcessDataSource: new kendo.data.DataSource({
            serverFiltering: true,
            serverSorting: true,
            filter: {field: "diallistId", operator: "eq", value: Config.id},
            sort: [{field: "createdAt", dir: "asc"}],
            transport: {
                read: ENV.restApi + "dial_in_process",
                parameterMap: parameterMap
            },
            schema: {
                data: "data",
                total: "total",
                parse: function(res) {
                    res.data.map(doc => {
                        let d = new Date();
                        d.setHours(0,0,0,0);
                        doc.timeSince = kendo.toString(new Date(d - new Date(doc.createdAt)), "mm:ss");
                    })
                    return res;
                }
            }
        }),
        runStatusChange: function(e) {
            $.ajax({
                url: ENV.restApi + "diallist/" + Config.id,
                type: "PUT",
                contentType: "application/json; charset=utf-8",
                data: JSON.stringify({runStatus: e.currentTarget.checked}),
                success: (response) => {
                    if(response.status) {
                        if(e.currentTarget.checked) {
                            $.get(ENV.vApi + "dial_queue/createDialQueue/" + Config.id);
                        }
                        notification.show("@Change status@ @success@", "success");
                    } else notification.show("@Change status@ @error@", "error");
                },
                error: errorDataSource
            })
        } 
    });
    kendo.bind($(".statistic-view"), statisticObservable);
    setTimeout(() => {
        updateStatistic();
        detailTable.dataSource.read();
    }, 10000);
}

if(typeof window.intervalCurrentCallInQueue == "undefined") {
    window.intervalCurrentCallInQueue = setInterval(() => {
        var $select = $(".time-interval[data-date-time]");
        var d = new Date(); d.setHours(0);
        if($select.length) {
            for (var i = 0; i < $select.length; i++) {
                var dateTime = $select[i].dataset.dateTime,
                    timeText = kendo.toString(new Date(d - new Date(dateTime)), 'mm:ss');
                $select[i].innerText = timeText;
            }
        } 
    }, 1000);
}
</script>

<script type="text/javascript">
	window.onload = async function() {
		detailTable.init();
		updateStatistic();
	}
</script>
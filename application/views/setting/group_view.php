<script>
var Config = {
    crudApi: `${ENV.restApi}`,
    templateApi: `${ENV.templateApi}`,
    collection: "group",
    observable: {
    	trueVar: true,
    },
    model: {
        id: "id",
        fields: {
            name: {type: "string", defaultValue: ""},
            queue: {type: "string"},
            members: {type: "object"},
            queues: {type: "object"}
        }
    }
}; 
</script>

<!-- Table Styles Header -->
<ul class="breadcrumb breadcrumb-top">
    <li>@Setting@</li>
    <li>@Group@</li>
</ul>
<!-- END Table Styles Header -->

<div class="container-fluid" id="allview" data-bind="css: {editable: editable}">
    <div class="row">
        <div class="col-md-6" style="border-right: 1px solid lightgray; padding-right: 30px">
            <div class="row">
                <h4 class="text-center" style="margin: 20px 0 10px">
                    <span style="font-weight: 500">QUEUE @CALL CENTER@</span>
                    <a style="font-size: 14px" data-role="button" data-icon="refresh" data-bind="click: updateQueueMembers"><b>@Update@</b></a>
                </h4>
            </div>
            <div class="row">
                <div class="text-center col-xs-12" style="margin: 20px 0 10px">
                    <input id="filter-queue" class="k-textbox" style="width: 100%" />
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <!-- Table Styles Content -->
                    <div data-role="listview" id="listview"
                     data-template="queue-group-template"
                     data-bind="source: dataSourceQueue"></div>
                    <!-- END Table Styles Content -->
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="row">
                <h4 class="text-center" style="margin: 20px 0 10px">
                    <span style="font-weight: 500">@GROUP@ @CUSTOM@</span>
                </h4>
            </div>
            <div class="row">
                <div class="text-center col-xs-12" style="margin: 20px 0 10px">
                    <input id="filter-custom-group" class="k-textbox" style="width: 100%" />
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <!-- Table Styles Content -->
                    <div data-role="listview" id="listview2"
                     data-template="custom-group-template"
                     data-bind="source: dataSourceCustom"></div>
                    <!-- END Table Styles Content -->
                </div>
                <div class="col-xs-12 text-center">
                    <button data-role="button" data-icon="add" onclick="openForm({title: `@Add@ @Custom group@`,width: 500}); addForm(this)" href="javascript:void(0)" class="btn btn-sm"><b>@Add@ @new@</b></button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="action-menu" style="width: 200px">
    <ul>
        <a href="javascript:void(0)" data-type="update" onclick="editQueueMembers(this)"><li><i class="fa fa-pencil-square-o text-warning"></i><span>@Edit@ @Members@</span></li></a>
        <a class="hidden" href="javascript:void(0)" data-type="delete" onclick="deleteDataItem(this)"><li><i class="fa fa-times-circle text-danger"></i><span>@Delete@</span></li></a>
    </ul>
</div>
<!-- END Page Content -->
<!-- <input type="checkbox" data-bind="checked: default"> -->
<script id="queue-group-template" type="text/x-kendo-template">
    <div class="view-container">
        <span class="check-active">
            <i class="fa fa-users text-primary"></i>
        </span>
        <span class="group-name" data-bind="text: name, invisible: isEdit"></span>
        <input class="k-textbox" data-bind="value: name, visible: isEdit">
        <div class="pull-right">
        	<a class="k-button btn-edit" href="javascript:void(0)" data-bind="click: editQueue, attr: {data-uid: uid}, invisible: isEdit">
                <i class="fa fa-pencil-square-o text-warning"></i>&nbsp;
                <b>@Edit@</b>
            </a>
            <a class="k-button" href="javascript:void(0)" data-bind="click: saveQueue, attr: {data-uid: uid}, visible: isEdit">
                <i class="fa fa-floppy-o text-success" data-bind="visible: isEdit"></i>&nbsp;
                <b>@Save@</b>
            </a>
            <a class="k-button" href="javascript:void(0)" data-bind="click: cancelQueue, attr: {data-uid: uid}, visible: isEdit">
                <i class="gi gi-ban text-muted" data-bind="visible: isEdit"></i>&nbsp;
                <b>@Cancel@</b>
            </a>
        </div>
        <br><br>
        <label>Queue: </label>
        <span class="label label-info" data-bind="text: queuename" style="font-size: 16px"></span>
        <br>
        <label>@Link@ @with@ @group@: </label>
        <span data-bind="invisible: isEdit" class="member-array">#= gridCustomGroups(data.customGroups) #</span>
        <select data-role="multiselect" multiple="multiple"
            data-filter="contains"
            data-text-field="name"
            data-value-field="id"
            data-value-primitive="true"            
            data-bind="value: customGroups, source: relatedDataSourceCustom, visible: isEdit, events: {select: customGroupsSelect}" 
            style="width: 100%"></select>
        <br>
        <label>@Members@: </label>
        <span data-bind="invisible: isEdit" class="member-array">#= gridMembers(data.members) #</span>
        <div data-bind="visible: isEdit">
            <select data-role="multiselect" 
            data-text-field="extension"
            data-value-field="extension"
            data-item-template="itemGroupTemplate"
            data-tag-template="tagGroupTemplate"
            data-clear-button="false"
            data-value-primitive="true"
            data-auto-bind="false"  
            data-bind="value: members, source: membersOption, events: {select: membersSelect, deselect: membersDeselect}"></select>
        </div>
    </div>
</script>
<script id="custom-group-template" type="text/x-kendo-template">
    <div class="view-container">
        <span class="check-active">
            <i class="fa fa-creative-commons text-primary"></i>
        </span>
        <span class="group-name" data-bind="text: name"></span>
        <div class="pull-right">
            <a class="k-button btn-edit" href="javascript:void(0)" data-bind="click: editCustomGroup, attr: {data-uid: uid}">
                <i class="fa fa-pencil-square-o text-warning"></i>&nbsp;
                <b>@Edit@</b>
            </a>
            <a class="k-button btn-delete" href="javascript:void(0)" onclick="deleteDataItem(this)" data-bind="attr: {data-uid: uid}">
                <i class="fa fa-times-circle text-danger"></i>&nbsp;
                <b>@Delete@</b>
            </a>
        </div>
        <br><br>
        <label>@Lead@: </label>
        <span class="member-array">#= gridMembers([data.lead]) #</span>
        <br>
        <label>@Active@: </label>
        <span>
            <i class="fa fa-check text-success" data-bind="visible: active"></i>
            <i class="fa fa-times text-danger" data-bind="invisible: active"></i>
        </span>
        <br>
        <label>@Members@: </label>
        <span class="member-array">#= gridMembers(data.members) #</span>
    </div>
</script>
<style type="text/css">
    #allview:not(.editable) .btn-edit {
        display: none;
    }
    .k-widget.k-listview {
        background-color: inherit;
    }
    .view-container {
    	border-radius: 5px;
        border: 1px solid lightgray;
        padding: 10px 20px;
        width: 95%;
        background-color: white;
        margin-bottom: 5px;
    }
    .view-container span {
        font-size: 18px;
    }
    [data-role=listview] {
        border: 0;
    }
    .queue-array span, .member-array span {
		font-size: 12px;
		vertical-align: -3px;
	}
	.check-active {
	}

    .dropdown-header {
        border-width: 0 0 1px 0;
        text-transform: uppercase;
    }

    .dropdown-header > span {
        display: inline-block;
        padding: 10px;
    }

    .selected-value {
        display: inline-block;
        vertical-align: middle;
        width: 18px;
        height: 18px;
        background-size: 100%;
        margin-right: 5px;
        border-radius: 50%;
    }

    .member-element {
        display: inline-block; 
        border: 1px solid ghostwhite; 
        border-radius: 5px; 
        padding: 4px; 
        font-size: 14px;
        background-color: lightgray;
    }
</style>

<script id="itemGroupTemplate" type="text/x-kendo-template">
    <span class="selected-value" style="background-image: url('/api/v1/avatar/agent/#: data.extension #')"></span><span><b>#: (data.extension || "") #</b> (#:data.agentname#)</span>
</script>

<script id="tagGroupTemplate" type="text/x-kendo-template">
    <span class="selected-value" style="background-image: url('/api/v1/avatar/agent/#: data.extension #')"></span><span><b>#: (data.extension || "") #</b> (#:data.agentname#)</span>
</script>

<script type="text/javascript">

    var List = function() {
        return {
            dataSourceQueue: {},
            dataSourceCustom: {},
            relatedDataSourceCustom: {},
            listview: {},
            columns: Config.columns,
            init: function() {
                var dataSourceQueue = this.dataSourceQueue = new kendo.data.DataSource({
                    filter: {field: "type", operator: "eq", value: "queue"},
                    serverFiltering: true,
                    serverPaging: true,
                    serverSorting: true,
                    serverGrouping: false,
                    pageSize: 100,
                    batch: false,
                    schema: {
                        data: function(response) {
                            return response.data;
                        },
                        total: "total",
                        model: Config.model,
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
                            type: "DELETE"
                        },
                        parameterMap: parameterMap
                    },
                    sync: syncDataSource,
                    error: errorDataSource
                });

                var dataSourceCustom = this.dataSourceCustom = new kendo.data.DataSource({
                    filter: {field: "type", operator: "eq", value: "custom"},
                    serverFiltering: true,
                    serverPaging: true,
                    serverSorting: true,
                    serverGrouping: false,
                    pageSize: 100,
                    batch: false,
                    schema: {
                        data: function(response) {
                            return response.data;
                        },
                        total: "total",
                        model: Config.model,
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
                            type: "DELETE"
                        },
                        parameterMap: parameterMap
                    },
                    sync: syncDataSource,
                    error: errorDataSource
                });

                var relatedDataSourceCustom = this.relatedDataSourceCustom = new kendo.data.DataSource({
                    filter: {field: "type", operator: "eq", value: "custom"},
                    serverFiltering: true,
                    serverPaging: true,
                    serverSorting: true,
                    serverGrouping: false,
                    pageSize: 100,
                    batch: false,
                    schema: {
                        data: function(response) {
                            return response.data;
                        },
                        total: "total",
                        model: Config.model,
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
                            type: "DELETE"
                        },
                        parameterMap: parameterMap
                    },
                    sync: syncDataSource,
                    error: errorDataSource
                });

                var observable = this.observable = Object.assign({
                    dataSourceQueue: dataSourceQueue,
                    dataSourceCustom: dataSourceCustom,
                    relatedDataSourceCustom: relatedDataSourceCustom,
                }, Config.observable)

                kendo.bind($("#allview"), observable)

                /*
                 * Right Click Menu
                 */
                var menu = $("#action-menu");

                $("html").on("click", function() {menu.hide()});

                /*$(document).on("click", "#listview a.btn-action", function(e){
                    let row = $(e.target).closest("div.view-container");
                    e.pageX -= 20;
                    showMenu(e, row);
                });*/

                function showMenu(e, that) {
                    //hide menu if already shown
                    menu.hide(); 

                    //Get id value of document
                    var uid = $(that).data('uid');
                    if(uid)
                    {
                        menu.find("a[data-type=read], a[data-type=update], a[data-type=delete]").data('uid',uid);

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
        Config.observable = Object.assign({
            editable: true,
            typeOption: ["queue", "custom"],
            typeChange: function(e) {
                let type = e.sender.value();
                if(type == "queue") {
                    this.set("visibleQueues", true);
                    this.set("visibleMembers", false);
                } else {
                    this.set("visibleQueues", false);
                    this.set("visibleMembers", true);
                    this.membersOption.read();
                }
            },
            queuesOption: new kendo.data.DataSource({
                transport: {
                    read: ENV.vApi + "select/queues",
                    parameterMap: parameterMap
                },
                schema: {
                    data: "data"
                }
            }),
            editQueue: function(e) {
                var uid = $(e.currentTarget).data("uid"),
                    dataItem = List.dataSourceQueue.getByUid(uid);
                this.set("selectedQueue", dataItem.queuename);
                dataItem.set("isEdit", true);
                this.set("editable", false);
                this.membersOption.read();
            },
            leadOption: dataSourceDropDownListPrivate("User", ["extension", "agentname"], {role_name: {$ne: "Agent"}}, function(res) {
                        res.data.unshift({extension: null, agentname: "@None@"});
                        return res;
            }),
            membersOption: new kendo.data.DataSource({
                transport: {
                    read: ENV.vApi + "select/queuemembers",
                    parameterMap: parameterMap
                },
                schema: {
                    data: "data",
                    total: "total"
                }
            }),
            customGroupsSelect: function(e) {
                var queuename = this.get("selectedQueue");
                var members = e.dataItem.members.toJSON();
                members.forEach(extension => {
                    $.ajax({
                        url: ENV.vApi + "wfpbx/change_queue_member/add",
                        data: JSON.stringify({extension: extension, queuename: queuename}),
                        contentType: "application/json; charset=utf-8",
                        type: "POST",
                        success: function(res) {
                            notification.show(res.message, res.status ? "success" : "error");
                        }
                    })
                })
            },
            membersSelect: function(e) {
                var extension = e.dataItem.extension,
                    agentname = e.dataItem.agentname,
                    queuename = this.get("selectedQueue"),
                    members = e.sender.value();
                swal({
                    title: `@Are you sure@?`,
                    text: `@Add@ ${extension} (${agentname}) @at@ queue ${queuename}`,
                    icon: "warning",
                    buttons: true,
                    dangerMode: false,
                })
                .then((sure) => {
                    if (sure) {
                        $.ajax({
                            url: ENV.vApi + "wfpbx/change_queue_member/add",
                            data: JSON.stringify({extension: extension, queuename: queuename}),
                            contentType: "application/json; charset=utf-8",
                            type: "POST",
                            success: function(res) {
                                if(res.status) {
                                    notification.show(`@Add@ ${extension} (${agentname}) @at@ queue ${queuename}`, "success");
                                } else {
                                    e.sender.value(members);
                                    notification.show(res.message, "error");
                                }
                            }
                        })  
                    }
                });
            },
            membersDeselect: function(e) {
                var extension = e.dataItem.extension,
                    agentname = e.dataItem.agentname,
                    queuename = this.get("selectedQueue"),
                    members = e.sender.value();
                swal({
                    title: `@Are you sure@?`,
                    text: `@Remove@ ${extension} (${agentname}) @from@ queue ${queuename}`,
                    icon: "warning",
                    buttons: true,
                    dangerMode: false,
                })
                .then((sure) => {
                    if (sure) {
                        $.ajax({
                            url: ENV.vApi + "wfpbx/change_queue_member/remove",
                            data: JSON.stringify({extension: extension, queuename: queuename}),
                            contentType: "application/json; charset=utf-8",
                            type: "POST",
                            success: function(res) {
                                if(res.status) {
                                    notification.show(`@Remove@ ${extension} (${agentname}) @from@ queue ${queuename}`, "success");
                                } else {
                                    e.sender.value(members);
                                    notification.show(res.message, "error");
                                }
                            }
                        }) 
                    }
                });
            },
            saveQueue: function(e) {
                var uid = $(e.currentTarget).data("uid"),
                    dataItem = List.dataSourceQueue.getByUid(uid);
                if(dataItem.dirtyFields.name || dataItem.dirtyFields.customGroups) {
                    swal({
                        title: `@Are you sure@?`,
                        text: `@Save@ @this change@`,
                        icon: "warning",
                        buttons: true,
                        dangerMode: false,
                    })
                    .then((sure) => {
                        if (sure) {
                            $.ajax({
                                url: ENV.restApi + "group/" + dataItem.id,
                                data: JSON.stringify({name: dataItem.name, customGroups: dataItem.customGroups}),
                                contentType: "application/json; charset=utf-8",
                                type: "PUT",
                                success: (res) => {
                                    if(res.status) {
                                        this.set("editable", true);
                                        notification.show(`@Success@`, "success");
                                    } else notification.show(res.message, "error");
                                }
                            })
                        }
                    });
                } else {
                    List.dataSourceQueue.read();
                    this.set("editable", true);
                } 
            },
            cancelQueue: function(e) {
                var dataItem = List.dataSourceQueue.getByUid($(e.currentTarget).data("uid"));
                dataItem.set("isEdit", false);
                this.set("editable", true);
            },
            editCustomGroup: function(e) {
                openForm({title: "@Edit@ @Custom group@", width: 500});
                editForm(e.currentTarget)
            },
            updateQueueMembers: function(e) {
                $.get({
                    url: ENV.vApi + "wfpbx/updateQueueMembers",
                    timeout: 10000, 
                    success: (res) => {
                        notification.show(res.message, res.status ? "success" : "error");
                        this.dataSourceQueue.read();
                    }
                })
            },
        }, Config.observable);

        List.init();

        var inputQueue = document.getElementById("filter-queue");
        // Execute a function when the user releases a key on the keyboard
        inputQueue.addEventListener("keyup", function(event1) {
            $("#listview").data('kendoListView').dataSource.filter({
                logic: 'and',
                filters: [{
                    logic: 'or',
                    filters:[
                        {
                            field: 'queuename',
                            operator: 'contains',
                            value: inputQueue.value,
                            ignoreCase: true
                        }, 
                        {
                            field: 'queues',
                            operator: 'in',
                            value: inputQueue.value
                        },
                        {
                            field: 'members',
                            operator: 'in',
                            value: inputQueue.value
                        }
                    ]
                }, {
                    field: "type", 
                    operator: "eq", 
                    value: "queue"
                }]
            });
        });

        var inputCustom = document.getElementById("filter-custom-group");
        // Execute a function when the user releases a key on the keyboard
        inputCustom.addEventListener("keyup", function(event) {
            $("#listview2").data('kendoListView').dataSource.filter({
                logic: 'and',
                filters: [{
                    logic: 'or',
                    filters:[
                        {
                            field: 'name',
                            operator: 'contains',
                            value: inputCustom.value,
                            ignoreCase: true
                        }, 
                        {
                            field: 'members',
                            operator: 'in',
                            value: inputCustom.value
                        },
                        {
                            field: 'lead',
                            operator: 'eq',
                            value: inputCustom.value
                        }
                    ]
                }, {
                    field: "type", 
                    operator: "eq", 
                    value: "custom"
                }]
            });
        });
    }

    function gridCustomGroups(data = []) {
        var template = [];
        if(data && data.length) {
            template = $.map($.makeArray(data), function(value, index) {
                return `<img src="/api/v1/group/getImageNameById/${value}" style="height: 18px; padding: 2px; background-color: ghostwhite" class="img-thumbnail">`;
            });
        }
        return template.join(' ');
    }

    function gridMembers(data = []) {
        var bs_color = HELPER.bsColors,
            template = [];
        if(data && data.length) {
            template = $.map($.makeArray(data), function(value, index) {
                return "<div class=\"member-element\"><span class=\"selected-value\" style=\"background-image: url('/api/v1/avatar/agent/"+value+"')\"></span><b>"+value+"</b> ("+convertExtensionToAgentname[value]+")</div>";
            });
        }
        return template.join(' ');
    }

    async function editForm(ele) {
        var dataItem = List.dataSourceCustom.getByUid($(ele).data("uid")),
            formHtml = await $.ajax({
                url: Config.templateApi + Config.collection + "/form",
                error: errorDataSource
            });

        dataItem.linkToQueues = await $.get(ENV.vApi + Config.collection + "/getQueuesLinkToGroupId/" + dataItem.id);
        var model = Object.assign(Config.observable, {
            item: dataItem,
            membersCustomSelect: function(e) {
                var extension = e.dataItem.extension,
                    agentname = e.dataItem.agentname,
                    queues = this.get("item.linkToQueues"),
                    members = e.sender.value();

                if(queues && queues.length) {
                    queues.forEach(queuename => {
                        $.ajax({
                            url: ENV.vApi + "wfpbx/change_queue_member/add",
                            data: JSON.stringify({extension: extension, queuename: queuename}),
                            contentType: "application/json; charset=utf-8",
                            type: "POST",
                            success: (res) => {
                                if(res.status) {
                                    notification.show(`@Add@ ${extension} (${agentname}) @at@ queue ${queuename}`, "success");
                                    List.dataSourceQueue.read();
                                }
                            }
                        }) 
                    })
                }
                setTimeout(() => List.dataSourceCustom.sync(), 100);
            },
            membersCustomDeselect: function(e) {
                var extension = e.dataItem.extension,
                    agentname = e.dataItem.agentname,
                    queues = this.get("item.linkToQueues"),
                    members = e.sender.value();

                if(queues && queues.length) {
                    queues.forEach(queuename => {
                        $.ajax({
                            url: ENV.vApi + "wfpbx/change_queue_member/remove",
                            data: JSON.stringify({extension: extension, queuename: queuename}),
                            contentType: "application/json; charset=utf-8",
                            type: "POST",
                            success: (res) => {
                                if(res.status) {
                                    notification.show(`@Remove@ ${extension} (${agentname}) @from@ queue ${queuename}`, "success");
                                    List.dataSourceQueue.read();
                                }
                            }
                        })
                    })
                }
                setTimeout(() => List.dataSourceCustom.sync(), 100);
            },
            save: function() {
                List.dataSourceCustom.sync().then(() => {List.dataSourceCustom.read()});
                closeForm();
            },
            cancel: function() {
                List.dataSourceCustom.read();
                closeForm();
            }
        });
        kendo.destroy($("#right-form"));
        $("#right-form").empty();
        var kendoView = new kendo.View(formHtml, { wrap: false, model: model, evalTemplate: false });
        kendoView.render($("#right-form"));
    }

    async function addForm() {
        var formHtml = await $.ajax({
            url: Config.templateApi + Config.collection + "/form",
            error: errorDataSource
        });
        var model = Object.assign(Config.observable, {
            item: {type: "custom"},
            membersCustomSelect: () => {},
            membersCustomDeselect: () => {},
            save: function() {
                List.dataSourceCustom.add(this.item);
                List.dataSourceCustom.sync().then(() => {List.dataSourceCustom.read()});
                closeForm();
            }
        });
        kendo.destroy($("#right-form"));
        $("#right-form").empty();
        var kendoView = new kendo.View(formHtml, { wrap: false, model: model, evalTemplate: false });
        kendoView.render($("#right-form"));
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
                var dataItem = List.dataSourceCustom.getByUid(uid);
                List.dataSourceCustom.remove(dataItem);
                List.dataSourceCustom.sync();
            }
        });
    }
</script>
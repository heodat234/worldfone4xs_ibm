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

<!-- Page content -->
<div id="page-content">
    <!-- Table Styles Header -->
    <ul class="breadcrumb breadcrumb-top">
        <li>@Setting@</li>
        <li>@Group@</li>
        <li class="pull-right none-breakcrumb">
            <a role="button" onclick="openForm({title: `@Add@ @Group@`,width: 400}); addForm(this)" href="javascript:void(0)" class="btn btn-sm"><b>@Add@</b></a>
        </li>
    </ul>
    <!-- END Table Styles Header -->

    <div class="container-fluid">
        <h4 class="fieldset-legend" style="margin: 10px 0 30px"><span style="font-weight: 500">@LIST@ @GROUP@ @CALL CENTER@</span></h4>
        <div class="row">
            <div class="col-sm-12" style="height: 80vh; overflow-y: auto; padding: 0">
                <!-- Table Styles Content -->
                <div data-role="listview" id="listview"
                 data-template="template"
                 data-bind="source: dataSource"></div>
                <!-- END Table Styles Content -->
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
    <script id="template" type="text/x-kendo-template">
        <div class="view-container">
            <span class="check-active">
                <i class="fa fa-users text-muted"></i>
            </span>
            <span class="group-name" data-bind="text: name, invisible: isEdit"></span>
            <input class="k-textbox" data-bind="value: name, visible: isEdit">
            <div class="pull-right">
            	<a href="javascript:void(0)" class="btn-action" data-bind="click: toggleEditQueue, attr: {data-uid: uid}, invisible: isEdit"><i class="fa fa-pencil-square-o text-warning fa-2x"></i></a>
                <i class="gi gi-floppy_save fa-2x text-success" data-bind="visible: isEdit"></i>
            </div>
            <br><br>
            <label>Queue: </label>
            <span class="queue-array">
                <span class="label label-info" data-bind="text: queuename"></span>
            </span>
            <br>
            <label>@Members@: </label>
            <span  data-bind="invisible: isEdit" class="member-array">#= gridArray(data.members) #</span>
            <div data-bind="visible: isEdit">
                <select data-role="multiselect" 
                data-text-field="agentname"
                data-value-field="extension"
                data-item-template="itemGroupTemplate"
                data-tag-template="tagGroupTemplate"
                data-value-primitive="true"  
                data-bind="value: members, source: membersOption, events: {select: membersSelect, deselect: membersDeselect}"></select>
            </div>
        </div>
    </script>
    <style type="text/css">
        .view-container {
        	border-radius: 5px;
            border: 1px solid lightgray;
            padding: 10px 20px;
            margin: 10px;
            width: 320px;
            float: left;
        }
        .view-container span {
            font-size: 20px;
        }
        #listview {
            border: 0;
        }
        .queue-array span, .member-array span {
    		font-size: 12px;
    		vertical-align: 2px;
    	}
    	.check-active {
    		border-radius: 7px;
    		border: 1px dashed gray;
    		padding: 1px 3px;
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
    </style>
</div>

<script id="itemGroupTemplate" type="text/x-kendo-template">
    <span class="selected-value" style="background-image: url('/api/v1/avatar/agent/#: data.extension #')"></span><span><b>#: data.extension #</b> (#:data.agentname#)</span>
</script>

<script id="tagGroupTemplate" type="text/x-kendo-template">
    <span class="selected-value" style="background-image: url('/api/v1/avatar/agent/#: data.extension #')"></span><span><b>#: data.extension #</b> (#:data.agentname#)</span>
</script>

<script type="text/javascript">

    var List = function() {
        return {
            dataSource: {},
            listview: {},
            columns: Config.columns,
            init: function() {
                var dataSource = this.dataSource = new kendo.data.DataSource({
                    filter: {field: "type", operator: "eq", value: "queue"},
                    serverFiltering: true,
                    serverPaging: true,
                    serverSorting: true,
                    serverGrouping: false,
                    pageSize: 10,
                    batch: false,
                    schema: {
                        data: "data",
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
                    dataSource: dataSource
                }, Config.observable)

                kendo.bind($("#listview"), observable)

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
            toggleEditQueue: function(e) {
                var uid = $(e.currentTarget).data("uid"),
                    dataItem = List.dataSource.getByUid(uid);
                this.set("selectedQueue", dataItem.queuename);
                dataItem.set("isEdit", !dataItem.get("isEdit"));
            },
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
            membersSelect: function(e) {
                /*$.ajax({
                    url: ENV.vApi + "wfpbx/addExtensionToQueue",
                    data: {extension: e.dataItem.extension,}
                })*/
                notification.show(`@Add@ ${e.dataItem.extension} (${e.dataItem.agentname}) @to@ queue ${this.get("selectedQueue")}`);
            },
            membersDeselect: function(e) {
                notification.show(`@Remove@ ${e.dataItem.extension} (${e.dataItem.agentname}) @from@ queue ${this.get("selectedQueue")}`);
            },
            queuesChange: function(e) {
                var queues = e.sender.value();
                this.set("visibleMembers", Boolean(queues.length));
                this.membersOption.read({queues: queues}).then(() => {
                    let data = this.membersOption.data();
                    this.set("item.members", data);
                })
            }
        }, Config.observable);

        List.init();
   
    }

    async function editForm(ele) {
        var dataItem = List.dataSource.getByUid($(ele).data("uid")),
            formHtml = await $.ajax({
                url: Config.templateApi + Config.collection + "/form",
                error: errorDataSource
            });
        var model = Object.assign(Config.observable, {
            visibleMembers: Boolean(dataItem.members),
            disabled: Boolean(dataItem.type == "queue"),
            item: dataItem,
            save: function() {
                List.dataSource.sync().then(() => {List.dataSource.read()});
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
            visibleMembers: true,
            // disabled: true,
            // item: {type: "custom"},
            save: function() {
                List.dataSource.add(this.item);
                List.dataSource.sync().then(() => {List.dataSource.read()});
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
                var dataItem = List.dataSource.getByUid(uid);
                List.dataSource.remove(dataItem);
                List.dataSource.sync();
            }
        });
    }
</script>
<script>
var Config = {
    crudApi: `${ENV.restApi}`,
    templateApi: `${ENV.templateApi}`,
    collection: "activity_definition",
    model: {
        id: "id",
        fields: {
            uri: {defaultValue: ""},
        	directory: {defaultValue: ""},
        	class: {defaultValue: ""},
        	function: {defaultValue: ""},
        	method: {defaultValue: "get"},
            definition: {defaultValue: ""},
        }
    }
}; 

window.onload = function() {
    var pageObservable = window.pageObservable = kendo.observable({
        activityViewDataSource: new kendo.data.DataSource({
            serverSorting: true,
            serverPaging: true,
            serverFiltering: true,
            pageSize: 10,
            sort: [{field: "createdAt", dir: "desc"}],
            transport: {
                read: ENV.reportApi + "activity/read",
                parameterMap: parameterMap
            },
            schema: {
                data: "data",
                total: "total",
                model: {
                    id: "id",
                    fields: {
                        createdAt: {type: "date"},
                        elapsed_time: {type: "number"},
                        memory_usage: {type: "number"},
                    }
                },
                parse: function(res) {
                    res.data.map(doc => doc.createdAt = new Date(doc.createdAt * 1000))
                    return res;
                }
            }
        }),
        activityLogDataSource: new kendo.data.DataSource({
            serverSorting: true,
            serverPaging: true,
            serverFiltering: true,
            pageSize: 10,
            sort: [{field: "createdAt", dir: "desc"}],
            transport: {
                read: ENV.restApi + "activity_log",
                parameterMap: parameterMap
            },
            schema: {
                data: "data",
                total: "total",
                model: {
                    id: "id",
                    fields: {
                        createdAt: {type: "date"},
                        elapsed_time: {type: "number"},
                        memory_usage: {type: "number"},
                        ajaxs_elapsed_time: {type: "number"},
                        ajaxs_memory_usage: {type: "number"},
                    }
                },
                parse: function(res) {
                    res.data.map(doc => doc.createdAt = new Date(doc.createdAt * 1000))
                    return res;
                }
            }
        }),
        activityDefineDataSource: new kendo.data.DataSource({
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
            error: errorDataSource,
            change: function(e) {
                if(e.action == "sync") {
                    e.sender.read();
                }
            }
        }),
        activateTabstrip: function (e) {
            var collection =  $(e.item).data("collection");
            if(collection) {
                if(collection == "Activity_log") {
                    this.activityLogDataSource.read();
                } else {
                    this.activityDefineDataSource.read();
                }
                $(e.item).data("collection", "");
            }
        }
    });
    kendo.bind($(".mvvm"), pageObservable);
    $("#grid").data("kendoGrid").bind("detailInit",  function(e) {
        kendo.bind($(e.detailCell), e.data)
        $(e.detailCell).find("[data-role=grid]").data("kendoGrid").dataSource.sort({field: 'createdAt', dir: 'desc'})
    });
    $("#activity-log-grid").data("kendoGrid").bind("detailInit",  function(e) {
        kendo.bind($(e.detailCell), e.data)
    });
}

function clickDefine(e) {
    // e.target is the DOM element representing the button
    var tr = $(e.target).closest("tr");
    // get the data bound to the current table row
    var data = this.dataItem(tr);
    swal({
        title: 'Define this view.',
        text: `${data.method.toUpperCase()} ${data.uri}`,
        content: "input",
        cancel: true,
        button: {
            text: "Define!",
            closeModal: true
        },
    })
    .then(definition => {
        if (!definition) return;
        pageObservable.activityDefineDataSource.add({
            uri: data.uri,
            directory: data.directory,
            class: data.class,
            function: data.function,
            method: data.method,
            definition: definition
        });
        pageObservable.activityDefineDataSource.sync();
    })
}

function clickDefineAjax(e) {
    // e.target is the DOM element representing the button
    var tr = $(e.target).closest("tr");
    // get the data bound to the current table row
    var data = this.dataItem(tr);
    swal({
        title: 'Define this action.',
        text: `${data.method.toUpperCase()} ${data.uri}`,
        content: "input",
        cancel: true,
        button: {
            text: "Define!",
            closeModal: true
        },
    })
    .then(definition => {
        if (!definition) return;
        pageObservable.activityDefineDataSource.add({
            uri: data.uri,
            directory: data.directory,
            class: data.class,
            function: data.function,
            method: data.method,
            definition: definition
        });
        pageObservable.activityDefineDataSource.sync();
    })
}
</script>

<!-- Table Styles Header -->
<ul class="breadcrumb breadcrumb-top">
    <li>Admin</li>
    <li>Activities</li>
</ul>
<!-- END Table Styles Header -->
<div id="tabstrip" class="mvvm" data-role="tabstrip" data-bind="events: {activate: activateTabstrip}">
    <ul>
        <li class="k-state-active">
            Activity view
        </li>
        <li data-collection="Activity_log">
            Activity log
        </li>
        <li data-collection="Activity_definition">
            Activity definition
        </li>
    </ul>
    <div>
    	<div class="container-fluid">
            <div class="row">
            	<div class="col-sm-12" style="padding: 0">
                    <!-- Table Styles Content -->
                    <div data-role="grid" id="grid"
                    data-sortable="true"
                    data-pageable="{refresh: true, input: true, pageSizes: [10,20,50,100]}"
                    data-filterable="{extra: true}"
                    data-detail-template="ajaxs-template"
                    data-columns="[
                    	{field: 'extension', title: 'Extension'},
                        {field: 'agentname', title: 'Agent name'},
                    	{field: 'uri', title: 'Uri'},
                    	{field: 'definition', title: 'Definition', filterable: false},
                    	{field: 'createdAt', title: 'At', format: '{0: dd/MM/yy HH:mm:ss}'},
                        {
                            title: 'Action',
                            command: {name: 'define', text: 'Define', click: clickDefine},
                            width: 100
                        }
                    ]"
                    data-bind="source: activityViewDataSource"></div>
                    <!-- END Table Styles Content -->
                </div>
            </div>
        </div>
    </div>
    <div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-12" style="padding: 0">
                    <!-- Table Styles Content -->
                    <div data-role="grid" id="activity-log-grid"
                    data-auto-bind="false"
                    data-sortable="true"
                    data-pageable="{refresh: true, input: true, pageSizes: [10,20,50,100]}"
                    data-filterable="{extra: true}"
                    data-detail-template="log-ajaxs-template"
                    data-columns="[{
                        field: 'createdAt',
                        title: 'Time',
                        format: '{0: dd/MM/yy HH:mm:ss}',
                        width: 120
                    },{
                        field: 'extension',
                        title: 'Extension',
                        width: 90
                    },{
                        field: 'directory',
                        title: 'Directory',
                    },{
                        field: 'class',
                        title: 'Class',
                        width: 80
                    },{
                        field: 'function',
                        title: 'Function',
                        width: 80
                    },{
                        field: 'method',
                        title: 'Method',
                        width: 80
                    },{
                        field: 'uri',
                        title: 'Uri',
                    },{
                        field: 'elapsed_time',
                        title: 'Elapsed time',
                        width: 120
                    },{
                        field: 'memory_usage',
                        title: 'Memory',
                        width: 100
                    },{
                        field: 'ajaxs_elapsed_time',
                        title: 'Ajaxs time',
                        width: 110
                    },{
                        field: 'ajaxs_memory_usage',
                        title: 'Ajaxs mem',
                        width: 110
                    }]"
                    data-bind="source: activityLogDataSource"></div>
                    <!-- END Table Styles Content -->
                </div>
            </div>
        </div>
    </div>
    <div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-12" style="padding: 0">
                    <!-- Table Styles Content -->
                    <div data-role="grid"
                    data-toolbar="['excel','pdf']"
                    data-auto-bind="false"
                    data-sortable="true"
                    data-toolbar="[{name: 'create'}]"
                    data-editable="inline"
                    data-pageable="{refresh: true, input: true, pageSizes: [10,20,50,100]}"
                    data-filterable="{extra: true}"
                    data-columns="[{
                        field: 'directory',
                        title: 'Directory',
                    },{
                        field: 'class',
                        title: 'Class',
                        width: 100
                    },{
                        field: 'function',
                        title: 'Function',
                        width: 100
                    },{
                        field: 'uri',
                        title: 'Uri',
                    },{
                        field: 'method',
                        title: 'Method',
                        width: 100
                    },{
                        field: 'definition',
                        title: 'Definition',
                    },{
                        field: 'type',
                        title: 'Type',
                    },{
                        title: 'Action',
                        command: [{name: 'edit', text: 'Edit'}, {name: 'destroy', text: 'Delete'}],
                        width: 170
                    }]"
                    data-bind="source: activityDefineDataSource"></div>
                    <!-- END Table Styles Content -->
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/x-kendo-template" id="ajaxs-template">
    <div data-role="grid" data-bind="source: ajaxs"
        data-sortable="true"
        data-pageable="{input: true, pageSize: 5, pageSizes: [5,10,20]}"
        data-filterable="{extra: true}"
        data-columns="[{
            field: 'uri',
            title: 'Uri',
        },{
            field: 'method',
            title: 'Method',
            width: 120
        },{
            field: 'definition',
            title: 'Definition',
        },{
            field: 'createdAt',
            title: 'At',
            filterable: false,
            template: data => gridTimestamp(data.createdAt, 'dd/MM/yy HH:mm:ss')
        },{
            title: 'Action',
            command: {name: 'define-ajax', text: 'Define', click: clickDefineAjax},
            width: 100
        }]">
    </div>
</script>

<script type="text/x-kendo-template" id="log-ajaxs-template">
    <div data-role="grid" data-bind="source: ajaxs"
        data-toolbar="['excel','pdf']"
        data-sortable="true"
        data-pageable="{input: true, pageSize: 5, pageSizes: [5,10,20]}"
        data-filterable="{extra: true}"
        data-columns="[{
            field: 'uri',
            title: 'Uri',
        },{
            field: 'method',
            title: 'Method',
            width: 120
        },{
            field: 'elapsed_time',
            title: 'Elapsed time',
            width: 120
        },{
            field: 'memory_usage',
            title: 'Memory',
            width: 100
        },{
            field: 'createdAt',
            title: 'At',
            template: data => gridTimestamp(data.createdAt, 'dd/MM/yy HH:mm:ss')
        }]">
    </div>
</script>
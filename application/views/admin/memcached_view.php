<!-- Table Styles Header -->
<ul class="breadcrumb breadcrumb-top">
    <li>Admin</li>
    <li>Memcached</li>
</ul>
<!-- END Table Styles Header -->
<div class="container-fluid">
	 <div class="row" style="padding-top: 20px">
        <div class="col-md-12">
            <!-- Web Server Block -->
            <div class="block full">
                <!-- Web Server Title -->
                <div class="block-title">
                    <h2><strong>Statistic</strong></h2>
                    <div class="block-options pull-right">
                    	<a href="javascript:void(0)" class="btn btn-alt btn-sm btn-primary" onclick="List.dataSource.read()"><i class="fa fa-refresh"></i></a>
						<a href="javascript:void(0)" class="btn btn-alt btn-sm btn-primary" data-toggle="block-toggle-content"><i class="fa fa-arrows-v"></i></a>
						<a href="javascript:void(0)" class="btn btn-alt btn-sm btn-primary" data-toggle="block-toggle-fullscreen"><i class="fa fa-desktop"></i></a>
						<a href="javascript:void(0)" class="btn btn-alt btn-sm btn-primary" data-toggle="block-hide"><i class="fa fa-times"></i></a>
					</div>
                </div>
                <!-- END Web Server Title -->

                <div class="block-content">
                	<table class="table table-striped table-borderless table-vcenter">
	                    <tbody id="listview">
	                    </tbody>
	                </table>
                    <div class="text-center"><a href="javascript:void(0)" onclick="viewMore(this)">See more</a></div>
                </div>

            </div>
            <!-- END Web Server Block -->
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <!-- Web Server Block -->
            <div class="block full">
                <!-- Web Server Title -->
                <div class="block-title">
                    <h2><strong>Data</strong></h2>
                    <div class="block-options pull-right">
                    	<a href="javascript:void(0)" class="btn btn-alt btn-sm btn-primary" onclick="flushMemcached()"><i class="fa fa-paint-brush"></i></a>
                    	<a href="javascript:void(0)" class="btn btn-alt btn-sm btn-primary" onclick="Table.dataSource.read()"><i class="fa fa-refresh"></i></a>
						<a href="javascript:void(0)" class="btn btn-alt btn-sm btn-primary" data-toggle="block-toggle-content"><i class="fa fa-arrows-v"></i></a>
						<a href="javascript:void(0)" class="btn btn-alt btn-sm btn-primary" data-toggle="block-toggle-fullscreen"><i class="fa fa-desktop"></i></a>
						<a href="javascript:void(0)" class="btn btn-alt btn-sm btn-primary" data-toggle="block-hide"><i class="fa fa-times"></i></a>
					</div>
                </div>
                <!-- END Web Server Title -->

                <div class="block-content">
                	<div id="grid"></div>
                </div>

            </div>
            <!-- END Web Server Block -->
        </div>
    </div>
</div>
<script type="text/javascript">
var Config = {
    crudApi: `${ENV.reportApi}memcachestats/`,
    templateApi: `${ENV.templateApi}`,
    database: "",
    collection: "",
    observable: {
    },
    model: {
        id: "key"
    },
    columns: [
    	{
    		field: "key",
    		template: data => gridLongText(data.key)
    	}, 
    	{
    		field: "value",
    		template: data => {
    			if(typeof data.value == "object") {
    				return gridLongText(JSON.stringify(data.value));
    			} else return gridLongText(data.value);
    		}
    	},
    	{
    		field: "metadata",
    		template: data => {
    			var html = `Length: <b>${data.metadata[0]}</b> characters<br>Time expire: <b>${gridTimestamp(data.metadata[1], "dd/MM/yy H:mm:ss")}</b>`;
    			return html;
    		}
    	},
    	{
    		width: 100,
    		command: ["destroy"]
    	}
    ],
    filterable: KENDO.filterable,
    scrollable: true
}; 
var Table = function() {
    return {
        dataSource: {},
        columns: Config.columns,
        init: function() {
            var dataSource = this.dataSource = new kendo.data.DataSource({
                serverFiltering: false,
                serverPaging: false,
                serverSorting: false,
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
                    read: Config.crudApi + "read",
                    destroy: Config.crudApi + "delete",
                    parameterMap: function(options, operation) {
                    	if(operation == "destroy") {
                    		return {key: options.key};
                    	} else return {q: JSON.stringify(options)};
					}
                },
                sync: syncDataSource,
                error: errorDataSource
            });

            var grid = this.grid = $("#grid").kendoGrid({
                dataSource: dataSource,
                resizable: true,
                pageable: {
                    refresh: true,
                    pageSizes: true,
                    input: true,
                    messages: KENDO.pageableMessages ? KENDO.pageableMessages : {}
                },
                sortable: true,
                scrollable: Boolean(Config.scrollable),
                columns: this.columns,
                filterable: Config.filterable ? Config.filterable : true,
                editable: "inline",
                detailTemplate: kendo.template($("#detail-template").html()),
                detailInit:  function(e) {
                    var container = $(e.detailCell).find(".jsoneditor"); 
                    var options = {
                        mode: 'code',
                        modes: ['tree','code']
                    };
                    var jsonEditor = new JSONEditor(container[0], options);
                    jsonEditor.set(e.data);
                },
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
        }
    }
}();

var List = function() {
    return {
        dataSource: {},
        columns: [{field: "key", title: ""}, {field: "key", title: ""}],
        init: function() {
            var dataSource = this.dataSource = new kendo.data.DataSource({
                pageSize: 5,
                transport: {
                    read: Config.crudApi + "stats"
                },
                sync: syncDataSource,
                error: errorDataSource
            });

            var listview = this.listview = $("#listview").kendoListView({
                dataSource: dataSource,
                template: kendo.template($("#listview-template").html())
            }).data("kendoGrid");
        }
    }
}();

window.onload = function() {
	Table.init();
	List.init();
}

function viewMore(ele) {
    List.dataSource.pageSize(20);
    $(ele).hide();
}

function flushMemcached() {
	$.get(ENV.reportApi + "memcachestats/flush", function(res) {
		if(res.status) {
			notification.show("Clear all memcached", "success");
			Table.dataSource.read();
		} else errorDataSource();
	});
}
</script>

<script type="text/x-kendo-template" id="detail-template">
    <div class="jsoneditor" style="width: 100%; height: 400px;"></div>
</script>

<script type="text/x-kendo-template" id="listview-template">
    <tr>
        <td class="text-right" style="width: 50%">
            <span>#: key #</span>
        </td>
        <td class="text-left">
            <b>#: value #</b>
        </td>
    </tr>
</script>
<script>
var Config = {
    crudApi: `${ENV.vApi}`,
    templateApi: `${ENV.templateApi}`,
    collection: "ticketreport",
    observable: {
    },
    model: {
        id: "id",
    },
    parse: function(response) {
    	response.data.map(doc => {});
    	return response;
    },
    columns: [{
            field: "_id.source",
            title: "@Source@",
            footerTemplate: "Total"
        },{
            field: "total",
            title: "@Total@",
            aggregates: ["sum"],
            footerTemplate: "#: sum #"
        },{
            field: "open",
            title: "Open",
            aggregates: ["sum"],
            footerTemplate: "#: sum #"
        },{
            field: "pending",
            title: "Pending",
            aggregates: ["sum"],
            footerTemplate: "#: sum #"
        },{
            field: "close",
            title: "Close",
            aggregates: ["sum"],
            footerTemplate: "#: sum #"
        }],
    filterable: KENDO.filterable,
    aggregate: [
        { field: "total", aggregate: "sum" },
        { field: "open", aggregate: "sum"},
        { field: "pending", aggregate: "sum" },
        { field: "close", aggregate: "sum" }
    ]
}; 
</script>

<!-- Table Styles Header -->
<ul class="breadcrumb breadcrumb-top">
    <li>@Report@</li>
    <li>@Ticket by source@</li>
    <li class="pull-right none-breakcrumb">
        <a role="button" class="btn btn-sm" data-field="createdAt" onclick="customFilter(this, Table.dataSource)"><i class="fa fa-filter"></i> <b>@Custom Filter@</b></a>
        <div class="input-group-btn column-widget">
            <a role="button" class="btn btn-sm dropdown-toggle" data-toggle="dropdown" onclick="editColumns(this)"><i class="fa fa-calculator"></i> <b>@Edit Columns@</b></a>
            <ul class="dropdown-menu dropdown-menu-right" style="width: 300px">
                <li class="dropdown-header text-center">@Choose columns will show@</li>
                <li class="filter-container" style="padding-bottom: 15px">
                    <div class="form-horizontal" data-bind="source: columns" data-template="column-template"/>
                </li>
            </ul>
        </div>
        <a role="button" class="btn btn-sm" onclick="Table.grid.saveAsExcel()"><i class="fa fa-file-excel-o"></i> <b>@Export@</b></a>
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

<script type="text/javascript">
	window.onload = function() {
		Table.init();
	}
	
</script>
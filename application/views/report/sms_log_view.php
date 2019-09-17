<script>
var Config = {
    crudApi: `${ENV.restApi}`,
    templateApi: `${ENV.templateApi}`,
    collection: "sms_logs",
    observable: {
    },
    model: {
        id: "id",
        fields: {
            createdAt: {type: "date"},
            billduration: {type: "number"},
            show_popup: {type: "boolean"}
        }
    },
    parse: function(response) {
    	response.data.map(doc => {
    		doc.createdAt = new Date(doc.createdAt);
    	})
    	return response;
    },
    columns: [{
            field: "createdAt",
            title: "@Time@",
            width: 140,
            template: function(dataItem) {
                return (kendo.toString(dataItem.createdAt, "dd/MM/yy H:mm:ss") ||  "").toString();
            }
        },{
            field: "createdBy",
            title: "@Created by@",
            width: 160
        },{
            field: "phone",
            title: "@Phone@",
            width: 200
        },{
            field: "content",
            title: "@Content@"
        }],
    filterable: KENDO.filterable
}; 
</script>

<!-- Table Styles Header -->
<ul class="breadcrumb breadcrumb-top">
    <li>@Report@</li>
    <li>@Sms log@</li>
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
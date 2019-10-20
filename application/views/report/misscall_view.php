<script>
var Config = {
    crudApi: `${ENV.restApi}`,
    templateApi: `${ENV.templateApi}`,
    collection: "misscall",
    observable: {
    },
    model: {
        id: "id",
        fields: {
            starttime: {type: "date"},
            billduration: {type: "number"},
            show_popup: {type: "boolean"}
        }
    },
    columns: [{
            field: "starttime",
            title: "@Time@",
            width: 140,
            template: function(dataItem) {
                return (kendo.toString(dataItem.starttime, "dd/MM/yy H:mm:ss") ||  "").toString();
            }
        },{
            field: "userextension",
            title: "Queue/@Extension@",
            width: 160
        },{
            field: "customernumber",
            title: "@Phone@",
            width: 200
        },{
            field: "disposition",
            title: "@Result@",
            width: 200
        },{
            field: "extension_available",
            title: "@Available Extensions@",
            template: (dataItem) => gridArray(dataItem.extension_available)
        },{
            field: "assign",
            title: "@Assign@",
            width: 140
        }]
}; 
</script>

<!-- Page content -->
<div id="page-content">
    <!-- Table Styles Header -->
    <ul class="breadcrumb breadcrumb-top">
        <li>@Report@</li>
        <li>@Misscall@</li>
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
</div>
<!-- END Page Content -->
<script>
var Config = {
    crudApi: `${ENV.restApi}`,
    templateApi: `${ENV.templateApi}`,
    collection: "import_file",
    observable: {
    },
    model: {
        id: "id",
        fields: {
            name: {type: "string"},
            exists: {type: "boolean"},
        }
    },
    columns: [{
            selectable: true,
            width: 32,
            locked: true
        },{
            title: "@Created at@",
            template: data => gridTimestamp(data.modify_time),
            width: 140
        },{
            field: "file_name",
            title: "@File name@",
            width: 180
        },{
            field: "exists",
            title: "@Exists@",
            template: data => gridBoolean(data.exists),
            width: 80
        },{
            field: "last_import_time",
            title: "@Last import time@",
            template: data => gridTimestamp(data.last_import_time),
            width: 80
        },{
            field: "description",
            title: "@Description@",
        },{
            // Use uid to fix bug data-uid of row undefined
            template: `<a role="button" class="k-button" href="javascript:void(0)" data-type="import" onclick="importFile(this)"><i class="fa fa-upload text-danger"></i>&nbsp;@Import@</a>`,
            width: 100
        }]
}; 
</script>

<!-- Table Styles Header -->
<ul class="breadcrumb breadcrumb-top">
    <li>@Manage@</li>
    <li>@Data@</li>
    <li>@Upload file@</li>
    <li class="pull-right none-breakcrumb">
        <div class="input-group-btn column-widget">
            <a role="button" class="btn btn-sm btn-success btn-alt" onclick="uploadMany()"><i class="fa fa-upload"></i> <b>@Import@ @selected file@</b></a>
            <a role="button" class="btn btn-sm btn-success btn-alt dropdown-toggle" data-toggle="dropdown" onclick="editColumns(this)"><i class="fa fa-calculator"></i> <b>@Edit Columns@</b></a>
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

function uploadMany() {

}
</script>
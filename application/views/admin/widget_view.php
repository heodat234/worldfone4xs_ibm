<script>
function typeEditor(container, options) {
    let field = options.field;
    var select = $(`<input name="${field}" style="width: 300px"/>`)
        .appendTo(container)
        .kendoDropDownList({
            valuePrimitive: true,
            filter: "contains",
            dataSource: ["LEFT_HEADER_WIDGET", "RIGHT_HEADER_WIDGET", "SIDEBAR_WIDGET"]
        }).data("kendoDropDownList");
}; 

function nameEditor(container, options) {
    let field = options.field;
    var select = $(`<input name="${field}"/>`)
        .appendTo(container)
        .kendoDropDownList({
            valuePrimitive: true,
            filter: "contains",
            dataSource: new kendo.data.DataSource({
                transport: {
                    read: ENV.vApi + "select/widget"  
                },
                schema: {
                    data: "data",
                    total: "total"
                }
            })
        }).data("kendoDropDownList");
}; 

var Config = {
    crudApi: `${ENV.restApi}`,
    templateApi: `${ENV.templateApi}`,
    collection: "widget",
    observable: {
    },
    model: {
        id: "id",
        fields: {
        	index: {type: "number"},
        	active: {type: "boolean", defaultValue: true}
        }
    },
    columns: [{
            field: "index",
            title: "Index",
            width: 120
        },{
            field: "type",
            title: "Type",
            editor: typeEditor
        },{
            field: "name",
            title: "Name",
            editor: nameEditor,
            width: 300
        },{
            field: "active",
            title: "Active",
            template: "#= gridBoolean(data.active) #",
            width: 120
        },{
            command: ["edit", "destroy"],
            width: 200
        }]
}; 
</script>

<!-- Page content -->
<div id="page-content">
    <!-- Table Styles Header -->
    <ul class="breadcrumb breadcrumb-top">
        <li><a href="<?= base_url("admin") ?>">Admin</a></li>
        <li>Model</li>
        <li class="pull-right none-breakcrumb">
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

    <div id="action-menu">
        <ul>
            <a href="javascript:void(0)" data-type="update" onclick="openForm({title: 'Edit JSON Data', width: 1000}); editForm(this)"><li><i class="fa fa-pencil-square-o text-warning"></i><span>Edit</span></li></a>
            <a href="javascript:void(0)" data-type="create" onclick="cloneDataItem(this)"><li><i class="fa fa-clipboard text-info"></i><span>Clone</span></li></a>
            <a href="javascript:void(0)" data-type="delete" onclick="deleteDataItem(this)"><li><i class="fa fa-times-circle text-danger"></i><span>Delete</span></li></a>
            <li class="devide"></li>
        </ul>
    </div>
</div>
<!-- END Page Content -->
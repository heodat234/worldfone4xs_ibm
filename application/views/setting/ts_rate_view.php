<script>
var Config = {
    crudApi: `${ENV.restApi}`,
    templateApi: `${ENV.templateApi}`,
    collection: "tS_rate",
    observable: {
    },
    model: {
        id: "id"
    },
    sort: [{
        field: "code", 
        dir: "asc"
    }],
    columns: [{
        selectable: true,
        width: 32,
        hidden: true
    },{
        field: "text",
        title: "@Rate@",
        format: "{0:p5}",
        editor: discountEditor,
    },{
        title: `@Action@`,
        command: [{name: "edit", text: "@Edit@"}, {name: "destroy", text: "@Delete@"}],
        width: 200
    }]
};

function discountEditor (container, options) {
    $('<input data-bind="value:' + options.field + '"/>')
        .appendTo(container)
        .kendoNumericTextBox({
        decimals: 7,
        format: "p5"
        });
}
</script>

<!-- Page content -->
<div id="page-content">
    <!-- Table Styles Header -->
    <ul class="breadcrumb breadcrumb-top">
        <li>@Setting@</li>
        <li>@Rate@</li>
        <li class="pull-right none-breakcrumb" id="top-row">
        	<div class="btn-group btn-group-sm">
                <button class="btn btn-alt btn-default" onclick="addForm(this)">@Create@</button>
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

    <!-- <div id="action-menu">
        <ul>
            <a href="javascript:void(0)" data-type="update" onclick="openForm({title: 'Edit Profile', width: 1000}); editForm(this)"><li><i class="fa fa-pencil-square-o text-warning"></i><span>Edit</span></li></a>
            <a href="javascript:void(0)" data-type="create" onclick="cloneDataItem(this)"><li><i class="fa fa-clipboard text-info"></i><span>Clone</span></li></a>
            <a href="javascript:void(0)" data-type="delete" onclick="deleteDataItem(this)"><li><i class="fa fa-times-circle text-danger"></i><span>Delete</span></li></a>
            <li class="devide"></li>
        </ul>
    </div> -->
</div>
<!-- END Page Content -->
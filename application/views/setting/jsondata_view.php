<script>
var Config = {
    crudApi: `${ENV.restApi}`,
    templateApi: `${ENV.templateApi}`,
    collection: "jsondata",
    observable: {
        arrayOpen: function(e) {
            e.preventDefault();
            var widget = e.sender;
            widget.input[0].onkeyup = function(ev) {
                if(ev.keyCode == 13) {
                    var values = widget.value();
                    values.push(this.value);
                    widget.dataSource.data(values);
                    widget.value(values);
                    widget.trigger("change");
                }
            }
        },
        cellClose: function(e) {
            this.set("item.data", e.sender.dataSource.data().toJSON())
        }
    },
    model: {
        id: "id",
    },
    columns: [{
            field: "name",
            title: "Name",
            template: `<b>#: name #</b>`,
            width: 240
        },{
            field: "tags",
            title: "TAGS",
            template: "#=gridArray(tags)#",
            width: 400
        },{
            field: "description",
            title: "DESCRIPTION",
        },{
            // Use uid to fix bug data-uid of row undefined
            template: '<a role="button" class="btn btn-sm btn-circle btn-action" data-uid="#: uid #"><i class="fa fa-ellipsis-v"></i></button>',
            width: 20
        }]
}; 
</script>

<!-- Page content -->
<div id="page-content">
    <!-- Table Styles Header -->
    <ul class="breadcrumb breadcrumb-top">
        <li>Admin</li>
        <li>Jsondata</li>
        <li class="pull-right none-breakcrumb">
            <div class="input-group-btn">
                <a role="button" class="btn btn-sm dropdown-toggle" data-toggle="dropdown"><b>Action</b> <span class="caret"></span></a>
                <ul class="dropdown-menu">
                    <li><a href="javascript:void(0)">Import</a></li>
                    <li><a data-type="create" onclick="openForm({title: 'Add JSON Data', width: 1000}); addForm(this)" href="javascript:void(0)">Add</a></li>
                    <li class="divider"></li>
                    <li><a href="javascript:void(0)">Separated link</a></li>
                </ul>
            </div>
            <a role="button" class="btn btn-sm"><i class="fa fa-calculator"></i> <b>Chỉnh cột</b></a>
            <a role="button" class="btn btn-sm"><i class="fa fa-filter"></i> <b>Lọc</b></a>
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
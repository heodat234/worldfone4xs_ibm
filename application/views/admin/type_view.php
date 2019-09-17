<script>
var Config = {
    crudApi: `${ENV.restApi}`,
    templateApi: `${ENV.templateApi}`,
    collection: "configtype",
    observable: {
    },
    model: {
        id: "id"
    },
    parse: function (response) {
        return response;
    },
    columns: [{
            field: "secret_key",
            title: "Secret key",
            width: 100
        },{
            field: "pbx_url",
            title: "Pbx url",
        },{
            field: "type",
            title: "Type",
            width: 100
        },{
            field: "typename",
            title: "Type Name",
            width: 140
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
        <li>Admin</li>
        <li>Type</li>
        <li class="pull-right none-breakcrumb" id="top-row">
        	<div class="btn-group btn-group-sm">
                <button class="btn btn-alt btn-default" onclick="addForm(this)">Create</button>
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

    <div id="action-menu">
        <ul>
        	<a href="javascript:void(0)" data-type="detail" onclick="detailData(this)"><li><i class="fa fa-exclamation-circle text-info"></i><span>Detail</span></li></a>
	    	<li class="devide"></li>
	        <a href="javascript:void(0)" data-type="update" onclick="editForm(this)"><li><i class="fa fa-pencil-square-o text-warning"></i><span>Edit</span></li></a>
	        <a href="javascript:void(0)" data-type="delete" onclick="deleteDataItem(this)"><li><i class="fa fa-times-circle text-danger"></i><span>Delete</span></li></a>
        </ul>
    </div>
</div>
<!-- END Page Content -->
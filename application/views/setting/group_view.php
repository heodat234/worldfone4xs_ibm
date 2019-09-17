<script>
var Config = {
    crudApi: `${ENV.restApi}`,
    templateApi: `${ENV.templateApi}`,
    collection: "group",
    observable: {
    	trueVar: true,
    },
    model: {
        id: "id",
        fields: {
            name: {type: "string", defaultValue: ""},
            queue: {type: "string"},
            members: {type: "object"},
            queues: {type: "object"}
        }
    }
}; 
</script>

<!-- Page content -->
<div id="page-content">
    <!-- Table Styles Header -->
    <ul class="breadcrumb breadcrumb-top">
        <li>@Setting@</li>
        <li>@Group@</li>
        <li class="pull-right none-breakcrumb">
            <a role="button" onclick="openForm({title: `@Add@ @Group@`,width: 400}); addForm(this)" href="javascript:void(0)" class="btn btn-sm"><b>@Add@</b></a>
        </li>
    </ul>
    <!-- END Table Styles Header -->

    <div class="container-fluid">
        <h4 class="fieldset-legend" style="margin: 10px 0 30px"><span style="font-weight: 500">@LIST@ @GROUP@</span></h4>
        <div class="row">
            <div class="col-sm-12" style="height: 80vh; overflow-y: auto; padding: 0">
                <!-- Table Styles Content -->
                <div data-role="listview" id="listview"
                 data-template="template"
                 data-bind="source: dataSource"></div>
                <!-- END Table Styles Content -->
            </div>
        </div>
    </div>

    <div id="action-menu">
        <ul>
            <a href="javascript:void(0)" data-type="update" onclick="openForm({title: `Edit Group`,width: 400}); editForm(this)"><li><i class="fa fa-pencil-square-o text-warning"></i><span>Edit</span></li></a>
            <a href="javascript:void(0)" data-type="delete" onclick="deleteDataItem(this)"><li><i class="fa fa-times-circle text-danger"></i><span>Delete</span></li></a>
        </ul>
    </div>
    <!-- END Page Content -->
    <!-- <input type="checkbox" data-bind="checked: default"> -->
    <script id="template" type="text/x-kendo-template">
        <div class="view-container">
            <span class="check-active">#= gridBoolean(data.active) #</span>
            <span class="group-name" data-bind="text: name"></span>
            <small class="text-warning">(<i data-bind="text: type"></i>)</small>
            <div class="pull-right">
            	<a href="javascript:void(0)" class="btn-action"><i class="gi gi-message_forward fa-2x"></i></a>
            </div>
            <br><br>
            <label>Queues: </label>
            <span class="queue-array">#= gridArray(data.queues) #</span>
            <br>
            <label>Members: </label>
            <span class="member-array">#= gridArray(data.members) #</span>
        </div>
    </script>
    <style type="text/css">
        .view-container {
        	border-radius: 5px;
            border: 1px solid lightgray;
            padding: 10px 20px;
            margin: 10px;
            width: 320px;
            float: left;
        }
        .view-container span {
            font-size: 20px;
        }
        #listview {
            border: 0;
        }
        .queue-array span, .member-array span {
    		font-size: 12px;
    		vertical-align: 2px;
    	}
    	.check-active {
    		border-radius: 7px;
    		border: 1px dashed gray;
    		padding: 1px 3px;
    	}
    </style>
</div>
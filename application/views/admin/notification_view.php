<script>
var Config = {
    crudApi: `${ENV.restApi}`,
    templateApi: `${ENV.templateApi}`,
    collection: "notification",
    observable: {
    	iconOption: function() {
            return dataSourceJsonData(["Navigators","icon"], function(response) {
                response.data.unshift({text: "", value: ""});
                return response;
            })
        },
        userListData:  function() {
        	return new kendo.data.DataSource({
	            transport: {
	                read: ENV.vApi + "widget/user_list",
	                parameterMap: parameterMap
	            },
	            schema: {
	                data: "data",
	                total: "total",
	                parse: function(response) {
	                	response.data.map(doc => {
	                		doc.text = doc.extension + " (" + doc.agentname + ")";
	                	})
	                	return response;
	                }
	            }
	        })
        },
        colorOption: HELPER.bsColors.map(doc => "text-" + doc)
    },
    model: {
        id: "id",
        fields: {
        	active: {type: "boolean"},
        	createdAt: {type: "date"}
        }
    },
    parse: function(response) {
    	response.data.map(doc => {
    		doc.createdAt = new Date(doc.createdAt * 1000);
    	});
    	return response;
    },
    columns: [{
            selectable: true,
            width: 32
        },{
            field: "title",
            title: "@Title@",
            width: 180
        },{
            field: "content",
            title: "@Content@",
            encoded: false
        },{
            field: "active",
            title: "@Active@",
            width: 100,
            template: (data) => gridBoolean(data.active)
        },{
            title: "@Icon@",
            width: 50,
            template: (data) => {
            	return data.icon ? `<i class="${data.icon} ${data.color}"></i>` : ``;
            }
        },{
            field: "link",
            title: "@Link@",
        },{
            field: "to",
            title: "@To@",
            template: (data) => gridArray(data.to)
        },{
            title: "@Read@",
            template: (data) => {
            	var htmlArr = [];
            	if(data.read) {
            		data.read.forEach((doc, index) => {
            			htmlArr.push(`<span class="label label-${HELPER.bsColors[index%6]}">${doc.extension}</span>`);
            		})
            		return htmlArr.join(" ");
            	} else return "";
            }
        },{
            field: "createdAt",
            title: "@Created at@",
            template: (data) => gridDate(data.createdAt),
            hidden: true
        },{
            field: "createdBy",
            title: "@Created by@",
            hidden: true
        },{
        	title: `<a class='btn btn-sm btn-circle btn-action btn-primary' onclick='return deleteDataItemChecked();'><i class='fa fa-times-circle'></i></a>`,
            template: '<a role="button" class="btn btn-sm btn-circle btn-action btn-primary"><i class="fa fa-ellipsis-v"></i></button>',
            width: 20
        }]
}; 

function deleteDataItem(ele) {
    swal({
        title: "@Are you sure@?",
        text: "@Once deleted, you will not be able to recover this document@!",
        icon: "warning",
        buttons: true,
        dangerMode: true,
    })
    .then((willDelete) => {
        if (willDelete) {
            var uid = $(ele).data('uid');
            var dataItem = Table.dataSource.getByUid(uid);
            Table.dataSource.remove(dataItem);
            Table.dataSource.sync();
        }
    });
}

function deleteDataItemChecked() {
	var checkIds = Table.grid.selectedKeyNames();
	if(checkIds.length) {
		swal({
		    title: "@Are you sure@?",
		    text: "@Once deleted, you will not be able to recover these documents@!",
		    icon: "warning",
		    buttons: true,
		    dangerMode: true,
	    })
	    .then((willDelete) => {
			if (willDelete) {
				checkIds.forEach(uid => {
					var dataItem = Table.dataSource.getByUid(uid);
				    Table.dataSource.remove(dataItem);
				    Table.dataSource.sync();
				})
			}
	    });
	} else {
		swal({
			title: "@No row is checked@!",
		    text: "@Please check least one row to remove@",
		    icon: "error"
		});
	}
}
</script>

<!-- Page content -->
<div id="page-content">
    <!-- Table Styles Header -->
    <ul class="breadcrumb breadcrumb-top">
        <li>@User@</li>
        <li>@Notification@</li>
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
            <a role="button" class="btn btn-sm" onclick="openForm({title: `@Add@ @Notification@`, width: 400});  addForm(this);"><i class="fa fa-plus"></i> <b>@Add@</b></a>
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

    <div id="action-menu">
        <ul>
            <a href="javascript:void(0)" data-type="update" onclick="openForm({title: '@Edit@ @Notification@', width: 400}); editForm(this)"><li><i class="fa fa-pencil-square-o text-warning"></i><span>@Edit@</span></li></a>
            <a href="javascript:void(0)" data-type="create" onclick="cloneDataItem(this)"><li><i class="fa fa-clipboard text-info"></i><span>@Clone@</span></li></a>
            <li class="devide"></li>
            <a href="javascript:void(0)" data-type="delete" onclick="deleteDataItem(this)"><li><i class="fa fa-times-circle text-danger"></i><span>@Delete@</span></li></a>
        </ul>
    </div>
</div>
<!-- END Page Content -->
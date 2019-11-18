<script>
var Config = {
    crudApi: `${ENV.restApi}`,
    templateApi: `${ENV.templateApi}`,
    collection: "follow_up",
    observable: {
    },
    model: {
        id: "id"
    }
}; 
</script>

<!-- Table Styles Header -->
<ul class="breadcrumb breadcrumb-top">
    <li>@Manage@</li>
    <li>@Follow up@</li>
    <li class="pull-right none-breakcrumb">
        <div class="input-group-btn column-widget">
            <a role="button" class="btn btn-sm dropdown-toggle" data-toggle="dropdown" onclick="editColumns(this)"><i class="fa fa-calculator"></i> <b>@Edit Columns@</b></a>
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
		<div id="action-menu">
		    <ul>
		        <a href="javascript:void(0)" data-type="detail" onclick="detailData(this)"><li><i class="fa fa-exclamation-circle text-info"></i><span>@Detail@</span></li></a>
		    	<li class="devide"></li>
		        <a href="javascript:void(0)" data-type="delete" onclick="deleteDataItem(this)"><li><i class="fa fa-times-circle text-danger"></i><span>@Delete@</span></li></a>
		    </ul>
		</div>
		<script>
		var Config = Object.assign(Config, {
		    model: {
		        id: "id",
		        fields: {
		        	reCall: {type: "date"}
		        }
		    },
		    parse: function(response) {
		    	response.data.map(function(doc) {
		            doc.reCall = new Date(doc.reCall * 1000);
		            return doc;
		        })
		        return response;
		    },
		    columns: [],
		    filterable: KENDO.filterable
		}); 

		function gridReCall(data, format = "dd/MM/yy H:mm") {
			var iconClass = (data < new Date()) ? "label-danger" : "label-default";
		    return data ? `<span class="label ${iconClass}">${kendo.toString(data, format)}</span>` : "";
		}

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
		<script src="<?= STEL_PATH.'js/table.js' ?>"></script>

		<script type="text/javascript">
			window.onload = function() {
				var followUpFields = new kendo.data.DataSource({
					serverFiltering: true,
					serverSorting: true,
					transport: {
						read: `${ENV.vApi}model/read`,
						parameterMap: parameterMap
					},
					schema: {
						data: "data",
						parse: function(response) {
							response.data = response.data.filter(function(doc) {
								if(doc.sub_type) 
									doc.subType = JSON.parse(doc.sub_type);
								else doc.subType = {};
								return doc.subType.gridShow;
							})
							return response;
						}
					},
					filter: {
						field: "collection",
						operator: "eq",
						value: (ENV.type ? ENV.type + "_" : "") +"Follow_up"
					},
					sort: {field: "index", dir: "asc"}
				})
				followUpFields.read().then(function(){
					var columns = followUpFields.data().toJSON();
					columns.map(col => {
						switch (col.type) {
							case "name":
								col.template = (dataItem) => gridName(dataItem[col.field]);
								break;
							case "phone":
								col.template = (dataItem) => gridPhone(dataItem[col.field]);
								break;
							case "arrayPhone":
								col.template = (dataItem) => gridArray(dataItem[col.field]);
								break;
							case "timestamp":
								if(col.field == "reCall")
									col.template = (dataItem) => gridReCall(dataItem[col.field]);
								else col.template = (dataItem) => gridDate(dataItem[col.field]);
								break;
							default:
								break;
						}
					});
					columns.unshift({
			    		selectable: true,
			            width: 32,
			            locked: true
			        });
			        columns.push({
			            // Use uid to fix bug data-uid of row undefined
			            title: `<a class='btn btn-sm btn-circle btn-action' onclick='return deleteDataItemChecked();'><i class='fa fa-times-circle'></i></a>`,
			            template: '<a role="button" class="btn btn-sm btn-circle btn-action" data-uid="#: uid #"><i class="fa fa-ellipsis-v"></i></a>',
			            width: 20
			        });
					Table.columns = columns;
					Table.init();
				})
			}

			function detailData(ele) {
				var uid = $(ele).data('uid');
				var dataItem = Table.dataSource.getByUid(uid);
				window.open(ENV.baseUrl + "manage/telesalelist/#/detail_customer/" + dataItem.foreign_id,'_blank','noopener');
			}

			$(document).on("click", ".grid-name", function() {
				detailData($(this).closest("tr"));
			})
		</script>
    </div>
</div>
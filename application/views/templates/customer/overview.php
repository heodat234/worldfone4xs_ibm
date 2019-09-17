<div class="col-sm-3" style="margin: 10px 0" id="page-widget"></div>
<div class="col-sm-9 filter-mvvm" style="display: none; margin: 10px 0"></div>
<div class="col-sm-12" style="padding: 0">
    <!-- Table Styles Content -->
    <div id="grid"></div>
    <!-- END Table Styles Content -->
</div>
<div id="action-menu">
    <ul>
        <a href="javascript:void(0)" data-type="detail" onclick="detailData(this)"><li><i class="fa fa-exclamation-circle text-info"></i><span>@Detail@</span></li></a>
    	<li class="devide"></li>
        <a href="javascript:void(0)" data-type="update" onclick="openForm({title: '@Edit@', width: 700}); editForm(this)"><li><i class="fa fa-pencil-square-o text-warning"></i><span>@Edit@</span></li></a>
        <a href="javascript:void(0)" data-type="delete" onclick="deleteDataItem(this)"><li><i class="fa fa-times-circle text-danger"></i><span>@Delete@</span></li></a>
    </ul>
</div>
<script>
var Config = Object.assign(Config, {
    model: {
        id: "id",
        fields: {
        	createdAt: {type: "date"}
        }
    },
    parse: function (response) {
        response.data.map(function(doc) {
            doc.createdAt = doc.createdAt ? new Date(doc.createdAt * 1000) : undefined;
            return doc;
        })
        return response;
    },
    columns: [{
    		selectable: true,
            width: 32,
            locked: true
        },{
            field: "name",
            title: "Name",
            width: 140
        },{
            field: "phone",
            title: "Main phone",
            width: 100
        },{
            field: "email",
            title: "Email",
        },{
            field: "address",
            title: "Address",
        },{
            field: "description",
            title: "Description",
        },{
            // Use uid to fix bug data-uid of row undefined
            title: `<a class='btn btn-sm btn-circle btn-action btn-primary' onclick='return deleteDataItemChecked();'><i class='fa fa-times-circle'></i></a>`,
            template: '<a role="button" class="btn btn-sm btn-circle btn-action btn-primary" data-uid="#: uid #"><i class="fa fa-ellipsis-v"></i></a>',
            width: 32
        }]
}); 
</script>
<script src="<?= STEL_PATH.'js/table.js' ?>"></script>

<script type="text/javascript">
	async function editForm(ele) {
		var dataItem = Table.dataSource.getByUid($(ele).data("uid")),
	        dataItemFull = await $.ajax({
	            url: `${Config.crudApi+Config.collection}/${dataItem.id}`,
	            error: errorDataSource
	        }),
		    formHtml = await $.ajax({
	    	    url: Config.templateApi + Config.collection + "/form",
	    	    error: errorDataSource
	    	});
		var model = Object.assign({
			item: dataItemFull,
			save: function() {
	            $.ajax({
	                url: `${Config.crudApi+Config.collection}/${dataItem.id}`,
	                data: kendo.stringify(this.item.toJSON()),
	                error: errorDataSource,
	                contentType: "application/json; charset=utf-8",
	                type: "PUT",
	                success: function() {
	                    Table.dataSource.read()
	                }
	            })
			}
		}, Config.observable);
		kendo.destroy($("#right-form"));
		$("#right-form").empty();
		var kendoView = new kendo.View(formHtml, { wrap: false, model: model, evalTemplate: false });
		kendoView.render($("#right-form"));
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

	function importData(ele) {
		var uid = $(ele).data('uid');
		var dataItem = Table.dataSource.getByUid(uid);
		router.navigate(`/import/${dataItem.id}`);
	}

	function detailData(ele) {
		var uid = $(ele).data('uid');
		var dataItem = Table.dataSource.getByUid(uid);
		router.navigate(`/detail/${dataItem.id}`);
	}

	$(document).on("click", ".grid-name", function() {
		detailData($(this).closest("tr"));
	})

	var customerFields = new kendo.data.DataSource({
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
			value: (ENV.type ? ENV.type + "_" : "") + "Customer"
		},
		sort: {field: "index", dir: "asc"}
	})
	customerFields.read().then(function(){
		var columns = customerFields.data().toJSON();
		columns.map(col => {
			switch (col.type) {
				case "name":
					col.template = (dataItem) => gridName(dataItem[col.field]);
					break;
				case "phone": case "arrayPhone":
					col.template = (dataItem) => gridPhone(dataItem[col.field]);
					break;
				case "array":
					col.template = (dataItem) => gridArray(dataItem[col.field]);
					break;
				case "timestamp":
					col.template = (dataItem) => gridDate(dataItem[col.field]);
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
            title: `<a class='btn btn-sm btn-circle btn-action btn-primary' onclick='return deleteDataItemChecked();'><i class='fa fa-times-circle'></i></a>`,
            template: '<a role="button" class="btn btn-sm btn-circle btn-action btn-primary" data-uid="#: uid #"><i class="fa fa-ellipsis-v"></i></a>',
            width: 32
        });
		Table.columns = columns;
		Table.init();
	})
</script>
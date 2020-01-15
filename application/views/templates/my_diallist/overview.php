<div class="col-sm-12" style="overflow-y: auto; padding: 0">
	<div id="grid"></div>
</div>
<div id="action-menu">
    <ul>
    	<a href="javascript:void(0)" data-type="detail" onclick="detailData(this)"><li><i class="fa fa-exclamation-circle text-info"></i><span>Detail</span></li></a>
    </ul>
</div>
<script>
var todayMidnight = new Date();
todayMidnight.setHours(0,0,0,0);

var Config = {
    crudApi: `${ENV.restApi}`,
    templateApi: `${ENV.templateApi}`,
    collection: "my_diallist",
    observable: {
    },
    model: {
        id: "id",
        fields: {
        	count_detail: {type: "number"},
        	createdAt: {type: "date"}
        }
    },
    parse: function(res) {
    	res.data.map(doc => {
    		doc.createdAt = doc.createdAt ? new Date(doc.createdAt * 1000) : null;
    	})
    	return res;
    },
    filter: {field: "createdAt", operator: "gte", value: todayMidnight},
    filterable: KENDO.filterable,
    columns: [{
            field: "name",
            title: "@Name@",
            template: dataItem => gridNameDiallist(dataItem),
            width: 300
        },{
            field: "mode",
            title: "@Mode@",
            width: 100
        },{
            field: "group_name",
            title: "@Group@",
        },{
            field: "createdAt",
            title: "@Create at@",
            format: "{0: dd/MM/yy HH:mm}"
        },{
            field: "createdBy",
            title: "@Create by@"
        },{
            // Use uid to fix bug data-uid of row undefined
            template: '<a role="button" class="btn btn-sm btn-circle btn-action" data-uid="#: uid #"><i class="fa fa-ellipsis-v"></i></a>',
            width: 20
        }]
}; 
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

	async function addForm() {
		var formHtml = await $.ajax({
		    url: Config.templateApi + Config.collection + "/form",
		    error: errorDataSource
		});
		var model = Object.assign({
			item: {},
			save: function() {
				Table.dataSource.add(this.item);
				Table.dataSource.sync().then(() => {Table.dataSource.read()});
			}
		}, Config.observable);
		kendo.destroy($("#right-form"));
		$("#right-form").empty();
		var kendoView = new kendo.View(formHtml, { wrap: false, model: model, evalTemplate: false });
		kendoView.render($("#right-form"));
	}

	function deleteDataItem(ele) {
		swal({
		    title: "Are you sure?",
		    text: "Once deleted, you will not be able to recover this document!",
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

	function gridNameDiallist(data, href = "javascript:void(0)") {
	    return (data.runStatus ? '<i class="fa fa-cog fa-spin text-warning"></i> ' : '') + `<a href="${href}"><span class="grid-name">${data.name}</span></a>`;
	}

	$(document).on("click", ".grid-name", function() {
		detailData($(this).closest("tr"));
	})
	Table.init();
</script>
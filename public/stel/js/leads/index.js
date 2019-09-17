Table.init();

async function editForm(ele) {
	var dataItem = Table.dataSource.getByUid($(ele).data("uid")),
		dataItemFull = await $.ajax({
			url: `${Config.crudApi+Config.collection}/${dataItem._id}`,
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
                url: `${Config.crudApi+Config.collection}/${dataItem._id}`,
                data: this.item.toJSON(),
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
		item: {
			owner_id: ENV.uid,
			company: {name: ""},
			deal: {name: ""}
		},
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

function cloneDataItem(ele) {
	swal({
        title: "Oops!",
        text: "Do you wanna clone this document?",
        icon: "warning",
        buttons: true,
        dangerMode: false,
    }).then((willClone) => {
        if(willClone) {
            var uid = $(ele).data('uid');
            var dataItem = Table.dataSource.getByUid(uid).toJSON();
            delete dataItem._id;
            Table.dataSource.add(dataItem);
            Table.dataSource.sync().then(() => {Table.dataSource.read()});
        }
    })
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

async function convertForm(ele) {
	var formHtml = await $.ajax({
	    url: Config.templateApi + Config.collection + "/convert",
	    error: errorDataSource
	});
	var uid = $(ele).data('uid'),
   		dataItem = Table.dataSource.getByUid(uid),
   		dataItemFull = await $.ajax({
			url: `${Config.crudApi+Config.collection}/${dataItem._id}`,
			error: errorDataSource
		}),
		model = Object.assign({
			item: dataItemFull,
			save: function() {
				$.ajax({
					url: Config.crudApi + "action/convert/" + dataItem._id,
					data: this.item.toJSON(),
					type: "PUT",
					success: function() {
						Table.dataSource.read().then(() => {
							syncDataSource();
						});
					},
					error: errorDataSource
				});
			}
		}, Config.observable);
	kendo.destroy($("#right-form"));
	$("#right-form").empty();
	var kendoView = new kendo.View(formHtml, { wrap: false, model: model, evalTemplate: false });
	kendoView.render($("#right-form"));
}
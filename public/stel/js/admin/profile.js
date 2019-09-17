Table.init();

function functionFalse() {
	return false;
}

$.get(Config.crudApi + "module", {q: JSON.stringify({filter: {field: "active", operator: "eq", value: true}})}, function(response) {
	var modules = response.data;
	modules.map(function(value) {
		value.value = value.id;
		value.text = value.name;
		return value;
	})
	window.modules = modules;
})

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
                contentType: "application/json; charset=utf-8",
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
            delete dataItem.id;
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

function editorActions(container, options) {
    let field = options.field;
    var select = $('<select name="' + field + '" style="float:left; width: 100%"></select>')
	        .appendTo(container)
	        .kendoMultiSelect({
	            valuePrimitive: true,
	            values: (options.model[field] || []).slice(0),
	            dataSource: (options.model[field] || []).slice(0)
	        }).data("kendoMultiSelect");
};
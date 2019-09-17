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
                contentType: "application/json; charset=utf-8",
                type: "PUT",
                error: errorDataSource,
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
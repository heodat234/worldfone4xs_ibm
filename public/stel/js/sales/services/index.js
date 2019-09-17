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
			active: true,
			usage: {value: 1, unit: "MONTHS"},
			price: {currency: "VND"},
            priceDetails: [],
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

function addColumn(ele) {
    var $input = $(ele).prev("input"),
        name = $input.val(),
        $dataGrid = $("#optionsGrid"),
        dataGrid = $dataGrid.data("kendoGrid"),
        options = dataGrid.getOptions(),
        columns = options.columns,
    	priceColumn = {field: "price", title: "PRICE", type: "number"};
    	newColumns = [];
    // Remove column price
    columns.forEach(function(val){
    	if(val.field != "price"){
    		newColumns.push(val);
    	}
    })
    // Add new column
    newColumns.push({field: name, type: "boolean"});
    // Add column price
    newColumns.push(priceColumn);
    dataGrid.setOptions({columns: newColumns});
    $input.val("");
}

function addOption(ele) {
    var $input = $(ele).prev("input"),
        name = $input.val(),
        $multiselect = $("select[name=options]"),
        tooltip = $multiselect.closest(".form-group").find(".btn-add").data("kendoTooltip"),
        multiselect = $multiselect.data("kendoMultiSelect"),
        values = multiselect.value(),
        data = multiselect.dataSource.data().toJSON();
    data.push(name);
    multiselect.dataSource.data(data);
    values.push(name);
    multiselect.value(values);
    multiselect.trigger("change");
    tooltip.hide();
    $input.val("");
}

function gridMultiTags(container, options) {
    let field = options.field;
	var select = $('<select name="' + field + '" width: 100%"></select>')
	    .appendTo(container)
	    .kendoMultiSelect({
	        valuePrimitive: true,
	        dataSource: options.model.tags
	    }).data("kendoMultiSelect");
}; 
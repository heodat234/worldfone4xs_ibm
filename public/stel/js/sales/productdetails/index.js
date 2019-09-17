Table.init();

Config.observable.productOption = dataSourceDropDownList("Products", "name");

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
			status: "IN STOCK",
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

async function addManufacturer(ele) {
	var name = $(ele).prev("input").val();
	await $.ajax({
		url: Config.crudApi + "companies",
		type: "POST",
		data: {
			name: name,
			owner_id: ENV.uid,
			tags: ["MANUFACTURER"]
		}
	})
	var $dropdownlist = $("input[name=manufacturer_id]"),
		dropdownlist = $dropdownlist.data("kendoDropDownList");
	await dropdownlist.dataSource.read();
	dropdownlist.select(function(dataItem) {
    	return dataItem.name === name;
	});
	var tooltip = $dropdownlist.closest(".form-group").find(".btn-add").data("kendoTooltip");
	tooltip.hide();
}

async function addVendor(ele) {
	var name = $(ele).prev("input").val();
	await $.ajax({
		url: Config.crudApi + "companies",
		type: "POST",
		data: {
			name: name,
			owner_id: ENV.uid,
			tags: ["VENDOR"]
		}
	})
	var $dropdownlist = $("input[name=vendor_id]"),
		dropdownlist = $dropdownlist.data("kendoDropDownList");
	await dropdownlist.dataSource.read();
	dropdownlist.select(function(dataItem) {
    	return dataItem.name === name;
	});
	var tooltip = $dropdownlist.closest(".form-group").find(".btn-add").data("kendoTooltip");
	tooltip.hide();
}
Table.init();

async function editForm(ele) {
	var dataItem = Table.dataSource.getByUid($(ele).data("uid")),
		dataItemFull = await $.ajax({
			url: `${Config.crudApi+Config.collection}/${dataItem._id}`,
			error: errorDataSource
		}),
		typeForm = dataItemFull.type ? dataItemFull.type.toLowerCase() : "purchase";
		formHtml = await $.ajax({
		    url: Config.templateApi + Config.collection + "/" + typeForm,
		    error: errorDataSource
		});
	
	var model = Object.assign({
		visibleProduct: (dataItemFull.tags.indexOf("PRODUCTS") != -1) ? true : false,
		visibleService: (dataItemFull.tags.indexOf("SERVICES") != -1) ? true : false,  
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
	var type = await swal("Which type of contract do you want to create?", {
		buttons: {
		    PURCHASE: {
			    text: "Purchase Contract"
		    },
		    SALE: {
			    text: "Sale Contract"
		    },
		    cancel: true,
		},
	})

	if(type) {
		var formHtml = await $.ajax({
		    url: Config.templateApi + Config.collection + "/" + type.toLowerCase(),
		    error: errorDataSource
		});
		var model = Object.assign({
			item: {
				type: type,
				approver_id: ENV.uid,
				status: "DRAFT",
				price: {currency: "VND"},
				tags: ["PRODUCTS","SERVICES"],
                purchaseProductDetails: [],
                purchaseServiceDetails: [],
                saleProductDetails: [],
                saleServiceDetails: []
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
	} else {
		closeForm();
	}
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

function editorSelectProductPurchase(container, options) {
    let field = options.field;
    var select = $('<input name="product_id"/>')
        .appendTo(container)
        .kendoDropDownList({
            valuePrimitive: true,
            dataTextField: 'name', 
            dataValueField: '_id',
            filter: "startswith",
            dataSource: dataSourceDropDownList("Products", ["name", "unit", "cost"]),
            select: function(e) {
                options.model.set("product_name", e.dataItem.name);
                options.model.set("unit", e.dataItem.unit);
                options.model.set("unit_price", e.dataItem.cost ? e.dataItem.cost.value : 0);
                options.model.set("currency", e.dataItem.cost ? e.dataItem.cost.currency : "VND");
            }
        }).data("kendoDropDownList");
}; 

function editorSelectProductSale(container, options) {
    let field = options.field;
    var select = $('<input name="product_id"/>')
        .appendTo(container)
        .kendoDropDownList({
            valuePrimitive: true,
            dataTextField: 'name', 
            dataValueField: '_id',
            filter: "startswith",
            dataSource: dataSourceDropDownList("Products", ["name", "unit", "price"]),
            select: function(e) {
                options.model.set("product_name", e.dataItem.name);
                options.model.set("unit", e.dataItem.unit);
                options.model.set("unit_price", e.dataItem.price ? e.dataItem.price.value : 0);
                options.model.set("currency", e.dataItem.price ? e.dataItem.price.currency : "VND");
            }
        }).data("kendoDropDownList");
}; 

function tplUnitPrice(dataItem) {
    var currency = dataItem.currency ? dataItem.currency : "";
    var unitPrice =  dataItem.unit_price ? dataItem.unit_price : 0;
    return currency + " " + unitPrice
}

function tplTotalPrice(dataItem) {
    var currency = dataItem.currency ? dataItem.currency : "";
    var unitPrice =  dataItem.unit_price ? dataItem.unit_price : 0;
    return currency + " " + unitPrice * dataItem.qty;
}

function tplPrice(dataItem) {
	var currency = dataItem.currency ? dataItem.currency : "";
    var price =  dataItem.price ? dataItem.price : 0;
    return currency + " " + price;
}

function editorTextarea(container, options) {
	let field = options.field;
    var textarea = $('<textarea class="k-textbox" name="'+field+'"/></textarea>')
        .appendTo(container);
}

function editorSelectVendor(container, options) {
    let field = options.field;
    var select = $('<input name="vendor_id"/>')
        .appendTo(container)
        .kendoComboBox({
            valuePrimitive: true,
            dataTextField: 'name', 
            dataValueField: '_id',
            filter: "startswith",
            dataSource: dataSourceDropDownList("Companies", ["name"], {tags: {$in: ["VENDOR"]}}),
            select: function(e) {
                options.model.set("vendor_name", e.dataItem.name);
            }
        }).data("kendoComboBox");
}; 

function editorPurchaseServicePrice(container, options) {
	let field = options.field;
	$('<input name="currency" style="width: 40px"/>')
        .appendTo(container)
        .kendoDropDownList({
            valuePrimitive: true,
            dataTextField: 'symbol', 
            dataValueField: 'code',
            dataSource: dataSourceJsonData(["currency"])
        }).data("kendoDropDownList");

    $('<input name="price" style="width: 120px"/>')
        .appendTo(container)
        .kendoNumericTextBox();
}

function editorSelectServiceSale(container, options) {
    let field = options.field;
    var select = $('<input name="service_id"/>')
        .appendTo(container)
        .kendoDropDownList({
            valuePrimitive: true,
            dataTextField: 'name', 
            dataValueField: '_id',
            filter: "startswith",
            dataSource: dataSourceDropDownList("Services", ["name", "category", "price", "priceDetails"]),
            select: async function(e) {
            	if(e.dataItem.priceDetails.length) {
            		var buttons = {
            			origin: {
            				text: "ORIGIN",
            				className: "swal-btn-sm"
            			}
            		};
            		for (var i = 0; i < e.dataItem.priceDetails.length; i++) {
            			buttons[e.dataItem.priceDetails[i]._id] = {
            				className: "swal-btn-sm",
            				text: e.dataItem.priceDetails[i].name ? e.dataItem.priceDetails[i].name : e.dataItem.priceDetails[i].tags.join(",")
            			}
            		}
            		
            		var chosen = await swal("Which type of options do you want?", {
						buttons: buttons,
					})

					switch(chosen) {
						case "origin":
							options.model.set("service_name", e.dataItem.name);
			                options.model.set("category", e.dataItem.category);
			                options.model.set("price", e.dataItem.price ? e.dataItem.price.value : 0);
                            options.model.set("currency", e.dataItem.price ? e.dataItem.price.currency : "VND");
	                		break;	

	                	default: 
	                		for (var i = 0; i < e.dataItem.priceDetails.length; i++) {
		            			if(e.dataItem.priceDetails[i]._id) {
		            				options.model.set("service_name", e.dataItem.name);
					                options.model.set("category", e.dataItem.category);
                                    options.model.set("currency", e.dataItem.price ? e.dataItem.price.currency : "VND");
					                options.model.set("description", e.dataItem.priceDetails[i].tags.join(", "));
					                options.model.set("price", e.dataItem.priceDetails[i].value ? e.dataItem.priceDetails[i].value : 0);
		            				break;
		            			}
		            		}
	                		break;

	                	case null:
	                		break;
					}
            	} else {
	                options.model.set("service_name", e.dataItem.name);
	                options.model.set("category", e.dataItem.category);
	                options.model.set("price", e.dataItem.price ? e.dataItem.price.value : 0);
                    options.model.set("currency", e.dataItem.price ? e.dataItem.price.currency : "VND");
                }
            }
        }).data("kendoDropDownList");
}; 
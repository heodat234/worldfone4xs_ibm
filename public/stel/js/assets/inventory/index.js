var List = function() {
    return {
        dataSource: {},
        listview: {},
        columns: Config.columns,
        init: function() {
            var dataSource = this.dataSource = new kendo.data.DataSource({
                serverFiltering: true,
                serverPaging: true,
                serverSorting: true,
                serverGrouping: false,
                pageSize: 10,
                schema: {
                    data: "data",
                    total: "total",
                    model: Config.model,
                },
                transport: {
                    read: {
                        url: Config.crudApi + Config.path,
                    },
                    parameterMap: function(options, operation) {
                        return options;
                    }
                },
                sync: syncDataSource,
                error: errorDataSource
            });

            var importDataSource = this.importDataSource = new kendo.data.DataSource({
                serverFiltering: true,
                serverPaging: true,
                serverSorting: true,
                serverGrouping: false,
                pageSize: 10,
                filter: { field: "type", operator: "eq", value: "input" },
                schema: {
                    data: "data",
                    total: "total",
                    model: Config.model,
                },
                transport: {
                    read: {
                        url: Config.crudApi + Config.collection,
                    },
                    parameterMap: function(options, operation) {
                        return options;
                    }
                },
                sync: syncDataSource,
                error: errorDataSource
            });

            var exportDataSource = this.exportDataSource = new kendo.data.DataSource({
                serverFiltering: true,
                serverPaging: true,
                serverSorting: true,
                serverGrouping: false,
                pageSize: 10,
                filter: { field: "type", operator: "eq", value: "output" },
                schema: {
                    data: "data",
                    total: "total",
                    model: Config.model,
                },
                transport: {
                    read: {
                        url: Config.crudApi + Config.collection,
                    },
                    parameterMap: function(options, operation) {
                        return options;
                    }
                },
                sync: syncDataSource,
                error: errorDataSource
            });

            var observable = this.observable = Object.assign({
            	dataSource: dataSource,
                importDataSource: importDataSource,
                exportDataSource: exportDataSource
            }, Config.observable)

            kendo.bind($("#page-content"), observable)

            /*
             * Right Click Menu
             */
            var menu = $("#action-menu");

            $("html").on("click", function() {menu.hide()});

            $(document).on("click", "#listview a.btn-action", function(e){
                let row = $(e.target).closest("div.view-container");
                e.pageX -= 20;
                showMenu(e, row);
            });

            function showMenu(e, that) {
                //hide menu if already shown
                menu.hide(); 

                //Get id value of document
                var uid = $(that).data('uid');
                if(uid)
                {
                    menu.find("a[data-type=read], a[data-type=update], a[data-type=delete], a[data-type=duplicate]").data('uid',uid);

                    //get x and y values of the click event
                    var pageX = e.pageX;
                    var pageY = e.pageY;

                    //position menu div near mouse cliked area
                    menu.css({top: pageY , left: pageX});

                    var mwidth = menu.width();
                    var mheight = menu.height();
                    var screenWidth = $(window).width();
                    var screenHeight = $(window).height();

                    //if window is scrolled
                    var scrTop = $(window).scrollTop();

                    //if the menu is close to right edge of the window
                    if(pageX+mwidth > screenWidth){
                    menu.css({left:pageX-mwidth});
                    }

                    //if the menu is close to bottom edge of the window
                    if(pageY+mheight > screenHeight+scrTop){
                    menu.css({top:pageY-mheight});
                    }

                    //finally show the menu
                    menu.show();     
                }
            }
        }
    }
}();

List.init();

async function inputForm() {
    var formHtml = await $.ajax({
        url: Config.templateApi + Config.path + "/input",
        error: errorDataSource
    });
    var model = Object.assign({
        item: {from: "ORDER", type: "input", owner_id: ENV.uid, productDetails: []},
        save: function() {
            $.ajax({
                url: Config.crudApi + Config.collection,
                type: "POST",
                data: this.item.toJSON(),
                success: function() {
                    syncDataSource();
                    List.dataSource.read();
                },
                error: errorDataSource
            })
        }
    }, Config.observable);
    kendo.destroy($("#right-form"));
    $("#right-form").empty();
    var kendoView = new kendo.View(formHtml, { wrap: false, model: model, evalTemplate: false });
    kendoView.render($("#right-form"));
}

async function outputForm() {
    var formHtml = await $.ajax({
        url: Config.templateApi + Config.path + "/output",
        error: errorDataSource
    });
    var model = Object.assign({
        item: {from: "ORDER", type: "output", owner_id: ENV.uid, productDetails: []},
        save: function() {
            $.ajax({
                url: Config.crudApi + Config.collection,
                type: "POST",
                data: this.item.toJSON(),
                success: function() {
                    syncDataSource();
                    List.dataSource.read();
                },
                error: errorDataSource
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
        title: "Are you sure?",
        text: "Once deleted, you will not be able to recover this document!",
        icon: "warning",
        buttons: true,
        dangerMode: true,
    })
    .then((willDelete) => {
        if (willDelete) {
            var uid = $(ele).data('uid');
            var dataItem = List.dataSource.getByUid(uid);
            List.dataSource.remove(dataItem);
            List.dataSource.sync();
        }
    });
}

async function addAction(ele) {
    var name = $(ele).prev("input").val(),
        $multiselect = $("select[name=actions]"),
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
}

$("#selectWarehouse").kendoDropDownList({
    dataTextField: "name",
    dataValueField: "_id",
    dataSource: dataSourceDropDownList("Locations", ["name","address"], {tags: {$in: ["WAREHOUSE"]}}, function(response) {
        response.data.map(function(val) {
            val.name = val.name + " - " + val.address;
        })
        response.data.unshift({name: "All inventory", _id: "ALL"});
        return response;
    }),
    change: function(e) {
        var value = e.sender.value();
        switch(value) {
            case "ALL":
                List.dataSource.read({});
                List.importDataSource.read({});
                List.exportDataSource.read({});
                break;
            default:
                List.dataSource.read({location_id: value});
                List.importDataSource.read({location_id: value});
                List.exportDataSource.read({location_id: value});
                break;
        }
    }
});

function gridSelectProduct(container, options) {
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
    select.open();
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
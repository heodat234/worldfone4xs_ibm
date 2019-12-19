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

    var model = kendo.observable(Object.assign({
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
    }, Config.observable));
    kendo.destroy($("#right-form"));
    $("#right-form").empty();
    var kendoView = new kendo.View(formHtml, { wrap: false, model: model, evalTemplate: false });
    kendoView.render($("#right-form"));

    var navigatorHTML = await $.get(ENV.baseUrl + "template/nav/from_privileges", {q: JSON.stringify(dataItemFull.privileges)});
    model.set("navigatorHTML", navigatorHTML);
    handleNavCheck();
}

async function addForm() {
    var formHtml = await $.ajax({
        url: Config.templateApi + Config.collection + "/form",
        error: errorDataSource
    });
    var model = kendo.observable(Object.assign({
        item: {},
        save: function() {
            Table.dataSource.add(this.item);
            Table.dataSource.sync().then(() => {Table.dataSource.read()});
        }
    }, Config.observable));
    kendo.destroy($("#right-form"));
    $("#right-form").empty();
    var kendoView = new kendo.View(formHtml, { wrap: false, model: model, evalTemplate: false });
    kendoView.render($("#right-form"));

    var navigatorHTML = await $.get(ENV.baseUrl + "template/nav/from_privileges");
    model.set("navigatorHTML", navigatorHTML);
    handleNavCheck();
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

function editorModule(container, options) {
    let field = options.field;
    var select = $('<input name="' + field + '" style="float:left; width: 100%"/>')
            .appendTo(container)
            .kendoDropDownList({
                filter: "contains",
                valuePrimitive: true,
                dataValueField: "value",
                dataTextField: "text",
                dataSource: window.modules,
                select: function(e) {
                    options.model.set("actions", e.dataItem.actions);
                }
            }).data("kendoDropDownList");
    select.open();
};

function editorActions(container, options) {
    let field = options.field;
    var select = $('<select name="' + field + '" style="float:left; width: 100%"></select>')
            .appendTo(container)
            .kendoMultiSelect({
                valuePrimitive: true,
                values: (options.model[field] || []).slice(0),
                dataSource: new kendo.data.DataSource({
                    transport: {
                        read: ENV.restApi + "module/" + options.model.module_id
                    },
                    schema: {
                        data: "actions",
                        parse: function(res) {
                            res.actions = res.actions || [];
                            return res;
                        }
                    }
                })
            }).data("kendoMultiSelect");
};

var handleNavCheck = function() {
    // Animation Speed, change the values for different results
    var page        = $('#page-container');
    var upSpeed     = 250;
    var downSpeed   = 250;

    // Get all vital links
    var menuLinks       = $('.check-sidebar .sidebar-nav-menu');
    var submenuLinks    = $('.check-sidebar .sidebar-nav-submenu');

    // Primary Accordion functionality
    menuLinks.click(function(){
        var link = $(this);

        if (page.hasClass('sidebar-mini') && page.hasClass('sidebar-visible-lg-mini') && (getWindowWidth() > 991)) {
            if (link.hasClass('open')) {
                link.removeClass('open');
            }
            else {
                $('.sidebar-nav-menu.open').removeClass('open');
                link.addClass('open');
            }
        }
        else if (!link.parent().hasClass('active')) {
            if (link.hasClass('open')) {
                link.removeClass('open').next().slideUp(upSpeed);
            }
            else {
                $('.sidebar-nav-menu.open').removeClass('open').next().slideUp(upSpeed);
                link.addClass('open').next().slideDown(downSpeed);
            }
        }

        link.blur();

        return false;
    });
};
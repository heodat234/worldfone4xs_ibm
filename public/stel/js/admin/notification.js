Table.init();

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
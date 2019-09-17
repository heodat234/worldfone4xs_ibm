function detailInit(e) {
    var detailRow = e.detailRow,
        data =  e.data.data.toJSON();
    detailRow.find(".data").kendoGrid({
        dataSource: {
            data: e.data.data,
        },
        height: (data.length > 5) ? 200: false,
        sortable: true,
        scrollable: true,
    });
}

var Table = {
    dataSource: {},
    grid: {},
    columns: Config.columns,
    init: function() {
        var dataSource = this.dataSource = new kendo.data.DataSource({
            serverFiltering: true,
            serverPaging: true,
            serverSorting: true,
            serverGrouping: false,
            pageSize: 10,
            batch: false,
            schema: {
                data: "data",
                total: "total",
                groups: "groups",
                model: Config.model,
                parse: Config.parse
            },
            filter: {
                logic: 'and',
                filters: [{
                    field: 'trigger_type',
                    operator: 'eq',
                    value: Config.currentTrigger
                }]
            },
            transport: {
                read: {
                    url: Config.crudApi + Config.collection,
                },
                update: {
                    url: function(data) {
                        return Config.crudApi + Config.collection + "/" + data.id;
                    },
                    type: "PUT",
                    contentType: "application/json; charset=utf-8"
                },
                create: {
                    url: Config.crudApi + Config.collection,
                    type: "POST",
                    contentType: "application/json; charset=utf-8"
                },
                destroy: {
                    url: function(data) {
                        return Config.crudApi + Config.collection + "/" + data.id;
                    },
                    type: "DELETE"
                },
                parameterMap: parameterMap
            },
            sync: syncDataSource,
            error: errorDataSource
        });

        var grid = this.grid = $("#grid").kendoGrid({
            dataSource: dataSource,
            resizable: true,
            pageable: {
                refresh: true
            },
            sortable: true,
            scrollable: false,
            columns: this.columns,
            filterable: false,
            dataBound: function() {
                this.tbody.find(".customClass").kendoSwitch({
                    onLabel: "YES",
                    offLabel: "NO"
                })
            }
            // detailTemplate: `<div class="data"></div>`,
            // detailInit: detailInit,
        }).data("kendoGrid");

        grid.selectedKeyNames = function() {
            var items = this.select(),
                that = this,
                checkedIds = [];
            $.each(items, function(){
                if(that.dataItem(this))
                    checkedIds.push(that.dataItem(this).uid);
            })
            return checkedIds;
        }

        /*
         * Right Click Menu
         */
        var menu = $("#action-menu");

        $("html").on("click", function() {menu.hide()});

        $(document).on("click", "#grid tr[role=row] a.btn-action", function(e){
            // Fix bug data-uid of row undefined
            console.log("TEST");
            let row = $(e.target);
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
                menu.find("a[data-type=convert], a[data-type=update], a[data-type=delete], a[data-type=duplicate]").data('uid',uid);

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
};

Table.init();
kendo.bind($("#page-content"), kendo.Observable(Config));

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
                data: JSON.stringify(this.item.toJSON()),
                contentType: "application/json; charset=utf-8",
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
    console.log(formHtml);
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

function sortable(e) {
    e.preventDefault();
    var grid = $("#dataGrid").data("kendoGrid");
    grid.table.kendoSortable({
        filter: ">tbody >tr",
        hint: $.noop,
        cursor: "move",
        placeholder: function(element) {
            return element.clone().addClass("k-state-hover").css("opacity", 0.65);
        },
        container: "#dataGrid tbody",
        change: function(e) {
            var newIndex = e.newIndex,
                dataItem = grid.dataSource.getByUid(e.item.data("uid"));

            grid.dataSource.remove(dataItem);
            grid.dataSource.insert(newIndex, dataItem);
        }
    });
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

function addColumn(ele) {
    var $input = $(ele).prev("input"),
        name = $input.val(),
        $dataGrid = $("#dataGrid"),
        dataGrid = $dataGrid.data("kendoGrid"),
        options = dataGrid.getOptions(),
        columns = options.columns;
    columns.push({field: name});
    dataGrid.setOptions({columns: columns});
    $input.val("");
}

function addTextValue(e) {
    e.preventDefault();
    var dataGrid = $("#dataGrid").data("kendoGrid"),
        options = dataGrid.getOptions(),
        columns = options.columns;
    if(!columns.length) {
        columns.push({field: "text"}, {field: "value"});
        dataGrid.setOptions({columns: columns});
    }
}

function changeTriggerType(type) {
    Config.currentTrigger = type;
    console.log(Config.currentTrigger);
    Table.dataSource.filter({field: 'trigger_type', operator: 'eq', value: type});
}

$(document).on("click", "a.k-grid-deleteDataItem", function(e){
    e.preventDefault();
    $("#dataGrid").data("kendoGrid").removeRow("tr:eq(1)");
})

$(document).on("click", "a.k-grid-sortDataItem", sortable);

$(document).on("click", "a.k-grid-textValueColumn", addTextValue)
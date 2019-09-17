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
                batch: false,
                schema: {
                    data: "data",
                    total: "total",
                    model: Config.model,
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

            var observable = this.observable = Object.assign({
            	dataSource: dataSource
            }, Config.observable)

            kendo.bind($(".mvvm"), observable)

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
                    menu.find("a[data-type=read], a[data-type=update], a[data-type=delete]").data('uid',uid);

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

async function editForm(ele) {
    var dataItem = List.dataSource.getByUid($(ele).data("uid")),
        dataItemFull = await $.ajax({
            url: Config.crudApi + Config.collection + "/" + dataItem.id,
            error: errorDataSource
        });
        dataItem.set("paths", dataItemFull.paths);
        formHtml = await $.ajax({
            url: Config.templateApi + Config.collection + "/form",
            error: errorDataSource
        });
    var model = Object.assign(Config.observable, {
        item: dataItem,
        save: function() {
            List.dataSource.sync().then(() => {List.dataSource.read()});
        }
    });
    var $rightForm = $("#right-form");
    kendo.destroy($rightForm);
    $rightForm.empty();
    var kendoView = new kendo.View(formHtml, { wrap: false, model: model, evalTemplate: false });
    kendoView.render($rightForm);
}

async function addForm() {
    var formHtml = await $.ajax({
        url: Config.templateApi + Config.collection + "/form",
        error: errorDataSource
    });
    var model = Object.assign({
        item: {},
        save: function() {
            List.dataSource.add(this.item);
            List.dataSource.sync().then(() => {List.dataSource.read()});
        }
    }, Config.observable);
    var $rightForm = $("#right-form");
    kendo.destroy($rightForm);
    $rightForm.empty();
    var kendoView = new kendo.View(formHtml, { wrap: false, model: model, evalTemplate: false });
    kendoView.render($rightForm);
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
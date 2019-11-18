<script>
function typeEditor(container, options) {
    let field = options.field;
    var select = $(`<input name="${field}"/>`)
        .appendTo(container)
        .kendoDropDownList({
            valuePrimitive: true,
            dataTextField: 'value', 
            dataValueField: 'value',
            filter: "contains",
            dataSource: dataSourceDropDownListPrivate("DataType", ["value"]),
            select: function(e) {
                options.model.set("type", e.dataItem.value);
            }
        }).data("kendoDropDownList");
    select.open();
};

function subtypeEditor(container, options) {
    let field = options.field;
    var select = $(`<input name="${field}"/>`)
        .appendTo(container)
        .kendoDropDownList({
            valuePrimitive: true,
            dataTextField: 'text', 
            dataValueField: 'value',
            dataSource: dataSourceJsonData(["Diallist", "type"], res => {
                res.data.unshift({text: "@Common@", value: null});
                return res;
            }),
            select: function(e) {
                options.model.set("type", e.dataItem.value);
            }
        }).data("kendoDropDownList");
    select.open();
};  

var Table = {
    dataSource: {},
    grid: {},
    columns: [],
    init: function() {
        this.columns = Config.columns;
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
            error: errorDataSource,
            change: function(e) {
                if(e.action == "sync") {
                    e.sender.read();
                }
            }
        });

        var grid = this.grid = $("#grid").kendoGrid({
            toolbar: [{name: 'create'}],
            editable: "inline",
            dataSource: dataSource,
            resizable: true,
            pageable: {
                refresh: true
            },
            sortable: true,
            scrollable: false,
            columns: this.columns,
            filterable: true
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
</script>

<!-- Table Styles Header -->
<ul class="breadcrumb breadcrumb-top">
    <li>@Setting@</li>
    <li>@Diallist detail field@</li>
</ul>
<!-- END Table Styles Header -->

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12" style="height: 80vh; overflow-y: auto; padding: 0">
            <!-- Table Styles Content -->
            <div id="grid"></div>
            <!-- END Table Styles Content -->
        </div>
    </div>
</div>

<script>
    $.get(ENV.vApi + "select/jsondata", {tags: ["Diallist","type"]}, function(res) {
        if(!res.data) return;
        window.Config = {
            crudApi: `${ENV.restApi}`,
            templateApi: `${ENV.templateApi}`,
            collection: "diallistdetailfield",
            observable: {
            },
            model: {
                id: "id",
                fields: {
                    index: {type: "number"},
                    collection: {defaultValue: "Diallist_detail"},
                    type: {defaultValue: "string"}
                }
            },
            columns: [{
                    field: "index",
                    title: "#",
                    width: 70
                },{
                    field: "title",
                    title: "@Title@",
                    width: 220
                },{
                    field: "field",
                    title: "@Field@",
                    width: 220
                },{
                    field: "type",
                    title: "@Type@",
                    editor: typeEditor
                },{
                    field: "sub_type",
                    title: "@Diallist type@",
                    editor: subtypeEditor,
                    template: '<img style="height: 16px" src="api/v1/image_text/getDiallistTypeName/#: data.sub_type #">',
                    values: res.data 
                },{
                    command: ["edit", "destroy"],
                    width: 200
                }]
        }; 
        Table.init();
    });
</script>
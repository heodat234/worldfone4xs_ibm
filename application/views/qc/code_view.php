<script>
var typeList = ["Call", "Chat", "Email", "Document skill"]; 
function typeEditor(container, options) {
	var value = $("#top-row").find("button.selected").data("value");
	options.model.type = options.model.type ? options.model.type : value;
    let field = options.field;
    var select = $(`<input name="${field}"/>`)
        .appendTo(container)
        .kendoDropDownList({
            valuePrimitive: true,
            dataSource: typeList
        }).data("kendoDropDownList");
};

function languageEditor(container, options) {
    let field = options.field;
    var select = $(`<input name="${field}"/>`)
        .appendTo(container)
        .kendoDropDownList({
            valuePrimitive: true,
            dataSource: ["ENG", "VIE"]
        }).data("kendoDropDownList");
};

function addForm(ele) {
	Table.grid.addRow();
}

var Config = {
    crudApi: `${ENV.restApi}`,
    templateApi: `${ENV.templateApi}`,
    collection: "qc_code",
    observable: {
    },
    model: {
        id: "id",
        fields: {
            point: {type: "number"},
            index: {type: "number"},
        }
    },
    columns: [{
            selectable: true,
            width: 32,
            hidden: true
        },{
            field: "index",
            title: "#",
            editor: () => readOnly,
            width: 50
        },{
            field: "type",
            title: "@Type@",
            values: typeList,
            editor: typeEditor,
            template: (dataItem) => gridArray(dataItem.type),
            width: 140
        },{
            field: "content",
            title: "@Content@",
        },{
            field: "code",
            title: "@Code@",
            width: 160
        },{
            field: "point",
            title: "@Point@",
            width: 120
        },{
            title: `@Action@`,
            command: [{name: "edit", text: "@Edit@"}, {name: "destroy", text: "@Delete@"}],
            width: 220
        }]
}; 

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
            pageSize: 5,
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
        	toolbar: [{name: "create", text: "@Create@"}],
        	editable: "inline",
            dataSource: dataSource,
            resizable: true,
            pageable: {
                refresh: true,
            },
            sortable: true,
            scrollable: false,
            columns: this.columns,
            filterable: KENDO.filterable,
            noRecords: {
                template: `<h2 class='text-danger'>${KENDO.noRecords}</h2>`
            }
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
window.onload = function() {
    $("#tabstrip").kendoTabStrip({
        animation:  {
            open: {
                effects: "fadeIn"
            }
        },
        activate: function (e) {
            kendoConsole.log("Activated: " + $(e.item).find("> .k-link").text());
        }
    });
    kendo.bind($("#config-form"), {});
	Table.init();
    var collection = "configtype";
    var dataSource = new kendo.data.DataSource({
        filter: {field: "type", operator: "eq", value: ENV.type},
        serverFiltering: true,
        transport: {
            read: ENV.restApi + collection,
            parameterMap: parameterMap
        },
        schema: {
            data: "data",
            total: "total",
        }
    });
    $.ajax({
        url: ENV.vApi + collection + "/detail/" + ENV.type,
        type: "GET",
        success: function(response) {
            var observable =  kendo.observable({
                item: {call_init_point: response.call_init_point},
                save: function(e) {
                    var item = this.get("item").toJSON();
                    $.ajax({
                        url: ENV.restApi + collection + "/" + response.id,
                        type: "PUT",
                        data: JSON.stringify(item),
                        contentType: "application/json; charset=utf-8",
                        success: syncDataSource,
                        error: errorDataSource
                    })
                }
            })
            kendo.bind($(".mvvm"), observable);
        }
    })
    // dataSource.read().then(() => {
    //     var data = dataSource.data();
    //     if(data.length) {
    //         var item = data[0];
    //         
    //     }
    // })   
}

function filterType(ele) {
	$(ele).closest("div").find("button").removeClass("selected");
	$(ele).addClass("selected");
	var value = $(ele).data('value');
	var text = $(ele).text();
	if(value) {
		Table.dataSource.filter({field: "type", operator: "eq", value: value});
	} else Table.dataSource.filter({});
}
</script>

<style>
	.btn-group .selected {
		background-color: lightgray;
	}
</style>

<!-- Page content -->
<div id="page-content">
    <!-- Table Styles Header -->
    <ul class="breadcrumb breadcrumb-top">
        <li><a href="<?= base_url("users") ?>">@Quality control@</a></li>
        <li>@Config@</li>
    </ul>
    <!-- END Table Styles Header -->
    <div id="tabstrip">
        <ul>
            <li class="k-state-active">
                QC @Config@
            </li>
            <li>
                @QC Code@
            </li>
        </ul>
        <div>
            <div class="container-fluid">
                <div class="row mvvm" style="padding-bottom: 25px; background-color: whitesmoke">
                    <div class="col-sm-12">
                        <h4 class="fieldset-legend" style="margin: 0 0 20px"><span style="font-weight: 500; line-height: 1">QC @Config@</span></h4>
                    </div>
                    <div class="col-sm-12">
                        <form class="form-horizontal" id="config-form" style="margin-top: 10px">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="control-label col-sm-4">@Call@ @Initial@ @point@</label>
                                    <div class="col-sm-8">
                                        <input data-role="numerictextbox" style="width: 100%" data-bind="value: item.call_init_point">
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                
                            </div>
                            <div class="col-sm-12 text-center">
                                <a role="button" href="javascript:void(0)" class="k-button" data-bind="click: save"><b>@Save@</b></a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div>
            <div class="container-fluid">
                <div class="row" style="background-color: whitesmoke">
                    <div class="col-sm-6">
                        <h4 class="fieldset-legend" style="margin: 0 0 20px"><span style="font-weight: 500; line-height: 1">@QC Code@</span></h4>
                    </div>
                    <div class="col-sm-6 text-center">
                        <div class="btn-group btn-group-sm" style="margin-top: 10px">
                            <button class="btn btn-alt btn-default selected" onclick="filterType(this)" data-value="">@All@</button>
                            <button class="btn btn-alt btn-default" onclick="filterType(this)" data-value="Call">Call</button>
                            <button class="btn btn-alt btn-default" onclick="filterType(this)" data-value="Chat">Chat</button>
                            <button class="btn btn-alt btn-default" onclick="filterType(this)" data-value="Email">Email</button>
                            <button class="btn btn-alt btn-default" onclick="filterType(this)" data-value="Document skill">Document skill</button>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12" style="padding: 0">
                        <!-- Table Styles Content -->
                        <div id="grid"></div>
                        <!-- END Table Styles Content -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="action-menu">
        <ul>
            <a href="javascript:void(0)" data-type="update" onclick="openForm({title: 'Edit Profile', width: 1000}); editForm(this)"><li><i class="fa fa-pencil-square-o text-warning"></i><span>Edit</span></li></a>
            <a href="javascript:void(0)" data-type="create" onclick="cloneDataItem(this)"><li><i class="fa fa-clipboard text-info"></i><span>Clone</span></li></a>
            <a href="javascript:void(0)" data-type="delete" onclick="deleteDataItem(this)"><li><i class="fa fa-times-circle text-danger"></i><span>Delete</span></li></a>
            <li class="devide"></li>
        </ul>
    </div>
</div>
<!-- END Page Content -->
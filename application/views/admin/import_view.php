<script>
var Config = {
    crudApi: `${ENV.restApi}`,
    templateApi: `${ENV.templateApi}`,
    collection: "import_file",
    observable: {
    },
    model: {
        id: "id",
        fields: {
        	name: {type: "string"},
        	exists: {type: "boolean"},
        }
    },
    columns: [{
            selectable: true,
            width: 32,
            locked: true
        },{
            field: "file_name",
            title: "File name",
            width: 150
        },{
            field: "exists",
            title: "Exists",
            template: data => gridBoolean(data.exists),
            width: 80
        },{
            field: "file_path",
            title: "File path"
        },{
            field: "command",
            title: "Command",
        },{
            field: "status",
            title: "Status",
            width: 70
        },{
        	field: "modify_time",
            title: "Modify time",
            template: data => gridTimestamp(data.modify_time),
            width: 140
        },{
            title: "Action",
            command: ["edit","destroy"],
            width: 180
        }]
}; 
</script>

<!-- Table Styles Header -->
<ul class="breadcrumb breadcrumb-top">
    <li>Admin</li>
    <li>Import</li>
    <li class="pull-right none-breakcrumb">
        <div class="input-group-btn column-widget">
            <a role="button" class="btn btn-sm btn-default btn-alt" onclick="Table.grid.addRow()"><i class="fa fa-plus"></i> <b>@Add@</b></a>
            <a role="button" class="btn btn-sm btn-default btn-alt dropdown-toggle" data-toggle="dropdown" onclick="editColumns(this)"><i class="fa fa-calculator"></i> <b>@Edit Columns@</b></a>
            <ul class="dropdown-menu dropdown-menu-right" style="width: 300px">
                <li class="dropdown-header text-center">@Choose columns will show@</li>
                <li class="filter-container" style="padding-bottom: 15px">
                    <div class="form-horizontal" data-bind="source: columns" data-template="column-template"/>
                </li>
            </ul>
        </div>
    </li>
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

<div id="action-menu">
    <ul>
        <a href="javascript:void(0)" data-type="import" onclick="importFile(this)"><li><i class="fa fa-upload text-danger"></i><span>@Import@</span></li></a>
    </ul>
</div>

<script type="text/javascript">
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
            editable: "inline",
            dataSource: dataSource,
            resizable: true,
            pageable: {
                refresh: true
            },
            sortable: true,
            scrollable: false,
            columns: this.columns,
            filterable: true,
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
	Table.init();
}

function uploadMany() {

}
</script>
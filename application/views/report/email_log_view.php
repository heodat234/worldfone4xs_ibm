<script>
var Config = {
    crudApi: `${ENV.restApi}`,
    templateApi: `${ENV.templateApi}`,
    collection: "email_logs",
    observable: {
    },
    model: {
        id: "id",
        fields: {
            sendedAt: {type: "date"}
        }
    },
    parse: function(response) {
        response.data.map(doc => {
            doc.sendedAt = new Date(doc.sendedAt * 1000);
        })
        return response;
    },
    columns: [{
            field: "sendedAt",
            title: "@Sended at@",
            format: "{0: dd/MM/yy HH:mm}",
            width: 140
        },{
            field: "sendedBy",
            title: "@Sended by@",
            width: 100
        },{
            field: "email",
            title: "@Email@",
            width: 200
        },{
            field: "subject",
            title: "@Subject@"
        },{
            field: "createdBy",
            title: "@Created by@",
            width: 160
        }],
    filterable: KENDO.filterable
}; 
</script>

<script type="text/x-kendo-template" id="detail-template">
    <div data-role="tabstrip">
        <ul>
            <li class="k-state-active">
                @Content@
            </li>
            <li>
                @All@
            </li>
        </ul>
        <div>
            <div data-bind="html: content"></div>
        </div>
        <div>
            <div data-bind="html: message"></div>
        </div>
    </div>
</script>

<script type="text/javascript">
var Table = function() {
    return {
        dataSource: {},
        grid: {},
        columns: Config.columns,
        init: async function() {
            var dataSource = this.dataSource = new kendo.data.DataSource({
                serverFiltering: true,
                serverPaging: true,
                serverSorting: true,
                serverGrouping: false,
                sort: [{field: "sendedAt", dir: "desc"}],
                pageSize: 10,
                batch: false,
                schema: {
                    data: "data",
                    total: "total",
                    groups: "groups",
                    model: Config.model,
                    parse: Config.parse ? Config.parse : res => res
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
                        type: "DELETE",
                    },
                    parameterMap: parameterMap
                },
                sync: syncDataSource,
                error: errorDataSource
            });

            var grid = this.grid = $(`#grid`).kendoGrid({
                dataSource: dataSource,
                resizable: true,
                pageable: {
                    refresh: true,
                    pageSizes: true
                },
                sortable: true,
                scrollable: true,
                columns: this.columns,
                filterable: true,
                editable: false,
                detailTemplate: kendo.template($("#detail-template").html()),
                detailInit:  function(e) {
                    kendo.bind($(e.detailCell), e.data)
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
            var menu = $("#email-action-menu");
            if(!menu.length) return;
            
            $("html").on("click", function() {menu.hide()});

            $(document).on("click", `#grid-email tr[role=row] a.btn-action`, function(e){
                let row = $(e.target).closest("tr");
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
                    menu.find("a").data('uid',uid);

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
</script>

<!-- Table Styles Header -->
<ul class="breadcrumb breadcrumb-top">
    <li>@Report@</li>
    <li>@Email log@</li>
    <li class="pull-right none-breakcrumb">
        <a role="button" class="btn btn-sm" data-field="createdAt" onclick="customFilter(this, Table.dataSource)"><i class="fa fa-filter"></i> <b>@Custom Filter@</b></a>
        <div class="input-group-btn column-widget">
            <a role="button" class="btn btn-sm dropdown-toggle" data-toggle="dropdown" onclick="editColumns(this)"><i class="fa fa-calculator"></i> <b>@Edit Columns@</b></a>
            <ul class="dropdown-menu dropdown-menu-right" style="width: 300px">
                <li class="dropdown-header text-center">@Choose columns will show@</li>
                <li class="filter-container" style="padding-bottom: 15px">
                    <div class="form-horizontal" data-bind="source: columns" data-template="column-template"/>
                </li>
            </ul>
        </div>
        <a role="button" class="btn btn-sm" onclick="Table.grid.saveAsExcel()"><i class="fa fa-file-excel-o"></i> <b>@Export@</b></a>
    </li>
</ul>
<!-- END Table Styles Header -->

<div class="container-fluid">
    <div class="row filter-mvvm" style="display: none; margin: 10px 0">
    </div>
    <div class="row">
        <div class="col-sm-12" style="height: 80vh; overflow-y: auto; padding: 0">
            <!-- Table Styles Content -->
            <div id="grid"></div>
            <!-- END Table Styles Content -->
        </div>
    </div>
</div>

<script type="text/javascript">
    window.onload = function() {
        Table.init();
    }
    
</script>
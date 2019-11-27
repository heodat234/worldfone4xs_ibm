<script>
var Config = {
    crudApi: `${ENV.vApi}`,
    templateApi: `${ENV.templateApi}`,
    collection: "lawsuit_history",
    observable: {
    },
    scrollable: true,
    model: {
        id: "id",
        fields: {
        
        }
    },
    parse: function(res) {
    	res.data.map(doc => {
    		
    	})
    	return res;
    },
    columns: [
    	{
    		field: "type",
    		title: "@Type@"
    	},
    	{
    		field: "contract_no",
    		title: "@Contract No.@"
    	},
    	{
    		field: "lawsuit_date",
    		title: "@Lawsuit Date@",
    		format: "{0: dd/MM/yy}",
    	},
    	{
    		field: "responsible_date",
    		title: "@Responsible Date@"
    	},
    
    ],
    filterable: KENDO.filterable
}; 
</script>

<script>
var Table = function() {
    var columnsStorage = JSON.parse(sessionStorage.getItem("columns_" + ENV.currentUri));
    if(columnsStorage) {
        Config.columns.map((col, idx) => {
            col.hidden = columnsStorage[idx].hidden;
        })
    }
    var pageStorage = Number(sessionStorage.getItem("page_" + ENV.currentUri));
    if(pageStorage) {
        Config.page = pageStorage;
    }
    var sortStorage = JSON.parse(sessionStorage.getItem("sort_" + ENV.currentUri));
    if(sortStorage) {
        Config.sort = sortStorage;
    }
    var filterStorage = JSON.parse(sessionStorage.getItem("filter_" + ENV.currentUri))
    if(filterStorage) {
        Config.filter = filterStorage;
    }
    var columnsStorage = JSON.parse(sessionStorage.getItem("columns_" + ENV.currentUri));
    if(columnsStorage) {
        var fieldToIndex = {};
        var fieldToWidth = {};
        columnsStorage.forEach((col, idx) => {
            if(col.field) {
                fieldToIndex[col.field] = idx;
            }
        });
        Config.columns.sort(function(a, b) {
            if(a.field && b.field) {
                return fieldToIndex[a.field] - fieldToIndex[b.field];
            } return -1;
        });
    }
    return {
        dataSource: {},
        grid: {},
        columns: Config.columns,
        gridOptions: {},
        init: function() {
            var dataSource = this.dataSource = new kendo.data.DataSource({
                serverFiltering: true,
                serverPaging: true,
                serverSorting: true,
                serverGrouping: false,
                filter: Config.filter ? Config.filter : null,
                sort: Config.sort ? Config.sort : null,
                page: Config.page ? Config.page : null,
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
                        url: Config.crudApi + Config.collection + "/read"
                    },
                    parameterMap: parameterMap
                },
                sync: syncDataSource,
                error: errorDataSource
            });

            this.gridOptions = Object.assign({
                dataSource: dataSource,
                excel: {allPages: true},
                excelExport: function(e) {
                  var sheet = e.workbook.sheets[0];

                  for (var rowIndex = 1; rowIndex < sheet.rows.length; rowIndex++) {
                    var row = sheet.rows[rowIndex];
                    for (var cellIndex = 0; cellIndex < row.cells.length; cellIndex ++) {
                        if(row.cells[cellIndex].value instanceof Date) {
                            row.cells[cellIndex].format = "dd-MM-yy hh:mm:ss"
                        }
                    }
                  }
                },
                resizable: true,
                pageable: {
                    refresh: true,
                    pageSizes: [5, 10, 20, 50, 100],
                    input: true,
                    messages: KENDO.pageableMessages ? KENDO.pageableMessages : {}
                },
                sortable: true,
                reorderable: Boolean(Config.reorderable),
                scrollable: Boolean(Config.scrollable),
                columns: this.columns,
                filterable: Config.filterable ? Config.filterable : true,
                editable: false,
                noRecords: {
                    template: `<h2 class='text-danger'>${KENDO.noRecords}</h2>`
                },
                page: function(e) {
                    sessionStorage.setItem("page_" + ENV.currentUri, e.page);
                },
                sort: function(e) {
                    sessionStorage.setItem("sort_" + ENV.currentUri, JSON.stringify(e.sort));
                },
                filter: function(e) {
                    sessionStorage.setItem("filter_" + ENV.currentUri, JSON.stringify(e.filter));
                },
                columnReorder: function(e) {
                    setTimeout(() => {
                        sessionStorage.setItem("columns_" + ENV.currentUri, JSON.stringify(e.sender.columns));
                    }, 100);
                },
                dataBinding: function() {
                    record = (dataSource.page() -1) * dataSource.pageSize();
                }
            }, Config.gridOptions);

            var grid = this.grid = $("#grid").kendoGrid(this.gridOptions).data("kendoGrid");

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
            if(!menu.length) return;
            
            $("html").on("click", function() {menu.hide()});

            $(document).on("click", "#grid tr[role=row] a.btn-action", function(e){
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
    <li>@Manage@</li>
    <li>@Data@</li>
    <li>@Field action@</li>
    <li class="pull-right none-breakcrumb">
        <div class="input-group-btn column-widget">
            <a role="button" class="btn btn-sm dropdown-toggle" data-toggle="dropdown" onclick="editColumns(this)"><i class="fa fa-calculator"></i> <b>@Edit Columns@</b></a>
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
		<div id="action-menu">
		    <ul>
		        <a href="javascript:void(0)" data-type="detail" onclick="detailData(this)"><li><i class="fa fa-exclamation-circle text-info"></i><span>@Detail@</span></li></a>
		    	<li class="devide"></li>
		        <a href="javascript:void(0)" data-type="delete" onclick="deleteDataItem(this)"><li><i class="fa fa-times-circle text-danger"></i><span>@Delete@</span></li></a>
		    </ul>
		</div>
    </div>
</div>
<script type="text/javascript">
	window.onload = function() {
     <?php if(!empty($filter)) { ?>
        Config.filter = <?= $filter ?>;
    <?php } ?>
		Table.init();
	}
</script>
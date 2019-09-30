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
                        url: Config.crudApi + Config.collection
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
                scrollable: Boolean(Config.scrollable),
                columns: this.columns,
                filterable: Config.filterable ? Config.filterable : true,
                editable: false,
                detailTemplate: kendo.template($("#detail-template").html()),
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
                }
            }, Config.gridOptions);

            var grid = this.grid = $("#grid_1").kendoGrid(this.gridOptions).data("kendoGrid");

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

            $(document).on("click", "#grid_1 tr[role=row] a.btn-action", function(e){
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
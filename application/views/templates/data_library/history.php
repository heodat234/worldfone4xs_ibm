<div class="col-sm-3" style="margin: 10px 0" id="page-widget"></div>
<div class="col-sm-9 filter-mvvm" style="display: none; margin: 10px 0"></div>
<div class="col-sm-12" style="overflow-y: auto; padding: 0">
	<div id="grid_1"></div>
</div>
<div id="action-menu">
    <ul>
    	<a href="javascript:void(0)" data-type="detail" onclick="detailData(this)"><li><i class="fa fa-exclamation-circle text-info"></i><span>@View Detail@</span></li></a>
    	
    </ul>
</div>
<style type="text/css">
	.swal-footer {
	    text-align: center;
	}
	.swal-button--ftp {
		background: #0f73a5;
	}
	.swal-button--danger {
		background: #f1be06;
	}
    .grid {
        color: firebrick;
    }
    .progress{
        width:60%;
        height: 15px;
        border-radius: 20px;
    }
    .status-upload{
        width: 60%;
        margin-top: -17px;
        opacity: 0.5;
        text-align: center;
        font-size: 10px;
    }
</style>
<script id="detail-template" type="text/x-kendo-template">
  <div class="jsoneditor" style="width: 100%; height: 400px;"></div>
</script>
<script>
var Config = {
    crudApi: `${ENV.restApi}`,
    templateApi: `${ENV.templateApi}`,
    collection: "import_history",
    filter: {field: "collection", operator: "eq", value: 'Datalibrary'},
    observable: {
    },
    model: {
        id: "id",
        fields: {
           	begin_import: {type: "date"},
            complete_import: {type: "date"},
        }
    },
    parse: function (response) {
        response.data.map(function(doc) {
            doc.begin_import = new Date(doc.begin_import * 1000);
            doc.complete_import = doc.complete_import ? new Date(doc.complete_import * 1000) : null;
            return doc;
        })
        return response;
    },
    columns: [{
            field: "begin_import",
            title: "@Begin Imported@",
            template: function(dataItem) {
                return (kendo.toString(dataItem.begin_import, "dd/MM/yy H:mm:ss") ||  "").toString();
            }
        },{
            field: "complete_import",
            title: "@Finish Imported@",
            template: function(dataItem) {
                return (kendo.toString(dataItem.complete_import, "dd/MM/yy H:mm:ss") ||  "").toString();
            }
        },{
            field: "file_name",
            title: "@File Name@",
        },{
            field: "source",
            title: "@Source@",
        },{
            field: "status",
            title: "@Status@",
            locked: true,
            'template': kendo.template($('#status-template').html()),
        }
        // ,{
        //     // Use uid to fix bug data-uid of row undefined
        //     template: '<a role="button" class="btn btn-sm btn-circle btn-action" style="background: yellow;" data-uid="#: uid #"><i class="fa fa-ellipsis-v"></i></a>',
        //     width: 20
        // }
        ]
};
</script>
<!-- <script src="<?= STEL_PATH.'js/tablev2.js' ?>"></script> -->

<script id="status-template" type="text/x-kendo-template">
    #if(status == 1) {#
        <h4 style='font-weight: bold'>Success</h4>
    #} else if(status == 0) {#
        <h4 style='font-weight: bold'>Fail</h4>
    #} else if(status == 2) {#
        <div class='col-sm-8'><div class='progress' data-role='progressbar' data-min='0' data-max='100' data-value='false'></div><div class='status-upload'>Loading...</div></div><div class='col-sm-4 cancel-upload'><a href='javascript:void(0)' onclick='cancelUpload(#= JSON.stringify(id) #)'>Cancel</a></div>
    #}#
</script>

<script type="text/javascript">
    var router = new kendo.Router({routeMissing: function(e) { router.navigate("/") }});
	function re_Upload(ele) {
		swal({
		    title: "Do you want to Re-Upload this file?",
		    // text: "Once deleted, you will not be able to recover this document!",
		    icon: "warning",
		    buttons: {
		    	ftp: {text:"By FTP",value:"ftp"},
		    	confirm: {text:"By Manual", value:"manual"},
			    cancel: "Cancel"
			},
		 	dangerMode: true,
	    })
	    .then((value) => {
	    	var uid = $(ele).data('uid');
			var dataItem = Table.dataSource.getByUid(uid);
		  	switch (value) {
		    	case "ftp":
		    		console.log(dataItem);
	      			swal("Pikachu fainted!");
		      		break;
		    	case "manual":
		      		swal("Gotcha!", "Pikachu was caught!", "success");
		      		break;
		    	default:

		  	}
		});

	}


	function detailData(ele) {
        var uid = $(ele).data('uid');
        var dataItem = Table.dataSource.getByUid(uid);
        router.navigate(`/detail_customer/${dataItem.id}`);
    }

    $(document).on("click", ".grid-name", function() {
        detailData($(this).closest("tr"));
    })

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
                    sort: null,
                    page: null,
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

                var grid = this.grid = $("#grid_1").kendoGrid({
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
                        pageSizes: true,
                        input: true,
                        messages: KENDO.pageableMessages ? KENDO.pageableMessages : {}
                    },
                    sortable: true,
                    scrollable: Boolean(Config.scrollable),
                    columns: this.columns,
                    filterable: Config.filterable ? Config.filterable : true,
                    editable: false,
                    detailTemplate: 'Danh sách lỗi: <div class="grid"></div>',
                    detailInit: function(e) {
                        e.detailRow.find(".grid").kendoGrid({
                          dataSource: e.data.error
                        });
                    },
                    dataBound: function() {
                        this.expandRow(this.tbody.find("tr.k-master-row").first());
                        var grid = this;
                        grid.tbody.find(".progress").each(function(e) {
                            var row = $(this).closest("tr");
                          var model = grid.dataItem(row);

                          $(this).kendoProgressBar({
                            max: 1000,
                            // value: model.progress
                            value: false
                          })
                        });
                    },
                    noRecords: {
                        template: `<h2 class='text-danger'>${KENDO.noRecords}</h2>`
                    }
                }).data("kendoGrid");

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
	$( document ).ready(function() {
        Table.init();
    });


    function cancelUpload(id) {
        $.ajax({
            url: ENV.vApi + `data_library/update_import_log/${id}`,
            data: kendo.stringify({'status': 0}),
            error: errorDataSource,
            contentType: "application/json; charset=utf-8",
            type: "PUT",
            success: function() {
                $("#grid_1").data("kendoGrid").dataSource.read();
            }
        })
    }
</script>
<script>

</script>
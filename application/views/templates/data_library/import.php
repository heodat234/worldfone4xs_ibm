<div class="col-sm-4">
	<div data-bind="invisible: visibleData">
		<h4 class="fieldset-legend" style="margin: 0 0 20px"><span style="font-weight: 500">IMPORT</span></h4>
		<div id="popup-tabstrip" data-role="tabstrip" style="margin-top: 2px">
	        <ul>
	            <li class="k-state-active">
	                CSV
	            </li>
	            <li>
	                FTP
	            </li>
	        </ul>
	        <div>
	            <div class="container-fluid">
	                <div class="row">
	                    <div class="col-sm-12">
							<input id="excel-file" type="file" name="file" />
						</div>

	                </div>
	            </div>
	        </div>
	        <div>
	            <div class="container-fluid">
	                <div id="grid-3"></div>
	            </div>
	        </div>
	    </div>
	</div>
    <div data-bind="visible: visibleData">
    	<div data-bind="invisible: visibleAssign">
			<h4 class="fieldset-legend" style="margin: 0 0 20px"><span style="font-weight: 500">REORDER DATA</span></h4>
			<div class="col-xs-8" style="padding: 2px">
				<div data-template="diallist-detail-field-template" data-bind="source: item.columns"></div>
			</div>
			<div class="col-xs-4" style="padding: 2px">
				<div data-template="data-field-template" data-bind="source: dataColumns" id="data-field"></div>
			</div>
			<div class="col-xs-12 text-center"><button data-role="button" data-bind="click: goToAssign">Agree</button></div>
		</div>
	</div>

</div>
<div class="col-sm-8">
</div>
<div class="col-md-12">
	<h4 class="fieldset-legend" style="margin: 0 0 20px"><span style="font-weight: 500">DATA</span></h4>
	<div>
		<div id="spreadsheet"></div>
	</div>
</div>

<style>
	.item {
        margin: 2px;
        padding: 0 10px 0 0;
        min-width: 50px;
        background-color: #fff;
        border: 1px solid rgba(0,0,0,.1);
        border-radius: 3px;
        font-size: 1em;
        line-height: 2.5em;
    }
    .handler {
        display: inline-block;
        width: 30px;
        margin-right: 10px;
        border-radius: 3px 0 0 3px;
        background-color: deepskyblue;
        cursor: move;
    }

    .handler:hover {
        background-color: #2db245;
    }
    .k-spreadsheet {
     width: 100%;
    }
</style>
<!-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@8"></script> -->

<script type="text/javascript">
	// $("#spreadsheet").kendoSpreadsheet({toolbar: false});
	// $("#spreadsheet").hide();
   	// var spreadsheet = $("#spreadsheet").data("kendoSpreadsheet");

	var ALLOWED_EXTENSIONS = [".csv"];

    $("#excel-file").kendoUpload({
        async: {
        	autoUpload: false,
            saveUrl: Config.crudApi+"import/upload/Datalibrary"
        },
        multiple: false,
        localization: {
            "select": "Chọn 1 tệp để nhập..."
        },
        select: function(e) {
            var extension = e.files[0].extension.toLowerCase();
            if (ALLOWED_EXTENSIONS.indexOf(extension) == -1) {
                alert("Please, select a supported file format csv");
                e.preventDefault();
            }
            // $("#spreadsheet").show();
        	// spreadsheet.fromFile(e.files[0]['rawFile']);
        },
        clear: onClear,
        progress: onProgress,
        success: function(e) {
            if (e.response.status == -1) {
                swal({text: "Quá trình upload đang được thực hiện. Vui lòng đợi vài phút và kiểm tra trong lịch sử."});
            }else{
                notification.show(e.response.message, e.response.status ? "success" : "error");
                // router.navigate(`/history`);
            }
        }
    });
    function onClear(e) {
        // $("#spreadsheet").hide();
    }
  	function onProgress(e) {
        var files = e.files;
        // console.log(e.percentComplete);
    }

	

</script>
<script>
    var router = new kendo.Router({routeMissing: function(e) { router.navigate("/") }});
    var Config = {
        crudApi: `${ENV.restApi}`,
        templateApi: `${ENV.templateApi}`,
        collection: "Import",
        observable: {},
        model: {
            id: "id",
            fields: { }
        },
        columns: [{
                field: "file_name",
                title: "File Name",
                template: '<a role="button" href="javascript:void(0)" class="btn btn-sm" onclick="uploadFile(this)"><b>#= file_name #</b></a>'
            }]
    };

    var detailTable = function() {
        return {
            dataSource: {},
            grid: {},
            columns: Config.columns,
            init: async function() {
                var dataSource = this.dataSource = new kendo.data.DataSource({
                    serverFiltering: true,
                    serverPaging: true,
                    serverSorting: true,
                    sort: [{field: "index", dir: "asc"}],
                    pageSize: 10,
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
                        }
                        // parameterMap: parameterMap
                    },
                    sync: syncDataSource,
                    error: errorDataSource
                });


                var grid = this.grid = $(`#grid-3`).kendoGrid({
                    dataSource: dataSource,
                    resizable: true,
                    pageable: {
                        refresh: true,
                        pageSizes: true,
                        input: true
                    },
                    sortable: true,
                    scrollable: true,
                    height: '80vh',
                    columns: this.columns,
                    filterable: true,
                    editable: false
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



            }
        }
    }();

    function uploadFile(e) {
                var gridview = $("#grid-3").data("kendoGrid"),
                    selectedNode = gridview.select(),
                    dataItem = gridview.dataItem($(e).closest("tr"));
                swal({
                    title: "Bạn có muốn import file này?",
                    icon: "warning",
                    dangerMode: true,
                    buttons: {
                        confirm: {text:"Import", value:"import"},
                        cancel: "Cancel"
                    }
                })
                .then((value) => {
                    if (value == 'import') {
                        $.ajax({
                            url: Config.crudApi + Config.collection + "/importFTP/Datalibrary",
                            type: "POST",
                            data: {file_path: dataItem.file_path, file_name: dataItem.file_name},
                            success: function(result) {
                                notification.show(result.message, result.status ? "success" : "error");
                                if (result.status == 0) {
                                    router.navigate(`/history`);
                                }
                            },
                            error: errorDataSource
                        })
                    }
                });
    }

    detailTable.init();
</script>
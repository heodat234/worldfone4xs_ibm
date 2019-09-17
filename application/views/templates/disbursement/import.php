<div class="col-sm-4">
	<div data-bind="invisible: visibleData">
		<h4 class="fieldset-legend" style="margin: 0 0 20px"><span style="font-weight: 500">IMPORT</span></h4>
		<div id="popup-tabstrip" data-role="tabstrip" style="margin-top: 2px">
	        <ul>
	            <li class="k-state-active">
	                EXCEL
	            </li>
	            <li>
	                CSV
	            </li>
	           
	        </ul>
	        <div>
	            <div class="container-fluid">
	                <div class="row">
	                    <div class="col-sm-12">
							<input id="upload" type="file" />
						</div>
						<div class="col-sm-12" style="display: none">
							<div id="spreadsheet"></div>
						</div>
	                </div>
	            </div>
	        </div>
	        <div>
	            <div class="container-fluid">
	                2
	            </div>
	        </div>
	        
	    </div>
	</div>
    <div data-bind="visible: visibleData">
    	<div data-bind="invisible: visibleAssign">
			<h4 class="fieldset-legend" style="margin: 0 0 20px"><span style="font-weight: 500">REORDER DATA</span></h4>
			<div class="col-xs-8" style="padding: 2px">
				<div data-template="field-template" data-bind="source: columns"></div>
			</div>
			<div class="col-xs-4" style="padding: 2px">
				<div data-template="data-field-template" data-bind="source: dataColumns" id="data-field"></div>
			</div>
			<div class="col-xs-12 text-center"><button data-role="button" data-bind="click: import">Import</button></div>
		</div>
	</div>
</div>
<div class="col-sm-8">
	<h4 class="fieldset-legend" style="margin: 0 0 20px"><span style="font-weight: 500">DATA</span></h4>
	<div>
		<div data-role="grid" id="data-grid"
			data-sortable="true"
	        data-editable="true"
	        data-bind="source: data, visible: visibleData"/>
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
</style>
<script type="text/javascript">
	$("#upload").kendoUpload({
        async: {
        	autoUpload: false,
            saveUrl: "/worldfone4xs_ibm/import/upload"
        },
        multiple: false,
        localization: {
            "select": "Select file to import..."
        },
        select: function(e) {
            var extension = e.files[0].extension.toLowerCase();
            if (ALLOWED_EXTENSIONS.indexOf(extension) == -1) {
                alert("Please, select a supported file format");
                e.preventDefault();
            }
            $("#spreadsheet").show();
        	spreadsheet.fromFile(e.files[0]['rawFile']);
        },
        clear: onClear,
        progress: onProgress,
        success: function(e) {
	        notification.show(e.response.message, e.response.status ? "success" : "error");
        }
    });
    function onClear(e) {
        $("#spreadsheet").hide();
    }
  	function onProgress(e) {
        var files = e.files;
        console.log(e.percentComplete);
    }




	function getDataFromSpreadSheet(rows) {
		var data = [];
		var headerData = rows[0].cells;
		
		rows.forEach(function(row, index) {
			var doc = {}; 
			row.cells.forEach(function(cell, idx){
				if(index != 0 && cell.value != undefined) {
					doc["C"+cell.index] = cell.value;
				}
			})
			if(Object.keys(doc).length !== 0) {
				data.push(doc);
			}
		})
		return data;
	}
	$("#spreadsheet").kendoSpreadsheet({
		toolbar: false,
		sheetsbar: false,
		excelImport: function(e) {
			$("#bg-loader").html(HELPER.loaderHtml).show();
			e.promise.done(function() {
				$("#bg-loader").html("").hide();
				var rows = e.sender.toJSON().sheets[0].rows;
				if(rows.length) {
					var model = document.getElementById("data-grid").kendoBindingTarget.source,
						headerData = rows[0].cells,
						columns = [],
						data = getDataFromSpreadSheet(rows),
						grid = $("#data-grid").data("kendoGrid");

					model.set("file", e.file);
					grid.dataSource.data(data);
					var defaultColumns = model.columns.slice(0);
					headerData.forEach(function(cell, index) {
						var title = defaultColumns[index] ? defaultColumns[index].title : "Undefined";
						columns.push({
							title: title,
							field: "C"+index,
							index: index
						});
					})
					
					model.set("visibleData", true);
					model.set("originalDataColumns", columns);
					columns.map(ele => ele.title += ` (C${ele.index})`);
					model.set("dataColumns", columns);
					grid.setOptions({columns: columns});
					$("#data-grid").show();
					$("#data-field").kendoSortable({
				        handler: ".handler",
				        hint: function(element) {
				            return element.clone().addClass("hint");
				        },
				        change: function(e) {
				        	var columns = model.moveDataColumns(e.oldIndex, e.newIndex);
				        	grid.setOptions({columns: columns});
				        }
				    });
				}
			});
		}
	});

	$("#excel-file").on("change", function() {
        var spreadsheet = $("#spreadsheet").data("kendoSpreadsheet");
        spreadsheet.fromFile(this.files[0]);
    });
</script>

<script type="text/x-kendo-template" id="field-template">
	<div class="item">
        <span style="margin-left: 10px" data-bind="text: title"></span>
        <i class="fa fa-arrow-circle-o-right text-success" style="float: right; margin-top: 10px"></i>
    </div>
</script>
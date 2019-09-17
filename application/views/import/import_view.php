<div class="box wide">
    
    <div class="box-col">
        <h4>Import</h4>
        <input type="file" name="file" id="upload" />
        <!-- <button id="uploadAll" class="k-button">Start upload</button> -->
    </div>
</div>
<div id="spreadsheet" style="width: 100%;"></div>
<style>
 .download { width: 260px; }
</style>
<script>
    $(function() {
    	$("#spreadsheet").kendoSpreadsheet();
    	$("#spreadsheet").hide();
       	var spreadsheet = $("#spreadsheet").data("kendoSpreadsheet"); 
        var ALLOWED_EXTENSIONS = [".xlsx",".csv"];

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
        $(".download").click(function () {
            $("#download-data").val(JSON.stringify(spreadsheet.toJSON()));
            $("#download-extension").val($(this).data("extension"));
        });
    });
</script>

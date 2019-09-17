<li>
    <a href="javascript:void(0)" class="btn btn-alt btn-sm btn-default" data-toggle="tooltip" title="@Capture screen@" data-placement="bottom" onclick="captureScreen()">
        <i class="fa fa-camera"></i>
    </a>
</li>

<script type="text/javascript">	
function captureScreen(selector = "body", filename = "Screen-" + Date.now()) {
	notification.show("@Wait a minute@.");
	kendo.drawing.drawDOM($(selector))
        .then((group) => {
			return kendo.drawing.exportImage(group);
        })
        .done((dataImg) => {
        	swal({
		        title: `@Capture screen@.`,
		        text: `@Save image to@?`,
		        icon: "info",
		        buttons: {
		        	client: "@Client@",
		        	server: "@Server@",
		        	cancel: true
		        }
		    })
		    .then((key) => {
		    	switch (key) {
		    		case "client":
			    		kendo.saveAs({
			                dataURI: dataImg,
			                fileName: filename
			            });
			            break;

			        case "server":
			        	$.ajax({
			            	url: ENV.vApi + "upload/capture",
			            	type: "POST",
			            	data: {dataImg: dataImg, filename: filename},
			            	success: function(response) {
			            		if(response.status) {
			            			swal({
								        title: `@Action@.`,
								        icon: "info",
								        buttons: {
								        	report: "@Report@ @error@",
								        	view: "@View@",
								        	cancel: true
								        }
								    })
								    .then((val) => {
								    	switch (val) {
								    		case "report":
								    			openForm({title: "@Report@ @error@ " + filename , width: 400});
								    			addFormReportError(response.filepath);
								    			break;

								    		case "view":
								    			window.open(response.filepath);
								    			break;

								    		default:
								    			break;
								    	}
								    });
			            		} else notification.show(response.message, "error");
			            	}
			            });
			            break;

			        default:
			        	break;
		    	}
		    });
        });
	
}

async function addFormReportError(filepath) {
    var formHtml = await $.ajax({
        url: ENV.templateApi + "report_error/form",
        error: errorDataSource
    });
    var model = kendo.observable(Object.assign({
        item: {imgPath: filepath},
        priorityOption: dataSourceJsonData(["Reporterror","priority"]),
        save: function() {
            var item = this.item.toJSON();
            $.ajax({
            	url: ENV.restApi + "reporterror",
            	type: "POST",
            	contentType: "application/json; charset=utf-8",
                data: kendo.stringify(item),
                success: syncDataSource,
                error: errorDataSource
            })
        }
    }, Config.observable));
    kendo.destroy($("#right-form"));
    $("#right-form").empty();
    var kendoView = new kendo.View(formHtml, { wrap: false, model: model, evalTemplate: false });
    kendoView.render($("#right-form"));
}
</script>
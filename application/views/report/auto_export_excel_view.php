<div id="page-content">
	<!-- Table Styles Header -->
    <ul class="breadcrumb breadcrumb-top">
        <li>@Report@</li>
        <li>@Excel export@</li>
        <li>@Config@</li>
        <li class="pull-right none-breakcrumb">
            <a role="button" class="btn btn-sm" onclick="onclick="openForm({title: '@Create@ @Ticket@', width: 700}); addForm('WEB')""><i class="fa fa-file-excel-o"></i> <b>@Create@ Sheet</b></a>
        </li>
    </ul>

	<script>
	    var initReport = function() {

		    var observable = kendo.observable({
                addForm: async function() {
                    var formHtml = await $.ajax({
                        url: Config.templateApi + Config.collection + "/form",
                        error: errorDataSource
                    });
                    var model = Object.assign(Config.observable, {
                        item: {status: "Open", source: "Hotline", receive_time: new Date()},
                        save: function() {
                            Table.dataSource.add(this.item);
                            Table.dataSource.sync().then(() => {Table.dataSource.read()});
                        }
                    });
                    kendo.destroy($("#right-form"));
                    $("#right-form").empty();
                    var kendoView = new kendo.View(formHtml, { wrap: false, model: model, evalTemplate: false });
                    kendoView.render($("#right-form"));
                }
		    })

		    kendo.bind($(".mvvm"), observable);
		    //observable.setColumns();
	    }

	    window.onload = function() {
	    	initReport()
	    };

	    function ftTotal(data) {
		    return data.count ? data.count.sum : 0;
		}

	    function getPDF(selector, filename = "Report") {
            kendo.drawing.drawDOM($(selector)).then(function(group){
              kendo.drawing.pdf.saveAs(group, `${filename}.pdf`);
            });
        }
	</script>
</div>
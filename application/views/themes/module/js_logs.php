<!-- User Settings, modal which opens from Settings link (found in top right user menu) and the Cog link (found in sidebar user info) -->
<div id="modal-js-logs" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header text-center">
            	<button type="button" class="close" data-dismiss="modal">&times;</button>
                <h2 class="modal-title"><i class="fa fa-bug"></i> JAVASCRIPT BUG <span class="text-primary"><?= date("Y-m-d", $js_log_timestamp) ?> TO NOW</span></h2>
            </div>
            <!-- END Modal Header -->

            <!-- Modal Body -->
            <div class="modal-body">
                <div id="js-logs-grid"></div>
            </div>
            <!-- END Modal Body -->
        </div>
    </div>
</div>
<script type="text/x-kendo-template" id="js-capture-template">
	<a href="#: imgPath #" data-toggle="tooltip" title="#if(typeof createdAt != 'undefined'){##: gridTimestamp(createdAt, 'dd/MM/yy H:mm:ss') ##}#" target="_blank">
        <img src="#: imgPath #" alt="@Image@" style="max-width: 150px">
    </a>
</script>
<script type="text/javascript">
	window.onload = function() {
		$("#js-logs-grid").kendoGrid({
			sortable: true,
			filterable: true,
			columns: [
				{title: "Date", template: data => gridTimestamp(data.createdAt, "dd/MM/yy H:mm"), width: 110},
				{title: "Image", field: "url", template: kendo.template($('#js-capture-template').html()), width: 170},
				{title: "Content", field: "content"},
				{title: "Ext", field: "createdBy", width: 70},
			],
			dataSource: {
				serverSorting: true,
				serverFiltering: true,
				filter: {field: "createdAt", operator: "gt", value: <?= $js_log_timestamp ?>},
				transport: {
					read: ENV.restApi + "reporterror",
					parameterMap: parameterMap
				},
				schema: {
					data: "data",
					total: "total"
				}
			}
		});
	}
    $('#modal-js-logs').modal('show');
</script>
<!-- END User Settings -->
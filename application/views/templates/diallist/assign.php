<div class="col-sm-12 assign-view">
	<h4 class="fieldset-legend" style="margin: 0 0 20px"><span style="font-weight: 500; background-color: #eaedf1; line-height: 1">@Overview@ @of@ @dial list@ <i data-bind="text: item.name"></i></span></h4>
	<div class="row">
		<div class="col-sm-4" style="margin-top: 10px">
			<div class="alert alert-info" style="cursor: pointer;">
		    	<h4>@Total@</h4>
		        <p class="text-right text-muted"><span data-bind="text: statistic.total"></span></p>
		    </div>
		</div>
		<div class="col-sm-4" style="margin-top: 10px">
		    <div class="alert alert-success" style="cursor: pointer;">
		    	<h4>@Assigned@</h4>
		        <p class="text-right text-muted"><span data-bind="text: statistic.assigned"></span></p>
		    </div>
		</div>
		<div class="col-sm-4" style="margin-top: 10px">
		    <div class="alert alert-danger" style="cursor: pointer;" data-bind="click: notAssignedAction">
		    	<h4>@Not assigned@</h4>
		        <p class="text-right text-muted"><span data-bind="text: statistic.notAssigned"></span></p>
		    </div>
		</div>
	</div>
	<div class="row" data-bind="visible: assignText">
		<div class="col-sm-4">
			<label data-bind="text: assignText"></label>
		</div>
		<div class="col-sm-8">
			<select data-role="multiselect"
			data-bind="value: item.members, source: item.members"></select>
		</div>
		<div class="col-sm-12 text-center" style="padding-top: 10px">
			<button class="k-button">@Assign@</button>
		</div>
	</div>
</div>


<script type="text/javascript">
	var diallist_id = "<?= $this->input->get('id') ?>";

	$.get(ENV.restApi + "diallist/" + diallist_id, function(res) {
		layoutViewModel.set("breadcrumb2", res.name);
		var assignObservable = kendo.observable({
	        item: res,
	        statistic: {},
	        notAssignedAction: function(e) {
	        	if(!this.get('statistic.notAssigned')) {
	        		notification.show("@None of case not assigned@.");
	        	}
	        	this.set("assignText", "@Assign@ @not assigned case@ @for@")
	        }
		});
		kendo.bind(".assign-view", assignObservable);

		$.get(ENV.vApi + "diallist/getStatistic/" + diallist_id, (res) => {
			assignObservable.set("statistic", res);
		})
	})
</script>
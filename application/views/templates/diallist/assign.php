<div class="col-sm-12 assign-view">
	<h4 class="fieldset-legend" style="margin: 0 0 20px"><span style="font-weight: 500; background-color: #eaedf1; line-height: 1">@Statistic@ @of@ @dial list@ <i data-bind="text: item.name"></i></span></h4>
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
                data-item-template="itemGroupTemplate"
                data-tag-template="tagGroupTemplate"
                data-clear-button="false"
                data-value-primitive="true"  
                data-bind="value: item.members, source: membersOption"></select>
		</div>
		<div class="col-sm-12 text-center" style="padding-top: 10px">
			<button class="k-button" data-bind="click: assign">@Assign@</button>
		</div>
	</div>
</div>

<script id="itemGroupTemplate" type="text/x-kendo-template">
    <span class="selected-value" style="background-image: url('/api/v1/avatar/agent/#: data #')"></span><span><b>#: data #</b> (#:convertExtensionToAgentname[data]#)</span>
</script>

<script id="tagGroupTemplate" type="text/x-kendo-template">
    <span class="selected-value" style="background-image: url('/api/v1/avatar/agent/#: data #')"></span><span><b>#: data #</b> (#:convertExtensionToAgentname[data]#)</span>
</script>

<script type="text/javascript">
	var diallist_id = "<?= $this->input->get('id') ?>";

	$.get(ENV.restApi + "diallist/" + diallist_id, function(res) {
		layoutViewModel.set("breadcrumb2", res.name);
		if(res.mode != "manual") {
			router.navigate("/");
		}
		var assignObservable = kendo.observable({
	        item: res,
	        statistic: {},
	        assignText: "@Assign@ @not assigned case@ @for@",
	        membersOption: res.members,
	        notAssignedAction: function(e) {
	        	if(!this.get('statistic.notAssigned')) {
	        		notification.show("@None of case not assigned@.");
	        		return;
	        	}
	        	this.set("type", "notAssigned");
	        	this.set("assignText", "@Assign@ @not assigned case@ @for@")
	        },
	        assign: function(e) {
	        	$.ajax({
	        		url: ENV.vApi + "diallist/assign", 
	        		type: "POST",
	        		data: JSON.stringify({type: this.get("type"), members: this.get("item.members"), diallist_id: this.get("item.id")}),
	        		contentType: "application/json; charset=utf-8", 
	        		success: function(res) {
	        			notification.show(res.message, res.status ? "success" : "error");
	        			router.navigate("/");
	        		}
	        	});
	        }
		});
		kendo.bind(".assign-view", assignObservable);

		$.get(ENV.vApi + "diallist/getStatistic/" + diallist_id, (res) => {
			assignObservable.set("statistic", res);
		})
	})
</script>

<style type="text/css">
	.selected-value {
        display: inline-block;
        vertical-align: middle;
        width: 18px;
        height: 18px;
        background-size: 100%;
        margin-right: 5px;
        border-radius: 50%;
    }
</style>
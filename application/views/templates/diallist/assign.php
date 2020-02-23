<div class="col-sm-12 assign-view">
	<h4 class="fieldset-legend" style="margin: 0 0 20px"><span style="font-weight: 500; background-color: #eaedf1; line-height: 1">@Select@ @assign type@</span></h4>
	<div class="row">
		<div class="col-sm-4" style="margin-top: 10px">
			<div class="alert alert-info animation-tossing" style="cursor: pointer;" data-bind="click: allAction">
		    	<h4>@Total@</h4>
		        <p class="text-right text-muted"><span data-bind="text: statistic.total"></span></p>
		    </div>
		</div>
		<div class="col-sm-4" style="margin-top: 10px">
		    <div class="alert alert-success" style="cursor: pointer;" data-bind="click: assignedAction">
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
	<h4 class="fieldset-legend" style="margin: 0 0 20px"><span style="font-weight: 500; background-color: #eaedf1; line-height: 1">@Assign@ @option@</span></h4>
	<div class="row" data-bind="visible: visibleAssigned" style="padding-bottom: 20px">
		<div class="col-sm-4">
			<label><i class="text-muted">@Case assigned@ @for@</i></label>
		</div>
		<div class="col-sm-8">
			<select data-role="multiselect" 
                data-item-template="itemGroupTemplate"
                data-tag-template="tagGroupTemplate"
                data-clear-button="false"
                data-value-primitive="true"  
                data-bind="value: item.assignedExtensions, source: userOption"></select>
		</div>
	</div>
	<div class="row" data-bind="visible: assignText">
		<div class="col-sm-4">
			<label><i class="text-muted" data-bind="text: assignText"></i></label>
		</div>
		<div class="col-sm-8">
			<select data-role="multiselect" 
                data-item-template="itemGroupTemplate"
                data-tag-template="tagGroupTemplate"
                data-clear-button="false"
                data-value-primitive="true"  
                data-bind="value: item.members, source: membersOption"></select>
		</div>
		<div class="col-sm-12 text-center" style="padding-top: 20px">
			<button class="k-button" data-bind="click: assign" style="font-size: 18px">@Assign@</button>
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
	        assignText: "@Assign@ @all case@ @for@",
	        membersOption: new kendo.data.DataSource({
	        	transport: {
	        		read: ENV.restApi + "group/" + res.group_id,
	        		parameterMap: parameterMap,
	        	},
	        	schema: {
	        		data: "members"
	        	}
	        }),
	        userOption: Object.keys(convertExtensionToAgentname),
	        tossingAnimate: function(e) {
	        	$(".alert").removeClass("animation-tossing");
	        	$(e.currentTarget).addClass("animation-tossing");
	        },
	        allAction: function(e) {
	        	this.tossingAnimate(e);
	        	this.set("visibleAssigned", false);
	        	this.set("type", "all");
	        	this.set("assignText", "@Assign@ @all case@ @for@")
	        },
	        assignedAction: function(e) {
	        	this.tossingAnimate(e);
	        	this.set("visibleAssigned", true);
	        	this.set("type", "assigned");
	        	this.set("assignText", "@Replaced by@")
	        },
	        notAssignedAction: function(e) {
	        	if(!this.get('statistic.notAssigned')) {
	        		notification.show("@None of case not assigned@.");
	        		return;
	        	}
	        	this.set("visibleAssigned", false);
	        	this.tossingAnimate(e);
	        	this.set("type", "notAssigned");
	        	this.set("assignText", "@Assign@ @not assigned case@ @for@")
	        },
	        assign: function(e) {
	        	let text = "@Assign@ @all case@ @for@ " + this.get("item.members").join(",");
	        	switch(this.get("type")) {
	        		case "all": default:
	        			text = "@Assign@ @all case@ @for@ " + this.get("item.members").join(",");
	        			break;
	        		case "assigned":
	        			if(!this.get("item.assignedExtensions")) {
	        				notification.show("@You must select extension assigned@.");
	        				return;
	        			}
	        			text = "@Assign@ @Case assigned@ "+this.get("item.assignedExtensions").join(",")+" @for@ " + this.get("item.members").join(",");
	        			break;
	        		case "notAssigned":
	        			text = "@Assign@ @not assigned case@ @for@ " + this.get("item.members").join(",");
	        			break;
	        	}
	        	swal({
			        title: `${NOTIFICATION.checkSure}?`,
			        text: text,
			        icon: "warning",
			        buttons: true,
			        dangerMode: false,
			    })
			    .then((sure) => {
			        if (sure) {
			        	notification.show("@Wait a minute@");
			        	$.ajax({
			        		url: ENV.vApi + "diallist/assign", 
			        		type: "POST",
			        		data: JSON.stringify({type: this.get("type"), members: this.get("item.members"), diallist_id: this.get("item.id"), assignedExtensions: this.get("item.assignedExtensions")}),
			        		contentType: "application/json; charset=utf-8", 
			        		success: function(res) {
			        			notification.show(res.message, res.status ? "success" : "error");
			        			router.navigate("/");
			        		}
			        	});
			        }
			    });
	        }
		});
		kendo.bind(".assign-view", assignObservable);

		$.get(ENV.vApi + "diallist/getStatisticAssign/" + diallist_id, (res) => {
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
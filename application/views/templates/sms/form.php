<div class="container-fluid">
	<div class="row">
		<div class="col-xs-12" id="main-form">
	        <div class="form-group">
				<label>@To@</label>
				<input class="k-textbox" style="width: 100%" data-bind="value: item.phone">
			</div>
			<div class="form-group">
				<label>@Template@</label>
				<input data-role="dropdownlist"
					data-text-field="name"
					data-value-field="value"
                    data-value-primitive="true"                 
                    data-bind="value: item.template, source: smsTemplateOption, events: {change: smsTemplateChange}" style="width: 100%">
			</div>
			<div class="form-group" data-bind="visible: item.template">
				<label>@Template@ @content@</label>
				<p data-bind="text: item.template"></p>
			</div>
			<div class="form-group">
				<label>@Content@ <span data-bind="visible: item.content" class="text-danger">(<i data-bind="text: item.content.length"></i> <i>@characters@</i>)</span></label>
				<textarea class="k-textbox" style="width: 100%" data-bind="value: item.content"></textarea>
			</div>
		</div>
	</div>
	<div class="row side-form-bottom">
		<div class="col-xs-12 text-right">
			<button class="btn btn-sm btn-default" onclick="closeForm()">@Cancel@</button>
			<button class="btn btn-sm btn-primary btn-save" onclick="closeForm()" data-bind="click: send">@Send to pending@</button>
		</div>
	</div>
</div>

<script type="text/javascript">
var customerDetail = <?= !empty($doc) ? json_encode($doc) : '{}' ?>;
var emailObservable = {
    item: {phone: (customerDetail.phone || "").toString()},
    smsTemplateOption: new kendo.data.DataSource({
    	transport: {
    		read: ENV.restApi + "sms_template",
    		parameterMap: parameterMap
    	},
    	schema: {
    		data: "data",
    		total: "total"
    	}
    }),
    smsTemplateChange: function(e) {
    	var content = e.sender.value();
    	for(var field in customerDetail) {
    		content = content.replace("{" + field + "}", customerDetail[field]);
    	}
    	this.set("item.content", content);
    },
    send: function(e) {
    	$.ajax({
    		url: ENV.restApi + "sms_pending",
    		type: "POST",
    		data: JSON.stringify(this.item.toJSON()),
    		contentType: "application/json; charset=utf-8",
    		success: function(response) {
    			if(response.status) {
    				syncDataSource();
    				closeForm();
    			} else notification.show("@No success@", "error");
    		},
    		error: errorDataSource
    	})
    }
}
kendo.bind("#right-form", kendo.observable(emailObservable));
</script>
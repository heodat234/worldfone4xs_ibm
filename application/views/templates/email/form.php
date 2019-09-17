<div class="container-fluid">
	<div class="row">
		<div class="col-xs-12" id="main-form">
			<div class="form-group">
				<label>@To@</label>&nbsp;&nbsp;
				<label><input type="checkbox" data-bind="checked: visibleCC, events: {change: ccChange}"><span>CC</span></label>&nbsp;&nbsp;
				<label><input type="checkbox" data-bind="checked: visibleBCC, events: {change: bccChange}"><span>BCC</span></label>
				<input data-role="autocomplete" name="email"
					data-text-field="email"
					data-value-field="email"
					data-value-primitive="true"               
                    data-bind="value: item.email, source: toOption" style="width: 100%">
			</div>
			<div class="form-group" data-bind="visible: visibleCC">
				<label>CC</label>
				<input data-role="autocomplete" name="cc"
					data-text-field="cc"
					data-value-field="cc"
					data-value-primitive="true"            
                      data-bind="value: item.cc, source: ccOption" style="width: 100%"/>
			</div>
			<div class="form-group" data-bind="visible: visibleBCC">
				<label>BCC</label>
				<input data-role="autocomplete" name="bcc"
					data-text-field="bcc"
					data-value-field="bcc"
					data-value-primitive="true"              
                      data-bind="value: item.bcc, source: bccOption" style="width: 100%"/>
			</div>
			<div class="form-group" data-bind="visible: visibleBCC">
				<label>@Attach@</label>
				<a data-role="button" data-bind="click: attachFile">@Choose file@</a>
				<div class="hidden">
					<input name="file" type="file" id="upload-attact" 
	                   data-role="upload"
	                   data-multiple="false"
	                   data-async="{ saveUrl: '/api/v1/upload/attachment', autoUpload: true }"
	                   data-bind="events: { success: uploadAttachSuccess }">
	            </div>
				<div style="width: 100%">
					<ul data-template="attachments-template" data-bind="source: item.attachments"></ul>
				</div>
			</div>
			<div class="form-group">
				<label>@Template@</label>
				<input data-role="dropdownlist"
					data-text-field="name"
					data-value-field="value"
                    data-value-primitive="true"                 
                    data-bind="value: item.template, source: emailTemplateOption, events: {change: emailTemplateChange}" style="width: 100%">
			</div>
	        <div class="form-group">
				<label>@Subject@</label>
				<input class="k-textbox" style="width: 100%" data-bind="value: item.subject">
			</div>
			<div class="form-group hidden">
	            <label>@Attach@ @image@</label>
	            <div style="width: 100%">
	                <ul data-template="cid-attachments-template" data-bind="source: item.cid_attachments" style="margin-top: 6px"></ul>
	            </div>
	            <div>
	                <input name="file" type="file" id="upload-cid-attact" 
	                   data-role="upload"
	                   data-multiple="false"
	                   data-async="{ saveUrl: '/api/v1/upload/attachment', autoUpload: true }"
	                   data-bind="events: { success: uploadSuccess }">
	            </div>
	        </div>
			<div class="form-group">
				<label>@Content@</label>
				<textarea data-role="editor"
				data-tools="[
	                'bold',
	                'italic',
	                'underline',
	                'strikethrough',
	                'insertUnorderedList',
	                'insertOrderedList',
	                'indent',
	                'outdent',
	                'foreColor',
	                'backColor',
	                'insertImage', 'insertFile',
	                'viewHtml'
	            ]" 
				data-bind="value: item.content"></textarea>
			</div>
		</div>
	</div>
	<div class="row side-form-bottom">
		<div class="col-xs-12 text-right">
			<button class="btn btn-sm btn-default" onclick="closeForm()">@Cancel@</button>
			<button class="btn btn-sm btn-primary btn-save" data-bind="click: sendToPending">@Send to pending@</button>
			<button class="btn btn-sm btn-primary btn-save" data-bind="click: send">@Send@</button>
		</div>
	</div>
</div>

<script type="text/x-kendo-template" id="cid-attachments-template">
    <li>
        <i class="fa fa-file-text"></i>&nbsp;<span data-bind="text: filename"></span> (<i><span data-bind="text: size"></span> bytes</i>) <a href="javascript:void(0)" data-bind="click: removeAttach, attr: {data-filename: filename}"><i class="fa fa-times text-danger"></i></a>
    </li>
</script>

<script type="text/x-kendo-template" id="attachments-template">
	<li><i class="fa fa-file-text"></i>&nbsp;<span data-bind="text: filename"></span> (<i><span data-bind="text: size"></span> bytes</i>)</li>
</script>

<script type="text/javascript">
var customerDetail = <?= !empty($doc) ? json_encode($doc) : '{}' ?>;
var emailObservable = {
	visibleBCC: true,
    item: {email: customerDetail.email, bcc: ""},
    ccChange: function(e) {
    	if(!e.currentTarget.checked) {
    		this.set("item.cc", "");
    	}
    },
    bccChange: function(e) {
    	if(!e.currentTarget.checked) {
    		this.set("item.bcc", "");
    	}
    },
    emailTemplateOption: new kendo.data.DataSource({
    	transport: {
    		read: ENV.restApi + "email_template",
    		parameterMap: parameterMap
    	},
    	schema: {
    		data: "data",
    		total: "total"
    	}
    }),
    emailTemplateChange: function(e) {
    	var dataItem = e.sender.dataItem(),
    		subject = dataItem.subject,
    		content = e.sender.value(),
    		item = this.get("item").toJSON();
    	for(var field in customerDetail) {
    		subject = subject.replace("{" + field + "}", customerDetail[field]);
    		content = content.replace("{" + field + "}", customerDetail[field]);
    	}
    	this.set("item.subject", subject);
    	this.set("item.content", content);
    	if(dataItem.cid_attachments) {
    		var cid_attachments = dataItem.cid_attachments.toJSON();
    		if(!this.item.cid_attachments) {
    			this.set("item.cid_attachments", []);
    		}
    		this.set("item.cid_attachments", this.get("item.cid_attachments").toJSON().concat(cid_attachments));
    	}
    },
    toOption: dataSourceDistinct("Email_logs", "email"),
    ccOption: dataSourceDistinct("Email_logs", "cc"),
    bccOption: dataSourceDistinct("Email_logs", "bcc"),
    attachFile() {
		$("#upload-attact").click();
	},
	uploadAttachSuccess: function(e) {
		e.sender.clearAllFiles();
		if(!this.item.attachments) {
			this.set("item.attachments", []);
		}
		this.item.attachments.push({filepath: e.response.filepath, filename: e.response.filename, size: e.response.size});
	},
	uploadSuccess: function(e) {
		e.sender.clearAllFiles();
		if(!this.item.cid_attachments) {
			this.set("item.cid_attachments", []);
		}
		this.item.cid_attachments.push({filepath: e.response.filepath, filename: e.response.filename, size: e.response.size});
	},
    sendToPending: function() {
    	$.ajax({
    		url: ENV.restApi + "email_pending",
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
    },
    send: function() {
    	$.ajax({
    		url: ENV.vApi + "email/send",
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
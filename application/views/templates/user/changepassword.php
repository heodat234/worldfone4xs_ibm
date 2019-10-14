<div class="container-fluid" id="change-password-form">
	<div class="row">
		<div class="col-xs-12" id="main-form">
			<div class="form-group">
				<label>@Agent name@: </label>
				<span class="label label-info" data-bind="text: item.agentname"></span>
			</div>
			<div class="form-group">
				<label>@Extension@: </label>
				<span class="label label-primary" data-bind="text: item.extension"></span>
			</div>
			<div class="form-group">
				<label>@Old password@</label>
				<input required validationMessage="Empty!!!" type="password" class="k-textbox" style="width: 100%" data-bind="value: item.old_password">
			</div>
			<div class="form-group" data-bind="visible: item.old_password">
				<label>@New password@</label>
				<input required validationMessage="Empty!!!" type="password" class="k-textbox" style="width: 100%" data-bind="value: item.new_password">
			</div>
			<div class="form-group" data-bind="visible: item.old_password">
				<label>@Re-enter password@</label>
				<input required validationMessage="Empty!!!" type="password" class="k-textbox" style="width: 100%" data-bind="value: item.re_new_password">
			</div>
		</div>
	</div>
	<div class="row side-form-bottom">
		<div class="col-xs-12 text-right">
			<button class="btn btn-sm btn-default" onclick="closeForm()">@Cancel@</button>
			<button class="btn btn-sm btn-primary btn-save" data-bind="click: save">@Change@</button>
		</div>
	</div>
</div>
<script type="text/javascript">
var asyncChangePassword = async function() {
	var item = {extension: ENV.extension, agentname: ENV.agentname};
		kendo.bind($("#change-password-form"), kendo.observable({
			item: item,
			save: function() {
				var data = this.item.toJSON();
				if(data.new_password != data.re_new_password) {
					notification.show("Re new password not match new password", "warning");
					return;
				}
				var kendoValidator = $("#change-password-form").kendoValidator().data("kendoValidator");
				if(!kendoValidator.validate()) {
					return;
				}
				$.ajax({
					url: ENV.vApi + "changepassword/save",
					type: "POST",
					data: data,
					success: function(response) {
						notification.show(response.message, response.status ? "success" : "warning");
						if(response.status) location.reload();
					},
					error: errorDataSource
				})
			}
		}));
}();
</script>
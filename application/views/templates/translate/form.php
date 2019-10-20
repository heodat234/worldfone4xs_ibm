<div class="container-fluid" id="translate-form">
	<div class="row">
		<div class="col-xs-12" id="main-form">
			<div class="form-group">
				<label>@Language@</label>
				<input data-role="dropdownlist" required style="width: 100%" data-bind="value: item.language, source: languageOption">
			</div>
			<div class="form-group">
				<label>@Type@</label>
				<select data-role="multiselect" required style="width: 100%" data-bind="value: item.type, source: typeOption"></select>
			</div>
			<div class="form-group">
				<label>@Key@</label>
				<input required validationMessage="Empty!!!" class="k-textbox" style="width: 100%" data-bind="value: item.key">
			</div>
			<div class="form-group">
				<label>@Value@</label>
				<input required validationMessage="Empty!!!" class="k-textbox" style="width: 100%" data-bind="value: item.value">
			</div>
		</div>
	</div>
	<div class="row side-form-bottom">
		<div class="col-xs-12 text-right">
			<button class="btn btn-sm btn-default" onclick="closeForm()">@Cancel@</button>
			<button class="btn btn-sm btn-primary btn-save" data-bind="click: save">@Save@</button>
		</div>
	</div>
</div>
<script type="text/javascript">
var asyncChangePassword = async function() {
	try {
	var typeOption = ["SIDEBAR", "HEADERBAR", "CONTENT", "NOTIFICATION"];
	var text = `<?= $this->input->post("text") ?>`;
	var language = ENV.language.toUpperCase();
	if(text.indexOf('@') === 0 && text.lastIndexOf('@') === text.length -1) {
		var key = text.slice(1, -1);
		var item = {
			key: key,
			type: typeOption,
			language: language,
		};
	} else {
		var res = await $.ajax({
			url: ENV.restApi + "language",
			data: {q: JSON.stringify({
				filter: {
					logic: "and",
					filters: [
						{field: "language", operator: "eq", value: language},
						{field: "value", operator: "eq", value: text}
					]
				}
			})}
		});
		var item = res.total ? res.data[0] : {
			key: "",
			type: typeOption,
			language: language,
		};
	}
		kendo.bind($("#translate-form"), kendo.observable({
			item: item,
			languageOption: ["ENG", "VIE"],
			typeOption: typeOption,
			save: function() {
				var data = this.item.toJSON();
				var kendoValidator = $("#translate-form").kendoValidator().data("kendoValidator");
				if(!kendoValidator.validate()) {
					return;
				}
				$.ajax({
					url: ENV.restApi + "language/" + (item.id || "").toString(),
					type: item.id ? "PUT" : "POST",
					contentType: "application/json; charset=utf-8",
					data: JSON.stringify(data),
					success: function(response) {
						if(response.status) {
							notification.show("@Success@", "success");
							closeForm();
						} else notification.show("@No success@", "error");		
					},
					error: errorDataSource
				})
			}
		}));
	} catch(err) {
		notification.show(err.message, "error");
	}
}();
</script>
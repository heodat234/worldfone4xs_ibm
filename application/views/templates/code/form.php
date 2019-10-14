<div class="container-fluid" id="change-password-form">
	<div class="row">
		<div class="col-xs-12" id="main-form">
			<div class="form-group">
				<label>File path: </label>
				<span class="label label-info" data-bind="text: item.filepath"></span>
			</div>
			<div class="form-group">
				<label>Content: </label>
				<textarea class="k-textbox" data-bind="value: item.content" style="width: 100%; min-height: 300px"></textarea>
			</div>
			<div class="form-group">
				<label>Note: </label>
				<textarea data-role="editor" data-bind="value: item.change" style="width: 100%"
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
				            ]"></textarea>
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
	var item = <?= !empty($doc) ? json_encode($doc) : '{}' ?>;

		item.content = await $.get(ENV.vApi + "changelog/readfile", {filepath: item.filepath});
		kendo.bind($("#change-password-form"), kendo.observable({
			item: item,
			save: function() {
				var data = this.get("item").toJSON();
				$.ajax({
					url: ENV.vApi + "changelog/savefile",
					type: "POST",
					data: data,
					success: function(response) {
						notification.show(response.message, response.status ? "success" : "danger");
						if(response.status) closeForm();
					},
					error: errorDataSource
				})
			}
		}));
}();
</script>
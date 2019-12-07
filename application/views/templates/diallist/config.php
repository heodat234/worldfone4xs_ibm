<div class="col-sm-6">
	<div class="block" style="margin-top: 20px">
		<div class="block-title">
			<h2><strong>@Campaign setting@</strong></h2>
		</div>
		<div class="block-content">
			<div class="form-horizontal">
				<div class="form-group">
					<label class="control-label col-xs-4">Condition DoNotCall</label>
					<div class="col-xs-8">
						<input data-role="numerictextbox" data-format="n0" style="width: 100%"
						data-bind="value: item.conditionDonotCall">
					</div>
				</div>
				<div class="form-group text-center">
					<button data-role="button" data-bind="click: save">@Save@</button>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="col-sm-6">
	<div class="block" style="margin-top: 20px">
	
	</div>
</div>


<script type="text/javascript">
	$.get(ENV.vApi + "diallist/getDialConfig", function(res) {
		var model = {
			item: res,

			save: function() {
				var data = this.item.toJSON();
				$.ajax({
					url: ENV.vApi + "diallist/updateDialConfig",
					type: "POST",
					contentType: "application/json; charset=utf-8",
					data: JSON.stringify(data),
					success: function(response) {
						if(response.status) {
							syncDataSource();
						}
					},
				})
			},
		
		};

		kendo.bind("#bottom-row", kendo.observable(model));
	});
</script>
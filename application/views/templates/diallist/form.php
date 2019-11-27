<div class="container-fluid">
	<div class="row">
		<div class="col-xs-12" id="main-form">
			<div class='form-group'>
				<label>@Name@ @campaign@</label>
				<input class="k-textbox" style="width: 100%" name="name" data-bind='value: item.name'>
			</div>
			<div class='form-group'>
				<label>@Mode@</label>
				<input data-role="dropdownlist" style="width: 100%"
					data-value-primitive="true"
					data-text-field="text" data-value-field="value"
					data-bind="value: item.mode, source: modeOption">
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
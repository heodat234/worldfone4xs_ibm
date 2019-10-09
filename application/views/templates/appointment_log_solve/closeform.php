<div class="container-fluid">
	<div class="row">
		<div id="main-form" class="col-xs-12">
			<div class="form-group">
				<label>@Handle@</label>
				<textarea class="k-textbox" style="width: 100%; height: 94px" data-bind="value: item.handle"></textarea>
			</div>
			<div class="form-group">
				<label>@Complete time@</label><br>
				<input data-role="datetimepicker"              
                    data-bind="value: item.complete_time" style="width: 100%"/>
			</div>
		</div>
	</div>
	<div class="row side-form-bottom">
		<div class="col-xs-12 text-right">
			<button class="btn btn-sm btn-default" onclick="closeForm()">@Cancel@</button>
			<button class="btn btn-sm btn-primary btn-save" onclick="closeForm()" data-bind="click: save">@Save@</button>
		</div>
	</div>
</div>
<div class="container-fluid">
	<div class="row">
		<div id="main-form" class="col-xs-12">
			<div class="form-group">
				<label>@Return to supervisor@</label>
                <input data-role="dropdownlist"
                       data-value-primitive="true"
                       data-text-field="agentname"
                       data-value-field="extension"
                       data-bind="source: reassignOptionAgent, value: extension" style="width: 100%"/>
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
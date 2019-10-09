<div class="container-fluid">
	<div class="row">
		<div id="main-form" class="col-xs-12">
			<div class="form-group">
				<label>@Choose group to assign@</label>
                <input id="rs-group" data-role="dropdownlist"
                       data-value-primitive="true"
                       data-text-field="name"
                       data-value-field="id"
                       data-bind="value: group_id, source: reassignOptionGroup, events: {change: reReadAgent}" style="width: 100%"/>
			</div>
			<div class="form-group">
				<label>@Choose agent to assign@</label>
                <input id="rs-agent" data-role="dropdownlist"
                       data-value-primitive="true"
                       data-text-field="extension"
                       data-value-field="extension"
                       data-auto-bind="true"
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
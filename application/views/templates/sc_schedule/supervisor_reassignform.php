<div class="container-fluid">
	<div class="row">
		<div id="main-form" class="col-xs-12">
			<div class="form-group">
				<label>@Assign to another agent@</label>
                <input data-role="dropdownlist"
                       data-value-primitive="true"
                       data-text-field="agentname"
                       data-value-field="extension"
                       data-bind="source: reassignOptionAgent, value: extension" style="width: 100%"/>
			</div>
            <span>@Or@</span><br>
            <br>
            <div class="form-group">
				<label>@Assign to another group@</label>
                <input data-role="dropdownlist"
                       data-value-primitive="true"
                       data-text-field="name"
                       data-value-field="id"
                       data-bind="source: reassignOptionGroup, value: group_id" style="width: 100%"/>
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
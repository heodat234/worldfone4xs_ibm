<div class="container-fluid">
	<div class="row">
		<div id="main-form" class="col-xs-12">
            <div class="form-group">
                <label>@Sender@</label><br>
                <input data-role="autocomplete"
                       data-value-primitive="true"
                       data-filter="contains"
                       data-text-field="name"
                       data-value-field="id"
                       data-bind="value: item.sender_name, source: senderOption, events: {change: senderChange}" style="width: 100%"/>
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
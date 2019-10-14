<div class="container-fluid">
	<div class="row">
		<div class="col-xs-12" id="main-form">
	        <div class="form-group">
				<label>Module name</label>
				<input class="k-textbox" style="width: 100%" data-bind="value: item.name">
			</div>
			<div class="form-group">
				<label>Actions</label>
				<select data-role="multiselect" name="actions" multiple="multiple"
                    data-value-primitive="true"                 
                    data-bind="value: item.actions, source: item.actions, events: {open: arrayOpen}" 
                    style="width: 100%">
                </select>
			</div>
			<div class="form-group">
				<label>Pages</label>
				<div data-bind="invisible: item.paths.length"><span class="label label-danger">EMPTY</span></div>
				<div data-template="pathTemplate" data-bind="source: item.paths"></div>
			</div>
			<div class="form-group">
	            <div class="checkbox"><label><input type="checkbox" data-bind="checked: item.active"> <span>Active</span></label></div>
	        </div>
		</div>
	</div>
	<div class="row side-form-bottom">
		<div class="col-xs-12 text-right">
			<button class="btn btn-sm btn-default" onclick="closeForm()">Cancel</button>
			<button class="btn btn-sm btn-primary btn-save" onclick="closeForm()" data-bind="click: save">Save</button>
		</div>
	</div>
</div>
<script type="text/x-kendo-template" id="pathTemplate">
	<span class="label label-success" data-bind="text: name"></span>
</script>
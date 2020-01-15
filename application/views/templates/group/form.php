<div class="container-fluid">
	<div class="row">
		<div class="col-xs-12" id="main-form">
			<div class="form-group">
	            <label>
            		<input class="custom-checkbox" type="checkbox" data-bind="checked: item.active"> 
            		<span></span>
            		<span>@Active@</span>
            	</label>
	        </div>
	        <div class="form-group">
				<label>@Group name@</label>
				<input class="k-textbox" style="width: 100%" data-bind="value: item.name, enabled: item.active">
			</div>
			<div class="form-group">
				<label>@Lead@</label>
				<input data-role="dropdownlist" name="lead"
					data-filter="contains"
					data-text-field="extension"
					data-value-field="extension"
					data-template="itemGroupTemplate"
					data-value-template="tagGroupTemplate"
					data-value-primitive="true"           
                    data-bind="value: item.lead, source: leadOption, enabled: item.active" style="width: 100%">
			</div>
			<div class="form-group">
				<label>@Members@</label>
				<select data-role="multiselect" name="members" multiple="multiple"
					data-text-field="extension"
					data-value-field="extension"
					data-item-template="itemGroupTemplate"
					data-tag-template="tagGroupTemplate"
					data-value-primitive="true"
					data-clear-button="false"            
                    data-bind="value: item.members, source: membersOption, enabled: item.active, events: {select: membersCustomSelect, deselect: membersCustomDeselect}" style="width: 100%">
                      </select>
			</div>
			<div class="form-group">
				<label>@Link@ @to@ queue</label>
				<div data-template="queue-template" data-bind="source: item.linkToQueues"></div>
			</div>
		</div>
	</div>
	<div class="row side-form-bottom">
		<div class="col-xs-12 text-right">
			<button class="btn btn-sm btn-default" data-bind="click: cancel">@Cancel@</button>
			<button class="btn btn-sm btn-primary btn-save" data-bind="click: save">@Save@</button>
		</div>
	</div>
</div>
<script id="queue-template" type="text/x-kendo-template">
	<span class="label label-info" data-bind="text: this"></span>
</script>
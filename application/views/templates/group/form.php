<div class="container-fluid">
	<div class="row">
		<div class="col-xs-12" id="main-form">
			<div class="form-group">
	            <label>
            		<input class="custom-checkbox" type="checkbox" data-bind="checked: item.active"> 
            		<span></span>
            		<span>@Active@</span>
            	</label>
            	<label>
            		<input class="custom-checkbox" type="checkbox" data-bind="checked: item.isLinkToQueue, enabled: item.active"> 
            		<span></span>
            		<span>@Link@ @to@ queue</span>
            	</label>
	        </div>
	        <div class="form-group" data-bind="visible: item.isLinkToQueue">
				<label>Queue</label>
				<input data-role="dropdownlist" name="linkQueues" multiple="multiple"
					data-text-field="name"
					data-value-field="queuename"
					data-value-primitive="true"            
                    data-bind="value: item.queuename, source: queueOption, enabled: item.active" style="width: 100%">
			</div>
	        <div class="form-group">
				<label>@Group name@</label>
				<input class="k-textbox" style="width: 100%" data-bind="value: item.name, enabled: item.active">
			</div>
			<div class="form-group">
				<label>@Members@</label>
				<select data-role="multiselect" name="members" multiple="multiple"
					data-text-field="agentname"
					data-value-field="extension"
					data-item-template="itemGroupTemplate"
					data-tag-template="tagGroupTemplate"
					data-value-primitive="true"            
                    data-bind="value: item.members, source: membersOption, enabled: item.active, events: {select: membersSelect, deselect: membersDeselect}" style="width: 100%">
                      </select>
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
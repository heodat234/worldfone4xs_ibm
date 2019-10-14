<div class="container-fluid">
	<div class="row">
		<div class="col-xs-12" id="main-form">
	        <div class="form-group">
				<label>@Group name@</label>
				<input class="k-textbox" style="width: 100%" data-bind="value: item.name">
			</div>
			<div class="form-group">
				<label>@Group type@</label>
				<input data-role="dropdownlist" style="width: 100%" name="type" 
				data-value-primitive="true"
				data-bind="value: item.type, source: typeOption, disabled: disabled, events: {change: typeChange}">
			</div>
			<div class="form-group" data-bind="visible: visibleQueues">
				<label>Queues</label>
				<select data-role="multiselect" name="queues" multiple="multiple"
					data-text-field="queuename"
					data-value-field="queuename"
                    data-value-primitive="true"                 
                      data-bind="value: item.queues, source: queuesOption, disabled: disabled, events: {change: queuesChange}" style="width: 100%">
                      </select>
			</div>
			<div class="form-group" data-bind="visible: visibleMembers">
				<label>@Members@</label>
				<select data-role="multiselect" name="members" multiple="multiple"
					data-text-field="agentname"
					data-value-field="extension"
					data-item-template="itemGroupTemplate"
					data-tag-template="tagGroupTemplate"
					data-value-primitive="true"            
                    data-bind="value: item.members, source: membersOption" style="width: 100%">
                      </select>
			</div>
			<div class="form-group">
	            <div class="checkbox"><label><input type="checkbox" data-bind="checked: item.active"> <span>@Active@</span></label></div>
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
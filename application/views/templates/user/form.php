<div class="container-fluid">
	<div class="row">
	    <div class="col-xs-3" style="margin-top: 10px;" id="side-form">
	        <div class="form-group">
				<label>@Agent name@: </label>
				<span class="label label-info" data-bind="text: item.agentname"></span>
			</div>
			<div class="form-group">
				<label>@Extension@: </label>
				<span class="label label-primary" data-bind="text: item.extension"></span>
			</div>
			<div class="form-group">
				<label>@Role@</a>
				</label>
				<select data-role="dropdownlist" name="role"
                    	data-value-primitive="true"
                    	data-text-field="name"
                    	data-value-field="id"                 
                      data-bind="value: item.role_id, source: roleOption, events: {cascade: roleCascade}" style="width: 100%">
                      </select>
			</div>
			<div class="form-group">
				<label>@Description@</label>
				<textarea class="k-textbox" style="width: 100%" data-bind="value: item.description"></textarea>
			</div>
			<div class="form-group">
				<label>@Navigator@</label>
				<div style="background-color: #394263; overflow-y: scroll; height: 250px">
					<div data-bind="html: navigatorHTML" class="check-sidebar"></div>
				</div>
            </div>
	    </div>
		<div class="col-xs-9" id="main-form">
			<div class="form-group">
				<label>@Access@</label>
				<div data-role="grid" id="dataGrid"
					data-sortable="false"
	                data-editable="false"
	                data-columns="[
	                	{field: 'name', title: 'Page name', encoded: false},
	                	{field: 'uri', title: 'URI', hidden: true},
	                	{field: 'view', title: 'View', template: '#=gridBoolean(data.view)#', width: 70},
	                	{field: 'create', title: 'Create', template: '#=gridBoolean(data.create)#', width: 70},
	                	{field: 'update', title: 'Update', template: '#=gridBoolean(data.update)#', width: 70},
	                	{field: 'delete', title: 'Delete', template: '#=gridBoolean(data.delete)#', width: 70},
	                	{field: 'actions', title: 'Actions', template: '#=gridArray(data.actions)#'},
	                ]"
	                data-bind="source: accessData"/>
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
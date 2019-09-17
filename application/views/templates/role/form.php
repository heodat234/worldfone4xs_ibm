<div class="container-fluid">
	<div class="row">
	    <div class="col-xs-3" style="margin-top: 10px" id="side-form">
	        <div class="form-group">
				<label>@Name@</label>
				<input class="k-textbox" style="width: 100%" data-bind="value: item.name">
			</div>
			<div class="form-group">
				<label>@Description@</label>
				<textarea class="k-textbox" style="width: 100%" data-bind="value: item.description"></textarea>
			</div>
			<div class="form-group">
				<button type="button" data-bind="click: addPrivilege" data-role="button"><b>@Add@ @privilege@</b></button>
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
				<label>@Privileges@</label>
				<div class="pull-right">
					<a href="javascript:void(0)" data-bind="click: setDefault"><b>@Set all privileges@</b></a>
				</div>
				<div data-role="grid" id="privilegesGrid"
	                    data-editable="true"
	                    data-columns="[
	                        {field:'module_id', title: 'Module', values: modules, editor: editorModule},
	                        {field:'view', title: '@View@', type: 'boolean', width: '70px', template: '#=gridBoolean(data.view)#'},
	                        {field:'create', title: '@Create@', type: 'boolean', width: '70px', template: '#=gridBoolean(data.create)#'},
	                        {field:'update', title: '@Update@', type: 'boolean', width: '70px', template: '#=gridBoolean(data.update)#'},
	                        {field:'delete', title: '@Delete@', type: 'boolean', width: '70px', template: '#=gridBoolean(data.delete)#'},
	                        {field:'actions', title: '@Action@', width: '180px',template:'#=gridArray(data.actions)#', editor: editorActions},
	                        {title:'@Remove@', command: [{name: 'destroy', text: ''}], width: 80}
	                        ]"
	                  data-bind="source: item.privileges, events: {cellClose: privilegesSaveChange, remove: removePrivileges}"/>
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

<script type="text/x-kendo-template" id="templateDetail">
	<div class="data"></div>
</script>
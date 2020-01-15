<div class="container-fluid">
	<div class="row">
		<div class="col-xs-12" id="main-form">
			<div class='form-group'>
				<label>@Name@ @campaign@</label>
				<input class="k-textbox" style="width: 100%" name="name" data-bind='value: item.name'>
			</div>
			<div class="form-group">
				<label>@Members@ <a href="javascript:void(0)" data-bind="click: editGroupMembers" style="margin-top: -4px; margin-left: 10px"><i class="fa fa-edit"></i> @Edit@</a></label>
				<div data-bind="html: groupMembersHTML"></div>
			</div>
			<div class='form-group' data-bind="visible: visibleEditGroupMembers">
				<label for="group-members" style="width: 340px">@Members@ @group@</label>
				<label for="all-members">@User@</label>
	            <br />
	            <select id="group-members" data-role="listbox"
	                data-connect-with="all-members"
	                data-text-field="text"
	                data-value-field="extension"
	                data-toolbar='{
	                tools: ["transferTo", "transferFrom"]
	            }'
	                data-bind="source: groupMembersOption, events: {remove: onRemoveGroupMember, add: onAddGroupMember}" style="width: 340px">
	            </select>
				<select id="all-members" data-role="listbox"
	                data-text-field="text"
	                data-value-field="extension" 
	                data-connect-with="group-members"
	                data-bind="source: userOptions" style="width: 310px">
	            </select>
			</div>
			<div class='form-group'>
				<label>@Mode@</label>
				<input data-role="dropdownlist" style="width: 100%"
					data-value-primitive="true"
					data-text-field="text" data-value-field="value"
					data-bind="value: item.mode, source: modeOption, events: {change: modeChange}">
			</div>
			<i data-bind="text: notification" class="text-danger"></i>
			<div class='form-group' data-bind="visible: visibleAuto">
				<label>@Coefficient@</label>
				<div>
					<input data-role="numerictextbox" data-min="0.5" data-max="5" style="width: 70px"
					data-bind="value: item.coefficient">
					<span class="text-danger">x</span> <i class="text-info">@Total@ @user@ @in@ @group@ @with@ @status@ @Available@ @or@ Phone @oncall@</span></i>
				</div>
			</div>
			<div class='form-group' data-bind="visible: visibleAuto">
				<label for="queue-members" style="width: 340px">@Members@ queue <span data-bind="html: queuesHTML, visible: queuesHTML"></span></label>
				<label for="group-members">@Members@ @group@ @not@ @belong to@ queue</label>
	            <br />
	            <select id="queue-members" data-role="listbox"
	                data-connect-with="remain-group-members"
	                data-text-field="text"
	                data-value-field="extension"
	                data-toolbar='{
	                tools: ["transferTo", "transferFrom"]
	            }'
	                data-bind="source: queueMembers, events: {remove: onRemoveQueueMember, add: onAddQueueMember}" style="width: 340px">
	            </select>
				<select id="remain-group-members" data-role="listbox"
	                data-text-field="text"
	                data-value-field="extension" 
	                data-connect-with="queue-members"
	                data-bind="source: remainMembers" style="width: 310px">
	            </select>
			</div>
		</div>
	</div>
	<div class="row side-form-bottom">
		<div class="col-xs-12 text-right">
			<button class="btn btn-sm btn-default" onclick="closeForm()">@Cancel@</button>
			<button class="btn btn-sm btn-primary btn-save" data-bind="click: save">@Save@</button>
		</div>
	</div>
</div>
<style type="text/css">
    #run-status-switch {width: 110px}
    #run-status-switch .onoffswitch-inner:before {content: "RUNNING";}
    #run-status-switch .onoffswitch-inner:after {content: "STOP";}
</style>

<script type="text/javascript">
var Form = {
	init: async function() {
		var id = "<?= $this->input->get("id") ?>";

		var dataItemFull = await $.ajax({
	        url: `${Config.crudApi}diallist/${id}`,
	        error: errorDataSource
	    });

	    let is_auto = Boolean(dataItemFull.mode == "auto");

    	let queues = await $.get({
            url: ENV.vApi + "group/getQueuesLinkToGroupId/" + dataItemFull.group_id,
            global: false
        });
        dataItemFull.queuesHTML = gridArray(queues);

        let queueMembers = await $.get({
            url: ENV.vApi + "group/getQueueMembersOfGroupId/" + dataItemFull.group_id,
            global: false
        });

        var queueExtensions = [];
        queueMembers.map(doc => {
			doc.text = `${doc.extension} (${doc.agentname})`;
			queueExtensions.push(doc.extension);
		});

		let group = await $.get({
            url: ENV.restApi + "group/" + dataItemFull.group_id,
            global: false
        });

        groupMembers = (group.members || []);

		let userOptions = [];
		Object.keys(convertExtensionToAgentname).forEach(ext => {
			if(groupMembers.indexOf(ext) == -1) {
				userOptions.push({extension: ext, text: `${ext} (${convertExtensionToAgentname[ext]})`})
			}
		});
		let groupMembersOption = [];
		groupMembers.forEach(ext => {
			groupMembersOption.push({extension: ext, text: `${ext} (${convertExtensionToAgentname[ext]})`});
		})

	    var model = Object.assign({}, {
	    	queues: queues,
	    	queueMembers: queueMembers,
	    	userOptions: userOptions,
	    	groupMembersOption: groupMembersOption,
	    	groupMembersHTML: gridArray(groupMembers),
	    	editGroupMembers: function(e) {
	    		this.set("visibleEditGroupMembers", !this.get("visibleEditGroupMembers"));
	    	},
	    	remainMembers: new kendo.data.DataSource({
	    		transport: {
	    			read: ENV.restApi + "group/" + dataItemFull.group_id,
	    			parameterMap: parameterMap,
	    		},
	    		schema: {
	    			data: function(res) {
	    				let data = [];
	    				res.members.forEach(ext => {
	    					if(queueExtensions.indexOf(ext) == -1) {
	    						data.push({extension: ext, text: `${ext} (${convertExtensionToAgentname[ext]})`});
	    					}
	    				});
	    				return data;
	    			}
	    		}
	    	}),
	    	onAddQueueMember: function(e) {
	    		let dataItem = e.dataItems[0];
	    		let extension = dataItem.extension;
	    		queues.forEach(queuename => {
	    			$.ajax({
                        url: ENV.vApi + "wfpbx/change_queue_member/add",
                        data: JSON.stringify({extension: extension, queuename: queuename}),
                        contentType: "application/json; charset=utf-8",
                        type: "POST",
                        success: function(res) {
                            if(res.status) {
                                notification.show(`@Add@ ${extension} (${convertExtensionToAgentname[extension]}) @at@ queue ${queuename}`, "success");
                            } else {
                                notification.show(res.message, "error");
                            }
                        }
                    });
	    		})
	    	},
	    	onRemoveQueueMember: function(e) {
	    		let dataItem = e.dataItems[0];
	    		let extension = dataItem.extension;
	    		queues.forEach(queuename => {
	    			$.ajax({
	                    url: ENV.vApi + "wfpbx/change_queue_member/remove",
	                    data: JSON.stringify({extension: extension, queuename: queuename}),
	                    contentType: "application/json; charset=utf-8",
	                    type: "POST",
	                    success: (res) => {
	                        if(res.status) {
	                            notification.show(`@Remove@ ${extension} (${convertExtensionToAgentname[extension]}) @from@ queue ${queuename}`, "success");
	                        } else {
	                            notification.show(res.message, "error");
	                        }
	                    }
	                });
	    		})
	    	},
	    	onRemoveGroupMember: function(e) {
	    		let dataItem = e.dataItems[0];
	    		let extension = dataItem.extension;
	    		let members = [];
	    		let currentMembers = e.sender.dataItems();
	    		for (var i = 0; i < currentMembers.length; i++) {
	    			if(currentMembers[i].extension != extension) {
	    				members.push(currentMembers[i].extension);
	    			}
	    		}
	    		$.ajax({
	    			url: ENV.restApi + "group/" + dataItemFull.group_id,
	    			data: JSON.stringify({members: members}),
                    contentType: "application/json; charset=utf-8",
                    type: "PUT",
                    success: (res) => {
                        if(res.status) {
                            notification.show(`@Removed@ ${extension} (${convertExtensionToAgentname[extension]})`, "success");
                        } else {
                            notification.show(res.message, "error");
                        }
                    }
	    		})
	    	},
	    	onAddGroupMember: function(e) {
	    		let dataItem = e.dataItems[0];
	    		let extension = dataItem.extension;
	    		let members = [extension];
	    		let currentMembers = e.sender.dataItems();
	    		for (var i = 0; i < currentMembers.length; i++) {
	    			members.push(currentMembers[i].extension);
	    		}
	    		$.ajax({
	    			url: ENV.restApi + "group/" + dataItemFull.group_id,
	    			data: JSON.stringify({members: members}),
                    contentType: "application/json; charset=utf-8",
                    type: "PUT",
                    success: (res) => {
                        if(res.status) {
                            notification.show(`@Added@ ${extension} (${convertExtensionToAgentname[extension]})`, "success");
                        } else {
                            notification.show(res.message, "error");
                        }
                    }
	    		})
	    	},
	    	visibleAuto: is_auto,
	    	queuesHTML: dataItemFull.queuesHTML,
			item: {
				name: dataItemFull.name, 
				mode: dataItemFull.mode, 
				coefficient: dataItemFull.coefficient ? dataItemFull.coefficient : 1.5,
				agent_status: dataItemFull.agent_status
			},
			modeOption: dataSourceJsonData(["Diallist","mode"]),
			modeChange: function(e) {
				this.set("visibleAuto", e.sender.value() == "auto");
				if(this.get("visibleAuto")) {
					this.set("item.coefficient", 1.5);
					if(!queues.length) {
						let text = "@Group@ @not@ @belong to@ @any queue@";
						this.set("notification", text);
						notification.show(text, "error");
					}
				}
			},
			save: function() {
	            $.ajax({
	                url: `${Config.crudApi+Config.collection}/${id}`,
	                type: "PUT",
	                contentType: "application/json; charset=utf-8",
	                data: kendo.stringify(this.item.toJSON()),
	                error: errorDataSource,
	                success: function() {
	                    Table.dataSource.read();
	                    closeForm();
	                }
	            })
			}
		});

		kendo.bind("#right-form", kendo.observable(model));
	}
};

Form.init();
</script>
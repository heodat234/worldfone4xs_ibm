<div class="row" style="margin: 10px 0">
    <div class="col-sm-1">
        <button class="btn btn-alt btn-sm btn-default" onclick="openForm({title: '@Create@ @Ticket@', width: 700}); addForm('WEB')">@Create@ ticket</button>
    </div>
	<div class="col-sm-2" id="page-widget"></div>
	<div class="col-sm-9 filter-mvvm" style="display: none"></div>
</div>
<div class="row">
	<div class="col-sm-3 mvvm" style="padding: 0 5px">
		<!-- Menu Block -->
        <div class="block full">
            <!-- Menu Title -->
            <div class="block-title clearfix">
                <h2><i class="fa fa-ticket"></i> @Manage@ <strong>@Ticket@</strong></h2>
            </div>
            <!-- END Menu Title -->

            <!-- Menu Content -->
            <ul class="nav nav-pills nav-stacked" data-template="status-group-template" data-bind="source: ticketGroupStatus">
            </ul>
            <!-- END Menu Content -->
        </div>
        <!-- END Menu Block -->

        <!-- Quick Month Stats Block -->
        <div class="block">
            <!-- Quick Month Stats Title -->
            <div class="block-title">
                <h2><i class="gi gi-charts"></i> @Statistic@</strong></h2>
            </div>
            <!-- END Quick Month Stats Title -->

            <!-- Quick Month Stats Content -->
            <table class="table table-striped table-borderless table-vcenter">
                <tbody>
                    <tr>
                        <td class="text-right" style="width: 50%;">
                            <strong>@No reply@</strong>
                        </td>
                        <td><span class="badge" data-bind="text: ticketStatistic.noreply"></span></td>
                        <td><a href="javascript:void(0)" data-value="0" data-bind="click: filterReply"><i class="fa fa-filter"></i></a></td>
                    </tr>
                    <tr>
                        <td class="text-right">
                            <strong>@Replied@</strong>
                        </td>
                        <td><span class="badge" data-bind="text: ticketStatistic.reply"></span></td>
                        <td><a href="javascript:void(0)" data-value="1" data-bind="click: filterReply"><i class="fa fa-filter"></i></a></td>
                    </tr>
                </tbody>
            </table>
            <!-- END Quick Month Stats Content -->
        </div>
        <!-- END Quick Month Stats Block -->
	</div>
	<div class="col-sm-9" style="height: 80vh; overflow-y: auto; padding: 0">
	    <!-- Table Styles Content -->
	    <div id="grid"></div>
	    <!-- END Table Styles Content -->
	</div>
</div>
<div id="action-menu">
    <ul>
        <a href="javascript:void(0)" data-type="detail" onclick="detailData(this)"><li><i class="fa fa-exclamation-circle text-info"></i><span>@Detail@</span></li></a>
    	<li class="devide"></li>
        <a href="javascript:void(0)" data-type="delete" onclick="deleteDataItem(this)"><li><i class="fa fa-times-circle text-danger"></i><span>@Delete@</span></li></a>
    </ul>
</div>

<script type="text/x-kendo-template" id="status-group-template">
    <li data-bind="css: {active: active}">
        <a href="javascript:void(0)" data-bind="click: filterStatus, attr: {data-value: idFields}">
            <span class="badge pull-right" data-bind="text: count">250</span>
            <i class="#: data.iconClass #"></i> <strong data-bind="text: idFields">Closed</strong>
        </a>
    </li>
</script>

<script>
    $( document ).ready(function() {
        <?php
        if(!empty($assign_mess)) { ?>
            notification.show("<?=$assign_mess?>", "success");
        <?php } ?>
    });
var Config = Object.assign(Config, {
	observable: {
        ticketGroupStatus: new kendo.data.DataSource({
            transport: {
                read: ENV.vApi + "ticket/getGroupBy",
                parameterMap: function(options) {
                	options.group = [{field: "status"}];
                	return {q: JSON.stringify(options)}
                }
            },
            schema: {
                data: "data",
                total: "total",
                parse: function(response) {
                	var totalDoc = {count: 0, idFields: "@All@", active: true, iconClass: "fa fa-ticket fa-fw"};
                	response.data.forEach(doc => {
                		totalDoc.count += doc.count;
                		switch(doc.idFields) {
                			case "Open":
                				doc.iconClass = "fa fa-folder-open-o fa-fw text-success";
                				break;
                			case "Urgent":
                				doc.iconClass = "fa fa-exclamation-triangle fa-fw text-danger";
                				break;
                			case "Closed": default:
                				doc.iconClass = "fa fa-folder-o fa-fw text-muted";
                				break;
                			case "Invalid":
                				doc.iconClass = "fa fa-ban fa-fw text-dark";
                				break;
                			case "Assist":
                				doc.iconClass = "fa fa-support fa-fw text-info";
                				break;
                			case "Pending":
                				doc.iconClass = "fa fa-spinner fa-fw text-warning";
                				break;
                		}
                	});
                	response.data.unshift(totalDoc);
                	return response;
                }
            }
        }),
        filterStatus: function(e) {
        	var value = $(e.currentTarget).data("value");
        	if(["Open", "Urgent", "Closed", "Invalid", "Assist", "Pending"].indexOf(value) > -1) {
        		var field = "status";
	        	var filter = Table.dataSource.filter();
	        	if(!filter) {
	        		filter = {field: field, operator: "eq", value: value};
	        	} else {
	        		filter.filters.forEach((fil, idx) => {
	        			if(fil.field == field) {
	        				filter.filters[idx] = {field: field, operator: "eq", value: value}
	        			} else filter.filters.push({field: field, operator: "eq", value: value});
	        		})
	        	}
	        	Table.dataSource.filter(filter);
        	} else Table.dataSource.filter({});
        	var ticketGroupStatusData = this.ticketGroupStatus.data().toJSON();
        	ticketGroupStatusData.map(doc => {
        		if(doc.idFields == value) {
        			doc.active = true;
        		} else doc.active = false;
        	})
        	this.ticketGroupStatus.data(ticketGroupStatusData);
        },
        ticketStatistic: {},
        filterSource: function(e) {
        	var field = "source";
        	var value = $(e.currentTarget).data("value");
        	var filter = Table.dataSource.filter();
        	if(!filter) {
        		filter = {field: field, operator: "eq", value: value};
        	} else {
        		filter.filters.forEach((fil, idx) => {
        			if(fil.field == field) {
        				filter.filters[idx] = {field: field, operator: "eq", value: value}
        			} else filter.filters.push({field: field, operator: "eq", value: value});
        		})
        	}
        	Table.dataSource.filter(filter);
        },
        filterReply: function(e) {
        	var field = "reply";
        	var value = Number($(e.currentTarget).data("value"));
        	var filter = Table.dataSource.filter();
        	if(!filter) {
        		filter = {field: field, operator: value ? "gt" : "eq", value: 0};
        	} else {
        		filter.filters.forEach((fil, idx) => {
        			if(fil.field == field) {
        				filter.filters[idx] = {field: field, operator: value ? "gt" : "eq", value: 0}
        			} else filter.filters.push({field: field, operator: value ? "gt" : "eq", value: 0});
        		})
        	}
        	Table.dataSource.filter(filter);
        },
        filterAll: function(e) {
        	Table.dataSource.filter({});
        },
        deletePNRFromList: function (pnr) {
            var PNRList = this.get('item.PNRList');
            var PNRListDetail = this.get('item.PNRListDetail');
            $.each(PNRList, function(key, value) {
                if(pnr.data.pnr_code === value) {
                    PNRList.splice(key, 1);
                    PNRListDetail.splice(key, 1);
                }
            });
            this.set('item.PNRList', PNRList);
            this.set('item.PNRListDetail', PNRListDetail);
        },
	},
    model: {
        id: "id",
        fields: {
        	createdAt: {type: "date"}
        }
    },
    parse(response) {
        response.data.map(function(doc) {
            doc.createdAt = doc.createdAt ? new Date(doc.createdAt * 1000) : undefined;
            if(typeof doc.assignGroup !== 'undefined' && typeof groupInfo[doc.assignGroup] !== 'undefined') {
                var assignInfo = groupInfo[doc.assignGroup];
                if(typeof doc.assign !== 'undefined' && doc.assignGroup !== doc.assign) {
                    assignInfo = assignInfo + ' - ' + doc.assign;
                }
                doc.assign = assignInfo;
            }
            else if(typeof doc.assign === 'undefined' && typeof doc.assignGroup === 'undefined') {
                doc.assign = "@Not Assigned@"
            }
            return doc;
        });
        return response;
    },
    columns: [{
    		selectable: true,
            width: 32,
            locked: true
        },{
            field: "ticket_id",
            title: "ID",
            width: 80,
            template: data => gridName(data.ticket_id)
        },{
            field: "status",
            title: "@Status@",
            filterable: false,
            width: 80,
            template: data => gridTicketStatus(data.status)
        },{
            field: "source",
            title: "@Source@",
            filterable: {
                ui: (element) => {
                    element.kendoDropDownList({
                        dataTextField: "text",
                        dataValueField: "value",
                        dataSource: dataSourceJsonData(["Ticket","source"]),
                        optionLabel: "---- @Select@ ----"
                    });
                },
                operators: {
                  string: {
                    eq: '@Equal to@',
                  }
                }
            },
            width: 80,
        },{
            field: "title",
            title: "@Title@",
        },{
            field: "sender_name",
            title: "@Sender@",
        },{
            field: "assignView",
            title: "@Assign@",
            width: 80,
            template: data => reAssignButton(data)
            // template: `<a href="javascript:void(0)" class="btn btn-xs btn-primary" onclick="reAssign()">#=assign#</a>`
        },{
            field: "createdAt",
            title: "@Created at@",
            width: 80,
            template: data => gridDate(data.createdAt)
        },{
            field: "reply",
            title: "<i class='fa fa-comments'></i>",
            filterable: false,
            width: 30
        },{
            // Use uid to fix bug data-uid of row undefined
            title: `<a class='btn btn-sm btn-circle btn-action btn-primary' onclick='return deleteDataItemChecked();'><i class='fa fa-times-circle'></i></a>`,
            template: '<a role="button" class="btn btn-sm btn-circle btn-action btn-primary" data-uid="#: uid #"><i class="fa fa-ellipsis-v"></i></a>',
            width: 20
        }]
}); 
</script>
<script src="<?= STEL_PATH.'js/tablev1.js' ?>"></script>

<script type="text/javascript">
    var groupInfo;
	async function editForm(ele) {
		var dataItem = Table.dataSource.getByUid($(ele).data("uid")),
	        dataItemFull = await $.ajax({
	            url: `${Config.crudApi+Config.collection}/${dataItem.id}`,
	            error: errorDataSource
	        }),
		    formHtml = await $.ajax({
	    	    url: Config.templateApi + Config.collection + "/form",
	    	    error: errorDataSource
	    	});
		var model = Object.assign({
			item: dataItemFull,
			save: function() {
	            $.ajax({
	                url: `${Config.crudApi+Config.collection}/${dataItem.id}`,
	                data: kendo.stringify(this.item.toJSON()),
	                error: errorDataSource,
	                contentType: "application/json; charset=utf-8",
	                type: "PUT",
	                success: function() {
	                    Table.dataSource.read()
	                }
	            })
			}
		}, Config.observable);
		kendo.destroy($("#right-form"));
		$("#right-form").empty();
		var kendoView = new kendo.View(formHtml, { wrap: false, model: model, evalTemplate: false });
		kendoView.render($("#right-form"));
	}

	function deleteDataItem(ele) {
		swal({
		    title: "@Are you sure@?",
		    text: "@Once deleted, you will not be able to recover this document@!",
		    icon: "warning",
		    buttons: true,
		    dangerMode: true,
	    })
	    .then((willDelete) => {
			if (willDelete) {
				var uid = $(ele).data('uid');
				var dataItem = Table.dataSource.getByUid(uid);
			    Table.dataSource.remove(dataItem);
			    Table.dataSource.sync();
			}
	    });
	}

	function deleteDataItemChecked() {
		var checkIds = Table.grid.selectedKeyNames();
		if(checkIds.length) {
			swal({
			    title: "@Are you sure@?",
			    text: "@Once deleted, you will not be able to recover these documents@!",
			    icon: "warning",
			    buttons: true,
			    dangerMode: true,
		    })
		    .then((willDelete) => {
				if (willDelete) {
					checkIds.forEach(uid => {
						var dataItem = Table.dataSource.getByUid(uid);
					    Table.dataSource.remove(dataItem);
					    Table.dataSource.sync();
					})
				}
		    });
		} else {
			swal({
				title: "@No row is checked@!",
			    text: "@Please check least one row to remove@",
			    icon: "error"
			});
		}
	}

	function importData(ele) {
		var uid = $(ele).data('uid');
		var dataItem = Table.dataSource.getByUid(uid);
		router.navigate(`/import/${dataItem.id}`);
	}

	function detailData(ele) {
		var uid = $(ele).data('uid');
		var dataItem = Table.dataSource.getByUid(uid);
		router.navigate(`/detail/${dataItem.id}`);
	}

	$(document).on("click", ".grid-name", function() {
		detailData($(this).closest("tr"));
	})

	function gridTicketStatus(status) {
		var iconClass = "";
		switch(status) {
			case "Open": 
				iconClass = "label-success";
				break;
			case "Urgent":
				iconClass = "label-danger";
				break;
			case "Closed": default:
				iconClass = "label-default";
				break;
			case "Invalid":
				iconClass = "label-dark";
				break;
			case "Assist":
				iconClass = "label-info";
				break;
			case "Pending":
				iconClass = "label-warning";
				break;
		}
		return `<span class="label ${iconClass}">${status}</span>`;
	}

    function reAssignButton(dataItem) {
        if(typeof dataItem.assignView !== 'undefined' && dataItem.assignView !== '' && dataItem.assignView !== null) {
            return `<a href="javascript:void(0)" class="btn btn-xs btn-primary" onclick="reAssign('${dataItem.id}')">${dataItem.assignView}</a>`;
        }
        else {
            return `<a href="javascript:void(0)" class="btn btn-xs btn-primary" onclick="reAssign('${dataItem.id}')">@Not Assign@</a>`;
        }
    }

	new Promise(resolve => {
         groupInfo = function getGroupInfo() {
            var tmp = null;
            $.ajax({
                async: false,
                global: false,
                url: ENV.vApi + "group/getListGroupIdName",
                success: function (response) {
                    tmp = response;
                },
                error: function (data) {
                    console.log(data);
                }
            });
            return tmp;
        }();
	    resolve();
    }).then(() => {
        Table.init();
        Config.observable.ticketGroupStatus.read().then(() => {
            var model = Table.model = kendo.observable(Config.observable);
            kendo.bind($(".mvvm"), model);
            $.ajax({
                url: ENV.vApi + Config.collection + "/statistic",
                success: response => {
                    Table.model.set("ticketStatistic", response);
                }
            });
        });
        var tableOption = JSON.parse(sessionStorage.getItem(Config.collection));
        if(typeof tableOption !== 'undefined' && tableOption !== null) {
            Table.dataSource.query(tableOption);
            sessionStorage.removeItem(Config.collection);
        }
    });

    async function reAssign(ticket_id) {
        openForm({title: "@Reassign@", width: 400});
        this.reAssignAsync(ticket_id);
    }

    async function reAssignAsync(ticket_id_in) {
        var formHtmlUrl = '';
        var user_role = Config.userRole;
        switch (user_role) {
            case 'admin':
                formHtmlUrl = Config.templateApi + "ticket/admin_reassignform";
                break;
            case 'supervisor':
                formHtmlUrl = Config.templateApi + "ticket/supervisor_reassignform";
                break;
            default:
                formHtmlUrl = Config.templateApi + "ticket/agent_reassignform";
        }
        var formHtml = await $.ajax({
            url: formHtmlUrl,
            error: errorDataSource
        });
        var group_id_global = null;
        var ticket_id = ticket_id_in;
        var model = {
            group_id: null,
            extension: null,
            reassignOptionAgent: new kendo.data.DataSource({
                transport: {
                    read: {
                        url: `${ENV.vApi}ticket/getGroupInfoForAssign`,
                        data: function() {
                            return {
                                'isGroup': false,
                                'group_id': group_id_global
                            }
                        }
                    },
                    parameterMap: parameterMap
                }
            }),
            reassignOptionGroup: new kendo.data.DataSource({
                transport: {
                    read: {
                        url: `${ENV.vApi}ticket/getGroupInfoForAssign`,
                        data: {
                            'isGroup': true,
                        }
                    },
                    parameterMap: parameterMap
                },
            }),
            reReadAgent(e) {
                if(user_role !== 'supervisor') {
                    group_id_global = this.get('group_id');
                    this.reassignOptionAgent.read();
                }
            },
            save: function() {
                var group_id = this.get('group_id');
                var extension = this.get('extension');
                var updateInfo = {};
                if(typeof group_id !== 'undefined' && group_id !== null && group_id !== '') {
                    updateInfo.assign = '';
                    updateInfo.assignGroup = group_id;
                }
                if(typeof extension !== 'undefined' && extension !== null && extension !== '') {
                    updateInfo.assign = extension;
                }
                if((typeof group_id === 'undefined' || group_id === null || group_id === '') && (typeof extension === 'undefined' || extension === null || extension === '')) {
                    // notificationAfterRefresh("@Assign@ @error@", "error");
                    notification.show('@Xin vui lòng chọn extension hoặc nhóm cần assign@', 'error');
                    return false;
                }
                if(user_role === 'supervisor' && typeof group_id !== 'undefined' && group_id !== null && group_id !== '' && typeof extension !== 'undefined' && extension !== null && extension !== '') {
                    notification.show('@Trưởng nhóm chỉ có thể chọn nhóm HOẶC agent để assign@', 'error');
                    return false;
                }
                if((typeof extension !== 'undefined' && extension !== null && extension !== '') && (typeof group_id === 'undefined' || group_id === null || group_id === '')) {
                    updateInfo.assign = extension;
                    updateInfo.assignGroup = null;
                }

                $.ajax({
                    url: ENV.vApi + "ticket/update/" + ticket_id,
                    type: "PUT",
                    data: JSON.stringify(updateInfo),
                    success: (response) => {
                        if(response.status) {
                            notificationAfterRefresh("@Assign@ @success@", "success");
                            location.reload();
                        }
                    },
                    error: errorDataSource
                })
            }
        };
        kendo.destroy($("#right-form"));
        $("#right-form").empty();
        var kendoView = new kendo.View(formHtml, { wrap: false, model: model, evalTemplate: false });
        kendoView.render($("#right-form"));
    }

</script>
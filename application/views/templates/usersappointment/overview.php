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
                <h2><i class="fa fa-ticket"></i> <strong>@Ticket@</strong></h2>
            </div>
            <!-- END Menu Title -->

            <!-- Menu Content -->
            <ul class="nav nav-pills nav-stacked" data-template="status-group-template" data-bind="source: ticketGroupStatus">
            </ul>
            <!-- END Menu Content -->
        </div>
        <!-- END Menu Block -->
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

function dataSourceService(level=1, parent_id=null) {
    return new kendo.data.DataSource({
        transport: {
            read: {
                url: `${ENV.restApi}servicelevel`,
                data: {id: parent_id, "lv": level}
            },
            parameterMap: parameterMap
        }
    })
}

// async function addForm(fromPage) {
//     var formHtml = await $.ajax({
//         url: Config.templateApi + Config.collection + "/form",
//         error: errorDataSource
//     });
//     var model = Object.assign(Config.observable, {
//         isAgentAssignHide: false,
//         isGroupAssignHide: true,
//         ci_name: '',
//         ci_phone: '',
//         ci_email: '',
//         ci_no: '',
//         isEnabled: true,
//         isVisible: true,
//         item: {
//             status: "Open",
//             source: "Hotline",
//             receive_time: new Date(),
//             fromPage: fromPage,
//             assign: ENV.extension
//         },
//         sourceOption: dataSourceJsonData(["Ticket", "source"]),
//         senderOption: () => dataSourceDropDownList("Customer", ["name"]),
//         customerFormatOption: () => dataSourceJsonData(["Ticket", "customer format"]),
//         priorityOption: () => dataSourceJsonData(["Ticket", "priority"]),
//         relationOption: () => dataSourceJsonData(["Ticket", "relation"]),
//         serviceLv1Option: dataSourceService(1),
//         serviceLv2Option: [],
//         serviceLv3Option: [],
//         assignOptionAgent: new kendo.data.DataSource({
//             transport: {
//                 read: ENV.restApi + "user",
//                 parameterMap: parameterMap
//             },
//             schema: {
//                 data: function (response) {
//                     return response.data;
//                 },
//                 total: "total"
//             }
//         }),
//         assignOptionGroup: new kendo.data.DataSource({
//             transport: {
//                 read: ENV.restApi + "group",
//                 parameterMap: parameterMap
//             },
//             schema: {
//                 data: function (response) {
//                     return response.data;
//                 },
//                 total: "total"
//             }
//         }),
//         serviceOption: new kendo.data.DataSource({
//             transport: {
//                 read: ENV.vApi + "servicelevel/select",
//                 parameterMap: parameterMap
//             },
//             schema: {
//                 data: "data",
//                 total: "total"
//             },
//             error: errorDataSource
//         }),
//         save: function() {
//             if(typeof Table !== 'undefined') {
//                 Table.dataSource.add(this.item);
//                 Table.dataSource.sync().then(() => {Table.dataSource.read()});
//             }
//             else {
//                 $.ajax({
//                     url: ENV.vApi + Config.collection + "/create",
//                     type: "POST",
//                     data: JSON.stringify(this.item),
//                     contentType: "application/json; charset=utf-8",
//                     dataType: 'JSON',
//                     success: (response) => {
//                         if(response.status == 1) {
//                             notification.show("@Thêm mới ticket thành công@", 'success');
//                         }
//                         else{
//                             notification.show("@Thêm mới ticket thất bại, in vui lòng kiểm tra lại@", 'error')
//                         }
//                     },
//                     error: errorDataSource
//                 });
//             }
//         },
//         changeStatus: function(e) {
//             console.log("TEST function");
//             if(this.item.status == "Open") {
//                 this.set("item.status", "Urgent");
//                 $(e.currentTarget).removeClass("label-success").addClass("label-danger");
//             } else {
//                 this.set("item.status", "Open");
//                 $(e.currentTarget).removeClass("label-danger").addClass("label-success");
//             }
//         },
//         senderChange: function(e) {
//             var item = e.sender.dataItem();
//             if(typeof item !== 'undefined') {
//                 this.set("item.sender_id", item.id);
//             }
//         },
//         isAgentAssignClick: function(e) {
//             if($("#isAgentAssign").prop('checked') === true){
//                 this.set('isAgentAssignHide', true);
//                 this.set('isGroupAssignHide', false);
//             }
//             else {
//                 this.set('isAgentAssignHide', false);
//                 this.set('isGroupAssignHide', true);
//             }
//         },
//         enableAdvancedSearch: function(e) {
//             // this.set("visibleAdvancedSearch", true);
//             console.log('AdvancedSearch');
//             $("#AdvancedSearch1").animate({
//                 height: 'toggle'
//             });
//         },
//         serviceSelect: function(e) {
//             this.set("visibleAdvancedSearch", false);
//         },
//         onSearch1: function(e) {
//             var field = "value1";
//             var filterValue = {field: field, operator: "eq", value: e.dataItem.name};
//             var filter = {
//                 logic: "and",
//                 filters: [filterValue]
//             };
//             this.serviceOption.filter(filter);
//
//             var parent_id = e.dataItem.id;
//             this.set("serviceLv2Option", dataSourceService(2, parent_id));
//             this.set("serviceLv3Option", []);
//             $("input[name=serviceLv2]").data("kendoDropDownList").refresh();
//             $("input[name=serviceLv3]").data("kendoDropDownList").refresh();
//         },
//         onSearch2: function(e) {
//             var filter = this.serviceOption.filter();
//             var field = "value2";
//             var filterValue = {field: field, operator: "eq", value: e.dataItem.name};
//             if(filter) {
//                 filter.filters.filter(doc => doc.field != field);
//                 filter.filters.push(filterValue);
//             } else {
//                 filter = {
//                     logic: "and",
//                     filters: []
//                 };
//                 filter.filters.push(filterValue);
//             }
//
//             this.serviceOption.filter(filter);
//
//             var parent_id = e.dataItem.id;
//             this.set("serviceLv3Option", dataSourceService(3, parent_id));
//             $("input[name=serviceLv3]").data("kendoDropDownList").refresh();
//         },
//         onSearch3: function(e) {
//             var filter = this.serviceOption.filter();
//             var field = "value3";
//             var filterValue = {field: field, operator: "eq", value: e.dataItem.name};
//             if(filter) {
//                 filter.filters.filter(doc => doc.field != field);
//                 filter.filters.push(filterValue);
//             } else {
//                 filter = {
//                     logic: "and",
//                     filters: []
//                 };
//                 filter.filters.push(filterValue);
//             }
//             this.serviceOption.filter(filter);
//             var dropdownlist = $("input[name=service]").data("kendoDropDownList");
//
//             dropdownlist.select(dropdownlist.ul.children().eq(0));
//             this.set("visibleAdvancedSearch", false);
//             this.serviceOption.filter({});
//             this.set("item.service", dropdownlist.value());
//         },
//         savePNROnEnter: function(e) {
//             var code = (e.which);
//             if(code == 13) { //Enter keycode
//                 console.log(this.get('item.PNR'));
//                 $.ajax({
//                     url: ENV.vApi + "ticket/getPNRFromAPI/" + this.get('item.PNR'),
//                     type: "GET",
//                     success: (response) => {
//                         if(response.status == 1) {
//                             var PNRList = this.get('item.PNRList');
//                             var PNRListDetail = this.get('item.PNRListDetail');
//                             if(typeof PNRList !== 'undefined' && PNRList !== null && PNRList !== []) {
//                                 PNRList.push(this.get('item.PNR'));
//                             }
//                             else {
//                                 PNRList = [];
//                                 PNRList.push(this.get('item.PNR'));
//                             }
//                             if(typeof PNRListDetail !== 'undefined' && PNRListDetail !== null && PNRListDetail !== []) {
//                                 PNRListDetail.push({pnr_code: this.get('item.PNR'), pnr_state: 'success', pnr_info: '1. BL-740 Thu, 04Apr19 SGN - HAN 05:40 - 07:45HK 0St Starter (STARTER Class M) VND 1,240,000 VND 1,737,000 VND.\n2. BL-740 Thu, 04Apr19 SGN - HAN 05:40 - 07:45HK 0St Starter (STARTER Class M) VND 1,240,000 VND 1,737,000 VND'});
//                                 // PNRListDetail.push({pnr_code: this.get('item.PNR'), pnr_routine_list: ['BL-740 Thu, 04Apr19 SGN-HAN 05:40 - 07:45', 'BL-740 Thu, 04Apr19 SGN-HAN 05:40 - 07:45', 'BL-740 Thu, 04Apr19 SGN-HAN 05:40 - 07:45']);
//                             }
//                             else {
//                                 PNRListDetail = [];
//                                 PNRListDetail.push({pnr_code: this.get('item.PNR'), pnr_state: 'success', pnr_info: '1. BL-740 Thu, 04Apr19 SGN - HAN 05:40 - 07:45HK 0St Starter (STARTER Class M) VND 1,240,000 VND 1,737,000 VND.\n2. BL-740 Thu, 04Apr19 SGN - HAN 05:40 - 07:45HK 0St Starter (STARTER Class M) VND 1,240,000 VND 1,737,000 VND'});
//                             }
//                             this.set('item.PNRList', PNRList);
//                             this.set('item.PNRListDetail', PNRListDetail);
//                         }
//                         else {
//                             var PNRList = this.get('item.PNRList');
//                             var PNRListDetail = this.get('item.PNRListDetail');
//                             if(typeof PNRList !== 'undefined' && PNRList !== null && PNRList !== []) {
//                                 PNRList.push(this.get('item.PNR'));
//                             }
//                             else {
//                                 PNRList = [];
//                                 PNRList.push(this.get('item.PNR'));
//                             }
//                             if(typeof PNRListDetail !== 'undefined' && PNRListDetail !== null && PNRListDetail !== []) {
//                                 PNRListDetail.push({pnr_code: this.get('item.PNR'), pnr_state: 'error', pnr_info: '1. BL-740 Thu, 04Apr19 SGN - HAN 05:40 - 07:45HK 0St Starter (STARTER Class M) VND 1,240,000 VND 1,737,000 VND.\n2. BL-740 Thu, 04Apr19 SGN - HAN 05:40 - 07:45HK 0St Starter (STARTER Class M) VND 1,240,000 VND 1,737,000 VND'});
//                             }
//                             else {
//                                 PNRListDetail = [];
//                                 PNRListDetail.push({pnr_code: this.get('item.PNR'), pnr_state: 'error', pnr_info: '1. BL-740 Thu, 04Apr19 SGN - HAN 05:40 - 07:45HK 0St Starter (STARTER Class M) VND 1,240,000 VND 1,737,000 VND.\n2. BL-740 Thu, 04Apr19 SGN - HAN 05:40 - 07:45HK 0St Starter (STARTER Class M) VND 1,240,000 VND 1,737,000 VND'});
//                             }
//                             this.set('item.PNRList', PNRList);
//                             this.set('item.PNRListDetail', PNRListDetail);
//                         }
//                         console.log(this.get('item.PNRListDetail'));
//                     },
//                     error: errorDataSource
//                 });
//             }
//         },
//         openAddContactPersonForm: function(e) {
//             $("#addNewInfoForm").animate({
//                 height: 'toggle'
//             });
//         },
//         updateContactPersonInfo: function(e) {
//             var contactPersonInfoList = this.get('item.contactPersonInfo');
//             if(typeof contactPersonInfoList !== 'undefined' && contactPersonInfoList !== null && contactPersonInfoList !== []) {
//                 contactPersonInfoList.push({no: contactPersonInfoList.length.toString(), name: this.get('ci_name'), phone: this.get('ci_phone'), email: this.get('ci_email')});
//             }
//             else {
//                 contactPersonInfoList = [];
//                 contactPersonInfoList.push({no: 0, name: this.get('ci_name'), phone: this.get('ci_phone'), email: this.get('ci_email')});
//             }
//             this.set('item.contactPersonInfo', contactPersonInfoList);
//         },
//         deleteContactFromList: function(contact) {
//             var contactPersonInfoList = this.get('item.contactPersonInfo');
//             $.each(contactPersonInfoList, function(key, value) {
//                 if(contact.data.no === value.no) {
//                     contactPersonInfoList.splice(key, 1);
//                 }
//             });
//             this.set('item.contactPersonInfoList', contactPersonInfoList);
//         },
//         openPNRDetail: function(pnr_code) {
//             console.log(pnr_code);
//             window.open(`manage/ticket/pnrDetail?id=${pnr_code.data.pnr_code}`,'_blank',null);
//         },
//         onError: function(e) {
//             var files = e.files;
//             console.log(e);
//             if (e.operation == "upload") {
//                 alert("Failed to upload " + files.length + " files");
//             }
//         },
//         onSuccessUploadFile(e) {
//             var listUploadFile = this.get('item.listUploadFile');
//             if (e.operation == "upload") {
//                 if(typeof listUploadFile !== 'undefined' && listUploadFile !== null) {
//                     listUploadFile.push(e.response);
//                 }
//                 else {
//                     listUploadFile = [];
//                     listUploadFile.push(e.response);
//                 }
//                 this.set('item.listUploadFile', listUploadFile);
//                 console.log(this.get('item.listUploadFile'));
//             }
//         }
//     });
//     kendo.destroy($("#right-form"));
//     $("#right-form").empty();
//     var kendoView = new kendo.View(formHtml, { wrap: false, model: model, evalTemplate: false });
//     kendoView.render($("#right-form"));
// }

var Config = Object.assign(Config, {
	observable: {
		assignOption: new kendo.data.DataSource({
            transport: {
                read: ENV.restApi + "user",
                parameterMap: parameterMap
            },
            schema: {
                data: "data",
                total: "total"
            }
        }),
        sourceOption: dataSourceJsonData(["Ticket", "source"]),
        changeStatus: function(e) {
        	if(this.item.status == "Open") {
        		this.set("item.status", "Urgent");
        		$(e.currentTarget).removeClass("label-success").addClass("label-danger");
        	} else {
        		this.set("item.status", "Open");
        		$(e.currentTarget).removeClass("label-danger").addClass("label-success");
        	}
        },
        senderOption: () => dataSourceDropDownList("Customer", ["name"]),
        senderChange: function(e) {
        	var item = e.sender.dataItem();
        	this.set("item.sender_id", item.id);
        },
        ticketGroupStatus: new kendo.data.DataSource({
        	serverFiltering: true,
        	filter: {
        		logic: "and",
        		filters: [
        			{field: "assign", operator: "eq", value: ENV.extension},
	        		{
		        		logic: "or",
		        		filters: [
		        			{field: "status", operator: "eq", value: "Open"},
		        			{field: "status", operator: "eq", value: "Urgent"},
		        			{field: "status", operator: "eq", value: "Assist"},
		        			{field: "status", operator: "eq", value: "Pending"},
		        		]
	        		}
        		]
        	},
            transport: {
                read: ENV.vApi + "ticket_solve/getGroupBy",
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
                				doc.iconClass = "fa fa-ban fa-fw text-warning";
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
        relationOption: () => dataSourceJsonData(["Ticket", "relation"]),
        priorityOption: () => dataSourceJsonData(["Ticket", "priority"]),
        customerFormatOption: () => dataSourceJsonData(["Ticket", "customer format"]),
        serviceOption: new kendo.data.DataSource({
            transport: {
                read: ENV.vApi + "servicelevel/select",
                parameterMap: parameterMap
            },
            schema: {
                data: "data",
                total: "total"
            },
            error: errorDataSource
        }),
        serviceLv1Option: dataSourceService(1),
        serviceLv2Option: [],
        serviceLv3Option: [],
        onSearch1: function(e) {
            var field = "value1";
            var filterValue = {field: field, operator: "eq", value: e.dataItem.name};
            var filter = {
                logic: "and",
                filters: [filterValue]
            };
            this.serviceOption.filter(filter);

            var parent_id = e.dataItem.id;
            this.set("serviceLv2Option", dataSourceService(2, parent_id));
            this.set("serviceLv3Option", []);
            $("input[name=serviceLv2]").data("kendoDropDownList").refresh();
            $("input[name=serviceLv3]").data("kendoDropDownList").refresh();
        },
        onSearch2: function(e) {
            var filter = this.serviceOption.filter();
            var field = "value2";
            var filterValue = {field: field, operator: "eq", value: e.dataItem.name};
            if(filter) {
                filter.filters.filter(doc => doc.field != field);
                filter.filters.push(filterValue);
            } else {
                filter = {
                    logic: "and",
                    filters: []
                };
                filter.filters.push(filterValue);
            }

            this.serviceOption.filter(filter);

            var parent_id = e.dataItem.id;
            this.set("serviceLv3Option", dataSourceService(3, parent_id));
            $("input[name=serviceLv3]").data("kendoDropDownList").refresh();
        },
        onSearch3: function(e) {
            var filter = this.serviceOption.filter();
            var field = "value3";
            var filterValue = {field: field, operator: "eq", value: e.dataItem.name};
            if(filter) {
                filter.filters.filter(doc => doc.field != field);
                filter.filters.push(filterValue);
            } else {
                filter = {
                    logic: "and",
                    filters: []
                };
                filter.filters.push(filterValue);
            }
            this.serviceOption.filter(filter);
            var dropdownlist = $("input[name=service]").data("kendoDropDownList");

            dropdownlist.select(dropdownlist.ul.children().eq(0));
            this.set("visibleAdvancedSearch", false);
            this.serviceOption.filter({});
            this.set("item.service", dropdownlist.value());
        },
        enableAdvancedSearch: function(e) {
            this.set("visibleAdvancedSearch", true);
        },
        serviceSelect: function(e) {
            this.set("visibleAdvancedSearch", false);
        }
	},
    model: {
        id: "id",
        fields: {
        	createdAt: {type: "date"},
        	reply: {type: "number"}
        }
    },
    parse: function (response) {
        response.data.map(function(doc) {
            doc.createdAt = doc.createdAt ? new Date(doc.createdAt * 1000) : undefined;
            return doc;
        })
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
            width: 40
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
        return `<a href="javascript:void(0)" class="btn btn-xs btn-primary" onclick="reAssign('${dataItem.id}')">${dataItem.assignView}</a>`;
    }

    new Promise(resolve => {
        groupInfo = function getGroupInfo() {
            var tmp = null;
            $.ajax({
                async: false,
                global: false,
                url: ENV.vApi + "group/getListGoupIdName",
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
                formHtmlUrl = Config.templateApi + "ticket_solve/admin_reassignform";
                break;
            case 'supervisor':
                formHtmlUrl = Config.templateApi + "ticket_solve/supervisor_reassignform";
                break;
            default:
                formHtmlUrl = Config.templateApi + "ticket_solve/agent_reassignform";
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
                        url: `${ENV.vApi}ticket_solve/getGroupInfoForAssign`,
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
                        url: `${ENV.vApi}ticket_solve/getGroupInfoForAssign`,
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
                    url: ENV.vApi + "ticket_solve/update/" + ticket_id,
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

<script type="text/x-kendo-template" id="pnr-template">
    #if(pnr_state === 'success') {#
    <li class="pnr-info-detail" style="margin-left: 7px; display:inline;">
        <a data-html="true" href="javascript:void(0)" class="label label-success" data-bind="text: pnr_code, click: openPNRDetail" data-toggle="tooltip" data-placement="top" title="#: pnr_info #"></a><i class="fa fa-times" style="color: \#dd2200" aria-hidden="true" style="cursor: pointer" data-bind="click: deletePNRFromList"></i>&nbsp;
    </li>
    #} else {#
    <li style="margin-left: 7px; display:inline;">
        <a href="javascript:void(0)" class="label label-danger" data-bind="text: pnr_code"></a><i class="fa fa-times" style="color: \#dd2200" aria-hidden="true" style="cursor: pointer" data-bind="click: deletePNRFromList"></i>&nbsp;
    </li>
    #}#
</script>

<script type="text/x-kendo-template" id="pnr-tooltip-template">
    <span data-role="tooltip" data-auto-hide="false" data-position="top" data-bind="attr: {title: this}"></span>
</script>

<script type="text/x-kendo-template" id="contact-person-info-template">
    <li><i class="fa fa-times" style="color: \#dd2200" aria-hidden="true" style="cursor: pointer" data-bind="click: deleteContactFromList"></i><i class="fa fa-user" aria-hidden="true"></i> <span class="label label-info" data-bind="text: name"></span><span class="pull-right" data-bind="text: phone"></span></br><span data-bind="text: email"></span></li>
</script>
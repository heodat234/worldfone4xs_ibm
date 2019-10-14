<div id="form-loader" style="display: none;"></div>
<div class="container-fluid">
	<div class="row">
		<div class="col-xs-12" id="main-form">
			<div class="col-sm-6">
				<div class="form-group">
					<label>@Title@</label>
					<input class="k-textbox" style="width: 100%" data-bind="value: item.title">
				</div>
				<div class="form-group">
					<label>@Status@: </label>
					<a class="label label-success" data-bind="click: changeStatus"><b data-bind="text: item.status"></b></a>
				</div>
				<div class="form-group">
		            <h4 class="fieldset-legend"></h4>
		        </div>
				<div class="form-group">
					<label>@Source@</label>
					<input data-role="dropdownlist"
						data-value-primitive="true"  
	                    data-text-field="text"
	                    data-value-field="value"               
	                    data-bind="value: item.source, source: sourceOption" style="width: 100%"/>
				</div>
				<div class="form-group">
					<label>@Sender@</label><br>
					<input data-role="autocomplete"
						data-value-primitive="true"
						data-filter="contains"  
	                    data-text-field="name"
	                    data-value-field="id"               
	                    data-bind="value: item.sender_name, source: senderOption, events: {change: senderChange}" style="width: 100%"/>
				</div>
				<div class="form-group">
					<label>@Customer format@</label><br>
					<select data-role="multiselect"
						data-value-primitive="true"  
	                    data-text-field="text"
	                    data-value-field="value"               
	                    data-bind="value: item.customerFormat, source: customerFormatOption" style="width: 100%">
	                    </select>
				</div>
				<div class="form-group">
					<label>@Priority@</label><br>
					<input data-role="dropdownlist"
						data-value-primitive="true"  
	                    data-text-field="text"
	                    data-value-field="value"               
	                    data-bind="value: item.priority, source: priorityOption" style="width: 100%"/>
				</div>
				<div class="form-group">
					<label>@Receive time@</label><br>
					<input data-role="datetimepicker"              
	                    data-bind="value: item.receive_time" style="width: 100%"/>
				</div>
			</div>
			<div class="col-sm-6">
				<div class="form-group">
					<label>@Content@</label>
					<textarea class="k-textbox" style="width: 100%; height: 94px" data-bind="value: item.content"></textarea>
				</div>
		        <div class="form-group" data-bind="visible: item.notOwner">
					<label>@Require name@</label>
					<input class="k-textbox" style="width: 100%" data-bind="value: item.requireName">
				</div>
				<div class="form-group" data-bind="visible: item.notOwner">
					<label>@Require phone@</label>
					<input class="k-textbox" style="width: 100%" data-bind="value: item.requirePhone">
				</div>
				<div class="form-group" data-bind="visible: item.notOwner">
					<label>@Require email@</label>
					<input class="k-textbox" style="width: 100%" data-bind="value: item.requireEmail">
				</div>
				<div class="form-group" data-bind="visible: item.notOwner">
					<label>@Relationship@</label>
					<input data-role="dropdownlist"
						data-value-primitive="true"  
	                    data-text-field="text"
	                    data-value-field="value"               
	                    data-bind="value: item.requireRelation, source: relationOption" style="width: 100%"/>
				</div>
				<div class="form-group">
					<label>@Service@</label>
                    <a href="javascript:void(0)" class="pull-right" data-bind="click: enableAdvancedSearch"><i class="fa fa-search"></i> @Advanced search@</a>
					<input data-role="dropdownlist" name="service"
						data-filter="contains"
						data-value-primitive="true"  
	                    data-text-field="value"
	                    data-value-field="value"               
	                    data-bind="value: item.service, source: serviceOption, events: {select: serviceSelect}" style="width: 100%"/>
	            </div>
                <div id="AdvancedSearch" style="display: none">
                    <div class="form-group">
                        <label>@Service@ level 1</label>
                        <input data-role="dropdownlist" name="serviceLv1"
                               data-filter="contains"
                               data-value-primitive="true"
                               data-text-field="name"
                               data-value-field="name"
                               data-bind="source: serviceLv1Option, events: {select: onSearch1}" style="width: 100%"/>
                    </div>
                    <div class="form-group">
                        <label>@Service@ level 2</label>
                        <input data-role="dropdownlist" name="serviceLv2"
                               data-filter="contains"
                               data-value-primitive="true"
                               data-text-field="name"
                               data-value-field="name"
                               data-bind="source: serviceLv2Option, events: {select: onSearch2}" style="width: 100%"/>
                    </div>
                    <div class="form-group">
                        <label>@Service@ level 3</label>
                        <input data-role="dropdownlist" name="serviceLv3"
                               data-filter="contains"
                               data-value-primitive="true"
                               data-text-field="name"
                               data-value-field="name"
                               data-bind="source: serviceLv3Option, events: {select: onSearch3}" style="width: 100%"/>
                    </div>
                </div>
                <div class="form-group">
                    <h4 class="fieldset-legend"></h4>
                </div>
                <div class="form-group">
                    <label>PNR</label>
                    <input id="pnr-input" data-value-update="keyup" data-bind="value: item.PNR, events: {keypress: savePNROnEnter}" class="k-textbox" style="width: 100%">
                    <input type="hidden" data-bind="value: item.PNRList" />
                </div>
                <div class="form-group">
                    <ul id="pnr-list" style="width: 100%; padding-left: 0" data-template="pnr-template" data-bind="source: item.PNRListDetail"></ul>
                </div>
                <div class="form-group">
                    <label>@Contact person info@</label>
                    <a href="javascript:void(0)" data-bind="click: openAddContactPersonForm" class="pull-right"><i class="fa fa-user-plus"></i> @Add new info@</a>
                    <div id="addNewInfoForm" style="display: none">
                        <div class="form-group">
                            <label>@Name@</label>
                            <input type="hidden" data-bind="value: ci_no">
                            <input class="k-textbox" data-bind="value: ci_name" style="width: 100%">
                        </div>
                        <div class="form-group">
                            <label>@Phone@</label>
                            <input class="k-textbox" data-bind="value: ci_phone" style="width: 100%">
                        </div>
                        <div class="form-group">
                            <label>@Email@</label>
                            <input class="k-textbox" data-bind="value: ci_email" style="width: 100%">
                        </div>
                        <div class="form-group">
                            <button class="btn btn-sm btn-primary btn-save" data-bind="click: updateContactPersonInfo">@Save Info@</button>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <ul class="list-unstyled" data-bind="source: item.contactPersonInfo" data-template="contact-person-info-template"></ul>
                </div>
                <div class="form-group">
                    <input name="files"
                           type="file"
                           data-role="upload"
                           data-async="{ saveUrl: 'api/v1/Ticket_solve/uploadToServer', removeUrl: 'api/v1/Ticket/deleteFromServer', autoUpload: true }"
                           data-bind="visible: isVisible, enabled: isEnabled, events: { error: onError, success: onSuccessUploadFile, remove: onRemoveFile }">
                </div>
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

<script type="text/javascript">
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

    Config.observable.ticketInfo = {
        isAgentAssignHide: false,
        isGroupAssignHide: true,
        ci_name: '',
        ci_phone: '',
        ci_email: '',
        ci_no: '',
        isEnabled: true,
        isVisible: true,
        item: {
            status: "Open",
            source: "Hotline",
            receive_time: new Date(),
            fromPage: Config.observable.fromPage,
            assign: ENV.extension
        },
        sourceOption: dataSourceJsonData(["Ticket", "source"]),
        senderOption: () => dataSourceDropDownList("Customer", ["name"]),
        customerFormatOption: () => dataSourceJsonData(["Ticket", "customer format"]),
        priorityOption: () => dataSourceJsonData(["Ticket", "priority"]),
        relationOption: () => dataSourceJsonData(["Ticket", "relation"]),
        serviceLv1Option: dataSourceService(1),
        serviceLv2Option: [],
        serviceLv3Option: [],
        assignOptionAgent: new kendo.data.DataSource({
            transport: {
                read: ENV.restApi + "user",
                parameterMap: parameterMap
            },
            schema: {
                data: function (response) {
                    return response.data;
                },
                total: "total"
            }
        }),
        assignOptionGroup: new kendo.data.DataSource({
            transport: {
                read: ENV.restApi + "group",
                parameterMap: parameterMap
            },
            schema: {
                data: function (response) {
                    return response.data;
                },
                total: "total"
            }
        }),
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
        save: function() {
            if(typeof Table !== 'undefined') {
                Table.dataSource.add(this.item);
                Table.dataSource.sync().then(() => {Table.dataSource.read()});
            }
            else {
                $.ajax({
                    url: ENV.vApi + Config.collection + "/create",
                    type: "POST",
                    data: JSON.stringify(this.item),
                    contentType: "application/json; charset=utf-8",
                    dataType: 'JSON',
                    success: (response) => {
                        if(response.status == 1) {
                            notification.show("@Thêm mới ticket thành công@", 'success');
                        }
                        else{
                            notification.show("@Thêm mới ticket thất bại, in vui lòng kiểm tra lại@", 'error')
                        }
                    },
                    error: errorDataSource
                });
            }
        },
        changeStatus: function(e) {
            if(this.item.status == "Open") {
                this.set("item.status", "Urgent");
                $(e.currentTarget).removeClass("label-success").addClass("label-danger");
            } else {
                this.set("item.status", "Open");
                $(e.currentTarget).removeClass("label-danger").addClass("label-success");
            }
        },
        senderChange: function(e) {
            var item = e.sender.dataItem();
            if(typeof item !== 'undefined') {
                this.set("item.sender_id", item.id);
            }
        },
        isAgentAssignClick: function(e) {
            if($("#isAgentAssign").prop('checked') === true){
                this.set('isAgentAssignHide', true);
                this.set('isGroupAssignHide', false);
            }
            else {
                this.set('isAgentAssignHide', false);
                this.set('isGroupAssignHide', true);
            }
        },
        enableAdvancedSearch: function(e) {
            $("#AdvancedSearch1").animate({
                height: 'toggle'
            });
        },
        serviceSelect: function(e) {
            this.set("visibleAdvancedSearch", false);
        },
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
        savePNROnEnter: function(e) {
            var code = (e.which);
            if(code == 13) { //Enter keycode
                $.ajax({
                    url: ENV.vApi + "ticket/getPNR/" + this.get('item.PNR'),
                    type: "GET",
                    beforeSend: function(){
                        if(HELPER.loaderHtml) $("#form-loader").html(HELPER.loaderHtml).show();
                    },
                    complete: function(){
                        if(HELPER.loaderHtml) $("#form-loader").html("").hide();
                    },
                    success: (response) => {
                        if(response.status == 1) {
                            var PNRList = this.get('item.PNRList');
                            var PNRListDetail = this.get('item.PNRListDetail');
                            if(typeof PNRList !== 'undefined' && PNRList !== null && PNRList !== []) {
                                PNRList.push(this.get('item.PNR'));
                            }
                            else {
                                PNRList = [];
                                PNRList.push(this.get('item.PNR'));
                            }
                            if(typeof PNRListDetail !== 'undefined' && PNRListDetail !== null && PNRListDetail !== []) {
                                PNRListDetail.push(response.data);
                            }
                            else {
                                PNRListDetail = [];
                                PNRListDetail.push(response.data);
                            }
                            this.set('item.PNRList', PNRList);
                            this.set('item.PNRListDetail', PNRListDetail);
                        }
                        else {
                            var PNRList = this.get('item.PNRList');
                            var PNRListDetail = this.get('item.PNRListDetail');
                            if(typeof PNRList !== 'undefined' && PNRList !== null && PNRList !== []) {
                                PNRList.push(this.get('item.PNR'));
                            }
                            else {
                                PNRList = [];
                                PNRList.push(this.get('item.PNR'));
                            }
                            if(typeof PNRListDetail !== 'undefined' && PNRListDetail !== null && PNRListDetail !== []) {
                                PNRListDetail.push({pnr_code: this.get('item.PNR'), pnr_state: 'error', pnr_info: '1. BL-740 Thu, 04Apr19 SGN - HAN 05:40 - 07:45HK 0St Starter (STARTER Class M) VND 1,240,000 VND 1,737,000 VND.\n2. BL-740 Thu, 04Apr19 SGN - HAN 05:40 - 07:45HK 0St Starter (STARTER Class M) VND 1,240,000 VND 1,737,000 VND'});
                            }
                            else {
                                PNRListDetail = [];
                                PNRListDetail.push({pnr_code: this.get('item.PNR'), pnr_state: 'error', pnr_info: '1. BL-740 Thu, 04Apr19 SGN - HAN 05:40 - 07:45HK 0St Starter (STARTER Class M) VND 1,240,000 VND 1,737,000 VND.\n2. BL-740 Thu, 04Apr19 SGN - HAN 05:40 - 07:45HK 0St Starter (STARTER Class M) VND 1,240,000 VND 1,737,000 VND'});
                            }
                            this.set('item.PNRList', PNRList);
                            this.set('item.PNRListDetail', PNRListDetail);
                            this.set('isPNRErrorMesg', false);
                        }
                    },
                    error: errorDataSource
                });
            }
        },
        openAddContactPersonForm: function(e) {
            $("#addNewInfoForm").animate({
                height: 'toggle'
            });
        },
        updateContactPersonInfo: function(e) {
            var contactPersonInfoList = this.get('item.contactPersonInfo');
            if(typeof contactPersonInfoList !== 'undefined' && contactPersonInfoList !== null && contactPersonInfoList !== []) {
                contactPersonInfoList.push({no: contactPersonInfoList.length.toString(), name: this.get('ci_name'), phone: this.get('ci_phone'), email: this.get('ci_email')});
            }
            else {
                contactPersonInfoList = [];
                contactPersonInfoList.push({no: 0, name: this.get('ci_name'), phone: this.get('ci_phone'), email: this.get('ci_email')});
            }
            this.set('item.contactPersonInfo', contactPersonInfoList);
        },
        deleteContactFromList: function(contact) {
            var contactPersonInfoList = this.get('item.contactPersonInfo');
            $.each(contactPersonInfoList, function(key, value) {
                if(contact.data.no === value.no) {
                    contactPersonInfoList.splice(key, 1);
                }
            });
            this.set('item.contactPersonInfoList', contactPersonInfoList);
        },
        openPNRDetail: function(pnr_code) {
            window.open(`manage/Pnrdetail/#/detail/${pnr_code.data.pnr_code}`,'_blank',null);
        },
        onError: function(e) {
            var files = e.files;
            if (e.operation == "upload") {
                alert("Failed to upload " + files.length + " files");
            }
        },
        onSuccessUploadFile(e) {
            var listUploadFile = this.get('item.listUploadFile');
            if (e.operation == "upload") {
                if(typeof listUploadFile !== 'undefined' && listUploadFile !== null) {
                    listUploadFile.push(e.response);
                }
                else {
                    listUploadFile = [];
                    listUploadFile.push(e.response);
                }
                this.set('item.listUploadFile', listUploadFile);
                e.files[0].fileOriginalName = e.files[0].name;
                e.files[0].name = e.response;
            }
        },
        onRemoveFile(e) {
            var listUploadFile = this.get('item.listUploadFile');
            listUploadFile.splice($.inArray(e.files[0].fileUrl, listUploadFile),1);
            this.set('item.listUploadFile', listUploadFile);
        },
        deletePNRFromList(pnrInfo) {
            var PNRList = this.get('item.PNRList');
            var PNRListDetail = this.get('item.PNRListDetail');
            console.log(PNRListDetail);
            $.each(PNRListDetail, function (key, value) {
                if(typeof value !== 'undefined' && pnrInfo.data.pnr_code === value.pnr_code) {
                    PNRListDetail.splice(key, 1);
                }
            });
            PNRList.splice($.inArray(pnrInfo.data.pnr_code, PNRList), 1);
            this.set('item.PNRList', PNRList);
            this.set('item.PNRListDetail', PNRListDetail);
        }
    };
    $(document).ready(function() {
        $("body").tooltip({ selector: '[data-toggle=tooltip]' });
    });
</script>

<style>
    .tooltip-inner {
        max-width: 100% !important;
    }
</style>
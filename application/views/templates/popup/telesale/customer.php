<div id="all-popup">
    <div id="popup-window" data-role="window"
                     data-title="TELESALE CUSTOMER"
                     data-width="1200"
                     data-actions="['Arrows-no-change', 'Save','Tri-state-indeterminate','Refresh', 'Minimize', 'Maximize', 'Close']"
                     data-position="{'top': 20}"
                     data-visible="false"
                     data-bind="events: {open: openPopup, close: closePopup, activate: activatePopup}" style="padding: 2px; max-height: 90vh">
        <div class="container-fluid">
            <div class="row">
                <div id="popup-tabstrip" data-role="tabstrip" style="margin-top: 2px">
                    <ul>
                        <li class="k-state-active">
                            <i class="fa fa-user"></i><b> OBJECT INFOMATION</b>
                        </li>
                        <li data-bind="visible: isCallinglistCus, click: openDetail">
                            <i class="gi gi-vcard"></i><b> CUSTOMER DETAIL</b>
                        </li>
                        <li data-bind="click: openCdr">
                            <i class="fa fa-phone-square"></i><b> CDR</b>
                        </li>
                        <li data-bind="click: openDatalibrary, invisible: item.assign">
                            <i class="fa fa-book"></i><b> Data library</b>
                        </li>
                        <div class="pull-right">
                            <span data-bind="text: phone" style="font-size: 18px; vertical-align: -2px" class="text-primary"></span>
                            <a data-role="button" data-bind="click: playRecording, visible: _dataCall.record_file_name" title="Recording" style="vertical-align: 2px">
                                <i class="fa fa-play"></i>
                            </a>
                        </div>
                    </ul>
                    <div>
                        <div class="container-fluid">
                            <div class="row form-horizontal" style="padding-top: 10px">
                                <div class="col-sm-5">
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">@Type of object@</label>
                                        <div class="col-xs-8">
                                            <span style="vertical-align: -7px">Customer</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">@Assign@</label>
                                        <div class="col-xs-8">
                                            <div data-bind="visible: isNewData"><button style="vertical-align: -7px" data-role="button" data-icon="edit" data-bind="click: assigning">New - Xin data</button></div>
                                            <div data-bind="visible: item.dl_assign"><span style="vertical-align: -7px" data-bind="text: item.dl_assign_name"></span></div>
                                            <div data-bind="visible: item.assigning"><span style="vertical-align: -7px" data-bind="text: item.assigning_name"></span><span style="vertical-align: -7px"> - Chờ duyệt</span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row form-horizontal">
                                <div class="col-sm-5">
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">@Name@</label>
                                        <div class="col-xs-8">
	                                        <div class="input-group">
                                                <span style="vertical-align: -7px" data-bind="text: item.name, invisible: enableName"></span>
						                        <input class="k-textbox upper-case-input" name="name" data-bind="value: item.name, visible: enableName" style="width: 100%">
						                        <div class="input-group-addon" style="border: 0">
						                        	<label style="margin-bottom: 0; cursor: pointer">
						                        		<input type="checkbox" class="hidden" data-bind="checked: enableName">
						                        		<!-- <span class="fa fa-pencil"></span> -->
						                        	</label>
						                        </div>
						                    </div>
					                	</div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">@Nation ID@</label>
                                        <div class="col-xs-8">
	                                        <div class="input-group">
                                                <span style="vertical-align: -7px" data-bind="text: item.id_no"></span>
						                    </div>
					                	</div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">@Birthday@</label>
                                        <div class="col-xs-8">
	                                        <div class="input-group">
                                                <span style="vertical-align: -7px" data-format="dd/MM/yyyy" data-bind="text: item.date_of_birth, invisible: enableBirthday"></span>
						                        <input data-role="datepicker" data-format="dd/MM/yyyy" name="date_of_birth" data-bind="value: item.date_of_birth, visible: enableBirthday" style="width: 100%">
						                        <div class="input-group-addon" style="border: 0">
						                        	<label style="margin-bottom: 0; cursor: pointer">
						                        		<input type="checkbox" class="hidden" data-bind="checked: enableBirthday">
						                        		<!-- <span class="fa fa-pencil"></span> -->
						                        	</label>
						                        </div>
						                    </div>
					                	</div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">@Object's Phone@</label>
                                        <div class="col-xs-8">
	                                        <div class="input-group">
                                                <span style="vertical-align: -7px" data-bind="text: item.phone, invisible: enablePhone"></span>
						                        <input class="k-textbox" name="phone" data-bind="value: item.phone, visible: enablePhone" style="width: 100%">
						                        <div class="input-group-addon" style="border: 0">
						                        	<label style="margin-bottom: 0; cursor: pointer">
						                        		<input type="checkbox" class="hidden" data-bind="checked: enablePhone">
						                        		<!-- <span class="fa fa-pencil"></span> -->
						                        	</label>
						                        </div>
						                    </div>
					                	</div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">@Preference Phone@</label>
                                        <div class="col-xs-8">
                                            <div class="input-group">
                                                <span style="vertical-align: -7px" data-bind="text: item.phone_ref"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Potential</label>
                                        <div class="col-xs-8">
                                            <div class="checkbox"><label><input type="checkbox" data-bind="checked: item.is_potential"></label></div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Result</label>
                                        <div class="col-xs-8">
                                            <input data-role="dropdownlist" name="result" 
                                            data-value-primitive="true"
                                            data-text-field="text"
                                            data-value-field="value" 
                                            data-bind="value: item.result, source: callCodeOption" 
                                            style="width: 100%">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Note</label>
                                        <div class="col-xs-8">
                                            <textarea class="k-textbox" name="note" data-bind="value: item.note" style="width: 100%; height: 72px"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label class="control-label col-xs-4" style="padding-top: 2px">
	                                        <input type="checkbox" data-bind="checked: followUpChecked">
	                                        <span>ReCall</span>
	                                    </label>
	                                    <div class="col-xs-8">
	                                        <input data-role="datetimepicker" data-date-input="true" data-format="dd/MM/yyyy H:mm" data-bind="value: followUp.reCall, visible: followUpChecked" style="width: 100%">
	                                    </div>
                                    </div>
                                    <div class="form-group" data-bind="visible: followUpChecked">
                                        <label class="control-label col-xs-4">Recall reason</label>
                                        <div class="col-xs-8">
                                            <input class="k-textbox" name="result" data-bind="value: followUp.reCallReason" style="width: 100%">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row title-row">
                            	<div class="col-sm-5">
	                                <span class="text-primary">LAST LOAN INFORMATION</span>
	                                <hr class="popup">
                            	</div>
                            	<div class="col-sm-7">
	                                <span class="text-primary">ESTIMATE PAYMENT</span>
	                                <hr class="popup">
                            	</div>
                            </div>
                            <div class="row form-horizontal">
                                <div class="col-sm-5">
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Last interest rate</label>
                                        <div class="col-xs-8">
                                            <span style="vertical-align: -7px" data-bind="text: item.interest_rate"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Last term</label>
                                        <div class="col-xs-8">
                                            <span style="vertical-align: -7px" data-bind="text: item.term"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Last balance</label>
                                        <div class="col-xs-8">
                                            <span data-format="n0" style="vertical-align: -7px" data-bind="text: item.balance"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Old loan</label>
                                        <div class="col-xs-8">
                                            <span data-format="n0" style="vertical-align: -7px" data-bind="text: item.old_loan"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Old monthly payment</label>
                                        <div class="col-xs-8">
                                            <span data-format="n0" style="vertical-align: -7px" data-bind="text: item.old_monthly_payment"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Dealer name</label>
                                        <div class="col-xs-8">
                                            <span data-format="n0" style="vertical-align: -7px" data-bind="text: item.dealer_name"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Rate <i class="fa fa-info-circle text-info" data-role="tooltip" title="Add on interest / Rate"></i></label>
                                        <div class="col-xs-8">
                                            <input id="rate" data-role="combobox" data-text-field="text" data-value-field="value" data-clear-button="false" name="rate" data-value-primitive="true" data-bind="value: item.rate_text, events: {change: operandChange, dataBound: onDataBoundRate}, source: rateOption" style="width: 100%">
                                            <!-- <div class="input-group">
						                        <input data-role="numerictextbox" data-decimals="6" data-format="p4" data-spinners="false" data-round="4" data-factor="100" data-max="1" data-min="0" name="rate" data-bind="value: item.rate, events: {change: operandChange}" style="width: 100%">
						                        <div class="input-group-addon">
						                        	<b>%</b>
						                        </div>
						                    </div> -->
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Loan amount</label>
                                        <div class="col-xs-8">
                                            <input data-role="numerictextbox" data-format="n0" name="loan_amount" data-bind="value: item.loan_amount, events: {change: operandChange}" style="width: 100%">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Term</label>
                                        <div class="col-xs-8">
                                            <input data-role="numerictextbox" data-format="n0" name="temp_term" data-bind="value: item.temp_term, events: {change: operandChange}" style="width: 100%">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                	<div class="form-group">
                                        <label class="control-label col-xs-6">Monthly payment</label>
                                        <div class="col-xs-6">
                                        	<b class="text-success" data-bind="text: item.monthlyPayment" data-format="n0" style="line-height: 2.3"></b>
                                        </div>
                                        <div class="col-xs-12" style="margin-top: 10px">
                                        	<table style="font-weight: bold; font-size: 14px">
                                        		<tr>
                                        			<td rowspan="2">
                                        				<i class="text-danger" data-format="n0" data-bind="text: item.loan_amount"></i>
                                        				<span> x&nbsp;</span>
                                        			</td>
                                        			<td style="border-bottom: 1px solid black; text-align: center; padding-bottom: 5px">
                                        				<i class="text-danger" data-bind="text: item.rate">Rate</i>
                                        			</td>
                                        		</tr>
                                        		<tr>
                                        			<td style="padding-top: 5px">
                                        				<span>1 - (1 + <i class="text-danger" data-bind="text: item.rate">Rate</i>)<sup>-<i class="text-danger" data-bind="text: item.temp_term">Term</i></sup></span>
                                        			</td>
                                        		</tr>
                                        	</table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row text-center">
                                <button data-role="button" class="btn-primary" data-icon="calendar" data-bind="click: makeAppointment, visible: isSavePopup">@Make appointment@</button>
                                <button data-role="button" data-icon="save" data-bind="click: save, visible: isSavePopup">@Save@</button>
                            </div>
                        </div>
                    </div>
                    <div style="padding: 0; overflow-x: hidden; overflow-y: hidden; min-height: 100%" id="customer-detail-content">
                    </div>
                    <div style="padding: 0; overflow-x: hidden; overflow-y: hidden; min-height: 100%" id="cdr-content">
                    </div>
                    <div style="padding: 0; overflow-x: hidden; overflow-y: hidden; min-height: 100%" id="data-library-content">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
class diallistPopup1 extends Popup {
    constructor(dataCall) {
        super(dataCall);
        Object.assign(this, {
            _fieldId : "dialid",
            _popupType: "default",
            phone: dataCall.customernumber,
            openDetail: function(e) {
            	var $content = $("#customer-detail-content");
            	if(!$content.find("iframe").length)
            		$content.append(`<iframe src="${this.detailUrl}"" style="width: 100%; height: 70vh; border: 0"></iframe>`);
            },
        });
        return this;
    }

    async init(fieldId) {
    	var fieldIdValue = this._dataCall[this._fieldId];
        /* Lấy dữ liệu */
        var responseObj = await $.get(ENV.restApi + `telesalelist/${fieldIdValue}`);

        if(!responseObj) {
            responseObj = {};
            $("#popup-contain").empty();
            notification.show("Data is not found", "error");
            return;
        }

        if(responseObj.date_of_birth) responseObj.date_of_birth = new Date(responseObj.date_of_birth);

        this.item = responseObj;

        /* Lấy iframe chi tiết khách hàng */
        var detailUrl = detailUrl = `${ENV.baseUrl}manage/telesalelist/solve?omc=1#/detail_customer/${responseObj.id}`
        this.assign({detailUrl: detailUrl}).open();
    }
}

var callData = <?= json_encode($callData) ?>;

window.popupObservable = new diallistPopup1(callData);
window.popupObservable.assign({
	followUp: {},
    callCodeOption: dataSourceJsonData(["Call", "result"]),
    rateOption: () => dataSourceDropDownList("Rate", ["text", "value"]),
    playRecording: function(e) {
        play(this._dataCall.calluuid);
    },
    loan_amount: "Loan amount",
    rate: "Rate",
    term: "Term",
    operandChange: function(e) {
        var rate_source = $("#rate").data("kendoComboBox");
        var selectedIndex = rate_source.selectedIndex
        if(selectedIndex == -1) {
            this.set("item.rate", (parseFloat(this.get("item.rate_text").replace('%','')) / 100).toFixed(4))
            this.set("item.rate_text", parseFloat(this.get("item.rate_text")).toFixed(2) + "%")
        }
        else {
            this.set("item.rate", this.get("item.rate_text"))
        }
        var rate = parseFloat(this.get("item.rate")),
    		loan_amount = this.get("item.loan_amount"),
            temp_term = this.get("item.temp_term");
    	if(typeof rate == "number" && typeof loan_amount == "number" && typeof temp_term == "number") {
	    	var monthlyPayment = loan_amount * (rate / (1 - (1 + rate) ** (-temp_term)));
	    	this.set("item.monthlyPayment", Math.round(monthlyPayment));
    	}
    },
    onDataBoundRate: function(e) {
        if(!this.get('item.rate_text')) {
            this.set('item.rate_text', this.get('item.rate'));
        }
    },
    save: function() {
        var data = this.item.toJSON();
        data['calluuid'] = window.popupObservable._dataCall.calluuid;
        $.ajax({
            url: ENV.vApi + "telesalelist_solve/updateByCif/" + (data.cif || "").toString(),
            type: "PUT",
            contentType: "application/json; charset=utf-8",
            data: kendo.stringify(data),
            success: (response) => {
                if(response.status)
                    syncDataSource();
            },
            error: errorDataSource
        });
        $.ajax({
            url: ENV.restApi + "data_library/" + (data.id || "").toString(),
            type: "PUT",
            contentType: "application/json; charset=utf-8",
            data: kendo.stringify(data),
            success: (response) => {
                if(response.status)
                    syncDataSource();
            },
            error: errorDataSource
        })
        $.ajax({
            url: ENV.vApi + "cdr/" + this._dataCall.calluuid,
            type: "PUT",
            contentType: "application/json; charset=utf-8",
            data: kendo.stringify({customer: data}),
            error: errorDataSource
        })
        if(this.followUpChecked) {
            var followUpData = Object.assign(this.get("followUp").toJSON(), {
                name: data.name,
                phone: data.phone,
                id: data.id,
                collection: "TS_Telesalelist"
            });
            $.ajax({
                url: ENV.restApi + "follow_up",
                type: "POST",
                contentType: "application/json; charset=utf-8",
                data: kendo.stringify(followUpData),
                success: (response) => {
                    if(response.status)
                        syncDataSource();
                },
                error: errorDataSource
            })
        }
    },
    openAppointmentForm: async function(option = {}) {
        $rightForm = $("#right-form");
        var formHtml = await $.ajax({
            url: ENV.templateApi + "appointment_log_solve/formAutoFill",
            data: {doc: option},
            error: errorDataSource
        });
        kendo.destroy($rightForm);
        $rightForm.empty();
        $rightForm.append(formHtml);
    },
    makeAppointment: function(e) {
	    this.save();
        openForm({title: '@Add@ @Appointment@', width: 700});
        this.openAppointmentForm(this.item.toJSON());
        this.closePopup();
    },
    openCdr: function(e) {
        var filter = JSON.stringify({
            logic: "and",
            filters: [
                {field: "customernumber", operator: "eq", value: this.phone}
            ]
        });
        var query = httpBuildQuery({filter: filter, omc: 1});
        var $content = $("#cdr-content");
        if(!$content.find("iframe").length)
            $content.append(`<iframe src='${ENV.baseUrl}manage/cdr?${query}' style="width: 100%; height: 500px; border: 0"></iframe>`);
    },
    openDatalibrary: function(e) {
        var filter = JSON.stringify({
            logic: "and",
            filters: [
                {field: "mobile_phone_no", operator: "eq", value: this.item.phone}
            ]
        });
        var query = httpBuildQuery({filter: filter, omc: 1});
        var $content = $("#data-library-content");
        if(!$content.find("iframe").length)
            $content.append(`<iframe src='${ENV.baseUrl}manage/data_library?${query}' style="width: 100%; height: 500px; border: 0"></iframe>`);
    },
    assigning: function(e) {
        var assigning_data = this.item;
        assigning_data.calluuid = this._dataCall.calluuid;
        $.ajax({
            url: ENV.vApi + "telesalelist_assigning/requestAssign",
            type: "PUT",
            contentType: "application/json; charset=utf-8",
            data: kendo.stringify(assigning_data),
            success: (response) => {
                if(response.status) {
                    notification.show(response.message, 'success');
                }
                else {
                    notification.show(response.message, 'error');
                }
            },
            // error: errorDataSource
        });
    },
    isNewData: function(e) {
        if(!this.get('item.assign') && !this.get('item.assigning') && this.get('item.id')) {
            return true
        }
        else return false
    },
    isCallinglistCus: function(e) {
        if(this.get('detailUrl') && !this.get('item.is_data_library_list')) {
            return true;
        }
        else {
            return false;
        }
    },
    isSavePopup: function(e) {
        if(ENV.extension == this.get('item.assign') || ENV.role_name == 'Admin - Manager') {
            return true;
        }
        else {
            return false;
        }
    }
})
window.popupObservable.init();
</script>

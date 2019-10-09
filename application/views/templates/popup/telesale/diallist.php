ư<div id="all-popup">
    <div id="popup-window" data-role="window"
                     data-title="POPUP TELESALE"
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
                        <li data-bind="visible: detailUrl, click: openDetail">
                            CUSTOMER DETAIL
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
		                                    <input data-role="dropdownlist" name="object_type" 
		                                    data-bind="value: item.object_type, source: objectTypeOption" 
		                                    style="width: 100%">
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
						                        <input class="k-textbox upper-case-input" name="name" data-bind="value: item.name, enabled: enableName" style="width: 100%">
						                        <div class="input-group-addon">
						                        	<label style="margin-bottom: 0; cursor: pointer">
						                        		<input type="checkbox" class="hidden" data-bind="checked: enableName">
						                        		<span class="fa fa-pencil"></span>
						                        	</label>
						                        </div>
						                    </div>
					                	</div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">@Nation ID@</label>
                                        <div class="col-xs-8">
	                                        <div class="input-group">
						                        <input class="k-textbox" name="nation_id" data-bind="value: item.nation_id, enabled: enableNationId" style="width: 100%">
						                        <div class="input-group-addon">
						                        	<label style="margin-bottom: 0; cursor: pointer">
						                        		<input type="checkbox" class="hidden" data-bind="checked: enableNationId">
						                        		<span class="fa fa-pencil"></span>
						                        	</label>
						                        </div>
						                    </div>
					                	</div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">@Birthday@</label>
                                        <div class="col-xs-8">
	                                        <div class="input-group">
						                        <input class="k-textbox" name="birthday" data-bind="value: item.birthday, enabled: enableBirthday" style="width: 100%">
						                        <div class="input-group-addon">
						                        	<label style="margin-bottom: 0; cursor: pointer">
						                        		<input type="checkbox" class="hidden" data-bind="checked: enableBirthday">
						                        		<span class="fa fa-pencil"></span>
						                        	</label>
						                        </div>
						                    </div>
					                	</div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">@Object's Phone@</label>
                                        <div class="col-xs-8">
	                                        <div class="input-group">
						                        <input class="k-textbox" name="phone" data-bind="value: item.phone, enabled: enablePhone" style="width: 100%">
						                        <div class="input-group-addon">
						                        	<label style="margin-bottom: 0; cursor: pointer">
						                        		<input type="checkbox" class="hidden" data-bind="checked: enablePhone">
						                        		<span class="fa fa-pencil"></span>
						                        	</label>
						                        </div>
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
                                            <input class="k-textbox" name="result" data-bind="value: call.result" style="width: 100%">
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
                                            <input class="k-textbox" name="last_int_rate" data-bind="value: item.last_int_rate" style="width: 100%">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Last term</label>
                                        <div class="col-xs-8">
                                            <input class="k-textbox" name="last_term" data-bind="value: item.last_term" style="width: 100%">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Last balance</label>
                                        <div class="col-xs-8">
                                            <input class="k-textbox" name="last_balance" data-bind="value: item.last_balance" style="width: 100%">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Rate <i class="fa fa-info-circle text-info" data-role="tooltip" title="Add on interest / Rate"></i></label>
                                        <div class="col-xs-8">
                                            <div class="input-group">
						                        <input data-role="numerictextbox" data-decimals="6" data-format="p4" data-spinners="false" data-round="4" data-factor="100" data-max="1" data-min="0" name="rate" data-bind="value: rate, events: {change: operandChange}" style="width: 100%">
						                        <div class="input-group-addon">
						                        	<b>%</b>
						                        </div>
						                    </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Loan amount</label>
                                        <div class="col-xs-8">
                                            <input data-role="numerictextbox" data-format="n0" name="loan_amount" data-bind="value: loan_amount, events: {change: operandChange}" style="width: 100%">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Term</label>
                                        <div class="col-xs-8">
                                            <input data-role="numerictextbox" data-format="n0" name="term" data-bind="value: term, events: {change: operandChange}" style="width: 100%">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                	<div class="form-group">
                                        <label class="control-label col-xs-6">Monthly payment</label>
                                        <div class="col-xs-6">
                                        	<b class="text-success" data-bind="text: monthlyPayment" data-format="n0" style="line-height: 2.3"></b>
                                        </div>
                                        <div class="col-xs-12" style="margin-top: 10px">
                                        	<table style="font-weight: bold; font-size: 14px">
                                        		<tr>
                                        			<td rowspan="2">
                                        				<i class="text-danger" data-format="n0" data-bind="text: loan_amount"></i>
                                        				<span> x&nbsp;</span>
                                        			</td>
                                        			<td style="border-bottom: 1px solid black; text-align: center; padding-bottom: 5px">
                                        				<i class="text-danger" data-bind="text: rate">Rate</i>
                                        			</td>
                                        		</tr>
                                        		<tr>
                                        			<td style="padding-top: 5px">
                                        				<span>1 - (1 + <i class="text-danger" data-bind="text: rate">Rate</i>)<sup>-<i class="text-danger" data-bind="text: term">Term</i></sup></span>
                                        			</td>
                                        		</tr>
                                        	</table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row text-center">
                            	<button data-role="button" class="btn-primary" data-icon="calendar" data-bind="click: makeAppointment">@Make appointment@</button>
                                <button data-role="button" data-icon="save" data-bind="click: save">@Save@</button>
                            </div>
                        </div>
                    </div>
                    <div style="padding: 0; overflow-x: hidden; overflow-y: hidden; min-height: 100%" id="customer-detail-content">
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
            }
        });
        return this;
    }

    async init(fieldId) {
    	var fieldIdValue = this._dataCall[this._fieldId];

        var responseObj = await $.get(ENV.restApi + `diallist_detail/${fieldIdValue}`);

        if(!responseObj) {
        	responseObj = {};
            /*notification.show("Diallist detail is not found", "error");
            return;*/
        }

        this.item = responseObj;
        var phone = responseObj.phone;
        var detailUrl = "";
        $.get(ENV.vApi + `popup/get_customer_by_phone?_=${Date.now()}&phone=${phone}`).then(res => {
            if(res.total == 1) {
                detailUrl = `${ENV.baseUrl}manage/customer?omc=1#/detail/${res.data[0].id}` 
            }
            this.assign({detailUrl: detailUrl}).open();
        }, (err) => {
            this.assign({detailUrl: detailUrl}).open();
        })
    }
}

var callData = <?= json_encode($callData) ?>;

window.popupObservable = new diallistPopup1(callData);
window.popupObservable.assign({
	objectTypeOption: ["SC", "Customer", "Other"],
	followUp: {},
    callCodeOption: dataSourceDropDownList("Call_code", ["text", "value", "type"]),
    playRecording: function(e) {
        play(this._dataCall.calluuid);
    },
    loan_amount: "Loan amount",
    rate: "Rate",
    term: "Term",
    operandChange: function(e) {
    	var rate = this.get("rate"),
    		loan_amount = this.get("loan_amount"),
    		term = this.get("term");
    	if(typeof rate == "number" && typeof loan_amount == "number" && typeof term == "number") {
	    	var monthlyPayment = loan_amount * (rate / (1 - (1 + rate) ** (-term)));
	    	this.set("monthlyPayment", Math.round(monthlyPayment));
    	}
    },
    save: function() {
        var data = this.item.toJSON();
        $.ajax({
            url: ENV.restApi + "diallist_detail/" + (data.id || "").toString(),
            type: "PUT",
            contentType: "application/json; charset=utf-8",
            data: kendo.stringify(data),
            success: (response) => {
                if(response.status)
                    syncDataSource();
            },
            error: errorDataSource
        })
    }
})
window.popupObservable.init();
</script>

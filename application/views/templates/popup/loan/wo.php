<div id="all-popup">
    <div id="popup-window" data-role="window"
                     data-title="LOAN CUSTOMER"
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
                            <i class="gi gi-vcard"></i><b> CUSTOMER DETAIL</b>
                        </li>
                        <li data-bind="click: openCdr">
                            <i class="fa fa-phone-square"></i><b> CDR</b>
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
                                            <span style="vertical-align: -7px">Normal</span>
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
                                                <span style="vertical-align: -7px" data-bind="text: item.phone, invisible: enablePhone"></span>
                                                <input class="k-textbox" name="phone" data-bind="value: item.phone, visible: enablePhone" style="width: 100%">
                                                <div class="input-group-addon" style="border: 0">
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
                                            <input class="k-textbox" name="result" data-bind="value: followUp.reCallReason" style="width: 100%">
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
                    <div style="padding: 0; overflow-x: hidden; overflow-y: hidden; min-height: 100%" id="cdr-content">
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
            _fieldId : "customernumber",
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
        var responseObj = await $.get(ENV.vApi + `popup/get_customer_by_phone`, {phone: fieldIdValue});

        if(responseObj.total == 0) {
            this.item = {};
            this.open();
        } else if(responseObj.total == 1) {
            var customer = responseObj.data[0];
            if(customer.date_of_birth) customer.date_of_birth = new Date(customer.date_of_birth * 1000);
            var detailUrl = `${ENV.baseUrl}manage/telesalelist?omc=1#/detail_customer/${customer.id}`;
            this.item = customer;
            this.assign({detailUrl: detailUrl}).open();
        } else {
            var buttons = {cancel: true};
            for (var i = 0; i < responseObj.total; i++) {
                buttons[i] = {text: responseObj.data[i].name};
            }
            var type = swal({
                title: "@Choose one@.",
                text: `@Greater than one customer have this number@.`,
                icon: "warning",
                buttons: buttons
            }).then(async index => {
                if(index !== null && index !== false) {
                    var customer = responseObj.data[index];
                    if(customer.date_of_birth) customer.date_of_birth = new Date(customer.date_of_birth * 1000);
                    var detailUrl = `${ENV.baseUrl}manage/customer?omc=1#/detail/${customer.id}`;
                    this.item = customer;
                    this.assign({detailUrl: detailUrl}).open();
                } else {
                    $("#popup-contain").empty();
                }
            })
        }
    }
}

var callData = <?= json_encode($callData) ?>;

window.popupObservable = new diallistPopup1(callData);
window.popupObservable.assign({
    followUp: {},
    callCodeOption: dataSourceJsonData(["Call", "result"]),
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
            url: ENV.restApi + "telesalelist/" + (data.id || "").toString(),
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
            url: ENV.vApi + "cdr/" + this._dataCall.calluuid,
            type: "PUT",
            contentType: "application/json; charset=utf-8",
            data: kendo.stringify({customer: data}),
            error: errorDataSource
        });
        if(this.followUpChecked) {
            var followUpData = Object.assign(this.get("followUp").toJSON(), {
                name: data.customer_name,
                phone: data.mobile_phone_no,
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
    }
})
window.popupObservable.init();
</script>

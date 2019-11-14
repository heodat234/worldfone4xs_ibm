ư<div id="all-popup">
    <div id="popup-window" data-role="window"
                     data-title="POPUP LOAN"
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
                                            <span style="vertical-align: -7px">Other</span>
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
                                                <input class="k-textbox upper-case-input" name="customer_name" data-bind="value: item.customer_name, enabled: enableName" style="width: 100%">
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
class otherPopup extends Popup {
    constructor(dataCall) {
        super(dataCall);
        Object.assign(this, {
            _fieldId : "customernumber",
            _popupType: "other",
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
        var responseObj = await $.get(ENV.vApi + `popup/get_customer_by_phone`, {_: Date.now(), phone: fieldIdValue});

        if(!responseObj) {
            responseObj = {};
        }

        this.item = responseObj;
        /* Lấy iframe chi tiết khách hàng */
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

window.popupObservable = new otherPopup(callData);
window.popupObservable.assign({
    followUp: {},
    callCodeOption: dataSourceDropDownList("Call_code", ["text", "value", "type"]),
    playRecording: function(e) {
        play(this._dataCall.calluuid);
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
    },
    openAppointmentForm: async function(option = {}) {
        $rightForm = $("#right-form");
        var formHtml = await $.ajax({
            url: ENV.templateApi + "appointment_log/formAutoFill",
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
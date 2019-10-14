ư<div id="all-popup">
    <div id="popup-window" data-role="window"
                     data-title="Click to call"
                     data-width="1000"
                     data-actions="['Save','Pause','Refresh', 'Minimize', 'Maximize', 'Close']"
                     data-position="{'top': 20}"
                     data-visible="false"
                     data-bind="events: {open: openPopup, close: closePopup, activate: activatePopup}" style="padding: 2px; max-height: 90vh">
        <div class="container-fluid">
            <div class="row">
                <div id="popup-tabstrip" data-role="tabstrip" style="margin-top: 2px">
                    <ul>
                        <li class="k-state-active">
                            CALL FORM
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
                            <div class="row title-row">
                                <span class="text-primary">CUSTOMER INFOMATION</span>
                                <hr class="popup">
                            </div>
                            <div class="row form-horizontal">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Customer Name</label>
                                        <div class="col-xs-8">
                                            <input class="k-textbox upper-case-input" name="name" data-bind="value: item.name" style="width: 100%">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Phone</label>
                                        <div class="col-xs-8">
                                            <input class="k-textbox" name="phone" data-bind="value: item.phone" style="width: 100%">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Email</label>
                                        <div class="col-xs-8">
                                            <input class="k-textbox" name="email" data-bind="value: item.email" style="width: 100%">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Person ID</label>
                                        <div class="col-xs-8">
                                            <input class="k-textbox" name="id_number" data-bind="value: item.id_number" style="width: 100%">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">CIF</label>
                                        <div class="col-xs-8">
                                            <input class="k-textbox" name="cif" data-bind="value: item.cif" style="width: 100%">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Address</label>
                                        <div class="col-xs-8">
                                            <input class="k-textbox" name="address" data-bind="value: item.address" style="width: 100%">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row title-row">
                                <span class="text-primary">CALL RESULT</span>
                                <hr class="popup">
                            </div>
                            <div class="row form-horizontal">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Note</label>
                                        <div class="col-xs-8">
                                            <input class="k-textbox" name="note" data-bind="value: item.note" style="width: 100%">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
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
                                </div>
                            </div>
                            <div class="row title-row">
                                <span class="text-primary">RECALL</span>
                                <hr class="popup">
                            </div>
                            <div class="row form-horizontal">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="control-label col-xs-4" style="padding-top: 2px">
	                                        <input type="checkbox" data-bind="checked: followUpChecked">
	                                        <span>ReCall</span>
	                                    </label>
	                                    <div class="col-xs-8">
	                                        <input data-role="datetimepicker" data-date-input="true" data-format="dd/MM/yyyy H:mm" data-bind="value: followUp.reCall, visible: followUpChecked" style="width: 100%">
	                                    </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Recall reason</label>
                                        <div class="col-xs-8">
                                            <input class="k-textbox" name="result" data-bind="value: call.result" style="width: 100%">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row text-center">
                                <button data-role="button" data-icon="save" data-bind="click: save">SAVE</button>
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
            notification.show("Something is wrong", "danger");
            return;
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
    }
})
window.popupObservable.init();
</script>

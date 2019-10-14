<div id="all-popup">
    <div id="popup-window" data-role="window"
                     data-title="@Call Default@"
                     data-width="1000"
                     data-actions="['Arrows-no-change','Save','Pause','Refresh', 'Minimize', 'Maximize', 'Close']"
                     data-position="{'top': 20}"
                     data-visible="false"
                     data-bind="events: {open: openPopup, close: closePopup, activate: activatePopup}" style="padding: 2px; max-height: 90vh">
        <div class="container-fluid" style="height: 85vh"> 
            <div class="row">
                <div id="popup-tabstrip" data-role="tabstrip" style="margin-top: 2px">
                    <ul>
                        <li class="k-state-active">
                            @FORM@
                        </li>
                        <li data-bind="visible: detailUrl, click: openDetail">
                            @CUSTOMER DETAIL@
                        </li>
                        <div class="pull-right">
                            <span data-bind="text: phone" style="font-size: 18px; vertical-align: 0" class="text-primary"></span>
                            <a data-role="button" data-bind="click: playRecording, visible: _dataCall.record_file_name" title="Recording" style="vertical-align: 2px">
                                <i class="fa fa-play"></i>
                            </a>
                        </div>
                    </ul>
                    <div>
                        <div class="container-fluid" style="margin-top: 5px">
                            <div class="row title-row">
                                <span class="text-primary">@CUSTOMER INFORMATION@ <b data-bind="visible: item.cif">(CORE BANKING)</b> <a data-role="button" style="font-size: 12px" data-bind="click: onSearchCustomer"><b data-bind="invisible: visibleSearchCustomer">@Search@</b><b data-bind="visible: visibleSearchCustomer">@Information@</b></a></span>
                                <hr class="popup">
                            </div>
                            <div class="row form-horizontal" data-bind="visible: visibleSearchCustomer">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">@Customer Name@</label>
                                        <div class="col-xs-8">
                                            <input class="k-textbox" data-bind="value: searchCustomer.customerName">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">CMND</label>
                                        <div class="col-xs-8">
                                            <input class="k-textbox" data-bind="value: searchCustomer.gttt">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">@Birthday@</label>
                                        <div class="col-xs-8">
                                            <input class="k-textbox" data-bind="value: searchCustomer.birthday">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">@Email@</label>
                                        <div class="col-xs-8">
                                            <input class="k-textbox" data-bind="value: searchCustomer.email">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">@Email@</label>
                                        <div class="col-xs-8">
                                            <input class="k-textbox" data-bind="value: searchCustomer.email">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">@Phone@</label>
                                        <div class="col-xs-8">
                                            <input class="k-textbox" data-bind="value: searchCustomer.mobile_no">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">CIF</label>
                                        <div class="col-xs-8">
                                            <input class="k-textbox" data-bind="value: searchCustomer.cif">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">@Customer account no@</label>
                                        <div class="col-xs-8">
                                            <input class="k-textbox" data-bind="value: searchCustomer.cust_ac_no">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-xs-8 col-xs-offset-4">
                                            <a data-role="button" data-bind="click: getSearchCustomer">@Search@</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div data-role="grid" id="searchCustomerGrid"
                                        data-pageable="{pageSize: 5}"
                                        data-columns="[
                                            {field:'CUSTOMER_NAME', title: '@Customer name@'},
                                            {field:'CUSTOMER_NO', title: 'CIF'},
                                            {field:'BIRTH_DAY', title: '@Birthday@'},
                                            {field:'ADDRESS', title: '@Address@'},
                                            {template: chooseSearchCustomerTemplate, title:'@Action@'}
                                            ]"
                                      data-bind="source: searchCustomerData"/>
                                </div>
                            </div>
                            <div class="row form-horizontal" data-bind="invisible: visibleSearchCustomer">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">@Customer Name@</label>
                                        <div class="col-xs-8">
                                            <span class="label label-info" data-bind="text: item.name, visible: item.cif" style="font-size: 14px; line-height: 27px"></span>
                                            <input class="k-textbox" data-bind="value: item.name, invisible: item.cif">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">@Birthday@</label>
                                        <div class="col-xs-8">
                                            <span data-bind="text: item.BIRTH_DAY, visible: item.cif" style="font-size: 14px; line-height: 27px"></span>
                                            <input class="k-textbox" data-bind="value: item.BIRTH_DAY, invisible: item.cif">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">@Email@</label>
                                        <div class="col-xs-8">
                                            <span data-bind="text: item.email, visible: item.cif" style="font-size: 14px; line-height: 27px"></span>
                                            <input class="k-textbox" data-bind="value: item.email, invisible: item.cif">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">@Phone@</label>
                                        <div class="col-xs-8">
                                            <span data-bind="text: item.phone, visible: item.cif" style="font-size: 14px; line-height: 27px"></span>
                                            <input class="k-textbox" data-bind="value: item.phone, invisible: item.cif">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">@Branchname@</label>
                                        <div class="col-xs-8">
                                            <span data-bind="text: item.BRANCH_NAME, visible: item.cif" style="font-size: 14px; line-height: 27px"></span>
                                            <input class="k-textbox" data-bind="value: item.BRANCH_NAME, invisible: item.cif">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="control-label col-xs-4" data-bind="text: item.UNIQUE_ID_NAME">CMND / HC</label>
                                        <div class="col-xs-8">
                                            <span data-bind="text: item.UNIQUE_ID_VALUE, visible: item.cif" style="font-size: 14px; line-height: 27px"></span>
                                            <input class="k-textbox" data-bind="value: item.UNIQUE_ID_VALUE, invisible: item.cif">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">CIF</label>
                                        <div class="col-xs-8">
                                            <span data-bind="text: item.cif" style="font-size: 14px; line-height: 27px"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">CIF @createdAt@</label>
                                        <div class="col-xs-8">
                                            <span data-bind="text: item.CIF_CREATION_DATE" style="font-size: 14px; line-height: 27px"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">@Address@</label>
                                        <div class="col-xs-8">
                                            <span data-bind="text: item.address, visible: item.cif" style="font-size: 14px; line-height: 27px"></span>
                                            <input class="k-textbox" data-bind="value: item.address, invisible: item.cif">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row title-row">
                                <span class="text-primary">CASE <a data-role="button" style="font-size: 12px" data-bind="click: onAddTicket"><b>@Add@ case</b></a></span>
                                <hr class="popup">
                            </div>
                            <div class="row form-horizontal">
                                <div class="col-sm-12" data-bind="invisible: visibleAddTicket">
                                    <div data-role="grid"
                                        data-pageable="{refresh: true}"
                                        data-columns="[
                                            {field:'code', title: '@Case code@'},
                                            {field:'service', title: '@Service@'},
                                            {field:'status', title: '@Status@'},
                                            {field:'customer_type', title: '@Customer type@'},
                                            {field:'contact_channel', title: '@Contact_channel@'},
                                            {field:'createdBy', title: '@Created by@'},
                                            {field:'createdAtText', title: '@Created at@'}
                                            ]"
                                      data-bind="source: caseData"/>
                                </div>
                                <div class="col-sm-12" id="add-ticket-content" data-bind="visible: visibleAddTicket">
                                </div>
                            </div>
                            <div class="row title-row">
                                <span class="text-primary">@CALL RESULT@</span>
                                <hr class="popup">
                            </div>
                            <div class="row form-horizontal">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Service level 1</label>
                                        <div class="col-xs-8">
                                            <input data-role="dropdownlist" name="serviceLv1"
                                                required validationMessage="Empty!!!"
                                                data-filter="contains"
                                                data-value-primitive="true"
                                                data-text-field="name"
                                                data-value-field="name"                  
                                                data-bind="value: call.serviceLv1, source: serviceLv1Option, events: {cascade: topicLv1Change}" 
                                                style="width: 100%"/>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Service level 2</label>
                                        <div class="col-xs-8">
                                            <input data-role="dropdownlist" name="serviceLv2"
                                                required validationMessage="Empty!!!"
                                                data-filter="contains"
                                                data-value-primitive="true"
                                                data-text-field="name"
                                                data-value-field="name"                  
                                                data-bind="value: call.serviceLv2, source: serviceLv2Option, events: {cascade: topicLv2Change}" 
                                                style="width: 100%"/>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Service level 3</label>
                                        <div class="col-xs-8">
                                            <input data-role="dropdownlist" name="serviceLv3"
                                                required validationMessage="Empty!!!"
                                                data-filter="contains"
                                                data-value-primitive="true"
                                                data-text-field="name"
                                                data-value-field="name"                  
                                                data-bind="value: call.serviceLv3, source: serviceLv3Option" 
                                                style="width: 100%"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">@Note@</label>
                                        <div class="col-xs-8">
                                            <input class="k-textbox" name="note" data-bind="value: call.note" style="width: 100%">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">@Result@</label>
                                        <div class="col-xs-8">
                                            <input data-role="dropdownlist" name="result"
                                                required validationMessage="Empty!!!"
                                                data-value-primitive="true"
                                                data-text-field="text"
                                                data-value-field="value"                  
                                                data-bind="value: call.result, source: callResultOption" 
                                                style="width: 100%"/>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4" style="padding-top: 2px">
                                            <input type="checkbox" data-bind="checked: item.followUpChecked">
                                            <span>@ReCall@</span>
                                        </label>
                                        <div class="col-xs-8">
                                            <input data-role="datetimepicker" data-date-input="true" data-format="dd/MM/yyyy H:mm" data-bind="value: followUp.reCall, visible: item.followUpChecked" style="width: 100%">
                                        </div>
                                    </div>
                                    <div class="form-group" data-bind="visible: item.followUpChecked">
                                        <label class="control-label col-xs-4">@Recall reason@</label>
                                        <div class="col-xs-8">
                                            <input class="k-textbox" name="reCallReason" data-bind="value: followUp.reCallReason" style="width: 100%">
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
function chooseSearchCustomer(ele) {
    var cif = $(ele).data("cif");
    swal({
        title: "@Are you sure@?",
        text: `@Change subject of call to this customer@.`,
        icon: "warning",
        buttons: true,
        dangerMode: false,
    })
    .then((sure) => {
        if(sure) {
            $("#popup-window").data("kendoWindow").close();
            $.ajax({
                url: ENV.vApi + "cdr/update/" + callData.calluuid,
                type: "PUT",
                contentType: "application/json; charset=utf-8",
                data: JSON.stringify({dialid: cif, dialtype: "cif"}),
                success: (response) => {
                    rePopup(callData.calluuid);
                },
                error: errorDataSource
            })
        }
    });
}

function chooseSearchCustomerTemplate(data) {
    return `<button class="k-button" data-cif="${data.cif}" onclick="chooseSearchCustomer(this)">@Choose@</button>`;
}

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

class defaultCifPopup extends Popup {
    constructor(dataCall) {
        super(dataCall);
        Object.assign(this, {
            _fieldId : "dialid",
            _popupType: "default",
            call: dataCall,
            phone: dataCall.customernumber,
            openDetail: function(e) {
                var $content = $("#customer-detail-content");
                if(!$content.find("iframe").length)
                    $content.append(`<iframe src="${this.detailUrl}"" style="width: 100%; height: 70vh; border: 0"></iframe>`);
            }
        });
        return this;
    }

    async init() {
        var fieldIdValue = this._dataCall[this._fieldId];

        var responseObj = await $.get(ENV.namaApi + `core/getInfoCustomer`, {_: Date.now(), q: JSON.stringify({cif: fieldIdValue})});

        if(!responseObj) {
            notification.show("Something is wrong", "danger");
            return;
        }

        if(!responseObj.status) {
            notification.show(responseObj.message, "danger");
            return;
        }

        if(!responseObj.total) {
            this.item.phone = this._dataCall.customernumber;
            var customer4x = await $.get(ENV.vApi + `popup/get_customer_by_cif_or_phone`, {_: Date.now(), cif: this.item.cif, phone: this.item.phone});
            var detailUrl = customer4x ? `${ENV.baseUrl}manage/customer?omc=1#/detail/${customer4x.id}` : ``;
            this.item.id = customer4x ? customer4x.id : null;
            this.assign({detailUrl: detailUrl}).open();
        } else if(responseObj.total == 1) {
            this.item = responseObj.data[0];
            var customer4x = await $.get(ENV.vApi + `popup/get_customer_by_cif_or_phone`, {_: Date.now(), cif: this.item.cif, phone: this.item.phone});
            var detailUrl = customer4x ? `${ENV.baseUrl}manage/customer?omc=1#/detail/${customer4x.id}` : ``;
            this.item.id = customer4x ? customer4x.id : null;
            this.assign({detailUrl: detailUrl}).open();
        } else {
            var buttons = {cancel: true};
            for (var i = 0; i < responseObj.total; i++) {
                buttons[i] = {text: responseObj.data[i].name};
            }
            var type = swal({
                title: "Choose one.",
                text: `Greater than one customer have this number.`,
                icon: "warning",
                buttons: buttons
            }).then(async index => {
                if(index !== null && index !== false) {
                    this.item = responseObj.data[index];
                    var customer4x = await $.get(ENV.vApi + `popup/get_customer_by_cif_or_phone`, {_: Date.now(), cif: this.item.cif, phone: this.item.phone});
                    var detailUrl = customer4x ? `${ENV.baseUrl}manage/customer?omc=1#/detail/${customer4x.id}` : ``;
                    this.item.id = customer4x ? customer4x.id : null;
                    this.assign({detailUrl: detailUrl}).open();
                } else {
                    $("#popup-contain").empty();
                }
            })
        }
    }
}

var callData = <?= json_encode($callData) ?>;

window.popupObservable = new defaultCifPopup(callData);
window.popupObservable.assign({
    visibleSearchCustomer: false,
    searchCustomer: {},
    searchCustomerData: [],
    onSearchCustomer: function() {
        this.set("visibleSearchCustomer", !this.get("visibleSearchCustomer"));
    },
    getSearchCustomer: function() {
        notification.show("@Wait a minute@.");
        $.ajax({
            url: ENV.namaApi + "core/getInfoCustomer",
            data: {q: JSON.stringify(this.get("searchCustomer").toJSON())},
            success: response => {
                if(response.total)
                    this.set("searchCustomerData", response.data);
                else notification.show("@No data@", "error");
            }
        })
    },
    followUp: {},
    serviceLv1Option: dataSourceService(1),
    serviceLv2Option: [],
    serviceLv3Option: [],
    topicLv1Change: function(e) {
        if(e.sender.dataItem()) {
            var parent_id = e.sender.dataItem().id;
            this.set("call.serviceLv2", null);
            this.set("call.serviceLv3", null);
            this.set("serviceLv2Option", dataSourceService(2, parent_id));
            this.set("serviceLv3Option", []);
            $("input[name=serviceLv2]").data("kendoDropDownList").refresh();
            $("input[name=serviceLv3]").data("kendoDropDownList").refresh();
        }
    },
    topicLv2Change: function(e) {
        if(e.sender.dataItem()) {
            var parent_id = e.sender.dataItem().id;
            this.set("call.serviceLv3", null);
            this.set("serviceLv3Option", dataSourceService(3, parent_id));
            $("input[name=serviceLv3]").data("kendoDropDownList").refresh();
        }
    },
    onAddTicket: function(e) {
        this.set("visibleAddTicket", true);
        var $content = $("#add-ticket-content");
        if(!$content.find("iframe").length) {
            $content.append(`<iframe id="cep-iframe" src="${ENV.cepApi}RequestTicket/Create" style="width: 100%; height: 500px; border: 0; overflow-y: hidden"></iframe>`);
        }
    },
    caseData: new kendo.data.DataSource({
        serverFiltering: true,
        serverPaging: true,
        serverSorting: true,
        pageSize: 5,
        transport: {
            read: ENV.restApi + "cases",
            parameterMap: parameterMap
        },
        schema: {
            data: "data",
            total: "total",
            parse: function(response) {
                response.data.map(doc => {
                    doc.createdAtText = gridTimestamp(doc.createdAt);
                    doc.contact_channelArr = [];
                    if(doc.contacts) {
                        doc.contacts.forEach(contact => {
                            doc.contact_channelArr.push(contact.channel);
                        })
                        doc.contact_channel = doc.contact_channelArr.join(",");
                    }
                })
                return response
            }
        },
        error: errorDataSource
    }),
    callResultOption: dataSourceJsonData(["Call", "result"]),
    playRecording: function(e) {
        play(this._dataCall.calluuid);
    },
    save: function() {
        var kendoValidator = $("#popup-window").kendoValidator().data("kendoValidator");
            
        if(kendoValidator.validate()) {
            this.asyncSave();
        } else notification.show("@Your data is invalid@", "error");
    },
    asyncSave: async function() {
        var data = this.item.toJSON();
        $.ajax({
            url: ENV.restApi + "customer/" + (data.id || "").toString(),
            type: data.id ? "PUT" : "POST",
            contentType: "application/json; charset=utf-8",
            data: kendo.stringify(data),
            success: (response) => {
                syncDataSource();
                if(response.status && response.data && !this.item.id) {
                    this.item.id = response.data[0].id;
                    this.detailUrl = `${ENV.baseUrl}manage/customer?omc=1#/detail/${response.data[0].id}`;
                }
            },
            error: errorDataSource
        })
        var call = this.call.toJSON();
        $.ajax({
            url: ENV.vApi + "cdr/update/" + (this._dataCall.calluuid || "").toString(),
            type: "PUT",
            contentType: "application/json; charset=utf-8",
            data: kendo.stringify(call),
            success: (response) => {
                syncDataSource();
            },
            error: errorDataSource
        })
        var followUp = this.followUp.toJSON();
        if(followUp.reCall) {
            $.ajax({
                url: ENV.restApi + "follow_up",
                type: "POST",
                contentType: "application/json; charset=utf-8",
                data: kendo.stringify(Object.assign(data, followUp)),
                success: (response) => {
                    syncDataSource();
                },
                error: errorDataSource
            })
        }
    }
})
window.popupObservable.init();
</script>

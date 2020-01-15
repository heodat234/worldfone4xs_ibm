<div id="all-popup">
    <div id="popup-window" data-role="window"
                     data-title="POPUP LOAN WO"
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
                            <i class="fa fa-user"></i><b> @CUSTOMER INFO@</b>
                        </li>
                        <li data-bind="click: openNotes">
                            <i class="fa fa-sticky-note"></i><b> @Note@</b>
                        </li>
                        <li data-bind="click: openPaymentHistory">
                            <b> @PAYMENT HISTORY@</b>
                        </li>
                        <li data-bind="click: openFieldAction">
                            <b> @FIELD ACTION@</b>
                        </li>
                        <li data-bind="click: openLawSuit">
                            <b> @LAWSUIT@</b>
                        </li>
                        <li data-bind="click: openCrossSell">
                            <b> @CROSS-SELL@</b>
                        </li>
                        <li data-bind="click: openCdr">
                            <i class="fa fa-phone-square"></i><b> @CDR@</b>
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
                                            <span style="vertical-align: -7px">Write off</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-7">
                                    <div class="form-group">
                                        <label class="control-label col-xs-2">Tag</label>
                                        <div class="col-xs-10">
                                            <span class="label label-success" style="vertical-align: -7px" data-bind="text: diallist.name"></span>
                                            <span class="label label-info" style="vertical-align: -7px">#<b data-bind="text: item.index"></b></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row form-horizontal">
                                <div class="col-sm-5">
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">@Name@</label>
                                        <div class="col-xs-8">
                                            <span style="vertical-align: -7px" data-bind="text: item.CUS_NM"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">@Birthday@</label>
                                        <div class="col-xs-8">
                                            <span style="vertical-align: -7px" data-bind="text: item.cus_birthday"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">@Occupation@</label>
                                        <div class="col-xs-8">
                                            <span style="vertical-align: -7px" data-bind="text: item.WRK_PST"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">@Profession@</label>
                                        <div class="col-xs-8">
                                            <input data-role="dropdownlist" name="actionCode"
                                                data-filter="contains"
                                                data-value-primitive="true"
                                                data-text-field="text"
                                                data-value-field="value"                  
                                                data-bind="value: item.profession, source: professionOption" 
                                                style="width: 100%"/>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">@Nation ID@</label>
                                        <div class="col-xs-8">
                                            <span style="vertical-align: -7px" data-bind="text: item.LICNO"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-7">
                                    <div class="form-group">
                                        <label class="control-label col-xs-2">@Address@</label>
                                        <div class="col-xs-10">
                                            <span style="vertical-align: -7px" data-bind="text: item.address"></span>
                                        </div>
                                    </div>
                                    <div class="form-group" data-bind="visible: item.permanent_address">
                                        <label class="control-label col-xs-2">@Permanent Address@</label>
                                        <div class="col-xs-10">
                                            <span style="vertical-align: -7px" data-bind="text: item.permanent_address"></span>
                                        </div>
                                    </div>
                                    <div class="form-group" data-bind="visible: item.temp_address">
                                        <label class="control-label col-xs-2">@Temporary Address@</label>
                                        <div class="col-xs-10">
                                            <span style="vertical-align: -7px" data-bind="text: item.temp_address"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-2">@Main phone@</label>
                                        <div class="col-xs-2">
                                            <span style="vertical-align: -7px" data-bind="visible: item.phone">
                                                <i data-bind="text: item.phone"></i>
                                                <a href="javascript:void(0)" data-bind="click: callThisPhone, attr: {data-phone: item.phone}"><i class="fa fa-phone-square text-info"></i></a>
                                            </span>
                                        </div>
                                        <label class="control-label col-xs-2">@House@</label>
                                        <div class="col-xs-2">
                                            <span style="vertical-align: -7px" data-bind="visible: item.House_NO">
                                                <i data-bind="text: item.House_NO"></i>
                                                <a href="javascript:void(0)" data-bind="click: callThisPhone, visible: phone, attr: {data-phone: item.House_NO}"><i class="fa fa-phone-square text-info"></i></a>
                                            </span>
                                        </div>
                                        <label class="control-label col-xs-2">@Office@</label>
                                        <div class="col-xs-2">
                                            <span style="vertical-align: -7px" data-bind="visible: item.OFFICE_NO">
                                                <i data-bind="text: item.OFFICE_NO"></i>
                                                <a href="javascript:void(0)" data-bind="click: callThisPhone, visible: phone, attr: {data-phone: item.OFFICE_NO}"><i class="fa fa-phone-square text-info"></i></a>
                                            </span>
                                        </div>
                                    </div>
                                    <div data-template="relationship-template" data-bind="source: relationshipDataSource">
                                    </div>
                                    <div class="form-group">
                                        <div class="col-xs-2"></div>
                                        <div class="col-xs-10">
                                            <button data-role="button" data-icon="add" data-bind="click: addRef">@Add@</button>
                                            <button data-role="button" data-icon="save" data-bind="click: saveRef">@Save@</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row title-row wo-container">
                                <span class="text-primary">WRITE OFF</span>
                                <span data-bind="text: wotOptionLength"></span>
                                <hr class="popup">
                            </div>
                            <div class="row form-horizontal wo-container" id="collapseWoProduct">
                                <div class="row">
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label class="control-label col-xs-4">@Contract No.@ <span id="wo-count"></span></label>
                                            <div class="col-xs-8">
                                                <input id="wo-contract-no" data-role="dropdownlist" name="contractNo"
                                                data-value-primitive="true"
                                                data-text-field="account_number"
                                                data-value-field="account_number"                  
                                                data-bind="value: item.account_number, source: woOption, events: {cascade: woChange, dataBound: onDataBoundAccNo}" 
                                                style="width: 100%"/>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-xs-4">@CustID@</label>
                                            <div class="col-xs-8">
                                                <p class="form-control-static" data-bind="text: woProduct.CustID"></p>
                                            </div>
                                        </div>
                                        <div class="form-group" data-bind="visible: collapseMain">
                                            <label class="control-label col-xs-4">@LNTYP@</label>
                                            <div class="col-xs-8">
                                                <p class="form-control-static" data-format="n0" data-bind="text: woProduct.LNTYP"></p>
                                            </div>
                                        </div>
                                        <div class="form-group" data-bind="visible: collapseMain">
                                            <label class="control-label col-xs-4">@Amount@</label>
                                            <div class="col-xs-8">
                                                <p class="form-control-static" data-bind="text: woProduct.sum_amount"></p>
                                            </div>
                                        </div>
                                      
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label class="control-label col-xs-4">@WO9711@</label>
                                            <div class="col-xs-8">
                                                <p class="form-control-static" data-bind="text: woProduct.WO9711"></p>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-xs-4">@WO9712@</label>
                                            <div class="col-xs-8">
                                                <p class="form-control-static" data-bind="text: woProduct.WO9712"></p>
                                            </div>
                                        </div>
                                        <div class="form-group" data-bind="visible: collapseMain">
                                            <label class="control-label col-xs-4">@WO9713@</label>
                                            <div class="col-xs-8">
                                                <p class="form-control-static" data-bind="text: woProduct.WO9713"></p>
                                            </div>
                                        </div>
                                   
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group" >
                                            <label class="control-label col-xs-4">@PROD_ID@</label>
                                            <div class="col-xs-8">
                                                <p class="form-control-static" data-bind="text: woProduct.PROD_ID"></p>
                                            </div>
                                        </div>
                                        <div class="form-group" >
                                            <label class="control-label col-xs-4">@COLLATERAL@</label>
                                            <div class="col-xs-8">
                                                <p class="form-control-static" data-bind="text: woProduct.COLLATERAL"></p>
                                            </div>
                                        </div>
                                        <div class="form-group" data-bind="visible: collapseMain">
                                            <label class="control-label col-xs-4">@NGAY QUA HAN@</label>
                                            <div class="col-xs-8">
                                                <p class="form-control-static" data-bind="text: woProduct.NGAY_QUA_HAN"></p>
                                            </div>
                                        </div>
                                    
                                    </div>
                                </div>
                                <div class="row text-center" style="position: absolute; bottom: 0; left: 50%"> 
                                    <div class="col-sm-12"><a href="#" data-bind="click: collapseWoProduct, text:btnCollapseWO "></a></div>
                                </div>
                            </div>
                        
                            <div class="row title-row">
                                <span class="text-primary">@CALL RESULT@</span>
                                <hr class="popup">
                            </div>
                            <div class="row text-center" style="margin-bottom: 10px" data-template="action-code-template" data-bind="source: actionCodeData"></div>
                            <div class="row form-horizontal">
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">@Debt account@</label>
                                        <div class="col-xs-8">
                                            <input data-role="dropdownlist" id="debt-account-select"
                                                name="debtAccount"
                                                required validationMessage="Empty!!!" 
                                                data-value-primitive="true"
                                                data-text-field="value"
                                                data-value-field="value"               
                                                data-bind="value: call.debt_account, source: debtAccountOption, events: {dataBound: onDataBoundDebtAcc, cascade: debtAccountCascade}" 
                                                style="width: 100%"/>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4" style="padding-top: 2px">
                                            <input type="checkbox" data-bind="checked: followUpChecked">
                                            <span>@Requeue@</span>
                                        </label>
                                        <div class="col-xs-8">
                                            <input data-role="datetimepicker" data-date-input="true" data-format="dd/MM/yyyy H:mm" data-bind="value: followUp.reCall, visible: followUpChecked" style="width: 100%">
                                        </div>
                                    </div>
                                    <div class="form-group" data-bind="visible: followUpChecked">
                                        <label class="control-label col-xs-4">@Recall reason@</label>
                                        <div class="col-xs-8">
                                            <input class="k-textbox" name="reCallReason" data-bind="value: followUp.reCallReason" style="width: 100%">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">@Action code@</label>
                                        <div class="col-xs-8">
                                            <input data-role="dropdownlist" name="actionCode"
                                                required validationMessage="Empty!!!"
                                                data-filter="contains"
                                                data-value-primitive="true"
                                                data-text-field="value"
                                                data-value-field="value"                  
                                                data-bind="value: call.action_code, source: actionCodeOption, events: {change: actionCodeChange}" 
                                                style="width: 100%"/>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label style="line-height: 1.5">
                                            <span>SECURED ASSET</span>
                                            <input class="custom-checkbox" type="checkbox" data-bind="checked: item.secured_asset">
                                            <span></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">@Note@</label>
                                        <div class="col-xs-8">
                                            <textarea class="k-textbox" name="note" data-bind="value: note" style="width: 100%"></textarea> 
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row title-row" data-bind="visible: visibleFillingForm">
                                <span class="text-primary">@FILLING FORM@</span>
                                <hr class="popup">
                            </div>
                            <div class="row form-horizontal" id="filling-form" data-bind="visible: visibleFillingForm">
                            </div>
                            <div class="row text-center" data-bind="visible: call.action_code">
                                <button data-role="button" data-icon="save" data-bind="click: save">@Save@ (@Press@ Enter @order to@ @save@)</button>
                            </div>
                        </div>
                    </div>
                    <div style="padding: 0; overflow-x: hidden; overflow-y: hidden; min-height: 100%" id="note-content"></div>
                    <div style="padding: 0; overflow-x: hidden; overflow-y: hidden; min-height: 100%" id="payment_history-content"></div>
                    <div style="padding: 0; overflow-x: hidden; overflow-y: hidden; min-height: 100%" id="field_action-content"></div>
                    <div style="padding: 0; overflow-x: hidden; overflow-y: hidden; min-height: 100%" id="lawsuit-content"></div>
                    <div style="padding: 0; overflow-x: hidden; overflow-y: hidden; min-height: 100%" id="cross_sell-content"></div>
                    <div style="padding: 0; overflow-x: hidden; overflow-y: hidden; min-height: 100%" id="cdr-content"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/x-kendo-template" id="relationship-template">
    <div class="form-group">
        <label class="control-label col-xs-2">REF <span data-bind="text: index"></span></label>
        <div class="col-xs-10">
            <input class="k-textbox" data-bind="value: relation" style="width: 20%">
            <span>@Name@</span>
            <input class="k-textbox" data-bind="value: name" style="width: 30%">
            <span>@Phone@</span>
            <input class="k-textbox" data-bind="value: phone" style="width: 20%">
            <a class="k-button" data-bind="click: callThisPhone, visible: phone, attr: {data-phone: phone}"><i class="fa fa-phone-square text-info"></i></a>
        </div>
    </div>
</script>
<script type="text/javascript">
class diallistPopupManual extends Popup {
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
        var responseObj = await $.get(ENV.restApi + `diallist_detail/${fieldIdValue}`);

        if(!responseObj || typeof responseObj != "object") {
            responseObj = {};
            notification.show("Data is not found", "error");
            return;
        }
        this.item = responseObj;
        if(responseObj.debt_account) this.call.debt_account = responseObj.debt_account;
        if(responseObj.action_code) this.call.action_code = responseObj.action_code;

        if(responseObj.LICNO) {
            this.relationshipDataSource = new kendo.data.DataSource({
                serverFiltering: true,
                filter: {field: "LIC_NO", operator: "eq", value: responseObj.LIC_NO},
                transport: {
                    read: ENV.restApi + "relationship",
                    create: {
                        url: ENV.restApi + "relationship",
                        type: "POST",
                        contentType: "application/json; charset=utf-8"
                    },
                    parameterMap: parameterMap
                },
                schema: {
                    data: "data",
                    total: "total",
                    model: {
                        id: "id"
                    },
                    parse: function(res) {
                        res.data.map((doc, idx) => {
                            doc.index = idx + 1;
                        })
                        return res;
                    }
                }
            });
            this.woOption = new kendo.data.DataSource({
                serverFiltering: true,
                filter: {field: "LIC_NO", operator: "eq", value: responseObj.LICNO},
                transport: {
                    read: ENV.restApi + "wo",
                    parameterMap: parameterMap
                },
                schema: {
                    data: "data",
                    total: "total",
                    parse: function(res) { console.log(1111)
                        res.data.map(doc => {
                            addNewDebtAccount("#debt-account-select", doc.account_number, "wo");
                        })
                        
                        $("#wo-count").html('<span class="text-danger">(' + res.total + ')</span>');
                        if(!res.total) $(".wo-container").addClass("hidden");
                        return res;
                    }
                }
            });

           
        }console.log(111)
        
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

window.popupObservable = new diallistPopupManual(callData);

window.popupObservable.assign({
    diallist: <?= $diallist ? json_encode($diallist) : "{}" ?>,
    followUp: {},
    woProduct: {},
    card: {},
    call: {},
    action: {},
    collapseMain: false,
    collapseCard: false,
    reason_nonpayment_note: false,
    btnCollapseWO: "@See more@",
    btnCollapseCard: "@See more@",
    actionCodeOption: dataSourceJsonData(["Call", "result"]),
    actionCodeChange: function(e) {
        this.actionCodeChangeAsync(e);
        document.onkeydown = (evt) => {
            evt = evt || window.event;
            if (evt.keyCode == 13) {
                this.save();
                document.onkeydown = null;
            }
        };
    },
    actionCodeChangeAsync: async function(e) {
        var actionType = e.sender.dataItem().type;
        switch(actionType) {
            case "1": default:
                this.set("visibleFillingForm", true);
                var HTML = await $.get(ENV.templateApi + "action_code/type" + actionType);
                var kendoView = new kendo.View(HTML, { model: this, template: false, wrap: false });
                $("#filling-form").html(kendoView.render());
                break;
            case "2":
                this.set("visibleFillingForm", false);
                break;
            case "3":
                this.set("visibleFillingForm", true);
                var HTML = await $.get(ENV.templateApi + "action_code/type" + actionType);
                var kendoView = new kendo.View(HTML, { model: this, template: false, wrap: false });
                $("#filling-form").html(kendoView.render());
                break;
            case "4":
            var requeuefield = (typeof e.sender.dataItem().requeuefield != 'undefined' && e.sender.dataItem().requeuefield != '') ? e.sender.dataItem().requeuefield : '';
                this.set("visibleFillingForm", true);
                var HTML = await $.get(ENV.templateApi + "action_code/type" + actionType);
                var kendoView = new kendo.View(HTML, { model: this, template: false, wrap: false });
                $("#filling-form").html(kendoView.render());
                break;
            case "5":
                this.set("visibleFillingForm", true);
                var HTML = await $.get(ENV.templateApi + "action_code/type" + actionType);
                var kendoView = new kendo.View(HTML, { model: this, template: false, wrap: false });
                $("#filling-form").html(kendoView.render());
                break;
            case "6":
                this.set("visibleFillingForm", true);
                var HTML = await $.get(ENV.templateApi + "action_code/type" + actionType);
                var kendoView = new kendo.View(HTML, { model: this, template: false, wrap: false });
                $("#filling-form").html(kendoView.render());
                break;
            case "7":
                this.set("visibleFillingForm", true);
                var HTML = await $.get(ENV.templateApi + "action_code/type" + actionType);
                var kendoView = new kendo.View(HTML, { model: this, template: false, wrap: false });
                $("#filling-form").html(kendoView.render());
                break;
        }

        var requeuenumdate = parseInt(e.sender.dataItem().requeuenumdate);
        this.set('followUpChecked', true);
        if(requeuenumdate != 0) {
            recallDate = addSecondToDate(new Date(), requeuenumdate);
            this.set('followUp.reCall', recallDate);
        }

        if(requeuenumdate == 0) {
            this.set('followUpChecked', false);
        }
    },
    professionOption: dataSourceJsonData(["Customer", "profession"]),
    woChange: function(e) {
        this.set("woProduct", e.sender.dataItem());
    },

    raaStatusOption: dataSourceJsonData(["RAA", "status"]),
    callThisPhone: function(e) {
        let diallistDetailId = this._dataCall[this._fieldId];
        let phone = $(e.currentTarget).data("phone");
        startPopup({dialid:diallistDetailId,customernumber:phone,dialtype:"manual",direction:"outbound"})
        makeCall(phone, diallistDetailId, "manual");
    },
    actionCodeData: [],
    playRecording: function(e) {
        play(this._dataCall.calluuid);
    },
    save: function() {

        swal({
            title: `${NOTIFICATION.checkSure}?`,
            text: "@Save@ @this form@",
            icon: "warning",
            buttons: true,
            dangerMode: false,
        })
        .then((sure) => {

            var kendoValidator = $("#popup-window").kendoValidator().data("kendoValidator");
                
            if(!kendoValidator.validate()) {
                notification.show("@Your data is invalid@", "error");
                return;
            }

            var data = this.get("item").toJSON();
            var call = this.get("call").toJSON();

            data = Object.assign(data, call);

            if(data.cus_name) data.name = data.cus_name;

            if(this.followUpChecked) {
                var followUp = this.get("followUp").toJSON();
                data = Object.assign(data, followUp);

                var followUpData = Object.assign(followUp, {
                    name: data.name,
                    phone: this.get("phone"),
                    account_number: this.get("call.debt_account"),
                    id: data.id,
                    collection: "Diallist_detail"
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

            $.ajax({
                url: ENV.vApi + "diallist_detail/update/" + (data.id || ""),
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
                url: ENV.vApi + "customer/upsert/LIC_NO/" + data.LIC_NO,
                type: "POST",
                contentType: "application/json; charset=utf-8",
                data: kendo.stringify(data),
                error: errorDataSource,
                success: res => {
                    if(res.status) {
                        var customer = res.data[0];
                    }
                }
            })

            $.ajax({
                url: ENV.vApi + "cdr/update/" + window.popupObservable.dataCall.calluuid,
                type: "PUT",
                contentType: "application/json; charset=utf-8",
                data: kendo.stringify(Object.assign(this.get("call").toJSON(), {customer: data})),
                error: errorDataSource
            })

            if(this.get("note")) {
                $.ajax({
                    url: ENV.restApi + "note",
                    type: "POST",
                    contentType: "application/json; charset=utf-8",
                    data: kendo.stringify({
                        from: "Diallist_detail",
                        foreign_id: data.LIC_NO,
                        content: this.get("note"),
                    }),
                    error: errorDataSource
                })
            }

            this.relationshipDataSource.sync();
            // Change status to ready
            changeStatus(1);

            var data_action_code                = this.get("action");
            data_action_code['calluuid']        = window.popupObservable.dataCall.calluuid;
            data_action_code['LIC_NO']          = this.get("item.LIC_NO");
            data_action_code['account_number']  = this.get("call.debt_account");
            data_action_code['action_code']     = this.get("call.action_code");
            data_action_code['account_type']    = this.get("call.account_type");
            data_action_code['note']            = this.get("note");

            $.ajax({
                url: ENV.restApi + "action_code",
                type: "POST",
                contentType: "application/json; charset=utf-8",
                data: kendo.stringify(data_action_code),
                success: (response) => {
                    if(response.status) {
                        syncDataSource();
                        let actionCodeData = this.get("actionCodeData") || [];
                        actionCodeData.push(data_action_code);
                        this.set("actionCodeData", actionCodeData);
                    }
                },
                error: errorDataSource
            })

            this.closePopup();
        })
    },
    collapseWoProduct: function() {
        if(this.collapseMain){
            this.set("btnCollapseWO", "@See more@");
            this.set("collapseMain", false);
        }else{
            this.set("btnCollapseWO", "@Collapse@");
            this.set("collapseMain", true);
        }
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

    openNotes: function(e) {
        var filter = JSON.stringify({
            logic: "and",
            filters: [
                {field: "foreign_id", operator: "eq", value: this.get("item.LIC_NO")}
            ]
        });
        var query = httpBuildQuery({filter: filter, omc: 1});
        var $content = $("#note-content");
        if(!$content.find("iframe").length)
            $content.append(`<iframe src='${ENV.baseUrl}manage/data/note?${query}' style="width: 100%; height: 500px; border: 0"></iframe>`);
    },

    openPaymentHistory: function(e) { 
        var filter = JSON.stringify({
            logic: "and",
            filters: [
                {field: "account_number", operator: "eq", value: this.item.account_number}
            ]
        });
        var query = httpBuildQuery({filter: filter, omc: 1});
        var $content = $("#payment_history-content");
        if(!$content.find("iframe").length)
            $content.append(`<iframe src='${ENV.baseUrl}manage/data/payment_history?${query}' style="width: 100%; height: 500px; border: 0"></iframe>`);
    },

    openFieldAction: function(e) { 
        var filter = JSON.stringify({
            logic: "and",
            filters: [
                {field: "contract_no", operator: "eq", value: this.item.account_number}
            ]
        });
        var query = httpBuildQuery({filter: filter, omc: 1});
        var $content = $("#field_action-content");
        if(!$content.find("iframe").length)
            $content.append(`<iframe src='${ENV.baseUrl}manage/data/field_action?${query}' style="width: 100%; height: 500px; border: 0"></iframe>`);
    },

    openLawSuit: function(e) { 
        var filter = JSON.stringify({
            logic: "and",
            filters: [
                {field: "contract_no", operator: "eq", value: this.item.account_number}
            ]
        });
        var query = httpBuildQuery({filter: filter, omc: 1});
        var $content = $("#lawsuit-content");
        if(!$content.find("iframe").length)
            $content.append(`<iframe src='${ENV.baseUrl}manage/data/lawsuit_history?${query}' style="width: 100%; height: 500px; border: 0"></iframe>`);
    },

    openCrossSell: function(e) { 
        var filter = JSON.stringify({
            logic: "and",
            filters: [
                {field: "LIC_NO", operator: "eq", value: this.item.LICNO}
            ]
        });
        var query = httpBuildQuery({filter: filter, omc: 1});
        var $content = $("#cross_sell-content");
        if(!$content.find("iframe").length)
            $content.append(`<iframe src='${ENV.baseUrl}manage/data/cross_sell?${query}' style="width: 100%; height: 500px; border: 0"></iframe>`);
    },

    onChangePromiseDate: function(e) {
        var value = this.get('item.promised_date');
        this.set('followUp.reCall', value);
    },

    nonePaymentOption: dataSourceJsonData(["Actioncode", "reasonnonpayment"]),

    onChangeReasonNonePayment: function(e) {
        if(this.get('item.reason_nonpayment') == 'others') {
            this.set('reason_nonpayment_note', true);
        }
        else {
            this.set('reason_nonpayment_note', false);
        }
    },

    onDataBoundDebtAcc: function(e) {
        var debtAcc = $("#debt-account-select").data("kendoDropDownList");
        var debtAccDB = debtAcc.dataSource.data();
        if(debtAccDB.length > 0) {
            debtAcc.select(0);
            var dataItem = debtAcc.dataItem(0);
            this.set('call.debt_account', dataItem['value']);
        }
    },

    debtAccountCascade: function(e) {
        this.set("call.account_type", e.sender.dataItem().type);
    },

    onDataBoundContractCard: function(e) {
        var cardContractNo = $("#card-contract-no").data("kendoDropDownList");
        if(typeof cardContractNo != 'undefined'){
            var contractNoDB = cardContractNo.dataSource.data();
            if(contractNoDB.length > 0) {
                cardContractNo.select(0);
            }
        }
    },

    onDataBoundAccNo: function(e) {
        var mainContractNo = $("#wo-contract-no").data("kendoDropDownList");
        if(typeof mainContractNo != 'undefined'){
            var mainContractNoDB = mainContractNo.dataSource.data();
            if(mainContractNoDB.length > 0) {
                mainContractNo.select(0);
            }
        }
    },

    addRef: function(e) {
        if(typeof this.relationshipDataSource != 'undefined') {
            this.relationshipDataSource.insert(this.relationshipDataSource.total(), {index: this.relationshipDataSource.total() + 1, LIC_NO: this.get("item.LIC_NO"), phone: ""});
        }
        
    },

    saveRef: function(e) {
        this.relationshipDataSource.sync();
    }
});

window.popupObservable.init();

function addNewDebtAccount(widgetId, value, type = "SIBS") {
    var widget = $(widgetId).getKendoDropDownList();
    if(widget) {
        var dataSource = widget.dataSource;
        dataSource.add({value: value, type: type});
    }
};

function addSecondToDate(dateNeedSet, seconds) {
    dateNeedSet.setSeconds( dateNeedSet.getSeconds() + seconds );
    return dateNeedSet;
}
</script>

<script id="action-code-template" type="text/x-kendo-template">
    <span class="label label-success">
        <i data-bind="text: account_number"></i>: <b data-bind="text: action_code"></b>
    </span>
</script>

<style type="text/css">
    .form-group {
        margin-bottom: 0;
    }
    #collapseWoProduct{
        position: relative;
    }

</style>
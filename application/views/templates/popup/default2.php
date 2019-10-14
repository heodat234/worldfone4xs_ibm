<div id="allPopup">
    <div id="window" data-role="window"
                     data-title="Call Default"
                     data-width="1000"
                     data-actions="['Pause', 'Refresh', 'Minimize', 'Maximize', 'Close']"
                     data-position="{'top': 20}"
                     data-visible="false"
                     data-bind="events: {open: openPopup, close: closePopup}" style="padding: 2px; max-height: 90vh">
        <div class="container-fluid">
            <div class="row">
                <div id="popup-tabstrip" data-role="tabstrip" style="margin-top: 2px">
                    <ul>
                        <li class="k-state-active">
                            BASIC INFORMATION
                        </li>
                        <div class="pull-right">
                            <div style="display: inline-block; width: 100px"></div>
                            <a href="javascript:void(0)" data-bind="text: phone, click: openCustomerDetail" style="font-size: 18px; vertical-align: -2px"></a> 
                            <span id="timePopup" data-status="run" class="label label-default" data-bind="text: calltimeText" style="font-size: 14px"></span>
                            <span id="statusPopup" class="text-default"></span>
                            <div class="dropdown" style="display: inline-block;">
                                <div class="btn-group">
                                    <button class="k-button dropdown-toggle" type="button" data-toggle="dropdown">Search
                                    <span class="caret"></span></button>
                                    <ul class="dropdown-menu dropdown-custom dropdown-menu-right">
                                        <a target="_blank" href="#"><li style="display: block">Customer</li></a>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </ul>
                    <div>
                        <div class="container-fluid">
                            <div class="row title-row">
                                <span class="text-primary">CUSTOMER INFORMATION</span>
                                <hr>
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
                                        <label class="control-label col-xs-4">Phone Number</label>
                                        <div class="col-xs-8">
                                            <select data-role="multiselect" name="phone"
                                                data-value-primitive="true"                  
                                                data-bind="value: item.phone, source: item.phone, events: {open: phoneOpen}" 
                                                style="width: 100%"></select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Phone</label>
                                        <div class="col-xs-8">
                                            <input class="k-textbox" name="phone" data-bind="value: item.phone" style="width: 100%">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">DOB</label>
                                        <div class="col-xs-8">
                                            <input class="k-textbox" name="dob" data-bind="value: item.dob" style="width: 100%">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Gender</label>
                                        <div class="col-xs-8">
                                            <input class="k-textbox" name="gender" data-bind="value: item.gender" style="width: 100%">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Permanent address</label>
                                        <div class="col-xs-8">
                                            <input class="k-textbox" name="permanent_address" data-bind="value: item.permanent_address" style="width: 100%">
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
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Current Account</label>
                                        <div class="col-xs-8">
                                            <input class="k-textbox" name="current_account" data-bind="value: item.current_account" style="width: 100%">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">EMI</label>
                                        <div class="col-xs-8">
                                            <input data-role="numerictextbox" data-format="n0" name="emi_amt" data-bind="value: item.emi_amt" style="width: 100%">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Loan account&nbsp;
                                            <i class="fa fa-info-circle text-info" data-role="tooltip" title="Loan account number"></i>
                                        </label>
                                        <div class="col-xs-8">
                                            <input class="k-textbox" name="loan_account_num" data-bind="value: item.loan_account_num" style="width: 100%">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Disbursement date</label>
                                        <div class="col-xs-8">
                                            <input class="k-textbox"  name="disbursement_date" data-bind="value: item.disbursement_date" style="width: 100%">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row title-row">
                                <span class="text-primary">CALL RESULT</span>
                                <hr>
                            </div>
                            <div class="row form-horizontal">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Comment</label>
                                        <div class="col-xs-8">
                                            <input class="k-textbox" name="name" data-bind="value: item.comment" style="width: 100%">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Result</label>
                                        <div class="col-xs-8">
                                            <input data-role="dropdownlist" name="callCode"
                                                data-filter="contains"
                                                data-value-primitive="true"
                                                data-text-field="text"
                                                data-value-field="value"                     
                                                data-bind="value: item.callCode, source: callCodeOption, events: {change: callCodeChange}" style="width: 100%"/>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Receive call</label>
                                        <div class="col-xs-8">
                                            <input data-role="dropdownlist" name="callCode"
                                                data-filter="contains"
                                                data-value-primitive="true"                     
                                                data-bind="value: item.receiveCall, source: receiveCallOption" style="width: 100%"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="control-label col-xs-4" style="padding-top: 2px">
                                            <input type="checkbox" data-bind="checked: item.followUp, events: {change: followUpChange}">
                                            <span>ReCall</span>
                                        </label>
                                        <div class="col-xs-8">
                                            <input data-role="datetimepicker" data-date-input="true" data-format="dd/MM/yyyy H:mm" data-bind="value: item.re_call, visible: item.followUp" style="width: 100%">
                                        </div>
                                    </div>
                                    <div class="form-group" data-bind="visible: item.followUp">
                                        <label class="control-label col-xs-4">ReCall Content</label>
                                        <div class="col-xs-8">
                                            <input class="k-textbox" name="re_call_content" data-bind="value: item.re_call_content" style="width: 100%">
                                        </div>
                                    </div>                  
                                </div>
                            </div>
                            <div class="row">
                                <div class="text-center col-xs-12" style="margin-top: 10px; margin-bottom: 5px">
                                    <button data-role="button" data-icon="save" data-bind="click: save">SAVE</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
function createTimeTemplate(data) {
    return kendo.toString(new Date(data.create_time * 1000), "dd/MM/yyyy H:mm");
}


function dataSourceTemplate(type = 'email') {
    return new kendo.data.DataSource({
        transport: {
            read: ENV.baseUrl + "api/data/templateData/" + type
        }
    })
}

var getDataPopup = window.getDataPopup = <?= json_encode($get) ?>,
    date =  new Date();
    
var startPopup = async function () {
    var response = await $.ajax({
            url: ENV.vApi + "popup/get_customer_by_phone",
            data: {
                phone: getDataPopup.customernumber
            }
        });
        customer = response ? response.item : {phone: [getDataPopup.customernumber]};
        customer.followUp = customer.followUp ? true : false;
        customer.re_call = customer.re_call ? new Date(customer.re_call * 1000) : null;

    var observable = window.popupObservable = kendo.observable({
        trueVar: true,
        onProcess: true,
        phone: getDataPopup.customernumber,
        followUpChange: function(e) {
            if(e.currentTarget.checked) {
                var d = new Date(date.getTime() + 3600000);
                this.set("item.re_call", d);
            }
        },
        phoneOpen: function(e) {
            e.preventDefault();
            var widget = e.sender;
            widget.input[0].onkeyup = function(ev) {
                if(ev.keyCode == 13) {
                    var values = widget.value();
                    values.push(this.value);
                    widget.dataSource.data(values);
                    widget.value(values);
                    widget.trigger("change");
                }
            }
        },
        openPopup: function(e) {
            e.sender.wrapper.css({ top: 20 });
            flag_popup = true;
            if(typeof tabTitle != 'undefined') tabTitle(`POPUP (${getDataPopup.direction})`,  "/public/stel/img/call.ico");
            e.sender.wrapper.find(".k-i-refresh").parent("a").click(function(ev){
                ev.preventDefault();
                e.sender.close();
                refreshPopup(getDataPopup);
            });
            e.sender.wrapper.find(".k-i-pause").parent("a").click(function(ev){
                ev.preventDefault();
                hangupCall(getDataPopup.calluuid);
                endCurrentCall(getDataPopup);
            });
        },
        closePopup: function(){
            var dialog = $("#window").data("kendoWindow");
            dialog.destroy();
            if( typeof timerVar !== 'undefined' )
            {
                clearInterval(timerVar);
            }
            // Api Window
            if(typeof timeInterval !== 'undefined') clearInterval(timeInterval);
            if(typeof intervalCheckCallComplete !== 'undefined') clearInterval(intervalCheckCallComplete);   
            if(typeof intervalCallAnswer !== 'undefined') clearInterval(intervalCallAnswer);
            flag_popup=false;
            $("#popup-contain").empty();
            if(typeof tabTitle != 'undefined') tabTitle();
            if(typeof changeToState != 'undefined') changeToState(1);
            if(typeof shownPopup != 'undefined') shownPopup(getDataPopup.calluuid);
        },
        item: customer,
        callCodeOption: [],
        receiveCallOption: [],
        callCodeChange: function(e) {
            var dataItem = e.sender.dataItem();
            this.set("item.callType", dataItem.type);
            if(Number(dataItem.type) == 1) {
                var d = new Date(date.getTime() + 10800000);
                this.set("item.followUp", true);
                this.set("item.re_call", d);
                this.set("visibleReCall", true);
                this.set("re_call_content", `Re call ${getDataPopup.customernumber}`);
            } else {
                this.set("item.followUp", false);
                this.set("item.re_call", null);
                this.set("visibleReCall", false);
                this.set("re_call_content", null);
            }
        },
        starttime: getDataPopup.starttime,
        calltime: (getDataPopup.time - getDataPopup.starttime + date.getTimezoneOffset()*60)*1000,
        calltimeText: "",
        intervalCallTime: function() {
            this.calltime += 1000;
            var time = kendo.toString(new Date(this.calltime), 'H:mm:ss');
            this.set('calltimeText', time);
        },
        save: function() {
            var kendoValidator = $("#window").kendoValidator().data("kendoValidator");
            
            if(!kendoValidator.validate()) {
                $("<div/>").appendTo("#page-content").kendoConfirm({
                  content: `Your data not validate. Do you still want to save?`,
                  messages:{
                    okText: "Sure",
                    cancel: "No"
                  }
                }).data("kendoConfirm").result.done(() => {
                    this.asyncSave();
                });
            } else this.asyncSave();
        },
        asyncSave: async function() {
            var data = this.item.toJSON();
            if(data.re_call)
                data.re_call = Number((new Date(data.re_call).getTime()) / 1000);
            
            var customerResponse = await $.ajax({
                url: ENV.baseUrl + "api/data/saveCustomer",
                type: "POST",
                data: data
            })
            
            if(customerResponse.status) {
                notification.show("Success!", "success");
                
                var call = {};
                    call.comment = data.comment ? data.comment : null;
                    call.callCode = data.callCode ? data.callCode : null;
                    call.receiveCall =  data.receiveCall ? data.receiveCall : null;
                    
                var callResponse = $.ajax({
                    url: ENV.baseUrl + "api/data/saveCall/" + getDataPopup.calluuid,
                    type: "POST",
                    data: call
                })
            }
        },
        callLogsData: new kendo.data.DataSource({
            serverFiltering: true,
            serverSorting: true,
            serverPaging: true,
            filter: {field: "customernumber", operator: "eq", value: getDataPopup.customernumber},
            pageSize: 12,
            transport: {
                read: {
                    url: ENV.restApi + "cdr",
                },
                parameterMap: function(options, operation) {
                    return options;
                }
            },
            schema: {
                data: "data",
                total: "total"
            },
            sort: {
                field: "starttime",
                dir: "desc"
            }
        }),
        openCustomerDetail(e) {
            var item = this.get("item");
            if(item.id)
                window.open(`${ENV.baseUrl}customers/customerdetail?id=${item.id}&callPopup=none`);
        },
        onRingEvent(data) {
            var countTime = data.currentTime - data.starttime;
            this.set("calltime" , (countTime) * 1000);
            this.intervalCallTime();
            $("#statusPopup").html(" (RINGING)");
            $("#timePopup").removeClass();
            $("#timePopup").addClass("label label-default");
            
            $(".phone-ring-container").removeClass("hidden");
            var name = (customer.name || '').toString();
            var phone = (getDataPopup.customernumber || '').toString();
            $(".phone-ring-container").find(".phonering-alo-ph-img-circle")
            .attr('title', `${name} - ${phone}`).tooltip('fixTitle').tooltip("show");
        },
        onCallEvent(data) {
            var countTime = data.currentTime - data.answertime;
            this.set("calltime" , (countTime) * 1000);
            this.intervalCallTime();
            $("#statusPopup").html(" (ON-CALL)");
            $("#timePopup").removeClass();
            $("#timePopup").addClass("label label-warning");
            
            $(".phone-ring-container").addClass("hidden");
            $(".phone-ring-container").find(".phonering-alo-ph-img-circle").tooltip("hide");
        }
    })

    kendo.bind($("#allPopup"), observable);
    if($("#window").length) {
        dialog = $("#window").data("kendoWindow");
        if(dialog) dialog.center().open();
        //realTimeFunction();
    } else flag_popup = false;
}();

function realTimeFunction() {
    var timeInterval = window.timeInterval = null,
        intervalCallAnswer = null;
    var checkWaiting = false;
    var checkOnCall = false;
    // Interval
    function checkPopupCallAnswer() {
        if(popupObservable.get("onProcess") && !checkOnCall){
            $.ajax({
                type: 'GET',
                url: ENV.baseUrl+'popup/checkCallAnswer',
                data: {
                    calluuid: getDataPopup.calluuid
                },
                dataType: "text",
                success: function (response) {
                    if (response != "" && popupObservable.intervalCallTime) {
                        // If On-Call
                        var calltime = 0;
                        popupObservable.set("calltime" , (calltime + date.getTimezoneOffset()*60) * 1000);
                        $("#statusPopup").html(" (ON-CALL)");
                        $("#timePopup").removeClass();
                        $("#timePopup").addClass("label label-warning");
                        if( typeof timeInterval !== 'undefined' ) clearInterval(timeInterval);
                        if( typeof intervalCallAnswer !== 'undefined' ) clearInterval(intervalCallAnswer);
                        timeInterval = window.timeInterval = setInterval(() => popupObservable.intervalCallTime(), 1000);
                        checkOnCall = true;
                    } else {
                        if(!checkWaiting) {
                            // Only run one in first
                            $("#statusPopup").html(" (RINGING)");
                            timeInterval = window.timeInterval = setInterval(() => popupObservable.intervalCallTime(), 1000);
                            // Callback to check On-call
                            checkWaiting = true;
                        }
                    }
                }
            });
        }
    }
    // Interval
    function checkPopupCallComplete() { 
        if(popupObservable.get("onProcess")) {
            $.ajax({
                url: ENV.baseUrl+'customers/popup/checkCallComplete',
                data: {
                    calluuid: getDataPopup.calluuid
                },
                dataType: "json",
                success: function (data) { 
                    if (data != '') {
                        $("#statusPopup").html(" (" + data.disposition + ")");
                        $("#timePopup").removeClass();
                        $("#timePopup").addClass((data.disposition == "ANSWERED") ? "label label-success" : "label label-danger");
                        if( typeof timeInterval !== 'undefined' ) clearInterval(timeInterval);
                        if( typeof intervalCheckCallComplete !== 'undefined' ) clearInterval(intervalCheckCallComplete);
                        if( typeof intervalCallAnswer !== 'undefined' ) clearInterval(intervalCallAnswer);
                        popupObservable.set("onProcess", false);
                        popupObservable.set("enabledTransfer", false);
                        
                        // Show time
                        var totalSeconds        = data.callduration; 
                        popupObservable.set("calltime", (totalSeconds-1 + date.getTimezoneOffset()*60)*1000);
                        popupObservable.set("continueDisabled", false);
                        popupObservable.intervalCallTime();
                    }
                }
            });
        }
    }
    
    if(getDataPopup.direction == "outbound") {
        // Outbound to show status and start time
        intervalCallAnswer = window.intervalCallAnswer = setInterval(checkPopupCallAnswer, 1000);
    } else {
        // Inbound
        var timeInterval = window.timeInterval = setInterval(() => popupObservable.intervalCallTime(), 1000);
        $("#statusPopup").html(" (ON-CALL)");
        popupObservable.set("enabledTransfer", true);
        popupObservable.listTransferExtensions.read();
    }
    var intervalCheckCallComplete = window.intervalCheckCallComplete = setInterval(
        function () {
            // Check complete to pause popup
            checkPopupCallComplete();
        }, 1000
    );
}
</script>
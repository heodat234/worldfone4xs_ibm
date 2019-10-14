<div id="all-popup">
    <div id="popup-window" data-role="window"
                     data-title="@Call Default@"
                     data-width="1000"
                     data-actions="['Arrows-no-change', 'Save','Pause','Refresh', 'Minimize', 'Maximize', 'Close']"
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
                                            <input class="k-textbox" data-bind="value: searchCustomer.name">
                                        </div>
                                    </div>
                                    <div class="form-group" style="display: none">
                                        <label class="control-label col-xs-4">CMND</label>
                                        <div class="col-xs-8">
                                            <input class="k-textbox" data-bind="value: searchCustomer.gttt">
                                        </div>
                                    </div>
                                    <div class="form-group" style="display: none">
                                        <label class="control-label col-xs-4">@Birthday@</label>
                                        <div class="col-xs-8">
                                            <input class="k-textbox" data-bind="value: searchCustomer.DOB">
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
                                            <input class="k-textbox" data-bind="value: searchCustomer.phone">
                                        </div>
                                    </div>
                                    <div class="form-group" style="display: none">
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
                                            {field:'name', title: '@Customer name@'},
                                            {field:'phone', title: '@Phone@'},
                                            {field:'DOB', title: '@Birthday@', hidden: true},
                                            {field:'ADDRESS', title: '@Address@', hidden: true},
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
                                            <input class="k-textbox" data-bind="value: item.name, invisible: item.cif" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">@Birthday@</label>
                                        <div class="col-xs-8">
                                            <span data-bind="text: item.BIRTH_DAY, visible: item.cif" style="font-size: 14px; line-height: 27px"></span>
                                            <input class="k-textbox" data-bind="value: item.BIRTH_DAY, invisible: item.cif" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">@Email@</label>
                                        <div class="col-xs-8">
                                            <span data-bind="text: item.email, visible: item.cif" style="font-size: 14px; line-height: 27px"></span>
                                            <input class="k-textbox" data-bind="value: item.email, invisible: item.cif" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">@Phone@</label>
                                        <div class="col-xs-8">
                                            <span data-bind="text: item.phone, visible: item.cif" style="font-size: 14px; line-height: 27px"></span>
                                            <input class="k-textbox" data-bind="value: item.phone, invisible: item.cif" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">CMND / HC</label>
                                        <div class="col-xs-8">
                                            <span data-bind="text: item.UNIQUE_ID_VALUE, visible: item.cif" style="font-size: 14px; line-height: 27px"></span>
                                            <input class="k-textbox" data-bind="value: item.UNIQUE_ID_VALUE, invisible: item.cif" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">@Address@</label>
                                        <div class="col-xs-8">
                                            <span data-bind="text: item.address, visible: item.cif" style="font-size: 14px; line-height: 27px"></span>
                                            <input class="k-textbox" data-bind="value: item.address, invisible: item.cif" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row title-row">
                                <span class="text-primary">@TICKET@</span>
                                <hr class="popup">
                            </div>
                            <div class="row form-horizontal">
                                <div class="col-sm-12">
                                    <div class="timeline block-content-full">
                                        <a data-role="button" class="pull-right" style="margin-top: 14px" data-bind="click: createTicket">@Create@ @ticket@</a>
                                        <h3 class="timeline-header">@Ticket@ @timeline@</h3>
                                        <!-- You can remove the class .timeline-hover if you don't want each event to be highlighted on mouse hover -->
                                        <ul class="timeline-list timeline-hover" data-template="case-timeline-template" data-bind="source: ticketData">
                                        </ul>
                                        <div class="text-center show-more-ticket">
                                            <a href="javascript:void(0)" data-bind="click: viewMoreTicket">@Show more@</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row title-row">
                                <span class="text-primary">@CALL RESULT@</span>
                                <hr class="popup">
                            </div>
                            <div class="row form-horizontal">
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
                                </div>
                                <div class="col-sm-6">
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

<script type="text/x-kendo-template" id="case-timeline-template">
    <li data-bind="css: {active: active}">
        <a href="javascript:void(0)" data-bind="attr: {data-id: id}, click: openTicketDetail">
            <div class="timeline-icon">
                <i class="fa fa-ticket"></i>
            </div>
        </a>
        <div class="timeline-time">
            <span class="text-muted" data-bind="text: createdAtText"></span>
        </div>
        <div class="timeline-content">
            <p class="pull-right">
                <i>@Created by@: </i> <span data-bind="text: createdBy"></span><br>
                <i>@Reply@: </i> <span data-bind="text: reply"></span>
            </p>
            <p class="push-bit">
                <a href="javascript: void(0)" data-bind="attr: {data-id: id}, click: openTicketDetail"><b class="text-danger" data-bind="text: ticket_id"></b></a>
                <i>(@Source@: <span data-bind="text: source"></span>)</i>
                <br>
                <strong data-bind="text: title"></strong><br>
                <i data-bind="text: service"></i><br>
                <span data-bind="text: status" class="label label-# if(data.status == "Open"){#success#}else{#default#}#"></span>
            </p>
        </div>
    </li>
</script>

<script type="text/javascript">
function chooseSearchCustomer(cif, customerName) {
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
                data: JSON.stringify({customername: customerName}),
                success: (response) => {
                    rePopup(callData.calluuid);
                },
                error: errorDataSource
            })
        }
    });
}

function chooseSearchCustomerTemplate(data) {
    return `<button class="k-button" onclick="chooseSearchCustomer('${data.cif}', '${data.name}')">@Choose@</button>`;
}

class defaultPopup extends Popup {
    constructor(dataCall) {
        super(dataCall);
        Object.assign(this, {
            _fieldId : "customernumber",
            _popupType: "default",
            call: dataCall,
            phone: dataCall.customernumber,
            openDetail: function(e) {
                var $content = $("#customer-detail-content");
                if(!$content.find("iframe").length)
                    $content.append(`<iframe src="${this.detailUrl}"" style="width: 100%; height: 610px; border: 0"></iframe>`);
            }
        });
        return this;
    }

    async init() {
        var fieldIdValue = this._dataCall[this._fieldId];

        var responseObj = await $.get(ENV.vApi + `popup/get_customer_by_phone`, {_: Date.now(), phone: fieldIdValue});

        if(!responseObj) {
            notification.show("Something is wrong", "error");
            $("#popup-contain").empty();
            return;
        }

        if(!responseObj.status) {
            notification.show(responseObj.message, "error");
            //$("#popup-contain").empty();
            //return;
        }

        if(!responseObj.total) {
            this.item.phone = this._dataCall.customernumber;
            var customer4x = await $.get(ENV.vApi + `popup/get_customer_by_cif_or_phone`, {_: Date.now(), phone: this.item.phone});
            var detailUrl = customer4x ? `${ENV.baseUrl}manage/customer?omc=1#/detail/${customer4x.id}` : ``;
            this.item.id = customer4x ? customer4x.id : null;
            this.assign({detailUrl: detailUrl}).open();
        } else if(responseObj.total == 1) {
            this.item = responseObj.data[0];
            var customer4x = await $.get(ENV.vApi + `popup/get_customer_by_cif_or_phone`, {_: Date.now(), phone: this.item.phone});
            var detailUrl = customer4x ? `${ENV.baseUrl}manage/customer?omc=1#/detail/${customer4x.id}` : ``;
            this.item.id = customer4x ? customer4x.id : null;
            this.assign({detailUrl: detailUrl}).open();
        } else {
            var buttons = {cancel: true};
            for (var i = 0; i < responseObj.total; i++) {
                buttons[i] = {text: responseObj.data[i].name, value: responseObj.data[i].id};
            }
            var type = swal({
                title: "Choose one.",
                text: `Greater than one customer have this number.`,
                icon: "warning",
                buttons: buttons
            }).then(async index => {
                console.log(index);
                if(index !== null && index !== false) {
                    // this.item = responseObj.data[index];
                    // var customer4x = await $.get(ENV.vApi + `popup/get_customer_by_cif_or_phone`, {_: Date.now(), id: this.item});
                    // var detailUrl = customer4x ? `${ENV.baseUrl}manage/customer?omc=1#/detail/${customer4x.id}` : ``;
                    // this.item.id = customer4x ? customer4x.id : null;
                    var customer4x = await $.get(`${ENV.restApi}customer/${index}`);
                    var detailUrl = `${ENV.baseUrl}manage/customer?omc=1#/detail/${index}`;
                    this.item = customer4x;
                    this.assign({detailUrl: detailUrl}).open();
                } else {
                    $("#popup-contain").empty();
                }
            })
        }
    }
}

var callData = <?= json_encode($callData) ?>;

window.popupObservable = new defaultPopup(callData);
window.popupObservable.assign({
    visibleSearchCustomer: false,
    searchCustomer: {},
    searchCustomerData: [],
    hideTicket: false,
    onSearchCustomer: function() {
        this.set("visibleSearchCustomer", !this.get("visibleSearchCustomer"));
    },
    getSearchCustomer: function() {
        $(".timeline-header").hide();
        $(".show-more-ticket").hide();
        notification.show("@Wait a minute@.");
        var searchCustomer = JSON.parse(JSON.stringify(this.get("searchCustomer")));
        var filterCustomer = [];
        $.each(searchCustomer, function(key, value) {
            filterCustomer.push({
                field   : key,
                operator: 'eq',
                value   : value
            });
        });
        $.ajax({
            url: ENV.restApi + "customer",
            data: {q: JSON.stringify({
                    filter: {
                        logic   : 'and',
                        filters : filterCustomer
                    }
                })},
            success: response => {
                if(response.total)
                    this.set("searchCustomerData", response.data);
                else notification.show("@No data@", "error");
            }
        })
    },
    followUp: {},
    call: {},
    afterActivePopup: function(e) {
        this.ticketData.filter({field: "sender_id", operator: "eq", value: this.item.id});
    },
    callResultOption: dataSourceJsonData(["Call", "result"]),
    playRecording: function(e) {
        play(this._dataCall.calluuid);
    },
    ticketData: new kendo.data.DataSource({
        serverFiltering: true,
        serverPaging: true,
        serverSorting: true,
        pageSize: 2,
        transport: {
            read: {
                url: ENV.restApi + "ticket",
                data: function() {
                    return {
                        filter: {
                            logic   : 'and',
                            filters : [
                                {
                                    field   : 'sender_id',
                                    operator: 'eq',
                                    value   : window.popupObservable.item.id
                                }
                            ]
                        }
                    }
                }
            },
            parameterMap: parameterMap
        },
        schema: {
            data: "data",
            total: function (response) {
                if(response.total === 0) {
                    $(".timeline-header").hide();
                    $(".show-more-ticket").hide();
                }
                return response.total;
            },
            parse: function(response) {
                response.data.map(doc =>  {
                    doc.createdAtText = gridTimestamp(doc.createdAt);
                    doc.customerFormat = doc.customerFormat ? doc.customerFormat.join(", ") : "";
                })
                return response
            }
        },
        error: errorDataSource
    }),
    openTicketDetail: function(e) {
        $currentTarget = $(e.currentTarget);
        window.open("manage/ticket/#/detail/" + $currentTarget.data("id"),'_blank','noopener');
    },
    createTicket: function(e) {
        openForm({title: "@Create@ @ticket@"});
        ticketForm({title: `@Call@ ${kendo.toString(new Date, "dd/MM/yy H:mm:ss")}`, source: "Call center", sender_id: this.item.id, sender_name: this.item.name, assign: ENV.extension, calluuid: this._dataCall.calluuid, fromPage: 'CAL'});
    },
    viewMoreTicket: function(e) {
        this.ticketData.pageSize(this.ticketData.pageSize() + 2);
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

<style>
    .k-textbox {
        width: 90%;
    }
</style>

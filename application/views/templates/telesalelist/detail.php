<div id="cdr-action-menu" class="action-menu">
    <ul>
        <a href="javascript:void(0)" data-type="action/play" onclick="playAction(this)"><li><i class="fa fa-play text-info" style="padding-left: 3px"></i><span>Play</span></li></a>
        <a href="javascript:void(0)" data-type="action/download" onclick="downloadAction(this)"><li><i class="fa fa-cloud-download text-danger"></i><span>Download</span></li></a>
        <a href="javascript:void(0)" data-type="action/repopup" onclick="repopupAction(this)"><li><i class="hi hi-new_window text-warning"></i><span>Repopup</span></li></a>
    </ul>
</div>
<div id="detail" class="after-breadcrumb" style="overflow-y: auto;">
    <div class="col-sm-9" id="left-detail" style="padding: 0; border-right: 1px solid lightgray;">
        <div data-role="tabstrip">
            <ul>
                <li class="k-state-active">
                    @BASIC INFORMATION@
                </li>

                <li>
                    @LOG@
                </li>
            </ul>
            <div>
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-2" style="border-right: 1px solid lightgray; height: 100vh">
                            <div class="row text-center" style="padding: 15px 0">
                                <div class="col-sm-12">
                                    <a href="javascript:void(0)">
                                        <img src="<?= PROUI_PATH . "img/placeholders/avatars/avatar2.jpg" ?>" alt="avatar" style="border-radius: 32px; border: 1px solid lightgray; width: 64px; height: 64px" data-bind="invisible: item.avatar, click: uploadAvatar" class="preview-avatar img-circle">
                                        <img data-bind="attr: {src: item.avatar}, visible: item.avatar, click: uploadAvatar" class="preview-avatar img-circle">
                                        <div style="display: none">
                                            <input name="file" type="file" id="upload-avatar"
                                           data-role="upload"
                                           data-multiple="false"
                                           data-async="{ saveUrl: 'api/v1/upload/avatar/customer', autoUpload: true }"
                                           data-bind="events: { success: uploadSuccessAvatar }">
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 text-center">
                                    <p><b>@Customer signature@</b></p>
                                    <a id="customer-sign-link" target="_blank" data-toggle="lightbox-image" title="@Customer signature@">
                                        <img id="customer-sign-img" style="width: 100%">
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-10">
                            <div class="row" style="border-bottom: 1px solid lightgray">
                                <div class="col-sm-9" style="margin-bottom: 0">
                                    <h4>
                                        <span data-bind="text: item.customer_name" class="copy-item"></span>
                                        <span class="social-icon" data-bind="invisible: item.facebook">
                                            <span class="fa-stack fa-sm">
                                                <i class="fa fa-circle-thin fa-stack-2x"></i>
                                                <i class="fa fa-facebook fa-stack-1x"></i>
                                            </span>
                                        </span>
                                        <a data-bind="attr: {href: item.facebook}, visible: item.facebook" class="social-icon" target="_blank">
                                            <span class="fa-stack fa-sm">
                                                <i class="fa fa-circle-thin fa-stack-2x"></i>
                                                <i class="fa fa-facebook fa-stack-1x"></i>
                                            </span>
                                        </a>
                                        <span class="social-icon" data-bind="invisible: item.twitter">
                                            <span class="fa-stack fa-sm">
                                                <i class="fa fa-circle-thin fa-stack-2x"></i>
                                                <i class="fa fa-twitter fa-stack-1x"></i>
                                            </span>
                                        </span>
                                        <a data-bind="attr: {href: item.twitter}, visible: item.twitter" class="social-icon" target="_blank">
                                            <span class="fa-stack fa-sm">
                                                <i class="fa fa-circle-thin fa-stack-2x"></i>
                                                <i class="fa fa-twitter fa-stack-1x"></i>
                                            </span>
                                        </a>
                                        <span class="social-icon" data-bind="invisible: item.linkedin">
                                            <span class="fa-stack fa-sm">
                                                <i class="fa fa-circle-thin fa-stack-2x"></i>
                                                <i class="fa fa-linkedin fa-stack-1x"></i>
                                            </span>
                                        </span>
                                        <a data-bind="attr: {href: item.linkedin}, visible: item.linkedin" class="social-icon" target="_blank">
                                            <span class="fa-stack fa-sm">
                                                <i class="fa fa-circle-thin fa-stack-2x"></i>
                                                <i class="fa fa-linkedin fa-stack-1x"></i>
                                            </span>
                                        </a>
                                    </h4>
                                    <i data-bind="text: item.job"></i>
                                    <span data-bind="visible: item.work_location"> at </span>
                                    <b data-bind="text: item.work_location"></b>
                                    <i class="text-danger" data-bind="text: item.status"></i>
                                </div>
                                <div class="col-sm-3 text-center" style="padding-top: 8px">
                                    <div class="btn-group">
                                        <a class="btn btn-alt btn-default btn-sm" data-type="update" onclick="openForm({title: '@Edit@ @Customer@ ' + Detail.model.get('item').customer_name}); editForm()" data-role="tooltip" title="@Edit@">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                        <!-- <a class="btn btn-alt btn-default btn-sm" href="javascript:void(0)" onclick="openForm({title: '@Send email@ @to@ ' + Detail.model.get('item').name, width: 850}); emailFormDetail(this)" data-role="tooltip" title="@Send email@">
                                            <i class="fa fa-envelope"></i>
                                        </a>
                                        <a class="btn btn-alt btn-default btn-sm" href="javascript:void(0)" onclick="openForm({title: '@Send SMS@ @to@ ' + Detail.model.get('item').name, width: 500}); smsFormDetail(this)" data-role="tooltip" title="@Send SMS@">
                                            <i class="fa fa-commenting"></i>
                                        </a> -->
                                    </div>
                                </div>
                            </div>
                            <div class="row form-horizontal" style="padding-top: 10px;">
                                <div class="col-sm-12" id="customer-detail-view" style="height: 300px">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- <div>
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="timeline block-content-full">
                                <a data-role="button" class="pull-right" style="margin-top: 14px" data-bind="click: createTicket">@Create@ @ticket@</a>
                                <h3 class="timeline-header">@Ticket@ @timeline@</h3>
                                You can remove the class .timeline-hover if you don't want each event to be highlighted on mouse hover
                                <ul class="timeline-list timeline-hover" data-template="ticket-timeline-template" data-bind="source: caseData">
                                </ul>
                                <div class="text-center">
                                    <a href="javascript:void(0)" data-bind="click: viewMoreTicket">@Show more@</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> -->
            <div>
                <div class="container-fluid">
                    <div class="row" style="margin-bottom: 10px">
                        <div class="panel panel-primary">
                            <div class="panel-heading">@APPOINTMENT HISTORY@</div>
                            <div class="panel-body" style="padding: 0">
                                <div data-role="grid"
                                    data-pageable="{refresh: true}"
                                    data-no-records="{
                                        template: `<h2 class='text-danger'>@NO DATA@</h2>`
                                    }"
                                    data-columns='[
                                        {
                                            title: "@Created at@",
                                            field: "created_at",
                                            headerAttributes: { style: "white-space: normal"},
                                            width: "110px",
                                            filterable: false,
                                            template: data => gridDate(data.appointment_date),
                                        },{
                                            title: "@Telesale code@",
                                            field: "tl_code",
                                            headerAttributes: { style: "white-space: normal"},
                                            width: "110px",
                                            filterable: false
                                        },{
                                            field: "tl_name",
                                            title: "@Telesale name@",
                                            headerAttributes: { style: "white-space: normal"},
                                            width: "110px",
                                            filterable: false
                                        },{
                                            title: "@Customer@",
                                            columns: [{
                                                field: "customer_info.cmnd",
                                                title: "@National ID@",
                                                headerAttributes: { style: "white-space: normal"},
                                                width: "110px",
                                                filterable: false
                                            }, {
                                                field: "customer_info.name",
                                                title: "@Name@",
                                                width: "200px",
                                                headerAttributes: { style: "white-space: normal"},
                                                filterable: false
                                            }, {
                                                field: "customer_info.phone",
                                                title: "@Phone@",
                                                width: "150px",
                                                headerAttributes: { style: "white-space: normal"},
                                                filterable: false
                                            }]
                                        },{
                                            field: "appointment_date",
                                            title: "@Appointment date@",
                                            headerAttributes: { style: "white-space: normal"},
                                            width: "150px",
                                            template: data => gridDate(data.appointment_date, "dd/MM/yyyy"),
                                            filterable: false
                                        },{
                                            title: "@Loan Counter@",
                                            columns: [{
                                                field: "dealer_code",
                                                title: "@Code@",
                                                headerAttributes: { style: "white-space: normal"},
                                                width: "100px",
                                                filterable: false
                                            }, {
                                                field: "dealer_name",
                                                title: "@Name@",
                                                headerAttributes: { style: "white-space: normal"},
                                                width: "200px",
                                                filterable: false
                                            }, {
                                                field: "dealer_address",
                                                title: "@Address@",
                                                headerAttributes: { style: "white-space: normal"},
                                                width: "250px",
                                                filterable: false
                                            }]
                                        },{
                                            title: "SC",
                                            columns: [{
                                                field: "sc_code",
                                                title: "@Code@",
                                                headerAttributes: { style: "white-space: normal"},
                                                width: "100px",
                                                filterable: false
                                            }, {
                                                field: "sc_name",
                                                title: "@Name@",
                                                headerAttributes: { style: "white-space: normal"},
                                                width: "200px",
                                                filterable: false
                                            }, {
                                                field: "sc_phone",
                                                title: "@Phone@",
                                                headerAttributes: { style: "white-space: normal"},
                                                width: "150px",
                                                filterable: false
                                            }]
                                        }
                                        ]'
                                  data-bind="source: appointment_log"></div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row" style="margin-bottom: 10px">
                        <div class="panel panel-warning">
                            <div class="panel-heading">@CALL HISTORY@</div>
                            <div class="panel-body" style="padding: 0">
                                <div data-role="grid" id="call-history-grid"
                                data-pageable="{refresh: true}"
                                data-scrollable="false"
                                data-no-records="{
                                        template: `<h2 class='text-danger'>@NO DATA@</h2>`
                                    }"
                                data-columns="[
                                {field:'direction', title: '@Direction@', width: 80},
                                {field:'starttime', title: '@Time@', width: 140},
                                {field:'userextension', title: '@Extension@', width: 80},
                                {field:'customernumber', title: '@Phone@', width: 150},
                                {field:'disposition', title: '@Result@'},
                                {field:'note', title: '@Note@'},
                                {
                                    title: '',
                                    template: tplRecording,
                                    width: 36
                                }
                                ]"
                              data-bind="source: callHistory"></div>
                            </div>
                        </div>
                    </div>
                    <hr>

                    <div class="row hidden">
                        <div class="panel panel-info">
                            <div class="panel-heading">@CONVERSATION HISTORY@</div>
                            <div class="panel-body" style="padding: 0">
                                <div
                                    data-pageable="{refresh: true}"
                                    data-no-records="{
                                        template: `<h2 class='text-danger'>@NO DATA@</h2>`
                                    }"
                                    data-columns="[
                                        {
                                            field: 'source',
                                            title: '@Source@',
                                            width: 120,
                                        },
                                        {
                                            field: 'to.username',
                                            title: '@Customer name@',
                                            width: 150,
                                        },
                                        {
                                            field: 'page_name',
                                            title: '@FanPage Name@',
                                            width: 160,
                                        },
                                        {
                                            field: 'from.id',
                                            title: '@Agent@',
                                            width: 120,
                                        },
                                        {
                                            field: 'date_added',
                                            title: '@Time open conversation@',
                                            width: 160,
                                        },
                                        {
                                            field: 'close_time',
                                            title: '@Time close conversation@',
                                            width: 160,
                                        },
                                        {
                                            template: detailChatView,
                                            title: '@Detail@',
                                            width: 160,
                                        },
                                        ]"
                                  data-nobind="source: conversationData"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-3" style="height: 90vh; overflow-y: auto; padding: 0">
        <!-- Default Tabs -->
        <div data-role="tabstrip">
            <ul>
                <li class="k-state-active">
                    @Timeline@
                </li>
                <li>
                    @Note@
                </li>
                <li>
                    @Log@
                </li>
            </ul>
            <div>
                <div class="timeline block-content-full">
                    <h3 class="timeline-header">@Interactive history@</h3>
                    <!-- You can remove the class .timeline-hover if you don't want each event to be highlighted on mouse hover -->
                    <ul class="timeline-list timeline-hover" data-template="timeline-template" data-bind="source: interactiveDataSource">
                    </ul>
                </div>
            </div>
            <div>
                <textarea class="k-textbox" style="width: 100%" rows="5" data-bind="events: {keyup: addNote}" placeholder="@Type then enter to note@"></textarea>
                <div class="container-fluid" style="margin-top: 10px" data-template="note-template" data-bind="source: noteData">
                </div>
            </div>
            <div>
                <p>
                    <i>@Create by@: </i>
                    <b data-bind="text: item.createdBy"></b><br>
                    <i>@Create at@: </i>
                    <span data-bind="text: item.createdAtText"></span>
                </p>
                <p>
                    <i>@Last update by@: </i>
                    <b data-bind="text: item.updatedBy"></b><br>
                    <i>@Last update at@: </i>
                    <span data-bind="text: item.updatedAtText"></span>
                </p>
            </div>
        </div>
        <!-- END Default Tabs -->
    </div>
</div>

<link rel="stylesheet" href="<?= STEL_PATH.'css/detail.css' ?>" type="text/css">

<script type="text/x-kendo-template" id="ticket-timeline-template">
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

<script type="text/x-kendo-template" id="account-template">
    <div class="col-md-4">
        <h3>#= templateAccountType(data) #</h3>
        <div>
            #= templateAccountList(data) #
        </div>
    </div>
</script>

<script type="text/x-kendo-template" id="timeline-template">
    <li data-bind="css: {active: active}, attr: {data-id: id}">
        <a href="javascript:void(0)" #if(data.active){#data-bind="click: deactive"#}#>
            <div class="timeline-icon">
                # switch(data.type) {
                    case "call": #
                    <i class="fa fa-phone"></i>
                #   break;
                    case "email": #
                    <i class="fa fa-envelope"></i>
                #   break;
                    case "sms": #
                    <i class="fa fa-commenting"></i>
                #   break;
                    case "chat": #
                    <i class="gi gi-conversation"></i>
                #   break;
                } #
            </div>
        </a>
        <div class="timeline-time">
            <span class="text-muted" data-bind="text: createdAtText"></span>
        </div>
        <div class="timeline-content">
            <p class="push-bit"><strong data-bind="text: title"></strong></p>
            <p class="push-bit" data-bind="invisible: visibleEditInteractive">
                <span data-bind="text: content"></span>
                <br>
                <a href="javascript:void(0)" data-bind="click: editInteractive, visible: active"><i class="fa fa-pencil"></i></a>
            </p>
            <p class="push-bit" data-bind="visible: visibleEditInteractive">
                <textarea class="k-textbox" data-bind="value: content"></textarea>
                <br>
                <a href="javascript:void(0)" class="k-button" data-bind="click: saveEditInteractive">Save</a>
                <a href="javascript:void(0)" class="k-button" data-bind="click: closeEditInteractive">Cancel</a>
            </p>
        </div>
    </li>
</script>

<script type="text/x-kendo-template" id="note-template">
    <div class="row">
        <div class="alert alert-#= HELPER.bsColors[index % HELPER.bsColors.length] #">
            <button type="button" class="close" aria-hidden="true" data-bind="click: removeNote" data-id="#= id #">×</button>
            <p data-bind="text: content"></p>
            <p class="text-right text-muted"><span data-bind="text: createdBy"></span> - <span data-bind="text: createdAtText"></span></p>
        </div>
    </div>
</script>

<script>
var Config = Object.assign(Config, {
    id: '<?= $this->input->get("id") ?>',
    collection: 'telesalelist'
});

function detailChatView(data) {
    return '<a role="button" target="_blank" class="btn btn-sm btn-action" href="'+ENV.baseUrl+'app/chatdetail?room_id='+data.id+'" >Chi tiết chat</a>'
}

function templateAccountType(data) {
    var values = {
        ListAccountCasa : "@TKTT@",
        ListAccountSaving : "@TKTK@",
        ListAccountLoan : "@KUV@",
    }
    return (values[data.type] || "").toString()
}

function templateAccountList(data) {
    var arrHTML = [];
    arrHTML.push(`<ul class="list-group" style="margin-bottom: 0">`);
    switch (data.type) {
        case "ListAccountCasa":
            if(data.list.length) {
                data.list.forEach((account, index) => {
                    arrHTML.push(`<li class="list-group-item list-group-item-action list-group-item-${HELPER.bsColors[index%5]}">
                        <ul class="none-style-type">
                            <li><label>@Branchname@:</label> ${account.AccountBranchName}</li>
                            <li><label>@Account no@:</label> ${account.AccountNumber}</li>
                            <li><label>@Account class@:</label> ${account.Account_class}</li>
                            <li><label>@Account Branch@:</label> ${account.AccountBranch}</li>
                            <li><label>@Working Balance@:</label> ${kendo.toString(Number(account.WorkingBalance), "n0")}</li>
                            <li><label>@AvailableAmount@:</label> ${kendo.toString(Number(account.AvailableAmount), "n0")}</li>
                            <li><label>@Currency@:</label> ${account.Currency}</li>
                            <li><label>@Account Open Date@:</label> ${account.Ac_Open_Date}</li>
                            <li><label>@Account Status@:</label> ${account.AccStatus}</li>
                        </ul>
                    </li>`);
                })
            }
            break;
        case "ListAccountSaving":
            if(data.list.length) {
                data.list.forEach((account, index) => {
                    arrHTML.push(`<li class="list-group-item list-group-item-action list-group-item-${HELPER.bsColors[index%5]}">
                        <ul class="none-style-type">
                            <li><label>@Branchname@:</label> ${account.AccountBranchName}</li>
                            <li><label>@Account no@:</label> ${account.AccountNumber}</li>
                            <li><label>@Account class@:</label> ${account.Account_class}</li>
                            <li><label>@Account Branch@:</label> ${account.AccountBranch}</li>
                            <li><label>@Working Balance@:</label> ${kendo.toString(Number(account.WorkingBalance), "n0")}</li>
                            <li><label>@AvailableAmount@:</label> ${kendo.toString(Number(account.AvailableAmount), "n0")}</li>
                            <li><label>@Currency@:</label> ${account.Currency}</li>
                            <li><label>@Account Open Date@:</label> ${account.Ac_Open_Date}</li>
                            <li><label>@Account Status@:</label> ${account.AccStatus}</li>
                        </ul>
                    </li>`);
                })
            }
            break;
        case "ListAccountLoan":
            if(data.list.length) {
                data.list.forEach((account, index) => {
                    arrHTML.push(`<li class="list-group-item list-group-item-action list-group-item-${HELPER.bsColors[index%5]}">
                        <ul class="none-style-type">
                            <li><label>@Branchname@:</label> ${account.AccountBranchName}</li>
                            <li><label>@Account no@:</label> ${account.AccountNumber}</li>
                            <li><label>@Account Branch@:</label> ${account.AccountBranch}</li>
                            <li><label>@AmountFinanced@:</label> ${kendo.toString(Number(account.AmountFinanced), "n0")}</li>
                            <li><label>@Currency@:</label> ${account.Currency}</li>
                            <li><label>@Account Status@:</label> ${account.AccStatus}</li>
                        </ul>
                    </li>`);
                })
            }
            break;
        default:
            break;
    }
    arrHTML.push(`</ul>`);
    return arrHTML.join("");
}

function tplRecording(data) {
    return `<a role="button" class="btn btn-sm btn-circle btn-action" data-uid="${data.uid}"><i class="fa fa-ellipsis-v"></i></a>`
}

function playAction(ele) {
    var uid = $(ele).data("uid"),
        dataItem = Detail.model.callHistory.getByUid(uid),
        calluuid = dataItem.calluuid,
        callduration = dataItem.callduration;
    if(callduration)
        play(calluuid);
    else notification.show("No recording", "warning");
}

function downloadAction(ele) {
    var uid = $(ele).data("uid");
        dataItem = Detail.model.callHistory.getByUid(uid),
        calluuid = dataItem.calluuid,
        callduration = dataItem.callduration;
    if(callduration)
        downloadRecord(calluuid);
    else notification.show("No recording", "warning");
}

function repopupAction(ele) {
    var uid = $(ele).data("uid");
    var calluuid = Detail.model.callHistory.getByUid(uid).calluuid;
    rePopup(calluuid);
}

function emailFormDetail(ele) {
    emailForm({doc: Detail.model.get("item").toJSON()});
}

function smsFormDetail(ele) {
    smsForm({doc: Detail.model.get("item").toJSON()});
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

var Detail = function() {
    var observable = Object.assign(Config.observable, {
        item: {},
        interactiveDataSource: new kendo.data.DataSource({
            serverFiltering: true,
            serverSorting: true,
            sort: [{field: "createdAt", dir: "desc"}],
            transport: {
                read: `${ENV.vApi}interactive/read`,
                parameterMap: parameterMap
            },
            schema: {
                data: "data",
                total: "total",
                parse: function(response) {
                    response.data.map(function(doc, idx){
                        doc.index = idx;
                        doc.createdAtText = (kendo.toString(new Date(doc.createdAt * 1000), "dd/MM/yy H:mm") ||  "").toString();
                    })
                    return response;
                }
            }
        }),
        editInteractive: function(e) {
            let id = $(e.currentTarget).closest("li").data("id"),
                data = this.interactiveDataSource.data();

            data.map(doc =>  doc.visibleEditInteractive = Boolean(doc.id == id));
            this.interactiveDataSource.data(data);
        },
        closeEditInteractive: function() {
            let data = this.interactiveDataSource.data();
            data.map(doc =>  doc.visibleEditInteractive = false);
            this.interactiveDataSource.data(data);
        },
        saveEditInteractive: function(e) {
            var id = $(e.currentTarget).closest("li").data("id");
            var content = $(e.currentTarget).closest("p").find("textarea").val();
            swal({
                title: "Are you sure?",
                text: `Save content this interactive.`,
                icon: "warning",
                buttons: true,
                dangerMode: false,
            })
            .then((sure) => {
                if (sure) {
                    $.ajax({
                        url: `${ENV.vApi}interactive/update/${id}`,
                        type: "PUT",
                        contentType: "application/json; charset=utf-8",
                        data: kendo.stringify({content: content}),
                        success: syncDataSource,
                        error: errorDataSource
                    })
                }
                this.interactiveDataSource.read();
            });
        },
        deactive: function(e) {
            var id = $(e.currentTarget).closest("li").data("id");
            swal({
                title: "Are you sure?",
                text: `Apply this interactive. You can't change info after apply.`,
                icon: "warning",
                buttons: true,
                dangerMode: false,
            })
            .then((sure) => {
                if (sure) {
                    $.ajax({
                        url: `${ENV.vApi}interactive/update/${id}`,
                        type: "PUT",
                        contentType: "application/json; charset=utf-8",
                        data: kendo.stringify({active: false, foreign_id: Config.id}),
                        success: syncDataSource,
                        error: errorDataSource
                    })
                }
                this.interactiveDataSource.read();
            });
        },
        noteData: new kendo.data.DataSource({
            serverFiltering: true,
            serverSorting: true,
            filter: [
                {field: "foreign_id", operator: "eq", value: Config.id}
            ],
            sort: [{field: "createdAt", dir: "desc"}],
            transport: {
                read: `${ENV.restApi}customer_note`,
                parameterMap: parameterMap
            },
            schema: {
                data: "data",
                total: "total",
                parse: function(response) {
                    response.data.map(function(doc, idx){
                        doc.index = idx;
                        doc.createdAtText = (kendo.toString(new Date(doc.createdAt * 1000), "dd/MM/yy H:mm") ||  "").toString();
                    })
                    return response;
                }
            }
        }),
        addNote: function(e) {
            if(e.keyCode == 13) {
                var content = e.currentTarget.value.replace("\n", "");
                swal({
                    title: "@Are you sure@?",
                    text: `@Save@ @this note@.`,
                    icon: "warning",
                    buttons: true,
                    dangerMode: false,
                })
                .then((sure) => {
                    if (sure) {
                        e.currentTarget.value = "";
                        $.ajax({
                            url: `${ENV.restApi}customer_note`,
                            type: "POST",
                            contentType: "application/json; charset=utf-8",
                            data: kendo.stringify({
                                collection: "Customer",
                                foreign_id: Config.id,
                                content: content
                            }),
                            success: function() {
                                syncDataSource();
                                Detail.model.noteData.read();
                            },
                            error: errorDataSource
                        })
                    }
                });
            }
        },
        removeNote: function(e) {
            var id = $(e.currentTarget).data("id");
            swal({
                title: "Are you sure?",
                text: `Remove this note.`,
                icon: "warning",
                buttons: true,
                dangerMode: false,
            })
            .then((sure) => {
                if(sure) {
                    $.ajax({
                        url: `${ENV.restApi}customer_note/${id}`,
                        type: "DELETE",
                        success: function() {
                            syncDataSource();
                            Detail.model.noteData.read();
                        },
                        error: errorDataSource
                    })
                }
            })
        },
        onSelectFile: function(e) {
            console.log(e);
        },
        callHistory: new kendo.data.DataSource({
            serverFiltering: true,
            serverPaging: true,
            serverSorting: true,
            sort: {field: "starttime", dir: "desc"},
            pageSize: 5,
            transport: {
                read: `${ENV.restApi}cdr`,
                parameterMap: parameterMap
            },
            schema: {
                data: "data",
                total: "total",
                parse: function(response) {
                    response.data.map(function(doc){
                        doc.starttime = doc.starttime ? kendo.toString(new Date(doc.starttime * 1000), "dd/MM/yy H:mm:ss").toString() : "";
                    })
                    return response;
                }
            }
        }),
        uploadAvatar: function() {
            $("#upload-avatar").click();
        },
        uploadSuccessAvatar: function(e) {
            notification.show(e.response.message, e.response.status ? "success" : "error");
            e.sender.clearAllFiles();
            if(e.response.filepath) {
                this.set("item.avatar", e.response.filepath);
                $.ajax({
                    url: ENV.restApi + "customer/" + this.get("item.id"),
                    type: "PUT",
                    contentType: "application/json; charset=utf-8",
                    data: JSON.stringify({avatar: e.response.filepath})
                })
            }
        },
        accountData: [],
        openAccount: function() {
            $.ajax({
                url: ENV.namaApi + "core/getInfoAccount",
                data: {q: JSON.stringify({cif: this.item.cif})},
                success: response => {
                    if(response.data) {
                        this.set("accountData", response.data);
                        console.log(response.data);
                    }
                },
                error: errorDataSource
            })
        },
        openCard: function() {
            this.cardTransactionData.filter({field: "cif", operator: "eq", value: this.get("item.cif")})
        },
        appointment_log: new kendo.data.DataSource({
            serverFiltering: true,
            serverPaging: true,
            serverSorting: true,
            // filter: {field: "cmnd", operator: "eq", value: "5d81d7c71ef2b43aff4326ac"},
            pageSize: 5,
            transport: {
                read: ENV.restApi + "appointment_log",
                parameterMap: parameterMap
            },
            schema: {
                data: "data",
                total: "total",
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
        viewMoreTicket: function(e) {
            this.caseData.pageSize(this.caseData.pageSize() + 2);
        },
        cardTransactionData: new kendo.data.DataSource({
            serverFiltering: true,
            serverPaging: true,
            serverSorting: true,
            pageSize: 2,
            transport: {
                read: ENV.restApi + "card_transaction",
                parameterMap: parameterMap
            },
            schema: {
                data: "data",
                total: "total"
            },
            error: errorDataSource
        }),
        conversationData: new kendo.data.DataSource({
            serverFiltering: true,
            serverPaging: true,
            serverSorting: true,
            pageSize: 5,
            transport: {
                read: {
                    url: ENV.vApi + "conversation/read",
                    data:{ id: Config.id},
                },
                parameterMap: parameterMap
            },
            schema: {
                data: "data",
                total: "total",
                parse: function(response) {
                    response.data.map(doc =>  {
                        doc.close_time = gridTimestamp(doc.close_time);
                        doc.date_added = gridTimestamp(doc.date_added);
                    })
                    return response
                }
            },
            error: errorDataSource
        }),
        openTicketDetail: function(e) {
            $currentTarget = $(e.currentTarget);
            window.open("manage/ticket/solve/#/detail/" + $currentTarget.data("id"),'_blank','noopener');
        },
        createTicket: function() {
            openForm({title: "@Create@ @ticket@", width: 700});
            ticketForm({sender_id: Config.id, sender_name: this.get("item.name")});
        }
    });
    var model = kendo.observable(observable);
    return {
        model: model,
        read: async function() {
            // Built view
            var customerHTMLArray = [];

            var dataItemFull = await $.get(`${ENV.restApi}${Config.collection}/${Config.id}`);
            dataItemFull.phoneHTML = gridPhone(dataItemFull.phone);
            dataItemFull.other_phonesHTML = gridPhone(dataItemFull.other_phones);
            dataItemFull.createdAtText = gridTimestamp(dataItemFull.createdAt);
            dataItemFull.updatedAtText = gridTimestamp(dataItemFull.updatedAt);
            dataItemFull.date_of_birthText = gridTimestamp(dataItemFull.date_of_birth);
            dataItemFull.date_receive_dataText = gridTimestamp(dataItemFull.date_receive_data);
            dataItemFull.date_send_dataText = gridTimestamp(dataItemFull.date_send_data);
            dataItemFull.exporting_dateText = gridTimestamp(dataItemFull.exporting_date);
            dataItemFull.first_due_dateText = gridTimestamp(dataItemFull.first_due_date);


            var customerModel_1 = await $.get(`${ENV.vApi}model/read`, {
                q: JSON.stringify({filter: {field: "collection", operator: "eq", value: (ENV.type ? ENV.type + "_" : "") + "Telesalelist"}, sort: {field: "index", dir: "asc"}, take: 30,skip:0})
            });
            customerModel_1.data.forEach(doc => {

                if(!doc.sub_type) return;
                let sub_type = JSON.parse(doc.sub_type);
                if(!sub_type.detailShow) return;

                if(doc.field != "name") {
                    switch(doc.type) {
                        case "phone": case "arrayPhone":
                            dataItemFull[doc.field + "HTML"] = gridPhone(dataItemFull[doc.field]);
                            customerHTMLArray.push(`<div class="form-group">
                                <label class="col-sm-3 control-label">${doc.title}</label>
                                <div class="col-sm-9">
                                    <p class="form-control-static" data-bind="html: item.${doc.field}HTML"></p>
                                </div>
                            </div>`);
                            break;
                        case "timestamp":
                            dataItemFull[doc.field + "Text"] = gridTimestamp(dataItemFull[doc.field]);
                            customerHTMLArray.push(`<div class="form-group">
                                <label class="col-sm-3 control-label">${doc.title}</label>
                                <div class="col-sm-9">
                                    <p class="form-control-static" data-bind="text: item.${doc.field}Text"></p>
                                </div>
                            </div>`);
                            break;
                        default:
                            customerHTMLArray.push(`<div class="form-group">
                                <label class="col-sm-3 control-label">${doc.title}</label>
                                <div class="col-sm-9">
                                    <p class="form-control-static copy-item" data-bind="text: item.${doc.field}"></p>
                                </div>
                            </div>`);
                            break;
                    }
                }
            });

            $("#customer-detail-view").html(customerHTMLArray.join(''));
            this.model.set("item", dataItemFull);

            // Sig_code
            if(dataItemFull.cif) {
                $.get(ENV.namaApi + "core/getInfoListSignature",
                    {q: JSON.stringify({cif: dataItemFull.cif})},
                    function(response) {
                        if(response.status && response.doc) {
                            $("#customer-sign-img").attr("src", "data:image/png;base64, " + response.doc.Sig_code);
                            $("#customer-sign-link").attr("href", "data:image/png;base64, " + response.doc.Sig_code);
                        } else $("#customer-sign-link").html(`<i>@No data@</i>`);
                })
            }

            // CDR
            var filter = {
                logic: "or",
                filters: [
                    {field: "customernumber", operator: "eq", value: dataItemFull.phone}
                ]
            };
            if(dataItemFull.other_phones) {
                dataItemFull.other_phones.forEach(function(phone){
                    filter.filters.push({field: "customernumber", operator: "eq", value: phone})
                })
            }
            this.model.callHistory.filter(filter);

            var filter_appointment = {
                logic: "or",
                filters: [
                    {field: "cmnd", operator: "eq", value: dataItemFull.cmnd}
                ]
            };
            this.model.appointment_log.filter(filter_appointment);


            var interactiveFilters = [{
                logic: "and",
                filters: [
                    {field: "foreign_id", operator: "eq", value: Config.id},
                    {field: "active", operator: "eq", value: false}
                ]
            }];
            if(dataItemFull.phone) {
                interactiveFilters.push({
                    logic: "and",
                    filters: [
                        {field: "type", operator: "eq", value: "call"},
                        {field: "foreign_key", operator: "eq", value: dataItemFull.phone},
                        {field: "active", operator: "eq", value: true}
                    ]
                })
            }
            if(dataItemFull.email) {
                interactiveFilters.push({
                    logic: "and",
                    filters: [
                        {field: "type", operator: "eq", value: "email"},
                        {field: "foreign_key", operator: "eq", value: dataItemFull.email},
                        {field: "active", operator: "eq", value: true}
                    ]
                })
            }
            this.model.interactiveDataSource.filter({
                logic: "or",
                filters: interactiveFilters
            });
            return dataItemFull;
        },
        init: function() {
            this.read().then(() => {
                kendo.bind($("#detail"), this.model);

                /*
                 * Right Click Menu
                 */
                var menu = $("#cdr-action-menu");
                if(!menu.length) return;

                $("html").on("click", function() {menu.hide()});

                $(document).on("click", "#call-history-grid tr[role=row] a.btn-action", function(e){
                    let row = $(e.target).closest("tr");
                    e.pageX -= 20;
                    showMenu(e, row);
                });

                function showMenu(e, that) {
                    //hide menu if already shown
                    menu.hide();

                    //Get id value of document
                    var uid = $(that).data('uid');
                    if(uid)
                    {
                        menu.find("a").data('uid',uid);

                        //get x and y values of the click event
                        var pageX = e.pageX;
                        var pageY = e.pageY;

                        //position menu div near mouse cliked area
                        menu.css({top: pageY , left: pageX});

                        var mwidth = menu.width();
                        var mheight = menu.height();
                        var screenWidth = $(window).width();
                        var screenHeight = $(window).height();

                        //if window is scrolled
                        var scrTop = $(window).scrollTop();

                        //if the menu is close to right edge of the window
                        if(pageX+mwidth > screenWidth){
                        menu.css({left:pageX-mwidth});
                        }

                        //if the menu is close to bottom edge of the window
                        if(pageY+mheight > screenHeight+scrTop){
                        menu.css({top:pageY-mheight});
                        }

                        //finally show the menu
                        menu.show();
                    }
                }
            })
        }
    }
}();

Detail.init();

async function editForm(ele) {
    var dataItemFull = await $.get(`${Config.crudApi + Config.collection}/${Config.id}`),
        formHtml = await $.ajax({
            url: Config.templateApi + Config.collection + "/form",
            error: errorDataSource
        });

    dataItemFull.exporting_date = new Date((dataItemFull.exporting_date) * 1000 + 86400000);
    dataItemFull.date_of_birth = new Date((dataItemFull.date_of_birth) * 1000 + 86400000);
    dataItemFull.date_send_data = new Date((dataItemFull.date_send_data) * 1000 + 86400000);
    dataItemFull.date_receive_data = new Date((dataItemFull.date_receive_data) * 1000 + 86400000);
    var model = Object.assign(Config.observable, {
        item: dataItemFull,
        save: function() {
            var data = this.item.toJSON();
            $.ajax({
                url: `${Config.crudApi + Config.collection}/${Config.id}`,
                type: "PUT",
                data: JSON.stringify(data),
                contentType: "application/json; charset=utf-8",
                success: (response) => {
                    if(response.status) {
                        notificationAfterRefresh("@Edit@ @Customer@ @Success@", "success");
                        location.reload();
                    } else notification.show("@Error@", "error");
                },
                error: errorDataSource
            })
        }
    });
    kendo.destroy($("#right-form"));
    $("#right-form").empty();
    var kendoView = new kendo.View(formHtml, { wrap: false, model: model, evalTemplate: false });
    kendoView.render($("#right-form"));
}


$('[data-toggle="lightbox-image"]').magnificPopup({type:"image", image: {titleSrc:"title"}});
</script>
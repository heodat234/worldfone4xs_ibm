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
                <li data-bind="click: openPaymentHistory">
                    PAYMENT HISTORY
                </li>
                <li data-bind="click: openFieldAction">
                    FIELD ACTION
                </li>
                <li data-bind="click: openLawSuit">
                    LAWSUIT
                </li>
                <li data-bind="click: openCrossSell">
                    CROSS-SELL
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
                        </div>
                        <div class="col-sm-10">
                            <div class="row" style="border-bottom: 1px solid lightgray">
                                <div class="col-sm-9" style="margin-bottom: 0">
                                    <h4>
                                        <span data-bind="text: item.name" class="copy-item"></span>
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
                                        <a class="btn btn-alt btn-default btn-sm" data-type="update" onclick="openForm({title: '@Edit@ @Customer@ ' + Detail.model.get('item').name}); editForm()" data-role="tooltip" title="@Edit@">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                        <a class="btn btn-alt btn-default btn-sm hidden" href="javascript:void(0)" onclick="openForm({title: '@Send email@ @to@ ' + Detail.model.get('item').name, width: 850}); emailFormDetail(this)" data-role="tooltip" title="@Send email@">
                                            <i class="fa fa-envelope"></i>
                                        </a>
                                        <a class="btn btn-alt btn-default btn-sm hidden" href="javascript:void(0)" onclick="openForm({title: '@Send SMS@ @to@ ' + Detail.model.get('item').name, width: 500}); smsFormDetail(this)" data-role="tooltip" title="@Send SMS@">
                                            <i class="fa fa-commenting"></i>
                                        </a>
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
            <div>
                <div class="container-fluid">
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
                    <div class="row title-row main-product-container">
                        <span class="text-primary">MAIN PRODUCT</span>
                        <span data-bind="text: mainProductOptionLength"></span>
                        <hr class="popup">
                    </div>
                    <div class="row form-horizontal main-product-container">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label col-xs-4">Contract No. <span id="main-product-count"></span></label>
                                <div class="col-xs-8">
                                    <input data-role="dropdownlist" name="contractNo"
                                        data-value-primitive="true"
                                        data-text-field="account_number"
                                        data-value-field="account_number"                  
                                        data-bind="value: mainProduct.account_number, source: mainProductOption, events: {change: mainProductChange}" 
                                        style="width: 100%"/>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-xs-4">Product name</label>
                                <div class="col-xs-8">
                                    <p class="form-control-static" data-bind="text: mainProduct.product_name"></p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-xs-4">Monthly amount</label>
                                <div class="col-xs-8">
                                    <p class="form-control-static" data-bind="text: mainProduct.RPY_PRD"></p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-xs-4">Maturity Date</label>
                                <div class="col-xs-8">
                                    <p class="form-control-static" data-bind="text: mainProduct.DT_MAT"></p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-xs-4">Last Payment Date</label>
                                <div class="col-xs-8">
                                    <p class="form-control-static" data-bind="text: mainProduct.last_payment_date"></p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-xs-4">Debt group</label>
                                <div class="col-xs-8">
                                    <p class="form-control-static" data-bind="text: mainProduct.debt_group"></p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-xs-4">First/Last payment default</label>
                                <div class="col-xs-8">
                                    <p class="form-control-static" data-bind="text: mainProduct.first_last_payment_default"></p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-xs-4">Interest rate</label>
                                <div class="col-xs-8">
                                    <p class="form-control-static" data-bind="text: mainProduct.interst_rate"></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label col-xs-4">Due date</label>
                                <div class="col-xs-8">
                                    <p class="form-control-static" data-bind="text: mainProduct.due_date"></p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-xs-4">Last action code</label>
                                <div class="col-xs-8">
                                    <p class="form-control-static" data-bind="text: mainProduct.lastActionCode"></p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-xs-4">Overdue amount</label>
                                <div class="col-xs-8">
                                    <p class="form-control-static" data-bind="text: mainProduct.overdue_amount"></p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-xs-4">Approved Limit</label>
                                <div class="col-xs-8">
                                    <p class="form-control-static" data-bind="text: mainProduct.approved_limit"></p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-xs-4">Last payment amount</label>
                                <div class="col-xs-8">
                                    <p class="form-control-static" data-bind="text: mainProduct.last_payment_amount"></p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-xs-4">Term</label>
                                <div class="col-xs-8">
                                    <p class="form-control-static" data-bind="text: mainProduct.term"></p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-xs-4">Sales (Code name)</label>
                                <div class="col-xs-8">
                                    <p class="form-control-static" data-bind="text: mainProduct.sale_consultant"></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label col-xs-4">No. of Overdue days</label>
                                <div class="col-xs-8">
                                    <p class="form-control-static" data-bind="text: mainProduct.no_of_overdue_date"></p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-xs-4">Last action code date</label>
                                <div class="col-xs-8">
                                    <p class="form-control-static" data-bind="text: mainProduct.lastActionCodeDate"></p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-xs-4">Outstanding balance</label>
                                <div class="col-xs-8">
                                    <p class="form-control-static" data-bind="text: mainProduct.outstanding_balance"></p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-xs-4">Advance money</label>
                                <div class="col-xs-8">
                                    <p class="form-control-static" data-bind="text: mainProduct.advance_money"></p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-xs-4">Name of store</label>
                                <div class="col-xs-8">
                                    <p class="form-control-static" data-bind="text: mainProduct.name_of_store"></p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-xs-4">Principal Amount</label>
                                <div class="col-xs-8">
                                    <p class="form-control-static" data-bind="text: mainProduct.principal_amount"></p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-xs-4">Staff in Charge</label>
                                <div class="col-xs-8">
                                    <p class="form-control-static" data-bind="text: mainProduct.staffInCharge"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row title-row card-container">
                        <span class="text-primary">CARD INFORMATION</span>
                        <hr class="popup">
                    </div>
                    <div class="row form-horizontal card-container">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label col-xs-4">Contract No. <span id="card-count"></span></label>
                                <div class="col-xs-8">
                                    <input data-role="dropdownlist" name="contract_no"
                                        data-value-primitive="true"
                                        data-text-field="contract_no"
                                        data-value-field="contract_no"                  
                                        data-bind="value: card.account_number, source: cardOption, events: {change: cardChange}" 
                                        style="width: 100%"/>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-xs-4">Interest Rate</label>
                                <div class="col-xs-8">
                                    <p class="form-control-static" data-bind="text: card.interest_rate"></p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-xs-4">Approved Limit</label>
                                <div class="col-xs-8">
                                    <p class="form-control-static" data-bind="text: card.approved_limit"></p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-xs-4">Open Date / First released date</label>
                                <div class="col-xs-8">
                                    <p class="form-control-static" data-bind="text: card.first_released_date"></p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-xs-4">Last Payment Date</label>
                                <div class="col-xs-8">
                                    <p class="form-control-static" data-bind="text: card.last_payment_date"></p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-xs-4">Principal Amount</label>
                                <div class="col-xs-8">
                                    <p class="form-control-static" data-bind="text: card.principal_amount"></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                         <div class="form-group">
                            <label class="control-label col-xs-4">Due date</label>
                            <div class="col-xs-8">
                                <p class="form-control-static" data-bind="text: card.due_date"></p>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-xs-4">Last action code</label>
                            <div class="col-xs-8">
                                <p class="form-control-static" data-bind="text: card.last_action_code"></p>
                            </div>
                        </div>
                            <div class="form-group">
                                <label class="control-label col-xs-4">Overdue Amount</label>
                                <div class="col-xs-8">
                                    <p class="form-control-static" data-bind="text: card.overdue_amount"></p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-xs-4">Expired Date</label>
                                <div class="col-xs-8">
                                    <p class="form-control-static" data-bind="text: card.expiry_date"></p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-xs-4">Last payment amount</label>
                                <div class="col-xs-8">
                                    <p class="form-control-static" data-bind="text: card.last_payment_amount"></p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-xs-4">Debt Group</label>
                                <div class="col-xs-8">
                                    <p class="form-control-static" data-bind="text: card.debt_group"></p>
                                </div>
                            </div>
                        
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label col-xs-4">No of Overdue days</label>
                                <div class="col-xs-8">
                                    <p class="form-control-static" data-bind="text: card.no_of_overdue_date"></p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-xs-4">Last action code date</label>
                                <div class="col-xs-8">
                                    <p class="form-control-static" data-bind="text: card.last_action_code_date"></p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-xs-4">Outstanding balance</label>
                                <div class="col-xs-8">
                                    <p class="form-control-static" data-bind="text: card.outstanding_balance"></p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-xs-4">Staff in charge</label>
                                <div class="col-xs-8">
                                    <p class="form-control-static" data-bind="text: card.staff_in_charge"></p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-xs-4">Time moving to higher debt group</label>
                                <div class="col-xs-8">
                                    <p class="form-control-static" data-bind="text: card.time_moving"></p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-xs-4">Sale (Code - Name)</label>
                                <div class="col-xs-8">
                                    <p class="form-control-static" data-bind="text: card.sale_consultant"></p>
                                </div>
                            </div>
                        
                        </div>
                    </div>
                </div>
            </div>
            <div style="padding: 0; overflow-x: hidden; overflow-y: hidden; min-height: 100%" id="payment_history-content"></div>
            <div style="padding: 0; overflow-x: hidden; overflow-y: hidden; min-height: 100%" id="field_action-content"></div>
            <div style="padding: 0; overflow-x: hidden; overflow-y: hidden; min-height: 100%" id="lawsuit-content"></div>
            <div style="padding: 0; overflow-x: hidden; overflow-y: hidden; min-height: 100%" id="cross_sell-content"></div>
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
    id: '<?= $this->input->get("id") ?>'
});

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
        mainProduct: {},
        card: {},
        mainProductChange: function(e) {
            this.set("mainProduct", e.sender.dataItem());
        },
        cardChange: function(e) {
            this.set("card", e.sender.dataItem());
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
                    {field: "contract_no", operator: "eq", value: this.item.account_number}
                ]
            });
            var query = httpBuildQuery({filter: filter, omc: 1});
            var $content = $("#cross_sell-content");
            if(!$content.find("iframe").length)
                $content.append(`<iframe src='${ENV.baseUrl}manage/data/cross_sell?${query}' style="width: 100%; height: 500px; border: 0"></iframe>`);
        },
    });
    var model = kendo.observable(observable);
    return {
        model: model,
        read: async function() {
            // Built view
            var customerHTMLArray = [];
            var customerModel = await $.get(`${ENV.vApi}model/read`, {
                q: JSON.stringify({filter: {field: "collection", operator: "eq", value: (ENV.type ? ENV.type + "_" : "") + "Customer"}, sort: {field: "index", dir: "asc"}})
            });

            var dataItemFull = await $.get(`${ENV.restApi}customer/${Config.id}`);
            dataItemFull.phoneHTML = gridPhone(dataItemFull.phone, dataItemFull.id, "customer");
            dataItemFull.other_phonesHTML = gridPhone(dataItemFull.other_phones, dataItemFull.id, "customer");
            dataItemFull.createdAtText = gridTimestamp(dataItemFull.createdAt);
            dataItemFull.updatedAtText = gridTimestamp(dataItemFull.updatedAt);
           
            customerModel.data.forEach(doc => {

                if(!doc.sub_type) return;
                let sub_type = JSON.parse(doc.sub_type);
                if(!sub_type.detailShow) return;

                if(doc.field != "name") {
                    switch(doc.type) {
                        case "phone": case "arrayPhone":
                            dataItemFull[doc.field + "HTML"] = gridPhone(dataItemFull[doc.field], dataItemFull.id, "customer");
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
                                    <p class="form-control-static" data-bind="text: item.${doc.field}"></p>
                                </div>
                            </div>`);
                            break;
                    }
                }
            });

            $("#customer-detail-view").html(customerHTMLArray.join(''));
            this.model.set("item", dataItemFull);

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

            this.model.mainProductOption = new kendo.data.DataSource({
                serverFiltering: true,
                filter: {field: "CUS_ID", operator: "eq", value: dataItemFull.LIC_NO},
                transport: {
                    read: ENV.restApi + "main_product",
                    parameterMap: parameterMap
                },
                schema: {
                    data: "data",
                    total: "total",
                    parse: function(res) {
                        $("#main-product-count").html('<span class="text-danger">(' + res.total + ')</span>');
                        if(!res.total) $(".main-product-container").addClass("hidden");
                        return res;
                    }
                }
            })

            this.model.cardOption = new kendo.data.DataSource({
                serverFiltering: true,
                filter: {field: "license_no", operator: "eq", value: dataItemFull.LIC_NO},
                transport: {
                    read: ENV.restApi + "card",
                    parameterMap: parameterMap
                },
                schema: {
                    data: "data",
                    total: "total",
                    parse: function(res) {
                        $("#card-count").html('<span class="text-danger">(' + res.total + ')</span>');
                        if(!res.total) $(".card-container").addClass("hidden");
                        return res;
                    }
                }
            })

            return dataItemFull;
        },
        init: function() {
            this.read().then((dataItemFull) => {
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
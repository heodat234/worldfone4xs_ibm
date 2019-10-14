<div id="detail" data-role="splitter"
             data-panes="[
                { collapsible: true, min: '700px'},
                { collapsible: true, min: '200px', size: '340px' },
             ]"
             data-orientation="horizontal" style="min-height: 540px; overflow-y: auto; margin: 0 -15px">
    <div class="col-sm-9" id="left-detail" style="padding: 5px">
        <!-- Ticket View -->
        <div class="container-fluid">
	        <div class="row" style="padding-top: 10px; display: flex">
                <div class="alert animation-fadeInQuick col-sm-4" style="margin-right: 15px; padding-top: 25px" data-bind="css: {alert-success: Open, alert-danger: Urgent, alert-default: Closed, alert-dark: Invalid, alert-info: Assist, alert-warning: Pending}">@Current Status@: <strong data-bind="text: item.status"></strong></div>
                <div class="alert alert-info animation-fadeInQuick text-center col-sm-8">
                    <a style="color: red; text-transform: uppercase" href="javascript:void(0)" data-bind="click: editTicket"><i>@Edit@</i></a>
                    <b style="font-size: small">[<span data-bind="text: item.ticket_id"></span>]</b> <br> <b style="font-size: medium" data-bind="text: item.title"></b>
                </div>
			</div>
		</div>
        <div data-bind="visible: Closed">
            <table class="table table-striped table-borderless table-vcenter">
                <tbody>
                    <?php $fields = array(
                        "complete_time"     => "@Complete time@",
                        "handle"            => "@Handle@",
                    );
                    foreach ($fields as $key => $value) { ?>
                        <tr>
                            <td class="text-right" style="width: 20%;">
                                <strong><?= $value ?></strong>
                            </td>
                            <td>
                                <?php if(in_array($key, ["complete_time"])) { ?>
                                <span data-bind="html: item.<?= $key ?>Html, visible: item.<?= $key ?>Html"></span>
                                <?php } else { ?>
                                <span data-bind="text: item.<?= $key ?>"></span>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <div class="pull-right" data-bind="invisible: Closed">
	        <a href="javascript:void(0)" class="btn btn-xs btn-default" data-status="Closed" data-bind="click: closeTicket"><i class="fa fa-check"></i> @Flag as@ @Closed@</a>
        </div>
        <div data-bind="invisible: Closed">
            <a href="javascript:void(0)" class="btn btn-xs btn-success" data-status="Open" data-bind="invisible: Open, click: changeStatus"><i class="fa fa-flag"></i> @Flag as@ @Open@</a>
    	    <a href="javascript:void(0)" class="btn btn-xs btn-danger" data-status="Urgent" data-bind="invisible: Urgent, click: changeStatus"><i class="fa fa-flag"></i> @Flag as@ @Urgent@</a>
    	    <a style="background-color: #6C757D; color: #ffffff" href="javascript:void(0)" class="btn btn-xs btn-dark" data-status="Invalid" data-bind="invisible: Invalid, click: changeStatus"><i class="fa fa-flag"></i> @Flag as@ @Invalid@</a>
    	    <a href="javascript:void(0)" class="btn btn-xs btn-warning" data-status="Pending" data-bind="invisible: Pending, click: changeStatus"><i class="fa fa-flag"></i> @Flag as@ @Pending@</a>
    	    <a href="javascript:void(0)" class="btn btn-xs btn-primary" data-bind="click: reAssign">@Assigned@ <b data-bind="text: item.assignView"></b></a>
        </div>
	    <hr style="margin: 10px 0 0">
	    <ul class="media-list media-feed push" style="margin-bottom: 0px !important">
	        <!-- Ticket -->
	        <li class="media">
	            <a href="javascript:void(0)" data-bind="click: openCustomerDetail" class="pull-left">
                    <img src="public/proui/img/placeholders/avatars/avatar.jpg" alt="Avatar" class="img-circle ticket-avatar" data-bind="invisible: customer.avatar">
	                <img alt="Avatar" class="img-circle ticket-avatar" data-bind="visible: customer.avatar, attr: {src: customer.avatar}">
	            </a>
	            <div class="media-body">
                    <span class="text-muted pull-right">
                        <small data-bind="text: item.createdAtFrom"></small>
                    </span>
                    <p class="push-bit">
                        <small><a href="javascript:void(0)" data-bind="click: openCustomerDetail"><b
                                        data-bind="text: item.sender_name"></b></a> @through source@ <a
                                    href="javascript:void(0)" data-bind="text: item.source"></a></small>
                    </p>
                    <div class="row">
                        <div class="col-sm-12">
                            <p class="ticket-content" data-bind="html: item.content"></p>
                            <div class="fade-content"></div>
                        </div>
                        <div class="col-sm-12" style="text-align: center">
                            <button id="show-hide-content-btn" class="btn btn-primary" onclick="showMoreLessContent()"><span id="show-content">@Show more@</span><span id="hide-content" style="display: none">@Show less@</span></button>
                        </div>

                    </div>
                    <div class="row" style="display: none">
                        <div class="col-sm-6">
                            <ul style='list-style-type: none; padding-left: unset' data-template="pnr-template"
                                data-bind="source: item.PNRList"></ul>
                        </div>
                        <div class="col-sm-6">
                            <ul class="list-unstyled" data-bind="source: item.contactPersonInfo" data-template="contact-person-info-template"></ul>
                        </div>
                    </div>
                    <div class="row" data-bind="invisible: isAttachFile">
                        <div class="col-sm-6" data-bind="invisible: isAttachIMG">
                            <h6>@IMAGE@</h6>
                            <ul style="list-style-type: none; padding-left: unset" data-template="list-img-template" data-bind="source: item.listIMG"></ul>
                            <div id="myModal" class="modal">
                                <span class="close">&times;</span>
                                <img class="modal-content" id="img01">
                                <div id="caption"></div>
                            </div>
                        </div>
                        <div class="col-sm-6" data-bind="invisible: isAttachOtherFile">
                            <h6>@FILE@</h6>
                            <ul style="list-style-type: none; padding-left: unset" data-template="list-file-template" data-bind="source: item.listOtherFile"></ul>
                        </div>
                    </div>
	            </div>
	        </li>
	    </ul>

	    <h4 class="fieldset-legend" style="margin: 0 0 20px"><span style="font-weight: 500; background-color: #eaedf1; line-height: 1">@SOLVE@</span></h4>
	    <ul class="media-list media-feed push" data-template="reply-template" data-bind="source: replyTicketSource">
	    </ul>
        <div data-bind="invisible: Invalid">
    	    <ul class="media-list media-feed push" data-bind="invisible: Closed">
    	    	<li class="media" data-bind="invisible: Email">
    	            <a href="javascript:void(0)" class="pull-left">
    	                <img alt="Avatar" class="img-circle ticket-avatar reply-avatar">
    	            </a>
    	            <div class="media-body">
    	                <form>
                            <div class="form-group">
                                <label class="radio-inline">
                                    <input name="sender_type" type="radio" value="agent" checked="checked" autocomplete="off" data-bind="events: {change: changeSenderType}" /><span>@Me@</span>
                                </label>
                                <label class="radio-inline" style="margin-left: 50px">
                                    <input name="sender_type" type="radio" value="customer" autocomplete="off" data-bind="events: {change: changeSenderType}" /><span>@Customer@</span>
                                </label>
                            </div>
    	                    <textarea class="form-control" rows="5" placeholder="@Content@" data-bind="value: ticketReply"></textarea>
    	                    <button type="button" class="btn btn-sm btn-success" data-bind="click: manualReply"><i class="fa fa-reply"></i> @Save@</button>
    	                </form>
    	            </div>
    	        </li>
                <li class="media" data-bind="visible: Email">
                    <a href="javascript:void(0)" class="pull-left">
                        <img alt="Avatar" class="img-circle ticket-avatar reply-avatar">
                    </a>
                    <div class="media-body">
                        <form>
                            <textarea data-role="editor" id="tickets-reply" name="tickets-reply" class="form-control" rows="5" placeholder="Enter your reply" data-bind="value: ticketReply"></textarea>
                            <button type="button" class="btn btn-sm btn-danger" data-bind="click: emailReply"><i class="fa fa-reply"></i> @Reply@ Email</button>
                        </form>
                    </div>
                </li>
    	        <script type="text/javascript">
    	        	$(".reply-avatar").attr("src", $("#avatar-img").attr("src"));
    	        </script>
    	    </ul>
        </div>
		<!-- END Ticket View -->
    </div>
    <div class="col-sm-3" style="height: 80vh; overflow-y: auto; padding: 0">
        <!-- Default Tabs -->
        <div data-role="tabstrip">
            <ul>
                <li class="k-state-active">
                    @Info@
                </li>
                <li>
                	@Customer@
                </li>
                <li>
                    @Note@
                </li>
                <li data-bind="click: openTicketLog">
                	@Log@
                </li>
            </ul>
            <div>
                <table class="table table-striped table-borderless table-vcenter">
                    <tbody>
                        <?php $fields = array(
                            "receive_time"          => "@Receive time@",
                            "priority"              => "@Priority@",
                            "customerFormatText"    => "@Customer format@",
                            "service"               => "@Service@",
                            "requireName"           => "@Require name@",
                            "requirePhone"          => "@Require phone@",
                            "requireEmail"          => "@Require email@",
                            "requireRelation"       => "@Relationship@"
                        );
                        foreach ($fields as $key => $value) { ?>
                            <tr <?php if(in_array($key, ["requireName","requirePhone","requireEmail","requireRelation"])) { echo 'data-bind="visible: item.notOwner"'; } ?>>
                                <td class="text-right" style="width: 40%;">
                                    <strong><?= $value ?></strong>
                                </td>
                                <td>
                                    <?php if(in_array($key, array("customerFormatText","notOwner","iso","receive_time"))) { ?>
                                    <span data-bind="html: item.<?= $key ?>Html, visible: item.<?= $key ?>Html"></span>
                                    <?php } else { ?>
                                    <span data-bind="text: item.<?= $key ?>"></span>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <div>
                <table class="table table-striped table-borderless table-vcenter">
                    <tbody>
                        <?php $fields = array(
                            "name"      => "@Customer name@",
                            "BIRTH_DAY" => "@Birthday@",
                            "phone"     => "@Phone@",
                            "email"     => "@Email@",
                            "address"   => "@Address@"
                        );
                        foreach ($fields as $key => $value) { ?>
                            <tr>
                                <td class="text-right" style="width: 40%;">
                                    <strong><?= $value ?></strong>
                                </td>
                                <td>
                                <?php if($key == "name") { ?>
                                <a href="javascript:void(0)" data-bind="text: customer.<?= $key ?>, click: openCustomerDetail"></a>
                                <?php } else { ?>
                                <span data-bind="text: customer.<?= $key ?>"></span>
                                <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <div style="width: 100%; text-align: center">
                    <a href="javascript:void(0)" class="btn btn-sm btn-success" data-bind="click: openChangeSender">@Change sender@</a>
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
                <hr>
                <p>
                    <h4 class="text-center">@Changelog@</h4>
                    <div data-role="grid"
                        data-auto-bind="false"
                        data-no-records="{
                            template: `<h2 class='text-danger'>@NO DATA@</h2>`
                        }"
                        data-columns="[
                            {field:'action_time', title: '@Time@'},
                            {field:'action_by', title: '@Updated by@'}
                            ]"
                        data-detail-template="log-ticket-template"
                      data-bind="source: ticketLogData, events: {detailInit: logDetailInit}"></div>
                </p>
    		</div>

    	</div>
        <!-- END Default Tabs -->
    </div>
</div>

<style type="text/css">
	.ticket-avatar {
		width: 80px
	}
	.media {
		min-height: 100px;
	}
</style>

<link rel="stylesheet" href="<?= STEL_PATH.'css/detail.css' ?>" type="text/css">

<script type="text/x-kendo-template" id="note-template">
    <div class="row">
        <div class="alert alert-#= HELPER.bsColors[index % HELPER.bsColors.length] #">
            <button type="button" class="close" aria-hidden="true" data-bind="click: removeNote" data-id="#= id #">×</button>
            <p data-bind="text: content"></p>
            <p class="text-right text-muted"><span data-bind="text: createdBy"></span> - <span data-bind="text: createdAtText"></span></p>
        </div>
    </div>
</script>

<script type="text/x-kendo-template" id="logs-template">
    #switch(ticket_type) {
        case 'create':#
            <p>
                <i>@Create by@: </i>
                <b data-bind="text: action_by"></b><br>
                <i>@Create at@: </i>
                <span data-bind="text: createdAtText"></span>
            </p>
            #break;
        case 'update':#
            <p>
                <i>@Update by@: </i>
                <b data-bind="text: action_by"></b><br>
                <i>@Action@:</i><br>
                <span data-template="logs-detail-template" data-bind="source: action"></span>
                <i>@Update at@: </i>
                <span data-bind="text: createdAtText"></span>
            </p>
            #break;
        case 'delete':#
            <p>
                <i>@Update by@: </i>
                <b data-bind="text: action_by"></b><br>
                <i>@Update at@: </i>
                <span data-bind="text: createdAtText"></span>
            </p>
            #break;
    }#
</script>

<script type="text/x-kendo-template" id="logs-detail-template">
    <span style="padding-left: 15px">
        #if(field !== 'updatedBy') {#
         - <i>@#=field#@: </i>
        <b data-bind="text: old_data"></b> @into@ <b data-bind="text: new_data"></b>
        #}#
    </span><br>
</script>

<script type="text/x-kendo-template" id="reply-template">
    <li class="media">
        <a href="javascript:void(0)" class="pull-left">
            <img src="#: ENV.vApi #avatar/#: sender_type #/#: sender_id #" alt="Avatar" class="img-circle ticket-avatar">
        </a>
        <div class="media-body">
            <p class="push-bit">
                <span class="text-muted pull-right">
                    <small data-bind="text: createdAtFrom"></small>
                </span>
                <small><a><b data-bind="text: sender_name"></b></a></small>
            </p>
            <p data-bind="html: content">
            </p>
        </div>
    </li>
</script>

<script id="pnr-template" type="text/x-kendo-template">
    <li style="display: inline-block;"><a href="javascript:void(0)" style="margin-right: 5px" data-bind="text: this" onclick="openPNRDetail(this.text)" class="label label-success"></a></li>
</script>

<script type="text/x-kendo-template" id="contact-person-info-template">
    <li><i class="fa fa-user" aria-hidden="true"></i> <span class="label label-info" data-bind="text: name"></span><span class="pull-right" data-bind="text: phone"></span></br><span data-bind="text: email"></span></li>
</script>

<script type="text/x-kendo-template" id="list-img-template">
    <li style="display:inline;"><img style="width: 50px; height: 50px; cursor: pointer" src="#: url #" onclick="openIMG('#: url #')"></li>
</script>

<script type="text/x-kendo-template" id="list-file-template">
    <li><a class="label label-info" data-bind="attr: {href: url}" download>#: filename #</a></li>
</script>

<script type="text/x-kendo-template" id="log-ticket-template">
    <div class="containter-fluid">
        <table class="table table-striped table-borderless table-vcenter" data-template="log-detail-ticket-template" data-bind="source: listDisplayLogs"></table>
    </div>
</script>

<script type="text/x-kendo-template" id="log-detail-ticket-template">
    <tr>
        <td class="text-right" style="width: 30%;"><strong data-bind="text: title"></strong></td>
        <td data-bind="html: value"></td>
    </tr>
</script>

<script>
var Config = Object.assign(Config, {
	id: '<?= $this->input->get("id") ?>',
	timestamp: '<?= time() ?>',
    logs_view: 5
});


function time_ago(time) {

  switch (typeof time) {
    case 'number':
      break;
    case 'string':
      time = +new Date(time);
      break;
    case 'object':
      if (time.constructor === Date) time = time.getTime();
      break;
    default:
      time = +new Date();
  }
  var time_formats = [
    [60, '@seconds@', 1], // 60
    [120, '1 @minute@ @ago@', '1 @minute@ @from now@'], // 60*2
    [3600, '@minutes@', 60], // 60*60, 60
    [7200, '1 @hour@ @ago@', '1 @hour@ @from now@'], // 60*60*2
    [86400, '@hours@', 3600], // 60*60*24, 60*60
    [172800, '@Yesterday@', '@Tomorrow@'], // 60*60*24*2
    [604800, '@days@', 86400], // 60*60*24*7, 60*60*24
    [1209600, '@Last week@', '@Next week@'], // 60*60*24*7*4*2
    [2419200, '@weeks@', 604800], // 60*60*24*7*4, 60*60*24*7
    [4838400, '@Last month@', '@Next month@'], // 60*60*24*7*4*2
    [29030400, 'months', 2419200], // 60*60*24*7*4*12, 60*60*24*7*4
    [58060800, '@Last year@', '@Next year@'], // 60*60*24*7*4*12*2
    [2903040000, '@years@', 29030400], // 60*60*24*7*4*12*100, 60*60*24*7*4*12
  ];
  var seconds = (+new Date() - time) / 1000,
    token = '@ago@',
    list_choice = 1;

  if (seconds == 0) {
    return '@Just now@'
  }
  if (seconds < 0) {
    seconds = Math.abs(seconds);
    token = '@from now@';
    list_choice = 2;
  }
  var i = 0,
    format;
  while (format = time_formats[i++])
    if (seconds < format[0]) {
      if (typeof format[2] == 'string')
        return format[list_choice];
      else
        return Math.floor(seconds / format[2]) + ' ' + format[1] + ' ' + token;
    }
  return time;
}

var Detail = function() {
    var observable = Object.assign(Config.observable, {
        item: {},
        customer: {},
        isAttachFile: true,
        isAttachIMG: true,
        isAttachOtherFile: true,
        changeStatus: function(e) {
            var status = $(e.currentTarget).data("status");
            $.ajax({
                url: ENV.vApi + "ticket/update/" + this.get("item.id"),
                type: "PUT",
                data: JSON.stringify({status: status}),
                success: (response) => {
                    if(response.status) {
                        notificationAfterRefresh("@Change status@ @success@", "success");
                        if(typeof response.data.isChangeAppTicketStatus !== 'undefined' && response.data.isChangeAppTicketStatus) {
                            $.ajax({
                                url: ENV.vApi + "Appservices/pushStatusUpdateNoti",
                                type: "POST",
                                data: {ticket_id: this.get('item.ticket_id')},
                                success: (response) => {
                                    location.reload();
                                },
                                error: errorDataSource
                            })
                        }
                        else {
                            location.reload();
                        }
                    }
                    else {
                        location.reload();
                    }
                },
                error: errorDataSource
            })
        },
        closeTicket: function() {
            openForm({title: "@Close@ ticket", width: 400});
            this.closeTicketAsync();
        },
        closeTicketAsync: async function() {
            var formHtml = await $.ajax({
                url: Config.templateApi + "ticket/closeform",
                error: errorDataSource
            });
            var model = {
                id: this.get("item.id"),
                item: {complete_time: new Date(), status: "Closed"},
                save: function() {
                    var data = this.get("item");
                    $.ajax({
                        url: ENV.vApi + "ticket/update/" + this.get("id"),
                        type: "PUT",
                        data: JSON.stringify(data),
                        success: (response) => {
                            if(response.status) {
                                notificationAfterRefresh("@Close@ @success@", "success");
                                // location.reload();
                            }
                        },
                        error: errorDataSource
                    })
                }
            };
            kendo.destroy($("#right-form"));
            $("#right-form").empty();
            var kendoView = new kendo.View(formHtml, { wrap: false, model: model, evalTemplate: false });
            kendoView.render($("#right-form"));
        },
        noteData: new kendo.data.DataSource({
            serverFiltering: true,
            serverSorting: true,
            filter: [
                {field: "foreign_id", operator: "eq", value: Config.id}
            ],
            sort: [{field: "createdAt", dir: "desc"}],
            transport: {
                read: `${ENV.restApi}ticket_note`,
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
                    title: "Are you sure?",
                    text: `Save this note.`,
                    icon: "warning",
                    buttons: true,
                    dangerMode: false,
                })
                .then((sure) => {
                    if (sure) {
                        e.currentTarget.value = "";
                        $.ajax({
                            url: `${ENV.restApi}ticket_note`,
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
                        url: `${ENV.restApi}ticket_note/${id}`,
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
        replyTicketSource: new kendo.data.DataSource({
            serverFiltering: true,
            serverSorting: true,
            sort: [{field: "createdAt", dir: "asc"}],
            transport: {
                read: `${ENV.restApi}ticket_reply`,
                parameterMap: parameterMap
            },
            schema: {
                data: "data",
                total: "total",
                parse: function(response) {
                    response.data.map(function(doc, idx){
                        doc.index = idx;
                        doc.createdAtText = (kendo.toString(new Date(doc.createdAt * 1000), "dd/MM/yy H:mm") ||  "").toString();
                        doc.createdAtFrom = time_ago(new Date(doc.createdAt * 1000));
                    })
                    return response;
                }
            }
        }),
        openCustomerDetail: function(e) {
        	window.open(`manage/customer/#/detail/${this.customer.id}`,'_blank',null);
        },
        manualReply: function(e) {
            var sender_type = this.sender_type;
            if(this.get("ticketReply")) {
                var item = {
                    ticket_id: this.get("item.ticket_id"),
                    content: this.get("ticketReply"),
                    sender_id: (sender_type == "customer") ? this.get("customer.id") : ENV.extension,
                    sender_name: (sender_type == "customer") ? this.get("customer.name") : ENV.agentname,
                    sender_type: sender_type
                }
                swal({
                    title: "@Are you sure@?",
                    text: `@Reply@ @this ticket@.`,
                    icon: "warning",
                    buttons: true,
                    dangerMode: false,
                })
                .then(sure => {
                    if(sure) {
                        $.ajax({
                            url: ENV.restApi + "ticket_reply",
                            type: "POST",
                            data: JSON.stringify(item),
                            success: (response) => {
                                if(response.status) {
                                    this.replyTicketSource.read();
                                    syncDataSource();
                                }
                            },
                            error: errorDataSource
                        })
                    }
                })
            } else notification.show("Empty", "error");
        },
        callCustomer: function() {
            makeCallWithDialog(this.customer.phone, this.item.ticket_id, "ticket");
        },
        sender_type: "agent",
        changeSenderType: function(e) {
            let value = e.currentTarget.value;
            this.set("sender_type", value);
            if(value == "customer") {
                if(this.get("customer.avatar")) 
                    $(".reply-avatar").attr("src", this.get("customer.avatar"));
                else $(".reply-avatar").attr("src", ENV.vApi + "avatar/customer/" + this.get("customer.id"));
            } else $(".reply-avatar").attr("src", $("#avatar-img").attr("src"));
        },
        callReply: function(e) {
            var sender_type = this.sender_type;
            if(this.get("ticketReply")) {
                var item = {
                    ticket_id: this.get("item.ticket_id"),
                    content: this.get("ticketReply"),
                    sender_id: (sender_type == "customer") ? this.get("customer.id") : ENV.extension,
                    sender_name: (sender_type == "customer") ? this.get("customer.name") : ENV.agentname,
                    sender_type: sender_type
                }
                swal({
                    title: "@Are you sure@?",
                    text: `@Reply@ @this ticket@.`,
                    icon: "warning",
                    buttons: true,
                    dangerMode: false,
                })
                .then(sure => {
                    if(sure) {
                        $.ajax({
                            url: ENV.restApi + "ticket_reply",
                            type: "POST",
                            data: JSON.stringify(item),
                            success: (response) => {
                                if(response.status) {
                                    this.replyTicketSource.read();
                                    syncDataSource();
                                }
                            },
                            error: errorDataSource
                        })
                    }
                })
            } else notification.show("Empty", "error");
        },
        assignOption: new kendo.data.DataSource({
            transport: {
                read: ENV.vApi + "widget/user_list",
                parameterMap: parameterMap
            },
            schema: {
                data: "data",
                total: "total"
            }
        }),
        assignTicket: function() {
            this.set("visibleAssign", true);
        },
        changeAssign: function(e) {
            var assign = this.get("assign");
            $.ajax({
                url: ENV.vApi + "ticket/update/" + this.get("item.id"),
                type: "PUT",
                data: JSON.stringify({assign: assign}),
                success: (response) => {
                    if(response.status) {
                        notificationAfterRefresh("@Assign@ @success@", "success");
                        location.reload();
                    }
                },
                error: errorDataSource
            })
        },
        reAssign: function() {
            openForm({title: "@Reassign@", width: 400});
            this.reAssignAsync();
        },
        reAssignAsync: async function() {
            var formHtmlUrl = '';
            var user_role = this.get("item.userRole");
            switch (user_role) {
                case 'admin':
                    formHtmlUrl = Config.templateApi + "ticket/admin_reassignform";
                    break;
                case 'supervisor':
                    formHtmlUrl = Config.templateApi + "ticket/supervisor_reassignform";
                    break;
                default:
                    formHtmlUrl = Config.templateApi + "ticket/agent_reassignform";
            }
            var formHtml = await $.ajax({
                url: formHtmlUrl,
                error: errorDataSource
            });
            var group_id_global = null;
            var ticket_id = this.get("item.id");
            var model = {
                group_id: null,
                extension: null,
                reassignOptionAgent: new kendo.data.DataSource({
                    transport: {
                        read: {
                            url: `${ENV.vApi}ticket/getGroupInfoForAssign`,
                            data: function() {
                                return {
                                    'isGroup': false,
                                    'group_id': group_id_global
                                }
                            }
                        },
                        parameterMap: parameterMap
                    }
                }),
                reassignOptionGroup: new kendo.data.DataSource({
                    transport: {
                        read: {
                            url: `${ENV.vApi}ticket/getGroupInfoForAssign`,
                            data: {
                                'isGroup': true,
                            }
                        },
                        parameterMap: parameterMap
                    },
                }),
                reReadAgent(e) {
                    if(user_role !== 'supervisor') {
                        group_id_global = this.get('group_id');
                        this.reassignOptionAgent.read();
                    }
                },
                save: function() {
                    var group_id = this.get('group_id');
                    var extension = this.get('extension');
                    var updateInfo = {};
                    if(typeof group_id !== 'undefined' && group_id !== null && group_id !== '') {
                        updateInfo.assign = group_id;
                        updateInfo.assignGroup = group_id;
                    }
                    if(typeof extension !== 'undefined' && extension !== null && extension !== '') {
                        updateInfo.assign = extension;
                    }
                    if((typeof group_id === 'undefined' || group_id === null || group_id === '') && (typeof extension === 'undefined' || extension === null || extension === '')) {
                        // notificationAfterRefresh("@Assign@ @error@", "error");
                        notification.show('@Xin vui lòng chọn extension hoặc nhóm cần assign@', 'error');
                        return false;
                    }
                    if(user_role === 'supervisor' && typeof group_id !== 'undefined' && group_id !== null && group_id !== '' && typeof extension !== 'undefined' && extension !== null && extension !== '') {
                        notification.show('@Trưởng nhóm chỉ có thể chọn nhóm HOẶC agent để assign@', 'error');
                        return false;
                    }
                    if((typeof extension !== 'undefined' && extension !== null && extension !== '') && (typeof group_id === 'undefined' || group_id === null || group_id === '')) {
                        updateInfo.assign = extension;
                        updateInfo.assignGroup = '';
                    }

                    $.ajax({
                        url: ENV.vApi + "ticket/update/" + ticket_id,
                        type: "PUT",
                        data: JSON.stringify(updateInfo),
                        success: (response) => {
                            if(response.status) {
                                notificationAfterRefresh("@Assign@ @success@", "success");
                                location.reload();
                            }
                        },
                        error: errorDataSource
                    })
                }
            };
            kendo.destroy($("#right-form"));
            $("#right-form").empty();
            var kendoView = new kendo.View(formHtml, { wrap: false, model: model, evalTemplate: false });
            kendoView.render($("#right-form"));
        },
        groupInfo: new kendo.data.DataSource({
            transport: {
                read: {
                    url: `${ENV.vApi}ticket/getTicketLogsById`,
                    type: 'POST',
                    data: function () {
                        return {id: Config.id, 'logs_view': Config.logs_view};
                    }
                },
                parameterMap: parameterMap
            },
            schema: {
                data: 'data',
                total: 'total',
                parse: function (response) {
                    response.data.map(function (doc, idx) {
                        doc.index = idx;
                        doc.createdAtText = (kendo.toString(new Date(doc.action_time * 1000), "dd/MM/yy H:mm") || "").toString();
                        doc.createdAtFrom = time_ago(new Date(doc.action_time * 1000));
                        doc.action = [];
                        if (typeof doc.update_field !== 'undefined') {
                            doc.update_field.map(function (field, index) {
                                doc.action.push({
                                    field: field,
                                    old_data: doc.old_data[field],
                                    new_data: doc.new_data[field]
                                });
                            });
                        }
                    });
                    return response;
                }
            }
        }),
        openChangeSender: function() {
            openForm({title: "@Change@ sender", width: 400});
            this.openChangeSenderAsync();
        },
        openChangeSenderAsync: async function() {
            var formHtml = await $.ajax({
                url: Config.templateApi + "ticket/changeSender",
                error: errorDataSource
            });
            var model = {
                id: this.get("item.id"),
                item: {},
                senderOption: () => dataSourceDropDownList("Customer", ["name"]),
                senderChange: function(e) {
                    var item = e.sender.dataItem();
                    if(typeof item !== 'undefined') {
                        this.set("item.sender_id", item.id);
                    }
                },
                save: function() {
                    var data = this.get("item");

                    $.ajax({
                        url: ENV.vApi + "ticket/update/" + this.get("id"),
                        type: "PUT",
                        data: JSON.stringify(data),
                        success: (response) => {
                            if(response.status) {
                                notificationAfterRefresh("@Change@ @sender@ @success@", "success");
                                location.reload();
                            }
                            else {
                                notification.show("@Error@", "error");
                            }
                        },
                        error: errorDataSource
                    })
                },
            };
            kendo.destroy($("#right-form"));
            $("#right-form").empty();
            var kendoView = new kendo.View(formHtml, { wrap: false, model: model, evalTemplate: false });
            kendoView.render($("#right-form"));
        },
        logDetailInit: function(e) {
            var dataItem = e.sender.dataSource.getByUid(e.masterRow.data("uid"));
            kendo.bind(e.detailRow, dataItem);
        },
        ticketLogData: new kendo.data.DataSource({
            serverFiltering: true,
            serverSorting: true,
            filter: [
                {field: "ticket_id", operator: "eq", value: Config.id},
                {field: "ticket_type", operator: "eq", value: "update"}
            ],
            sort: [{field: "action_time", dir: "desc"}],
            transport: {
                read: `${ENV.vApi}/ticket/getTicketLogsById`,
                parameterMap: parameterMap
            },
            schema: {
                data: "data",
                total: "total",
                parse: function(response) {
                    var listDisplayField = {
                        title: '@Title@',
                        status: '@Status@',
                        source: '@Source@',
                        sender_name: '@Sender@',
                        customerFormat: '@Customer format@',
                        assignView: '@Assign@',
                        priority: '@Priority@',
                        content: '@Content@',
                        service: '@Service@',
                        contactPersonInfo: '@Contact person info@'
                    };
                    response.data.map(function(doc, idx){
                        doc.action_time = (kendo.toString(new Date(doc.action_time * 1000), "dd/MM/yy H:mm:ss") ||  "").toString();
                        var listLogDetail = arrayColumn(doc.log_detail, 'new_data', 'field');
                        doc.listDisplayLogs = [];
                        Object.keys(listDisplayField).forEach(function(idxField) {
                            if(idxField !== 'contactPersonInfo' && typeof listLogDetail[idxField] !== 'undefined') {
                                doc.listDisplayLogs.push({
                                    title: listDisplayField[idxField],
                                    value: (typeof listLogDetail[idxField] == 'Array') ? gridArray(listLogDetail[idxField]) : `<span>` + listLogDetail[idxField] + `</span>`
                                });
                            }
                            if(idxField === 'contactPersonInfo' && typeof listLogDetail['contactPersonInfo'] !== 'undefined') {
                                var contactPersonInfoHTML = '';
                                listLogDetail['contactPersonInfo'].map((contact, contactIdx) => {
                                    contactPersonInfoHTML += `<div>
                                                                  <span>` + contact.name + `</span><br>
                                                                  <span>` + contact.phone + `</span><br>
                                                                  <span>` + contact.email + `</span><br>
                                                              </div><br>`;
                                });
                                doc.listDisplayLogs.push({
                                    title: listDisplayField['contactPersonInfo'],
                                    value: contactPersonInfoHTML
                                });
                            }
                        });
                    });
                    return response;
                }
            }
        }),
        openTicketLog: function() {
            this.ticketLogData.read();
        },
        editTicket: function() {
            openForm({title: "@Edit@ ticket " + this.item.ticket_id, width: 700});
            var data = this.item.toJSON();
            if(data.content && data.content.length > 500) delete data.content;
            editForm(data);
        },
    });
    var model = kendo.observable(observable);
	return {
        model: model,
		read: async function() {
            var listCustomerFormatTemp = arrayJsonData(['Ticket', 'customer format']);
            var listCustomerFormat = arrayColumn(listCustomerFormatTemp['data'], 'text', 'value');
            var dataItemFull = await $.get(`${ENV.vApi}ticket/detail/${Config.id}`);
            dataItemFull.createdAtFrom = time_ago(new Date(dataItemFull.createdAt * 1000));
            dataItemFull.createdAtText = gridTimestamp(dataItemFull.createdAt);
            dataItemFull.updatedAtText = gridTimestamp(dataItemFull.updatedAt);
            dataItemFull.customerFormatText = [];
            $.each(dataItemFull.customerFormat, function(formatKey, formatValue) {
                dataItemFull.customerFormatText.push(listCustomerFormat[formatValue]);
            });
            dataItemFull.customerFormatTextHtml = gridArray(dataItemFull.customerFormatText);
            dataItemFull.notOwnerHtml = gridBoolean(dataItemFull.notOwner);
            dataItemFull.isoHtml = gridBoolean(dataItemFull.iso);
            dataItemFull.receive_timeHtml = gridTimestamp(dataItemFull.receive_time);
            dataItemFull.complete_timeHtml = gridTimestamp(dataItemFull.complete_time);
            dataItemFull.listIMG = [];
            dataItemFull.listOtherFile = [];
            if(typeof dataItemFull.assignView === 'undefined' || dataItemFull.assignView === null || dataItemFull.assignView === '' ) {
                dataItemFull.assignView = '@Not assign@';
            }
            if(typeof dataItemFull.listUploadFile !== 'undefined') {
                var listIMG = [];
                var listOtherFile = [];
                var listIMGExtension = ['jpg', 'png', 'tiff', 'gif', 'bmp'];
                $.each(dataItemFull.listUploadFile, function (key, value) {
                    var extension = extractExtensionFromFileNameString(value);
                    if(listIMGExtension.indexOf(extension) !== -1) {
                        var filename = getFileNameFromFullUrl(value);
                        listIMG.push({url: value, filename: filename});
                    }
                    else {
                        var filename = getFileNameFromFullUrl(value);
                        listOtherFile.push({url: value, filename: filename});
                    }
                });
                dataItemFull.listIMG = listIMG;
                dataItemFull.listOtherFile = listOtherFile;
                if(dataItemFull.listIMG.length > 0) {
                    this.model.set('isAttachIMG', false);
                }
                if(dataItemFull.listOtherFile.length > 0) {
                    this.model.set('isAttachOtherFile', false);
                }

                this.model.set("isAttachFile", false);
            }
            if(typeof dataItemFull.images !== 'undefined') {
                var listIMG = [];
                $.each(dataItemFull.images, function(key, value) {
                    listIMG.push({url: "https://apps-demobh.worldfone.vn/img/index?uri=" + value, filename: value});
                });
                dataItemFull.listIMG = listIMG;
                this.model.set("isAttachFile", false);
                this.model.set('isAttachIMG', false);
            }
            this.model.set(dataItemFull.status, true);
			/* if(typeof dataItemFull.source !== 'undefined') {
				this.model.set(dataItemFull.source, true);
			} */
            this.model.set("item", dataItemFull);
            this.model.replyTicketSource.filter({field: "ticket_id", operator: "eq", value: dataItemFull.ticket_id});
            return dataItemFull;
		},
		init: function() {
            this.read().then((dataItem) => {
                kendo.bind($("#detail"), this.model);
                // show hide ticket content button
                if($('.ticket-content')[0].scrollHeight <= 200) {
                    $('.fade-content').css('display', 'none');
                    $('#show-hide-content-btn').css('display', 'none');
                }
                // show hide ticket content button

                $.get(`${ENV.restApi}customer/${dataItem.sender_id}`, (customer) => {
                	this.model.set("customer", customer);
                });
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
            });
		}
	}
}();

Detail.init();

// async function editForm(ele) {
//     var dataItemFull = await $.get(`${Config.crudApi + Config.collection}/${Config.id}`),
//         formHtml = await $.ajax({
//             url: Config.templateApi + Config.collection + "/form",
//             error: errorDataSource
//         });

//     var model = Object.assign(Config.observable, {
//         item: JSON.stringify(ele),
//         // item: dataItemFull,
//         // save: function() {
//         //     var data = this.item.toJSON();
//         //     $.ajax({
//         //     	url: `${Config.crudApi + Config.collection}/${Config.id}`,
//         //     	type: "PUT",
//         //     	data: JSON.stringify(data),
//         //         contentType: "application/json; charset=utf-8",
//         //     	success: (response) => {
//         //             if(response.status) {
//         //                 notificationAfterRefresh("@Edit@ @Customer@ @Success@", "success");
//         //         		location.reload();
//         //             } else notification.show("@Error@", "error");
//         //     	},
//         //     	error: errorDataSource
//         //     })
//         // }
//     });
//     // kendo.destroy($("#right-form"));
//     // $("#right-form").empty();
//     // var kendoView = new kendo.View(formHtml, { wrap: false, model: model, evalTemplate: false });
//     // kendoView.render($("#right-form"));
//     // $rightForm = $("#right-form");
//     // var formHtml = await $.ajax({
//     //     url: ENV.templateApi + "ticket/formAutoFill",
//     //     data: {doc: option},
//     //     error: errorDataSource
//     // });
//     // kendo.destroy($rightForm);
//     // $rightForm.empty();
//     // $rightForm.append(formHtml);
// }

async function editForm(option = {}) {
    $rightForm = $("#right-form");
    var formHtml = await $.ajax({
        url: ENV.templateApi + "ticket/formAutoFill",
        data: {doc: option},
        error: errorDataSource
    });
    kendo.destroy($rightForm);
    $rightForm.empty();
    $rightForm.append(formHtml);
}

function openPNRDetail(pnr_code) {
    window.open(`manage/ticket/pnrDetail?id=${pnr_code}`,'_blank',null);
}

function openIMG(url) {
    // Get the modal
    var modal = document.getElementById("myModal");

// Get the image and insert it inside the modal - use its "alt" text as a caption
//     var img = document.getElementById("myImg");
    var modalImg = document.getElementById("img01");
    // var captionText = document.getElementById("caption");
    modal.style.display = "block";
    modalImg.src = url;
    // captionText.innerHTML = this.alt;

// Get the <span> element that closes the modal
    var span = document.getElementsByClassName("close")[0];

// When the user clicks on <span> (x), close the modal
    span.onclick = function() {
        modal.style.display = "none";
    }
}

function showMoreLessContent() {
    if($('.ticket-content').height() <= 200) {
        $('.ticket-content').css('max-height', '9000px');
        $('#show-content').css('display', 'none');
        $('.fade-content').css('display', 'none');
        $('#hide-content').css('display', 'block');
    }
    else {
        $('.ticket-content').css('max-height', '200px');
        $('#show-content').css('display', 'block');
        $('.fade-content').css('display', 'block');
        $('#hide-content').css('display', 'none');
    }
}
</script>

<style>
    /* Style the Image Used to Trigger the Modal */
    #myImg {
        border-radius: 5px;
        cursor: pointer;
        transition: 0.3s;
    }

    #myImg:hover {opacity: 0.7;}

    /* The Modal (background) */
    .modal {
        display: none; /* Hidden by default */
        position: fixed; /* Stay in place */
        z-index: 99999; /* Sit on top */
        padding-top: 100px; /* Location of the box */
        left: 0;
        top: 0;
        width: 100%; /* Full width */
        height: 100%; /* Full height */
        overflow: auto; /* Enable scroll if needed */
        background-color: rgb(0,0,0); /* Fallback color */
        background-color: rgba(0,0,0,0.9); /* Black w/ opacity */
    }

    /* Modal Content (Image) */
    .modal-content {
        margin: auto;
        display: block;
        width: 80%;
        max-width: 700px;
    }

    /* Caption of Modal Image (Image Text) - Same Width as the Image */
    #caption {
        margin: auto;
        display: block;
        width: 80%;
        max-width: 700px;
        text-align: center;
        color: #ccc;
        padding: 10px 0;
        height: 150px;
    }

    /* Add Animation - Zoom in the Modal */
    .modal-content, #caption {
        animation-name: zoom;
        animation-duration: 0.6s;
    }

    @keyframes zoom {
        from {transform:scale(0)}
        to {transform:scale(1)}
    }

    /* The Close Button */
    .close {
        position: absolute;
        top: 15px;
        right: 35px;
        color: #f1f1f1;
        font-size: 40px;
        font-weight: bold;
        transition: 0.3s;
    }

    .close:hover,
    .close:focus {
        color: #bbb;
        text-decoration: none;
        cursor: pointer;
    }

    /* 100% Image Width on Smaller Screens */
    @media only screen and (max-width: 700px){
        .modal-content {
            width: 100%;
        }
    }

    .alert-dark {
        background-color: #6C757D;
        color: #ffffff
    }

    .ticket-content {
        max-height: 200px;
        overflow: hidden;
        transition: max-height .5s ease;
    }

    .fade-content {
        background-image: linear-gradient(to bottom, rgba(255, 255, 255, 0) 0%, rgba(255, 255, 255, 1) 75%);
        height: 100px;
        margin-top: -100px;
        position: relative;
    }
</style>
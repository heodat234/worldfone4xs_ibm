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
                    <b style="font-size: small">[<span data-bind="text: item.ticket_id"></span>]</b> <br> <b style="font-size: medium" data-bind="text: item.title"></b>
                </div>
            </div>
		</div>
	    <a href="javascript:void(0)" class="btn btn-xs btn-default pull-right" data-status="Closed" data-bind="invisible: Closed, click: changeStatus"><i class="fa fa-check"></i> @Flag as@ @Closed@</a>
        <div data-bind="invisible: Closed">
            <a href="javascript:void(0)" class="btn btn-xs btn-success" data-status="Open" data-bind="invisible: Open, click: changeStatus"><i class="fa fa-flag"></i> @Flag as@ @Open@</a>
    	    <a href="javascript:void(0)" class="btn btn-xs btn-danger" data-status="Urgent" data-bind="invisible: Urgent, click: changeStatus"><i class="fa fa-flag"></i> @Flag as@ @Urgent@</a>
            <a style="background-color: #6C757D; color: #ffffff" href="javascript:void(0)" class="btn btn-xs btn-dark" data-status="Invalid" data-bind="invisible: Invalid, click: changeStatus"><i class="fa fa-flag"></i> @Flag as@ @Invalid@</a>
            <a href="javascript:void(0)" class="btn btn-xs btn-warning" data-status="Pending" data-bind="invisible: Pending, click: changeStatus"><i class="fa fa-flag"></i> @Flag as@ @Pending@</a>
        </div>
	    <hr>
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
                    <div class="row">
                        <div class="col-sm-6">
                            <ul style='list-style-type: none; padding-left: unset' data-template="pnr-template"
                                data-bind="source: item.PNRList"></ul>
                        </div>
                        <div class="col-sm-6">
                            <p>
                            <ul class="list-unstyled" data-bind="source: item.contactPersonInfo" data-template="contact-person-info-template"></ul>
                            </p>
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
            <!-- END Ticket -->
        </ul>
	    <h4 class="fieldset-legend" style="margin: 0 0 20px"><span style="font-weight: 500; background-color: #eaedf1; line-height: 1">@SOLVE@</span></h4>
	    <ul class="media-list media-feed push" data-template="reply-template" data-bind="source: replyTicketSource">
	    </ul>
        <div data-bind="invisible: Invalid">
    	    <ul class="media-list media-feed push" data-bind="invisible: Closed">
    	    	<li class="media" data-bind="visible: Manual">
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
                <li class="media" data-bind="visible: Call">
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
                            <button type="button" class="btn btn-sm btn-default" data-bind="click: callCustomer"><i class="gi gi-earphone sidebar-nav-icon"></i> @Call@</button>
                            <button type="button" class="btn btn-sm btn-success" data-bind="click: callReply"><i class="fa fa-reply"></i> @Save@</button>
                        </form>
                    </div>
                </li>
                <li class="media" data-bind="visible: Chat">
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
                	@Customer@
                </li>
                <li>
                    @Note@
                </li>
                <li>
                	@Log@
                </li>
            </ul>
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

<script>
var Config = Object.assign(Config, {
	id: '<?= $this->input->get("id") ?>',
	timestamp: '<?= time() ?>'
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
    [60, 'seconds', 1], // 60
    [120, '1 minute ago', '1 minute from now'], // 60*2
    [3600, 'minutes', 60], // 60*60, 60
    [7200, '1 hour ago', '1 hour from now'], // 60*60*2
    [86400, '@hours@', 3600], // 60*60*24, 60*60
    [172800, '@Yesterday@', 'Tomorrow'], // 60*60*24*2
    [604800, 'days', 86400], // 60*60*24*7, 60*60*24
    [1209600, '@Last week@', 'Next week'], // 60*60*24*7*4*2
    [2419200, 'weeks', 604800], // 60*60*24*7*4, 60*60*24*7
    [4838400, 'Last month', 'Next month'], // 60*60*24*7*4*2
    [29030400, 'months', 2419200], // 60*60*24*7*4*12, 60*60*24*7*4
    [58060800, 'Last year', 'Next year'], // 60*60*24*7*4*12*2
    [2903040000, 'years', 29030400], // 60*60*24*7*4*12*100, 60*60*24*7*4*12
  ];
  var seconds = (+new Date() - time) / 1000,
    token = '@ago@',
    list_choice = 1;

  if (seconds == 0) {
    return 'Just now'
  }
  if (seconds < 0) {
    seconds = Math.abs(seconds);
    token = 'from now';
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
            if(value == "customer" && this.get("customer.avatar")) {
                $(".reply-avatar").attr("src", this.get("customer.avatar"));
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
        askAssist: function(e) {
            $.ajax({
                url: ENV.restApi + "ticket/" + this.get("item.id"),
                type: "PUT",
                data: JSON.stringify({status: "Assist"}),
                success: (response) => {
                    if(response.status) {
                        notificationAfterRefresh("@Ask for Assistance@ @success@", "success");
                        location.reload();
                    }
                },
                error: errorDataSource
            })
        },
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
                }
            };
            kendo.destroy($("#right-form"));
            $("#right-form").empty();
            var kendoView = new kendo.View(formHtml, { wrap: false, model: model, evalTemplate: false });
            kendoView.render($("#right-form"));
        },
    });
    var model = kendo.observable(observable);
	return {
        model: model,
		read: async function() {
            var dataItemFull = await $.get(`${ENV.vApi}ticket/detail/${Config.id}`);
            dataItemFull.createdAtFrom = time_ago(new Date(dataItemFull.createdAt * 1000));
            dataItemFull.createdAtText = gridTimestamp(dataItemFull.createdAt);
            dataItemFull.updatedAtText = gridTimestamp(dataItemFull.updatedAt);
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

                console.log(dataItemFull.listIMG);
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
            console.log(dataItemFull.listUploadFile);
            this.model.set(dataItemFull.status, true);
            this.model.set("item", dataItemFull);
            this.model.replyTicketSource.filter({field: "ticket_id", operator: "eq", value: dataItemFull.ticket_id});
            return dataItemFull;
		},
		init: function() {
            this.read().then((dataItem) => {
                kendo.bind($("#detail"), this.model);

                // show hide ticket content button
                console.log($('.ticket-content')[0].scrollHeight);
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
    console.log($('.ticket-content').height());
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
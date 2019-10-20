<li class="dropdown" id="chat-status-widget" data-toggle="tooltip" data-placement="left" title="@Chat status@">
    <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown" data-bind="css: {disabled: item.disabled}">
        <i class="gi gi-snowflake fa-spin text-muted" data-bind="visible: item.WAI"></i>
        <i class="gi gi-comments text-success" data-bind="visible: item.AVA" style="display: none; margin-top: 0"></i>
        <i class="gi gi-circle_minus text-warning" data-bind="visible: item.BUS" style="display: none; margin-top: -2px"></i>
        <span class="caret"></span>
        <small data-bind="text: item.substatus"></small>
    </a>
    <ul class="dropdown-menu dropdown-custom dropdown-options">
        <li class="dropdown-header" data-bind="css: {disabled: item.AVA}"><a href="javascript:void(0)" data-bind="click: changeStatus" data-code="1"><i class="gi gi-comments text-success"></i> <span class="label label-success">@Ready@</span><span data-bind="text: item.time, visible: item.AVA"></span></a></li>
        <li data-bind="css: {disabled: item.BUS}"><a href="javascript:void(0)" data-bind="click: changeStatus" data-code="0"><i class="gi gi-circle_minus text-warning"></i> <span class="label label-warning">@Busy@</span><span data-bind="text: item.time, visible: item.BUS"></span></a></li>
    </ul>
</li>
<script type="text/javascript">
	function changeStatusChat(code = 1, substatus = null) {
		$.ajax({
	        url: ENV.vApi + "chat/change_status_chat",
	        data: kendo.stringify({statuscode: code, substatus: substatus}),
	        contentType: "application/json; charset=utf-8",
	        type: "POST",
	        success: function(e) {
	            notification.show(e.message, e.status ? "success" : "error");
	        },
	        error: errorDataSource
	    })
	}
    function chatStatusWidget(e) {
        var chatStatusObeservable = kendo.observable({
            item: {WAI: true},
            changeStatus: function(e) {
                if(!$(e.currentTarget).closest("li").hasClass("disabled"))
                {
                    this.changeStatusAsync(e);
                }
            }, 
            changeStatusAsync: async function(e) {
				var code = e.currentTarget.dataset.code;
                var status = await $.get(`${ENV.vApi}chatstatuscode/get_by_value/${code}`);
                if(status.sub && status.sub.length) {
                    var subStatusOption = status.sub;
                    var buttons = {cancel: true};
                    
                    for (var i = 0; i < subStatusOption.length; i++) {
                        buttons[i] = {text: subStatusOption[i]};
                    }
                    var type = swal({
                        title: "@Choose your reason@.",
                        text: `@Why you change to@ ${status.text}?`,
                        icon: "info",
                        buttons: buttons
                    }).then(index => {
                        if(index !== null && index !== false) {
                            var substatus = subStatusOption[index];
                            changeStatusChat(code, (substatus || "").toString() );
                        }
                    })
                } else changeStatusChat(code);
            }
        })
        kendo.bind($("#chat-status-widget"), chatStatusObeservable);
        var data = JSON.parse(e.data);
        if(!data) {
            // Loi khi chua co bang agent status code
            console.log("No agent status code");
            $.get(ENV.vApi + "chatstatuscode/init");
            return;
        }
        data.AVA = data.statuscode;
        data.BUS = !data.statuscode;
        var countTime = data.lastupdate - data.starttime;
        var d = new Date();
        d.setHours(0,0,0,0);
        data.time =  " - " + kendo.toString(new Date(d.getTime() + (countTime) * 1000), "H:mm:ss");
        chatStatusObeservable.set("item", data);
    }
</script>
<li class="dropdown" id="queue" data-toggle="tooltip" data-placement="left" title="@Softphone state@">
    <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown">
        <label class="label label-default" data-bind="css: {success: item.ready, danger: item.dnd, warning: item.ringing, info: item.oncall}"><b data-bind="text: item.extension">___</b></label>
        <span class="caret"></span>
    </a>
    <ul class="dropdown-menu dropdown-custom dropdown-options" data-bind="source: item.queues.queue, visible: item.queues.queue.length" data-template="queue-template">
    </ul>
    <ul class="dropdown-menu dropdown-custom dropdown-options" data-bind="invisible: item.queues.queue.length">
        <li class="dropdown-header"><a>@Not belong to any queue@</a></li>
    </ul>
</li>
<script id="queue-template" type="text/x-kendo-template"> 
    <li>
        <a href="javascript:void(0)">
        Queue: <span class="label label-success" data-bind="text: queuename, attr: {title: queuemembership}, css: {danger: queuememberpaused}"></span>
        <span class="label label-default" data-bind="text: queuememberstatus, attr: {title: queuememberstatusText}"></span>
        <button class="btn btn-xs btn-default text-center pull-right hidden" style="margin-top: -2px; padding: 1px 1px 1px 5px; line-height: 1.2" data-bind="click: changeQueue, attr: {title: queuename, data-status: queuememberpaused}">
            <i class="fa fa-pause" data-bind="invisible: queuememberpaused"></i>
            <i class="fa fa-play" data-bind="visible: queuememberpaused"></i>
        </button>
        </a>
    </li>
</script>
<script type="text/javascript">
    function queueWidget(e) {
        var queueObservable = kendo.observable({
            item: {extension: ENV.extension},
            changeQueue: function(e) {
                var queuename = e.currentTarget.title,
                    status = Boolean(e.currentTarget.dataset.status == "true");
                $.ajax({
                    url: ENV.vApi + "wfpbx/change_one_queue",
                    type: "PUT",
                    contentType: "application/json; charset=utf-8",
                    data: kendo.stringify({queuename: queuename, pause: !status}),
                    success: function(e) {
                        notification.show(e.message, e.status ? "success" : "error");
                    },
                    error: errorDataSource
                })
            }
        })
        kendo.bind($("#queue"), queueObservable);
        var data = JSON.parse(e.data);
        if(!data) return;
        if(!data.agentstate) return;
        var agentstate = data.agentstate;
        agentstate.dnd = Number(agentstate.dnd) && (["LOGGEDOFF","UNKNOWN"].indexOf(agentstate.state) == -1);
        if(agentstate.queues) {
            agentstate.queues.queue.map(function(ele) {
                switch(ele.queuememberstatus) {
                    case 1:
                        ele.queuememberstatusText = "Not in use"; break;
                    case 2:
                        ele.queuememberstatusText = "In use"; break;
                    case 3:
                        ele.queuememberstatusText = "Busy"; break;
                    case 4:
                        ele.queuememberstatusText = "Invalid"; break;
                    case 5:
                        ele.queuememberstatusText = "Unavailable"; break;
                    case 6:
                        ele.queuememberstatusText = "Ringing"; break;
                    default:
                        ele.queuememberstatusText = "Undefined"; break;
                }
                ele.queuememberpaused = Boolean(Number(ele.queuememberpaused));
            })
            agentstate.ready = (agentstate.state == "IDLE" && !agentstate.dnd) ? true : false;
            agentstate.ringing = Boolean(agentstate.state == "RINGING");
            agentstate.oncall = Boolean(agentstate.state == "ONCALL");
            queueObservable.set("item", agentstate);
        }
    }
</script>
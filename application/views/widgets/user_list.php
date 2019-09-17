<li id="dropdown-agent" data-toggle="tooltip" title="@Extension@ - @Agent name@" data-placement="left">
    <a href="javascript:void(0)" class="btn btn-alt btn-sm btn-default" data-toggle="dropdown" data-bind="click: refreshList">
        <i class="gi gi-user"></i>
    </a>
    <ul class="dropdown-menu dropdown-custom dropdown-options" style="min-width: 280px">
        <li>
            <table class="text-right table table-striped" style="width: 100%; margin-bottom: 0">
                <thead>
                    <tr>
                        <td class="text-center">@Extension@</td>
                        <td class="text-center">Call</td>
                        <td class="text-center">Chat</td>
                        <td>@Agent name@</td>
                        <td style="width: 24px">
                            <div class="list-user-avatar">
                                <i class="fa fa-user"></i>
                            </div>
                        </td>
                    </tr>
                </thead>
                <tbody data-template="user-list-template" data-auto-bind="false" data-bind="source: userListData">
                </tbody>
            </table>
        </li>
    </ul>
</li>
<style>
    .list-user-avatar {
        width: 18px;
        height: 18px;
        border: 1px solid lightgray;
        margin-left: 2px;
        border-radius: 9px;
        background: rgba(255, 255, 255, 0.75);
        overflow: hidden;
    }

    .list-user-avatar img {
        width: 16px;
        height: 16px;
        border-radius: 8px;
    }
    
    .list-user-avatar i {
        font-size: 8px;
        margin-left: 2px
    }

    .list-user-agentname {
        cursor: pointer;
        display: block;
        width: 100%;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }
</style>
<script id="user-list-template" type="text/x-kendo-template">
<tr>
    <td class="text-center"><span class="badge" data-bind="text: extension, css: {success: totalCurrentUser}"></span></td>
    <td class="text-center" data-bind="attr: {title: substatus}">
        # switch(data.statuscode) { 
            case 0: default: #
            <span class="gi gi-ban text-muted"></span>
                # break; 
            case 1: #
            <span class="gi gi-headset text-success"></span>
                # break; 
            case 2: #
            <span class="gi gi-earphone text-primary"></span>
                # break; 
            case 4: #
                <span class="gi gi-briefcase text-warning"></span>
                # break;
        } #
    </td>
    <td class="text-center" data-bind="attr: {title: chat_substatus}">
        # switch(data.chat_statuscode) { 
            case 0: #
            <span class="gi gi-circle_minus text-warning"></span>
                # break; 
            case 1: #
            <span class="gi gi-comments text-success"></span>
                # break;
            default: #
            <span class="gi gi-ban text-muted"></span>
                # break;  
        } #
    </td>
    <td>
        <span class="text-default list-user-agentname" data-bind="text: agentname"></span>
    </td>
    <td>
        <div class="list-user-avatar">
        # if(data.avatar) { #
        <img data-bind="attr: {src: avatar}"/>
        # } else { #
        <i class="fa fa-user"></i>
        # } #
        </div>
    </td>
</tr>
</script>
<script id="user-list-template1" type="text/x-kendo-template"> 
<a>
    <div class="pull-left" data-bind="attr: {title: substatus}" style="margin-top: -1px; margin-right: 8px">
        # switch(data.statuscode) { 
            case 0: default: #
            <span class="gi gi-ban text-muted"></span>
                # break; 
            case 1: #
            <span class="gi gi-headset text-success"></span>
                # break; 
            case 2: #
            <span class="gi gi-earphone text-primary"></span>
                # break; 
            case 4: #
                <span class="gi gi-briefcase text-warning"></span>
                # break;
        } #
    </div>
    <span class="badge pull-left" data-bind="text: extension, css: {success: totalCurrentUser}"></span>
    <span class="text-default" data-bind="text: agentname" style="text-indent: 20px"></span>
    <div class="list-user-avatar pull-right">
    # if(data.avatar) { #
    <img data-bind="attr: {src: avatar}"/>
    # } else { #
    <i class="fa fa-user"></i>
    # } #
    </div>
</a>
</script>
<script>
var userListFunction = function () {
    var $userListElement = $("#dropdown-agent");
    var userListObservable = kendo.observable({
        userListData: new kendo.data.DataSource({
            transport: {
                read: ENV.vApi + "widget/user_list",
                parameterMap: parameterMap
            },
            schema: {
                data: "data",
                total: "total",
                parse: function(res) {
                    res.data.map(doc => {
                        if(!doc.totalCurrentUser) {
                            doc.chat_statuscode = undefined;
                            doc.statuscode = undefined;
                        }
                    })
                    return res;
                }
            }
        }),
        refreshList: function(e) {
            if(!$userListElement.hasClass("open"))
                this.userListData.read();
        }
    });
    kendo.bind($userListElement, userListObservable);
}()
</script>
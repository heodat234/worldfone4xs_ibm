<div data-role="splitter"
             data-panes="[
                { collapsible: true, min: '700px'},
                { collapsible: true, min: '300px', size: '300px' }
             ]"
             data-orientation="horizontal" class="after-breadcrumb" style="overflow-y: auto;" id="splitter-view">
    <div class="col-sm-9" id="left-col">
        <div id="overview-wait-in-queue">
            <h3 class="text-center" style="margin-top: 10px; margin-bottom: 0"><span data-bind="css: {animation-pulse: waitInQueue.total, label: waitInQueue.total, label-danger: waitInQueue.total}">@Call in queue@</span></h3>
            <div data-bind="visible: waitInQueue.total">
                <table class="text-left table table-hover" style="width: 100%; margin-bottom: 0; font-size: 22px">
                    <thead>
                        <tr>
                            <td>@Phone@</td>
                            <td>@Customer type@</td>
                            <td>@Waiting time@</td>
                            <td>@DID@</td>
                            <td>@Available Extensions@</td>
                        </tr>
                    </thead>
                    <tbody data-template="overview-wait-in-queue-template" data-bind="source: waitInQueue.data">
                    </tbody>
                </table>
            </div>
            <div data-bind="invisible: waitInQueue.total" class="text-center text-success"><h3>@Not any call@</h3></div>
        </div>
        <h4 class="fieldset-legend" style="margin: 0 0 20px">
            <span style="font-weight: 500; background-color: #eaedf1; line-height: 1">@OVERVIEW AGENT ACTIVITY TODAY@</span>
        </h4>
        <!-- Card Styles Content -->
        <div data-role="listview" id="listview"
         data-template="element-template"
         data-bind="source: dataSource, invisible: displayGrid" class="row"></div>
        <!-- END Card Styles Content -->
        <!-- Table Styles Content -->
        <div style="background-color: white; padding: 5px; margin: 0 -10px" data-bind="visible: displayGrid">
            <table class="metrotable">
                <thead>
                    <tr>
                        <th style="width: 80px">@Extension@</th>
                        <th style="min-width: 140px">@Agent name@</th>
                        <th style="width: 80px">
                            <i class="fa fa-exchange"></i>
                        </th>
                        <th style="width: 45px; text-align: center"><i class="gi gi-ban text-muted"></i></th>
                        <th style="width: 45px; text-align: center"><i class="gi gi-headset text-success"></i></th>
                        <th style="width: 45px; text-align: center"><i class="gi gi-briefcase text-warning"></i></th>
                        <th style="width: 45px; text-align: center"><i class="gi gi-earphone text-primary"></i></th>
                        <th style="width: 45px;">@Total@</th>
                        <th style="width: 150px">@Action@</th>
                        <th style="width: 120px; text-align: center">@Status@</th>
                        <th style="width: 35px">@Online@</th>
                        <th style="width: 35px">@In@</th>
                        <th style="width: 35px">@Out@</th>
                    </tr>
                </thead>
                <tbody data-role="listview" data-template="body-template"
                 data-bind="source: dataSource"></tbody>
                <tfoot></tfoot>
            </table>
        </div>
        <!-- END Table Styles Content -->
    </div>
    <div class="col-sm-3" style="height: 80vh; overflow-y: auto; overflow-x: hidden; padding: 0;" id="right-col">
        <div style="padding: 10px">
            <h4 class="text-center" style="margin-top: 4px; margin-bottom: 10px">@OPTION@</h4>
            <div class="row">
                <div class="col-md-3"><label>@DISPLAY@</label></div>
                <div class="col-md-9" data-role="listview" data-selectable="true" data-template="display-list-template" data-bind="source: displayOption, events: {change: displayChange}">
                </div>
            </div>
            <div class="row" style="margin-top: 10px">
                <div class="col-md-3"><label>@SORT@</label></div>
                <div class="col-md-9" data-template="sort-list-template" data-bind="source: sortOption">
                </div>
            </div>
            <div class="row" style="margin-top: 10px">
                <div class="col-md-3"><label>@FAVORITE@</label></div>
                <div class="col-md-9">
                    <span class="label label-default filter-item filter-favorite" data-bind="click: filterFavorite" data-value="1">@Yes@</span>
                    <span class="label label-default filter-item filter-favorite" data-bind="click: filterFavorite" data-value="0">@No@</span>
                </div>
            </div>
            <div class="row" style="margin-top: 10px">
                <div class="col-md-3"><label>@GROUP@</label></div>
                <div class="col-md-9" data-role="listview" data-selectable="true" data-template="group-list-template" data-bind="source: groupOption, events: {change: groupFilterChange}">
                </div>
            </div>
            <div class="row" style="margin-top: 10px">
                <div class="col-md-3"><label>@STATUS@</label></div>
                <div class="col-md-9" data-template="filter-list-template" data-bind="source: filterOption">
                </div>
            </div>
        </div>
    </div>
</div>

<script id="overview-wait-in-queue-template" type="text/x-kendo-template"> 
    <tr>
        <td data-bind="text: customernumber"></td>
        <td data-bind="html: customer_typeHtml"></td>
        <td>
            <span class="label label-danger animation-pulse" data-bind="text: waitingTime"></span>
            &nbsp;<b>s</b>
        </td>
        <td><span data-bind="text: dnis, attr: {title: queue}"></span></td>
        <td>#= gridArray(data.extension_available) #</td>
    </tr>
</script>

<script id="body-template" type="text/x-kendo-template">
    # var d = new Date(); d.setHours(0,0,0,0); (typeof idx == 'undefined') ? idx = 0 : ++idx; #
    <tr class="# switch (data.activeStatus) { 
                    case "s0": # 
                        default
                    # break;
                    case "s1": # 
                        success
                    # break;
                    case "s2": #
                        info
                    # break;
                    case "s3": # 
                        danger
                    # break;
                    case "s4": # 
                        warning
                    # break;
                    default: #
                    # break; 
                } #">
        <td style="width: 80px">
            <i class="fa fa-heart" style="cursor: pointer" data-bind="click: toggleFavorite, attr: {data-id: id, data-extension: extension}, css: {text-danger: favorite}"></i>
            <span data-bind="text: extension"></span>
        </td>
        <td>
            <b data-bind="text: agentname"></b>
        </td>
        <td style="width: 80px">
            # if(data.extension != ENV.extension && data.currentStatusCode) { #
            <i class="fa fa-exchange" style="cursor: pointer" data-bind="click: changeStatusExtension, attr: {data-extension: extension, data-active-status: activeStatus}, visible: online" title="@Change status@"></i>
            # } #
        </td>
        <td style="text-align: center">
            <span data-bind="attr: {data-total-time: s0}" #if(activeStatus == "s0"){#class="label label label-primary time-interval"#}#>
            #if(data.s0){##: kendo.toString(new Date(d.getTime() + data.s0 * 1000), 'H:mm:ss') ##}#
            </span>
        </td>
        <td style="text-align: center">
            <span data-bind="attr: {data-total-time: s1}" #if(activeStatus == "s1"){#class="label label label-primary time-interval"#}#>
            #if(data.s1){##: kendo.toString(new Date(d.getTime() + data.s1 * 1000), 'H:mm:ss') ##}#
            </span>
        </td>
        <td style="text-align: center">
            <span data-bind="attr: {data-total-time: s4}" #if(activeStatus == "s4"){#class="label label label-primary time-interval"#}#>
            #if(data.s4){##: kendo.toString(new Date(d.getTime() + data.s4 * 1000), 'H:mm:ss') ##}#
            </span>
        </td>
        <td style="text-align: center">
            <span data-bind="attr: {data-total-time: s2}" #if(activeStatus == "s2"){#class="label label label-primary time-interval"#}#>
            #if(data.s2){##: kendo.toString(new Date(d.getTime() + data.s2 * 1000), 'H:mm:ss') ##}#
            </span>
        </td>
        <td>
            <span data-bind="attr: {data-total-time: total_time}" #if(online){#class="label label label-success time-interval"#}#>
            #if(data.total_time){##: kendo.toString(new Date(d.getTime() + data.total_time * 1000), 'H:mm:ss') ##}#
            </span>
        </td>
        <td style="width: 150px">
            # if(data.currentCall) { #
                <div class="btn-group btn-group-xs">
                    <a href="javascript:void(0)" data-role="tooltip" title="@Listen@" onclick="spyAction('#: data.extension #', 'whisper')"><img src="public/stel/img/icon/barge-enable.png"/></a>
                    <a href="javascript:void(0)" data-role="tooltip" title="@Eavesdrop@" onclick="spyAction('#: data.extension #', 'spy')"><img src="public/stel/img/icon/spy-enable.png"/></a>
                    <a href="javascript:void(0)" data-role="tooltip" title="@Three hand conversation@" onclick="spyAction('#: data.extension #', 'barge')" class="hidden"><img src="public/stel/img/icon/whisper-enable.png"/></a>
                    <a href="javascript:void(0)" onclick="hangupAction('#: data.currentCall.calluuid #')"  class="hidden"><img src="public/stel/img/icon/hangup-enable.png"/></a>
                </div>
            # } else { #
                <div class="btn-group btn-group-xs">
                    <a href="javascript:void(0)" data-role="tooltip" title="@Listen@"><img src="public/stel/img/icon/barge-disable.png"/></a>
                    <a href="javascript:void(0)" data-role="tooltip" title="@Eavesdrop@"><img src="public/stel/img/icon/spy-disable.png"/></a>
                    <a href="javascript:void(0)" data-role="tooltip" title="@Three hand conversation@"  class="hidden"><img src="public/stel/img/icon/whisper-disable.png"/></a>
                    <a href="javascript:void(0)" data-role="tooltip" title="@Hangup@" class="hidden"><img src="public/stel/img/icon/hangup-disable.png"/></a>
                </div>
            # } #
        </td>
        <td style="text-align: center">
            #switch(activeStatus) {
                case "s0":#
                <i class="gi gi-ban text-muted"></i>
                <span data-bind="attr: {data-total-time: currentCallStatus.total_time}" #if(online){#class="label label label-default time-interval"#}#>#if(currentCallStatus.total_time){##: kendo.toString(new Date(d.getTime() + currentCallStatus.total_time * 1000), 'H:mm:ss') ##}#</span>
            #   break;
                case "s1":#
                <i class="gi gi-headset text-success"></i>
                <span data-bind="attr: {data-total-time: currentCallStatus.total_time}" #if(online){#class="label label label-success time-interval"#}#>#if(currentCallStatus.total_time){##: kendo.toString(new Date(d.getTime() + currentCallStatus.total_time * 1000), 'H:mm:ss') ##}#</span>
            #   break;
                case "s2":#
                <i class="gi gi-earphone text-primary"></i>
                <span data-bind="attr: {data-total-time: currentCallStatus.total_time}" #if(online){#class="label label label-primary time-interval"#}#>#if(currentCallStatus.total_time){##: kendo.toString(new Date(d.getTime() + currentCallStatus.total_time * 1000), 'H:mm:ss') ##}#</span>
            #   break;
                case "s4":#
                <i class="gi gi-briefcase text-warning"></i>
                <span data-bind="attr: {data-total-time: currentCallStatus.total_time}" #if(online){#class="label label label-warning time-interval"#}#>#if(currentCallStatus.total_time){##: kendo.toString(new Date(d.getTime() + currentCallStatus.total_time * 1000), 'H:mm:ss') ##}#</span>
            #   break;
                default: #
                <i class="gi gi-ban text-muted"></i>
            #   break;
            }#
            <br><small data-bind="text: currentCallStatus.substatus"></small>
        </td>
        <td>
            <span data-bind="text: totalCurrentUser"></span>
        </td>
        <td>
            <span data-bind="text: totalCallIn"></span>
        </td>
        <td>
            <span data-bind="text: totalCallOut"></span>
        </td>
    </tr>
</script>

<script id="display-list-template" type="text/x-kendo-template">
    <span class="label filter-item label-default" data-bind="text: text, css: {displayGrid: value}"></span>
</script>

<script id="sort-list-template" type="text/x-kendo-template">
    <span class="label filter-item #if(data.dir){##: 'label-success' ##}else{##:'label-default'##}# #: data.dir #" data-bind="click: sort, text: text, attr: {data-field: field}"></span>
</script>

<script id="group-list-template" type="text/x-kendo-template">
    <span class="label filter-item label-default" data-bind="text: name"></span>
</script>

<script id="filter-list-template" type="text/x-kendo-template">
    <span class="label filter-item #if(data.active){##: 'label-info' ##}else{##:'label-default'##}#" data-bind="click: filter, text: text, attr: {data-value: value, data-text: text}"></span>
</script>

<script id="status-template" type="text/x-kendo-template">
    # var d = new Date(); d.setHours(0,0,0,0); #
    <tr>
        <td data-bind="attr: {title: statustext}" class="icon-status">
            # switch (data.statuscode) { 
                case 0:# 
                <i class="gi gi-ban text-muted"></i>

                # break;
                case 1: # 
                <i class="gi gi-headset text-success"></i>

                # break;
                case 2: #
                <i class="gi gi-earphone text-primary"></i>
                
                # break;
                case 3: # 
                <i class="gi gi-airplane text-danger"></i>
                
                # break;
                case 4: # 
                <i class="gi gi-briefcase text-warning"></i>
            # break;
                default: break; 
            } #
        </td>
        <td>
            <span data-bind="css: {label : active, label-primary: active, time-interval: active}, attr: {data-total-time: total_time}">#: kendo.toString(new Date(d.getTime() + total_time * 1000), 'H:mm:ss') #</span>
        </td>
        <td class="text-center overview-substatus">
            <span data-bind="text: currentSubstatus"></span>
        </td>
    </tr>
</script>

<script id="element-template" type="text/x-kendo-template">
# var d = new Date(); d.setHours(0,0,0,0); #
<div class="col-lg-2 col-md-4 col-sm-6 col-xs-12">
    <div class="widget">
        <div class="widget-advanced">
            <!-- Widget Header -->
            <div class="widget-header text-center # switch (data.activeStatus) { 
                    case "s0": # 
                        themed-background-dark
                    # break;
                    case "s1": # 
                        themed-background-success
                    # break;
                    case "s2": #
                        themed-background-info
                    # break;
                    case "s3": # 
                        themed-background-danger
                    # break;
                    case "s4": # 
                        themed-background-warning
                    # break;
                    default: #
                        themed-background-muted
                    # break; 
                } #">
                <div class="widget-options-left widget-content-light">
                    <b data-bind="text: extension"></b>
                </div>
                <div class="widget-options widget-content-light">
                    # if(data.extension != ENV.extension && data.currentStatusCode) { #
                    <i class="fa fa-exchange" style="cursor: pointer" data-bind="click: changeStatusExtension, attr: {data-extension: extension, data-active-status: activeStatus}, visible: online" title="@Change status@"></i>
                    # } #
                    <i class="fa fa-heart" style="cursor: pointer" data-bind="click: toggleFavorite, attr: {data-id: id, data-extension: extension}, css: {text-danger: favorite}"></i>
                </div>
                <div class="widget-options-bottom-left widget-content-light">
                    <small>@Call in@: </small>
                    <b data-bind="text: totalCallIn, css: {text-danger: totalCallIn}"></b>
                </div>
                <div class="widget-options-bottom-right widget-content-light">
                    <small>@Call out@: </small>
                    <b data-bind="text: totalCallOut, css: {text-danger: totalCallOut}"></b>
                </div>
                <div class="widget-content-light" style="margin-top: 5px">
                    <a href="javascript:void(0)" class="themed-color"><b data-bind="text: agentname" style="font-size: 18px"></b></a>
                    <br>
                    <small data-bind="attr: {data-total-time: total_time}, css: {time-interval: online}">#: kendo.toString(new Date(d.getTime() + data.total_time * 1000), 'H:mm:ss') #</small>
                </div>
            </div>
            <!-- END Widget Header -->

            <!-- Widget Main -->
            <div class="widget-main">
                <a href="javascript:void(0)" class="widget-image-container">
                    # if(!data.avatar) { #
                    <span class="widget-icon themed-background-custom" data-bind="css: {themed-background: online}"><i class="fa fa-user" data-bind="css: {text-success: online}" title="Online status"></i></span>
                    # } else { #
                    <img alt="avatar" class="widget-image img-circle" style="position: absolute; top: 4px; left: 4px; width: 67px; height: 67px; # if(!data.online){ # filter: grayscale(100%); #}#" data-bind="attr: {src: avatar, title: totalCurrentUser}, css: {online: online}">
                    # } #
                </a>
                # if(data.currentCall) { #
                <div class="widget-options-left">
                    <div class="btn-group btn-group-xs">
                        <a href="javascript:void(0)" data-role="tooltip" title="@Listen@" onclick="spyAction('#: data.extension #', 'whisper')"><img src="public/stel/img/icon/barge-enable.png"/></a>
                    </div>
                </div>
                <div class="widget-options">
                    <div class="btn-group btn-group-xs">
                        <a href="javascript:void(0)" data-role="tooltip" title="@Eavesdrop@" onclick="spyAction('#: data.extension #', 'spy')"><img src="public/stel/img/icon/spy-enable.png"/></a>
                        <a href="javascript:void(0)" data-role="tooltip" title="@Three hand conversation@" onclick="spyAction('#: data.extension #', 'barge')" class="hidden"><img src="public/stel/img/icon/whisper-enable.png"/></a>
                        <a href="javascript:void(0)" data-role="tooltip" title="@Hangup@" onclick="hangupAction('#: data.currentCall.calluuid #')" class="hidden"><img src="public/stel/img/icon/hangup-enable.png"/></a>
                    </div>
                </div>
                # } else { #
                <div class="widget-options-left">
                    <div class="btn-group btn-group-xs">
                        <a href="javascript:void(0)" data-role="tooltip" title="@Listen@"><img src="public/stel/img/icon/barge-disable.png"/></a>
                    </div>
                </div>
                <div class="widget-options">
                    <div class="btn-group btn-group-xs">
                        <a href="javascript:void(0)" data-role="tooltip" title="@Eavesdrop@"><img src="public/stel/img/icon/spy-disable.png"/></a>
                        <a href="javascript:void(0)" data-role="tooltip" title="@Three hand conversation@" class="hidden"><img src="public/stel/img/icon/whisper-disable.png"/></a>
                        <a href="javascript:void(0)" data-role="tooltip" title="@Hangup@" class="hidden"><img src="public/stel/img/icon/hangup-disable.png"/></a>
                    </div>
                </div>
                # } #
                <div class="text-center" data-bind="invisible: status.length" style="padding-top: 20px">
                    <span class="text-muted" style="opacity: 0.3; font-size: 64px"><i class="gi gi-circle_question_mark"></i></span>
                </div>
                <table class="table table-borderless table-striped table-condensed table-vcenter">
                    <tbody data-bind="source: status" data-template="status-template">
                    </tbody>
                </table>
            </div>
            <!-- END Widget Main -->
        </div>
    </div>
</div>
</script>

<style type="text/css">
    #left-col {
        background-color: #eaedf1;
    }
    [data-role="listview"] {
        border: 0;
    }
    #listview {
        background-color: #eaedf1;
    }
    .widget {
        min-height: 240px;
        margin: 0 -10px 10px; 
    }
    .themed-background-custom {
        border: 1px solid lightgray;
        background-color: #fff;
    }
    .widget .widget-icon {
        color: #000;
    }

    .widget:hover {
        box-shadow: 4px 2px #888888;
    }
    .widget-advanced .widget-main {
        padding: 40px 10px 0;
    }
    .widget-advanced .widget-image-container {
        padding-bottom: 0;
    }
    .widget-advanced .widget-image-container {
        width: 66px;
    }
    .widget-advanced .widget-header {
        max-height: 70px;
        padding-top: 0;
        padding-bottom: 7px;
    }
    .widget-main table {
        margin-bottom: 0;
    }
    .widget .widget-options-bottom-left {
        position: absolute;
        left: 5px;
        bottom: 5px;
    }

    .widget .widget-options-bottom-right {
        position: absolute;
        right: 5px;
        bottom: 5px;
    }

    .widget-image-container > img.widget-image {
        border: 1px solid gray;
    }

    .widget-image-container > img.widget-image.online {
        border-color: green;
    }

    #page-content span.label {
        line-height: 1.6;
    }

    span.label.desc::before {
        content: "\f175";
        display: inline-block;
        font: normal normal normal 14px/1 FontAwesome;
        font-size: 14px;
        font-size: inherit;
        text-rendering: auto;
        -webkit-font-smoothing: antialiased;
        margin-right: 5px;
    }

    span.label.asc::before {
        content: "\f176";
        display: inline-block;
        font: normal normal normal 14px/1 FontAwesome;
        font-size: inherit;
        text-rendering: auto;
        -webkit-font-smoothing: antialiased;
        margin-right: 5px;
    }

    .filter-item {
        cursor: pointer;
    }

    .icon-status {
        width: 10px;
    }

    .icon-status > i {
        vertical-align: -2px;
    }

    .metrotable {
        background-color: white;
        padding: 5px;
        width: 100%;
    }

    .metrotable > thead > tr > th {
        padding: .5em .2em .5em .4em;
        text-align: left;
        font-size: 1.2em;
        font-weight: lighter;
        color: #bbb;
        border-bottom: 1px solid #ccc;
    }

    .metrotable > tbody > tr > td {
        padding: .5em .2em .5em .4em;
        text-align: left;
        font-size: 1em;
        font-weight: lighter;
        color: #787878;
        border-bottom: 1px solid #ddd;
    }

    .metrotable > tfoot > tr > td {
        padding: .5em .2em .5em .4em;
        text-align: left;
        font-size: 1.6em;
        font-weight: lighter;
        color: #000;
        border-bottom: 1px solid #e1e1e1;
    }

    .metrotable > tbody > tr:hover {
        background-color: #DDD;
    }

    .k-loading-mask{
        display:none;
    }

    .themed-color {
        text-shadow: 2px 0 0 #fff, -2px 0 0 #fff, 0 2px 0 #fff, 0 -2px 0 #fff, 1px 1px #fff, -1px -1px 0 #fff, 1px -1px 0 #fff, -1px 1px 0 #fff;
    }

    .overview-substatus {
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }
    .metrotable > tbody > tr.default {
        background-color: #cccccc;
    }
    .metrotable > tbody > tr.success {
        background-color: #66ff66;
    }
    .metrotable > tbody > tr.info {
        background-color: #99ddff;
    }
    .metrotable > tbody > tr.warning {
        background-color: #f3bd72;
    }
    .metrotable > tbody > tr.danger {
        background-color: #e38682;
    }
</style>

<script>
var List = function() {
    return {
        dataSource: {},
        listview: {},
        columns: Config.columns,
        init: function() {
            var dataSource = this.dataSource = new kendo.data.DataSource({
                schema: {
                    data: "data",
                    total: "total",
                    model: Config.model,
                    parse: function(response) {
                        response.data.map(doc => {
                            doc.total_time = 0;
                            doc.activeStatus = "";
                            doc.online = false;
                            doc.extension = Number(doc.extension);
                            doc.currentCallStatus = {};
                            if(doc.status.length) {
                                doc.status.map(state => {
                                    if(!doc.last_update) doc.last_update = state.last_update;
                                    else doc.last_update = Math.max(doc.last_update, state.last_update);
                                    doc.online = (doc.last_update > response.time - 10) ? true : false;
                                    state.active = doc.online && !Boolean(state.last_endtime);
                                    if(!doc.currentStatusCode && state.active) doc.currentStatusCode = state.statuscode;
                                    if(state.active) state.currentSubstatus = state.last_substatus;
                                    doc.total_time += state.total_time;
                                    // Set for grid
                                    doc["s" + state.statuscode] = state.total_time;
                                    if(state.active) doc.activeStatus = "s" + state.statuscode;
                                    if(state.active) {
                                        doc.currentCallStatus = {
                                            total_time: state.last_update - state.last_starttime,
                                            statuscode: state.statuscode,
                                            substatus: state.last_substatus
                                        };
                                    }
                                })
                            }
                            if(!doc.currentStatusCode && doc.currentStatusCode !== 0) doc.currentStatusCode = -1;
                        })
                        return response;
                    }
                },
                transport: {
                    read: Config.crudApi + "monitor/users",
                    parameterMap: parameterMap,
                    global: false
                },
                sync: syncDataSource
            });

            var observable = {
                dataSource: dataSource,
                sortOption: [
                    {text: "@Extension@", field: "extension", dir: ""},
                    {text: "@Agent name@", field: "agentname", dir: ""},
                    {text: "@Call in@", field: "totalCallIn", dir: ""},
                    {text: "@Call out@", field: "totalCallOut", dir: ""},
                    {text: "@Available time@", field: "s1", dir: ""},
                    {text: "@ACW time@", field: "s4", dir: ""},
                    {text: "@Oncall time@", field: "s2", dir: ""},
                    {text: "@Online time@", field: "total_time", dir: ""},
                ],
                sort: function(e) {
                    var $currentTarget  = $(e.currentTarget),
                        field           = $currentTarget.data('field'),
                        dir             = "";
                    
                    this.get("sortOption").map(function(doc){
                        if(doc.field == field) {
                            switch (doc.dir) {
                                case "": default:
                                    dir = "asc";
                                    break;
                                case "asc":
                                    dir = "desc";
                                    break;
                                case "desc":
                                    dir = "";
                                    break; 
                            }
                            doc.dir = dir;
                        }
                    })
                    List.dataSource.sort({field: field, dir: dir});
                    kendo.bind($('[data-template="sort-list-template"]'), this);
                },
                filterOption: [
                  {
                    "value": 1,
                    "text": "@Available@"
                  },
                  {
                    "value": 0,
                    "text": "Phone @unvailable@"
                  },
                  /*{
                    "value": 3,
                    "text": "Unvailable"
                  },*/
                  {
                    "value": 4,
                    "text": "@After call work@"
                  },
                  {
                    "value": 2,
                    "text": "Phone @oncall@",
                  },
                  {
                    "value" : -1,
                    "text"  : "Offline"
                  }
                ],
                filter: function(e) {
                    var $currentTarget  = $(e.currentTarget),
                        value           = Number($currentTarget.data("value")),
                        text            = $currentTarget.data("text"),
                        active          = $currentTarget.hasClass("label-info");

                    this.get("filterOption").map(function(doc){
                        if(doc.text == text) {
                            doc.active = !active;
                        }
                    })
                    var currentFilter   = List.dataSource.filter();
                    if(!active) {
                        if(currentFilter) {
                            var hasFilterStatus = false;
                            currentFilter.filters.forEach((filter, index) => {
                                if(filter.type == "status") {
                                    currentFilter.filters[index].filters.push({field: "currentStatusCode", operator: "eq", value: value});
                                    hasFilterStatus = true;
                                }
                            });
                            if(!hasFilterStatus) {
                                currentFilter.filters.push({
                                    type: "status",
                                    logic: "or",
                                    filters: [{field: "currentStatusCode", operator: "eq", value: value}]
                                });
                            }
                        } else currentFilter = {
                                logic: "and", 
                                filters: [{
                                    type: "status",
                                    logic: "or",
                                    filters: [{field: "currentStatusCode", operator: "eq", value: value}]
                                }]
                            };
                        List.dataSource.filter(currentFilter);
                    } else {
                        if(currentFilter) {
                            var removeIndex = -1;
                            currentFilter.filters.forEach((filter, index) => {
                                if(filter.type == "status") {
                                    currentFilter.filters[index].filters = currentFilter.filters[index].filters.filter(doc => doc.value != value);
                                    if(!currentFilter.filters[index].filters.length) removeIndex = index;
                                }
                            });
                            if(removeIndex > -1) currentFilter.filters.splice(removeIndex, 1);
                        }
                        List.dataSource.filter(currentFilter);
                    }
                    kendo.bind($('[data-template="filter-list-template"]'), this);
                },
                groupOption: new kendo.data.DataSource({
                    serverFiltering: true,
                    transport: {
                        read: `${ENV.restApi}group`
                    },
                    schema: {
                        data: "data"
                    },
                    filter: {field: "active", operator: "eq", value: true}
                }),
                displayOption: [
                    {text: "@Card@", value: false},
                    {text: "@Grid@", value: true}
                ],
                waitInQueue: {data: [], total: 0}
            };

            this.observable = kendo.observable(observable);

            kendo.bind($("#splitter-view"), this.observable);

            /*
             * Right Click Menu
             */
            var menu = $("#action-menu");

            $("html").on("click", function() {menu.hide()});

            $(document).on("click", "#listview a.btn-action", function(e){
                let row = $(e.target).closest("div.view-container");
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
                    menu.find("a[data-type=read], a[data-type=update], a[data-type=delete]").data('uid',uid);

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
        }
    }
}();

$.ajaxSetup({global: false});

List.init();

window.overviewInterval1 = setInterval(() => {
    List.dataSource.read();
}, 5000);

window.overviewInterval2 = setInterval(() => {
    var $select = $(".time-interval[data-total-time]");
    var d = new Date(); d.setHours(0,0,0,0);
    if($select.length) {
        for (var i = 0; i < $select.length; i++) {
            var totalTime = Number($select[i].dataset.totalTime),
                timeText = kendo.toString(new Date(d.getTime() + totalTime * 1000), 'H:mm:ss');
            $select[i].innerText = timeText;
            totalTime++;
            $select[i].dataset.totalTime = totalTime;   
        }
    } 
}, 1000);

window.overviewInterval3 = setInterval(() => {
    $.get(ENV.vApi + "monitor/get_call_in_queue", function(res) {
        var item = res;
        if(item.total) {
            item.data.map((doc, index) => {
                if(index % 2 != 0)
                    doc.odd = true;
                doc.waitingTime = item.time - doc.starttime;
                switch((doc.customer_type || "").toString()) {
                    case "": 
                        doc.customer_typeHtml = `<span class="label label-default">@Normal@</span>`;
                        break;
                    default:
                        doc.customer_typeHtml = `<span class="label label-warning">${doc.customer_type}</span>`;
                        break;
                }
                if(doc.ring_extensions && doc.extension_available) {
                    doc.extension_available.forEach(function(ext, idx) {
                        if(doc.ring_extensions.indexOf(ext) != -1) {
                            doc.extension_available[idx] = '<i class="fa fa-phone animation-tossing" style="font-size: 10px; color: white"></i>' + ext;
                        }
                    })
                }
            })
            item.totalText = item.total.toString();
        } else item.totalText = "";
        List.observable.set("waitInQueue", item);
        kendo.bind($("#overview-wait-in-queue"), List.observable);
    });
}, 2000);

function changeStatusExtension(e) {
    var $target     = $(e.currentTarget),
        extension   = $target.data("extension"),
        activeStatus= $(e.currentTarget).data("active-status");

    var buttons = {
        "1": "@Available@",
        "4": "@ACW@",
        cancel: true,
    }

    buttons[activeStatus.replace("s", "")] = null;

    var type = swal({
        title: "@Change status@.",
        text: `@Change@ ${extension} @to status@?`,
        icon: "info",
        buttons: buttons
    }).then(code => {
        if(code !== null && code !== false) {
            $.ajax({
                url: ENV.vApi + "monitor/change_status_extension",
                data: kendo.stringify({agentState: code, subState: `${ENV.extension} change`, extension : `${extension}`}),
                contentType: "application/json; charset=utf-8",
                type: "POST",
                success: function(e) {
                    notification.show(e.message, e.status ? "success" : "error");
                },
                error: errorDataSource
            })
        }
    })
}

function spyAction(extension, mode) {
    $.ajax({
        url: ENV.vApi + "monitor/spy",
        data: kendo.stringify({spied_extension: extension, mode : mode}),
        contentType: "application/json; charset=utf-8",
        type: "POST",
        success: function(e) {
            notification.show(e.message, e.status ? "success" : "error");
        },
        error: errorDataSource
    })
}

function hangupAction(calluuid) {
    $.ajax({
        url: ENV.vApi + "wfpbx/hangup",
        data: kendo.stringify({calluuid: calluuid}),
        contentType: "application/json; charset=utf-8",
        type: "POST",
        success: function(e) {
            notification.show(e.message, e.status ? "success" : "error");
        },
        error: errorDataSource
    })
}
</script>
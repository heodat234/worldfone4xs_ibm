<div data-role="splitter"
             data-panes="[
                { collapsible: true, min: '700px'},
                { collapsible: true, min: '300px', size: '320px' },
             ]"
             data-orientation="horizontal" style="height: 80vh; overflow-y: auto;" id="splitter-view">
    <div class="col-sm-9" id="left-col">
        <h4 class="fieldset-legend" style="margin: 0 0 20px"><span style="font-weight: 500; background-color: #eaedf1; line-height: 1">@OVERVIEW AGENT ACTIVITY TODAY@</span></h4>
        <!-- Card Styles Content -->
        <div data-role="listview" id="listview"
         data-template="element-template"
         data-bind="source: dataSource, invisible: displayGrid" class="row"></div>
        <!-- END Card Styles Content -->
        <!-- Table Styles Content -->
        <div style="background-color: white; padding: 5px; margin: 0 -10px" data-bind="visible: displayGrid">
            <table class="metrotable table-striped" style="min-width: 1000px">
                <thead>
                    <tr>
                        <th colspan="4" class="text-center"><b>@Agent@</b></th>
                        <th colspan="2" class="text-center"><b>@Status@ @current@</b></th>
                        <th colspan="2" class="text-center"><b>@Call@</b></th>
                        <th colspan="6" class="text-center"><b>@Call status@ @aggregate@</b></th>
                        <th colspan="3" class="text-center"><b>@Status@ chat  @aggregate@</b></th>
                    </tr>
                    <tr>
                        <th style="width: 30px">
                            <div class="monitor-list-user-avatar">
                                <i class="fa fa-user"></i>
                            </div>
                        </th>
                        <th style="width: 50px">@Extension@</th>
                        <th style="width: 140px">@Agent name@</th>
                        <th style="width: 55px">@Online@</th>
                        <th style="width: 80px; text-align: center">Call</th>
                        <th style="width: 80px; text-align: center">Chat</th>
                        <th style="width: 55px">@Call in@</th>
                        <th style="width: 55px">@Call out@</th>
                        <th style="width: 45px; text-align: center"><i class="gi gi-ban text-muted"></i></th>
                        <th style="width: 45px; text-align: center"><i class="gi gi-headset text-success"></i></th>
                        <th style="width: 45px; text-align: center"><i class="gi gi-briefcase text-warning"></i></th>
                        <th style="width: 45px; text-align: center"><i class="gi gi-earphone text-primary"></i></th>
                        <th style="width: 45px; text-align: center">@Total@</th>
                        <th style="width: 120px">@Action@</th>
                        <th style="width: 45px; text-align: center"><i class="gi gi-comments text-success"></i></th>
                        <th style="width: 45px; text-align: center"><i class="gi gi-circle_minus text-warning"></i></th>
                        <th style="width: 70px">@Action@</th>
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
            <h4 class="text-center" style="margin-top: 7px; margin-bottom: 12px">@OPTION@</h4>
            <div class="row hidden">
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

<script id="body-template" type="text/x-kendo-template">
    # var d = new Date(); d.setHours(0,0,0,0); #
    <tr>
        <td>
            <div class="monitor-list-user-avatar">
                # if(data.avatar) { #
                <img data-bind="attr: {src: avatar}"/>
                # } else { #
                <i class="fa fa-user"></i>
                # } #
            </div>
        </td>
        <td>
            <i class="fa fa-heart" style="cursor: pointer" data-bind="click: toggleFavorite, attr: {data-id: id, data-extension: extension}, css: {text-danger: favorite}"></i>
            <span data-bind="text: extension"></span>
        </td>
        <td>
            <span data-bind="text: agentname"></span>
        </td>
        <td>
            <span data-bind="text: totalCurrentUser"></span>
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
        <td style="text-align: center">
            #if(activeChatStatus == "c1"){#
                <i class="gi gi-comments text-success"></i>
                <span data-bind="attr: {data-total-time: currentChatStatus.total_time}" #if(online){#class="label label label-success time-interval"#}#>#if(currentChatStatus.total_time){##: kendo.toString(new Date(d.getTime() + currentChatStatus.total_time * 1000), 'H:mm:ss') ##}#</span>
            #} else if(activeChatStatus == "c0") {#
                <i class="gi gi-circle_minus text-warning"></i>
                <span data-bind="attr: {data-total-time: currentChatStatus.total_time}" #if(online){#class="label label label-warning time-interval"#}#>#if(currentChatStatus.total_time){##: kendo.toString(new Date(d.getTime() + currentChatStatus.total_time * 1000), 'H:mm:ss') ##}#</span>
            #}else{#
                <i class="gi gi-ban text-muted"></i>
            #}#
            <br><small data-bind="text: currentChatStatus.substatus"></small>
        </td>
        <td>
            <span data-bind="text: totalCallIn"></span>
        </td>
        <td>
            <span data-bind="text: totalCallOut"></span>
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
            #if(total_time){##: kendo.toString(new Date(d.getTime() + total_time * 1000), 'H:mm:ss') ##}#
            </span>
        </td>
        <td>
            # if(data.currentCall) { #
                <div class="btn-group btn-group-xs">
                    <a href="javascript:void(0)" data-role="tooltip" title="@Eavesdrop@"><img src="public/stel/img/icon/barge-enable.png"/></a>
                    <a href="javascript:void(0)" data-role="tooltip" title="@Listen@"><img src="public/stel/img/icon/spy-enable.png"/></a>
                    <a href="javascript:void(0)" data-role="tooltip" title="@Three hand conversation@"><img src="public/stel/img/icon/whisper-enable.png"/></a>
                    <a href="javascript:void(0)" data-role="tooltip" title="@Hangup@"><img src="public/stel/img/icon/hangup-enable.png"/></a>
                </div>
            # } else { #
                <div class="btn-group btn-group-xs">
                    <a href="javascript:void(0)" data-role="tooltip" title="@Eavesdrop@"><img src="public/stel/img/icon/barge-disable.png"/></a>
                    <a href="javascript:void(0)" data-role="tooltip" title="@Listen@"><img src="public/stel/img/icon/spy-disable.png"/></a>
                    <a href="javascript:void(0)" data-role="tooltip" title="@Three hand conversation@"><img src="public/stel/img/icon/whisper-disable.png"/></a>
                    <a href="javascript:void(0)" data-role="tooltip" title="@Hangup@"><img src="public/stel/img/icon/hangup-disable.png"/></a>
                </div>
            # } #
            # if(data.extension != ENV.extension && data.currentStatusCode) { #
            <span style="cursor: pointer" data-bind="click: changeStatusExtension, attr: {data-extension: extension, data-active-status: activeChatStatus}, visible: online">
                <i class="fa fa-exchange" data-role="tooltip" title="@Change status@"></i>
            </span>
            # } #
        </td>
        <td>
            <span data-bind="attr: {data-total-time: c1}" #if(activeChatStatus == "c1"){#class="label label label-primary time-interval"#}#>
            #if(data.c1){##: kendo.toString(new Date(d.getTime() + data.c1 * 1000), 'H:mm:ss') ##}#
            </span>
        </td>
        <td>
            <span data-bind="attr: {data-total-time: c0}" #if(activeChatStatus == "c0"){#class="label label label-primary time-interval"#}#>
            #if(data.c0){##: kendo.toString(new Date(d.getTime() + data.c0 * 1000), 'H:mm:ss') ##}#
            </span>
        </td>
        <td>
            # if(data.extension != ENV.extension && data.currentChatStatusCode !== undefined) { #
            <span style="cursor: pointer" data-bind="click: changeChatStatusExtension, attr: {data-extension: extension, data-active-status: activeChatStatus}, visible: online">
                <i class="fa fa-exchange" data-role="tooltip" title="@Change status@"></i>
            </span>
            # } #
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
        <td class="text-center">
            <span data-bind="text: currentSubstatus"></span>
        </td>
    </tr>
</script>

<script id="element-template" type="text/x-kendo-template">
# var d = new Date(); d.setHours(0,0,0,0); #
<div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
    <div class="widget">
        <div class="widget-advanced">
            <!-- Widget Header -->
            <div class="widget-header text-center themed-background-dark">
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
                        <a href="javascript:void(0)" data-role="tooltip" title="@Eavesdrop@"><img src="public/stel/img/icon/barge-enable.png"/></a>
                        <a href="javascript:void(0)" data-role="tooltip" title="@Listen@"><img src="public/stel/img/icon/spy-enable.png"/></a>
                    </div>
                </div>
                <div class="widget-options">
                    <div class="btn-group btn-group-xs">
                        <a href="javascript:void(0)" data-role="tooltip" title="@Three hand conversation@"><img src="public/stel/img/icon/whisper-enable.png"/></a>
                        <a href="javascript:void(0)" data-role="tooltip" title="@Hangup@"><img src="public/stel/img/icon/hangup-enable.png"/></a>
                    </div>
                </div>
                # } else { #
                <div class="widget-options-left">
                    <div class="btn-group btn-group-xs">
                        <a href="javascript:void(0)" data-role="tooltip" title="@Eavesdrop@"><img src="public/stel/img/icon/barge-disable.png"/></a>
                        <a href="javascript:void(0)" data-role="tooltip" title="@Listen@"><img src="public/stel/img/icon/spy-disable.png"/></a>
                    </div>
                </div>
                <div class="widget-options">
                    <div class="btn-group btn-group-xs">
                        <a href="javascript:void(0)" data-role="tooltip" title="@Three hand conversation@"><img src="public/stel/img/icon/whisper-disable.png"/></a>
                        <a href="javascript:void(0)" data-role="tooltip" title="@Hangup@"><img src="public/stel/img/icon/hangup-disable.png"/></a>
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

    .metrotable > tbody > tr:nth-child(odd) {
        background: #EEE
    }

    .metrotable > tbody > tr > td {
        padding: .5em .2em .5em .4em;
        text-align: left;
        font-size: 1em;
        font-weight: lighter;
        color: #787878;
        border-bottom: 1px solid #e1e1e1;
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

    .monitor-list-user-avatar {
        width: 18px;
        height: 18px;
        border: 1px solid lightgray;
        margin-left: 2px;
        border-radius: 9px;
        background: rgba(255, 255, 255, 0.75);
        overflow: hidden;
    }

    .monitor-list-user-avatar img {
        width: 16px;
        height: 16px;
        border-radius: 8px;
    }
    
    .monitor-list-user-avatar i {
        font-size: 16px;
        margin-left: 2px
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
                            // Chat
                            doc.total_chat_time = 0;
                            doc.activeChatStatus = "";
                            doc.currentChatStatus = {};
                            if(doc.chat_status.length) {
                                doc.chat_status.map(state => {
                                    state.active = doc.online && !Boolean(state.last_endtime);
                                    if(!doc.currentChatStatusCode && state.active) doc.currentChatStatusCode = state.statuscode;
                                    if(state.active) state.currentChatSubstatus = state.last_substatus;
                                    doc.total_chat_time += state.total_time;
                                    // Set for grid
                                    doc["c" + state.statuscode] = state.total_time;
                                    if(state.active) doc.activeChatStatus = "c" + state.statuscode;
                                    if(state.active) {
                                        doc.currentChatStatus = {
                                            total_time: state.last_update - state.last_starttime,
                                            statuscode: state.statuscode,
                                            substatus: state.last_substatus
                                        };
                                    }
                                })
                            }
                            if(!doc.currentChatStatusCode && doc.currentChatStatusCode !== 0) doc.currentChatStatusCode = -1;
                        })
                        return response;
                    }
                },
                transport: {
                    read: Config.crudApi + "monitor/users_with_chat",
                    parameterMap: parameterMap
                },
                sync: syncDataSource
            });

            var observable = this.observable = Object.assign({
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
                    "text": "Softphone @unvailable@"
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
                    "text": "Softphone @oncall@",
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
                    {text: "@Grid@", value: true},
                    {text: "@Card@", value: false}
                ]
            }, Config.observable)

            kendo.bind($("#splitter-view"), observable)

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

function changeChatStatusExtension(e) {
    var $target     = $(e.currentTarget),
        extension   = $target.data("extension"),
        activeStatus= $(e.currentTarget).data("active-status");

    var buttons = {
        "1": "@Ready@",
        "0": "@Busy@",
        cancel: true,
    }

    buttons[activeStatus.replace("c", "")] = null;

    var type = swal({
        title: "@Change status@ chat.",
        text: `@Change@ ${extension} chat @to status@?`,
        icon: "info",
        buttons: buttons
    }).then(code => {
        if(code !== null && code !== false) {
            $.ajax({
                url: ENV.vApi + "monitor/change_chat_status_extension",
                data: kendo.stringify({statuscode: Number(code), substatus: `${ENV.extension} change`, extension : `${extension}`}),
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
</script>
<script>
var Config = {
    crudApi: `${ENV.vApi}`,
    templateApi: `${ENV.templateApi}`,
    collection: "voicemail",
    observable: {
    },
    model: {
        id: "id",
        fields: {
            create_time: {type: "date"},
            billduration: {type: "number"},
            show_popup: {type: "boolean"}
        }
    },
    parse: function (response) {
        response.data.map(function(doc) {
            doc.create_time = new Date(doc.create_time * 1000);
            return doc;
        })
        return response;
    },
    columns: [{
            field: "create_time",
            title: "@Time@",
            template: function(dataItem) {
                return (kendo.toString(dataItem.create_time, "dd/MM/yy H:mm:ss") ||  "").toString();
            },
            width: 140
        },{
            field: "assign",
            title: "@Assign@",
            width: 100
        },{
            field: "customer.name",
            title: "@Customer name@",
            template: function(dataItem) {
                var result = '';
                if(dataItem.customer.length) {
                    result = dataItem.customer.map(doc => `<b class="copy-item">${(doc.name || '').toString()}</b>`).join(" <i class='text-danger'>OR</i> ");
                }
                return result
            }
        },{
            field: "customernumber",
            title: "@Phone@",
            template: function(dataItem) {
                var phone = (dataItem.customernumber || '').toString();
                return phone ? `<b href="javascript:void(0)" class="copy-item text-info">${phone}</b>` : ``;
            },
            width: 100
        },{
            field: "duration",
            title: "@Duration@",
            width: 120
        },{
            field: "status",
            title: "@Status@",
            width: 120
        },{
            field: "mailbox",
            title: "@Mailbox@",
            width: 120
        },{
            // Use uid to fix bug data-uid of row undefined
            template: '<a role="button" class="btn btn-sm btn-circle btn-action" data-uid="#: uid #"><i class="fa fa-ellipsis-v"></i></a>',
            width: 20
        }]
}; 


function playAction(ele) {
    var uid = $(ele).data("uid"),
        dataItem = Table.dataSource.getByUid(uid),
        calluuid = dataItem.calluuid,
        callduration = dataItem.callduration;
    if(callduration) 
        play(calluuid);
    else notification.show("No recording", "warning");
}

function downloadAction(ele) {
    var uid = $(ele).data("uid");
        dataItem = Table.dataSource.getByUid(uid),
        calluuid = dataItem.calluuid,
        callduration = dataItem.callduration;
    if(callduration) 
        downloadRecord(calluuid);
    else notification.show("No recording", "warning");
}

function reassignAction(ele) {
    var uid = $(ele).data("uid");
    var calluuid = Table.dataSource.getByUid(uid).calluuid;
}
</script>

<!-- Table Styles Header -->
<ul class="breadcrumb breadcrumb-top">
    <li>@Manage@</li>
    <li>@Voicemail@</li>
    <li class="pull-right none-breakcrumb">
        <div class="input-group-btn column-widget">
            <a role="button" class="btn btn-sm dropdown-toggle" data-toggle="dropdown" onclick="editColumns(this)"><i class="fa fa-calculator"></i> <b>@Edit Columns@</b></a>
            <ul class="dropdown-menu dropdown-menu-right" style="width: 300px">
                <li class="dropdown-header text-center">@Choose columns will show@</li>
                <li class="filter-container" style="padding-bottom: 15px">
                    <div class="form-horizontal" data-bind="source: columns" data-template="column-template"/>
                </li>
            </ul>
        </div>
    </li>
</ul>
<!-- END Table Styles Header -->

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12" style="height: 80vh; overflow-y: auto; padding: 0">
            <!-- Table Styles Content -->
            <div id="grid"></div>
            <!-- END Table Styles Content -->
        </div>
    </div>
</div>

<div id="action-menu">
    <ul>
        <a href="javascript:void(0)" data-type="action/play" onclick="playAction(this)"><li><i class="fa fa-play text-info" style="padding-left: 3px"></i><span>Play</span></li></a>
        <a href="javascript:void(0)" data-type="action/download" onclick="downloadAction(this)"><li><i class="fa fa-cloud-download text-danger"></i><span>Download</span></li></a>
        <a href="javascript:void(0)" data-type="action/repopup" onclick="reassignAction(this)"><li><i class="fa fa-exchange text-warning"></i><span>Re Assign</span></li></a>
    </ul>
</div>
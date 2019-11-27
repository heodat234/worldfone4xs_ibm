<script>
var Config = {
    crudApi: `${ENV.vApi}`,
    templateApi: `${ENV.templateApi}`,
    collection: "cdr",
    observable: {
    },
    model: {
        id: "id",
        fields: {
            starttime: {type: "date"},
            billduration: {type: "number"},
            callduration: {type: "number"},
            show_popup: {type: "boolean"}
        }
    },
    parse: function (response) {
        response.data.map(function(doc) {
            doc.starttime = new Date(doc.starttime * 1000);
            return doc;
        })
        return response;
    },
    columns: [{
        field: "direction",
        title: "@Direction@",
        template: dataItem => templateDirection(dataItem),
        filterable: {
            ui: (element) => selectFilter(element, (ENV.type ? ENV.type + "_" : "") + "worldfonepbxmanager", "direction"),
            operators: {
              string: {
                eq: '@Equal to@',
              }
            }
        },
        width: 100
    },{
        field: "dialtype",
        title: "@Dial type@",
        width: 100
    },{
        field: "starttime",
        title: "@Time@",
        format: "{0: dd/MM/yy HH:mm}",
        width: 100
    },{
        field: "userextension",
        title: "@Extension@",
        width: 100
    },{
        field: "agentname",
        title: "@Agent name@",
        width: 100
    },{
        field: "customer.name",
        title: "@Customer name@",
        template: function(dataItem) {
            var result = '';
            if(dataItem.customer) {
                if(dataItem.customer.length) {
                    result = dataItem.customer.map(doc => `<a href="${ENV.baseUrl}manage/telesalelist/#/detail_customer/${doc.id}" target="_blank" class="grid-name" data-id="${doc.id}" title="@View detail@">${(doc.name || '').toString()}</a>`).join(" <i class='text-danger'>OR</i> ");
                    result += `<br><a href="javascript:void(0)" onclick="defineCustomerCdr(this)" class="text-danger"><i>@Define@ @customer@ @of@ @this call@</i></a>`
                } else {
                    result = `<a href="${ENV.baseUrl}manage/telesalelist/#/detail_customer/${dataItem.customer.id}" target="_blank" class="grid-name" data-id="${dataItem.customer.id}" title="@View detail@">${(dataItem.customer.name || '').toString()}</a>`;
                }
            }
            return result
        }
    },{
        field: "customernumber",
        title: "@Phone number@",
        template: function(dataItem) {
            var phone = (dataItem.customernumber || '').toString();
            return phone ? `<b href="javascript:void(0)" class="copy-item text-info">${phone}</b>` : ``;
        },
        width: 100
    },{
        field: "disposition",
        title: "@Result@",
        filterable: {
            ui: (element) => selectFilter(element, (ENV.type ? ENV.type + "_" : "") + "worldfonepbxmanager", "disposition"),
            operators: {
              string: {
                eq: '@Equal to@',
              }
            }
        },
        template: dataItem => templateDisposition(dataItem),
        width: 120
    },{
        field: "billduration",
        title: "@Bill duration@",
        width: 120
    },{
        field: "callduration",
        title: "@Call duration@",
        width: 120
    },{
        // Use uid to fix bug data-uid of row undefined
        template: '<a role="button" class="btn btn-sm btn-circle btn-action btn-primary" data-uid="#: uid #"><i class="fa fa-ellipsis-v"></i></a>',
        width: 20
    }],
    filterable: KENDO.filterable,
    reorderable: true
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

function repopupAction(ele) {
    var uid = $(ele).data("uid");
    var calluuid = Table.dataSource.getByUid(uid).calluuid;
    rePopup(calluuid);
}

$(document).on("click", ".grid-name", function() {
    var id = $(this).data("id"),
        url = ENV.baseUrl + "manage/telesalelist/#/detail_customer/" + id;
    window.open(url,'_blank','noopener');
})

window.onload = function() {
    <?php if(!empty($filter)) { ?>
        Config.filter = <?= $filter ?>;
    <?php } ?>
    Table.init();
}
</script>

<!-- Table Styles Header -->
<ul class="breadcrumb breadcrumb-top">
    <li>@Manage@</li>
    <li>@CDR@</li>
    <li class="pull-right none-breakcrumb">
        <a role="button" class="btn btn-sm" data-field="starttime" onclick="customFilter(this, Table.dataSource)"><i class="fa fa-filter"></i> <b>@Custom Filter@</b></a>
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
    <div class="row filter-mvvm" style="display: none; margin: 10px 0">
    </div>
    <div class="row">
        <div class="col-sm-12" style="padding: 0">
            <!-- Table Styles Content -->
            <div id="grid"></div>
            <!-- END Table Styles Content -->
        </div>
    </div>
</div>

<div id="action-menu">
    <ul>
        <a href="javascript:void(0)" data-type="action/play" onclick="playAction(this)"><li><i class="fa fa-play text-info" style="padding-left: 3px"></i><span>@Play@ @recording@</span></li></a>
        <a href="javascript:void(0)" data-type="action/download" onclick="downloadAction(this)"><li><i class="fa fa-cloud-download text-danger"></i><span>@Download@ @recording@</span></li></a>
        <a href="javascript:void(0)" data-type="action/repopup" onclick="repopupAction(this)"><li><i class="hi hi-new_window text-warning"></i><span>@Repopup@</span></li></a>
        <a href="javascript:void(0)" data-type="action/detail" class="hidden"><li><i class="fa fa-exclamation-circle text-info"></i><span>@Detail@</span></li></a>
    </ul>
</div>
<script>
var Config = {
    crudApi: `${ENV.restApi}`,
    templateApi: `${ENV.templateApi}`,
    collection: "import_history",
    observable: {
    },
    scrollable: true,
    model: {
        id: "id",
        fields: {
            name: {type: "string"},
            exists: {type: "boolean"},
        }
    },
    columns: [{
            selectable: true,
            width: 32
        },{
            field: "file_name",
            title: "@File name@"
        },{
            field: "begin_import",
            title: "@Import at@",
            template: data => gridTimestamp(data.begin_import, "dd/MM/yy H:mm"),
            width: 140
        },{
            field: "status",
            title: "@Result@",
            template: function(data) {
                var html  = "";
                switch(data.status) {
                    case 0:
                        html  = `<div class="progress" style="margin-bottom: 0">
                          <div class="progress-bar progress-bar-danger" role="progressbar" style="width: 100%">
                            Failure
                          </div>
                        </div>`;
                        break;
                    case 1:
                        html  = data.total ? `<div class="progress" style="margin-bottom: 0">
                          <div class="progress-bar ${data.complete != data.total ? 'progress-bar-warning progress-bar-striped active' : 'progress-bar-success'}" role="progressbar" style="width: 100%">
                            Complete ${data.complete} records
                          </div>
                        </div>`: 
                        `<div class="progress" style="margin-bottom: 0">
                          <div class="progress-bar progress-bar-warning progress-bar-striped active" role="progressbar" style="width: 100%">
                            Progressing complete ${data.complete} records
                          </div>
                        </div>`;
                        break;
                    case 2: default:
                        html  =  data.total ? `<div class="progress" style="margin-bottom: 0">
                          <div class="progress-bar progress-bar-info progress-bar-striped active" role="progressbar" style="width: ${data.total ? (data.complete*100 / data.total) : 0}%">
                            ${data.total ? (data.complete*100 / data.total) : 0}%
                          </div>
                        </div>`: 
                        `<div class="progress" style="margin-bottom: 0">
                          <div class="progress-bar progress-bar-warning progress-bar-striped active" role="progressbar" style="width: 100%">
                            Progressing complete ${data.complete} records
                          </div>
                        </div>`;
                        break;
                }
                return html;
            },
            values: [{text: "@Failed@", value: 0},{text: "@Success@", value: 1},{text: "@Processing@", value: 2}],
            width: 240
        },{
            field: "description",
            title: "@Description@",
        },{
            // Use uid to fix bug data-uid of row undefined
            template: `<a role="button" class="k-button" href="javascript:void(0)" data-type="import" onclick="importFile(this)"><i class="fa fa-upload text-danger"></i>&nbsp;@Reimport@</a>`,
            width: 140
        }]
}; 
</script>

<!-- Table Styles Header -->
<ul class="breadcrumb breadcrumb-top">
    <li>@Manage@</li>
    <li>@Data@</li>
    <li>@Upload file@</li>
    <li class="pull-right none-breakcrumb">
        <div class="input-group-btn column-widget">
            <a role="button" class="btn btn-sm btn-success btn-alt dropdown-toggle" data-toggle="dropdown" onclick="editColumns(this)"><i class="fa fa-calculator"></i> <b>@Edit Columns@</b></a>
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

<script type="text/javascript">
window.onload = function() {
    Table.init();
}

function importFile(ele) {
    swal({
        title: "@Are you sure@?",
        text: "@Reimport this file@!",
        icon: "warning",
        buttons: true,
        dangerMode: true,
    })
    .then((sure) => {
        if (sure) {
            var uid = $(ele).closest("tr").data('uid');
            var dataItem = Table.dataSource.getByUid(uid);
            $.get({
                url: ENV.vApi + "import/reImport/" + dataItem.id, 
                global: false,
                success: function(res) {
                    notification.show(res.message, res.status ? "success" : "error")
                }
            });
            Table.dataSource.read();
        }
    });
}
</script>
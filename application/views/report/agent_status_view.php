<script>
var Config = {
    crudApi: `${ENV.restApi}`,
    templateApi: `${ENV.templateApi}`,
    collection: "agentstatus",
    observable: {
    },
    model: {
        id: "id",
        fields: {
            starttime: {type: "date"},
            endtime: {type: "date"},
            statuscode : {type: "number"},
        }
    },
    parse: function (response) {
        response.data.map(function(doc) {
            doc.duration = doc.endtime ? doc.endtime - doc.starttime : undefined;
            doc.starttime = new Date(doc.starttime * 1000);
            doc.endtime = doc.endtime ? new Date(doc.endtime * 1000) : null;
            return doc;
        })
        return response;
    },
    columns: [{
            field: "starttime",
            title: "@Start@",
            template: function(dataItem) {
                return (kendo.toString(dataItem.starttime, "dd/MM/yy H:mm:ss") ||  "").toString();
            },
            width: 140
        },{
        	field: "endtime",
            title: "@End@",
            template: function(dataItem) {
                return (kendo.toString(dataItem.endtime, "dd/MM/yy H:mm:ss") ||  "").toString();
            },
            width: 140
        },{
            field: "duration",
            title: "@Duration@",
            template: function(dataItem) {
                return (dataItem.duration !== undefined) ? kendo.toString(new Date(dataItem.duration * 1000  + (((new Date()).getTimezoneOffset() - 60) * 60000)), "H:mm:ss") : "@Not over@";
            },
            filterable: false,
            width: 140
        },{
            field: "extension",
            title: "@Extension@",
            width: 100
        },{
            field: "status.text",
            title: "@Status@",
            width: 180
        },{
            field: "substatus",
            title: "@Sub status@",
            width: 140
        },{
            field: "note",
            title: "@Note@"
        },{
            field: "endnote",
            title: "@End note@"
        }]
}; 
</script>

<!-- Page content -->
<div id="page-content">
    <!-- Table Styles Header -->
    <ul class="breadcrumb breadcrumb-top">
        <li>@Report@</li>
        <li>@Status@</li>
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
            <a role="button" class="btn btn-sm" onclick="Table.grid.saveAsExcel()"><i class="fa fa-file-excel-o"></i> <b>@Export@</b></a>
        </li>
    </ul>
    <!-- END Table Styles Header -->

    <div class="container-fluid">
        <div class="row filter-mvvm" style="display: none; margin: 10px 0">
        </div>
        <div class="row">
            <div class="col-sm-12" style="height: 80vh; overflow-y: auto; padding: 0">
                <!-- Table Styles Content -->
                <div id="grid"></div>
                <!-- END Table Styles Content -->
            </div>
        </div>
    </div>
</div>
<!-- END Page Content -->
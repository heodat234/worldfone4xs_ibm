<script>
var Config = {
    crudApi: `${ENV.restApi}`,
    templateApi: `${ENV.templateApi}`,
    collection: "note",
    scrollable: true,
    observable: {
    },
    model: {
        id: "id",
        fields: {
            createdAt: {type: "date"}
        }
    },
    parse: function (response) {
        response.data.map(function(doc) {
            doc.createdAt = new Date(doc.createdAt * 1000);
            return doc;
        })
        return response;
    },
    columns: [
    {
        field: "createdAt",
        title: "@Created at@",
        format: "{0: dd/MM/yy HH:mm}",
        width: 120
    },
    {
        field: "content",
        title: "@Content@"
    },
    {
        field: "createdBy",
        title: "@Created by@",
        template: function(data) { 
            if(convertExtensionToAgentname[data.createdBy] != undefined)
                return convertExtensionToAgentname[data.createdBy];
           else return 'system';
          },
        filterable: false,
        width: 150
    },
    ],
    filterable: KENDO.filterable,
    reorderable: true
}; 

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
    <li>@Note@</li>
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

<style type="text/css" media="screen">
	#grid .k-grid-content{
		height: 350px;
	}	
</style>
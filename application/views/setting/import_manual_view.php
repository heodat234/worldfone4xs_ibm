<script>
var Config = {
    crudApi: `${ENV.restApi}`,
    vApi: `${ENV.vApi}`,
    templateApi: `${ENV.templateApi}`,
    observable: {
        trueVar: true,
    },
    model: {
        id: "id",
        fields: {
            name: {type: "string", defaultValue: ""},
            queue: {type: "string"},
            members: {type: "object"},
            queues: {type: "object"}
        }
    }
}; 
</script>

<!-- Table Styles Header -->
<ul class="breadcrumb breadcrumb-top">
    <li>@Setting@</li>
    <li>@Manage Create Calling List@</li>
</ul>
<!-- END Table Styles Header -->

<div class="container-fluid" id="allview">
    <div class="row">
        <div class="col-md-12" style="border-right: 1px solid lightgray; padding-right: 40px">
            <div class="row">
                <h4 class="text-center" style="margin: 20px 0 10px">
                    <span style="font-weight: 500">@Import Monitor@</span>
                </h4>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div id="list_file_import"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function(){
        $('#list_file_import').kendoGrid({
            dataSource: {
                transport: {
                    read: {
                        url: Config.vApi + 'Import_manual/read_list_file_import'
                    },
                },
                schema: {
                    data: 'data',
                    total: 'total',
                    parse: function(response) {
                        response.data.map(function(doc) {
                            doc.begin_import = doc.begin_import ? new Date(doc.begin_import * 1000) : undefined;
                            doc.complete_import = doc.complete_import ? new Date(doc.complete_import * 1000) : undefined;
                            return doc;
                        });
                        return response;
                    }
                }
            },
            pageable: {
                refresh: true,
                pageSize: 10,
            },
            columns: [{
                field: "fileName",
                title: "File Import Status",
                width: 530
            }, {
                field: "importStatus",
                title: "Status",
                template: function(dataItem) {
                    if(dataItem.importStatus == -1) {
                        return '<span class="label label-default">NOT YET</span>';
                    }
                    if(dataItem.importStatus == 0) {
                        return '<span class="label label-danger">FAIL</span>';
                    }
                    if(dataItem.importStatus == 1) {
                        return '<span class="label label-success">SUCCESS</span>';
                    }
                    if(dataItem.importStatus == 2) {
                        return '<span class="label label-warning">PENDING</span>';
                    }
                },
                width: 100
                // template: '# if(status == true){ # <i class="fa fa-check text-success"></i> #} else {# <i class="fa fa-times text-danger"></i> #}#'
            },
            {
                title: 'Total',
                field: 'total'
            },
            {
                title: 'Begin import',
                field: 'begin_import',
                format: "{0: dd/MM/yyyy HH:mm:ss}",
            },
            {
                title: 'Complete import',
                field: 'complete_import',
                format: "{0: dd/MM/yyyy HH:mm:ss}",
            },
            {
                title: "Re Import",
                width: 100,
                template: function(dataItem){
                    if(dataItem.importStatus != 2) {
                        return `<a style="font-size: 12px" class="btn btn-primary" onclick= 'reImport("${dataItem.fileName}")' >Re Import</a>`
                    }
                    else {
                        return `<span></span>`
                    }
                }
            }],
        })

        var viewModel = kendo.observable({
        
        });

    kendo.bind($("#allview"), viewModel);
    // setInterval(function(){
    //     checkOneUserOnly();
    // }, 3000)
    })

    function reImport(collection){
        swal({
            title: `@Are your sure@?`,
            text: `@Re Import This File?@?`,
            icon: "warning",
            buttons: true,
            dangerMode: false,
        })
        .then((sure) => {
            if(sure){
                var test = $("#list_file_import").data("kendoGrid").dataSource.read();
                console.log(test);
                $.get(Config.vApi + `Import_manual/reImport?param=${collection}`, '', function(){});
                notification.show("Processing, Wait 10 seconds pls!", "success");
            }
        });
    }

    setInterval(function(){
        $("#list_file_import").data("kendoGrid").dataSource.read();

        // $("#list_data_imported").data("kendoGrid").dataSource.read();

        // $("#basket_campaign").data("kendoGrid").dataSource.read();

        // $("#diallist").data("kendoGrid").dataSource.read();

    }, 5000);

    // $("#list_file_import").data("kendoGrid").dataSource.read();

</script>
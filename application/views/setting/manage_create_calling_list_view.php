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
                <div class="col-md-6">
                    <div id="list_file_import"></div>
                </div>
                <div class="col-md-6">
                    <div id="list_data_imported"></div>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <h4 class="text-center" style="margin: 20px 0 10px">
                    <span style="font-weight: 500">@Basket Campaign Monitor@</span>
            </h4>
            <div class="col-md-12">
                <div id="basket_campaign"></div>
            </div>
        </div>
        <div class="col-md-12">
            <h4 class="text-center" style="margin: 20px 0 10px">
                    <span style="font-weight: 500">@Calling Diallist Monitor@</span>
            </h4>
            <div class="col-md-12">
                <div id="diallist"></div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function(){

        function checkOneUserOnly(){
            $.get(Config.vApi + `Manage_create_calling_list/oneUserOnly`, '', function(result){

                if(result.status == 0){
                   swal({
                        title: `@This site is accessed by@ ${result.extension} - ${convertExtensionToAgentname[result.extension]}?`,
                        text: `@Press OK to getback to Home and Cancel to Reload?@?`,
                        icon: "warning",
                        buttons: true,
                        dangerMode: false,
                    }).then((sure) => {
                        if(sure){
                            window.location.href = "/";
                        }else{
                            location.reload();
                        }
                    });

                }

            });
        }
        checkOneUserOnly();

        $('#list_file_import').kendoGrid({
            dataSource: {
                transport: {
                    read: {
                        url: Config.vApi + 'Manage_create_calling_list/read_list_file_import'
                    },
                }
            },
            pageable: {
                refresh: true,
                pageSize: 10,
            },

            columns: [{
                field: "fileName",
                title: "File Import Status",
                width: 230
            }, {
                field: "status",
                title: "Status",
                width: 50,
                template: '# if(status == true){ # <i class="fa fa-check text-success"></i> #} else {# <i class="fa fa-times text-danger"></i> #}#'
            },
            ],


        }) // end list_file_import

        window.check_data_imported = [];
        $('#list_data_imported').kendoGrid({
            dataSource: {
                transport: {
                    read: {
                        url: Config.vApi + 'Manage_create_calling_list/read_list_data_imported'
                    },
                }
            },
            pageable: {
                refresh: true,
                pageSize: 10,
            },

            columns: [{
                field: "collection",
                title: "Data Imported",
                width: 120
            }, {
                field: "totalData",
                title: "Data",
                template: function(data){
                    if(data.collection == 'LNCJ05' || data.collection == 'ListOfAccount')
                    {
                        window.check_data_imported[data.collection] = data.totalData;
                        if(window.check_data_imported['LNCJ05'] != 0 && window.check_data_imported['ListOfAccount'] !=0)
                            $('#basket_campaign').show();
                        else
                            $('#basket_campaign').hide();
                    }

                    return `<span>${data.totalData}</span>`;
                },
                width: 120,
            },{
                title: "Re Import",
                width: 100,
                template: function(data){
                    return `<a style="font-size: 12px" class="btn btn-primary" onclick= 'reImport("${data.collection}")' >Re Import</a>`
                }
            }
            ],


        }) // end list_data_imported

        window.check_data_basket = [];
        $('#basket_campaign').kendoGrid({
            dataSource: {
                transport: {
                    read: {
                        url: Config.vApi + 'Manage_create_calling_list/read_basket_campaign'
                    },
                }
            },
            pageable: {
                refresh: true,
                pageSize: 10,
            },

            columns: [{
                field: "basket",
                title: "Basket Campaign",
                width: 120
            },{
               field: "totaldata",
               title: "Data",
               template: function(data){
                window.check_data_basket[data.basket] = data.totaldata;
                if(window.check_data_basket['SIBS'] ==0 || window.check_data_basket['CARD'] ==0 || window.check_data_basket['CARD'] == 'Running' || window.check_data_basket['SIBS'] ==0){
                    $('#diallist').hide();
                }else{
                    $('#diallist').show();
                }
                if(data.totaldata == "Running"){
                    return `<span class="label label-info animated pulse">${data.totaldata}</span>`;
                }else
                if(+data.totaldata > 0)
                    return ` <i class="fa fa-check text-success"></i> `;
                else{
                    return `<i class="fa fa-times text-danger"></i>`;
                }
            },
               width: 120
            }, {
                title: "Re Running",
                width: 100,
                template: function(data){
                    return `<a style="font-size: 12px" class="btn btn-primary" onclick= 'reRunBasketCampaign("${data.basket}")' >Re Run Campaign</a>`
                }
            }
            ],


        }) // end basket_campaign

        $('#diallist').kendoGrid({
            dataSource: {
                transport: {
                    read: {
                        url: Config.vApi + 'Manage_create_calling_list/read_diallist'
                    },
                }
            },
            pageable: {
                refresh: true,
                pageSize: 10,
            },

            columns: [{
                field: "diallist",
                title: "Diallist",
                width: 120
            },{
               field: "total_detail",
               title: "Total Data",
               template: function(data){
                
                    if(typeof window.checkRunning !== 'undefined' ){
                        if(window.checkRunning.includes(data.total_detail)){
                            return `<span class="label label-info">${data.total_detail}</span>`;
                        }else{
                             window.checkRunning.push(data.total_detail);
                            return `<span class="label label-info">Running</span>`;
                        }
                    }else{
                        window.checkRunning = [];
                        window.checkRunning.push(data.total_detail);
                        return `<span class="label label-info">Running</span>`;
                    }
               },
               width: 120
            }, {
                title: "Re Running",
                width: 100,
                template: function(data){
                    return `<a style="font-size: 12px" class="btn btn-primary" onclick= 'reRunDiallist("${data.diallist}")' >Re Run Diallist</a>`
                }
            }
            ],


        }) // end diallist

    var viewModel = kendo.observable({
        
    });

    kendo.bind($("#allview"), viewModel);
    setInterval(function(){
        checkOneUserOnly();
    }, 3000)
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
                $.get(Config.vApi + `Manage_create_calling_list/reImport?param=${collection}`, '', function(){})
                notification.show("Processing, Wait 10 seconds pls!", "success");
                $("#list_data_imported").data("kendoGrid").dataSource.read();
            }
        });
    }

    function reRunBasketCampaign(basket){
        swal({
            title: `@Are your sure@?`,
            text: `@Re Create This Basket@?`,
            icon: "warning",
            buttons: true,
            dangerMode: false,
        })
        .then((sure) => {
            if(sure){
                $.get(Config.vApi + `Manage_create_calling_list/reRunBasketCampaign?param=${basket}`, '', function(){})
                notification.show("Processing Basket, Waitting AVG 10mins", "success");
                $("#basket_campaign").data("kendoGrid").dataSource.read();
            }
        });           
    }

    function reRunDiallist(diallist){
        swal({
            title: `@Are your sure@?`,
            text: `@Re Create This Diallist@?`,
            icon: "warning",
            buttons: true,
            dangerMode: false,
        })
        .then((sure) => {
            if(sure){
                $.get(Config.vApi + `Manage_create_calling_list/reRunDiallist?param=${diallist}`, '', function(){})
                notification.show("Processing Diallist, Waitting AVG 10 seconds", "success");
                $("#diallist").data("kendoGrid").dataSource.read();
            }
        });
    }

    setInterval(function(){
        $("#list_file_import").data("kendoGrid").dataSource.read();

        $("#list_data_imported").data("kendoGrid").dataSource.read();

        $("#basket_campaign").data("kendoGrid").dataSource.read();

        $("#diallist").data("kendoGrid").dataSource.read();

    }, 5000)

    

</script>
<div id="sibs-history">
<!--    <div class="col-sm-3" style="margin: 10px 0" id="page-widget"></div>-->
<!--    <div class="col-sm-9 filter-mvvm" style="display: none; margin: 10px 0"></div>-->
    <div class="col-sm-12" style="overflow-y: auto; padding: 0">
        <div id="history-grid" data-role="grid"
             data-sortable="true"
             data-pageable="true"
             data-filterable="true"
             data-detail-init="model.detailInit"
             data-columns="[
                                {
                                    'field': 'begin_import',
                                    'title': '@Begin Imported@',
                                    'template': function(dataItem) {
                                        console.log(dataItem.begin_import);
                                        return (kendo.toString(new Date(dataItem.begin_import * 1000), 'dd/MM/yy H:mm:ss') ||  '').toString();
                                    },
                                    'width': '200px'
                                },{
                                    'field': 'complete_import',
                                    'title': '@Finish Imported@',
                                    'template': function(dataItem) {
                                        return (kendo.toString(new Date(dataItem.complete_import * 1000), 'dd/MM/yy H:mm:ss') ||  '').toString();
                                    },
                                    'width': '200px'
                                },{
                                    'field': 'file_name',
                                    'title': '@File Name@',
                                    'width': '300px'
                                },{
                                    'field': 'source',
                                    'title': '@Source@',
                                    'width': '150px'
                                },{
                                    'field': 'total_row',
                                    'title': '@Total@',
                                    'width': '100px'
                                },{
                                    'field': 'status',
                                    'title': '@Status@',
                                    'template': function(dataItem) {
                                        if(dataItem.status == 1) {
                                            return '<h4 style=\'font-weight: bold\'>Success</h4>';
                                        }else if(dataItem.status == 0) {
                                            return '<h4 style=\'font-weight: bold\'>Fail</h4>';
                                        }else if(dataItem.status == 2) {
                                            return '<div class=\'col-sm-8\'><div class=\'progress\' data-role=\'progressbar\' data-min=\'0\' data-max=\'100\' data-value=\'false\'></div><div class=\'status-upload\'>Loading...</div></div><div class=\'col-sm-4 cancel-upload\'><a href=\'javascript:void(0)\' onclick=\'cancelUpload(this)\'>Cancel</a></div>';
                                        }
                                    },
                                },{
                                    // Use uid to fix bug data-uid of row undefined
                                    'template': '<a role=\'button\' class=\'btn btn-sm btn-circle btn-action\' style=\'background: yellow;\' data-uid=\'#: uid #\'><i class=\'fa fa-ellipsis-v\'></i></a>',
                                    'width': 50
                                }]"
             data-bind="source: historyData"></div>
    </div>
    <div id="history-action-menu" class="action-menu">
        <ul>
            <a href="javascript:void(0)" data-type="detail" onclick="detailData(this)"><li><i class="fa fa-exclamation-circle text-info"></i><span>View Detail</span></li></a>
            <li class="devide"></li>
<!--            <a href="javascript:void(0)" data-type="import" onclick="re_Upload(this)"><li><i class="fa fa-exclamation-circle text-info"></i><span>Re-Upload</span></li></a>-->
        </ul>
    </div>
</div>

<script id="rowTemplate" type="text/x-kendo-tmpl">
    #if(status === 0) {#
        <tr data-uid="#: uid #" class='error-row'>
            <td>
                #: begin_import #
            </td>
            <td>
                #: complete_import #
            </td>
            <td>
                #: file_name #
            </td>
            <td>
                #: source #
            </td>
            <td>
                #: total_row #
            </td>
            <td>
                #: success_row #
            </td>
            <td>
                #: error_row #
            </td>
            <td>
                #: status #
            </td>
        </tr>
    #}#
</script>

<style type="text/css">
	.swal-footer {
	    text-align: center;
	}
	.swal-button--ftp {
		background: #0f73a5;
	}
	.swal-button--danger {
		background: #f1be06;
	}
    .grid {
        color: firebrick;
    }
    
    #history-action-menu {
        display: none;
    }

    .error-row {
        background-color: #F8D7DA;
        color: #721C24;
        font-weight: bold;
    }

    .progress{
        height: 15px;
        border-radius: 20px;
        width: 100%
    }

    .status-upload{
        margin-top: -17px;
        opacity: 0.5;
        text-align: center;
        font-size: 10px;
        width: 100%;
    }

    .cancel-upload > a {
        color: red;
        text-decoration: underline;
    }
</style>
<script id="detail-template" type="text/x-kendo-template">
  <div class="jsoneditor" style="width: 100%; height: 400px;"></div>
</script>
<script>
    var model = kendo.observable({
        historyData: new kendo.data.DataSource({
            pageSize: 5,
            transport: {
                read: {
                    url: ENV.vApi + "sibs/importHistoryRead",
                },
                parameterMap: parameterMap
            },
            schema: {
                data: "data",
                total: "total"
            },
            serverFiltering: true,
            filter: [{field: 'collection', operator: 'eq', value: "Sibs"}]
        }),
        detailInit: function (e) {
            $("<div/>").appendTo(e.detailCell).kendoGrid({
                dataSource: {
                    transport: {
                        read: {
                            url: ENV.vApi + "sibs/importHistoryDetail",
                        },
                        parameterMap: parameterMap
                    },
                    schema: {
                        data: "data",
                        total: "total"
                    },
                    serverPaging: true,
                    serverSorting: true,
                    serverFiltering: true,
                    pageSize:6,
                    filter: [
                        {field: "import_id", operator: "eq", value: e.data.id},
                        {field: 'result', operator: 'eq', 'value': 'error'}
                    ],
                },
                scrollable: false,
                sortable: true,
                pageable: true,
                columns: [
                    {field: "error_cell", title: '@Cell error@'},
                    {field: "type", title:"@Data type@"},
                    {field: "error_mesg", title:"@Error message@"},
                ]
            });
        },
        dataBound: function(e) {
            e.sender.expandRow(e.sender.tbody.find("tr.k-master-row").first());
        }
    });
var sibsHistory = function() {
    return {
        model: model,
        init: function () {
            kendo.bind("#sibs-history", kendo.observable(this.model));
            /*
             * Right Click Menu
             */
            var menu = $("#history-action-menu");
            if(!menu.length) return;

            $("html").on("click", function() {menu.hide()});

            $(document).on("click", "#history-grid tr[role=row] a.btn-action", function(e){
                console.log("Button");
                let row = $(e.target).closest("tr");
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
                    menu.find("a").data('uid',uid);

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
                    console.log('open menu');
                }
            }
        }
    }
}();
sibsHistory.init();

</script>
<script type="text/javascript">
	function detailData(ele) {
		var uid = $(ele).data('uid');
		var dataItem = $("#history-grid").data("kendoGrid").dataSource.getByUid(uid);
		router.navigate(`/detail/${dataItem.id}`);
	}

	function re_Upload(ele) {
        var uid = $(ele).data('uid');
        var dataItem = $("#history-grid").data("kendoGrid").dataSource.getByUid(uid);
        console.log(dataItem);
    }
</script>
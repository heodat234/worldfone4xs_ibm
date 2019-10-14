<div id="sc-schedule-detail-history">
    <!--    <div class="col-sm-3" style="margin: 10px 0" id="page-widget"></div>-->
    <!--    <div class="col-sm-9 filter-mvvm" style="display: none; margin: 10px 0"></div>-->
    <div class="col-sm-12" style="overflow-y: auto; padding: 0">
        <div id="history-detail-grid" data-role="grid"
             data-sortable="false"
             data-pageable="true"
             data-filterable="false"
             data-toolbar="['excel']"
             data-columns='[
                {
                    field: "from_date",
                    title: "@Date@",
                    template: (dataItem) => {
                        return gridDate(dataItem.from_date, "dd/MM/yyyy");
                    },
                    filterable: false
                },{
                    field: "dealer_code",
                    title: "@Dealer code@",
                    filterable: false
                },{
                    field: "sc_code",
                    title: "@SC code@",
                    template: (dataItem) => {
                        return gridArray(dataItem.sc_code);
                    },
                    filterable: false
                },{
                    // Use uid to fix bug data-uid of row undefined
                    title: `<a class="btn btn-sm btn-circle btn-action btn-primary" onclick="return deleteDataItemChecked();"><i class="fa fa-times-circle"></i></a>`,
                    template: `<a role="button" class="btn btn-sm btn-circle btn-action btn-primary" data-uid="#: uid #"><i class="fa fa-ellipsis-v"></i></a>\`,
                    width: 20
                }]'
             data-bind="source: historyDetailData"></div>
    </div>
</div>
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
</style>
<script id="detail-template" type="text/x-kendo-template">
    <div class="jsoneditor" style="width: 100%; height: 400px;"></div>
</script>
<script>
    var scScheduleDetailHistory = function() {
        var model = kendo.observable({
            historyDetailData: new kendo.data.DataSource({
                pageSize: 5,
                transport: {
                    read: {
                        url: ENV.vApi + "sc_schedule/importHistoryDetail",
                    },
                    parameterMap: parameterMap
                },
                schema: {
                    data: "data",
                    total: "total"
                },
                serverFiltering: true,
                filter: [
                    {field: 'import_id', operator: 'eq', value: '<?= $this->input->get("id") ?>'},
                    {field: 'result', operator: 'eq', value: 'success'}
                ]
            }),
        });
        return {
            model: model,
            init: function () {
                kendo.bind("#sc-schedule-detail-history", kendo.observable(this.model));
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
    scScheduleDetailHistory.init();
</script>
<script type="text/javascript">
    // var router = new kendo.Router({routeMissing: function(e) { router.navigate("/") }});
    // function re_Upload(ele) {
    // 	swal({
    // 	    title: "Do you want to Re-Upload this file?",
    // 	    // text: "Once deleted, you will not be able to recover this document!",
    // 	    icon: "warning",
    // 	    buttons: {
    // 	    	ftp: {text:"By FTP",value:"ftp"},
    // 	    	confirm: {text:"By Manual", value:"manual"},
    // 		    cancel: "Cancel"
    // 		},
    // 	 	dangerMode: true,
    //     })
    //     .then((value) => {
    //     	var uid = $(ele).data('uid');
    // 		var dataItem = Table.dataSource.getByUid(uid);
    // 	  	switch (value) {
    // 	    	case "ftp":
    // 	    		console.log(dataItem);
    //       			swal("Pikachu fainted!");
    // 	      		break;
    // 	    	case "manual":
    // 	      		swal("Gotcha!", "Pikachu was caught!", "success");
    // 	      		break;
    // 	    	default:
    //
    // 	  	}
    // 	});
    //
    // }
    // function divideList(ele) {
    // 	var uid = $(ele).data('uid');
    // 	var dataItem = Table.dataSource.getByUid(uid);
    // 	router.navigate(`/divide/${dataItem.id}`);
    // }
    //
    function detailData(ele) {
        var uid = $(ele).data('uid');
        var dataItem = Table.dataSource.getByUid(uid);
        router.navigate(`/detail/${dataItem.id}`);
        // router.navigate(`/`);
    }

    // $(document).on("click", ".grid-name", function() {
    // 	detailData($(this).closest("tr"));
    //     divideList($(this).closest("tr"));
    // })

    var menu = $("#action-menu");
    // if(!menu.length) return;

    $("html").on("click", function() {menu.hide()});

    $(document).on("click", "#grid tr[role=row] a.btn-action", function(e){
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
        }
    }

</script>
<div class="col-sm-12" style="overflow-y: auto; padding: 0">
	<div id="grid-sms"></div>
</div>
<div id="sms-action-menu" class="action-menu">
    <ul>
        <a href="javascript:void(0)" data-type="detail" onclick="sendSMSNow(this)"><li><i class="gi gi-exit text-info"></i><span>@Send@</span></li></a>
        <li class="devide"></li>
        <a href="javascript:void(0)" data-type="delete" onclick="deleteDataItem(this)"><li><i class="fa fa-times-circle text-danger"></i><span>@Delete@</span></li></a>
    </ul>
</div>
<script>
var Config = {
    crudApi: `${ENV.restApi}`,
    templateApi: `${ENV.templateApi}`,
    collection: "sms_pending",
    parse: response => {
    	response.data.map(doc => {
    		doc.createdAt = new Date(doc.createdAt * 1000);
    	})
    	return response;
    },
    observable: {
    },
    model: {
        id: "id",
        fields: {
        }
    },
    columns: [{
            selectable: true,
            width: 32,
        },{
            field: "createdAt",
            title: "@Created at@",
            template: (data) => gridDate(data.createdAt),
            width: 180,
        },{
            field: "createdBy",
            title: "@Created by@",
            width: 100,
        },{
            field: "phone",
            title: "@Phone@",
            width: 120,
        },{
            field: "content",
            title: "@Content@",
        },{
            // Use uid to fix bug data-uid of row undefined
            title: `<a class='btn btn-sm btn-circle btn-action' onclick='return deleteDataItemChecked();'><i class='fa fa-times-circle'></i></a>`,
            template: '<a role="button" class="btn btn-sm btn-circle btn-action" data-uid="#: uid #"><i class="fa fa-ellipsis-v"></i></a>',
            width: 32
        }]
}; 

var detailTable = function() {
    return {
        dataSource: {},
        grid: {},
        columns: Config.columns,
        init: async function() {
            var dataSource = this.dataSource = new kendo.data.DataSource({
                serverFiltering: true,
                serverPaging: true,
                serverSorting: true,
                serverGrouping: false,
                sort: [{field: "createdAt", dir: "desc"}],
                pageSize: 10,
                batch: false,
                schema: {
                    data: "data",
                    total: "total",
                    groups: "groups",
                    model: Config.model,
                    parse: Config.parse ? Config.parse : res => res
                },
                transport: {
                    read: {
                        url: Config.crudApi + Config.collection,
                    },
                    update: {
                        url: function(data) {
                            return Config.crudApi + Config.collection + "/" + data.id;
                        },
                        type: "PUT",
                        contentType: "application/json; charset=utf-8"
                    },
                    create: {
                        url: Config.crudApi + Config.collection,
                        type: "POST",
                        contentType: "application/json; charset=utf-8"
                    },
                    destroy: {
                        url: function(data) {
                            return Config.crudApi + Config.collection + "/" + data.id;
                        },
                        type: "DELETE",
                    },
                    parameterMap: parameterMap
                },
                sync: syncDataSource,
                error: errorDataSource
            });

            var grid = this.grid = $(`#grid-sms`).kendoGrid({
                dataSource: dataSource,
                resizable: true,
                pageable: {
                    refresh: true,
                    pageSizes: true
                },
                sortable: true,
                scrollable: true,
                columns: this.columns,
                filterable: true,
                editable: false
            }).data("kendoGrid");

            grid.selectedKeyNames = function() {
                var items = this.select(),
                    that = this,
                    checkedIds = [];
                $.each(items, function(){
                    if(that.dataItem(this))
                        checkedIds.push(that.dataItem(this).uid);
                })
                return checkedIds;
            }

            /*
             * Right Click Menu
             */
            var menu = $("#sms-action-menu");
            if(!menu.length) return;
            
            $("html").on("click", function() {menu.hide()});

            $(document).on("click", `#grid-sms tr[role=row] a.btn-action`, function(e){
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
        }
    }
}();
	
	function sendSMSNow(ele) {
		swal({
		    title: "@Are you sure@?",
		    text: "@Send this SMS right now@!",
		    icon: "warning",
		    buttons: true,
		    dangerMode: true,
	    })
	    .then((send) => {
			if (send) {
				var uid = $(ele).data('uid');
				var dataItem = detailTable.dataSource.getByUid(uid);
			    $.ajax({
			    	url: ENV.vApi + "sms/send/" + dataItem.id,
			    	success: function(res) {
			    		notification.show(res.status ? "Success" : res.message, res.status ? "success" : "error");
			    		detailTable.dataSource.read();
			    	},
			    	error: errorDataSource
			    })
			}
	    });
	}

	function deleteDataItem(ele) {
		swal({
		    title: "@Are you sure@?",
		    text: "@Once deleted, you will not be able to recover this document@!",
		    icon: "warning",
		    buttons: true,
		    dangerMode: true,
	    })
	    .then((willDelete) => {
			if (willDelete) {
				var uid = $(ele).data('uid');
				var dataItem = detailTable.dataSource.getByUid(uid);
			    detailTable.dataSource.remove(dataItem);
			    detailTable.dataSource.sync();
			}
	    });
	}

    function deleteDataItemChecked() {
        var checkIds = detailTable.grid.selectedKeyNames();
        if(checkIds.length) {
            swal({
                title: "Are you sure?",
                text: "Once deleted, you will not be able to recover these documents!",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
            .then((willDelete) => {
                if (willDelete) {
                    checkIds.forEach(uid => {
                        var dataItem = detailTable.dataSource.getByUid(uid);
                        detailTable.dataSource.remove(dataItem);
                        detailTable.dataSource.sync();
                    })
                }
            });
        } else {
            swal({
                title: "No row is checked!",
                text: "Please check least one row to remove",
                icon: "error"
            });
        }
    }
	detailTable.init();
</script>
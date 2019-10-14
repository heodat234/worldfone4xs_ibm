<?php $id = $this->input->get("id") ?>
<div class="col-sm-12" style="overflow-y: auto; padding: 0">
	<div id="grid-<?= $id ?>"></div>
</div>
<div id="detail-action-menu" class="action-menu">
    <ul>
        <a href="javascript:void(0)" data-type="detail" onclick="openForm({title: 'View diallist detail', width: 400}); viewForm(this)"><li><i class="fa fa-pencil-square-o text-info"></i><span>View</span></li></a>
        <li class="devide"></li>
        <a href="javascript:void(0)" data-type="update" onclick="openForm({title: 'Edit diallist detail', width: 400}); editForm(this)"><li><i class="fa fa-pencil-square-o text-warning"></i><span>Edit</span></li></a>
        <a href="javascript:void(0)" data-type="delete" onclick="deleteDataItem(this)"><li><i class="fa fa-times-circle text-danger"></i><span>Delete</span></li></a>
    </ul>
</div>
<script>
var Config = {
    id: '<?= $id ?>',
    crudApi: `${ENV.restApi}`,
    templateApi: `${ENV.templateApi}`,
    collection: "diallist_detail",
    observable: {
    },
    model: {
        id: "id",
        fields: {
            index: {type: "number"}
        }
    },
    columns: [{
            selectable: true,
            width: 32,
            locked: true
        },{
            field: "index",
            title: "#",
            width: 50,
            locked: true
        },{
            field: "phone",
            title: "@Phone@",
            width: 120,
            locked: true
        },{
            field: "assign",
            title: "@Assign@",
            width: 120,
            locked: true
        },{
            field: "callCode",
            title: "@Call code@",
            width: 120,
            locked: true
        },{
            field: "callResult",
            title: "@Calls@",
            template: diallistDetail => gridCallResult(diallistDetail.callResult),
            width: 120,
            locked: true
        },{
            // Use uid to fix bug data-uid of row undefined
            title: `<a class='btn btn-sm btn-circle btn-action' onclick='return deleteDataItemChecked();'><i class='fa fa-times-circle'></i></a>`,
            template: '<a role="button" class="btn btn-sm btn-circle btn-action" data-uid="#: uid #"><i class="fa fa-ellipsis-v"></i></a>',
            width: 32,
            locked: true
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
                filter: {field: "diallist_id", operator: "eq", value: Config.id},
                sort: [{field: "index", dir: "asc"}],
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

            var dataItemFull = this.diallist = await $.get(`${ENV.restApi}diallist/${Config.id}`);

            dataItemFull.columns.map((col, idx) => {
                col.width = 150;
            });

            this.columns = this.columns.concat(dataItemFull.columns);

            var grid = this.grid = $(`#grid-${Config.id}`).kendoGrid({
                dataSource: dataSource,
                resizable: true,
                pageable: {
                    refresh: true,
                    pageSizes: true,
                    input: true
                },
                sortable: true,
                scrollable: true,
                height: '80vh',
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
            var menu = $("#detail-action-menu");
            if(!menu.length) return;
            
            $("html").on("click", function() {menu.hide()});

            $(document).on("click", `#grid-${Config.id} tr[role=row] a.btn-action`, function(e){
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

    async function viewForm(ele) {
        var dataItem = detailTable.dataSource.getByUid($(ele).data("uid")),
            dataItemFull = await $.ajax({
                url: `${Config.crudApi+Config.collection}/${dataItem.id}`,
                error: errorDataSource
            }),
            formHtml = await $.ajax({
                url: Config.templateApi + Config.collection + "/view",
                data: {dataFields: JSON.stringify(detailTable.diallist.columns)},
                error: errorDataSource
            });
        var model = Object.assign({
            item: dataItemFull
        }, Config.observable);
        kendo.destroy($("#right-form"));
        $("#right-form").empty();
        var kendoView = new kendo.View(formHtml, { wrap: false, model: model, evalTemplate: false });
        kendoView.render($("#right-form"));
    }

	async function editForm(ele) {
		var dataItem = detailTable.dataSource.getByUid($(ele).data("uid")),
	        dataItemFull = await $.ajax({
	            url: `${Config.crudApi+Config.collection}/${dataItem.id}`,
	            error: errorDataSource
	        }),
		    formHtml = await $.ajax({
	    	    url: Config.templateApi + Config.collection + "/form",
                data: {dataFields: JSON.stringify(detailTable.diallist.columns)},
	    	    error: errorDataSource
	    	});
        var convert
		var model = Object.assign({
			item: dataItemFull,
            userDataSource: new kendo.data.DataSource({
                transport: {
                    read: ENV.restApi + "group/" + detailTable.diallist.group_id
                },
                schema: {
                    data: "members",
                    parse: function(response) {
                        console.log(response);
                        return response;
                    }
                }
            }),
			save: function() {
	            $.ajax({
	                url: `${Config.crudApi+Config.collection}/${dataItem.id}`,
                    type: "PUT",
                    contentType: "application/json; charset=utf-8",
                    data: kendo.stringify(this.item.toJSON()),
	                error: errorDataSource,
	                success: function() {
	                    detailTable.dataSource.read()
	                }
	            })
			}
		}, Config.observable);
		kendo.destroy($("#right-form"));
		$("#right-form").empty();
		var kendoView = new kendo.View(formHtml, { wrap: false, model: model, evalTemplate: false });
		kendoView.render($("#right-form"));
	}

	async function addForm() {
		var formHtml = await $.ajax({
		    url: Config.templateApi + Config.collection + "/form",
		    error: errorDataSource
		});
		var model = Object.assign({
			item: {},
			save: function() {
				detailTable.dataSource.add(this.item);
				detailTable.dataSource.sync().then(() => {detailTable.dataSource.read()});
			}
		}, Config.observable);
		kendo.destroy($("#right-form"));
		$("#right-form").empty();
		var kendoView = new kendo.View(formHtml, { wrap: false, model: model, evalTemplate: false });
		kendoView.render($("#right-form"));
	}

	function deleteDataItem(ele) {
		swal({
		    title: "Are you sure?",
		    text: "Once deleted, you will not be able to recover this document!",
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
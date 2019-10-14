<script>
var Config = {
    crudApi: `${ENV.restApi}`,
    templateApi: `${ENV.templateApi}`,
    collection: "card_transaction",
    observable: {
    },
    model: {
        id: "id"
    }
}; 
</script>

<!-- Table Styles Header -->
<ul class="breadcrumb breadcrumb-top">
    <li>@Manage@</li>
    <li>@Card transaction@</li>
    <li class="pull-right none-breakcrumb">
        <div class="input-group-btn column-widget">
            <a role="button" class="btn btn-sm dropdown-toggle" data-toggle="dropdown" onclick="editColumns(this)"><i class="fa fa-calculator"></i> <b>@Edit Columns@</b></a>
            <ul class="dropdown-menu dropdown-menu-right" style="width: 300px; height: 80vh; overflow-y: scroll;">
                <li class="dropdown-header text-center">@Choose columns will show@</li>
                <li class="filter-container" style="padding-bottom: 15px">
                    <div class="form-horizontal" data-bind="source: columns" data-template="column-template"/>
                </li>
            </ul>
        </div>
    </li>
</ul>
<!-- END Table Styles Header -->

<div class="container-fluid after-breadcrumb">
    <div class="row">
        <div class="col-sm-12" style="overflow-y: auto; padding: 0">
		    <!-- Table Styles Content -->
		    <div id="grid"></div>
		    <!-- END Table Styles Content -->
		</div>
		<div id="action-menu">
		    <ul>
		    </ul>
		</div>
		<script>
		var Config = Object.assign(Config, {
		    model: {
		        id: "id",
		        fields: {
		        	reCall: {type: "date"}
		        }
		    },
		    parse: function(response) {
		    	response.data.map(function(doc) {
		            doc.reCall = new Date(doc.reCall * 1000);
		            return doc;
		        })
		        return response;
		    },
		    columns: [],
		    filterable: KENDO.filterable
		}); 

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
					var dataItem = Table.dataSource.getByUid(uid);
				    Table.dataSource.remove(dataItem);
				    Table.dataSource.sync();
				}
		    });
		}

		function makeCallCheckTransaction(ele) {
			var uid = $(ele).data('uid'),
				dataItem = Table.dataSource.getByUid(uid),
				cif = dataItem.cif;
			swal({
			    title: "@Are you sure@?",
			    text: `Make call ${cif}.`,
			    icon: "warning",
			    buttons: true,
				dangerMode: true,
		    })
		    .then((code) => {
				if (code) {
				    $.ajax({
				    	url: ENV.namaApi + `core/getInfoCustomer`,
				    	type: "GET",
				    	data: {_: Date.now(), q: JSON.stringify({cif: cif})},
				    	contentType: "application/json; charset=utf-8",
                        success: function(response) {
                        	notification.show(response.message , response.status ? "success" : "error");
                        	if (typeof response.data[0] != 'undefined') {
                        		makeCall(response.data[0].phone, '', '')
                        	}
                        },
                        error: errorDataSource
				    })
				}
		    });
		}

		function acsIvrCheckTransaction(ele) {
			var uid = $(ele).data('uid'),
				dataItem = Table.dataSource.getByUid(uid),
				cif = dataItem.cif;
				last4CharsOfCard =  dataItem.card_number.substr(-4)
			swal({
			    title: "@Are you sure@?",
			    text: `Make call ${cif}.`,
			    icon: "warning",
			    buttons: true,
				dangerMode: true,
		    })
		    .then((code) => {
				if (code) {
				    $.ajax({
				    	url: ENV.namaApi + `core/getInfoCustomer`,
				    	type: "GET",
				    	data: {_: Date.now(), q: JSON.stringify({cif: cif})},
				    	contentType: "application/json; charset=utf-8",
                        success: function(response) {
                        	notification.show(response.message , response.status ? "success" : "error");
                        	if (typeof response.data[0] != 'undefined') {
                        		acsIvr(response.data[0].phone, last4CharsOfCard, 1)
                        	}
                        },
                        error: errorDataSource
				    })
				}
		    });
		}

		function acsIvr(phone, last4CharsOfCard, campaignId) {
			let data = {
				phone: phone, 
				last4CharsOfCard: last4CharsOfCard,
				campaignId: campaignId
			}

			$.ajax({
		    	url: `${ENV.vApi}acs/startAcs`,
		    	type: "POST",
		    	data: JSON.stringify(data),
		    	//contentType: "application/json; charset=utf-8",
	            success: function(response) {
	            	notification.show(response.message , response.status ? "success" : "error");	
	            },
	            error: errorDataSource
		    })
		}

		</script>
		<script id="rowTemplate" type="text/x-kendo-tmpl">
            <tr data-uid="#: uid #" style="background-color: #: data.color #">	       
            	<td><a role="button" class="btn btn-sm btn-circle btn-action" data-uid="#: uid #" onclick="makeCallCheckTransaction(this)" title="Call customer manually"><i class="fa fa-phone"></i></a>
			            <a role="button" class="btn btn-sm btn-circle btn-action" data-uid="#: uid #" onclick="acsIvrCheckTransaction(this)" title="Call automatically"><i class="fa fa-phone-square"></i></a></td>
            	# let fields = cardTransactionFields.data() #
            	# for (let i = 0; i < fields.length; i++) { console.log(fields[i]) #            	
            	<td style="#if(fields[i].hidden){##: 'display:none' ##}#">#: data[fields[i].field] #</td>    
            	# } #            	
           </tr>
        </script>
		<script type="text/javascript">			
			window.onload = function() {
				Table.init_card_transaction = function() {
		            var dataSource = this.dataSource = new kendo.data.DataSource({
		                serverFiltering: true,
		                serverPaging: true,
		                serverSorting: true,
		                serverGrouping: false,
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
		                        url: Config.crudApi + Config.collection
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

		            var grid = this.grid = $("#grid").kendoGrid({
		                dataSource: dataSource,
		                excel: {allPages: true},
		                excelExport: function(e) {
		                  var sheet = e.workbook.sheets[0];

		                  for (var rowIndex = 1; rowIndex < sheet.rows.length; rowIndex++) {
		                    var row = sheet.rows[rowIndex];
		                    for (var cellIndex = 0; cellIndex < row.cells.length; cellIndex ++) {
		                        if(row.cells[cellIndex].value instanceof Date) {
		                            row.cells[cellIndex].format = "dd-MM-yy hh:mm:ss"
		                        }
		                    }
		                  }
		                },
		                resizable: true,
		                pageable: {
		                    refresh: true,
		                    input: true,
		                    pageSizes: true
		                },
		                sortable: true,
		                scrollable: false,
		                columns: this.columns,
		                filterable: Config.filterable ? Config.filterable : true,
		                editable: false,
		                noRecords: {
		                    template: `<h2 class='text-danger'>${KENDO.noRecords}</h2>`
		                },
		                rowTemplate: kendo.template($("#rowTemplate").html()),
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
		            var menu = $("#action-menu");
		            if(!menu.length) return;
		            
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
		        }
				window.cardTransactionFields = new kendo.data.DataSource({
					serverFiltering: true,
					serverSorting: true,
					transport: {
						read: `${ENV.vApi}model/read`,
						parameterMap: parameterMap
					},
					schema: {
						data: "data",
						parse: function(response) {
							response.data.map(col => {
								if(col.sub_type) 
									col.subType = JSON.parse(col.sub_type);
								else col.subType = {};

								col.hidden = !col.subType.gridShow;
							})
							return response;
						}
					},
					filter: {
						field: "collection",
						operator: "eq",
						value: "card_transaction"
					},
					sort: {field: "index", dir: "asc"}
				})
				cardTransactionFields.read().then(function(){
					var columns = cardTransactionFields.data().toJSON();
					columns.map(col => {
						switch (col.type) {
							case "name":
								col.template = (dataItem) => gridName(dataItem[col.field]);
								break;
							case "phone":
								col.template = (dataItem) => gridPhone(dataItem[col.field]);
								break;
							case "arrayPhone":
								col.template = (dataItem) => gridArray(dataItem[col.field]);
								break;
							case "timestamp":
								if(col.field == "reCall")
									col.template = (dataItem) => gridReCall(dataItem[col.field]);
								else col.template = (dataItem) => gridDate(dataItem[col.field]);
								break;
							default:
								break;
						}
					});
					// columns.unshift({
			  //   		selectable: true,
			  //           width: 32,
			  //           locked: true
			  //       });
			        columns.unshift({
			            // Use uid to fix bug data-uid of row undefined
			            title: 'Action',
			            template: `<a role="button" class="btn btn-lg btn-circle btn-action" data-uid="#: uid #" onclick="makeCallCheckTransaction(this)" title="Call customer manually"><i class="fa fa-phone"></i></a>
			            <a role="button" class="btn btn-lg btn-circle btn-action" data-uid="#: uid #" onclick="acsIvrCheckTransaction(this)" title="Call automatically"><i class="fa fa-phone-square"></i></a>`,
			            width: 40
			        });
					Table.columns = columns;
					Table.init_card_transaction();
				})
			}

			function detailData(ele) {
				var uid = $(ele).data('uid');
				var dataItem = Table.dataSource.getByUid(uid);
				window.open(ENV.baseUrl + "manage/customer/#/detail/" + dataItem.foreign_id,'_blank','noopener');
			}

			$(document).on("click", ".grid-name", function() {
				detailData($(this).closest("tr"));
			})
		</script>
    </div>
</div>
<div class="col-sm-12 import-view">
	<div class="row" style="padding-top: 10px">
		<div class="col-sm-4">
			<div class="alert alert-success" style="cursor: pointer;">
		    	<h4>@Name@ @campaign@</h4>
		        <p class="text-right text-muted">
		        	<b data-bind="text: item.name"></b>
		        </p>
		    </div>
		</div>
		<div class="col-sm-4">
			<div class="alert alert-info" style="cursor: pointer;">
		    	<h4>@Group@</h4>
		        <p class="text-right text-muted">
		        	<b data-bind="text: item.group_name"></b>
		        </p>
		    </div>
		</div>
		<div class="col-sm-4">
			<div class="alert alert-warning" style="cursor: pointer;">
		    	<h4>@Mode@</h4>
		        <p class="text-right text-muted">
		        	<b data-bind="text: item.mode"></b>
		        </p>
		    </div>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-3" style="border-right: 1px solid lightblue">
			<h3>@List@ @data basket@</h3>
			<div class="files"
		     data-role="panelbar"
		     data-text-field="name"
		     data-spritecssclass-field="type"
		     data-bind="source: files, events: {select: fileSelect}"></div>
		</div>
		<div class="col-sm-9" data-bind="visible: dataName" style="height: 500px; overflow-y: scroll;">
			<h3>@Data@ - <b data-bind="text: dataName"></b> 
				<i class="fa fa-arrow-right text-success"></i>
				<button class="k-button" data-bind="click: import, visible: visibleAuto" style="font-size: 16px">@Import@</button>
				<button class="k-button" data-bind="click: importAndGoToAssign, invisible: visibleAuto" style="font-size: 16px">@Import@ @and@ @Assign@</button>
			</h3>
			<div id="data-grid"></div>
		</div>
	</div>
</div>

<script type="text/javascript">
	var diallist_id = "<?= $this->input->get('id') ?>";
	Config.crudApi = ENV.vApi + "database";

	$.get(ENV.vApi + "diallist/listDataBasket", function(res) {
		var observable = kendo.observable({
	        files: kendo.observableHierarchy(res),
	        fileSelect: function(e) {
	        	var dataItem = e.sender.dataItem($(e.item)),
	        		name = dataItem.name,
	        		type = dataItem.type;
	        	console.log(name, type);
	        	if(dataItem.type) {
	        		this.set("dataName", name);
	        		detailData(name);
	        	}
	        },
	        import: function(e) {
	        	$.get(ENV.vApi + "diallist_detail/insertFromBasket", {collection: this.get("dataName"), diallist_id: diallist_id} , function(res) {
	        		if(res.status) {
	        			router.navigate("/detail/" + diallist_id);
	        		}
	        	});
	        },
	        importAndGoToAssign: function(e) {
	        	$.get(ENV.vApi + "diallist_detail/insertFromBasket", {collection: this.get("dataName"), diallist_id: diallist_id} , function(res) {
	        		if(res.status) {
	        			router.navigate("/assign/" + diallist_id);
	        		}
	        	});
	        }
		});
		kendo.bind(".import-view", observable);

		$.get(ENV.restApi + "diallist/" + diallist_id, (diallist) => {
			observable.set("visibleAuto", Boolean(diallist.mode == "auto"));
			observable.set("item", diallist);
			layoutViewModel.set("breadcrumb2", diallist.name);
		})
	})

	function detailData(collection, columns = []) {
		// var database = "<?= substr($this->config->item("_mongo_db"), 1) ?>";
		var database = "LOAN_campaign_list";

        if(Table.grid) {
            Table.grid.destroy();
            Table.grid = false;
            $("#data-grid").empty();
        }
        var collectionFields = new kendo.data.DataSource({
            serverFiltering: true,
            serverSorting: true,
            serverPaging: true,
            pageSize: 10,
            transport: {
                read: `${Config.crudApi}/data/${database}/${collection}`,
                parameterMap: parameterMap
            },
            schema: {
                data: "data",
            }
        })
        collectionFields.read().then(() => {
            var data = collectionFields.data().toJSON();
            if(data[0]) {
                var listedProp = [], columns = [];
                data.forEach((doc, idx) => {
                    for(var prop in doc) {
                        if(listedProp.indexOf(prop) == -1) {
                            columns.push({field: prop, width: 140});
                            listedProp.push(prop);
                        }
                    }
                })
                Config.database = database;
                Config.collection = collection;
                Table.columns = Table.customColumns.concat(columns);
                Table.init();
            }
        })
    }

    var Table = function() {
	    return {
	        dataSource: {},
	        columns: Config.columns,
	        customColumns: [],
	        init: function() {
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
	                        url: Config.crudApi + "/data/" + Config.database + "/" + Config.collection
	                    },
	                    parameterMap: parameterMap
	                },
	                sync: syncDataSource,
	                error: errorDataSource
	            });

	            var grid = this.grid = $("#data-grid").kendoGrid({
	                dataSource: dataSource,
	                resizable: true,
	                pageable: {
	                    refresh: true,
	                    pageSizes: [10,20,50,100],
	                    input: true,
	                    messages: KENDO.pageableMessages ? KENDO.pageableMessages : {}
	                },
	                sortable: true,
	                reorderable: true,
	                scrollable: true,
	                columns: this.columns,
	                filterable: Config.filterable ? Config.filterable : true,
	                editable: false,
	                noRecords: {
	                    template: `<h2 class='text-danger'>${KENDO.noRecords}</h2>`
	                }
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
	        }
	    }
	}();
</script>
async function editColumns(ele) {
	$(ele).toggleClass("keepopen");
	var $columnWidget = $(ele).closest(".column-widget");
	if(!$columnWidget.hasClass("bound")) {

		var template = await $.get(`${ENV.templateApi}tools/column`);

		$columnWidget.append(template);
		
		var grid = $("#grid").data("kendoGrid"),
			options = grid.getOptions();
		options.columns.map(function(col) {
			if(col.title !== undefined && col.hidden !== true) {
				col.visible = true;
			}
		})
		var model = {
			columns: options.columns,
			change: function(e) {
				this.currentTarget = e.currentTarget;
				var field = $(e.currentTarget).val();
				if(e.currentTarget.checked)
					grid.showColumn(field);
				else grid.hideColumn(field);
				sessionStorage.setItem("columns_" + ENV.currentUri, JSON.stringify(grid.getOptions().columns));
			}
		}
		var div = $(ele).closest("div");
		kendo.bind(div, model);

		$columnWidget.addClass("bound");
	}
}

async function filterWidget(selector) {
	if(!$(selector).hasClass("bound")) {
		
		var html = await $.get(`${Config.templateApi}tools/filter`);
		$(selector).html(html);
		var model = kendo.observable({
			filterDataSource: new kendo.data.DataSource({
				serverFiltering: true,
				filter: { field: "table", operator: "eq", value: Config.collection },
				transport: {
					read: Config.crudApi + "filters",
					destroy: {
						url: function(dataItem) {
							return Config.crudApi + "filters/" + dataItem._id
						},
						type: "DELETE"
					}
				},
				schema: {
					data: "data",
					total: "total",
					model: {
						id: "_id"
					}
				}
			}),
			filterListChange: function(e) {
				var selectedItems = e.sender.select();
				$(selector).find(".list-group-item").removeClass("active");
				selectedItems.removeClass("k-state-selected").addClass("active");
				var dataItem = e.sender.dataItem(selectedItems),
					filter   = dataItem.filter.toJSON();
				Table.dataSource.filter(filter);
			},
			refreshFilter: function(e) {
				$(selector).find(".list-group-item").removeClass("active");
				Table.dataSource.filter({});
			},
			saveFilter: function(){
				this.asyncSaveFilter();
			},
			asyncSaveFilter: async function() {
				var filter = Table.dataSource.filter();
				if(filter && filter.filters.length) {
					var name = await swal({
					  text: 'Name of this filter',
					  content: "input",
					  button: {
					    text: "Save",
					    closeModal: true,
					  },
					})
					.then(name => {
					  	if(!name) {
					  		return swal("Need name for filter. Try save again!");
					  	} else {
							$.ajax({
								url : `${Config.crudApi}filters`,
								type: "POST",
								data: {
									name: name,
									table: Config.collection,
									filter: filter,
								},
								success: function(){
									if($("#filterList").data("kendoListView"))
										$("#filterList").data("kendoListView").dataSource.read();
									syncDataSource();
								},
								error: errorDataSource
							})
					  	}
					})
				} else {
					return swal("No filter to save!");
				}
			},
			removeFilter: function(e) {
				var filterDataSource = this.filterDataSource;
				swal({
				    title: "Are you sure?",
				    text: "Once deleted, you will not be able to recover this filter!",
				    icon: "warning",
				    buttons: true,
				    dangerMode: true,
			    })
			    .then((willDelete) => {
					if (willDelete) {
						var id = $(e.sender.wrapper).data("id"),
							dataItem = filterDataSource.get(id);
						filterDataSource.remove(dataItem);
						filterDataSource.sync();
					}
			    });
			}
		})
		kendo.bind($(selector), model);
		$(selector).addClass("bound");
	}
}

async function customFilter(ele, dataSource, dateRange = 1) {
	var field = $(ele).data("field"),
		mvvmSelector = ".filter-mvvm";
    if(!$(ele).hasClass("data-bound")) {
        var date =  new Date(),
            timeZoneOffset = date.getTimezoneOffset() * 60000;
            date.setHours(- timeZoneOffset / 3600000, 0, 0 ,0);
        var model = kendo.observable({
            fromDateTime: new Date(date.getTime() + timeZoneOffset - (dateRange - 1) * 86400000),
            toDateTime: new Date(date.getTime() + timeZoneOffset + 86400000 -1),
            startDateChange: function(e) {
				var start = e.sender,
					startDate = start.value(),
					end = $("#end-date").data("kendoDateTimePicker"),
                	endDate = end.value();

                if (startDate) {
                    startDate = new Date(startDate);
                    startDate.setDate(startDate.getDate());
                    end.min(startDate);
                } else if (endDate) {
                    start.max(new Date(endDate));
                } else {
                    endDate = new Date();
                    start.max(endDate);
                    end.min(endDate);
                }
            },
            endDateChange: function(e) {
            	var end = e.sender,
            		endDate = end.value(),
            		start = $("#start-date").data("kendoDateTimePicker"),
                	startDate = start.value();

                if (endDate) {
                    endDate = new Date(endDate);
                    endDate.setDate(endDate.getDate());
                    start.max(endDate);
                } else if (startDate) {
                    end.min(new Date(startDate));
                } else {
                    endDate = new Date();
                    start.max(endDate);
                    end.min(endDate);
                }
            },
            search: function() {
            	var filter = dataSource.filter();
                var fromDateTime = new Date(this.fromDateTime.getTime() - timeZoneOffset).toISOString();
                var toDateTime = new Date(this.toDateTime.getTime() - timeZoneOffset).toISOString();
                var filterTime = {
                    logic: "and",
                    filters: [
                        {field: field, operator: "gte", value: fromDateTime},
                        {field: field, operator: "lte", value: toDateTime}
                    ]
                };
                if(filter) {
                	var setFlag = false;
                	filter.filters.map((subFilters, index) => {
            			if(subFilters.filters) {
            				for (var i = 0; i < subFilters.filters.length; i++) {
            					if(subFilters.filters[i].field == field) {
            						subFilters.filters = [];
            						break;
            					}
            				}
            				if(!subFilters.filters.length) {
            					filter.filters[index] = filterTime;
            					setFlag = true;
            				}
            			} else {
            				if(subFilters[index] && subFilters[index].length)
            					subFilters[index] = subFilters[index].filter(doc => doc.field != field);
            			}
            		})
                	if(!setFlag) filter.filters.push(filterTime);
                } else {
                	filter = {
                		logic: "and",
                		filters: []
                	};
                	filter.filters.push(filterTime);
                }
                dataSource.filter(filter);
            }
        })
        var template = await $.get(`${Config.templateApi}tools/customfilter`);
        var kendoView = new kendo.View(template, {model: model, wrap: false, template: false});
        //kendo.bind($(mvvmSelector), model);
        kendoView.render($(mvvmSelector));
        $(ele).addClass("data-bound");
        $(mvvmSelector).fadeIn();
    } else {
        if($(mvvmSelector).is(":visible")) {
            $(mvvmSelector).fadeOut();
        } else {
            $(mvvmSelector).fadeIn();
        }
    }
}

function exportExcel(collection, dataSource = null, filename = "")
{
	var kendoQuery = {
        take: 999999999,
        skip: 0,
    }
    if(dataSource) {
    	kendoQuery.filter = dataSource.filter();
    	kendoQuery.sort = dataSource.sort();
    }
    $.ajax({
        url: `${ENV.reportApi}excecuteExcel/export`,
        type: "PATCH",
        contentType: "application/json; charset=utf-8",
        data: kendo.stringify({q: kendoQuery, collection: collection, filename: filename}),
        success: function(res) {
            if(res.status) {
                window.location = res.filepath;
                syncDataSource();
            } else notification.show(res.message, "error");
        },
        error: errorDataSource
    })
}
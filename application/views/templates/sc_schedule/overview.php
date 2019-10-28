<div class="row" style="margin: 10px 0">
	<div class="col-sm-2" id="page-widget"></div>
	<div class="col-sm-9 filter-mvvm" style="display: none"></div>
</div>
<div class="row">
	<div class="col-sm-12" style="height: 80vh;">
	    <!-- Table Styles Content -->
	    <div id="grid_1" style="width: 100%; overflow-x: scroll;"></div>
	    <!-- END Table Styles Content -->
	</div>
</div>
<div id="action-menu">
    <ul>
        <a href="javascript:void(0)" data-type="update" onclick="openForm({title: '@Edit@', width: 500}); editForm(this)"><li><i class="fa fa-pencil-square-o text-warning"></i><span>@Edit@</span></li></a>
    	<li class="devide"></li>
        <a href="javascript:void(0)" data-type="delete" onclick="deleteDataItem(this)"><li><i class="fa fa-times-circle text-danger"></i><span>@Delete@</span></li></a>
    </ul>
</div>

<script type="text/x-kendo-template" id="status-group-template">
    <li data-bind="css: {active: active}">
        <a href="javascript:void(0)" data-bind="click: filterStatus, attr: {data-value: idFields}">
            <span class="badge pull-right" data-bind="text: count">250</span>
            <i class="#: data.iconClass #"></i> <strong data-bind="text: idFields">Closed</strong>
        </a>
    </li>
</script>

<script>
var currentDate = new Date();
var currentMonth = currentDate.getMonth();
currentDate.setHours(0, 0, 0, 0);
defaultStartDate = new Date();
defaultStartDate.setDate(21);
if(currentMonth == 1) {
    defaultStartDate.setMonth(12);
}
else {
    defaultStartDate.setMonth(currentMonth - 1);
}
defaultEndDate = new Date();
defaultEndDate.setDate(20);
Date.prototype.addDays = function(days) {
    var dat = new Date(this.valueOf());
    dat.setDate(dat.getDate() + days);
    return dat;
};

function createColumn(startDate, endDate) {
    var columns = [{
        field: 'dealer_code',
        title: "@Dealer code@",
        width: 150,
        filterable: false,
    }];
    while(startDate <= endDate) {
        startDate.setHours(0, 0, 0, 0);
        var currentColumn = 'sc' + (startDate.getTime() / 1000);
        var title = startDate.getDate();
        // console.log('out: ' + currentColumn)
        columns.push({
            field: currentColumn,
            title: title.toString(),
            // template: (dataItem) => {
            //     console.log('in: ' + currentColumn);
            //     if(typeof dataItem[currentColumn] !== 'undefined' && dataItem[currentColumn] !== null && dataItem[currentColumn] !== '') {
            //         return gridArray(dataItem[currentColumn]);
            //     }
            //     else {
            //         console.log("TEST");
            //         return '<span>TEST</span>';
            //     }
            // },
            width: 100,
            filterable: false,
        });
        startDate = startDate.addDays(1);
    }
    return columns
}

var Config = Object.assign(Config, {
    model: {},
    parse(response) {
        response.data.map(function(doc) {
            // doc.from_date = new Date(doc.from_date * 1000);
            // console.log(doc);
            return doc;
        });
        return response;
    },
    sort: [{
        created_at: -1,
        dealer_code: 1
    }],
    scrollable: true,
    columns: createColumn(defaultStartDate, defaultEndDate)
});
</script>

<script src="<?= STEL_PATH.'js/tablev3.js' ?>"></script>

<script type="text/javascript">
	function deleteDataItemChecked() {
		var checkIds = Table.grid.selectedKeyNames();
		if(checkIds.length) {
			swal({
			    title: "@Are you sure@?",
			    text: "@Once deleted, you will not be able to recover these documents@!",
			    icon: "warning",
			    buttons: true,
			    dangerMode: true,
		    })
		    .then((willDelete) => {
				if (willDelete) {
					checkIds.forEach(uid => {
						var dataItem = Table.dataSource.getByUid(uid);
					    Table.dataSource.remove(dataItem);
					    Table.dataSource.sync();
					})
				}
		    });
		} else {
			swal({
				title: "@No row is checked@!",
			    text: "@Please check least one row to remove@",
			    icon: "error"
			});
		}
	}

	function importData(ele) {
		var uid = $(ele).data('uid');
		var dataItem = Table.dataSource.getByUid(uid);
		router.navigate(`/import/${dataItem.id}`);
	}

	function detailData(ele) {
		var uid = $(ele).data('uid');
		var dataItem = Table.dataSource.getByUid(uid);
		router.navigate(`/detail/${dataItem.id}`);
	}

	$(document).on("click", ".grid-name", function() {
		detailData($(this).closest("tr"));
	});

    async function customFilter_local(ele, dataSource, dateRange = 1) {
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
                    var fromDateTime = new Date(this.fromDateTime.getTime()).toISOString();
                    var toDateTime = new Date(this.toDateTime.getTime()).toISOString();
                    var column = createColumn(new Date(this.fromDateTime.getTime()), new Date(this.toDateTime.getTime()));
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
                    var grid = $("#grid_1").data('kendoGrid');
                    var options = grid.options;
                    options.columns = column;
                    console.log(options.dataSource);
                    grid.destroy();
                    $('#grid_1').empty().kendoGrid(options);
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

    Table.init();
</script>
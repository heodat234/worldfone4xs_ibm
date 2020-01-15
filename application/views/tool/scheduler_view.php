<script>
	var Config = {
		crudApi: ENV.restApi,
		collection: "scheduler",
		defaultShift: "HC",
	};
</script>
<!-- Search Results Header -->
<ul class="breadcrumb breadcrumb-top">
    <li>@Tool@</li>
    <li>@Scheduler@</li>
</ul>
<!-- END Search Results Header -->
<div class="container-fluid after-breadcrumb">
    <div class="row">
		<div class="col-sm-3" id="left-col">
			<div class="form-group">
				<div id="calendar"></div>
			</div>
			<div class="form-group">
				<label>Tags</label>
			    <select id="tags" style="width: 100%"></select>
			</div>
			<div class="form-group">
				<button class="k-button" id="exportPDF">@Export to PDF@</button>
			</div>
		</div>
		<div class="col-sm-9" id="right-col">
			<div class="row">
				<div id="scheduler"></div>
			</div>
		</div>
    </div>
</div>

<script>
$(function() {
    $("#exportPDF").click(function() {
    	notification.show("@Wait a minute@");
        $("#scheduler").data("kendoScheduler").saveAsPDF();
    });

    $("#tags").kendoMultiSelect({
    	dataTextField: "text",
    	dataValueField: "value",
    	tagTemplate: '<span class="label" style="background-color: #: color #">#: text #</span>',
        autoBind: false,
        valuePrimitive: true,
    	dataSource: dataSourceJsonData(["Scheduler", "tags"]),
    	change: function(e) {
    		var currentFilter = $("#scheduler").data("kendoScheduler").dataSource.filter() || {filters: []};
    		currentFilter.filters = currentFilter.filters.filter(fil => fil.field != "shift");
    		var filter = {
    			logic: "and",
    			filters: [
    				{field: "tags", operator: "in", value: e.sender.value()}
    			]
    		};
    		filter.filters.push(currentFilter);
    		$("#scheduler").data("kendoScheduler").dataSource.filter(filter);
    	}
    });

	$calendar = $("#calendar");
	$calendar.kendoCalendar({
		change: function(e) {
			$("#scheduler").data("kendoScheduler").date(e.sender.value());
		}
	}); 
	initReport();
});

function initReport(filter = null) {
	var $scheduler = $("#scheduler");
	if($scheduler.data("kendoScheduler")) {
		$scheduler.data("kendoScheduler").destroy();
		$scheduler.empty();
	}

	var date = new Date();
	KENDO.schedulerMessages.today = "@Today@ " + kendo.toString(date, "dd/MM/yy");
    $("#scheduler").kendoScheduler({
    	messages: KENDO.schedulerMessages,
        date: date,
        eventTemplate: $("#event-all-day-template").html(),
        startTime: new Date(date.setHours(0, 0, 0)),
        height: 800,
        views: [
            { type: "month", title: "@Month@", selected: true},
            { type: "week", title: "@Week@", allDayEventTemplate: $("#event-all-day-template").html()},
            { type: "day", title: "@Day@", allDayEventTemplate: $("#event-all-day-template").html(), group: {
            	resources: ["Shift"]}
        	},
        	{ type: "agenda", title: "@Agent@", group: {resources: ["Extension"]}},
        ],
        save: function(ev) {
        	ev.preventDefault();
        	var data = ev.event.toJSON();
        	delete data.uid;
        	$.ajax({
    			url: Config.crudApi + Config.collection + "/" + (ev.event.taskId || "").toString(),
    			type: ev.event.taskId ? "PUT" : "POST",
    			contentType: "application/json; charset=utf-8",
    			data: JSON.stringify(data),
    			success: (res) => {
    				if(res.status) {
    					ev.sender.dataSource.read();
    					syncDataSource();
    					if(ev.container) ev.container.data("kendoWindow").close();
    				} else notification.show(res.message, "error");
    			}
    		})
        },
        remove: function(e) {
		    e.preventDefault();
		    $.ajax({
		    	url: Config.crudApi + Config.collection + "/" + (e.event.taskId || "").toString(),
		    	type: "DELETE",
    			success: (res) => {
    				if(res.status) {
    					syncDataSource();
    					e.sender.dataSource.read();
    				} else notification.show(res.message, "error");
    			}
		    })
		},
		cancel: function(e) {
			e.preventDefault();
			e.container.data("kendoWindow").close();
		},
        timezone: "Asia/Ho_Chi_Minh",
        dataSource: {
        	filter: filter,
        	serverPaging: true,
        	serverFiltering: true,
        	serverSorting: true,
        	pageSize: 1000,
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
            schema: {
            	data: "data",
            	total: "total",
                model: {
                    id: "id",
                    fields: {
                        taskId: { from: "id" },
                        title: { defaultValue: "@Live@" },
                        start: { type: "date" },
                        end: { type: "date" },             
                        description: { },
                        ownerId: { defaultValue: ENV.extension , validation: { required: true }},
                        isAllDay: { type: "boolean" },
                        shift: { defaultValue: Config.defaultShift ,validation: { required: true }},
                    }
                }
            }
        },
        resources: [
            {
				field: "tags",
				title: "Tags",
				name: "tags",
				multiple: true,
				dataSource: dataSourceJsonData(["Scheduler", "tags"])
		    }
        ]
    });
}
</script>

<script id="event-all-day-template" type="text/x-kendo-template">
  <div># for (var i = 0; i < resources.length; i++) { #
        #if(resources[i].field == "tags"){#
        	#: resources[i].value #
        #}else{#
        	<span class="label label-default">#: resources[i].value #</span>
        #}#
      # } #
  </div>
</script>

<style type="text/css">
	#calendar,
    #calendar .k-calendar-view,
    #calendar .k-content {
        width: 100%;
    }
</style>
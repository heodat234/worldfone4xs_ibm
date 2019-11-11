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
	<div class="row form-horizontal" style="padding-top: 10px">
        <div class="col-md-4 form-group">
            <label class="control-label col-sm-3">@Group@</label>
            <div class="col-sm-9">
            	<input id="group" style="width: 100%"/>
        	</div>
        </div>
        <div class="col-md-8 form-group">
            <label class="control-label col-sm-2">@Extension@</label>
            <div class="col-sm-10">
            	<select id="select" style="width: 100%"></select>
            </div>
        </div>
        <div class="col-md-4 form-group">
        	<label class="control-label col-sm-3">@Shift@</label>
            <div class="col-sm-9">
            	<select id="shift" style="width: 100%"></select>
        	</div>
        </div>
        <div class="col-md-8 form-group">
        	<div class="col-sm-offset-3">
        		<button class="k-button" id="selectAll">@Select All@</button>
        		<button class="k-button" id="deselectAll">@Deselect All@</button>
        		<button class="k-button" id="exportPDF">@Export to PDF@</button>
        	</div>
        </div>
    </div>
    <div class="row">
    	<div id="scheduler"></div>
    </div>
</div>

<script>
$(function() {
	$("#selectAll").click(function() {
		var required = $("#select").data("kendoMultiSelect");
        var values = $.map(required.dataSource.data(), function(dataItem) {
          return dataItem.extension;
        });

    	required.value(values);
    	required.trigger("change");
  	});

	$("#deselectAll").click(function() {
		var required = $("#select").data("kendoMultiSelect");
        required.value([]);
        required.trigger("change");
    });

    $("#exportPDF").click(function() {
    	notification.show("@Wait a minute@");
        $("#scheduler").data("kendoScheduler").saveAsPDF();
    });

    $("#shift").kendoMultiSelect({
    	dataTextField: "text",
    	dataValueField: "value",
    	tagTemplate: '<span class="label" style="background-color: #: color #">#: text #</span>',
        autoBind: false,
        valuePrimitive: true,
    	dataSource: dataSourceJsonData(["Scheduler", "shift"]),
    	change: function(e) {
    		var currentFilter = $("#scheduler").data("kendoScheduler").dataSource.filter();
    		currentFilter.filters = currentFilter.filters.filter(fil => fil.field != "shift");
    		var filter = {
    			logic: "and",
    			filters: [
    				{field: "shift", operator: "in", value: e.sender.value()}
    			]
    		};
    		filter.filters.push(currentFilter);
    		$("#scheduler").data("kendoScheduler").dataSource.filter(filter);
    	}
    });

    function resetShiftFilter() {
    	$("#shift").data("kendoMultiSelect").value([]);
    }

    var dataSourceUser = new kendo.data.DataSource({
        serverPaging: true,
        serverSorting: true,
        pageSize : 100,
        transport: {
            read: {
                url: ENV.vApi + `select/foreign_private/User`,
                data: {field: ["extension", "agentname"], match: null}
            },
            parameterMap: parameterMap
        },
        schema: {
            data: "data",
            parse: res => {
		    	res.data.map(doc => {
		        	doc.ownerId = doc.value = doc.extension;
		        	doc.text = doc.extension + " - " + doc.agentname;
		        	doc.color = getRandomColor();
		    	})
		    	return res;
		    }
        },
        error: errorDataSource
    });

    dataSourceUser.fetch().then(() => {
    	var userArr = [];
    	dataSourceUser.data().forEach(user => {
    		userArr.push(user.extension);
    	});

		$("#group").kendoDropDownList({
			value: "",
	        dataTextField: "name",
            dataValueField: "id",
            valuePrimitive: true,
            dataSource: dataSourceDropDownList("Group", ["name", "members"], {active: true}, res => {
            	res.data.unshift({name: "@ALL@", members: userArr, id: ""});
            	return res;
            }),
	        cascade: function(e) {
	        	if(e.sender.dataItem().members) {
	        		var members = e.sender.dataItem().members.toJSON();
	        		var filter = {
	        			logic: "or",
	        			filters: []
	        		};

	        		//filter.filters.push({ field: "ownerId", operator: "contains", value: members });

	            	members.forEach(ext => {
	            		filter.filters.push({ field: "ownerId", operator: "eq", value: ext });
	            	})

	            	resetShiftFilter();

	        		dataSourceUser.filter(filter);

	        		var $select = $("#select");
	        		if($select.data("kendoMultiSelect")) 
	        		{
	        			$select.data("kendoMultiSelect").setDataSource(dataSourceUser);
	        			$select.data("kendoMultiSelect").value(members);
	        		} else {
		        		$select.kendoMultiSelect({
		        			value: members,
				            dataTextField: "text",
				            dataValueField: "extension",
				            tagTemplate: '<span class="label" style="background-color: #: color #">#: extension #</span>',
				            autoBind: false,
				            valuePrimitive: true,
				            dataSource: dataSourceUser,
				            change: function(e) {
				            	var extensions = e.sender.value(),
				            		filter = {logic: "or", filters: []};

				            	extensions.forEach(ext => {
				            		filter.filters.push({ field: "ownerId", operator: "eq", value: ext });
				            	})
								var newDataSource = new kendo.data.DataSource({
				            		data: dataSourceUser.data().toJSON()
				            	});
								newDataSource.filter(filter);
								initReport(newDataSource, filter);
								resetShiftFilter();
				            }
				        });
		        	}

	        		initReport(dataSourceUser, filter);
			
	        	}
	        }
	    });    
	}) 
});

function initReport(dataSourceUser, filter) {
	var $scheduler = $("#scheduler");
	if($scheduler.data("kendoScheduler")) {
		$scheduler.data("kendoScheduler").destroy();
		$scheduler.empty();
	}
	if(filter.filters.length) {
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
	                        ownerId: { defaultValue: [ENV.extension] , validation: { required: true }},
	                        isAllDay: { type: "boolean" },
	                        shift: { defaultValue: Config.defaultShift ,validation: { required: true }},
	                    }
	                }
	            }
	        },
	        resources: [
	            {
					field: "shift",
					title: "@Shift@",
					name: "Shift",
					dataSource: dataSourceJsonData(["Scheduler", "shift"], res => {
						res.data.map(doc => doc.text = doc.value + " - " + doc.text);
						return res;
					})
			    },
	            {
	                field: "ownerId",
	                title: "@Extension@",
	                name: "Extension",
	                multiple: true,
	                dataSource: dataSourceUser
	            }
	        ]
	    });
	}
}
</script>

<script id="event-all-day-template" type="text/x-kendo-template">
  <div># for (var i = 0; i < resources.length; i++) { #
        #if(resources[i].field == "shift"){#
        	#: resources[i].value #
        #}else{#
        	<span class="label label-default">#: resources[i].value #</span>
        #}#
      # } #
  </div>
</script>
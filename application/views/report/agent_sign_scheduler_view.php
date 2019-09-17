<div id="page-content">
	<ul class="breadcrumb breadcrumb-top">
        <li>@Report@</li>
        <li>@Scheduler sign@</li>
    </ul>
	<div class="container-fluid">
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
              return dataItem.value;
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
	        $("#scheduler").data("kendoScheduler").saveAsPDF();
	    });


		var dataSourceUser = dataSourceDropDownListPrivate("User", ["extension"], null, res => {
        	res.data.map(doc => {
        		doc.text = doc.extension;
            	doc.value = doc.extension;
            	doc.color = getRandomColor();
        	})
        	return res;
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
		        		};

		        		filter.filters = [];

		            	members.forEach(ext => {
		            		filter.filters.push({ field: "extension", operator: "eq", value: ext });
		            	})

		        		dataSourceUser.filter(filter);

		        		var $select = $("#select");
		        		if($select.data("kendoMultiSelect")) 
		        		{
		        			$select.data("kendoMultiSelect").setDataSource(dataSourceUser);
		        			$select.data("kendoMultiSelect").value(members);
		        		} else {
			        		$select.kendoMultiSelect({
			        			value: members,
					            dataTextField: "extension",
					            dataValueField: "extension",
					            tagTemplate: '<span class="label" style="background-color: #: color #">#: extension #</span>',
					            autoBind: false,
					            valuePrimitive: true,
					            dataSource: dataSourceUser,
					            change: function(e) {
					            	var extensions = e.sender.value(),
					            		filter = {logic: "or"};
					            	

					            	filter.filters = [];

					            	extensions.forEach(ext => {
					            		filter.filters.push({ field: "extension", operator: "eq", value: ext });
					            	})
									var newDataSource = new kendo.data.DataSource({
					            		data: dataSourceUser.data().toJSON()
					            	});
									newDataSource.filter(filter);
									initReport(newDataSource, filter);
					            }
					        });
			        	}

		        		initReport(dataSourceUser, filter);
				
		        	}
		        }
		    });    
		})       
	});

function initReport(dataSourceUser, filter){
	var $scheduler = $("#scheduler");
	if($scheduler.data("kendoScheduler")) {
		$scheduler.data("kendoScheduler").destroy();
		$scheduler.empty();
	}
	if(filter.filters.length) {
		var date = new Date();
		var startTime = new Date(date.setHours(0, 0, 0));
		var endTime = new Date(date.setHours(24, 0, 0));
	    $("#scheduler").kendoScheduler({
	        date: new Date(),
	        startTime: startTime,
	        endTime: endTime,
	        height: 600,
	        views: [
	            { type: "day", selected: true},
	            { type: "workWeek" },
	            "week",
	            "agenda",
	            { type: "timeline", eventHeight: 50}
	        ],
	        editable: false,
	        group: {
	            date: true,
	            resources: ["Extension"]
	        },
	        dataSource: {
	        	filter: filter,
	        	serverPaging: true,
	        	serverFiltering: true,
	        	serverSorting: true,
	        	pageSize: 1000,
	        	transport: {
	        		read: ENV.restApi + "agentsign",
	        		parameterMap: parameterMap
	        	},
	        	schema: {
	        		data: "data",
	        		total: "total",
	        		parse: function(response) {
	        			response.data.map(doc => {
	        				doc.title = doc.extension + " - " + doc.user;
	        				doc.signintime = new Date(doc.signintime * 1000);
	        				doc.signouttime = new Date(doc.signouttime * 1000);
	        				doc.isAllDay = true;
	        			})
	        			return response;
	        		},
	        		timezone: "Asia/Ho_Chi_Minh",
	        		model: {
	        			id: "my_session_id",
	        			fields: {
	                        taskId: { from: "my_session_id"},
	                        title: { from: "title", defaultValue: "No title", validation: { required: true } },
	                        start: { type: "date", from: "signintime" },
	                        end: { type: "date", from: "signouttime" },
	                        ownerId: { from: "extension"},
	                        isAllDay: { type: "boolean", from: "IsAllDay", defaultValue: true}
	                    }
	        		}
	        	}
	        },
	        resources: [
	            {
	                field: "ownerId",
	                title: "Extension",
	                name: "Extension",
	                dataSource: dataSourceUser
	            }
	        ]
	    });
	}
} 
	</script>
</div>
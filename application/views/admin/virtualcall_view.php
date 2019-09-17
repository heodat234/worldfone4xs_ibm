<script>
var Config = {
    crudApi: `${ENV.restApi}`,
    templateApi: `${ENV.templateApi}`,
    collection: "virtual_call",
    model: {
        id: "id"
    }
}; 

window.onload = function() {
    var pageObservable = window.pageObservable = kendo.observable({
        activityViewDataSource: new kendo.data.DataSource({
            serverSorting: true,
            serverPaging: true,
            serverFiltering: true,
            pageSize: 10,
            transport: {
                read: ENV.vApi + Config.collection + "/read",
                update: {
                	url: function(data) {
                        return ENV.vApi + Config.collection + "/update/" + data.id;
                    },
                    type: "PUT",
                    contentType: "application/json; charset=utf-8"
                },
                parameterMap: parameterMap
            },
            schema: {
                data: "data",
                total: "total",
                model: {
                    id: "id"
                }
            }
        })
    });
    kendo.bind($(".mvvm"), pageObservable);
    $("#grid").data("kendoGrid").bind("detailInit",  function(e) {
        kendo.bind($(e.detailCell), e.data);
    });
}

function clickRun(e) {
    // e.target is the DOM element representing the button
    var tr = $(e.target).closest("tr");
    // get the data bound to the current table row
    var data = this.dataItem(tr).toJSON();
    swal({
        title: `Virtual this call`,
        text: `Are you sure?`,
        icon: "warning",
        buttons: true,
        dangerMode: false,
    })
    .then(ok => {
        if (!ok) return;
        var time = (new Date()).getTime() / 1000;
        var timeRange = time - data.createdAt; 
        sendEvent(data.events, timeRange);
    })
}

async function sendEvent(events, timeRange) {
	if(events.length) {
		var event = events[0];

		var time = (new Date()).getTime() / 1000;
    	if(event.createdAt + timeRange < time) {
    		if(["Start","Dialing"].indexOf(event.callstatus) > -1) {
				await $.get(ENV.vApi + "virtual_call/remove_cdr/" + event.calluuid);
			}

    		var data = {};
    		for(var prop in event) {
    			if(["receivedtime","starttime","createdAt","answertime","endtime","datereceived"].indexOf(prop) > -1) {
    				data[prop] = kendo.toString(new Date((event[prop] + timeRange) * 1000), "yyyyMMddTHHmmss")
    			} else data[prop] = event[prop];
    		}
    		
    		$.ajax({
    			url: "/wfpbx/pbxevents",
    			data: data,
    			success: (res) => {
    				events.shift();
    				console.log("Send event: ", event);
    				setTimeout(() => sendEvent(events, timeRange), 100);
    			}
    		})
    	} else setTimeout(() => sendEvent(events, timeRange), 100);
    }
}
</script>

<!-- Table Styles Header -->
<ul class="breadcrumb breadcrumb-top">
    <li>Admin</li>
    <li>Virtual call</li>
</ul>
<!-- END Table Styles Header -->
<div class="container-fluid mvvm">
    <div class="row">
    	<div class="col-sm-12" style="padding: 0">
            <!-- Table Styles Content -->
            <div data-role="grid" id="grid"
            data-editable="inline"
            data-sortable="true"
            data-pageable="{refresh: true, input: true, pageSizes: [10,20,50,100]}"
            data-filterable="{extra: true}"
            data-detail-template="events-template"
            data-columns="[
            	{field: 'name', title: 'Name', width: 280},
            	{field: 'description', title: 'Description'},
                {
                    title: 'Action',
                    command: [{name: 'run', text: 'Run', click: clickRun}, 'edit'],
                    width: 180
                }
            ]"
            data-bind="source: activityViewDataSource"></div>
            <!-- END Table Styles Content -->
        </div>
    </div>
</div>

<script type="text/x-kendo-template" id="events-template">
    <div data-role="grid" 
    	data-editable="incell"
        data-sortable="true"
        data-pageable="{input: true, pageSize: 5, pageSizes: [5,10,20]}"
        data-filterable="{extra: true}"
        data-bind="source: events"
        >
    </div>
</script>
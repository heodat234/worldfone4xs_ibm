<div class="container-fluid after-breadcrumb">
	<div class="row" style="margin: 0 10px">
		<h4 class="fieldset-legend" style="margin: 0 0 20px">
			<span style="font-weight: 500; line-height: 1; text-transform: uppercase;">
				<i class="gi gi-dashboard" style="vertical-align: -2px"></i> @Performance@
			</span>
		</h4>
	</div>
	<div class="row performance">
		<div class="col-sm-12">
			<div class="panel panel-primary">
                <div class="panel-heading">@APPOINTMENT HISTORY@</div>
                <div class="panel-body" style="padding: 0">
                    <div data-role="grid"
                        data-pageable="{refresh: true}"
                        data-no-records="{
                            template: `<h2 class='text-danger'>@NO DATA@</h2>`
                        }"
                        data-columns='[
                          {
                              field: "name",
                              title: "@Telesale name@",
                              width: 150,
                          },{
                              field: "code",
                              title: "@Telesale code@",
                              width: 150,

                          },{
                              field: "team",
                              title: "@Team@",
                              width: 120,
                          },
                          {
                              field: "count_appointment",
                              title: "@Appointment@",
                              width: 150,
                          },
                          {
                              field: "count_applied",
                              title: "@Customer Applied@",
                              width: 150,
                          },
                          {
                              field: "count_approve",
                              title: "@Customer Approve@",
                              width: 150,
                          },
                          {
                              field: "count_reject",
                              title: "@Customer Reject@",
                              width: 150,
                          },
                          {
                              field: "count_release",
                              title: "@Customer Release@",
                              width: 150,
                          },

                     ]'
                      data-bind="source: appointment_log"></div>
                </div>
            </div>
		</div>
		<div class="col-sm-12">
			<div class="panel panel-default">
	            <div class="panel-heading">@CALL HISTORY@</div>
	            <div class="panel-body" style="padding: 0">
	                <div data-role="grid" id="call-history-grid"
	                data-pageable="{refresh: true}"
	                data-scrollable="false"
	                data-no-records="{
	                        template: `<h2 class='text-danger'>@NO DATA@</h2>`
	                    }"
	                data-columns="[
                          {
                              field: 'name',
                              title: '@Telesale name@',
                              width: 150,
                          },{
                              field: 'code',
                              title: '@Telesale code@',
                              width: 150,

                          },{
                              field: 'team',
                              title: '@Team@',
                              width: 120,
                          },
                          {
                              field: 'count_called',
                              title: '@Called@',
                              width: 150,
                          },
                          {
                              field: 'count_success',
                              title: '@Success Call@',
                              width: 150,
                          },
                          {
                              field: 'count_dont_pickup',
                              title: '@Dont pick up phone@',
                              width: 150,
                          },
                          {
                              field: 'count_appointment',
                              title: '@Appointment@',
                              width: 150,
                          },
                          {
                              field: 'count_potential',
                              title: '@Potential@',
                              width: 150,
                          },

                     ]"
	              data-bind="source: call_out_log"></div>
	            </div>
	        </div>
    	</div>
    	<div class="col-sm-12">
			<div class="panel panel-warning">
	            <div class="panel-heading">DATA SOURCE</div>
	            <div class="panel-body" style="padding: 0">
	                <div data-role="grid" id="call-history-grid"
	                data-pageable="{refresh: true}"
	                data-scrollable="false"
	                data-no-records="{
	                        template: `<h2 class='text-danger'>@NO DATA@</h2>`
	                    }"
	                data-columns='[
                          {
                              field: "code",
                              title: "@Source@",
                              width: 120,
                          },{
                              field: "count_data",
                              title: "@No. of data@",
                              width: 150,

                          },{
                              field: "count_called",
                              title: "@No. of called@",
                              width: 150,
                          },
                          {
                              field: "count_success",
                              title: "@Success Call@",
                              width: 150,
                          },
                          {
                              field: "count_dont_pickup",
                              title: "@Dont pick up phone@",
                              width: 150,
                          },
                          {
                              field: "count_appointment",
                              title: "@Appointment@",
                              width: 150,
                          },
                          {
                              field: "count_potential",
                              title: "@Potential@",
                              width: 150,
                          },

                     ]'
	              data-bind="source: data_log"></div>
	            </div>
	        </div>
    	</div>
    	<div class="col-sm-12">
			<div class="panel panel-danger">
	            <div class="panel-heading">SC DELIVER</div>
	            <div class="panel-body" style="padding: 0">
	                <div data-role="grid" id="call-history-grid"
	                data-pageable="{refresh: true}"
	                data-scrollable="false"
	                data-no-records="{
	                        template: `<h2 class='text-danger'>@NO DATA@</h2>`
	                    }"
	                data-columns='[
                          {
                              field: "code",
                              title: "@Source@",
                              width: 120,
                          },{
                              field: "count_data",
                              title: "@No. of data@",
                              width: 150,

                          },
                          {
                              field: "count_appointment",
                              title: "@Appointment@",
                              width: 150,
                          },
                          {
                              field: "count_sc",
                              title: "@Deliver to SC@",
                              width: 150,
                          },

                     ]'
	              data-bind="source: sc_log"></div>
	            </div>
	        </div>
    	</div>
	</div>
</div>
<script>
    window.onload = function() {

        var nowDate = new Date();
        var date =  new Date(),
               timeZoneOffset = date.getTimezoneOffset() * kendo.date.MS_PER_MINUTE;
               date.setHours(- timeZoneOffset / kendo.date.MS_PER_HOUR, 0, 0 ,0);

        var fromDate = new Date(date.getTime() + timeZoneOffset );
        var toDate = new Date(date.getTime() + timeZoneOffset + kendo.date.MS_PER_DAY -1);
        var observable = kendo.observable({
            appointment_log: new kendo.data.DataSource({
                serverFiltering: true,
                serverPaging: true,
                serverSorting: true,
                filter: {
                    logic: "and",
                    filters: [
                        {field: 'created_at', operator: "gte", value: fromDate},
                        {field: 'created_at', operator: "lte", value: toDate}
                    ]
                },
                pageSize: 5,
                transport: {
                    read: ENV.reportApi + "telesale/appointment",
                    parameterMap: parameterMap
                },
                schema: {
                    data: "data",
                    total: "total",
                    parse: function(response) {
                       response.data.map(doc => {
                          doc.code = doc._id.code;
                       });
                       return response;
                    }
                },
                error: errorDataSource
            }),
            call_out_log: new kendo.data.DataSource({
                serverFiltering: true,
                serverPaging: true,
                serverSorting: true,
                filter: {
                    logic: "and",
                    filters: [
                        {field: 'starttime', operator: "gte", value: fromDate},
                        {field: 'starttime', operator: "lte", value: toDate}
                    ]
                },
                pageSize: 5,
                transport: {
                    read: ENV.reportApi + "telesale/call_out",
                    parameterMap: parameterMap
                },
                schema: {
                    data: "data",
                    total: "total",
                    parse: function(response) {
                       response.data.map(doc => {
                          doc.code = doc._id.code;
                       });
                       return response;
                    }
                },
            }),
            data_log: new kendo.data.DataSource({
                serverFiltering: true,
                serverPaging: true,
                serverSorting: true,
                filter: {
                    logic: "and",
                    filters: [
                        {field: 'createdAt', operator: "gte", value: fromDate},
                        {field: 'createdAt', operator: "lte", value: toDate}
                    ]
                },
                pageSize: 5,
                transport: {
                    read: ENV.reportApi + "telesale/data_library",
                    parameterMap: parameterMap
                },
                schema: {
                    data: "data",
                    total: "total",
                    parse: function(response) {
                       response.data.map(doc => {
                          doc.code = doc._id.code;
                       });
                       return response;
                    }
                },
            }),
            sc_log: new kendo.data.DataSource({
                serverFiltering: true,
                serverPaging: true,
                serverSorting: true,
                filter: {
                    logic: "and",
                    filters: [
                        {field: 'createdAt', operator: "gte", value: fromDate},
                        {field: 'createdAt', operator: "lte", value: toDate}
                    ]
                },
                pageSize: 5,
                transport: {
                    read: ENV.reportApi + "telesale/sc_deliver",
                    parameterMap: parameterMap
                },
                schema: {
                    data: "data",
                    total: "total",
                    parse: function(response) {
                       response.data.map(doc => {
                          doc.code = doc._id.code;
                       });
                       return response;
                    }
                },
            }),
        });
        kendo.bind($(".performance"), observable);
    }
</script>
<div id="page-content">
	<!-- Table Styles Header -->
    <ul class="breadcrumb breadcrumb-top">
        <li>@Report@</li>
        <li>@Ticket@</li>
        <li class="pull-right none-breakcrumb">
            <a role="button" class="btn btn-sm" onclick="getPDF('.chart-page', 'Chart')"><i class="fa fa-file-pdf-o"></i> <b>@Export@ PDF</b></a>
        </li>
    </ul>
    <!-- END Table Styles Header -->
	<div class="container-fluid mvvm" style="padding-top: 20px; padding-bottom: 10px">
		<div class="row form-horizontal">
			<div class="form-group col-sm-4">
				<label class="control-label col-xs-4">@From date@</label>
				<div class="col-xs-8">
					<input id="start-date" data-role="datepicker" data-format="dd/MM/yyyy H:mm:ss" name="fromDateTime" data-bind="value: fromDateTime, events: {change: startDate}">
				</div>
			</div>
			<div class="form-group col-sm-4">
				<label class="control-label col-xs-4">@To date@</label>
				<div class="col-xs-8">
					<input id="end-date" data-role="datepicker" data-format="dd/MM/yyyy H:mm:ss" name="toDateTime" data-bind="value: toDateTime, events: {change: endDate}">
				</div>
			</div>
			<div class="form-group col-sm-4">
				<label class="control-label col-xs-4">@Group by@</label>
				<div class="col-xs-8">
					<input data-role="dropdownlist" name="groupBy" 
					data-text-field="text"
					data-value-field="value"
					data-value-primitive="true" 
					data-bind="value: groupBy, source: groupByOption, events: {change: groupByChange}">
				</div>
			</div>
			<div class="form-group col-sm-4">
				<label class="control-label col-xs-4">@Filter by@</label>
				<div class="col-xs-8">
					<input data-role="dropdownlist" name="filterField" 
					data-text-field="text"
					data-value-field="value"
					data-value-primitive="true" 
					data-bind="value: filterField, source: fieldOption"/>
				</div>
			</div>
			<div class="form-group col-sm-4">
				<label class="control-label col-xs-4">@Value@</label>
				<div class="col-xs-8">
					<input class="k-textbox" data-bind="value: filterValue"/>
				</div>
			</div>
			<div class="form-group col-sm-12 text-center">
				<button class="k-button" data-bind="click: search, disabled: loading">@Search@</button>
			</div>
		</div>
		<div class="row chart-page" data-bind="visible: visibleReport" style="background-color: white">
			<div class="col-sm-12">
				<h4 class="text-center text-warning">Biểu đồ tỷ lệ cuộc gọi theo <span data-bind="text: groupText"></span> từ ngày <span data-bind="text: fromDate"></span> đến <span data-bind="text: toDate"></span>
				<span data-bind="visible: filterValue"> lọc <i data-bind="text: filterValue"></i></span>
				</h4>
			</div>
			<div class="col-sm-6">
				<div data-role="chart"
					data-auto-bind="false"
	                 data-legend="{ position: 'bottom' }"
	                 data-series-defaults="{
		             	type: 'pie',
	                    labels: {
	                        template: `#= category # - #= kendo.format('{0:P}', percentage)#`,
	                        position: `outsideEnd`,
	                        visible: true,
	                        background: `transparent`
	                    }
	                 }"
	                 data-series="[{
	                                categoryField: 'idFields',
	                                field: 'count'
	                              }]"
	                 data-tooltip="{
		                    visible: true,
		                    template: '#= category # : #= value #'
		                }"
	                 data-bind="source: dataGroup"></div>
	        </div>
	        <div class="col-sm-6">
	        	<div id="groupGrid"></div>
	        </div>
		</div>
		<div class="row" data-bind="visible: visibleNoData">
			<h3 class="text-center">@NO DATA@</h3>
		</div>
	</div>

	<script>
	    var initReport = function() {
	    	var dateRange = 30;
	    	var nowDate = new Date();
	    	var date =  new Date(),
	            timeZoneOffset = date.getTimezoneOffset() * kendo.date.MS_PER_MINUTE;
	            date.setHours(- timeZoneOffset / kendo.date.MS_PER_HOUR, 0, 0 ,0);

	        var fromDate = new Date(date.getTime() + timeZoneOffset - (dateRange - 1) * 86400000);
	        var toDate = new Date(date.getTime() + timeZoneOffset + kendo.date.MS_PER_DAY -1)

		    var observable = kendo.observable({
		    	trueVar: true,
		    	loading: false,
		    	visibleReport: false,
		    	visibleNoData: false,
		    	fromDateTime: fromDate,
            	toDateTime: toDate,
            	groupText: "@Service@",
            	groupBy: "service",
            	filterField: "",
				fromDate: kendo.toString(fromDate, "dd/MM/yyyy H:mm"),
				toDate: kendo.toString(toDate, "dd/MM/yyyy H:mm"),
				groupByOption: [
					{text: "@Service@", value: "service"},
					{text: "@Created by@", value: "createdBy"},
					{text: "@Source@", value: "source"},
					{text: "@Service@ level 1", value: "serviceLv1"},
					{text: "@Service@ level 2", value: "serviceLv2"},
					{text: "@Service@ level 3", value: "serviceLv3"},
					{text: "@Service@ level 1 @and@ 2", value: "serviceLv1-serviceLv2"},
				],
				groupByChange: function(e) {
					this.set('visibleReport', false);
		        	this.set('visibleNoData', false);
					this.set("groupText", e.sender.text());
				},
				fieldOption: [
					{text: "@No@", value: ""},
					{text: "@Created by@", value: "createdBy"},
					{text: "@Source@", value: "source"}
				],
				startDate: function(e) {
					var start = e.sender,
						startDate = start.value(),
						end = $("#end-date").data("kendoDatePicker"),
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
	            endDate: function(e) {
	            	var end = e.sender,
	            		endDate = end.value(),
	            		start = $("#start-date").data("kendoDatePicker"),
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
		        	this.set("fromDate", kendo.toString(this.get("fromDateTime"), "dd/MM/yyyy H:mm"));
		        	this.set("toDate", kendo.toString(this.get("toDateTime"), "dd/MM/yyyy H:mm"));
		        	this.asyncSearch();
		        },
		        asyncSearch: async function() {
		        	var field = "createdAt";
		        	var fromDateTime = new Date(this.fromDateTime.getTime() - timeZoneOffset).toISOString();
	                var toDateTime = new Date(this.toDateTime.getTime() - timeZoneOffset).toISOString();
	                
	                var filter = {
	                    logic: "and",
	                    filters: [
	                        {field: field, operator: "gte", value: fromDateTime},
	                        {field: field, operator: "lte", value: toDateTime}
	                    ]
	                };

	                if(this.filterField && this.filterValue) {
	                	filter.filters.push({field: this.filterField, operator: "eq", value: this.filterValue})
	                }

	                var groupBy = this.groupBy;
	                var groupByArr = [];
	                if(groupBy.indexOf("-") > 0) {
	                	groupBy.split("-").forEach(field => groupByArr.push({field: field}));
	                } else groupByArr.push({field: groupBy});
		        	this.set("loading", true);
		        	
		        	this.dataGroup.read({group: groupByArr, filter: filter}).then(() => {
		        		this.set("loading", false);
		        		$groupGrid = $("#groupGrid");
		        		if($groupGrid.data("kendoGrid")) {
		        			$groupGrid.data("kendoGrid").destroy();
		        		}
		        		$groupGrid.kendoGrid({
					    	dataSource: {
					    		data: this.dataGroup.data(),
					    		aggregate: [
						            { field: 'count', aggregate: 'sum' },
						        ]
					    	},
					    	columns: [
				            	{field: 'idFields', title: this.groupText},
				            	{field: 'count', title: '@Total@'}
			            	]
					    })
		        		if(this.dataGroup.total()) {
			        		this.set('visibleReport', true);
			        		this.set('visibleNoData', false);
			        		$groupGrid.data("kendoGrid").setOptions({columns: [
				            	{field: 'idFields', title: this.groupText, footerTemplate: '@Total@'},
				            	{field: 'count', title: '@Total@', footerTemplate: ftTotal, width: 140}
			            	]});
		        		} else {
		        			this.set('visibleReport', false);
		        			this.set('visibleNoData', true);
		        		}
		        	});
		        },
		        dataGroup: new kendo.data.DataSource({
		        	serverGrouping: true,
		        	serverFiltering: true,
		        	transport: {
		        		read: ENV.reportApi + "ticket/getGroupBy",
		        		parameterMap: parameterMap
		        	},
		        	schema: {
		        		data: "data",
		        		total: "total",
		        		groups: "data",
		        		parse: function(response) {
		        			response.data.map(doc => {
		        				doc.idFields = (doc.idFields || "Undefined").toString();
		        				doc.color = getRandomColor();
		        			});
		        			return response;
		        		}
		        	}
		        })
		    })

		    kendo.bind($(".mvvm"), observable);
		    //observable.setColumns();
	    }

	    window.onload = function() {
	    	initReport()
	    };

	    function ftTotal(data) {
		    return data.count ? data.count.sum : 0;
		}

	    function getPDF(selector, filename = "Report") {
            kendo.drawing.drawDOM($(selector)).then(function(group){
              kendo.drawing.pdf.saveAs(group, `${filename}.pdf`);
            });
        }
	</script>
</div>
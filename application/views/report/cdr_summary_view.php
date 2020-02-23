<div id="page-content">
	<!-- Table Styles Header -->
    <ul class="breadcrumb breadcrumb-top">
        <li>@Report@</li>
        <li>@Call@ @summary@</li>
        <li class="pull-right none-breakcrumb">
            <a role="button" class="btn btn-sm" onclick="exportExcel()"><i class="fa fa-file-pdf-o"></i> <b>@Export@ Excel</b></a>
        </li>
    </ul>
    <!-- END Table Styles Header -->
	<div class="container-fluid mvvm" style="padding-top: 20px; padding-bottom: 10px">
		<div class="row form-horizontal">
			<div class="form-group col-sm-4">
	            <label class="control-label col-xs-4">@Group@</label>
	            <div class="col-xs-8">
	            	<input data-role="dropdownlist"
	            		data-value-primitive="true" 
	            		data-text-field="name"
	            		data-value-field="id"
	            		data-bind="value: group, source: groupOption, events: {change: groupChange}"/>
	        	</div>
	        </div>
	        <div class="form-group col-sm-8">
	            <label class="control-label col-xs-2">@Extension@</label>
	            <div class="col-xs-10">
	            	<select data-role="multiselect" id="select-extension" style="width: 100%"
	            		data-value-primitive="true" 
	            		data-text-field="extension"
	            		data-value-field="extension"
	            		data-tag-template="selectExtensionTemplate"
	            		data-bind="value: extensions, source: extensionOption"></select>
	            	<button class="k-button" data-bind="click: selectAll">@Select All@</button>
	            	<button class="k-button" data-bind="click: deselectAll">@Deselect All@</button>
	            </div>
	        </div>
	    </div>
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
			<div class="form-group col-sm-2">
				<label class="control-label col-xs-4">@Direction@</label>
	            <div class="col-xs-8">
	            	<input data-role="dropdownlist" style="width: 80px" 
	            		data-value-primitive="true" 
	            		data-text-field="text"
	            		data-value-field="value"
	            		data-bind="value: direction, source: directionOption"/>
	        	</div>
			</div>
			<div class="form-group col-sm-2">
	    		<label class="checkbox-inline">
	    			<input type="checkbox" autocomplete="off" data-bind="checked: showTime">
                	<span>@Call duration@</span>
                </label>
			</div>
			<div class="form-group col-sm-12 text-center">
				<button class="k-button" data-bind="click: search, disabled: loading">@Get@</button>
			</div>
		</div>
		<div class="row" data-bind="visible: visibleReport">
	        <div class="col-sm-12">
	        	<div data-role="grid" id="grid1"
                        data-scrollable="true"
                        data-auto-bind="false"
                        data-columns="[
				            	{field: 'extension', title: '@Extension@', footerTemplate: '@Total@', width: 80},
				            	{field: 'ANS', title: '@Answered@', footerTemplate: data => ftTotal(data, 'ANS')},
				            	{field: 'NOA', title: '@No answer@', footerTemplate: data => ftTotal(data, 'NOA')},
				            	{field: 'BUS', title: '@Busy@', footerTemplate: data => ftTotal(data, 'BUS')},
				            	{field: 'OTH', title: '@Other@', footerTemplate: data => ftTotal(data, 'OTH')},
				            	{field: 'total', title: '@Total@', footerTemplate: data => ftTotal(data, 'total')}
			            	]"
                        data-bind="source: dataReport, invisible: showTime"></div>
                <div data-role="grid" id="grid2"
                        data-scrollable="true"
                        data-auto-bind="false"
                        data-columns="[
				            	{field: 'extension', title: '@Extension@', footerTemplate: '@Total@', width: 80},
				            	{field: 'ANS', title: '@Total call answered@', footerTemplate: data => ftTotal(data, 'ANS')},
				            	{field: 'callduration_totalTime', title: '@Total call duration time@', footerTemplate: data => ftTimeTotal(data, 'callduration_total')},
				            	{field: 'callduration_totalAverageTime', title: '@Average call duration time@', footerTemplate: data => ftCallDurationAverage(data, 'callduration_totalAverage')},
				            	{field: 'firstcall_timeTime', title: '@First call@'},
				            	{field: 'lastcall_timeTime', title: '@Last call@'},
			            	]"
                        data-bind="source: dataReport, visible: showTime"></div>
	        </div>
		</div>
		<div class="row" data-bind="visible: visibleNoData">
			<h3 class="text-center">@NO DATA@</h3>
		</div>
	</div>

	<script id="selectExtensionTemplate" type="text/x-kendo-template">
		<span class="label" style="background-color: #: color #">#: extension #</span>
	</script>

	<script>
	    var initReport = function() {
	    	var dateRange = 1;
	    	var nowDate = new Date();
	    	var date =  new Date(),
	            timeZoneOffset = date.getTimezoneOffset() * kendo.date.MS_PER_MINUTE;
	            date.setHours(- timeZoneOffset / kendo.date.MS_PER_HOUR, 0, 0 ,0);

	        var fromDate = new Date(date.getTime() + timeZoneOffset - (dateRange -1) * 86400000);
	        var toDate = new Date(date.getTime() + timeZoneOffset + kendo.date.MS_PER_DAY -1);

	        var propArr = ["NOA", "ANS", "BUS", "OTH", "total"];

	        var durationArr = ["callduration_total", "billduration_total", "totalduration_total"];

	        var firstlastArr = ["firstcall_time", "lastcall_time"];

		    var observable = window.observable = kendo.observable({
		    	trueVar: true,
		    	loading: false,
		    	visibleReport: false,
		    	visibleNoData: false,
		    	fromDateTime: fromDate,
            	toDateTime: toDate,
				fromDate: kendo.toString(fromDate, "dd/MM/yyyy H:mm"),
				toDate: kendo.toString(toDate, "dd/MM/yyyy H:mm"),
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
	            groupOption: dataSourceDropDownList("Group", ["name", "members"], {active: true}, res => {
	            	res.data.unshift({name: "@ALL@", members: [], id: ""});
	            	return res;
	            }),
	            direction: "",
	            directionOption: [
	            	{value: "", text: "@ALL@"},
	            	{value: "inbound", text: "@Call in@"},
	            	{value: "outbound", text: "@Call out@"}
	            ],
	            groupChange: function(e) {
	            	let dataItem = e.sender.dataItem();
	            	let value = e.sender.value();
	            	if(value == "") {
	            		this.extensionOption.read({}).then(() => {this.selectAll()});
	            	} else {
	            		this.extensionOption.read({filter: {field: "extension", operator: "in", value: dataItem.members.toJSON()}}).then(() => {this.selectAll()});
	            	}
	            },
	            extensions: [],
	            extensionOption: dataSourceDropDownListPrivate("User", ["extension"], null, res => {
		        	res.data.map(doc => {
		            	doc.color = getRandomColor();
		        	})
		        	return res;
		        }),
		        selectAll: function(e) {
		        	var required = $("#select-extension").data("kendoMultiSelect");
		            var values = $.map(required.dataSource.data(), function(dataItem) {
		              return dataItem.extension;
		            });
		        	this.set("extensions", values);
		        },
		        deselectAll: function(e) {
		        	var required = $("#select-extension").data("kendoMultiSelect");
			        this.set("extensions", []);
		        },
		        search: function() {
		        	this.set("fromDate", kendo.toString(this.get("fromDateTime"), "dd/MM/yyyy H:mm"));
		        	this.set("toDate", kendo.toString(this.get("toDateTime"), "dd/MM/yyyy H:mm"));
		        	this.asyncSearch();
		        },
		        asyncSearch: async function() {
		        	var field = "starttime";
		        	var fromDateTime = new Date(this.fromDateTime.getTime() - timeZoneOffset).toISOString();
	                var toDateTime = new Date(this.toDateTime.getTime() - timeZoneOffset).toISOString();
	                var extensions = this.get("extensions");
	       			var direction = this.get("direction");
	                
	                var filter = {
	                    logic: "and",
	                    filters: [
	                        {field: field, operator: "gte", value: fromDateTime},
	                        {field: field, operator: "lte", value: toDateTime},
	                        {field: "userextension", operator: "in", value: extensions}
	                    ]
	                };

	                if(direction) {
	                	filter.filters.push({field: "direction", operator: "eq", value: direction})
	                }
		        	
		        	this.dataReport.filter(filter);
                    this.set("loading", false);
                    this.set('visibleReport', true);
                    this.set('visibleNoData', false);
                    $("#grid1").data("kendoGrid").refresh();
                    $("#grid2").data("kendoGrid").refresh();
		        },
		        dataReport: new kendo.data.DataSource({
		        	serverGrouping: true,
		        	serverFiltering: true,
		        	transport: {
		        		read: ENV.reportApi + "cdr/groupByExtensionAndDisposition",
		        		parameterMap: parameterMap
		        	},
		        	aggregate: [
			            { field: 'NOA', aggregate: 'sum' },
			            { field: 'ANS', aggregate: 'sum' },
			            { field: 'BUS', aggregate: 'sum' },
			            { field: 'OTH', aggregate: 'sum' },
			            { field: 'total', aggregate: 'sum' },
			            { field: 'callduration_total', aggregate: 'sum' },
			            { field: 'callduration_totalAverage', aggregate: 'average' }
			        ],
		        	schema: {
		        		data: "data",
		        		total: "total",
		        		groups: "data",
		        		parse: function(response) {
		        			response.data.map(doc => {
		        				propArr.forEach(prop => {
		        					doc[prop] = Number(doc[prop] || 0);
		        					doc[prop + "Time"] = secondsToTime(doc[prop]);
		        				})

		        				durationArr.forEach(prop => {
		        					doc[prop] = Number(doc[prop] || 0);
		        					doc[prop + "Time"] = secondsToTime(doc[prop]);
		        				})

		        				firstlastArr.forEach(prop => {
		        					doc[prop] = Number(doc[prop] || 0);
		        					doc[prop + "Time"] = gridTimestamp(doc[prop]);
		        				})
		        				doc.callduration_totalAverage = Math.ceil(doc.callduration_total / doc.ANS);
		        				doc.callduration_totalAverageTime = secondsToTime(doc.callduration_totalAverage);
		        			})
		        			return response;
		        		}
		        	}
		        }),
		    })

		    kendo.bind($(".mvvm"), observable);
		    //observable.setColumns();
	    }

	    window.onload = function() {
	    	initReport()
	    };

	    function ftTotal(data, field) {
		    return data[field] ? (data[field].sum ? data[field].sum : 0)  : 0;
		}

		function ftTimeTotal(data, field) {
			return data[field] ? secondsToTime(data[field].sum) :  "";
		}

		function ftCallDurationAverage(data) {
			var value = (data.callduration_total && data.ANS) ? Math.ceil(data.callduration_total.sum / data.ANS.sum) : 0; 
			return secondsToTime(value);
		}

	    function getPDF(selector, filename = "Report") {
            kendo.drawing.drawDOM($(selector)).then(function(group){
              kendo.drawing.pdf.saveAs(group, `${filename}.pdf`);
            });
        }

        function exportExcel() {
        	if($("#grid1").is(":visible")) {
        		$("#grid1").data("kendoGrid").saveAsExcel();
        	} else if($("#grid2").is(":visible")) {
        		$("#grid2").data("kendoGrid").saveAsExcel();
        	}
        }
	</script>
</div>
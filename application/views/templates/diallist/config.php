<div class="col-sm-6">
	<div class="block" style="margin-top: 20px">
		<div class="block-title">
			<h2><strong>@Campaign setting@</strong></h2>
		</div>
		<div class="block-content">
			<div class="form-horizontal">
				<div class="form-group">
					<label class="control-label col-xs-4">@Condition@ DoNotCall</label>
					<div class="col-xs-8">
						<input data-role="numerictextbox" data-format="n0" style="width: 100%"
						data-bind="value: item.conditionDonotCall">
					</div>
				</div>
				<div class="form-group">
					<label class="col-xs-4">PRODGRP_ID Code: </label>
					<div class="col-xs-8">
						<select id="prod_code" data-role="multiselect" multiple="multiple"
						dataTextField= "text",
						dataValueField= "value"
						data-no-data-template="noDataTemplate"
						data-bind="value: item.prod_code, source: item.prod_code"
						data-filter="contains"
						data-value-primitive="false"
						style="width: 100%"></select>
					</div>
				</div>
				<div class="form-group text-center">
					<button data-role="button" data-bind="click: save">@Save@</button>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="col-sm-6">
	<div class="block" style="margin-top: 20px">
		<div class="block-title">
			<h2><strong>@Campaign limit@</strong></h2>
		</div>
		<div class="block-content">
			<div class="form-horizontal">
				<div class="form-group">
					<label class="control-label col-xs-6">@Limit@ @concurrent call@</label>
					<div class="col-xs-6">
						<input data-role="numerictextbox" data-format="n0" style="width: 100%"
						data-bind="value: item.limitConcurrentCall, disabled: trueVar">
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="col-sm-12">
    <!-- Web Server Block -->
    <div class="block full">
        <!-- Web Server Title -->
        <div class="block-title">
        	<div class="block-options pull-right">
        		<a role="button" class="btn btn-sm btn-alt btn-info" href="javascript:void(0)" onclick="toggleIntervalDialInProcess(this)"><span class="fa fa-refresh"></span></a>
            </div>
            <h2><strong>@Concurrent auto call@</strong>&nbsp;(<b id="current-total-dial-in-process" class="text-danger"></b> @call@)</h2>
        </div>
        <!-- END Web Server Title -->

        <div class="block-content">
        	<div id="tabstrip">
                <ul>
                    <li class="k-state-active">
                        @Detail@
                    </li>
                    <li>
                        @Monitor@
                    </li>
                </ul>
                <div>
                	<div id="dial-in-process-grid" data-role="grid"
			        	data-columns="[
			        		{field: 'queuename', title: '@Name@ queue'},
			        		{field: 'phone', title: '@Phone@'},
			        		{field: 'createdAt', title: '@Time@'}
			        	]"
			        	data-no-records="{template: `<h2 class='text-danger'>${KENDO.noRecords}</h2>`}" 
			        	data-bind="source: dialInProcessDataSource"></div>
                </div>
                <div>
                	<div id="dial-in-process-live" class="chart"></div>
                </div>
            </div>
        </div>
    </div>
    <!-- END Web Server Block -->
</div>

<script id="noDataTemplate" type="text/x-kendo-tmpl">
        # var value = instance.input.val(); #
        # var id = instance.element[0].id; #
        <div>
            No data found. Do you want to add new item - '#: value #' ?
        </div>
        <br />
        <button class="k-button" onclick="addNew('#: id #', '#: value #')" ontouchend="addNew('#: id #', '#: value #')">Add new item</button>
    </script>

<script type="text/javascript">
	function addNew(widgetId, value) {
		var widget = $("#" + widgetId).getKendoMultiSelect();
		var dataSource = widget.dataSource;
console.log(1)
		if (confirm("Are you sure?")) {
			dataSource.add(value);

			dataSource.one("requestEnd", function(args) {
				if (args.type !== "create") {
					return;
				}

				var newValue = args.response[0].text;
				console.log(newValue)
				console.log(widget.value())
				dataSource.one("sync", function() {
					widget.value(widget.value().concat([newValue]));
				});
			});

			dataSource.sync();
		}
	}
	window.limitConcurrentCall = 30;

	window.queueArr = [];

	window.limitChart = 300;

	// Get the element to init
    var $chartLive = $('#dial-in-process-live');

    // Live Chart
    window.dataLive = [];

    for (var i = 0; i < window.limitChart; i++) {
    	window.dataLive.push(0);
    }

    window.dialInProcessData = getInit();

    function getInit() {
        var res = [{label: "Total", data: []}];

        for (var i = 0; i < window.dataLive.length; ++i)
            res[0].data.push([i, window.dataLive[i]]);

        return res;
    }

    // Initialize live chart
    var chartLive = $.plot($chartLive,
        getInit(),
        {
            series: {shadowSize: 0},
            lines: {show: true, lineWidth: 1, fill: true, fillColor: {colors: [{opacity: 0.2}, {opacity: 0.2}]}},
            colors: ['#cd0000', '#34495e', '#00cc00', '#f4f95e', '#00FFFF', '#D2691E', '#8B008B'],
            grid: {borderWidth: 0, color: '#aaaaaa'},
            yaxis: {show: true, min: 0, max: window.limitConcurrentCall},
            xaxis: {show: false},
            legend: {position: "nw", backgroundOpacity: 0.1, margin: [20, 90]}
        }
    );

    // Update live chart
    async function updateChartLive() {
    	let dataChart = [];
    	window.dialInProcessData.forEach(doc => {
    		let newData = [];
    		doc.data.forEach((value, idx) => {
    			newData.push([idx, value]);
    		});
			dataChart.push({label: doc.label, data: newData});
    	})
    	try {
            if(chartLive.getData().length != dataChart.length) {
            	chartLive.setData(dataChart);
            	chartLive.setupGrid();
            } else {
            	chartLive.setData(dataChart);
            	chartLive.draw();
            }
    	} catch(err) {
			console.log(err);
		}
    }

	$.get(ENV.vApi + "diallist/getDialConfig", function(res) {
		res.limitConcurrentCall = window.limitConcurrentCall;
		var model = {
			trueVar: true,
			item: res,
			save: function() {
				var data = this.item.toJSON();
				$.ajax({
					url: ENV.vApi + "diallist/updateDialConfig",
					type: "POST",
					contentType: "application/json; charset=utf-8",
					data: JSON.stringify(data),
					success: function(response) {
						if(response.status) {
							syncDataSource();
						}
					},
				})
			},

			dialInProcessDataSource: new kendo.data.DataSource({
	            serverFiltering: true,
	            serverSorting: true,
	            sort: [{field: "createdAt", dir: "asc"}],
	            transport: {
	                read: {
	                    url: ENV.restApi + "dial_in_process",
	                    global: false
	                },
	                parameterMap: parameterMap
	            },
	            schema: {
	                data: "data",
	                total: "total",
	                parse: function(res) {
	                	var totalDialInProcess = res.total || 0;
	                    $('#current-total-dial-in-process').text(totalDialInProcess);
	                    var countCallOfQueue = {};
	                    res.data.map(doc => {
	                    	doc.createdAt = gridDate(new Date(doc.createdAt));
	                    	if(window.queueArr.indexOf(doc.queuename) == -1) {
	                    		window.queueArr.push(doc.queuename);
	                    		window.dialInProcessData.push({
                    				label: doc.queuename,
                    				data: window.dataLive
                    			});
	                    	} 
	                    	if(!countCallOfQueue[doc.queuename]) {
                    			countCallOfQueue[doc.queuename] = 1;
                    		} else countCallOfQueue[doc.queuename]++;
	                    });
	                    window.dialInProcessData.map(doc => {
	                    	if(doc.label && doc.label != "Total") {
	                    		let newData = doc.data.slice(1);
	                    		let count = countCallOfQueue[doc.label] ? countCallOfQueue[doc.label] : 0;
                				newData.push(count);
                				doc.data = newData;
                			} else {
                				let newData = doc.data.slice(1);
                				newData.push(totalDialInProcess);
                				doc.data = newData;
                			}
                    	})
	                    setTimeout(updateChartLive, window.timeOutCheck);
	                    return res;
	                }
	            }
	        })
		};

		kendo.bind("#bottom-row", kendo.observable(model));
	});

	function toggleIntervalDialInProcess(ele) {
		if(window.intervalDialInProcess == undefined) {
			$(ele).addClass("fa-spin");
			window.intervalDialInProcess = setInterval(() => {
				if($("#dial-in-process-grid").data("kendoGrid"))
					$("#dial-in-process-grid").data("kendoGrid").dataSource.read();
			}, 2000);
		} else {
			$(ele).removeClass("fa-spin");
			clearInterval(window.intervalDialInProcess);
			window.intervalDialInProcess = undefined;
		}
	}

	$("#tabstrip").kendoTabStrip({
        animation:  {
            open: {
                effects: "fadeIn"
            }
        }
    });
</script>
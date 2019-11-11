<style type="text/css">
	.metrotable > thead > tr > th {
	    padding: .5em 1em 0 0;
	    text-align: left;
	    font-size: 1.5em;
	    font-weight: lighter;
	    color: #bbb;
	    border-bottom: 1px solid #ccc;
	}

	.metrotable > tbody > tr > td {
	    padding: .5em 1em .5em 0;
	    text-align: left;
	    font-size: 1.2em;
	    font-weight: lighter;
	    color: #787878;
	    border-bottom: 1px solid #e1e1e1;
	}
</style>
<script id="row-template" type="text/x-kendo-template">
	<tr>
        <td data-bind="text: name"></td>
        <td data-bind="text: port"></td>
        <td>#= gridBoolean(data.status) #</td>
    </tr>
</script>
<ul class="breadcrumb breadcrumb-top">
    <li>@Admin@</li>
    <li>@Server@</li>
    <li id="server-name"></li>
    <li class="pull-right none-breakcrumb">
    	<a role="button" class="btn btn-sm" onclick="showBeanstalkMonitor()"><i class="fa fa-plug"></i> <b>Check beanstalk</b></a>
    	<a role="button" class="btn btn-sm" onclick="showPSAUX()"><i class="fa fa-plug"></i> <b>Check process</b></a>
        <a role="button" class="btn btn-sm" onclick="checkMongoTop()"><i class="fa fa-plug"></i> <b>Check mongo top</b></a>
        <a role="button" class="btn btn-sm" onclick="checkService()"><i class="fa fa-plug"></i> <b>Check other services</b></a>
    </li>
</ul>
<div class="container-fluid">
	<!-- Dashboard 2 Content -->
    <div class="row">
    	<div class="col-sm-3">
    		<div style="margin-top: 10px">
	    		<div class="alert alert-success">
			    	<h4>UPTIME</h4>
			    	<p><b>Logon: </b><span id="logon-users"></span></p>
			        <p class="text-right text-muted"><b>Running for: </b><span id="running-for"></span></p>
			    </div>
			</div>
    	</div>
    	<div class="col-sm-3">
    	<?php foreach ($disks as $disk) { ?>
    		<div style="margin-top: 10px">
			    <div class="alert alert-info">
			    	<h4>DISK <b><?= $disk["disk_name"] ?></b></h4>
			        <p><b>Total: </b><span><?= getSymbolByQuantity($disk["disk_space"]) ?></span></p>
			        <p class="text-right text-muted"><b>Free: </b><span><?= getSymbolByQuantity($disk["disk_free"]) ?></span></p>
			    </div>
			</div>
    	<?php } ?>
    	</div>
    	<div class="col-sm-6" id="service-status">
    		<table class="metrotable" style="width: 100%; margin-bottom: 5px">
	            <thead>
	                <tr>
	                    <th>Service</th>
	                    <th>Port</th>
	                    <th>Status</th>
	                </tr>
	            </thead>
	            <tbody data-template="row-template"
		         data-bind="source: dataSource">
		         	<tr>
				        <td>Web 4Xs</td>
				        <td>80</td>
				        <td><span class="fa fa-check text-success"></span></td>
				    </tr>
		         	<tr>
				        <td>Mongo</td>
				        <td>27017</td>
				        <td><span class="fa fa-check text-success"></span></td>
				    </tr>
		         </tbody>
	        </table>
    	</div>
	</div>
	<div class="row" id="mongotop-row" style="display: none">
		<div class="col-md-12">
			<!-- Web Server Block -->
            <div class="block full">
                <!-- Web Server Title -->
                <div class="block-title">
                    <div class="block-options pull-right">
                    	<a role="button" class="btn btn-sm btn-alt btn-success" href="javascript:void(0)" onclick="clearMongoTop(this)"><b>Clear</b></a>
                    </div>
                    <h2><strong>MONGO</strong> Top</h2>
                </div>
                <!-- END Web Server Title -->

                <div class="block-content" id="loadarea-container">
                </div>
            </div>
            <!-- END Web Server Block -->
		</div>
	</div>
	<div class="row hidden" id="psaux-contain">
    	<div class="col-md-12">
            <!-- Web Server Block -->
            <div class="block full">
                <!-- Web Server Title -->
                <div class="block-title">
                	<div class="block-options pull-right">
                		<a role="button" class="btn btn-sm btn-alt btn-warning" href="javascript:void(0)" onclick="toggleIntervalPSAUX(this)"><b>Interval</b></a>
                    	<a role="button" class="btn btn-sm btn-alt btn-success" href="javascript:void(0)" onclick="showPSAUX()"><b>Clear</b></a>
                    </div>
                    <h2><strong>Process list</strong></h2>
                </div>
                <!-- END Web Server Title -->

                <div class="block-content">
                	<div class="row">
                		<div class="col-sm-4">
                			<label>Filter: </label>
                			<input id="psaux-filter" class="k-textbox" style="width: 150px"/>
                		</div>
                		<div class="col-sm-4">
                			<label>Limit: </label>
                			<input type="number" id="psaux-limit" class="k-textbox" style="width: 100px"/>
                		</div>
                		<div class="col-sm-4">
                			<button class="k-button" onclick="getPSAUX()">GET</button>
                		</div>
                	</div>
                	<div class="row" style="padding-top: 10px">
                		<div id="psaux-grid"></div>
                	</div>
                </div>
            </div>
            <!-- END Web Server Block -->
        </div>
    </div>

    <div class="row hidden" id="beanstalk-monitor-container">
    	<div class="col-md-12">
            <!-- Web Server Block -->
            <div class="block full">
                <!-- Web Server Title -->
                <div class="block-title">
                	<div class="block-options pull-right">
                    	<a role="button" class="btn btn-sm btn-alt btn-success" href="javascript:void(0)" onclick="clearBeanstalkMonitor(this)"><b>Clear</b></a>
                    	<a href="javascript:void(0)" class="btn btn-alt btn-sm btn-primary" data-toggle="block-toggle-fullscreen"><i class="fa fa-desktop"></i></a>
                    </div>
                    <h2><strong>Beanstalkd monitor</strong></h2>
                </div>
                <!-- END Web Server Title -->

                <div class="block-content">
                	<div class="row">
                		<iframe src="" style="border: 0; width: 100%; height: 500px"></iframe>
                	</div>
                </div>
            </div>
            <!-- END Web Server Block -->
        </div>
    </div>

	<div class="row">
        <div class="col-md-6">
            <!-- Web Server Block -->
            <div class="block full">
                <!-- Web Server Title -->
                <div class="block-title">
                    <div class="block-options pull-right">
                    	<a role="button" class="btn btn-sm btn-alt btn-success" href="javascript:void(0)" onclick="topcpuDetail(this)"><b>TOP</b></a>
                    	<span id="load-avg" class="label label-info">_ - _ - _</span>
                        <span id="cpu-load-live-info" class="label label-primary">%</span>
                        <span id="cpu-load-warning" class="label label-success animation-pulse">CPU Load</span>
                    </div>
                    <h2><strong>CPU</strong> Server (<span id="numcores"></span> cores)</h2>
                </div>
                <!-- END Web Server Title -->

                <!-- Web Server Content -->
                <!-- Flot Charts (initialized in js/pages/index2.js), for more examples you can check out http://www.flotcharts.org/ -->
                <div id="cpu-load-live" class="chart"></div>
                <!-- END Web Server Content -->
                <pre id="topcpu-detail" style="background-color: white; display: none; margin-top: 10px"></pre>
            </div>
            <!-- END Web Server Block -->
        </div>
        <div class="col-md-6">
            <!-- Web Server Block -->
            <div class="block full">
                <!-- Web Server Title -->
                <div class="block-title">
                    <div class="block-options pull-right">
                    	<a role="button" class="btn btn-sm btn-alt btn-success" href="javascript:void(0)" onclick="topmemDetail(this)"><b>TOP</b></a>
                    	<span id="ram-detail" class="label label-info">_ / _</span>
                        <span id="ram-load-live-info" class="label label-primary">%</span>
                        <span id="ram-load-warning" class="label label-success animation-pulse">RAM Usage</span>
                    </div>
                    <h2><strong>RAM</strong> Server</h2>
                </div>
                <!-- END Web Server Title -->

                <!-- Web Server Content -->
                <!-- Flot Charts (initialized in js/pages/index2.js), for more examples you can check out http://www.flotcharts.org/ -->
                <div id="ram-load-live" class="chart"></div>
                <pre id="topmem-detail" style="background-color: white; display: none; margin-top: 10px"></pre>
                <!-- END Web Server Content -->
            </div>
            <!-- END Web Server Block -->
        </div>
    </div>
	<!-- END Dashboard 2 Content -->
</div>
<script type="text/javascript">

	var timeOutCheck = 1000;

	function topcpuDetail(ele) {
		$(ele).hide();
		$.ajax({
			url: ENV.reportApi + "server/topcpu",
			global: false,
			dataType: "text",
			success: function(response) {
				$('#topcpu-detail').show().html(response);
			}
		})
		setTimeout(topcpuDetail, timeOutCheck);
	}

	function topmemDetail(ele) {
		$(ele).hide();
		$.ajax({
			url: ENV.reportApi + "server/topmem",
			global: false,
			dataType: "text",
			success: function(response) {
				$('#topmem-detail').show().html(response);
			}
		})
		setTimeout(topmemDetail, timeOutCheck);
	}

	function getSymbolByQuantity(Kbytes) {
		let symbol = ['KiB', 'MiB', 'GiB', 'TiB'];
		let exp = Math.floor(Math.log(Kbytes)/Math.log(1024));
		let stogare = Math.floor(Kbytes/Math.pow(1024, Math.floor(exp)) * 1000) / 1000;

		return [stogare, symbol[exp]];
	}

	function checkService() {
		var model = {
	        dataSource: new kendo.data.DataSource({
	            transport: {
	                read: `${ENV.reportApi}server/service`
	            },
	            schema: {
	                data: "data"
	            }
	        })
	    };
	    $serviceStatus = $("#service-status");
	    kendo.bind($serviceStatus, kendo.observable(model));
	}

	function clearMongoTop() {
		$("#mongotop-row").hide();
		$("#loadarea-container").html("");
		clearInterval(window.tailload);
	}

	function checkMongoTop() {
		$("#mongotop-row").show();
		$("#loadarea-container").html(`<iframe src="${ENV.reportApi}server/mongotop" id="loadarea" style="width: 100%; border: 0; height: 220px"></iframe>`);
		window.tailload = setInterval(function() {
		  var elem = document.getElementById('loadarea');
		  elem.contentWindow.scrollTo( 0, 999999 );
		}, 500);
	}

	function showPSAUX() {
		$("#psaux-contain").toggleClass("hidden");
		$("#psaux-limit").val(10);
	}

	function toggleIntervalPSAUX(ele) {
		if(window.intervalPSAUX == undefined) {
			window.intervalPSAUX = setInterval(() => {
				if($("#psaux-grid").data("kendoGrid"))
					$("#psaux-grid").data("kendoGrid").dataSource.read();
			}, 2000);
		} else {
			clearInterval(window.intervalPSAUX);
			window.intervalPSAUX = undefined;
		}
	}

	function showBeanstalkMonitor() {
		$container = $("#beanstalk-monitor-container");
		$container.find("iframe").attr("src", "/public/other/beanstalk_monitor/");
		$container.removeClass("hidden");
	}

	function clearBeanstalkMonitor() {
		$container = $("#beanstalk-monitor-container");
		$container.find("iframe").attr("src", "");
		$container.addClass("hidden");
	}

	function getPSAUX() {
		if($("#psaux-grid").data("kendoGrid")) {
			$("#psaux-grid").data("kendoGrid").destroy();
		}
		$("#psaux-grid").kendoGrid({
			columns: [
				{field: "USER", title: "USER", width: 90},
				{field: "PID", title: "PID", width: 90},
				{field: "CPU", title: "CPU", width: 90},
				{field: "MEM", title: "MEM", width: 90},
				{field: "START", title: "START", width: 90},
				{field: "TIME", title: "TIME", width: 90},
				{field: "COMMAND", title: "COMMAND"},
				/*{title: "ACTION", command: ["destroy"], width: 120}*/
			],
			dataSource: {
				pageSize: $("#psaux-limit").val(),
				transport: {
					read: {
						url: ENV.reportApi + "server/psaux",
						data: {
							filter: $("#psaux-filter").val(),
							limit: $("#psaux-limit").val()
						},
						global: false
					}
				},
				schema: {
					data: "data",
					total: "total"
				}
			},
			pageable: true,
			sortable: true,
			filterable: true,
			resizable: true,
		})
	}

	var Server = function() {
	    return {
	        init: function(timeOutCheck) {
	        	var n = 300;
	        	var numcores = <?= isset($numcores) ? $numcores : 1 ?>;
	        	var total_mem = <?= isset($total_mem) ? $total_mem : 0 ?>;
	        	$("#numcores").text(numcores);
	            /*
	             * Flot Jquery plugin is used for charts
	             *
	             * For more examples or getting extra plugins you can check http://www.flotcharts.org/
	             * Plugins included in this template: pie, resize, stack, time
	             */

	            // Get the element to init
	            var $chartLive = $('#cpu-load-live');
	            var $cpuLoadWarning = $("#cpu-load-warning");

	            // Live Chart
	            var dataLive = [];

	            for (var i = 0; i < n; i++) {
	            	dataLive.push(0);
	            }

	            function getInit() {
	                var res = [{label: "", data: []}];

	                for (var i = 0; i < dataLive.length; ++i)
	                    res[0].data.push([i, dataLive[i]]);

	                return res;
	            }

	            var currentServer = "";
	            var serverArr = [];
	            var dataChart = [dataLive, dataLive];

	            async function getCPUData() {
	            	var y = 0;

                	var loadAvg = await $.ajax({
                		url: ENV.reportApi + "server/loadavg",
                		global: false
                	});
                	if(loadAvg) {
	                    y = loadAvg.data[0] * 100 / numcores;
	                    $('#cpu-load-live-info').html(y.toFixed(0) + '%');
	                    $('#load-avg').text(loadAvg.data.join(" - "));
	                    $('#logon-users').text(loadAvg.users);
	                    $('#running-for').text(loadAvg.runningfor);
	                    currentServer = loadAvg.serverName;
	                    if(serverArr.indexOf(loadAvg.serverName) == -1) serverArr.push(loadAvg.serverName);
	                    var index = serverArr.indexOf(loadAvg.serverName);
	                    dataChart[index] = dataChart[index].slice(1);
	                    dataChart[index].push(y);
	                    if(y > 80) {
	                    	$cpuLoadWarning.removeClass("label-success label-warning").addClass("label-danger");
	                    } else if(y > 60) {
	                    	$cpuLoadWarning.removeClass("label-success label-danger").addClass("label-warning");
	                    } else {
	                    	$cpuLoadWarning.removeClass("label-danger label-warning").addClass("label-success");
	                    }
                    }

                    var res = [];

                    serverArr.forEach(serverName => {
                    	res.push({label: serverName, data: []})
                    });
	                
	                dataChart.forEach((data, index) => {
	                	if(typeof res[index] != "undefined") {
		                	for (var i = 0; i < data.length; ++i) 
		                    	res[index].data.push([i, data[i]]);
	                	}
	                })

	                // Show live chart info
	                return Object.values(res);
	            }

	            // Initialize live chart
	            var chartLive = $.plot($chartLive,
	                getInit(),
		            {
		                series: {shadowSize: 0},
		                lines: {show: true, lineWidth: 1, fill: true, fillColor: {colors: [{opacity: 0.2}, {opacity: 0.2}]}},
		                colors: ['#34495e', '#00cc00'],
		                grid: {borderWidth: 0, color: '#aaaaaa'},
		                yaxis: {show: true, min: 0, max: 105},
		                xaxis: {show: false},
		                legend: {position: "nw", backgroundOpacity: 0.1, margin: [20, 90]}
		            }
	            );

	            // RAM
	            var chartLiveRam = $('#ram-load-live');
	            var $ramLoadWarning = $("#ram-load-warning");

	            var dataLiveRam = [];
	            var serverRamArr = [];

	            function getInitRam() {

	            	var res = [{label: "", data: []}];

	                for (var i = 0; i < dataLive.length; ++i)
	                    res[0].data.push([i, dataLive[i]]);

	                return res;
	            }

	            var dataChartRam = [dataLive, dataLive];

	            async function getRAMData() {

	                var y = 0;

                	var ram = await $.ajax({
                		url: ENV.reportApi + "server/ram",
                		global: false
                	});
                	if(ram) {
	                    var used_mem = total_mem - ram.free; 
	                    y = used_mem * 100 / total_mem;
	                    $('#ram-load-live-info').html(y.toFixed(0) + '%');
	                    $('#ram-detail').text(getSymbolByQuantity(used_mem).join(" ") + " / " + getSymbolByQuantity(total_mem).join(" "));
	                    currentServer = ram.serverName;
	                    if(serverRamArr.indexOf(ram.serverName) == -1) serverRamArr.push(ram.serverName);
	                    var index = serverRamArr.indexOf(ram.serverName);
	                    dataChartRam[index] = dataChartRam[index].slice(1);
	                    dataChartRam[index].push(y);
	                    if(y > 80) {
	                    	$ramLoadWarning.removeClass("label-success label-warning").addClass("label-danger");
	                    } else if(y > 60) {
	                    	$ramLoadWarning.removeClass("label-success label-danger").addClass("label-warning");
	                    } else {
	                    	$ramLoadWarning.removeClass("label-danger label-warning").addClass("label-success");
	                    }
                    }

                    var res = [];

                    serverRamArr.forEach(serverName => {
                    	res.push({label: serverName, data: []})
                    });
	                
	                dataChartRam.forEach((data, index) => {
	                	if(typeof res[index] != "undefined") {
		                	for (var i = 0; i < data.length; ++i) 
		                    	res[index].data.push([i, data[i]]);
	                	}
	                })

	                // Show live chart info
	                return Object.values(res);
	            }

	            // Initialize live chart
	            var chartLiveRam = $.plot(chartLiveRam,
	                getInitRam(),
		            {
		                series: {shadowSize: 0},
		                lines: {show: true, lineWidth: 1, fill: true, fillColor: {colors: [{opacity: 0.2}, {opacity: 0.2}]}},
		                colors: ['#34495e', '#00cc00'],
		                grid: {borderWidth: 0, color: '#aaaaaa'},
		                yaxis: {show: true, min: 0, max: 105},
		                xaxis: {show: false},
		                legend: {position: "nw", backgroundOpacity: 0.1, margin: [20, 90]}
		            }
	            );

	            // Update live chart
	            async function updateChartLive() {
	            	try {
	            		var CPUData = await getCPUData();

		                if(chartLive.getData().length != CPUData.length) {
		                	chartLive.setData(CPUData);
		                	chartLive.setupGrid();
		                } else {
		                	chartLive.setData(CPUData);
		                	chartLive.draw();
		                }
		                var RAMData = await getRAMData();
		                if(chartLiveRam.getData().length != RAMData.length) {
		                	chartLiveRam.setData(RAMData);
		                	chartLiveRam.setupGrid();
		                } else {
		                	chartLiveRam.setData(RAMData);
		                	chartLiveRam.draw();
		                }
		                var serverArrHtml = [];
		                serverArr.forEach(serverName => {
		                	serverArrHtml.push(`<span class="label ${(currentServer == serverName) ? 'label-primary animation-pulse' : 'label-default'}">${serverName}</span>`);
		                })
		                $('#server-name').html(serverArrHtml.join("&nbsp;"));
	            	} catch(err) {
						console.log(err);
					}
	                setTimeout(updateChartLive, timeOutCheck);
	            }

	            // Start getting new data
	            updateChartLive();
	        }
	    };
	}();
	Server.init(timeOutCheck);
</script>
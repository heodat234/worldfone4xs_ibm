<style type="text/css">
    #all-scheduler-switch {width: 90px; margin: 5px auto 0;}
    #all-scheduler-switch .onoffswitch-inner:before {content: "@All@";}
    #all-scheduler-switch .onoffswitch-inner:after {content: "@Only me@";}
</style>

<!-- Table Styles Header -->
<ul class="breadcrumb breadcrumb-top">
    <li>Admin</li>
    <li>Currrent Call Average</li>
</ul>
<!-- END Table Styles Header -->

<div class="container-fluid">
    <div class="row options" style="margin-bottom: 10px; margin-top: 20px">
        <div class="col-sm-4">
            <b>@Date@: </b>
            <input id="date-picker" style="width: 110px" />
            <b>@Department@: </b>
            <input id="department-picker" style="width: 90px" />
        </div>
        <div class="col-sm-4">
            <b>@Chart type@: </b>
            <label class="radio-inline">
                <input id="typeColumn" name="seriesType" type="radio" value="column" checked="checked" autocomplete="off" />@Columns@
            </label>
            <label class="radio-inline">
                <input id="typeBar" name="seriesType" type="radio" value="bar" autocomplete="off" />@Bars@
            </label>
            <label class="radio-inline">
                <input id="typeLine" name="seriesType" type="radio" value="line" autocomplete="off" />@Lines@
            </label>
        </div>
        <div class="col-sm-4">
            <b>@Option@: </b>
            <label class="checkbox-inline">
                <input id="stack" type="checkbox" autocomplete="off" checked="checked">
                @Stacked@
            </label>
        </div>
    </div>
    <div class="demo-section k-content wide">
        <div id="chart"></div>
    </div>
</div>
<script>
    var series = [{
        field: "outbound",
        categoryField: "time",
        name: "@Total@ @Call out@",   
    }, {
        field: "inbound",
        categoryField: "time",
        name: "@Total@ @Call in@",
        // Line chart marker type
        markers: { type: "square" }
    }];

    function createChart() {
        $("#chart").kendoChart({
            dataSource: {
                transport: {
                    read: {
                        url: ENV.reportApi + "current_call/every_hour",
                        dataType: "json"
                    }
                },
                sort: {
                    field: "start",
                    dir: "asc"
                }
            },
            title: {
                text: "@Statistic@ @average@ @current call@ @today@"
            },
            legend: {
                position: "bottom"
            },
            seriesDefaults: {
                type: "column",
                stack: true
            },
            series: series,
            valueAxis: {
                labels: {
                    format: "N0"
                },
                majorUnit: 5,
                line: {
                    visible: false
                }
            },
            categoryAxis: {
                majorGridLines: {
                    visible: false
                }
            },
            tooltip: {
                visible: true,
                format: "{0}"
            }
        });
    }

    $(document).ready(function() {
        createChart();
        $(document).bind("kendo:skinChange", createChart);
        $(".options").bind("change", refresh);
        let date = new Date();
        $("#date-picker").kendoDatePicker({
        	format: "dd/MM/yyyy",
        	value: date
        });
        $("#department-picker").kendoDropDownList({
        	dataSource: <?php echo json_encode($types); ?>,
        })
    });

    function refresh() {
        var chart = $("#chart").data("kendoChart"),
            type = $("input[name=seriesType]:checked").val(),
            stack = $("#stack").prop("checked"),
            date = $("input#date-picker").data("kendoDatePicker"),
            department = $("#department-picker").data("kendoDropDownList").value();

        for (var i = 0, length = series.length; i < length; i++) {
            series[i].stack = stack;
            series[i].type = type;
        };
        chart.dataSource.read({date: date.value(), department: department}).then(function(){
            chart.setOptions({
                series: series,
                title: {
                    text: "@Statistic@ "+department+" @average@ @current call@ @date@ " + gridDate(date.value(), "dd/MM/yyyy")
                }
            });
        })
    }
</script>

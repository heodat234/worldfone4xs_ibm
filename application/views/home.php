<style type="text/css">
    #all-scheduler-switch {width: 90px; margin: 5px auto 0;}
    #all-scheduler-switch .onoffswitch-inner:before {content: "@All@";}
    #all-scheduler-switch .onoffswitch-inner:after {content: "@Only me@";}
</style>

<!-- Table Styles Header -->
<ul class="breadcrumb breadcrumb-top">
    <li>@Home@</li>
    <li class="pull-right none-breakcrumb">
        <div class="input-group-btn column-widget">
            <a role="button" class="btn btn-sm dropdown-toggle" data-toggle="dropdown" onclick="openHomeScheduler(this)"><i class="gi gi-calendar"></i> <b>@Calendar@</b></a>
            <div class="dropdown-menu dropdown-menu-right" style="width: 80vw; max-height: 80vh; overflow-y: scroll;">
                <div style="width: 100%;">
                    <div class="onoffswitch" id="all-scheduler-switch">
                        <input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" id="myonoffswitch">
                        <label class="onoffswitch-label" for="myonoffswitch">
                            <span class="onoffswitch-inner"></span>
                            <span class="onoffswitch-switch"></span>
                        </label>
                    </div>
                </div>
                <div id="home-scheduler"></div>
            </div>
        </div>
    </li>
</ul>
<!-- END Table Styles Header -->

<div class="container-fluid">
    <h2 class="text-center">@Welcome@ <span id="agentname-home"></span></h2>
    <script type="text/javascript">
        $("#agentname-home").text(ENV.agentname);
    </script>
    <div class="row options" style="margin-bottom: 10px">
        <div class="col-sm-4">
            <b>@Week@: </b>
            <label class="radio-inline">
                <input id="currentWeek" name="week" type="radio" value="0" checked="checked" autocomplete="off" />@Current week@
            </label>
            <label class="radio-inline">
                <input id="previousWeek" name="week" type="radio" value="-1" autocomplete="off" />@Previous week@
            </label>
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
            <label class="checkbox-inline <?= empty($viewAll) ? 'hidden' : '' ?>">
                <input id="all" type="checkbox" autocomplete="off">
                @All@
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
        categoryField: "date",
        name: "@Total@ @Call out@",   
    }, {
        field: "inbound",
        categoryField: "date",
        name: "@Total@ @Call in@",
        // Line chart marker type
        markers: { type: "square" }
    }];

    function createChart() {
        $("#chart").kendoChart({
            dataSource: {
                transport: {
                    read: {
                        url: ENV.vApi + "wfpbx/call_before_week",
                        dataType: "json",
                        data: {extension: ENV.extension}
                    }
                },
                sort: {
                    field: "start",
                    dir: "asc"
                }
            },
            title: {
                text: "@Statistic call current week@ @of me@"
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
    });

    function refresh() {
        var chart = $("#chart").data("kendoChart"),
            type = $("input[name=seriesType]:checked").val(),
            stack = $("#stack").prop("checked"),
            week = Number($("input[name=week]:checked").val()),
            all = $("#all").prop("checked");

        for (var i = 0, length = series.length; i < length; i++) {
            series[i].stack = stack;
            series[i].type = type;
        };
        chart.dataSource.read({week: week, extension: all ? "" : ENV.extension}).then(function(){
            chart.setOptions({
                series: series,
                valueAxis: {
                    majorUnit: all ? 50 : 5,
                },
                title: {
                    text: week ? "@Statistic call previous week@ " + (all ? " @all@" : "@of me@") : "@Statistic call current week@ "  + (all ? "@all@" : "@of me@")
                }
            });
        })
    }

    function openHomeScheduler(ele) {
        $(ele).toggleClass("keepopen");
        var Config = {
            crudApi: ENV.restApi,
            collection: "scheduler"
        };
        var $scheduler = $("#home-scheduler");
        if($scheduler.data("kendoScheduler")) {
            if($(ele).hasClass("keepopen")) {
                $scheduler.data("kendoScheduler").dataSource.read();
            }
        } else {
            var date = new Date();
            KENDO.schedulerMessages.today = "@Today@ " + kendo.toString(date, "dd/MM/yy");
            $scheduler.kendoScheduler({
                messages: KENDO.schedulerMessages,
                date: date,
                eventTemplate: $("#event-all-day-template").html(),
                startTime: new Date(date.setHours(0, 0, 0)),
                height: "800",
                views: [
                    { type: "month", title: "@Month@", selected: true},
                    { type: "week", title: "@Week@", allDayEventTemplate: $("#event-all-day-template").html()},
                    { type: "day", title: "@Day@", allDayEventTemplate: $("#event-all-day-template").html(), group: {
                        resources: ["Shift"]}
                    }
                ],
                timezone: "Asia/Ho_Chi_Minh",
                editable: false,
                dataSource: {
                    filter: {field: "ownerId", operator: "eq", value: ENV.extension},
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
                                title: { defaultValue: "No title", validation: { required: true } },
                                start: { type: "date" },
                                end: { type: "date" },             
                                description: { },
                                ownerId: { defaultValue: ENV.extension },
                                isAllDay: { type: "boolean" }
                            }
                        }
                    }
                },
                resources: [
                    {
                        field: "shift",
                        title: "@Shift@",
                        name: "Shift",
                        dataSource: dataSourceJsonData(["Scheduler", "shift"])
                    },
                    {
                        field: "ownerId",
                        title: "@Extension@",
                        name: "Extension",
                        multiple: true,
                        dataSource: {
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
                        }
                    }
                ]
            });
        }

        $("#myonoffswitch").on("change", function(e) {
            $scheduler.data("kendoScheduler").dataSource.filter(e.currentTarget.checked ? {} : {field: "ownerId", operator: "eq", value: ENV.extension});
        })
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
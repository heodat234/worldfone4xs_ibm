<div id="page-content">
    <!-- Table Styles Header -->
    <ul class="breadcrumb breadcrumb-top">
        <li>@Report@</li>
        <li>@QC By Agent@</li>
        <li class="pull-right none-breakcrumb">
            <a role="button" class="btn btn-sm" onclick="exportGridExcel()"><i class="fa fa-file-pdf-o"></i> <b>@Export@ Excel</b></a>
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
        </div>
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
            <div class="form-group col-sm-12 text-center">
                <button class="k-button" data-bind="click: search, disabled: loading">@Get@</button>
            </div>
        </div>
        <div class="row" data-bind="visible: visibleReport">
            <div class="col-sm-12">
                <div data-role="grid" id="grid"
                     data-pageable="true"
                     data-scrollable="true"
                     data-auto-bind="true"
                     data-excel="{allPages: true}"
                     data-columns="[
				            	{
				            	    title: '@No.@',
				            	    template: '#: ++record #',
				            	    field: 'no'
                                },
				            	{
				            	    field: 'extension',
				            	    title: '@Extension@',
                                },
				            	{
                                    field: 'agentname',
                                    title: '@Agent name@',
                                },
				            	{
				            	    field: 'totalCall',
                                    title: '@Counted call@',
                                },
				            	{
				            	    field: 'totalMark',
				            	    title: '@Total mark@',
                                },
				            	{
				            	    field: 'averageMark',
				            	    title: '@Average mark@',
                                },
			            	]"
                     data-bind="source: dataReport, events: {dataBinding: gridOnDataBinding}"></div>
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
        function dataSourceService(level=1, parent_id=null) {
            return new kendo.data.DataSource({
                transport: {
                    read: {
                        url: `${ENV.restApi}servicelevel`,
                        data: {id: parent_id, "lv": level}
                    },
                    parameterMap: parameterMap
                }
            })
        }

        var initReport = function() {
            var dateRange = 1;
            var nowDate = new Date();
            var date =  new Date(),
                timeZoneOffset = date.getTimezoneOffset() * kendo.date.MS_PER_MINUTE;
            date.setHours(- timeZoneOffset / kendo.date.MS_PER_HOUR, 0, 0 ,0);

            var fromDate = new Date(date.getTime() + timeZoneOffset - (dateRange - 1 + 1) * 86400000);
            var toDate = new Date(date.getTime() + timeZoneOffset - (1) * 86400000 + kendo.date.MS_PER_DAY -1);

            var propArr = ["AVA", "SOC", "ACW", "SUN", "total"];

            var observable = window.observable = kendo.observable({
                trueVar: true,
                loading: false,
                visibleReport: false,
                visibleNoData: false,
                fromDateTime: fromDate,
                toDateTime: toDate,
                fromDate: kendo.toString(fromDate, "dd/MM/yyyy H:mm"),
                toDate: kendo.toString(toDate, "dd/MM/yyyy H:mm"),
                isHiddenServiceLevel: true,
                status: '',
                service: '',
                serviceLv1Data: '',
                serviceLv2Data: '',
                serviceLv3Data: '',
                call_direction: '',
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
                    var customernumber = this.get('customernumber');
                    var queue = this.get('queue');

                    var filters = [
                        {field: field, operator: "gte", value: fromDateTime},
                        {field: field, operator: "lte", value: toDateTime},
                    ];

                    if(extensions.length > 0) {
                        filters.push({field: "glide_extension", operator: "in", value: extensions})
                    }

                    if(customernumber) {
                        filters.push({field: "customernumber", operator: "eq", value: customernumber})
                    }

                    if(queue) {
                        filters.push({field: "extension", operator: "eq", value: queue})
                    }

                    this.dataReport.filter({filters});
                    this.set("loading", false);
                    this.set('visibleReport', true);
                    this.set('visibleNoData', false);
                    // $("#grid").data("kendoGrid").refresh();
                },
                dataReport: new kendo.data.DataSource({
                    serverGrouping: true,
                    serverFiltering: true,
                    serverPaging: true,
                    pageSize: 15,
                    transport: {
                        read: ENV.reportApi + "qcbyagent/read",
                        parameterMap: parameterMap
                    },
                    schema: {
                        data: "data",
                        total: "total",
                        groups: "data",
                        parse: function(response) {
                            response.data.map(doc => {
                                doc.extension = doc._id.extension;
                                doc.averageMark = (doc.totalMark / doc.totalCall).toFixed(2);
                                return doc;
                            });
                            return response;
                        }
                    }
                }),
                callDirection: dataSourceJsonData(["Call", "direction"]),
                statusDataSource: dataSourceJsonData(["Ticket", "status"]),
                serviceLv1Option: dataSourceService(1),
                serviceLv2Option: [],
                serviceLv3Option: [],
                onSearch1: function(e) {
                    var field = "value1";
                    var filterValue = {field: field, operator: "eq", value: e.dataItem.name};
                    var filter = {
                        logic: "and",
                        filters: [filterValue]
                    };
                    this.serviceOption.filter(filter);

                    var parent_id = e.dataItem.id;
                    this.set("serviceLv2Option", dataSourceService(2, parent_id));
                    this.set("serviceLv3Option", []);
                    $("input[name=serviceLv2]").data("kendoDropDownList").refresh();
                    $("input[name=serviceLv3]").data("kendoDropDownList").refresh();
                    this.set("service", e.dataItem.name);
                },
                onSearch2: function(e) {
                    var filter = this.serviceOption.filter();
                    var field = "value2";
                    var filterValue = {field: field, operator: "eq", value: e.dataItem.name};
                    if(filter) {
                        filter.filters.filter(doc => doc.field != field);
                        filter.filters.push(filterValue);
                    } else {
                        filter = {
                            logic: "and",
                            filters: []
                        };
                        filter.filters.push(filterValue);
                    }

                    this.serviceOption.filter(filter);

                    var parent_id = e.dataItem.id;
                    this.set("serviceLv3Option", dataSourceService(3, parent_id));
                    $("input[name=serviceLv3]").data("kendoDropDownList").refresh();
                    this.set("service", this.get('serviceLv1Data') + ' / ' + e.dataItem.name);
                },
                onSearch3: function(e) {
                    var filter = this.serviceOption.filter();
                    var field = "value3";
                    // var filterValue = {field: field, operator: "eq", value: this.get('serviceLv3Data')};
                    // if(filter) {
                    //     filter.filters.filter(doc => doc.field != field);
                    //     filter.filters.push(filterValue);
                    // } else {
                    //     filter = {
                    //         logic: "and",
                    //         filters: []
                    //     };
                    //     filter.filters.push(filterValue);
                    // }
                    // this.serviceOption.filter(filter);
                    // var dropdownlist = $("input[name=service]").data("kendoDropDownList");
                    // dropdownlist.select(dropdownlist.ul.children().eq(0));
                    // this.serviceOption.filter({});
                    this.set("service", this.get("serviceLv1Data") + ' / ' + this.get("serviceLv2Data") + ' / ' + e.dataItem.name);
                },
                serviceOption: new kendo.data.DataSource({
                    transport: {
                        read: ENV.vApi + "servicelevel/select",
                        parameterMap: parameterMap
                    },
                    schema: {
                        data: "data",
                        total: "total"
                    },
                    error: errorDataSource
                }),
                gridOnDataBinding: function() {
                    var grid = $("#grid").data('kendoGrid');
                    record = (grid.dataSource.page() - 1) * grid.dataSource.pageSize();
                },
                queuesOption: new kendo.data.DataSource({
                    transport: {
                        read: ENV.vApi + "select/queues",
                        parameterMap: parameterMap
                    },
                    schema: {
                        data: "data"
                    }
                }),
            });
            kendo.bind($(".mvvm"), observable);
            //observable.setColumns();
        }

        window.onload = function() {
            initReport();
            // Table.init();
        };

        function ftTotal(data, field) {
            return data[field] ? (data[field].sum ? data[field].sum : 0)  : 0;
        }

        function ftTimeTotal(data, field) {
            return data[field] ? secondsToTime(data[field].sum) :  "";
        }

        function getPDF(selector, filename = "Report") {
            kendo.drawing.drawDOM($(selector)).then(function(group){
                kendo.drawing.pdf.saveAs(group, `${filename}.pdf`);
            });
        }

        function exportGridExcel() {
            var grid = $("#grid").data('kendoGrid');
            var gridColumn = grid.columns;
            var column = [];
            var filterValue = grid.dataSource.filter();
            $.each(gridColumn, function(key, value) {
                column.push({field: value.field, title: value.title});
            });
            $.ajax({
                url: ENV.reportApi + "qcbyagent/exportExcel",
                data: {q: JSON.stringify({take: grid.dataSource.total(), skip: 0, page: 1, pageSize: grid.dataSource.total(), filter: {logic: 'and', filters: filterValue.filters}, column: column})},
                success: response => {
                    window.location.href = response;
                }
            });
        }

        function detailData(ele) {
            console.log(ele);
            var uid = $(ele).data('uid');
            var dataItem = $("#grid").data("kendoGrid").dataSource.getByUid(uid);
            window.open(`manage/ticket/email/#/detail/${dataItem.id}`,'_blank',null);
        }

        $(document).on("click", ".grid-name", function() {
            console.log("TEST");
            detailData($(this).closest("tr"));
        });
    </script>
</div>
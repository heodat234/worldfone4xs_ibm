<div id="page-content">
    <!-- Table Styles Header -->
    <ul class="breadcrumb breadcrumb-top">
        <li>@Nam A Report@</li>
        <li>@Support Customer@</li>
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
            <div class="form-group col-sm-4">
                <label class="control-label col-xs-4">@Service level 1@</label>
                <div class="col-xs-8">
                    <input data-role="dropdownlist"
                           name="serviceLv1"
                           data-filter="contains"
                           data-value-primitive="true"
                           data-text-field="name"
                           data-value-field="name"
                           data-option-label="@All@"
                           data-bind="value: serviceLv1, source: serviceLv1Option, events: {select: onSearch1}"/>
                </div>
                <input data-role="dropdownlist" name="service"
                       data-filter="contains"
                       data-value-primitive="true"
                       data-text-field="value"
                       data-value-field="value"
                       data-bind="value: service, source: serviceOption, events: {select: serviceSelect}, invisible: isHiddenServiceLevel"/>
            </div>
            <div class="form-group col-sm-8">
                <label class="control-label col-xs-2">@Service level 2@</label>
                <div class="col-xs-10">
                    <input data-role="dropdownlist"
                           name="serviceLv2"
                           data-filter="contains"
                           data-value-primitive="true"
                           data-text-field="name"
                           data-value-field="name"
                           data-option-label="@All@"
                           data-bind="value: serviceLv2, source: serviceLv2Option, events: {select: onSearch2}"/>
                </div>
            </div>
        </div>
        <div class="row form-horizontal">
            <div class="form-group col-sm-4">
                <label class="control-label col-xs-4">@Service level 3@</label>
                <div class="col-xs-8">
                    <input data-role="dropdownlist"
                           name="serviceLv3"
                           data-filter="contains"
                           data-value-primitive="true"
                           data-text-field="name"
                           data-value-field="name"
                           data-option-label="@All@"
                           data-bind="value: serviceLv3, source: serviceLv3Option, events: {select: onSearch3}"/>
                </div>
            </div>
            <div class="form-group col-sm-8">
                <label class="control-label col-xs-2">@Status@</label>
                <div class="col-xs-10">
                    <select data-role="multiselect"
                           data-value-primitive="true"
                           data-text-field="text"
                           data-value-field="value"
                           data-option-label="@All@"
                            data-bind="value: status, source: statusDataSource"></select>
                </div>
            </div>
        </div>
        <div class="row form-horizontal">
            <div class="form-group col-sm-4">
                <label class="control-label col-xs-4">@Source@</label>
                <div class="col-xs-8">
                    <select data-role="multiselect"
                            data-value-primitive="true"
                            data-text-field="text"
                            data-value-field="value"
                            data-option-label="@All@"
                            data-bind="value: source, source: sourceDataSource"></select>
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
                     data-pageable="{
                        refresh: true,
                        pageSize: 15
                     }"
                     data-scrollable="true"
                     data-auto-bind="true"
                     data-filterable="{
                        extra: false
                     }"
                     data-columns="[
				            	{
				            	    field: 'ticket_id',
				            	    title: 'Profile No.',
				            	    template: data => gridName(data.ticket_id),
				            	    filterable: false
                                },
				            	{
				            	    field: 'title',
				            	    title: '@Title@',
				            	    filterable: {
				            	        operators: {
				            	            string: {
				            	                contains: '@Contains@'
				            	            }
				            	        }
				            	    }
                                },
				            	{
				            	    field: 'source',
				            	    title: '@Source@',
				            	    filterable: {
				            	        operators: {
				            	            string: {
				            	                eq: '@Equal@',
				            	                isnull: '@Empty@'
				            	            }
				            	        }
				            	    }
                                },
				            	{
				            	    field: 'serviceLv1',
				            	    title: 'Level 1',
				            	    filterable: {
				            	        operators: {
				            	            string: {
				            	                contains: '@Contains@',
				            	                isnull: '@Empty@'
				            	            }
				            	        }
				            	    }
                                },
				            	{
				            	    field: 'serviceLv2',
				            	    title: 'Level 2',
				            	    template: function(dataItem) {
                                        var service = (typeof dataItem.serviceLv2 !== 'undefined') ? dataItem.serviceLv2 : '';
                                        return service;
                                    },
                                    filterable: {
				            	        operators: {
				            	            string: {
				            	                contains: '@Contains@',
				            	                isnull: '@Empty@'
				            	            }
				            	        }
				            	    }
                                },
				            	{
				            	    field: 'serviceLv3',
				            	    title: 'Level 3',
				            	    template: function(dataItem) {
                                        var service = (typeof dataItem.serviceLv3 !== 'undefined') ? dataItem.serviceLv3 : '';
                                        return service;
                                    },
                                    filterable: {
				            	        operators: {
				            	            string: {
				            	                contains: '@Contains@',
				            	                isnull: '@Empty@'
				            	            }
				            	        }
				            	    }
                                },
				            	{
				            	    field: 'assign_view',
                                    title: '@Extension@',
                                    filterable: false
                                },
				            	{
				            	    field: 'assign_agentname_view',
				            	    title: '@Agent name@',
				            	    filterable: false
                                },
                                {
                                    field: 'status',
                                    title: '@Status@',
                                    filterable: false
                                },
                                {
                                    field: 'sender_name',
                                    title: 'Khách hàng'
                                },
                                {
                                    field: 'createdAt',
                                    title: 'Thời gian tạo',
                                    template: data => gridDate(data.createdAt),
                                    filterable: false
                                }
			            	]"
                     data-bind="source: dataReport"></div>
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
                status: [],
                source: [],
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
                    var field = "createdAt";
                    var fromDateTime = new Date(this.fromDateTime.getTime() - timeZoneOffset).toISOString();
                    var toDateTime = new Date(this.toDateTime.getTime() - timeZoneOffset).toISOString();
                    var extensions = this.get("extensions");
                    var status = this.get('status');
                    var source = this.get('source');
                    var serviceLv1 = this.get('serviceLv1');
                    var serviceLv2 = this.get('serviceLv2');
                    var serviceLv3 = this.get('serviceLv3');

                    var filters = [
                        {
                            logic: 'and',
                            filters: [
                                {field: field, operator: "gte", value: fromDateTime},
                                {field: field, operator: "lte", value: toDateTime},
                            ]
                        }
                    ];

                    if(typeof status !== 'undefined' && status !== null && status.length > 0) {
                        filters.push({field: "status", operator: "in", value: status})
                    }

                    if(typeof source !== 'undefined' && source !== null && source.length > 0) {
                        filters.push({field: "source", operator: "in", value: source})
                    }

                    if(serviceLv1) {
                        filters.push({field: "serviceLv1", operator: "eq", value: serviceLv1})
                    }

                    if(serviceLv2) {
                        filters.push({field: "serviceLv2", operator: "eq", value: serviceLv2})
                    }

                    if(serviceLv3) {
                        filters.push({field: "serviceLv3", operator: "eq", value: serviceLv3})
                    }

                    if(extensions.length > 0) {
                        filters.push({field: "assign", operator: "in", value: extensions})
                    }

                    this.dataReport.filter(filters);
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
                        read: ENV.reportApi + "supportcustomer/read",
                        parameterMap: parameterMap
                    },
                    // aggregate: [
                    //     { field: 'AVA', aggregate: 'sum' },
                    //     { field: 'SOC', aggregate: 'sum' },
                    //     { field: 'ACW', aggregate: 'sum' },
                    //     { field: 'SUN', aggregate: 'sum' },
                    //     { field: 'total', aggregate: 'sum' }
                    // ],
                    schema: {
                        data: "data",
                        total: "total",
                        groups: "data",
                        parse: function(response) {
                            response.data.map(doc => {
                                doc.createdAt = new Date(doc.createdAt * 1000);
                                doc.assign_view = doc.assign.join(', ');
                                doc.assign_agentname_view = doc.assign_agentname.join(', ');
                                return doc;
                            });
                            return response;
                        }
                    }
                }),
                callDirection: dataSourceJsonData(["Call", "direction"]),
                statusDataSource: dataSourceJsonData(["Ticket", "status"]),
                sourceDataSource: dataSourceJsonData(["Ticket", "source"]),
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
                    record = (grid.dataSource.page() -1) * grid.dataSource.pageSize();
                },
                mailDirectionDataSource: dataSourceJsonData(["Ticket", "email", "direction"]),
                customGridFilter: {
                    ignoreCase: true,
                    search: true,
                    operators: {
                        string: {
                            contains: "Contains"
                        }
                    }
                },
                sourceTicketFilter: dataSourceJsonData(["Ticket", "source"]),
            })

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
            console.log({column});
            $.each(gridColumn, function(key, value) {
                column.push({field: value.field, title: value.title});
            });
            $.ajax({
                url: ENV.reportApi + "namareport/supportcustomer/exportExcel",
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

        function sourceFilter(tags, field) {
            var dataSource = dataSourceJsonData(tags);
            console.log(dataSource);
        }
    </script>

    <script type="text/x-kendo-template" id="multiSourceFilter">
        <input id="ddlEventDescription"
               data-role="multiselect"
               data-value-primitive="true"
               data-filter="contains"
               data-auto-bind="false"
               data-bind="source: sourceTicketFilter"
               data-text-field="text"
               data-value-field="value" />
    </script>
</div>
<style>
    .k-tabstrip-items .k-item.k-state-active {
        background: #f1f1f1;
    }
</style>
<div id="page-content">
    <!-- Table Styles Header -->
    <ul class="breadcrumb breadcrumb-top">
        <li>@Report@</li>
        <li>Outsoucing Collection Trend</li>
    </ul>
    <!-- END Table Styles Header -->
    <div class="container-fluid mvvm" style="padding-top: 20px; padding-bottom: 10px">
        <div class="row form-horizontal">
            <div class="form-group col-sm-4">
               <label class="control-label col-xs-4">@Year@</label>
               <div class="col-xs-8">
               <input id="year" name="year" data-role="datepicker" data-format="yyyy" data-start="decade" data-depth="decade" data-bind="value: year" />
               </div>
            </div>
            <div class="form-group col-sm-4">
               <label class="control-label col-xs-4">@Partner@</label>
               <div class="col-xs-8">
                   <input id="partner" data-role="combobox"
                        data-placeholder="Select Partner"
                        data-value-primitive="true"
                        data-text-field="name"
                        data-value-field="name"
                        data-bind="value: selectedPartner, source: partners, events: { change: partnerChange }" />
                </div>
            </div>
            <div class="pull-right" style="margin-right:20px">
                <div class="btn-group btn-group-sm">
                    <a role="button" class="btn btn-sm" onclick="saveAsExcel()"><i class="fa fa-file-excel-o"></i> <b>@Export@</b></a>
                </div>
            </div>
        </div>
        <div class="row chart-page" style="background-color: white">
            <h4 class="text-center widget-content"><a href="javascript:void(0)" style="cursor: none;text-decoration: none;"><strong>OUTSOURCING COLLECTION TREND</strong></a></h4>
            <div data-role="tabstrip">
                <ul style="width: 300px; margin: 0 auto;">
                    <li class="k-state-active" style="width: 145px; text-align: center; border: 1px solid thistle; margin: 1px;">
                        Amount
                    </li>
                    <li style="width: 145px; text-align: center; border: 1px solid thistle; margin: 1px;">
                        Assigned DPD
                    </li>
                </ul>
                <div style="border:0">
                  <div class="container-fluid">
                    <div class="col-sm-12">
                        <div id="grid_amount"></div>
                    </div>
                  </div>
                </div>
                <div style="border:0">
                    <div class="container-fluid">
                      <div class="col-sm-12">
                        <div id="grid_assigned_dpd"></div>
                      </div>
                    </div>
                </div>
              </div>
        </div>
    </div>
    <script>
        var Table = function() {
            return {
                dataSourceAmount: {},
                gridAmount: {},
                dataSourceAssigned : {},
                gridAssigned : {},
                columns: [],
                init: function() {
                    var dataSourceAmount = this.dataSourceAmount = new kendo.data.DataSource({
                        serverPaging: true,
                        serverFiltering: true,
                        pageSize: 20,
                        transport: {
                            read: ENV.reportApi + "loan/outsoucing_collection_trend_report/read_amount",
                            parameterMap: parameterMap
                        }                
                    });

                    var dataSourceAssigned = this.dataSourceAssigned = new kendo.data.DataSource({
                        serverPaging: true,
                        serverFiltering: true,
                        pageSize: 20,
                        transport: {
                            read: ENV.reportApi + "loan/outsoucing_collection_trend_report/read_assigned",
                            parameterMap: parameterMap
                        }                
                    });

                    var gridAmount = this.gridAmount = $("#grid_amount").kendoGrid({
                        dataSource: dataSourceAmount,
                        excel: {allPages: true},
                        resizable: true,
                        pageable: true,
                        sortable: true,
                        scrollable: true,
                        columns: this.columns,
                        noRecords: {
                            template: `<h2 class='text-danger'>${KENDO.noRecords}</h2>`
                        }
                    }).data("kendoGrid");

                    var gridAssigned = this.grid = $("#grid_assigned_dpd").kendoGrid({
                        dataSource: dataSourceAssigned,
                        excel: {allPages: true},
                        resizable: true,
                        pageable: true,
                        sortable: true,
                        scrollable: true,
                        columns: [],
                        noRecords: {
                            template: `<h2 class='text-danger'>${KENDO.noRecords}</h2>`
                        }
                    }).data("kendoGrid");

                    
                }
            }
        }();
        window.onload = function() {
            var date = new Date(),
                timeZoneOffset = date.getTimezoneOffset() * kendo.date.MS_PER_MINUTE;
                date.setHours(- timeZoneOffset / kendo.date.MS_PER_HOUR, 0, 0 ,0);
                var fromDate = new Date(date.getYear(), 1, 1, 0, 0, 0);
                var toDate = new Date(date.getTime() + timeZoneOffset + kendo.date.MS_PER_DAY -1)
                Table.init();            
                var observable = kendo.observable({
                    trueVar: true,
                    loading: false,
                    visibleReport: false,
                    visibleNoData: false,
                    fromDateTime: fromDate,
                    toDateTime: toDate,
                    filterField: "",
                    fromDate: kendo.toString(fromDate, "dd/MM/yyyy H:mm"),
                    toDate: kendo.toString(toDate, "dd/MM/yyyy H:mm"),
                    selectedPartner : null,
                    partners : new kendo.data.DataSource({
                        transport: {
                            read: {
                                url: ENV.reportApi + "loan/outsoucing_collection_trend_report/get_partner"
                            }
                        }
                    }),
                    partnerChange: function(){
                        Table.init();                      
                    },
                    search: function() {
                        this.set("fromDate", kendo.toString(this.get("fromDateTime"), "dd/MM/yyyy H:mm"));
                        this.set("toDate", kendo.toString(this.get("toDateTime"), "dd/MM/yyyy H:mm"));
                        this.asyncSearch();
                    },
                    asyncSearch: async function() {
                        var field = "DT_TX";
                        var fromDateTime = new Date(this.fromDateTime.getTime() - timeZoneOffset).toISOString();
                        var toDateTime = new Date(this.toDateTime.getTime() - timeZoneOffset).toISOString();
                        
                        var filter = {
                            logic: "and",
                            filters: [
                                {field: field, operator: "gte", value: fromDateTime},
                                {field: field, operator: "lte", value: toDateTime}
                            ]
                        };

                    Table.dataSource.filter(filter);
                },
            })
            kendo.bind($(".mvvm"), observable);
        };
    </script>
    <script>
        function mergeGridRows(gridId, colTitle) {
            $('#' + gridId + '>.k-grid-content>table').each(function (index, item) {
                var dimension_col = 1;
                // First, scan first row of headers for the "Dimensions" column.
                $('#' + gridId + '>.k-grid-header>.k-grid-header-wrap>table').find('th').each(function () {
                    if ($(this).text() == colTitle) {

                        // first_instance holds the first instance of identical td
                        var first_instance = null;

                        $(item).find('tr').each(function () {

                            // find the td of the correct column (determined by the colTitle)
                            var dimension_td = $(this).find('td:nth-child(' + dimension_col + ')');

                            if (first_instance == null) {
                                first_instance = dimension_td;
                            } else if (dimension_td.text() == first_instance.text()) {
                                // if current td is identical to the previous
                                // then remove the current td
                                dimension_td.remove();
                                // increment the rowspan attribute of the first instance
                                first_instance.attr('rowspan', typeof first_instance.attr('rowspan') == "undefined" ? 2 : first_instance.attr('rowspan') + 1);
                            } else {
                                // this cell is different from the last
                                first_instance = dimension_td;
                            }
                        });
                        return;
                    }
                    dimension_col++;
                });
            });
        }

        function saveAsExcel() {
            $.ajax({
                url: ENV.reportApi + "loan/Outsoucing_collection_trend_report/exportExcel",
                type: 'POST',
                dataType: 'json',
                data : JSON.stringify({
                    year : $('#year').val(),
                    partner : $('#partner').val()
                }),
                timeout: 30000
            })
            .done(function(response) {
                if (response.status == 1) {
                    window.location = response.data
                }
            })
            .fail(function() {
                console.log("error");
            });
        }
    </script>
</div>
<div id="form-loader" style="display: none;"></div>
<div class="container-fluid">
	<div class="row">
        <div id="side-form" style="width: 50%">
            <div class="form-group">
                <label>@Date@</label>
                <input data-role="datepicker"
                       data-bind="value: item.appointment_date"
                       style="width: 100%"/>
            </div>
            <div class="form-group">
                <label>@Area@</label>
                <input data-role="dropdownlist"
                       data-value-primitive="true"
                       data-text-field="text"
                       data-value-field="value"
                       data-filter="contains"
                       data-bind="value: item.dealer_location, source: locationOption" style="width: 100%"/>
            </div>
            <div class="form-group">
                <label>@Loan counter code@</label>
                <input id="dealer-info" data-role="dropdownlist"
                       data-value-primitive="true"
                       data-text-field="dealer_name"
                       data-value-field="dealer_code"
                       data-filter="contains"
                       data-bind="value: item.dealer_code, source: dealerOption, events: {change: onChangeDealer}" style="width: 100%"/>
            </div>
            <div class="form-group">
                <label>@Counter's Name@</label>
                <input class="k-textbox" style="width: 100%" data-bind="value: item.dealer_name">
            </div>
            <div class="form-group">
                <label>@Counter's Address@</label>
                <input class="k-textbox" style="width: 100%" data-bind="value: item.dealer_address">
            </div>
            <div class="form-group">
                <label>@SC's code@</label>
                <input id="sc-info" data-role="dropdownlist"
                       data-value-primitive="true"
                       data-text-field="sc_name"
                       data-value-field="sc_code"
                       data-filter="contains"
                       data-bind="value: item.sc_code, source: scOption, events: {change: onChangeSC}" style="width: 100%"/>
            </div>
            <div class="form-group">
                <label>@SC's Name@</label>
                <input class="k-textbox" style="width: 100%" data-bind="value: item.sc_name">
            </div>
            <div class="form-group">
                <label>@SC's Mobile Phone@</label>
                <input class="k-textbox" style="width: 100%" data-bind="value: item.sc_phone">
            </div>
        </div>
        <div id="main-form" style="width: 50%">
            <div class="form-group">
                <label>@National ID@</label>
                <input id="customer-info" data-role="dropdownlist"
                       data-value-primitive="true"
                       data-text-field="cmnd"
                       data-value-field="id"
                       data-filter="contains"
                       data-bind="value: item.cmnd, source: customerOption, events:{change: onChangeCMND}" style="width: 100%"/>
            </div>
            <div class="form-group">
                <label>@Customer name@</label>
                <input class="k-textbox" style="width: 100%" data-bind="value: item.cus_name">
            </div>
        </div>
	</div>

	<div class="row side-form-bottom">
		<div class="col-xs-12 text-right">
			<button class="btn btn-sm btn-default" onclick="closeForm()">@Cancel@</button>
			<button class="btn btn-sm btn-primary btn-save" data-bind="click: save">@Save@</button>
		</div>
	</div>
</div>

<script type="text/javascript">
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

    Config.observable = Object.assign(Config.observable, {
        listSCByDateAndDealer: [],
        locationOption: () => dataSourceJsonData(["SC", "Dealer", "location"]),
        dealerOption: function() {
            var location = this.get('item.dealer_location');
            if(location) {
                return new kendo.data.DataSource({
                    pageSize: 5,
                    serverFiltering: true,
                    filter: [{field: 'location', operator: 'eq', value: location}],
                    transport: {
                        read: {
                            url: ENV.restApi + "Dealer",
                        },
                        parameterMap: parameterMap
                    },
                    schema: {
                        data: "data",
                        total: "total"
                    },
                });
            }
            else {
                return [];
            }
        },
        onChangeDealer: function() {
            var temp = [];
            var dealerDropDown = $("#dealer-info").data("kendoDropDownList");
            var dataItem = dealerDropDown.dataItem();
            this.set('item.dealer_name', dataItem.dealer_name);
            this.set('item.dealer_address', dataItem.address);
            var dealer_code = this.get('item.dealer_code');
            var appointment_date = this.get('item.appointment_date');
            if(dealer_code && appointment_date) {
                var listDealerCode = $.get(`${ENV.restApi}Sc_schedule`, {
                    q: JSON.stringify({
                        filter: {
                            logic: 'and',
                            filters: [
                                {field: 'dealer_code', operator: 'eq', value: dealer_code},
                                {field: 'from_date', operator: 'eq', value: (appointment_date.getTime() / 1000)}
                            ]
                        }
                    })
                }, function (response) {
                    response.data.forEach(doc => {
                        temp.push(...doc.sc_code);
                    });
                    var scOption = new kendo.data.DataSource({
                        pageSize: 5,
                        serverFiltering: true,
                        filter: [
                            {field: 'sc_code', operator: 'in', value: temp},
                        ],
                        transport: {
                            read: {
                                url: ENV.restApi + "Sc",
                            },
                            parameterMap: parameterMap
                        },
                        schema: {
                            data: "data",
                            total: "total"
                        },
                    });
                    $("#sc-info").data('kendoDropDownList').setDataSource(scOption);
                });
            }
        },
        onChangeSC: function() {
            var dataItem = $("#sc-info").data('kendoDropDownList').dataItem();
            this.set('item.sc_phone', dataItem.phone);
            this.set('item.sc_name', dataItem.sc_name);
        },
        customerOption: new kendo.data.DataSource({
            pageSize: 5,
            serverFiltering: true,
            transport: {
                read: {
                    url: ENV.restApi + "Customer",
                },
                parameterMap: parameterMap
            },
            schema: {
                data: "data",
                total: "total"
            },
        }),
        onChangeCMND: function() {
            var customerDropDown = $("#customer-info").data("kendoDropDownList");
            var dataItem = customerDropDown.dataItem();
            this.set('item.cus_name', dataItem.name);
        },
        // save: function () {
        //     $.ajax({
        //         url: ENV.vApi + "appointment_log/create",
        //         type: "POST",
        //         data: JSON.stringify(this.item),
        //         contentType: "application/json; charset=utf-8",
        //         dataType: 'JSON',
        //         success: (response) => {
        //             if(response.status == 1) {
        //                 notificationAfterRefresh("@Create@ @appointment@ @success@", "success");
        //                 location.reload();
        //             }
        //             else{
        //                 notification.show("@Create@ @appointment@ @fail@", 'error')
        //             }
        //         },
        //         error: errorDataSource
        //     });
        // }
    });
    // kendo.bind("#right-form", kendo.observable(appointmentObservable));
    $(document).ready(function() {
        $("body").tooltip({ selector: '[data-toggle=tooltip]' });
    });
</script>

<style>
    #pnr-list {
        border-collapse: collapse;
    }

    #pnr-list td {
        padding: 2px;
    }

    .tooltip-inner {
        max-width: 100% !important;
    }

    #pnr-list tr:nth-child(odd){
        background-color: #C2BAB5;
    }
</style>

<script type="text/x-kendo-template" id="pnr-template">
    #if(pnr_state === 'success') {#
    <li class="pnr-info-detail" style="margin-left: 7px; display:inline;">
        <a data-html="true" href="javascript:void(0)" class="label label-success" data-bind="text: pnr_code, click: openPNRDetail" data-toggle="tooltip" data-placement="top" title="#: pnr_info #"></a><i class="fa fa-times" style="color: \#dd2200" aria-hidden="true" style="cursor: pointer" data-bind="click: deletePNRFromList"></i>&nbsp;
    </li>
    #} else {#
    <li style="margin-left: 7px; display:inline;">
        <a href="javascript:void(0)" class="label label-danger" data-bind="text: pnr_code"></a><i class="fa fa-times" style="color: \#dd2200" aria-hidden="true" style="cursor: pointer" data-bind="click: deletePNRFromList"></i>&nbsp;
    </li>
    #}#
</script>

<script type="text/x-kendo-template" id="contact-person-info-template">
    <li><i class="fa fa-times" style="color: \#dd2200" aria-hidden="true" style="cursor: pointer" data-bind="click: deleteContactFromList"></i><i class="fa fa-user" aria-hidden="true"></i> <span class="label label-info" data-bind="text: name"></span><span class="pull-right" data-bind="text: phone"></span></br><span data-bind="text: email"></span></li>
</script>
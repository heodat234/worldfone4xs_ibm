<div id="form-loader" style="display: none;"></div>
<div class="container-fluid">
	<div class="row">
        <div id="side-form" style="width: 50%">
            <div class="form-group">
                <label>@Date@</label>
                <input data-role="datepicker"
                       data-format="dd/MM/yyyy"
                       data-bind="value: item.appointment_date"
                       style="width: 100%"
                       required validationMessage="Empty!!!"/>
            </div>
            <div class="form-group">
                <label>@Area@</label>
                <input id="area" data-role="dropdownlist"
                       data-value-primitive="false"
                       data-filter="contains"
                       data-text-field="location"
                       data-value-field="location"
                       data-bind="value: item.dealer_location, source: locationOption" style="width: 100%"
                       required validationMessage="Empty!!!"/>
            </div>
            <div class="form-group">
                <label>@Loan counter code@</label>
                <input id="dealer-info" data-role="dropdownlist"
                       data-value-primitive="true"
                       data-text-field="dealer_name"
                       data-value-field="dealer_code"
                       data-filter="contains"
                       data-bind="value: item.dealer_code, source: dealerOption, events: {change: onChangeDealer, dataBound: onDataBoundDealer}" style="width: 100%"
                       required validationMessage="Empty!!!"/>
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
                       data-text-field="sc_code"
                       data-value-field="sc_code"
                       data-filter="contains"
                       data-bind="value: item.sc_code, source: scOption, events: {change: onChangeSC, dataBound: onDataBoundSc}" style="width: 100%"/>
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
                       data-text-field="id_no"
                       data-value-field="id"
                       data-filter="contains"
                       data-bind="value: item.id_no, source: customerOption, events:{change: onChangeCMND, dataBound: onDataBoundCMND}" style="width: 100%"
                       required validationMessage="Empty!!!"/>
            </div>
            <div class="form-group">
                <label>@Customer name@</label>
                <input class="k-textbox" style="width: 100%" data-bind="value: item.name">
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
    var popupOption = <?= !empty($doc) ? json_encode($doc) : '{}' ?>;
    if(popupOption) {
        popupOption.name = popupOption.name;
    }
    appointmentObservable = {
        listScBySchedule: [],
        getSCBySchedule: function() {
            var self = this;
            var temp = [];
            var dealer_code = this.get('item.dealer_code');
            var sc_code = this.get('item.sc_code');
            var appointment_date = this.get('item.appointment_date');
            if(typeof appointment_date == 'string') {
                appointment_date_raw = appointment_date.split('/');
                appointment_date = new Date(appointment_date_raw[2], appointment_date_raw[1] - 1, appointment_date_raw[0]);
            }
            if(dealer_code && appointment_date) {
                var listDealerCode = $.get(`${ENV.restApi}sc_schedule`, {
                    q: JSON.stringify({
                        filter: {
                            logic: 'and',
                            filters: [
                                {field: 'dealer_code', operator: 'eq', value: dealer_code},
                                {field: 'from_date', operator: 'eq', value: appointment_date.toIsoLocalString()},
                            ]
                        }
                    })
                }, function (response) {
                    response.data.forEach(doc => {
                        temp.push(...doc.sc_code);
                    });
                    self.set('listScBySchedule', temp);
                });
            }
        },
        item: popupOption,
        dealerDataSource: new kendo.data.DataSource({
            transport: {
                read: {
                    url: ENV.restApi + "dealer",
                },
                parameterMap: parameterMap
            },
            schema: {
                data: "data",
                total: "total"
            },
        }),
        listSCByDateAndDealer: [],
        locationOption: () => dataSourceDistinct('Dealer', 'location'),
        dealerOption: function() {
            var location = this.get('item.dealer_location');
            if(location) {
                return new kendo.data.DataSource({
                    pageSize: 5,
                    serverFiltering: true,
                    filter: [{field: 'location', operator: 'eq', value: (location.location) ? location.location : location}],
                    transport: {
                        read: {
                            url: ENV.restApi + "dealer",
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
        scOption: function() {
            if(this.get('listScBySchedule')) {
                return new kendo.data.DataSource({
                    pageSize: 5,
                    serverFiltering: true,
                    filter: [
                        {field: 'sc_code', operator: 'in', value: this.get('listScBySchedule')},
                    ],
                    transport: {
                        read: {
                            url: ENV.restApi + "sc",
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
                return []
            }
        },
        onDataBoundSc: function() {
            console.log(this.get("item.sc_code"));
            if(this.get("item.sc_code")) {
                
                $("#sc-info").data("kendoDropDownList").value(this.get("item.sc_code"));
            }  
        },
        onChangeDealer: function() {
            var dealerDropDown = $("#dealer-info").data("kendoDropDownList");
            var dataItem = dealerDropDown.dataItem();
            if(typeof dataItem != 'undefined') {
                this.set('item.dealer_name', (dataItem.dealer_name) ? dataItem.dealer_name : '');
                this.set('item.dealer_address', (dataItem.address) ? dataItem.address : '');
            }
            this.getSCBySchedule();
        },
        onDataBoundDealer: function() {
            var dealer_code = this.get('item.dealer_code');
            var sc_code = this.get('item.sc_code');
            if(dealer_code && sc_code) {
                this.getSCBySchedule();
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
                    url: ENV.vApi + "telesalelist/read",
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
            this.set('item.name', dataItem.name);
        },
        save: function() {
            var item = this.get('item');
            var appointment_date = new Date(this.item.appointment_date);
            appointment_date.setHours(0, 0, 0, 0);
            item.appointment_date = appointment_date.getTime() / 1000;
            $.ajax({
                url: ENV.vApi + "appointment_log_solve/create",
                data: kendo.stringify(item.toJSON()),
                error: errorDataSource,
                contentType: "application/json; charset=utf-8",
                type: "PUT",
                success: function() {
                    closeForm();
                    Table.dataSource.sync().then(() => {Table.dataSource.read()});
                }
            });
        },
        onDataBoundCMND: function(e) {
            if(this.get('item.id_no')) {
                var cmnd = this.get('item.id_no');
                var cmndDD = $("#customer-info").data("kendoDropDownList");
                cmndDD.text(cmnd);
            }
        }
    };
    kendo.bind("#right-form", kendo.observable(appointmentObservable));
    $(document).ready(function() {
        $("body").tooltip({ selector: '[data-toggle=tooltip]' });
    });
    function convertDateToUTC(date) { return new Date(date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate(), date.getUTCHours(), date.getUTCMinutes(), date.getUTCSeconds()); }
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
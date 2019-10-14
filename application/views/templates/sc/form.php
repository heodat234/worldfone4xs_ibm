<div id="form-loader" style="display: none;"></div>
<div class="container-fluid">
	<div class="row">
        <div id="main-form" style="width: 100%">
            <div class="form-group">
                <label>@Code@</label>
                <input class="k-textbox" style="width: 100%" data-bind="value: item.sc_code">
            </div>
            <div class="form-group">
                <label>@Name@</label>
                <input class="k-textbox" style="width: 100%" data-bind="value: item.sc_name">
            </div>
            <div class="form-group">
                <label>@Phone@</label>
                <input class="k-textbox" style="width: 100%" data-bind="value: item.phone">
            </div>
            <div class="form-group">
                <label>@email@</label>
                <input class="k-textbox" style="width: 100%" data-bind="value: item.email">
            </div>
            <div class="form-group">
                <label>@Location@</label>
                <input data-role="dropdownlist"
                       data-value-primitive="true"
                       data-text-field="text"
                       data-value-field="value"
                       data-filter="contains"
                       data-bind="value: item.location, source: locationOption" style="width: 100%"/>
            </div>
        </div>
	</div>
	<div class="row side-form-bottom">
		<div class="col-xs-12 text-right">
			<button class="btn btn-sm btn-default" onclick="closeForm()">@Cancel@</button>
			<button class="btn btn-sm btn-primary btn-save" onclick="closeForm()" data-bind="click: save">@Save@</button>
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
        locationOption: () => dataSourceJsonData(["SC", "Dealer", "location"]),
    });

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
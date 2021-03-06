<div class="container-fluid">
    <div class="row">
        <div class="col-xs-12" id="main-form">
            <div class="form-group">
                <label>
                    <input class="custom-checkbox" type="checkbox" data-bind="checked: item.active">
                    <span></span>
                    <span>@Active@</span>
                </label>
            </div>
            <div class="form-group">
                <div class="row">
                    <div class="col-md-3">
                        <label>Type:</label>
                        <input data-role="dropdownlist" name="debt_type" data-value-primitive="true"
                            data-bind="value: item.debt_type, source: item.debtTypeOptions , enabled: item.active, events:{ select: item.debtTypeSelect}"
                            style="width: 100%">
                    </div>
                    <div class="col-md-3">
                        <label>Group:</label>
                        <input data-role="dropdownlist" name="debt_group" data-value-primitive="true"
                            data-bind="value: item.debt_group, source: item.debtGroupOptions , enabled: item.group_active, events:{ select: item.debtGroupSelect}"
                            style="width: 100%">
                    </div>
                    <div class="col-md-3" data-bind="visible: item.show_team_leader_name">
                        <label>Leader Name:</label>
                        <input class="k-textbox" style="width: 100%" data-bind="value: item.team_leader_name ">
                    </div>
                    <div class="col-md-3" data-bind="visible: item.show_team_leader_name">
                        <label>DueDate:</label>
                        <input data-role="dropdownlist" name="duedate_type" data-value-primitive="true"
                            data-bind="value: item.duedate_type, source: item.duedateTypeOptions" style="width: 100%">
                    </div>
                    <div class="col-md-3" data-bind="visible: item.show_B_plus_duedate_type">
                        <label>DueDate:</label>
                        <input data-role="dropdownlist" name="B_plus_duedate_type" data-value-primitive="true"
                            data-bind="value: item.B_plus_duedate_type, source: item.B_plus_duedateTypeOptions"
                            style="width: 100%">
                    </div>
                    <button id="trigger_update_item_name"
                        data-bind="visible:false, events:{click: item.updateItemName}">.</button>
                </div>
            </div>
            <div class="form-group">
                <input class="k-textbox" style="width: 100%" data-bind="value: item.name, enabled: false">
            </div>
            <div class="form-group">
                <label>@Lead@</label>
                <input data-role="dropdownlist" name="lead" data-filter="contains" data-text-field="extension"
                    data-value-field="extension" data-template="itemGroupTemplate"
                    data-value-template="tagGroupTemplate" data-value-primitive="true"
                    data-bind="value: item.lead, source: leadOption, enabled: item.active" style="width: 100%">
            </div>
            <div class="form-group">
                <label>@Members@</label>
                <select data-role="multiselect" name="members" multiple="multiple" data-text-field="extension"
                    data-value-field="extension" data-item-template="itemGroupTemplate"
                    data-tag-template="tagGroupTemplate" data-value-primitive="true" data-clear-button="false"
                    data-bind="value: item.members, source: membersOption, enabled: item.active, events: {select: membersCustomSelect, deselect: membersCustomDeselect}"
                    style="width: 100%">
                </select>
            </div>
            <div class="form-group">
                <label>@Link@ @to@ queue</label>
                <div data-template="queue-template" data-bind="source: item.linkToQueues"></div>
            </div>
        </div>
    </div>
    <div class="row side-form-bottom">
        <div class="col-xs-12 text-right">
            <button class="btn btn-sm btn-default" data-bind="click: cancel">@Cancel@</button>
            <button class="btn btn-sm btn-primary btn-save" data-bind="click: save">@Save@</button>
        </div>
    </div>
</div>
<script id="queue-template" type="text/x-kendo-template">
    <span class="label label-info" data-bind="text: this"></span>
</script>

<script>
    setInterval(()=>{
        $('#trigger_update_item_name').click();
    }, 1000)
</script>
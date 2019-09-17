<div class="col-sm-5">
    <label class="control-label col-xs-4" style="line-height: 2;">@From date@</label>
    <div class="col-xs-8">
        <input id="start-date" data-role="datetimepicker" data-format="dd/MM/yyyy H:mm:ss" name="fromDateTime" data-bind="value: fromDateTime, events: {change: startDateChange}">
    </div>
</div>
<div class="col-sm-5">
    <label class="control-label col-xs-4" style="line-height: 2;">@To date@</label>
    <div class="col-xs-8">
        <input id="end-date" data-role="datetimepicker" data-format="dd/MM/yyyy H:mm:ss" name="toDateTime" data-bind="value: toDateTime, events: {change: endDateChange}">
    </div>
</div>
<div class="col-sm-2 text-center">
    <button data-role="button" data-bind="click: search">@Search@</button>
</div>
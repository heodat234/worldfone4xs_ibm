<div class="col-sm-4">
	<div class="form-group">
        <label class="control-label col-xs-4">@Promised amount@</label>
        <div class="col-xs-8">
			<input class="k-textbox" name="promised_amount" data-bind="value: item.promised_amount" style="width: 100%">
		</div>
	</div>
	<div class="form-group">
        <label class="control-label col-xs-4">@Reason for nonpayment@</label>
        <div class="col-xs-8">
			<input class="k-textbox" name="reason_nonpayment" data-bind="value: item.reason_nonpayment" style="width: 100%">
		</div>
	</div>
</div>
<div class="col-sm-4">
	<div class="form-group">
        <label class="control-label col-xs-4">@Promised person@</label>
        <div class="col-xs-8">
			<input class="k-textbox" name="promised_person" data-bind="value: item.promised_person" style="width: 100%">
		</div>
	</div>
	<div class="form-group">
        <label class="control-label col-xs-4">@Promised date@</label>
        <div class="col-xs-8">
			<input data-role="datepicker" data-format="dd/MM/yy" name="promised_date" data-bind="value: item.promised_date" style="width: 100%">
		</div>
	</div>
</div>
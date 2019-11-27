<div class="col-sm-4">
	<div class="form-group">
        <label class="control-label col-xs-4">@Payment amount@</label>
        <div class="col-xs-8">
			<input class="k-textbox" name="payment_amount" data-bind="value: item.payment_amount" style="width: 100%">
		</div>
	</div>
	<div class="form-group">
        <label class="control-label col-xs-4">@Payment date@</label>
        <div class="col-xs-8">
			<input data-role="datepicker" data-format="dd/MM/yy" name="payment_date" data-bind="value: item.payment_date" style="width: 100%">
		</div>
	</div>
</div>
<div class="col-sm-4">
	<div class="form-group">
        <label class="control-label col-xs-4">@Payment person@</label>
        <div class="col-xs-8">
			<input class="k-textbox" name="payment_person" data-bind="value: item.payment_person" style="width: 100%">
		</div>
	</div>
	<div class="form-group">
        <label class="control-label col-xs-4">@Channel@</label>
        <div class="col-xs-8">
			<input class="k-textbox" name="channel" data-bind="value: item.channel" style="width: 100%">
		</div>
	</div>
</div>
<div class="col-sm-4">
	<div class="form-group">
        <label class="control-label col-xs-4">@Reason for nonpayment@</label>
        <div class="col-xs-8">
			<input class="k-textbox" name="reason_nonpayment" data-bind="value: item.reason_nonpayment" style="width: 100%">
		</div>
	</div>
</div>
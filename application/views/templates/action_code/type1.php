<div class="col-sm-4">
	<div class="form-group">
        <label class="control-label col-xs-4">@Promised amount@</label>
        <div class="col-xs-8">
			<input class="k-textbox" name="promised_amount" data-bind="value: action.promised_amount" style="width: 100%" required>
		</div>
	</div>
	<div class="form-group">
        <label class="control-label col-xs-4">@Reason for nonpayment@</label>
        <div class="col-xs-8">
			<input data-role="dropdownlist" name="reason_nonpayment"
				data-filter="contains"
				data-value-primitive="true"
				data-text-field="text"
				data-value-field="value"                  
				data-bind="value: action.reason_nonpayment, source: nonePaymentOption, events: {change: onChangeReasonNonePayment, dataBound: onChangeReasonNonePayment}" 
				style="width: 100%" required/>
		</div>
	</div>
	<div class="form-group" data-bind="visible: reason_nonpayment_note">
		<label class="control-label col-xs-4">@Reason for nonpayment Note@</label>
		<div class="col-xs-8">
			<input class="k-textbox" name="reason_nonpayment_note" data-bind="value: action.reason_nonpayment_note" style="width: 100%">
		</div>
	</div>
</div>
<div class="col-sm-4">
	<div class="form-group">
        <label class="control-label col-xs-4">@Promised person@</label>
        <div class="col-xs-8">
			<input class="k-textbox" name="promised_person" data-bind="value: action.promised_person" style="width: 100%">
		</div>
	</div>
	<div class="form-group">
        <label class="control-label col-xs-4">@Promised date@</label>
        <div class="col-xs-8">
			<input data-role="datepicker" data-format="dd/MM/yy" name="promised_date" data-bind="value: action.promised_date" style="width: 100%" required>
		</div>
	</div>
</div>
<!-- <div class="col-sm-4" data-bind="visible: reason_nonpayment_note">
	
</div> -->
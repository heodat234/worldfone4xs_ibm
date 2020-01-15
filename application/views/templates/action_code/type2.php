<div class="col-sm-4">
	<div class="form-group">
        <label class="control-label col-xs-4">@Reason for nonpayment@</label>
        <div class="col-xs-8">
			<input data-role="dropdownlist" name="actionCode"
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
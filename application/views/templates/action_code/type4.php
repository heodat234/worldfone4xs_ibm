<div class="col-sm-4">
	<div class="form-group">
        <label class="control-label col-xs-4">@Promised amount@</label>
        <div class="col-xs-8">
			<input class="k-textbox" name="phone" data-bind="value: action.promised_amount" style="width: 100%">
		</div>
	</div>
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
			<input data-role="datepicker" data-format="dd/MM/yy" name="promised_date" data-bind="value: action.promised_date, events: {change: onChangePromiseDate}" style="width: 100%">
		</div>
	</div>
	<div class="form-group">
        <label class="control-label col-xs-4">@Promised person's phone@</label>
        <div class="col-xs-8">
			<input class="k-textbox" name="promised_person_phone" data-bind="value: action.promised_person_phone" style="width: 100%">
		</div>
	</div>
</div>
<div class="col-sm-4">
	<div class="form-group">
        <label class="control-label col-xs-4">@FC Name@</label>
        <div class="col-xs-8">
			<input class="k-textbox" name="fc_name" data-bind="value: action.fc_name" style="width: 100%">
		</div>
	</div>
	<div class="form-group">
        <label class="control-label col-xs-4">@Report date@</label>
        <div class="col-xs-8">
			<input data-role="datepicker" data-format="dd/MM/yy" name="report_date" data-bind="value: action.report_date" style="width: 100%">
		</div>
	</div>
</div>
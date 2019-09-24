<div class="container-fluid">
	<div class="row">
	    <div id="side-form" style="width: 50%">
		<!-- <div id="main-form" style="width: 65%" data-width="65%"> -->
			<div class="form-group" data-field="@Name@">
				<label>@Source@</label>
				<input class="k-textbox" style="width: 100%" data-bind="value: item.Source">
			</div>
			<div class="form-group" data-field="@Phone@">
				<label>@Exporting Date@</label>
				<input data-role="datetimepicker"
                    data-bind="value: item.Exporting_Date" style="width: 100%"/>
			</div>
			<div class="form-group" data-field="@Email@">
				<label>@Contract No.@</label>
				<input class="k-textbox" style="width: 100%" data-bind="value: item.Contract_No">
			</div>
			<div class="form-group" data-field="@Email@">
				<label>@CIF@</label>
				<input class="k-textbox" style="width: 100%" data-bind="value: item.CIF">
			</div>
			<div class="form-group" data-field="@Email@">
				<label>@Customer name@</label>
				<input class="k-textbox" style="width: 100%" data-bind="value: item.Customer_name">
			</div>
			<div class="form-group" data-field="@Email@">
				<label>@Date of birth@</label>
				<input data-role="datetimepicker"
                    data-bind="value: item.Date_of_birth" style="width: 100%"/>
			</div>
			<div class="form-group" data-field="@Email@">
				<label>@ID No@</label>
				<input class="k-textbox" style="width: 100%" data-bind="value: item.ID_No">
			</div>
			<div class="form-group" data-field="@Email@">
				<label>@Mobile Phone No.@</label>
				<input class="k-textbox" style="width: 100%" data-bind="value: item.Mobile_Phone_No">
			</div>
			
		</div>
		<div id="main-form" style="width: 50%" data-width="50%">
			<div class="form-group" data-field="@Email@">
				<label>@Product(MB/CE/PL)@</label>
				<input class="k-textbox" style="width: 100%" data-bind="value: item.Product">
			</div>
			<div class="form-group" data-field="@Email@">
				<label>@Interest Rate(Latest Loan)@</label>
				<input class="k-textbox" style="width: 100%" data-bind="value: item.Interest_Rate">
			</div>
			<div class="form-group" data-field="@Email@">
				<label>@First due date(Latest Loan)@</label>
				<input class="k-textbox" style="width: 100%" data-bind="value: item.First_due_date">
			</div>
			<div class="form-group" data-field="@Email@">
				<label>@Term(Latest Loan)@</label>
				<input class="k-textbox" style="width: 100%" data-bind="value: item.Term">
			</div>
			<div class="form-group" data-field="@Email@">
				<label>@Balance(Latest Loan)@</label>
				<input class="k-textbox" style="width: 100%" data-bind="value: item.Balance">
			</div>
			<div class="form-group" data-field="@Email@">
				<label>@Debt group@</label>
				<input class="k-textbox" style="width: 100%" data-bind="value: item.Debt_group">
			</div>
			<div class="form-group" data-field="@Email@">
				<label>@No. of late(10-29 days)@</label>
				<input class="k-textbox" style="width: 100%" data-bind="value: item.No_of_late_1">
			</div>
			<div class="form-group" data-field="@Name@">
				<label>@No. of late( > 30 days)@</label>
				<input class="k-textbox" style="width: 100%" data-bind="value: item.No_of_late_2">
			</div>
			<div class="form-group" data-field="@Name@">
				<label>@PL-Interest Rate@</label>
				<input class="k-textbox" style="width: 100%" data-bind="value: item.PL_Interest_Rate">
			</div>
			<div class="form-group" data-field="@Name@">
				<label>@Note@</label>
				<input class="k-textbox" style="width: 100%" data-bind="value: item.Note">
			</div>
			<div class="form-group" data-field="@Name@">
				<label>@ownership@</label>
				<input class="k-textbox" style="width: 100%" data-bind="value: item.ownership">
			</div>
			<div class="form-group" data-field="@Name@">
				<label>@date send data@</label>
				<input data-role="datetimepicker"
                    data-bind="value: item.date_send_data" style="width: 100%"/>
			</div>
			<div class="form-group" data-field="@Name@">
				<label>@date receive data@</label>
				<input data-role="datetimepicker"
                    data-bind="value: item.date_receive_data" style="width: 100%"/>
			</div>
			<div class="form-group" data-field="@Name@">
				<label>@code@</label>
				<input class="k-textbox" style="width: 100%" data-bind="value: item.code">
			</div>
			<div class="form-group" data-field="@Name@">
				<label>@Area PL@</label>
				<input class="k-textbox " style="width: 100%" data-bind="value: item.Area_PL">
			</div>
	    </div>
	</div>
	<div class="side-form-bottom">
		<div class="text-right">
			<button class="btn btn-sm btn-default" onclick="closeForm()">@Cancel@</button>
			<button class="btn btn-sm btn-primary btn-save" onclick="closeForm()" data-bind="click: save">@Save@</button>
		</div>
	</div>
</div>
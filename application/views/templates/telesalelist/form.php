<div class="container-fluid change-form">
	<div class="row">
	    <div id="side-form" style="width: 50%">
		<!-- <div id="main-form" style="width: 65%" data-width="65%"> -->
			<div class="form-group" data-field="@Name@">
				<label>@Source@</label>
				<input class="k-textbox" style="width: 100%" data-bind="value: item.source">
			</div>
			<div class="form-group" data-field="@Phone@">
				<label>@Exporting Date@</label>
				<input data-role="datepicker"
                    data-bind="value: item.exporting_date" data-format="dd/MM/yyyy H:mm:ss" style="width: 100%"/>
			</div>
			<div class="form-group" data-field="@Email@">
				<label>@Contract No.(Latest Loan)@</label>
				<input class="k-textbox" style="width: 100%" data-bind="value: item.contract_no">
			</div>
			<div class="form-group" data-field="@Email@">
				<label>@CIF@</label>
				<input class="k-textbox" style="width: 100%" data-bind="value: item.cif">
			</div>
			<div class="form-group" data-field="@Email@">
				<label>@Customer name@</label>
				<input class="k-textbox" style="width: 100%" data-bind="value: item.customer_name">
			</div>
			<div class="form-group" data-field="@Email@">
				<label>@Date of birth@</label>
				<input data-role="datepicker"
                    data-bind="value: item.date_of_birth" data-format="dd/MM/yyyy H:mm:ss" style="width: 100%"/>
			</div>
			<div class="form-group" data-field="@Email@">
				<label>@ID No@</label>
				<input class="k-textbox" style="width: 100%" data-bind="value: item.id_no">
			</div>
			<div class="form-group" data-field="@Email@">
				<label>@Mobile Phone No.@</label>
				<input class="k-textbox" style="width: 100%" data-bind="value: item.mobile_phone_no">
			</div>
			
		</div>
		<div id="main-form" style="width: 50%" data-width="50%">
			<div class="form-group" data-field="@Email@">
				<label>@Product(MB/CE/PL)@</label>
				<input class="k-textbox" style="width: 100%" data-bind="value: item.product">
			</div>
			<div class="form-group" data-field="@Email@">
				<label>@Interest Rate(Latest Loan)@</label>
				<input class="k-textbox" style="width: 100%" data-bind="value: item.interest_rate">
			</div>
			<div class="form-group" data-field="@Email@">
				<label>@First due date(Latest Loan)@</label>
				<input data-role="datepicker"
                    data-bind="value: item.first_due_date" data-format="dd/MM/yyyy H:mm:ss" style="width: 100%"/>
			</div>
			<div class="form-group" data-field="@Email@">
				<label>@Term(Latest Loan)@</label>
				<input class="k-textbox" style="width: 100%" data-bind="value: item.term">
			</div>
			<div class="form-group" data-field="@Email@">
				<label>@Balance(Latest Loan)@</label>
				<input class="k-textbox" style="width: 100%" data-bind="value: item.balance">
			</div>
			<div class="form-group" data-field="@Email@">
				<label>@Debt group@</label>
				<input class="k-textbox" style="width: 100%" data-bind="value: item.debt_group">
			</div>
			<div class="form-group" data-field="@Email@">
				<label>@No. of late(10-29 days)@</label>
				<input class="k-textbox" style="width: 100%" data-bind="value: item.no_of_late_1">
			</div>
			<div class="form-group" data-field="@Name@">
				<label>@No. of late( > 30 days)@</label>
				<input class="k-textbox" style="width: 100%" data-bind="value: item.no_of_late_2">
			</div>
			<div class="form-group" data-field="@Name@">
				<label>@PL-Interest Rate@</label>
				<input class="k-textbox" style="width: 100%" data-bind="value: item.pl_interest_rate">
			</div>
			<div class="form-group" data-field="@Name@">
				<label>@Note@</label>
				<input class="k-textbox" style="width: 100%" data-bind="value: item.note">
			</div>
			<div class="form-group" data-field="@Name@">
				<label>@Assign@</label>
				<input data-role="dropdownlist"
               data-text-field="agentname"
               data-value-field="extension"
                    data-value-primitive="true"
                    data-bind="value: item.assign, source: userListData" style="width: 100%" id="changeAssign">
			</div>
			<div class="form-group" data-field="@Name@">
				<label>@Date send Data@</label>
				<input data-role="datepicker"
                    data-bind="value: item.date_send_data" data-format="dd/MM/yyyy H:mm:ss" style="width: 100%"/>
			</div>
			<div class="form-group" data-field="@Name@">
				<label>@Date receive Data@</label>
				<input data-role="datepicker"
                    data-bind="value: item.date_receive_data" data-format="dd/MM/yyyy H:mm:ss" style="width: 100%"/>
			</div>
			<div class="form-group" data-field="@Name@">
				<label>@Code@</label>
				<input class="k-textbox" style="width: 100%" data-bind="value: item.code">
			</div>
			<div class="form-group" data-field="@Name@">
				<label>@Area PL@</label>
				<input class="k-textbox " style="width: 100%" data-bind="value: item.area_pl">
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
<script>
	var $userListElement = $(".change-form");
    var userListObservable = kendo.observable({
        userListData: new kendo.data.DataSource({
            transport: {
                read: ENV.vApi + "widget/user_list",
                parameterMap: parameterMap
            },
            schema: {
                data: "data",
                total: "total",
               
            }
        })
    });
    kendo.bind($userListElement, userListObservable);
</script>
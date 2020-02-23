ư<div id="all-popup">
    <div id="popup-window" data-role="window"
                     data-title="POPUP LOAN"
                     data-width="1200"
                     data-actions="['Arrows-no-change', 'Save','Tri-state-indeterminate','Refresh', 'Minimize', 'Maximize', 'Close']"
                     data-position="{'top': 20}"
                     data-visible="false"
                     data-bind="events: {open: openPopup, close: closePopup, activate: activatePopup}" style="padding: 2px; max-height: 90vh">
        <div class="container-fluid">
            <div class="row">
                <div id="popup-tabstrip" data-role="tabstrip" style="margin-top: 2px">
                    <ul>
                        <li class="k-state-active">
                            <i class="fa fa-user"></i><b> CUSTOMER INFO</b>
                        </li>
                        <li data-bind="click: openCdr">
                            <i class="fa fa-phone-square"></i><b> CDR</b>
                        </li>
                        <li>
                            <b> PAYMENT HISTORY</b>
                        </li>
                        <li>
                            <b> FIELD ACTION</b>
                        </li>
                        <li>
                            <b> LAWNSUIT</b>
                        </li>
                        <li>
                            <b> CROSS-SELL</b>
                        </li>
                        <div class="pull-right">
                            <span data-bind="text: phone" style="font-size: 18px; vertical-align: -2px" class="text-primary"></span>
                            <a data-role="button" data-bind="click: playRecording, visible: _dataCall.record_file_name" title="Recording" style="vertical-align: 2px">
                                <i class="fa fa-play"></i>
                            </a>
                        </div>
                    </ul>
                    <div>
                        <div class="container-fluid">
                            <div class="row form-horizontal" style="padding-top: 10px">
                                <div class="col-sm-5">
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">@Type of object@</label>
                                        <div class="col-xs-8">
                                            <span style="vertical-align: -7px">Normal</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row form-horizontal">
                                <div class="col-sm-5">
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">@Name@</label>
                                        <div class="col-xs-8">
	                                        <div class="input-group">
						                        <input class="k-textbox upper-case-input" name="name" data-bind="value: item.name, enabled: enableName" style="width: 100%">
						                        <div class="input-group-addon">
						                        	<label style="margin-bottom: 0; cursor: pointer">
						                        		<input type="checkbox" class="hidden" data-bind="checked: enableName">
						                        		<span class="fa fa-pencil"></span>
						                        	</label>
						                        </div>
						                    </div>
					                	</div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">@Birthday@</label>
                                        <div class="col-xs-8">
	                                        <div class="input-group">
						                        <input class="k-textbox" name="date_of_birth" data-bind="value: item.date_of_birth, enabled: enableBirthday" style="width: 100%">
						                        <div class="input-group-addon">
						                        	<label style="margin-bottom: 0; cursor: pointer">
						                        		<input type="checkbox" class="hidden" data-bind="checked: enableBirthday">
						                        		<span class="fa fa-pencil"></span>
						                        	</label>
						                        </div>
						                    </div>
					                	</div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Occupation</label>
                                        <div class="col-xs-8">
	                                        <div class="input-group">
						                        <input class="k-textbox" name="occupation" data-bind="value: item.occupation, enabled: enableOccupation" style="width: 100%">
						                        <div class="input-group-addon">
						                        	<label style="margin-bottom: 0; cursor: pointer">
						                        		<input type="checkbox" class="hidden" data-bind="checked: enableOccupation">
						                        		<span class="fa fa-pencil"></span>
						                        	</label>
						                        </div>
						                    </div>
					                	</div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">@Nation ID@</label>
                                        <div class="col-xs-8">
	                                        <div class="input-group">
						                        <input class="k-textbox" name="nation_id" data-bind="value: item.disbursement_date, enabled: enableNationId" style="width: 100%">
						                        <div class="input-group-addon">
						                        	<label style="margin-bottom: 0; cursor: pointer">
						                        		<input type="checkbox" class="hidden" data-bind="checked: enableNationId">
						                        		<span class="fa fa-pencil"></span>
						                        	</label>
						                        </div>
						                    </div>
					                	</div>
                                    </div>
                                </div>
                                <div class="col-sm-7">
                                	<div class="form-group">
                                        <label class="control-label col-xs-2">@Address@</label>
                                        <div class="col-xs-10">
	                                        <div class="input-group">
						                        <input class="k-textbox" name="address" data-bind="value: item.description, enabled: enableaddress" style="width: 100%">
						                        <div class="input-group-addon">
						                        	<label style="margin-bottom: 0; cursor: pointer">
						                        		<input type="checkbox" class="hidden" data-bind="checked: enableaddress">
						                        		<span class="fa fa-pencil"></span>
						                        	</label>
						                        </div>
						                    </div>
					                	</div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-2">@Main phone@</label>
                                        <div class="col-xs-4">
	                                        <div class="input-group">
						                        <input class="k-textbox" name="main_phone" data-bind="value: item.main_phone, enabled: enablemain_phone" style="width: 100%">
						                        <div class="input-group-addon">
						                        	<label style="margin-bottom: 0; cursor: pointer">
						                        		<input type="checkbox" class="hidden" data-bind="checked: enablemain_phone">
						                        		<span class="fa fa-pencil"></span>
						                        	</label>
						                        </div>
						                    </div>
					                	</div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-2">REF 1</label>
                                        <div class="col-xs-10">
                                        	<input class="k-textbox" name="main_phone" value="Anh trai" style="width: 20%">
                                        	<span>Name</span>
                                        	<input class="k-textbox" name="main_phone" value="Trần Thế Vũ Trường Sơn" style="width: 30%">
                                        	<span>Phone</span>
                                        	<input class="k-textbox" name="main_phone" value="0912122122" style="width: 20%">
					                	</div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-2">REF 2</label>
                                        <div class="col-xs-10">
                                        	<input class="k-textbox" name="main_phone" value="Mẹ" style="width: 20%">
                                        	<span>Name</span>
                                        	<input class="k-textbox" name="main_phone" value="Lê Thị Nhựt Ánh" style="width: 30%">
                                        	<span>Phone</span>
                                        	<input class="k-textbox" name="main_phone" value="0356004937" style="width: 20%">
					                	</div>
                                    </div>
                                </div>
                            </div>
                            <div class="row title-row">
                            	<span class="text-primary">MAIN PRODUCT</span>
	                            <hr class="popup">
                            </div>
                            <div class="row form-horizontal">
                            	<div class="col-sm-4">
                            		<div class="form-group">
                                        <label class="control-label col-xs-4">Contract No.</label>
                                        <div class="col-xs-8">
                                            <input data-role="dropdownlist" name="contractNo"
                                                required validationMessage="Empty!!!"
                                                data-value-primitive="true"
                                                data-text-field="text"
                                                data-value-field="value"                  
                                                data-bind="value: mainProduct.contractNo, source: contractNoOption, events: {cascade: contractNoCascade}" 
                                                style="width: 100%"/>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Product name</label>
                                        <div class="col-xs-8">
                                            <p class="form-control-static" data-bind="text: mainProduct.productName"></p>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Monthly amount</label>
                                        <div class="col-xs-8">
                                        	<p class="form-control-static" data-bind="text: mainProduct.montlyAmount"></p>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Maturity Date</label>
                                        <div class="col-xs-8">
                                        	<p class="form-control-static" data-bind="text: mainProduct.maturityDate"></p>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Last Payment Date</label>
                                        <div class="col-xs-8">
                                        	<p class="form-control-static" data-bind="text: mainProduct.lastPaymentDate"></p>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Debt group</label>
                                        <div class="col-xs-8">
                                        	<p class="form-control-static" data-bind="text: mainProduct.debtGroup"></p>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">First/Last payment default</label>
                                        <div class="col-xs-8">
                                        	<p class="form-control-static" data-bind="text: mainProduct.firstLastPaymentDefault"></p>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Interest rate</label>
                                        <div class="col-xs-8">
                                        	<p class="form-control-static" data-bind="text: mainProduct.interestRate"></p>
                                        </div>
                                    </div>
                            	</div>
                            	<div class="col-sm-4">
                            		<div class="form-group">
                                        <label class="control-label col-xs-4">Due date</label>
                                        <div class="col-xs-8">
                                        	<p class="form-control-static" data-bind="text: mainProduct.dueDate"></p>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Last action code</label>
                                        <div class="col-xs-8">
                                        	<p class="form-control-static" data-bind="text: mainProduct.lastActionCode"></p>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Overdue amount</label>
                                        <div class="col-xs-8">
                                        	<p class="form-control-static" data-bind="text: mainProduct.overdueAmount"></p>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Approved Limit</label>
                                        <div class="col-xs-8">
                                        	<p class="form-control-static" data-bind="text: mainProduct.approvedLimit"></p>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Last payment amount</label>
                                        <div class="col-xs-8">
                                        	<p class="form-control-static" data-bind="text: mainProduct.lastPaymentAmount"></p>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Term</label>
                                        <div class="col-xs-8">
                                        	<p class="form-control-static" data-bind="text: mainProduct.term"></p>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Sales (Code name)</label>
                                        <div class="col-xs-8">
                                        	<p class="form-control-static" data-bind="text: mainProduct.sale"></p>
                                        </div>
                                    </div>
                            	</div>
                            	<div class="col-sm-4">
                            		<div class="form-group">
                                        <label class="control-label col-xs-4">No. of Overdue days</label>
                                        <div class="col-xs-8">
                                        	<p class="form-control-static" data-bind="text: mainProduct.noOverdueDays"></p>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Last action code date</label>
                                        <div class="col-xs-8">
                                        	<p class="form-control-static" data-bind="text: mainProduct.lastActionCodeDate"></p>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Outstanding balance</label>
                                        <div class="col-xs-8">
                                        	<p class="form-control-static" data-bind="text: mainProduct.outstandingBalance"></p>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Advance money</label>
                                        <div class="col-xs-8">
                                        	<p class="form-control-static" data-bind="text: mainProduct.advanceMoney"></p>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Name of store</label>
                                        <div class="col-xs-8">
                                        	<p class="form-control-static" data-bind="text: mainProduct.nameOfStore"></p>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Principal Amount</label>
                                        <div class="col-xs-8">
                                        	<p class="form-control-static" data-bind="text: mainProduct.principalAmount"></p>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Staff in Charge</label>
                                        <div class="col-xs-8">
                                        	<p class="form-control-static" data-bind="text: mainProduct.staffInCharge"></p>
                                        </div>
                                    </div>
                            	</div>
                            </div>
                            <div class="row title-row">
                            	<span class="text-primary">CARD INFORMATION</span>
	                            <hr class="popup">
                            </div>
                            <div class="row form-horizontal">
                            	<div class="col-sm-4">
                            		<div class="form-group">
                                        <label class="control-label col-xs-4">Contract No.</label>
                                        <div class="col-xs-8">
                                            <input data-role="dropdownlist" name="contractNo"
                                                required validationMessage="Empty!!!"
                                                data-value-primitive="true"
                                                data-text-field="text"
                                                data-value-field="value"                  
                                                data-bind="value: card.contractNo, source: contractNoOption" 
                                                style="width: 100%"/>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Product name</label>
                                        <div class="col-xs-8">
                                            <p class="form-control-static" data-bind="text: card.productName"></p>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Monthly amount</label>
                                        <div class="col-xs-8">
                                        	<p class="form-control-static" data-bind="text: card.montlyAmount"></p>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Maturity Date</label>
                                        <div class="col-xs-8">
                                        	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Last Payment Date</label>
                                        <div class="col-xs-8">
                                        	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Debt group</label>
                                        <div class="col-xs-8">
                                        	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">First/Last payment default</label>
                                        <div class="col-xs-8">
                                        	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Interest rate</label>
                                        <div class="col-xs-8">
                                        	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
                                        </div>
                                    </div>
                            	</div>
                            	<div class="col-sm-4">
                            		<div class="form-group">
                                        <label class="control-label col-xs-4">Due date</label>
                                        <div class="col-xs-8">
                                        	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Last action code</label>
                                        <div class="col-xs-8">
                                        	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Overdue amount</label>
                                        <div class="col-xs-8">
                                        	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Approved Limit</label>
                                        <div class="col-xs-8">
                                        	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Last payment amount</label>
                                        <div class="col-xs-8">
                                        	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Term</label>
                                        <div class="col-xs-8">
                                        	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Sales (Code name)</label>
                                        <div class="col-xs-8">
                                        	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
                                        </div>
                                    </div>
                            	</div>
                            	<div class="col-sm-4">
                            		<div class="form-group">
                                        <label class="control-label col-xs-4">No. of Overdue days</label>
                                        <div class="col-xs-8">
                                        	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Last action code date</label>
                                        <div class="col-xs-8">
                                        	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Outstanding balance</label>
                                        <div class="col-xs-8">
                                        	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Advance money</label>
                                        <div class="col-xs-8">
                                        	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Name of store</label>
                                        <div class="col-xs-8">
                                        	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Principal Amount</label>
                                        <div class="col-xs-8">
                                        	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Staff in Charge</label>
                                        <div class="col-xs-8">
                                        	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
                                        </div>
                                    </div>
                            	</div>
                            </div>
                            <div class="row title-row">
                                <span class="text-primary">@CALL RESULT@</span>
                                <hr class="popup">
                            </div>
                            <div class="row form-horizontal">
                            	<div class="col-sm-4">
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">Debt account</label>
                                        <div class="col-xs-8">
                                            <input data-role="dropdownlist" name="debtAccount"
                                                required validationMessage="Empty!!!"
                                                data-value-primitive="true"
                                                data-text-field="text"
                                                data-value-field="value"                  
                                                data-bind="value: call.debtAccount, source: contractNoOption" 
                                                style="width: 100%"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                	<div class="form-group">
                                        <label class="control-label col-xs-4">Action code</label>
                                        <div class="col-xs-8">
                                            <input data-role="dropdownlist" name="actionCode"
                                                required validationMessage="Empty!!!"
                                                data-value-primitive="true"
                                                data-text-field="text"
                                                data-value-field="value"                  
                                                data-bind="value: call.actionCode, source: actionCodeOption" 
                                                style="width: 100%"/>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4" style="padding-top: 2px">
                                            <input type="checkbox" data-bind="checked: item.followUpChecked">
                                            <span>@ReCall@</span>
                                        </label>
                                        <div class="col-xs-8">
                                            <input data-role="datetimepicker" data-date-input="true" data-format="dd/MM/yyyy H:mm" data-bind="value: followUp.reCall, visible: item.followUpChecked" style="width: 100%">
                                        </div>
                                    </div>
                                    <div class="form-group" data-bind="visible: item.followUpChecked">
                                        <label class="control-label col-xs-4">@Recall reason@</label>
                                        <div class="col-xs-8">
                                            <input class="k-textbox" name="reCallReason" data-bind="value: followUp.reCallReason" style="width: 100%">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                	<div class="form-group">
                                        <label class="control-label col-xs-4">@Note@</label>
                                        <div class="col-xs-8">
                                            <textarea class="k-textbox" name="note" data-bind="value: call.note" style="width: 100%"></textarea> 
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row text-center">
                                <button data-role="button" data-icon="save" data-bind="click: save">@Save@</button>
                            </div>
                        </div>
                    </div>
                    <div style="padding: 0; overflow-x: hidden; overflow-y: hidden; min-height: 100%" id="cdr-content">
                    </div>
                    <div>
                    	<div class="container-fluid">
	                    	<div class="row form-horizontal">
	                        	<div class="col-sm-4">
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Product name</label>
	                                    <div class="col-xs-8">
	                                        <p class="form-control-static" data-bind="text: card.productName"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Monthly amount</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.montlyAmount"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Maturity Date</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Last Payment Date</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Debt group</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">First/Last payment default</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Interest rate</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                        	</div>
	                        	<div class="col-sm-4">
	                        		<div class="form-group">
	                                    <label class="control-label col-xs-4">Due date</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Last action code</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Overdue amount</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Approved Limit</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Last payment amount</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Term</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Sales (Code name)</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                        	</div>
	                        	<div class="col-sm-4">
	                        		<div class="form-group">
	                                    <label class="control-label col-xs-4">No. of Overdue days</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Last action code date</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Outstanding balance</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Advance money</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Name of store</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Principal Amount</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Staff in Charge</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                        	</div>
	                        </div>
                    	</div>
                    </div>
                    <div>
                    	<div class="container-fluid">
	                    	<div class="row form-horizontal">
	                        	<div class="col-sm-4">
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Product name</label>
	                                    <div class="col-xs-8">
	                                        <p class="form-control-static" data-bind="text: card.productName"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Monthly amount</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.montlyAmount"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Maturity Date</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Last Payment Date</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Debt group</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">First/Last payment default</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Interest rate</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                        	</div>
	                        	<div class="col-sm-4">
	                        		<div class="form-group">
	                                    <label class="control-label col-xs-4">Due date</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Last action code</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Overdue amount</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Approved Limit</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Last payment amount</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Term</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Sales (Code name)</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                        	</div>
	                        	<div class="col-sm-4">
	                        		<div class="form-group">
	                                    <label class="control-label col-xs-4">No. of Overdue days</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Last action code date</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Outstanding balance</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Advance money</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Name of store</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Principal Amount</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Staff in Charge</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                        	</div>
	                        </div>
                    	</div>
                    </div>
                    <div>
                    	<div class="container-fluid">
	                    	<div class="row form-horizontal">
	                        	<div class="col-sm-4">
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Product name</label>
	                                    <div class="col-xs-8">
	                                        <p class="form-control-static" data-bind="text: card.productName"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Monthly amount</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.montlyAmount"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Maturity Date</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Last Payment Date</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Debt group</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">First/Last payment default</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Interest rate</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                        	</div>
	                        	<div class="col-sm-4">
	                        		<div class="form-group">
	                                    <label class="control-label col-xs-4">Due date</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Last action code</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Overdue amount</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Approved Limit</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Last payment amount</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Term</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Sales (Code name)</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                        	</div>
	                        	<div class="col-sm-4">
	                        		<div class="form-group">
	                                    <label class="control-label col-xs-4">No. of Overdue days</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Last action code date</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Outstanding balance</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Advance money</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Name of store</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Principal Amount</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Staff in Charge</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                        	</div>
	                        </div>
                    	</div>
                    </div>
                    <div>
                    	<div class="container-fluid">
	                    	<div class="row form-horizontal">
	                        	<div class="col-sm-4">
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Product name</label>
	                                    <div class="col-xs-8">
	                                        <p class="form-control-static" data-bind="text: card.productName"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Monthly amount</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.montlyAmount"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Maturity Date</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Last Payment Date</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Debt group</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">First/Last payment default</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Interest rate</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                        	</div>
	                        	<div class="col-sm-4">
	                        		<div class="form-group">
	                                    <label class="control-label col-xs-4">Due date</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Last action code</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Overdue amount</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Approved Limit</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Last payment amount</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Term</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Sales (Code name)</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                        	</div>
	                        	<div class="col-sm-4">
	                        		<div class="form-group">
	                                    <label class="control-label col-xs-4">No. of Overdue days</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Last action code date</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Outstanding balance</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Advance money</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Name of store</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Principal Amount</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                                <div class="form-group">
	                                    <label class="control-label col-xs-4">Staff in Charge</label>
	                                    <div class="col-xs-8">
	                                    	<p class="form-control-static" data-bind="text: card.maturityDate"></p>
	                                    </div>
	                                </div>
	                        	</div>
	                        </div>
                    	</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
class diallistPopup1 extends Popup {
    constructor(dataCall) {
        super(dataCall);
        Object.assign(this, {
            _fieldId : "dialid",
            _popupType: "default",
            phone: dataCall.customernumber,
            openDetail: function(e) {
            	var $content = $("#customer-detail-content");
            	if(!$content.find("iframe").length)
            		$content.append(`<iframe src="${this.detailUrl}"" style="width: 100%; height: 70vh; border: 0"></iframe>`);
            },
        });
        return this;
    }

    async init(fieldId) {
    	var fieldIdValue = this._dataCall[this._fieldId];
        /* Lấy dữ liệu */
        var responseObj = await $.get(ENV.restApi + `diallist_detail/${fieldIdValue}`);

        if(!responseObj) {
            responseObj = {};
            notification.show("Data is not found", "error");
            return;
        }

        this.item = responseObj;
        /* Lấy iframe chi tiết khách hàng */
        var phone = responseObj.phone;
        var detailUrl = "";
        $.get(ENV.vApi + `popup/get_customer_by_phone?_=${Date.now()}&phone=${phone}`).then(res => {
            if(res.total == 1) {
                detailUrl = `${ENV.baseUrl}manage/customer?omc=1#/detail/${res.data[0].id}` 
            }
            this.assign({detailUrl: detailUrl}).open();
        }, (err) => {
            this.assign({detailUrl: detailUrl}).open();
        })
    }
}

var callData = <?= json_encode($callData) ?>;

window.popupObservable = new diallistPopup1(callData);
window.popupObservable.assign({
	followUp: {},
	mainProduct: {},
	card: {},
	call: {},
    actionCodeOption: dataSourceJsonData(["Call", "result"]),
    contractNoOption: [
    	{text: "000018112052", value: 1},
    	{text: "000019142012", value: 2},
    ],
    contractNoCascade: function(e) {
    	this.set("mainProduct", Object.assign(this.get("mainProduct"), {
    		contractNo: 1,
    		productName: "Exciter",
    		montlyAmount: "1,220,000",
    		maturityDate: "20/11/2019",
    		lastPaymentDate: "21/10/2019",
    		lastPaymentAmount: "1,220,000",
    		debtGroup: "G1",
    		firstLastPaymentDefault: "2,540,000",
    		interestRate: "15%",
    		dueDate: "21/11/2019",
    		lastActionCode: "PTP",
    		overdueAmount: "0",
    		approvedLimit: "1,200,000",
    		term: "25/11/2019",
    		sale: "S0101221",
    		noOverdueDays: 1,
    		lastActionCodeDate: "05/11/2019",
    		outstandingBalance: "10,000",
    		staffInCharge: "999"
    	}));
    },
    playRecording: function(e) {
        play(this._dataCall.calluuid);
    },
    save: function() {
        var data = this.item.toJSON();
        $.ajax({
            url: ENV.restApi + "diallist_detail/" + (data.id || "").toString(),
            type: "PUT",
            contentType: "application/json; charset=utf-8",
            data: kendo.stringify(data),
            success: (response) => {
                if(response.status)
                    syncDataSource();
            },
            error: errorDataSource
        })
    },
    openCdr: function(e) {
        var filter = JSON.stringify({
            logic: "and",
            filters: [
                {field: "customernumber", operator: "eq", value: this.phone}
            ]
        });
        var query = httpBuildQuery({filter: filter, omc: 1});
        var $content = $("#cdr-content");
        if(!$content.find("iframe").length)
            $content.append(`<iframe src='${ENV.baseUrl}manage/cdr?${query}' style="width: 100%; height: 500px; border: 0"></iframe>`);
    }
})
window.popupObservable.init();
</script>

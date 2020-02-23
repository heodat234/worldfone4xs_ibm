<div id="all-popup">
    <div id="popup-window" data-role="window"
                     data-title="POPUP LOAN (Default call)"
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
                            <i class="fa fa-user"></i><b> @CUSTOMER INFO@</b>
                        </li>
                        <li data-bind="click: openNotes">
                            <i class="fa fa-sticky-note"></i><b> @Note@</b>
                        </li>
                        <li data-bind="click: openPaymentHistory">
                            <b> @PAYMENT HISTORY@</b>
                        </li>
                        <li data-bind="click: openFieldAction">
                            <b> @FIELD ACTION@</b>
                        </li>
                        <li data-bind="click: openLawSuit">
                            <b> @LAWSUIT@</b>
                        </li>
                        <li data-bind="click: openCrossSell">
                            <b> @CROSS-SELL@</b>
                        </li>
                        <li data-bind="click: openCdr">
                            <i class="fa fa-phone-square"></i><b> @CDR@</b>
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
                                            <span style="vertical-align: -7px" data-bind="text: item.cus_name"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">@Birthday@</label>
                                        <div class="col-xs-8">
                                            <span style="vertical-align: -7px" data-bind="text: item.BIR_DT8"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">@Occupation@</label>
                                        <div class="col-xs-8">
                                            <span style="vertical-align: -7px" data-bind="text: item.WRK_PST"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">@Profession@</label>
                                        <div class="col-xs-8">
                                            <input data-role="dropdownlist" name="actionCode"
                                                data-filter="contains"
                                                data-value-primitive="true"
                                                data-text-field="text"
                                                data-value-field="value"                  
                                                data-bind="value: item.profession, source: professionOption" 
                                                style="width: 100%"/>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">@Nation ID@</label>
                                        <div class="col-xs-8">
                                            <span style="vertical-align: -7px" data-bind="text: item.LIC_NO"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-7">
                                    <div class="form-group">
                                        <label class="control-label col-xs-2">@Address@</label>
                                        <div class="col-xs-10">
                                            <span style="vertical-align: -7px" data-bind="text: item.address"></span>
                                        </div>
                                    </div>
                                    <div class="form-group" data-bind="visible: item.permanent_address">
                                        <label class="control-label col-xs-2">@Permanent Address@</label>
                                        <div class="col-xs-10">
                                            <span style="vertical-align: -7px" data-bind="text: item.permanent_address"></span>
                                        </div>
                                    </div>
                                    <div class="form-group" data-bind="visible: item.temp_address">
                                        <label class="control-label col-xs-2">@Temporary Address@</label>
                                        <div class="col-xs-10">
                                            <span style="vertical-align: -7px" data-bind="text: item.temp_address"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-2">@Main phone@</label>
                                        <div class="col-xs-2">
                                            <span style="vertical-align: -7px" data-bind="visible: item.phone">
                                                <i data-bind="text: item.phone"></i>
                                                <a href="javascript:void(0)" data-bind="click: callThisPhone, attr: {data-phone: item.phone}"><i class="fa fa-phone-square text-info"></i></a>
                                            </span>
                                        </div>
                                        <label class="control-label col-xs-2">@House@</label>
                                        <div class="col-xs-2">
                                            <span style="vertical-align: -7px" data-bind="visible: item.House_NO">
                                                <i data-bind="text: item.House_NO"></i>
                                                <a href="javascript:void(0)" data-bind="click: callThisPhone, attr: {data-phone: item.House_NO}"><i class="fa fa-phone-square text-info"></i></a>
                                            </span>
                                        </div>
                                        <label class="control-label col-xs-2">@Office@</label>
                                        <div class="col-xs-2">
                                            <span style="vertical-align: -7px" data-bind="visible: item.OFFICE_NO">
                                                <i data-bind="text: item.OFFICE_NO"></i>
                                                <a href="javascript:void(0)" data-bind="click: callThisPhone, attr: {data-phone: item.OFFICE_NO}"><i class="fa fa-phone-square text-info"></i></a>
                                            </span>
                                        </div>
                                    </div>
                                    <div data-template="relationship-template" data-bind="source: relationshipDataSource">
                                    </div>
                                </div>
                            </div>
                            <div class="row title-row main-product-container">
                                <span class="text-primary">MAIN PRODUCT</span>
                                <span data-bind="text: mainProductOptionLength"></span>
                                <hr class="popup">
                            </div>
                            <div class="row form-horizontal main-product-container" id="collapseMainProduct">
                                <div class="row">
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label class="control-label col-xs-4">@Contract No.@ <span id="main-product-count"></span></label>
                                            <div class="col-xs-8">
                                                <input data-role="dropdownlist" name="contractNo"
                                                data-value-primitive="true"
                                                data-text-field="account_number"
                                                data-value-field="account_number"                  
                                                data-bind="value: item.account_number, source: mainProductOption, events: {cascade: mainProductChange, dataBound: onDataBoundAccNo}" 
                                                style="width: 100%"/>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-xs-4">@Product name@</label>
                                            <div class="col-xs-8">
                                                <p class="form-control-static" data-bind="text: mainProduct.product_name"></p>
                                            </div>
                                        </div>
                                        <div class="form-group" data-bind="visible: collapseMain">
                                            <label class="control-label col-xs-4">@Monthly amount@</label>
                                            <div class="col-xs-8">
                                                <p class="form-control-static" data-format="n0" data-bind="text: mainProduct.RPY_PRD"></p>
                                            </div>
                                        </div>
                                        <div class="form-group" data-bind="visible: collapseMain">
                                            <label class="control-label col-xs-4">@Maturity Date@</label>
                                            <div class="col-xs-8">
                                                <p class="form-control-static" data-bind="text: mainProduct.DT_MAT"></p>
                                            </div>
                                        </div>
                                        <div class="form-group" data-bind="visible: collapseMain">
                                            <label class="control-label col-xs-4">@Last Payment Date@</label>
                                            <div class="col-xs-8">
                                                <p class="form-control-static" data-bind="text: mainProduct.last_payment_date"></p>
                                            </div>
                                        </div>
                                        <div class="form-group" data-bind="visible: collapseMain">
                                            <label class="control-label col-xs-4">@Debt group@</label>
                                            <div class="col-xs-8">
                                                <p class="form-control-static" data-bind="text: mainProduct.debt_group"></p>
                                            </div>
                                        </div>
                                        <div class="form-group" data-bind="visible: collapseMain">
                                            <label class="control-label col-xs-4">@First/Last payment default@</label>
                                            <div class="col-xs-8">
                                                <p class="form-control-static" data-bind="text: mainProduct.first_last_payment_default"></p>
                                            </div>
                                        </div>
                                        <div class="form-group" data-bind="visible: collapseMain">
                                            <label class="control-label col-xs-4">@Interest rate@</label>
                                            <div class="col-xs-8">
                                                <p class="form-control-static" data-bind="text: mainProduct.interest_rate"></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label class="control-label col-xs-4">@Due date@</label>
                                            <div class="col-xs-8">
                                                <p class="form-control-static" data-bind="text: mainProduct.due_date"></p>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-xs-4">@Last action code@</label>
                                            <div class="col-xs-8">
                                                <p class="form-control-static" data-bind="text: mainProduct.last_action_code"></p>
                                            </div>
                                        </div>
                                        <div class="form-group" data-bind="visible: collapseMain">
                                            <label class="control-label col-xs-4">@Overdue amount@</label>
                                            <div class="col-xs-8">
                                                <p class="form-control-static" data-bind="text: mainProduct.overdue_amount"></p>
                                            </div>
                                        </div>
                                        <div class="form-group" data-bind="visible: collapseMain">
                                            <label class="control-label col-xs-4">@Approved Limit@</label>
                                            <div class="col-xs-8">
                                                <p class="form-control-static" data-bind="text: mainProduct.approved_limit"></p>
                                            </div>
                                        </div>
                                        <div class="form-group" data-bind="visible: collapseMain">
                                            <label class="control-label col-xs-4">@Last payment amount@</label>
                                            <div class="col-xs-8">
                                                <p class="form-control-static" data-bind="text: mainProduct.last_payment_amount"></p>
                                            </div>
                                        </div>
                                        <div class="form-group" data-bind="visible: collapseMain">
                                            <label class="control-label col-xs-4">@Term@</label>
                                            <div class="col-xs-8">
                                                <p class="form-control-static" data-bind="text: mainProduct.term"></p>
                                            </div>
                                        </div>
                                        <div class="form-group" data-bind="visible: collapseMain">
                                            <label class="control-label col-xs-4">@Sales@ (@Code name@)</label>
                                            <div class="col-xs-8">
                                                <p class="form-control-static" data-bind="text: mainProduct.sale_consultant"></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group" >
                                            <label class="control-label col-xs-4">@No. of Overdue days@</label>
                                            <div class="col-xs-8">
                                                <p class="form-control-static" data-bind="text: mainProduct.no_of_overdue_date"></p>
                                            </div>
                                        </div>
                                        <div class="form-group" >
                                            <label class="control-label col-xs-4">@Last action code date@</label>
                                            <div class="col-xs-8">
                                                <p class="form-control-static" data-bind="text: mainProduct.last_action_code_date"></p>
                                            </div>
                                        </div>
                                        <div class="form-group" data-bind="visible: collapseMain">
                                            <label class="control-label col-xs-4">@Outstanding balance@</label>
                                            <div class="col-xs-8">
                                                <p class="form-control-static" data-bind="text: mainProduct.outstanding_balance"></p>
                                            </div>
                                        </div>
                                        <div class="form-group" data-bind="visible: collapseMain">
                                            <label class="control-label col-xs-4">@Advance money@</label>
                                            <div class="col-xs-8">
                                                <p class="form-control-static" data-format="n0" data-bind="text: mainProduct.advance_money"></p>
                                            </div>
                                        </div>
                                        <div class="form-group" data-bind="visible: collapseMain">
                                            <label class="control-label col-xs-4">@Name of store@</label>
                                            <div class="col-xs-8">
                                                <p class="form-control-static" data-bind="text: mainProduct.name_of_store"></p>
                                            </div>
                                        </div>
                                        <div class="form-group" data-bind="visible: collapseMain">
                                            <label class="control-label col-xs-4">@Principal Amount@</label>
                                            <div class="col-xs-8">
                                                <p class="form-control-static" data-bind="text: mainProduct.principal_amount"></p>
                                            </div>
                                        </div>
                                        <div class="form-group" data-bind="visible: collapseMain">
                                            <label class="control-label col-xs-4">@Staff in Charge@</label>
                                            <div class="col-xs-8">
                                                <p class="form-control-static" data-bind="text: mainProduct.staff_in_charge"></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row text-center" style="position: absolute; bottom: 0; left: 50%"> 
                                    <div class="col-sm-12"><a href="#" data-bind="click: collapseMainProduct, text:btnCollapseMain "></a></div>
                                </div>
                            </div>
                            <div class="row title-row card-container">
                                <span class="text-primary">@CARD INFORMATION@</span>
                                <hr class="popup">
                            </div>
                            <div class="row form-horizontal card-container" id="collapseCardProduct">
                                <div class="row">
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label class="control-label col-xs-4">@Contract No.@ <span id="card-count"></span></label>
                                            <div class="col-xs-8">
                                                <input data-role="dropdownlist" name="contract_no"
                                                data-value-primitive="true"
                                                data-text-field="contract_no"
                                                data-value-field="contract_no"                  
                                                data-bind="value: item.account_number, source: cardOption, events: {change: cardChange, dataBound: onDataBoundContractCard}" 
                                                style="width: 100%"/>
                                            </div>
                                        </div>
                                        <div class="form-group" >
                                            <label class="control-label col-xs-4">@Interest Rate@</label>
                                            <div class="col-xs-8">
                                                <p class="form-control-static" data-bind="text: card.interest_rate"></p>
                                            </div>
                                        </div>
                                        <div class="form-group" data-bind="visible: collapseCard">
                                            <label class="control-label col-xs-4">@Approved Limit@</label>
                                            <div class="col-xs-8">
                                                <p class="form-control-static" data-bind="text: card.approved_limit"></p>
                                            </div>
                                        </div>
                                        <div class="form-group" data-bind="visible: collapseCard">
                                            <label class="control-label col-xs-4">@Open Date@ / @First released date@</label>
                                            <div class="col-xs-8">
                                                <p class="form-control-static" data-bind="text: card.first_released_date"></p>
                                            </div>
                                        </div>
                                        <div class="form-group" data-bind="visible: collapseCard">
                                            <label class="control-label col-xs-4">@Last Payment Date@</label>
                                            <div class="col-xs-8">
                                                <p class="form-control-static" data-bind="text: card.last_payment_date"></p>
                                            </div>
                                        </div>
                                        <div class="form-group" data-bind="visible: collapseCard">
                                            <label class="control-label col-xs-4">@Principal Amount@</label>
                                            <div class="col-xs-8">
                                                <p class="form-control-static" data-bind="text: card.principal_amount"></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                       <div class="form-group" >
                                        <label class="control-label col-xs-4">@Due date@</label>
                                        <div class="col-xs-8">
                                            <p class="form-control-static" data-bind="text: card.due_date"></p>
                                        </div>
                                    </div>
                                    <div class="form-group" >
                                        <label class="control-label col-xs-4">@Last action code@</label>
                                        <div class="col-xs-8">
                                            <p class="form-control-static" data-bind="text: card.last_action_code"></p>
                                        </div>
                                    </div>
                                    <div class="form-group" data-bind="visible: collapseCard">
                                        <label class="control-label col-xs-4">@Overdue Amount@</label>
                                        <div class="col-xs-8">
                                            <p class="form-control-static" data-bind="text: card.overdue_amount"></p>
                                        </div>
                                    </div>
                                    <div class="form-group" data-bind="visible: collapseCard">
                                        <label class="control-label col-xs-4">@Expired Date@</label>
                                        <div class="col-xs-8">
                                            <p class="form-control-static" data-bind="text: card.expiry_date"></p>
                                        </div>
                                    </div>
                                    <div class="form-group" data-bind="visible: collapseCard">
                                        <label class="control-label col-xs-4">@Last payment amount@</label>
                                        <div class="col-xs-8">
                                            <p class="form-control-static" data-bind="text: card.last_payment_amount"></p>
                                        </div>
                                    </div>
                                    <div class="form-group" data-bind="visible: collapseCard">
                                        <label class="control-label col-xs-4">@Debt Group@</label>
                                        <div class="col-xs-8">
                                            <p class="form-control-static" data-bind="text: card.debt_group"></p>
                                        </div>
                                    </div>

                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group" >
                                        <label class="control-label col-xs-4">@No of Overdue days@</label>
                                        <div class="col-xs-8">
                                            <p class="form-control-static" data-bind="text: card.no_of_overdue_date"></p>
                                        </div>
                                    </div>
                                    <div class="form-group" >
                                        <label class="control-label col-xs-4">@Last action code date@</label>
                                        <div class="col-xs-8">
                                            <p class="form-control-static" data-bind="text: card.last_action_code_date"></p>
                                        </div>
                                    </div>
                                    <div class="form-group" data-bind="visible: collapseCard">
                                        <label class="control-label col-xs-4">@Outstanding balance@</label>
                                        <div class="col-xs-8">
                                            <p class="form-control-static" data-bind="text: card.outstanding_balance"></p>
                                        </div>
                                    </div>
                                    <div class="form-group" data-bind="visible: collapseCard">
                                        <label class="control-label col-xs-4">@Staff in charge@</label>
                                        <div class="col-xs-8">
                                            <p class="form-control-static" data-bind="text: card.staff_in_charge"></p>
                                        </div>
                                    </div>
                                    <div class="form-group" data-bind="visible: collapseCard">
                                        <label class="control-label col-xs-4">@Time moving to higher debt group@</label>
                                        <div class="col-xs-8">
                                            <p class="form-control-static" data-bind="text: card.time_moving"></p>
                                        </div>
                                    </div>
                                    <div class="form-group" data-bind="visible: collapseCard">
                                        <label class="control-label col-xs-4">@Sale@ (@Code Name@)</label>
                                        <div class="col-xs-8">
                                            <p class="form-control-static" data-bind="text: card.sale_consultant"></p>
                                        </div>
                                    </div>
                                </div>
                                </div>
                      
                                  <div class="row text-center" style="position: absolute; bottom: 0; left: 50%"> 
                                    <div class="col-sm-12"><a href="#" data-bind="click: collapseCardProduct, text:btnCollapseCard "></a></div>
                                </div>
                            </div>
                            <div class="row title-row">
                                <span class="text-primary">@CALL RESULT@</span>
                                <hr class="popup">
                            </div>
                            <div class="row form-horizontal">
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">@Debt account@</label>
                                        <div class="col-xs-8">
                                            <input data-role="dropdownlist" id="debt-account-select" 
                                                name="debtAccount"
                                                required validationMessage="Empty!!!" 
                                                data-value-primitive="true"
                                                data-text-field="value"
                                                data-value-field="value"               
                                                data-bind="value: call.debt_account, source: debtAccountOption, events: {dataBound: onDataBoundDebtAcc}" 
                                                style="width: 100%"/>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-xs-4" style="padding-top: 2px">
                                            <input type="checkbox" data-bind="checked: followUpChecked">
                                            <span>@Requeue@</span>
                                        </label>
                                        <div class="col-xs-8">
                                            <input data-role="datetimepicker" data-date-input="true" data-format="dd/MM/yyyy H:mm" data-bind="value: followUp.reCall, visible: followUpChecked" style="width: 100%">
                                        </div>
                                    </div>
                                    <div class="form-group" data-bind="visible: followUpChecked">
                                        <label class="control-label col-xs-4">@Recall reason@</label>
                                        <div class="col-xs-8">
                                            <input class="k-textbox" name="reCallReason" data-bind="value: followUp.reCallReason" style="width: 100%">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">@Action code@</label>
                                        <div class="col-xs-8">
                                            <input data-role="dropdownlist" name="actionCode"
                                                required validationMessage="Empty!!!"
                                                data-filter="contains"
                                                data-value-primitive="true"
                                                data-text-field="value"
                                                data-value-field="value"                  
                                                data-bind="value: call.action_code, source: actionCodeOption, events: {change: actionCodeChange}" 
                                                style="width: 100%"/>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label style="line-height: 1.5">
                                            <span>SECURED ASSET</span>
                                            <input class="custom-checkbox" type="checkbox" data-bind="checked: item.secured_asset">
                                            <span></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label class="control-label col-xs-4">@Note@</label>
                                        <div class="col-xs-8">
                                            <textarea class="k-textbox" name="note" data-bind="value: note" style="width: 100%"></textarea> 
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row title-row" data-bind="visible: visibleFillingForm">
                                <span class="text-primary">@FILLING FORM@</span>
                                <hr class="popup">
                            </div>
                            <div class="row form-horizontal" id="filling-form" data-bind="visible: visibleFillingForm">
                            </div>
                            <div class="row text-center" data-bind="visible: call.action_code">
                                <button data-role="button" data-icon="save" data-bind="click: save">@Save@ (@Press@ Enter @order to@ @save@)</button>
                            </div>
                        </div>
                    </div>
                    <div style="padding: 0; overflow-x: hidden; overflow-y: hidden; min-height: 100%" id="note-content"></div>
                    <div style="padding: 0; overflow-x: hidden; overflow-y: hidden; min-height: 100%" id="payment_history-content"></div>
                    <div style="padding: 0; overflow-x: hidden; overflow-y: hidden; min-height: 100%" id="field_action-content"></div>
                    <div style="padding: 0; overflow-x: hidden; overflow-y: hidden; min-height: 100%" id="lawsuit-content"></div>
                    <div style="padding: 0; overflow-x: hidden; overflow-y: hidden; min-height: 100%" id="cross_sell-content"></div>
                    <div style="padding: 0; overflow-x: hidden; overflow-y: hidden; min-height: 100%" id="cdr-content"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/x-kendo-template" id="relationship-template">
    <div class="form-group">
        <label class="control-label col-xs-2">REF <span data-bind="text: index"></span></label>
        <div class="col-xs-10">
            <input class="k-textbox" data-bind="value: relation" style="width: 20%">
            <span>@Name@</span>
            <input class="k-textbox" data-bind="value: name" style="width: 30%">
            <span>@Phone@</span>
            <input class="k-textbox" data-bind="value: phone" style="width: 20%">
            <a class="k-button" data-bind="click: callThisPhone, attr: {data-phone: phone}"><i class="fa fa-phone-square text-info"></i></a>
        </div>
    </div>
</script>
<script type="text/javascript">
class diallistPopupManual extends Popup {
    constructor(dataCall) {
        super(dataCall);
        Object.assign(this, {
            _fieldId : "dialid",
            _popupType: "customer",
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
        var customer = await $.get(ENV.restApi + `customer/` + fieldIdValue);

        this.item = customer;

        if(customer.account_number) {
            this.relationshipDataSource = new kendo.data.DataSource({
                serverFiltering: true,
                filter: {field: "account_number", operator: "eq", value: customer.account_number},
                transport: {
                    read: ENV.restApi + "relationship",
                    parameterMap: parameterMap
                },
                schema: {
                    data: "data",
                    total: "total",
                    parse: function(res) {
                        res.data.map((doc, idx) => {
                            doc.index = idx + 1;
                        })
                        return res;
                    }
                }
            });
        }
        
        if(customer.LIC_NO) {
            this.mainProductOption = new kendo.data.DataSource({
                serverFiltering: true,
                filter: {field: "LIC_NO", operator: "eq", value: customer.LIC_NO},
                transport: {
                    read: ENV.restApi + "main_product",
                    parameterMap: parameterMap
                },
                schema: {
                    data: "data",
                    total: "total",
                    parse: function(res) {
                        res.data.map(doc => {
                            addNewDebtAccount("#debt-account-select", doc.account_number);
                        })
                        
                        $("#main-product-count").html('<span class="text-danger">(' + res.total + ')</span>');
                        if(!res.total) $(".main-product-container").addClass("hidden");
                        return res;
                    }
                }
            });
            this.cardOption = new kendo.data.DataSource({
                serverFiltering: true,
                filter: {field: "license_no", operator: "eq", value: customer.LIC_NO},
                transport: {
                    read: ENV.restApi + "card",
                    parameterMap: parameterMap
                },
                schema: {
                    data: "data",
                    total: "total",
                    parse: function(res) {
                        res.data.map(doc => {
                            addNewDebtAccount("#debt-account-select", doc.contract_no);
                        })
                        $("#card-count").html('<span class="text-danger">(' + res.total + ')</span>');
                        if(!res.total) $(".card-container").addClass("hidden");
                        return res;
                    }
                }
            });
        }
        var detailUrl = `${ENV.baseUrl}manage/customer?omc=1#/detail/${customer.id}`;
        this.assign({detailUrl: detailUrl}).open();
    }
}

var callData = <?= json_encode($callData) ?>;

window.popupObservable = new diallistPopupManual(callData);
window.popupObservable.assign({
    followUp: {},
    mainProduct: {},
    card: {},
    call: {},
    collapseMain: false,
    collapseCard: false,
    btnCollapseMain: "@See more@",
    btnCollapseCard: "@See more@",
    actionCodeOption: dataSourceJsonData(["Call", "result"]),
    actionCodeChange: function(e) {
        this.actionCodeChangeAsync(e);
        document.onkeydown = (evt) => {
            evt = evt || window.event;
            if (evt.keyCode == 13) {
                swal({
                    title: `${NOTIFICATION.checkSure}?`,
                    text: `@Save@ @this record@`,
                    icon: "warning",
                    buttons: true,
                    dangerMode: false,
                })
                .then((sure) => {
                    if (sure) {
                       this.save();
                       document.onkeydown = null;
                    }
                });
            }
        };
    },
    actionCodeChangeAsync: async function(e) {
        var actionType = e.sender.dataItem().type;
        switch(actionType) {
            case "1": default:
                this.set("visibleFillingForm", true);
                var HTML = await $.get(ENV.templateApi + "action_code/type" + actionType);
                var kendoView = new kendo.View(HTML, { model: this, template: false, wrap: false });
                $("#filling-form").html(kendoView.render());
                break;
            case "2":
                this.set("visibleFillingForm", false);
                break;
            case "3":
                this.set("visibleFillingForm", true);
                var HTML = await $.get(ENV.templateApi + "action_code/type" + actionType);
                var kendoView = new kendo.View(HTML, { model: this, template: false, wrap: false });
                $("#filling-form").html(kendoView.render());
                break;
            case "4":
                this.set("visibleFillingForm", true);
                var HTML = await $.get(ENV.templateApi + "action_code/type" + actionType);
                var kendoView = new kendo.View(HTML, { model: this, template: false, wrap: false });
                $("#filling-form").html(kendoView.render());
                break;
            case "5":
                this.set("visibleFillingForm", true);
                var HTML = await $.get(ENV.templateApi + "action_code/type" + actionType);
                var kendoView = new kendo.View(HTML, { model: this, template: false, wrap: false });
                $("#filling-form").html(kendoView.render());
                break;
            case "6":
                this.set("visibleFillingForm", true);
                var HTML = await $.get(ENV.templateApi + "action_code/type" + actionType);
                var kendoView = new kendo.View(HTML, { model: this, template: false, wrap: false });
                $("#filling-form").html(kendoView.render());
                break;
            case "7":
                this.set("visibleFillingForm", true);
                var HTML = await $.get(ENV.templateApi + "action_code/type" + actionType);
                var kendoView = new kendo.View(HTML, { model: this, template: false, wrap: false });
                $("#filling-form").html(kendoView.render());
                break;
        }
    },
    professionOption: dataSourceJsonData(["Customer", "profession"]),
    mainProductChange: function(e) {
        this.set("mainProduct", e.sender.dataItem());
    },
    cardChange: function(e) {
        this.set("card", e.sender.dataItem());
    },
    raaStatusOption: dataSourceJsonData(["RAA", "status"]),
    callThisPhone: function(e) {
        let diallistDetailId = this._dataCall[this._fieldId];
        let phone = $(e.currentTarget).data("phone");
        startPopup({dialid:diallistDetailId,customernumber:phone,dialtype:"manual",direction:"outbound"})
        makeCall(phone, diallistDetailId, "manual");
    },
    playRecording: function(e) {
        play(this._dataCall.calluuid);
    },
    save: function() {
        swal({
            title: `${NOTIFICATION.checkSure}?`,
            text: "@Save@ @this form@",
            icon: "warning",
            buttons: true,
            dangerMode: false,
        })
        .then((sure) => {
            var kendoValidator = $("#popup-window").kendoValidator().data("kendoValidator");
                
            if(!kendoValidator.validate()) {
                notification.show("@Your data is invalid@", "error");
                return;
            }
            
            var data = this.get("item").toJSON();
            var call = this.get("call").toJSON();
            data.action_code = call.action_code;
            $.ajax({
                url: ENV.restApi + "diallist_detail/" + (data.id || ""),
                type: "PUT",
                contentType: "application/json; charset=utf-8",
                data: kendo.stringify(data),
                success: (response) => {
                    if(response.status)
                        syncDataSource();
                },
                error: errorDataSource
            })

            if(data.cus_name) data.name = data.cus_name;
            if(this.followUpChecked) {
                var followUpData = Object.assign(this.get("followUp").toJSON(), {
                    name: data.name,
                    phone: this.get("phone"),
                    account_number: this.get("call.debt_account"),
                    id: data.id,
                    collection: "Customer"
                });
                $.ajax({
                    url: ENV.restApi + "follow_up",
                    type: "POST",
                    contentType: "application/json; charset=utf-8",
                    data: kendo.stringify(followUpData),
                    success: (response) => {
                        if(response.status)
                            syncDataSource();
                    },
                    error: errorDataSource
                })
            }
            $.ajax({
                url: ENV.vApi + "customer/upsert/LIC_NO/" + data.LIC_NO,
                type: "POST",
                contentType: "application/json; charset=utf-8",
                data: kendo.stringify(data),
                error: errorDataSource,
                success: res => {
                    if(res.status) {
                        var customer = res.data[0];
                    }
                }
            })

            $.ajax({
                url: ENV.vApi + "cdr/update/" + this._dataCall.calluuid,
                type: "PUT",
                contentType: "application/json; charset=utf-8",
                data: kendo.stringify(Object.assign(this.get("call").toJSON(), {customer: data})),
                error: errorDataSource
            })

            if(this.get("note")) {
                $.ajax({
                    url: ENV.restApi + "note",
                    type: "POST",
                    contentType: "application/json; charset=utf-8",
                    data: kendo.stringify({
                        from: "Diallist_detail",
                        foreign_id: data.LIC_NO,
                        content: this.get("note"),
                    }),
                    error: errorDataSource
                })
            }

            this.closePopup();
        })
    },
    collapseMainProduct: function() {
        if(this.collapseMain){
            this.set("btnCollapseMain", "@See more@");
            this.set("collapseMain", false);
        }else{
            this.set("btnCollapseMain", "@Collapse@");
            this.set("collapseMain", true);
        }
    },
    collapseCardProduct: function() {
        if(this.collapseCard){
            this.set("btnCollapseCard", "@See more@");
            this.set("collapseCard", false);
        }else{
            this.set("btnCollapseCard", "@Collapse@");
            this.set("collapseCard", true);
        }
    },

    openCdr: function(e) {
        var filter = JSON.stringify({
            logic: "or",
            filters: [
                {field: "customernumber", operator: "eq", value: this.phone},
                {field: "customernumber", operator: "in", value: this.get("item.other_phones") || []}
            ]
        });
        var query = httpBuildQuery({filter: filter, omc: 1});
        var $content = $("#cdr-content");
        if(!$content.find("iframe").length)
            $content.append(`<iframe src='${ENV.baseUrl}manage/cdr?${query}' style="width: 100%; height: 500px; border: 0"></iframe>`);
    },

    openNotes: function(e) {
        var filter = JSON.stringify({
            logic: "and",
            filters: [
                {field: "foreign_id", operator: "eq", value: this.get("item.LIC_NO")}
            ]
        });
        var query = httpBuildQuery({filter: filter, omc: 1});
        var $content = $("#note-content");
        if(!$content.find("iframe").length)
            $content.append(`<iframe src='${ENV.baseUrl}manage/data/note?${query}' style="width: 100%; height: 500px; border: 0"></iframe>`);
    },

    openPaymentHistory: function(e) { 
        var filter = JSON.stringify({
            logic: "and",
            filters: [
                {field: "account_number", operator: "eq", value: this.item.account_number}
            ]
        });
        var query = httpBuildQuery({filter: filter, omc: 1});
        var $content = $("#payment_history-content");
        if(!$content.find("iframe").length)
            $content.append(`<iframe src='${ENV.baseUrl}manage/data/payment_history?${query}' style="width: 100%; height: 500px; border: 0"></iframe>`);
    },

    openFieldAction: function(e) { 
        var filter = JSON.stringify({
            logic: "and",
            filters: [
                {field: "contract_no", operator: "eq", value: this.item.account_number}
            ]
        });
        var query = httpBuildQuery({filter: filter, omc: 1});
        var $content = $("#field_action-content");
        if(!$content.find("iframe").length)
            $content.append(`<iframe src='${ENV.baseUrl}manage/data/field_action?${query}' style="width: 100%; height: 500px; border: 0"></iframe>`);
    },

    openLawSuit: function(e) { 
        var filter = JSON.stringify({
            logic: "and",
            filters: [
                {field: "contract_no", operator: "eq", value: this.item.account_number}
            ]
        });
        var query = httpBuildQuery({filter: filter, omc: 1});
        var $content = $("#lawsuit-content");
        if(!$content.find("iframe").length)
            $content.append(`<iframe src='${ENV.baseUrl}manage/data/lawsuit_history?${query}' style="width: 100%; height: 500px; border: 0"></iframe>`);
    },

    openCrossSell: function(e) { 
        var filter = JSON.stringify({
            logic: "and",
            filters: [
                {field: "contract_no", operator: "eq", value: this.item.account_number}
            ]
        });
        var query = httpBuildQuery({filter: filter, omc: 1});
        var $content = $("#cross_sell-content");
        if(!$content.find("iframe").length)
            $content.append(`<iframe src='${ENV.baseUrl}manage/data/cross_sell?${query}' style="width: 100%; height: 500px; border: 0"></iframe>`);
    },

    onChangePromiseDate: function(e) {
        var value = this.get('item.promised_date');
        this.set('followUp.reCall', value);
    },

    nonePaymentOption: dataSourceJsonData(["Actioncode", "reasonnonpayment"]),

    onChangeReasonNonePayment: function(e) {
        if(this.get('item.reason_nonpayment') == 'others') {
            this.set('reason_nonpayment_note', true);
        }
        else {
            this.set('reason_nonpayment_note', false);
        }
    },

    onDataBoundDebtAcc: function(e) {
        var debtAcc = $("#debt-account-select").data("kendoDropDownList");
        var debtAccDB = debtAcc.dataSource.data();
        if(debtAccDB.length > 0) {
            debtAcc.select(0);
        }
    },

    onDataBoundContractCard: function(e) {
        var cardContractNo = $("#card-contract-no").data("kendoDropDownList");
        if(typeof contractNo != 'undefined'){
            var contractNoDB = contractNo.dataSource.data();
            if(contractNoDB.length > 0) {
                contractNo.select(0);
            }
        }
    },

    onDataBoundAccNo: function(e) {
        var mainContractNo = $("#main-contract-no").data("kendoDropDownList");
        if(typeof mainContractNo != 'undefined'){
            var mainContractNoDB = mainContractNo.dataSource.data();
            if(mainContractNoDB.length > 0) {
                mainContractNo.select(0);
            }
        }
    }
});

window.popupObservable.init();

function addNewDebtAccount(widgetId, value) {
    var widget = $(widgetId).getKendoDropDownList();
    var dataSource = widget.dataSource;
    dataSource.add({value: value});
};
</script>

<style type="text/css">
    .form-group {
        margin-bottom: 0;
    }
    #collapseMainProduct{
        position: relative;
    }
    #collapseCardProduct{
        position: relative;
    }
</style>
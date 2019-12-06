<div class="col-sm-3">
    <label class="control-label col-xs-2" style="line-height: 2;">CIF</label>
    <div class="col-xs-10">
        <input onkeypress="onKeyPressTextBox(event)" class="k-textbox" style="width: 100%" id="cif_id" name="cif" data-bind="value: cif">
    </div>
</div>
<div class="col-sm-5" style="padding-left: 21px">
    <label class="control-label col-xs-4" style="line-height: 2;">LOAN CONTRACT</label>
    <div class="col-xs-7">
        <input onkeypress="onKeyPressTextBox(event)" class="k-textbox" style="width: 95%" id="loan_id" name="loanContract" data-bind="value: loanContract}">
    </div>
</div>
<div class="col-sm-4">
    <label class="control-label col-xs-4" style="line-height: 2;">National ID</label>
    <div class="col-xs-8">
        <input onkeypress="onKeyPressTextBox(event)" class="k-textbox" style="width: 100%" id="national" name="nationalID" data-bind="value: nationalID">
    </div>
</div>
<div class="col-sm-12 text-center">
    <button id="filter-datalibrary" style="margin-top: 10px;" data-role="button" data-bind="click: search">@Search@</button>
</div>
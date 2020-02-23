<div class="col-sm-4">
    <label class="control-label col-xs-2" style="line-height: 2;">Contract number</label>
    <div class="col-xs-10">
        <input onkeypress="onKeyPressTextBox(event)" class="k-textbox" style="width: 100%" id="account_no" name="account_no" data-bind="value: account_no">
    </div>
</div>
<div class="col-sm-4" style="padding-left: 21px">
    <label class="control-label col-xs-4" style="line-height: 2;">CIF</label>
    <div class="col-xs-7">
        <input onkeypress="onKeyPressTextBox(event)" class="k-textbox" style="width: 95%" id="cif" name="cif" data-bind="value: cif}">
    </div>
</div>
<div class="col-sm-4">
    <label class="control-label col-xs-4" style="line-height: 2;">Customer name</label>
    <div class="col-xs-8">
        <input onkeypress="onKeyPressTextBox(event)" class="k-textbox" style="width: 100%" id="cus_name" name="cus_name" data-bind="value: cus_name">
    </div>
</div>
<div class="col-sm-12 text-center">
    <button id="filter-datalibrary" style="margin-top: 10px;" data-role="button" data-bind="click: search">@Search@</button>
</div>
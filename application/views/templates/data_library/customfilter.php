<div class="col-sm-5">
    <label class="control-label col-xs-4" style="line-height: 2;">CIF</label>
    <div class="col-xs-8">
        <input id="cif_id" name="cif" data-bind="value: cif, events: {change: cifChange}">
    </div>
</div>
<div class="col-sm-5">
    <label class="control-label col-xs-5" style="line-height: 2;">LOAN CONTRACT</label>
    <div class="col-xs-7">
        <input id="loan_id" name="loanContract" data-bind="value: loanContract, events: {change: loanContractChange}">
    </div>
</div>
<div class="col-sm-5">
    <label class="control-label col-xs-4" style="line-height: 2;">National ID</label>
    <div class="col-xs-8">
        <input id="national" name="nationalID" data-bind="value: nationalID, events: {change: nationalIDChange}">
    </div>
</div>
<div class="col-sm-5 text-center">
    <button data-role="button" data-bind="click: search">@Search@</button>
</div>
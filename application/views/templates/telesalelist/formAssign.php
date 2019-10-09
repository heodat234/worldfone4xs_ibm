
<div id="form-loader" style="display: none;"></div>
<div class="container-fluid">
   <div class="row">
      <div class="col-xs-12" id="main-form">
         <div class="form-group">
            <h3><input type="checkbox" data-bind="checked: item.isCheckedMail"> @Send email@</h3>
         </div>
         
         <div class="form-group">
            <label>@Reason@</label>
            <input data-role="dropdownlist"
               data-text-field="name_viet"
               data-value-field="code"
                    data-value-primitive="true"
                    data-bind="value: item.reason, source: reasonTemplateOption" style="width: 100%">
         </div>
         
      </div>
   </div>
   <div class="row side-form-bottom">
      <div class="col-xs-12 text-right">
        <button class="btn btn-sm btn-default" onclick="closeForm()">@Cancel@</button>
        <button class="btn btn-sm btn-primary btn-save" onclick="closeForm()" data-bind="click: save">@Save@</button>
      </div>
   </div>
</div>


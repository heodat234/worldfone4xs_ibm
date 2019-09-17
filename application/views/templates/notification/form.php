<div class="container-fluid">
	<div class="row">
		<div class="col-xs-12" id="main-form">
			<div class="form-group">
				<label>@Title@</label>
				<input class="k-textbox" style="width: 100%" data-bind="value: item.title">
			</div>
			<div class="form-group">
	            <div class="checkbox"><label><input type="checkbox" data-bind="checked: item.active"> <span>@Active@</span></label></div>
	        </div>
			<div class="form-group">
				<label>@Content@</label>
				<textarea data-role="editor" 
				data-tools="['bold',
                               'italic',
                               'underline',
                               'strikethrough',
                               'justifyLeft',
                               'justifyCenter',
                               'justifyRight',
                               'justifyFull']"
				style="width: 100%" data-bind="value: item.content"></textarea>
			</div>
			<div class="form-group">
				<label>@Icon@</label><br>
				<input data-role="dropdownlist"
                    data-value-primitive="true"  
                    data-text-field="text"
                    data-value-field="value"   
                    data-template="iconValueTemplate"
                    data-value-template="iconValueTemplate"                
                    data-bind="value: item.icon, source: iconOption" style="width: 70px; float: left"/>
                <input class="k-textbox" style="width: 270px; margin-left: 10px" data-bind="value: item.icon"/>
			</div>
			<div class="form-group">
				<label>@Color@</label><br>
				<input data-role="dropdownlist"
                    data-template="colorValueTemplate"
                    data-value-template="colorValueTemplate"                
                    data-bind="value: item.color, source: colorOption" style="width: 100%"/>
			</div>
			<div class="form-group">
				<label>@Link@</label>
				<textarea class="k-textbox" style="width: 100%" data-bind="value: item.link"></textarea>
			</div>
			<div class="form-group">
				<label>@To@</label>
				<select data-role="multiselect"
                    data-value-primitive="true"  
                    data-text-field="text"
                    data-value-field="extension"                 
                    data-bind="value: item.to, source: userListData" style="width: 100%"></select>
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
<script type="text/x-kendo-template" id="iconValueTemplate">
    <i class="#= data.value #"></i>
</script>
<script type="text/x-kendo-template" id="colorValueTemplate">
    <i class="fa fa-circle #= data #"></i>
</script>
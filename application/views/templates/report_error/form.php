<div class="container-fluid">
	<div class="row">
		<div class="col-xs-12" id="main-form">
			<div class="form-group">
				<label>@Title@</label>
				<input class="k-textbox" style="width: 100%" data-bind="value: item.title">
			</div>
			<div class="form-group">
				<label>@Image@</label>
				<img data-bind="attr: {src: item.imgPath}" style="width: 100%">
			</div>
			<div class="form-group">
				<label>@Content@</label>
				<textarea class="k-textbox" style="width: 100%" data-bind="value: item.content"></textarea>
			</div>
			<div class="form-group">
				<label>@Priority@</label><br>
				<input data-role="dropdownlist"
					data-value-primitive="true"  
                    data-text-field="text"
                    data-value-field="value"               
                    data-bind="value: item.priority, source: priorityOption" style="width: 100%"/>
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
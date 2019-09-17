<div class="container-fluid">
	<div class="row">
		<div class="col-xs-12" id="main-form">
			<div class="form-group">
				<label>@Quality control@</label>
				<span> | </span>
				<b data-bind="text: item.initPoint" class="text-danger"></b>
				<span>(@Initial@ @point@)</span>
				<span class="text-muted"> + </span>
				<b data-bind="text: item.totalPoint" class="text-danger"></b>
				<span>(@Total@ @point@)</span>
				<span class="text-muted"> = </span>
				<b data-bind="text: item.endPoint" class="text-danger"></b>
				<span>(@End@ @point@)</span>
                <div class="pull-right">
                	<label>@Play@ @recording@: </label>
                	<a data-role="button" title="@Play@ @recording@" data-bind="click: playRecording">
                        <i class="fa fa-play"></i>
                    </a>
                </div>
                <div style="margin-top: 10px">
					<div data-role="grid"
	                    data-editable="{mode: 'incell', createAt: 'bottom'}" id="qcdataGrid"
	                    data-toolbar="[{name: 'create', text: '@Add@'}]"
	                    data-columns="[
	                        {field:'code', title: '@Code@', editor: gridSelectCode, footerTemplate: '@Sum@'},
	                        {field:'point', title: '@Point@', footerTemplate: ftPoint, editor: functionFalse},
	                        {field:'content', title: '@Content@', editor: functionFalse},
	                        {title:'@Remove@', command: [{name: 'destroy', text: ''}], width: 80}
	                        ]"
	                  data-bind="source: item.qcdata, events: {cellClose: qcdataCellClose, dataBinding: qcdataDataBinding}"/>
	              </div>
            </div>
            <div class="form-group row form-horizontal">
				<label class="control-label col-sm-2 text-left">QC @status@</label>
				<div class="col-sm-4">
					<input data-role="dropdownlist" name="qcstatus"
                    	data-value-primitive="true"
                    	data-text-field="text"
                    	data-value-field="value"
                    	data-template="statusTemplate"                 
                      	data-bind="value: item.qcstatus, source: qcstatusOption, events: {select: qcstatusSelect}" style="width: 100%"/>
				</div>
				<label class="control-label col-sm-2 text-left">@Note@</label>
				<div class="col-sm-4">
					<textarea class="k-textbox" data-bind="value: item.qcnote"  
					style="width: 100%"></textarea>
				</div>
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
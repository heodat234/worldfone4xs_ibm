<div class="col-sm-6">
	<div class="block" style="margin-top: 20px">
		<div class="block-title">
			<h2><strong>Diallist setting</strong></h2>
		</div>
		<div class="block-content">
			<div class="form-horizontal">
				<div class="form-group">
					<label class="control-label col-xs-4">Name</label>
					<div class="col-xs-8">
						<input class="k-textbox" style="width: 100%"
						data-bind="value: item.name">
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-xs-4">Type</label>
					<div class="col-xs-8">
						<input data-role="dropdownlist" style="width: 100%"
						data-value-primitive="true"
						data-text-field="text" data-value-field="value"
						data-bind="value: item.type, source: typeOption, events: {cascade: typeCascade}">
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-xs-4">Info</label>
					<div class="col-xs-8">
						<textarea style="width: 100%; height: 100px"
							data-role="editor"
							data-tools="[
							'bold',
							'italic',
						   'underline',
						   'insertUnorderedList',
						   'insertOrderedList',
						   'indent',
						   'outdent'
						   ]"
						data-bind="value: item.info"></textarea>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-xs-4">Mode</label>
					<div class="col-xs-8">
						<input data-role="dropdownlist" style="width: 100%"
						data-value-primitive="true"
						data-text-field="text" data-value-field="value"
						data-bind="value: item.mode, source: modeOption">
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-xs-4">Group</label>
					<div class="col-xs-8">
						<input data-role="dropdownlist" style="width: 100%"
						data-value-primitive="true"
						data-text-field="name" data-value-field="id" 
						data-bind="value: item.group_id, source: groupOption">
					</div>
				</div>
				<div class="form-group" data-bind="visible: visibleTryCount">
					<label class="control-label col-xs-4">Try count</label>
					<div class="col-xs-8">
						<input data-role="numerictextbox" style="width: 100%"
						data-bind="value: item.maxTryCount">
					</div>
				</div>
				<div class="form-group" data-bind="visible: visibleTryInterval">
					<label class="control-label col-xs-4">Time auto</label>
					<div class="col-xs-8">
						<input data-role="numerictextbox" style="width: 100%"
						data-bind="value: item.tryInterval">
					</div>
				</div>
				<div class="form-group text-center">
					<button data-role="button" data-bind="click: save">Save</button>
					<button data-role="button" data-bind="click: saveAndImport">Save & Import</button>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="col-sm-6">
	<div class="block" style="margin-top: 20px">
		<div class="block-title">
			<h2><strong>Diallist Field</strong></h2>
		</div>
		<div class="block-content" style="min-height: 50vh; overflow-y: scroll; overflow-x: hidden">
			<div class="form-horizontal" data-bind="source: item.columns" data-template="column-template">
			</div>
		</div>
	</div>
</div>
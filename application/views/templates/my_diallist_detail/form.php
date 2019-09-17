<?php
	$dataFieldsJSON = $this->input->get("dataFields");
	$dataFields = $dataFieldsJSON ? json_decode($dataFieldsJSON, TRUE) : [];
?>
<div class="container-fluid">
	<div class="row">
		<div class="col-xs-12" id="main-form">
			<div class='form-group'>
				<label>@Assign@</label>
				<input class='k-textbox' style='width: 100%' data-bind='value: item.assign'>
			</div>
			<?php foreach ($dataFields as $fieldDoc) { 
				switch ($fieldDoc["type"]) {
					case 'value':
						# code...
						break;
					
					default:
						echo "
						<div class='form-group'>
							<label>{$fieldDoc['title']}</label>
							<input class='k-textbox' style='width: 100%' data-bind='value: item.{$fieldDoc['field']}'>
						</div>";
						break;
				}
			} ?>
		</div>
	</div>
	<div class="row side-form-bottom">
		<div class="col-xs-12 text-right">
			<button class="btn btn-sm btn-default" onclick="closeForm()">Cancel</button>
			<button class="btn btn-sm btn-primary btn-save" onclick="closeForm()" data-bind="click: save">Save</button>
		</div>
	</div>
</div>
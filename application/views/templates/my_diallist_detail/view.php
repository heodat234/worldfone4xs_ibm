<?php
	$dataFieldsJSON = $this->input->get("dataFields");
	$dataFields = $dataFieldsJSON ? json_decode($dataFieldsJSON, TRUE) : [];
?>
<div class="container-fluid">
	<div class="row">
		<div class="col-xs-12" id="main-form">
		<?php foreach ($dataFields as $fieldDoc) { 
			switch ($fieldDoc["type"]) {
				case 'value':
					# code...
					break;
				
				default:
					echo "
					<div class='form-group'>
						<label>{$fieldDoc['title']}: </label>
						<span data-bind='text: item.{$fieldDoc['field']}'>
					</div>";
					break;
			}
		} ?>
		</div>
	</div>
	<div class="row side-form-bottom">
		<div class="col-xs-12 text-right">
			<button class="btn btn-sm btn-default" onclick="closeForm()">Cancel</button>
		</div>
	</div>
</div>
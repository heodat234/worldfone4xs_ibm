<div class="col-sm-3">
	<?php $this->load->library("mongo_private"); 
	$jsondata = $this->mongo_private->where(["tags" => ["RAA","fields"]])->getOne("LO_Jsondata");
	$dataFields = isset($jsondata["data"]) ? $jsondata["data"] : []; ?>
	<?php foreach ($dataFields as $idx => $fieldDoc) { 
		if($idx && $idx % 5 == 0 && $idx + 1 != count($dataFields)) echo '</div><div class="col-sm-3">';
		switch ($fieldDoc["type"]) {
			case 'timestamp':
				echo "
				<div class='form-group'>
					<label class='control-label col-xs-4'>{$fieldDoc['title']}</label>
					<div class='col-xs-8'>
						<input data-role='datepicker' style='width: 100%' data-bind='value: action.{$fieldDoc['field']}'>
					</div>
				</div>";
				break;
			
			default:
				if($fieldDoc["field"] == "raaStatus") {
					echo '
					<div class="form-group">
				        <label class="control-label col-xs-4">@Status@</label>
				        <div class="col-xs-8">
							<input data-role="dropdownlist" name="raaStatus"
				                    required validationMessage="Empty!!!"
				                    data-value-primitive="true"
				                    data-text-field="text"
				                    data-value-field="value"                  
				                    data-bind="value: action.raaStatus, source: raaStatusOption" 
				                    style="width: 100%"/>
						</div>
					</div>
					';
				} else {
					echo "
					<div class='form-group'>
						<label class='control-label col-xs-4'>{$fieldDoc['title']}</label>
						<div class='col-xs-8'>
							<input class='k-textbox' style='width: 100%' data-bind='value: action.{$fieldDoc['field']}'>
						</div>
					</div>";
				}
				break;
		}
	} ?>
</div>
<div class="col-sm-3">
	<?php $this->load->library("mongo_private"); 
	$jsondata = $this->mongo_private->where(["tags" => ["LAWSUIT","fields"]])->getOne("LO_Jsondata");
	$dataFields = isset($jsondata["data"]) ? $jsondata["data"] : []; ?>
	<?php foreach ($dataFields as $idx => $fieldDoc) { 
		if($idx && $idx % 13 == 0 && $idx + 1 != count($dataFields)) echo '</div><div class="col-sm-3">';
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
				echo "
				<div class='form-group'>
					<label class='control-label col-xs-4'>{$fieldDoc['title']}</label>
					<div class='col-xs-8'>
						<input class='k-textbox' style='width: 100%' data-bind='value: action.{$fieldDoc['field']}'>
					</div>
				</div>";
				break;
		}
	} ?>
</div>
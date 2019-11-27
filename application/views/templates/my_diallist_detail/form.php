<?php
	$this->load->library("mongo_private");
	$dataFields = $this->mongo_private->where(["collection"=>getCT("Diallist_detail"), "sub_type"=>['$exists'=>TRUE,'$nin'=>['',null]]])->get("Model");
	foreach ($dataFields as &$doc) {
		$doc["title"] = !empty($doc["title"]) ? $doc["title"] : $doc["field"];
	}
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
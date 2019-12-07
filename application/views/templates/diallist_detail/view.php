<?php
	$this->load->library("mongo_private");
	$dataFields = $this->mongo_private->where(["collection"=>getCT("Diallist_detail"), "sub_type"=>['$exists'=>TRUE,'$nin'=>['',null]]])->get("Model");
	foreach ($dataFields as &$doc) {
		$doc["title"] = !empty($doc["title"]) ? $doc["title"] : $doc["field"];
	}
?>
<div class="container-fluid">
	<div class="row">
		<div>
			<div class="col-xs-8">
				<div class="form-group">
		            <h4 class="fieldset-legend"><span>@Reference@</span></h4>
		        </div>
				<div data-role="grid"
				data-columns="[
					{field: 'name', title: '@Name@'},
					{field: 'relation', title: '@Relationship@'},
					{field: 'phone', title: '@Phone@'},
				]"
				data-bind="source: relationshipDataSource"></div>
			</div>
		</div>
		<div class="col-xs-4" id="main-form">
		<?php foreach ($dataFields as $fieldDoc) { 
			switch ($fieldDoc["type"]) {
				case 'timestamp':
					echo "
					<div class='form-group'>
						<label>{$fieldDoc['title']}</label>
						<span data-format='dd/MM/yyyy' style='width: 100%' data-bind='text: item.{$fieldDoc['field']}'></span>
					</div>";
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

<script type="text/javascript">
	var diallistDetailId = "<?= $this->input->get('id') ?>";
	$.ajax({
        url: `${Config.crudApi+Config.collection}/${diallistDetailId}`,
        error: errorDataSource
    }).then(function(dataItemFull) {
    	var model = kendo.myClass.extend({
	        relationshipDataSource: new kendo.data.DataSource({
	        	serverFiltering: true,
	        	filter: {field: "LIC_NO", operator: "eq", value: dataItemFull.LIC_NO},
	        	transport: {
	        		read: ENV.restApi + "relationship",
	        		parameterMap: parameterMap
	        	},
	        	schema: {
	        		data: "data",
	        		total: "total"
	        	}
	        })
	    });
	    kendo.bind($("#right-form"), new model("item", dataItemFull));
    })
</script>
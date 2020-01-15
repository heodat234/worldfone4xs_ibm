<?php
	$this->load->library("mongo_private");
	$dataFields = $this->mongo_private->where(["collection"=>getCT("Diallist_detail"), "sub_type"=>['$exists'=>TRUE,'$nin'=>['',null]]])->get("Model");
	foreach ($dataFields as &$doc) {
		$doc["title"] = !empty($doc["title"]) ? $doc["title"] : $doc["field"];
	}
?>
<div class="container-fluid">
	<div class="row hidden" id="customer-detail-container">
		<div class="panel panel-info">
			<div class="panel-heading">
				<h4 class="panel-title">
					<a class="accordion-toggle" data-toggle="collapse" href="\#customer-detail"><b>@Customer detail@</b></a>
					&nbsp;
					<i class="fa fa-angle-down"></i>
				</h4>
			</div>
			<div id="customer-detail" class="panel-collapse collapse in">
				<div class="panel-body" style="padding: 5px">
					<iframe style="width: 100%; min-height: 70vh; border: 0"></iframe>
				</div>
			</div>
		</div>
	</div>
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

<script type="text/javascript">
	var diallistDetailId = "<?= $this->input->get('id') ?>";
	$.ajax({
        url: `${Config.crudApi+Config.collection}/${diallistDetailId}`,
        error: errorDataSource
    }).then(function(dataItemFull) {
    	var model = Object.assign({
	        item: dataItemFull,
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
	    }, Config.observable);
	    kendo.bind($("#right-form"), kendo.observable(model));

	    $.ajax({
	    	url: `${Config.crudApi}customer`,
	    	data: {q: JSON.stringify({filter: {field: "LIC_NO", operator: "eq", value: dataItemFull.LIC_NO}})},
	    	success: function(res) {
	    		if(res.total && res.data) {
	    			$("#customer-detail-container").removeClass("hidden");
	    			$("#customer-detail-container").find("iframe").attr("src", ENV.baseUrl + "manage/customer/?omc=1#/detail/" + res.data[0].id);
	    		}
	    	}
	    })
    })
</script>
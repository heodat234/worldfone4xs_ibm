<?php
	$this->load->library("mongo_private");
	$dataFields = $this->mongo_private->where(["collection"=>getCT("Diallist_detail"), "sub_type"=>$this->input->get("sub_type")])->get("Model");
	foreach ($dataFields as &$doc) {
		$doc["title"] = !empty($doc["title"]) ? $doc["title"] : $doc["field"];
	}
?>
<div class="container-fluid">
	<div class="row">
		<div class="col-xs-12" id="main-form">
			<div class='form-group'>
				<label>@Assign@</label>
				<input data-role="dropdownlist" style="width: 100%" name="assign"
				data-value-primitive="true" data-bind='value: item.assign, source: userDataSource'>
			</div>
			<div class='form-group'>
				<label>@Main phone@</label>
				<input class="k-textbox" style="width: 100%" name="phone" data-bind='value: item.phone'>
			</div>
			<?php foreach ($dataFields as $fieldDoc) { 
				switch ($fieldDoc["type"]) {
					case 'phone': case 'arrayPhone':
						echo "
						<div class='form-group'>
							<label>{$fieldDoc['title']} <i class='fa fa-phone-square text-success'></i></label>
							<input class='k-textbox' style='width: 100%' data-bind='value: item.{$fieldDoc['field']}'>
						</div>";
						break;

					case 'timestamp':
						echo "
						<div class='form-group'>
							<label>{$fieldDoc['title']}</label>
							<input data-role='datepicker' data-format='dd/MM/yyyy' style='width: 100%' data-bind='value: item.{$fieldDoc['field']}'>
						</div>";
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

<script type="text/javascript">

var Form = {
	init: async function() {
		var id = "<?= $this->input->get("id") ?>";

		var dataItemFull = await $.ajax({
	        url: `${Config.crudApi+Config.collection}/${id}`,
	        error: errorDataSource
	    });

	    var diallist = await $.get(ENV.restApi + "diallist/" + Config.id);

	    var model = Object.assign({}, {
			item: dataItemFull,
	        userDataSource: new kendo.data.DataSource({
	            transport: {
	                read: ENV.restApi + "group/" + diallist.group_id
	            },
	            schema: {
	                data: "members",
	                parse: function(response) {
	                    return response;
	                }
	            }
	        }),
			save: function() {
	            $.ajax({
	                url: `${Config.crudApi+Config.collection}/${id}`,
	                type: "PUT",
	                contentType: "application/json; charset=utf-8",
	                data: kendo.stringify(this.item.toJSON()),
	                error: errorDataSource,
	                success: function() {
	                    detailTable.dataSource.read()
	                }
	            })
			}
		});

		kendo.bind("#right-form", kendo.observable(model));
	}
};

Form.init();
</script>
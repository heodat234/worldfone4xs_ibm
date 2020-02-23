<script>
	var Config = {
		crudApi: `${ENV.restApi}`,
		templateApi: `${ENV.templateApi}`,
		collection: "target_of_report",
		observable: {
			debtGroupOptions: ["Group A", "Group B", "Group C", "Group D", "Group E"],
			debtTypeOptions: ["SIBS", "CARD"],
			duedateTypeOptions: ["A01", "A02", "A03"],
		},
		model: {
			id: "id",
			createdAt: {type: "date"}
		},
		sort: [{
			field: "group.name", 
			dir: "asc"
		}],
		columns: [
		{
			field: "name",
			title: "@Group name@",
		},{
			field: "target",
			title: "@Target@",
			template: dataItem => {
				return dataItem.target + '%';
			}
		}, {
			field: "create_by",
			title: "@Create By@",
		},{
			field: "createdAt",
			title: "@createdAt@",
			template: function(dataItem) {
				return new Date(dataItem.createdAt * 1000).toLocaleDateString()
			}
		},{
			field: "udpate_by",
			title: "@Update By@",
		},{
			field: "updatedAt",
			title: "@Updated At@",
			template: function(dataItem) {
				return dataItem.updatedAt ? new Date(dataItem.updatedAt * 1000).toLocaleDateString() : ''
			}
		},{
			title: `@Action@`,
			command: [{template: '<button class="btn btn-alt btn-default" onclick="openForm({title: `@Edit@ @Target@`,width: 500});editForm(this)">@Edit@</button>'}, {name: "destroy", text: "@Delete@"}],
			width: 200
		}]
	}; 
</script>

<!-- Page content -->
<div id="page-content">
	<!-- Table Styles Header -->
	<ul class="breadcrumb breadcrumb-top">
		<li>@Setting@</li>
		<li>Target</li>
		<li class="pull-right none-breakcrumb" id="top-row">
			<div class="btn-group btn-group-sm">
				<button class="btn btn-alt btn-default" onclick="openForm({title: `@Add@ @Target@`,width: 500});addNewTargetForm()">@Create@</button>
			</div>
		</li>
	</ul>
	<!-- END Table Styles Header -->

	<div class="container-fluid">
		<div class="row">
			<div class="col-sm-12" style="height: 80vh; overflow-y: auto; padding: 0">
				<!-- Table Styles Content -->
				<div id="grid"></div>
				<!-- END Table Styles Content -->
			</div>
		</div>
	</div>


</div>
<!-- END Page Content -->

<script>
	async function addNewTargetForm() {
		var formHtml = await $.ajax({
			url: Config.templateApi + Config.collection + "/add_form",
			error: errorDataSource
		});
		var model = Object.assign(Config.observable, {
			item: {
				active: false,
				create_by: ENV.extension,
				group_active: false,
				show_team_leader_name: false,
				show_B_plus_duedate_type: false,
				B_plus_duedateTypeOptions: [],
				debtTypeSelect: function(e) {
					this.item.set("group_active", true)
				},
				debtGroupSelect: function(e) {
					if (e.dataItem == "Group A") {
						this.item.set("show_team_leader_name", true);
						this.item.set("show_B_plus_duedate_type", false);
					} else {
						this.item.set("show_team_leader_name", false);
						this.item.set("show_B_plus_duedate_type", true);
					}
					switch (e.dataItem) {
						case "Group B":
						this.item.set("B_plus_duedateTypeOptions", ["B01", "B02", "B03"]);
						break;
						case "Group C":
						this.item.set("B_plus_duedateTypeOptions", ["C01", "C02", "C03"]);
						break;
						case "Group D":
						this.item.set("B_plus_duedateTypeOptions", ["D01", "D02", "D03"]);
						break;
						case "Group E":
						this.item.set("B_plus_duedateTypeOptions", ["E01", "E02", "E03"]);
						break;
					}
				},
				updateItemName: function() {
					var temp = this.item.debt_type != undefined ? this.item.debt_type : '';
					if(this.item.debt_group != undefined){
						temp += '/' + this.item.debt_group;
						switch (this.item.debt_group) {
							case "Group A":
							var duedate_type = this.item.duedate_type != undefined ? this.item.duedate_type : '';
							temp += '/' + duedate_type;
							break;
							case "Group B":
							case "Group C":
							case "Group D":
							case "Group E":
							var B_plus_duedate_type = this.item.B_plus_duedate_type != undefined ? this.item.B_plus_duedate_type : '';
							temp += '/' + B_plus_duedate_type;
							break;
						}
					}
					this.item.set("name", temp);
				}
			},
			save: function() {
				var newData = this.item;
				var check_trung = $.get(ENV.vApi + Config.collection + "/checkTrungName?name=" + this.item.name, function(data){
					if(data.length == 0){
						$("#grid").data("kendoGrid").dataSource.add(newData)
						$("#grid").data("kendoGrid").dataSource.sync().then(()=>{
							$("#grid").data("kendoGrid").dataSource.read()
						})
						closeForm(); 
					}else
						notification.show('Dupplicate Name!!!','error')
				});
			},
			cancel: function() {
				$("#grid").data("kendoGrid").dataSource.read()
				closeForm();
			}

		});
		kendo.destroy($("#right-form"));

		$("#right-form").empty();
		var kendoView = new kendo.View(formHtml, {
			wrap: false,
			model: model,
			evalTemplate: false
		});
		kendoView.render($("#right-form"));
	}

	async function editForm(ele) {
		var uid = $(ele).parent().parent().data('uid');
		var dataItem = $("#grid").data("kendoGrid").dataSource.getByUid(uid);
		console.log(dataItem)
		formHtml = await $.ajax({
			url: Config.templateApi + Config.collection + "/form",
			error: errorDataSource
		});
		dataItem.udpate_by = ENV.extension;
		var model = Object.assign(Config.observable, {
			item: dataItem,
			debtTypeSelect: function(e) {
				this.item.set("group_active", true)
			},
			debtGroupSelect: function(e) {
				if (e.dataItem == "Group A") {
					this.item.set("show_team_leader_name", true);
					this.item.set("show_B_plus_duedate_type", false);
				} else {
					this.item.set("show_team_leader_name", false);
					this.item.set("show_B_plus_duedate_type", true);
				}
				switch (e.dataItem) {
					case "Group B":
					this.item.set("B_plus_duedateTypeOptions", ["B01", "B02", "B03"]);
					break;
					case "Group C":
					this.item.set("B_plus_duedateTypeOptions", ["C01", "C02", "C03"]);
					break;
					case "Group D":
					this.item.set("B_plus_duedateTypeOptions", ["D01", "D02", "D03"]);
					break;
					case "Group E":
					this.item.set("B_plus_duedateTypeOptions", ["E01", "E02", "E03"]);
					break;
				}
			},
			updateItemName: function() {
				var temp = this.item.debt_type != undefined ? this.item.debt_type : '';
				if(this.item.debt_group != undefined){
					temp += '/' + this.item.debt_group;
					switch (this.item.debt_group) {
						case "Group A":
						var duedate_type = this.item.duedate_type != undefined ? this.item.duedate_type : '';
						temp += '/' + duedate_type;
						break;
						case "Group B":
						case "Group C":
						case "Group D":
						case "Group E":
						var B_plus_duedate_type = this.item.B_plus_duedate_type != undefined ? this.item.B_plus_duedate_type : '';
						temp += '/' + B_plus_duedate_type;
						break;
					}
				}
				this.item.set("name", temp);
			},
			save: function() {
				var group_name_check = this.item.name;
				var check_trung = false;

				$("#grid").data("kendoGrid").dataSource.sync().then(()=>{
					$("#grid").data("kendoGrid").dataSource.read()
				})
				closeForm(); 

			},
			cancel: function() {
				$("#grid").data("kendoGrid").dataSource.read()
				closeForm();
			}
		});
		kendo.destroy($("#right-form"));
		$("#right-form").empty();
		var kendoView = new kendo.View(formHtml, {
			wrap: false,
			model: model,
			evalTemplate: false
		});
		kendoView.render($("#right-form"));
	}

</script>
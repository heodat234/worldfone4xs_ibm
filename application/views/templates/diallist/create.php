<div class="col-sm-6">
	<div class="block" style="margin-top: 20px">
		<div class="block-title">
			<h2><strong>@Campaign setting@</strong></h2>
		</div>
		<div class="block-content">
			<div class="form-horizontal">
				<div class="form-group">
					<label class="control-label col-xs-4">@Name@</label>
					<div class="col-xs-8">
						<input class="k-textbox" style="width: 100%"
						data-bind="value: item.name">
					</div>
				</div>
				<!-- <div class="form-group">
					<label class="control-label col-xs-4">@Team@</label>
					<div class="col-xs-8">
						<input id="team-select" data-role="dropdownlist" style="width: 100%"
						data-value-primitive="true"
						data-text-field="text" data-value-field="text" 
						data-bind="value: item.team, source: teamOption, events: {change: teamChange}">
					</div>
				</div> -->
				<div class="form-group">
					<label class="control-label col-xs-4">@Campaign target@ (%)</label>
					<div class="col-xs-8">
						<input data-role="numerictextbox" style="width: 100%"
						data-bind="value: item.target">
					</div>
				</div>
				<div class="form-group hidden">
					<label class="control-label col-xs-4">@Infomation@</label>
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
					<label class="control-label col-xs-4">@Mode@</label>
					<div class="col-xs-8">
						<input data-role="dropdownlist" style="width: 100%"
						data-value-primitive="true"
						data-text-field="text" data-value-field="value"
						data-bind="value: item.mode, source: modeOption">
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-xs-4">@Group@</label>
					<div class="col-xs-8">
						<input id="group-select" data-role="dropdownlist" style="width: 100%"
						data-value-primitive="true"
						data-text-field="name" data-value-field="id" 
						data-bind="value: item.group_id, source: groupOption, events: {cascade: groupCascade}">
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-xs-4">@Members@</label>
					<div class="col-xs-8">
						<div data-template="member-template" data-bind="source: item.members"></div>
					</div>
				</div>
				<div class="form-group" data-bind="visible: visibleMinOutstanding">
					<label class="control-label col-xs-4">Minimum outstanding principal (vnd)</label>
					<div class="col-xs-8">
						<input data-role="numerictextbox" data-format="n0" style="width: 100%"
						data-bind="value: item.minOutstanding">
					</div>
				</div>
				<div class="form-group" data-bind="visible: visibleTryCount">
					<label class="control-label col-xs-4">Try count</label>
					<div class="col-xs-8">
						<input data-role="numerictextbox" data-format="n0" style="width: 100%"
						data-bind="value: item.maxTryCount">
					</div>
				</div>
				<div class="form-group" data-bind="visible: visibleTryInterval">
					<label class="control-label col-xs-4">Time auto</label>
					<div class="col-xs-8">
						<input data-role="numerictextbox" data-format="n0" style="width: 100%"
						data-bind="value: item.tryInterval">
					</div>
				</div>
				<div class="form-group text-center">
					<button data-role="button" data-bind="click: save">@Save@</button>
					<button data-role="button" class="btn-primary" data-bind="click: saveAndGoToImport">@Save@ @and@ @Import@</button>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="col-sm-6">
	<div class="block" style="margin-top: 20px">
		<div class="block-title">
			<h2><strong>@Campaign fields@</strong></h2>
		</div>
		<div class="block-content" style="min-height: 50vh; overflow-y: scroll; overflow-x: hidden">
			<div class="form-horizontal" data-bind="source: item.columns" data-template="column-template">
			</div>
		</div>
	</div>
</div>

<script id="column-template" type="text/x-kendo-template">
    <div class="form-group">
		<label class="control-label col-sm-4 hidden"><span data-bind="text: field"></span></label>
		<div class="col-sm-7 col-sm-offset-1">
			<input class="k-textbox" style="width: 100%" data-bind="value: title">
		</div>
		<label class="control-label col-sm-3 hidden">
			<span class="label label-info" data-bind="text: type"></span>
		</label>
	</div>
</script>

<script id="member-template" type="text/x-kendo-template">
    <div class="member-element"><span style="background-image: url('/api/v1/avatar/agent/#: data #')"></span><span><b>#: data #</b></span></div>
</script>

<style type="text/css">
	.member-element {
		width: 64px;
	}
</style>

<script type="text/javascript">
	$.get(ENV.vApi + "diallist/diallistdetailfield/1", function(res) {
		var model = {
			item: {columns: res.data, target: 90, mode: "manual"},
			modeOption: dataSourceJsonData(["Diallist","mode"]),
			/*teamOption: dataSourceJsonData(["Collection","team"]),
			teamChange: function(e) {
				let value = e.sender.value();
				this.groupOption.filter({field: "name", operator: "contains", value: value});
				this.groupOption.read().then(() => {
					if(this.groupOption.data().length)
						$("#group-select").data("kendoDropDownList").select(0);
				});
				if(value == "Main") {
					this.set("visibleMinOutstanding", true);
					this.set("item.minOutstanding", 40000);
				} else {
					this.set("visibleMinOutstanding", false);
					this.set("item.minOutstanding", undefined);
				}
			},*/
			groupOption: dataSourceDropDownList("Group", ["name", "members"], {members: {$exists: true}, type: "custom"}),
			groupCascade: function(e) {
				if(e.sender.dataItem()) {
					this.set("item.members", e.sender.dataItem().members);
					this.set("item.group_name", e.sender.dataItem().name);
					this.set("item.group_id", e.sender.dataItem().id);
				}
			},
			membersOption: new kendo.data.DataSource({
                transport: {
                    read: ENV.vApi + "select/queuemembers",
                    parameterMap: parameterMap
                },
                schema: {
                    data: "data",
                    total: "total"
                }
            }),
			save: function() {
				var data = this.item.toJSON();
				$.ajax({
					url: `${ENV.restApi}diallist`,
					type: "POST",
					contentType: "application/json; charset=utf-8",
					data: JSON.stringify(data),
					success: function(response) {
						if(response.status) {
							syncDataSource();
							router.navigate(`/`);
						}
					},
					error: errorDataSource
				})
			},
			saveAndGoToImport: function() {
				var data = this.item.toJSON();
				$.ajax({
					url: `${ENV.restApi}diallist`,
					type: "POST",
					contentType: "application/json; charset=utf-8",
					data: JSON.stringify(data),
					success: function(response) {
						if(response.status) {
							syncDataSource();
							var id = response.data.id;
							router.navigate(`/import_from_basket/${id}`);
						}
					},
					error: errorDataSource
				})
			}
		};

		kendo.bind("#bottom-row", kendo.observable(model));
	});
</script>
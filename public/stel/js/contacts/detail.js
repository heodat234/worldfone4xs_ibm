var $scope = kendo.observable({});

async function init() {
	var lead = await $.ajax({
	    url: Config.crudApi + Config.collection + "/" + Config.id,
	    error: errorDataSource
	});
	$scope.set("dataItem", lead);
	var user = await $.get(Config.crudApi + "users" + "/" + lead.owner_id); 
	var model = Object.assign(lead, {
		name: lead.first_name + " " + lead.last_name,
		statusConverted: lead.converted ? "Warning: This lead was converted." : "",
		job: (lead.job_title?lead.job_title+", ":"") + (lead.department?lead.department:""),
		location: (lead.city?lead.city+", ":"") + (lead.country?lead.country:""),
		owner: user.first_name + " " + user.last_name
	})
    kendo.bind($("#page-container"), model);
};

init();

async function editForm() {
	var formHtml = await $.ajax({
	    url: Config.templateApi + Config.collection + "/form",
	    error: errorDataSource
	});
	var model = Object.assign({
		item: $scope.dataItem,
		save: function() {
			$.ajax({
				url: Config.crudApi + Config.collection + "/" + Config.id,
				data: this.item.toJSON(),
				type: "PUT",
				success: function() {
					syncDataSource();
					init();
				},
				error: errorDataSource
			});
		}
	}, Config.observable);
	kendo.destroy($("#right-form"));
	$("#right-form").empty();
	var kendoView = new kendo.View(formHtml, { wrap: false, model: model, evalTemplate: false });
	kendoView.render($("#right-form"));
}

async function convertForm() {
	var formHtml = await $.ajax({
	    url: Config.templateApi + Config.collection + "/convert",
	    error: errorDataSource
	});
	var model = Object.assign({
		item: $scope.dataItem,
		save: function() {
			$.ajax({
				url: Config.crudApi + "action/convert/" + Config.id,
				data: this.item.toJSON(),
				type: "PUT",
				success: function() {
					syncDataSource();
					init();
				},
				error: errorDataSource
			});
		}
	}, Config.observable);
	kendo.destroy($("#right-form"));
	$("#right-form").empty();
	var kendoView = new kendo.View(formHtml, { wrap: false, model: model, evalTemplate: false });
	kendoView.render($("#right-form"));
}
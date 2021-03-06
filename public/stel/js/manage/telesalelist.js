var layoutViewModel = kendo.observable({
	breadcrumb: "",
	activeArray: [],
	buttonSelect: "#top-row .btn-group",
	init: function() {
		var hash = (window.location.hash || "#/").toString(),
			$currentTarget = $(this.buttonSelect).find(`button[href='${hash}']`),
			index = $(this.buttonSelect).find("button").index($currentTarget);
		this.set("activeArray", new Array($(this.buttonSelect).find("button").length));
		this.set("breadcrumb", $currentTarget.text());
		this.setActive(index);
	},
	goTo: function(e) {
		var $currentTarget = $(e.currentTarget);
		var index = $(this.buttonSelect).find("button").index($currentTarget);
		var nav = $currentTarget.attr("href");
		if(nav) {
			router.navigate(nav);

			this.set("breadcrumb", $currentTarget.text());
			if(index > -1) this.setActive(index);
		}
	},
	setActive: function(index) {
		for (var i = 0; i < this.activeArray.length; i++) {
			if(i == index)
				this.set(`activeArray[${i}]`, true);
			else this.set(`activeArray[${i}]`, false);
		}
	},
	hasDetail: false,
	telesaleList: [],
	addTelesalelistList: function(telesaleList) {
		var link = ENV.currentUri + '/#/detail/' + telesaleList.id;
		var check = this.telesaleList.find(obj => obj.id == telesaleList.id);
		if(!check) {
			this.telesaleList.push({id: telesaleList.id, url: link, name: telesaleList.name, active: true})
		}
		for (var i = 0; i < this.telesaleList.length; i++) {
			this.set(`diallistList[${i}].active`, (this.telesaleList[i].id == telesaleList.id) ? true : false);
		}
		this.set("hasDetail", true);
	}
})

// views, layouts
var layout = new kendo.Layout(`layout`, {model: layoutViewModel, wrap: false , init: layoutViewModel.init.bind(layoutViewModel)});

// routing
var router = new kendo.Router({routeMissing: function(e) { router.navigate("/") }});

router.bind("init", function() {
    layout.render($("#page-content"));
});

router.route("/", async function() {
	var HTML = await $.get(`${Config.templateApi}telesalelist/overview`);
	var kendoView = new kendo.View(HTML, { model: {}, template: false, wrap: false });
    await layout.showIn("#bottom-row", kendoView);
    var widget = await $.get(`${Config.templateApi}telesalelist/widget`);
    await $("#page-widget").html(widget);
    // if(Table.dataSource !== null && Table.dataSource !== []) {
    //     var customFilter = document.getElementById('custom-filter');
    //     customFilter.click();
    // }
});

router.route("/detail/:id", async function(id) {
	layoutViewModel.setActive(1);
	var dataItemFull = await $.get(`${ENV.restApi}import_history/${id}`);
	if(!dataItemFull) {
		notification.show("Can't find Detail List", "error");
		return;
	}
	// layoutViewModel.addTelesalelistList(dataItemFull);
	layoutViewModel.set("breadcrumb", dataItemFull.file_name);
	var HTML = await $.get(`${Config.templateApi}telesalelist/overview?id=${id}`);
	var kendoView = new kendo.View(HTML, { model: {}, template: false, wrap: false });
    await layout.showIn("#bottom-row", kendoView);
    var widget = await $.get(`${Config.templateApi}telesalelist/widget`);
    await $("#page-widget").html(widget);
});

router.route("/import", async function() {
	var HTML = await $.get(`${Config.templateApi}telesalelist/import`);
	var kendoView = new kendo.View(HTML);
	layout.showIn("#bottom-row", kendoView);
});

router.route("/history", async function() {
	var HTML = await $.get(`${Config.templateApi}telesalelist/history`);
	var kendoView = new kendo.View(HTML);
	layout.showIn("#bottom-row", kendoView);
});

router.route("/divide/:id", async function(id) {
	layoutViewModel.setActive(1);
	var dataItemFull = await $.get(`${ENV.restApi}import_history/${id}`);
	if(!dataItemFull) {
		notification.show("Can't find Divide List", "error");
		return;
	}
	layoutViewModel.set("breadcrumb", `Divide List`);
	var HTML = await $.get(`${Config.templateApi}telesalelist/divide_list?id=${id}`);
	var model = {
	}
	var kendoView = new kendo.View(HTML, { model: model, template: false, wrap: false });
    layout.showIn("#bottom-row", kendoView);
});

router.start();

async function addForm() {
	var formHtml = await $.ajax({
	    url: Config.templateApi + "telesalelist/form",
	    error: errorDataSource
	});
	var model = Object.assign(Config.observable, {
		item: {},
		save: function() {
			Table.dataSource.add(this.item);
			Table.dataSource.sync().then(() => {Table.dataSource.read()});
		}
	});
	kendo.destroy($("#right-form"));
	$("#right-form").empty();
	var kendoView = new kendo.View(formHtml, { wrap: false, model: model, evalTemplate: false });
	kendoView.render($("#right-form"));
	router.navigate(`/`);
}

document.onkeydown = function(evt) {
    evt = evt || window.event;
    if (evt.keyCode == 27) {
    	router.navigate(`/`);
    	layoutViewModel.init();
    }
};

function gridCallResult(data) {
	var htmlArr = [];
    if(data) {
    	data.forEach(doc => {
    		htmlArr.push(`<a href="javascript:void(0)" class="label label-${(doc.disposition == "ANSWERED")?'success':'warning'}" 
    			title="${kendo.toString(new Date(doc.starttime * 1000), "dd/MM/yy H:mm:ss")}">${doc.disposition}</a>`);
    	})
    }
    return htmlArr.join("<br>");
}
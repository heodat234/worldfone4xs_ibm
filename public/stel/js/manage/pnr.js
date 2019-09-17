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
	pnr_code: '',
	detailList: [],
	addDetail: function(ticket) {
		var link = ENV.currentUri + '/#/detail/' + ticket.id;
		var check = this.detailList.find(obj => obj.id == ticket.id);
		if(!check) {
			this.detailList.push({id: ticket.id, url: link, name: ticket.ticket_id, active: true})
		}
		for (var i = 0; i < this.detailList.length; i++) {
			this.set(`detailList[${i}].active`, (this.detailList[i].id == ticket.id) ? true : false);
		}
		this.set("hasDetail", true);
	},
})

// views, layouts
var layout = new kendo.Layout(`layout`, {model: layoutViewModel, wrap: false , init: layoutViewModel.init.bind(layoutViewModel)});

// routing
var router = new kendo.Router({routeMissing: function(e) { router.navigate("/") }});

router.bind("init", function() {
    layout.render($("#page-content"));
});

router.route("/", async function() {
	var HTML = await $.get(`${Config.templateApi}${Config.collection}/overview`);
	var kendoView = new kendo.View(HTML, { model: {}, template: false, wrap: false });
    layout.showIn("#pnr-body", kendoView);
    /* var widget = await $.get(`${Config.templateApi}${Config.collection}/widget`);
    $("#page-widget").html(widget); */
});

router.route("/detail/:id", async function(id) {
	layoutViewModel.setActive(1);
	var dataItemFull = await $.get(`${ENV.vApi}${Config.collection}/checkPNR/${id}`);
	var dataItemFullJSON = JSON.parse(dataItemFull);
	if(!dataItemFull) {
		notification.show("Can't find PNR Info", "error");
		return;
	}
	/* layoutViewModel.addDetail(dataItemFull); */
	/* layoutViewModel.set("breadcrumb", `${dataItemFull.ticket_id}`); */
	layoutViewModel.set("pnr_code", id);
	if(dataItemFullJSON.fromLocal) {
		var HTML = await $.get(`${Config.templateApi}${Config.collection}/local?id=${id}`);
	}
	else {
		var HTML = await $.get(`${Config.templateApi}${Config.collection}/api?id=${id}`);
	}
	var model = {
	};
	var kendoView = new kendo.View(HTML, { model: model, template: false, wrap: false });
    layout.showIn("#pnr-body", kendoView);
});

router.start();

document.onkeydown = function(evt) {
    evt = evt || window.event;
    if (evt.keyCode == 27) {
    	router.navigate(`/`);
    	layoutViewModel.init();
    }
};
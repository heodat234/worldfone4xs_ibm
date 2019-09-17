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
	customerDetailList: [],
	addCustomerDetail: function(customer) {
		var link = ENV.currentUri + '/#/detail/' + customer.id;
		var check = this.customerDetailList.find(obj => obj.id == customer.id);
		if(!check) {
			this.customerDetailList.push({id: customer.id, url: link, name: customer.name, active: true})
		}
		for (var i = 0; i < this.customerDetailList.length; i++) {
			this.set(`customerDetailList[${i}].active`, (this.customerDetailList[i].id == customer.id) ? true : false);
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
	var HTML = await $.get(`${Config.templateApi}browse/overview`);
	var kendoView = new kendo.View(HTML, { model: {}, template: false, wrap: false });
    layout.showIn("#bottom-row", kendoView);
});

router.route("/sms", async function() {
	var HTML = await $.get(`${Config.templateApi}browse/sms`);
	var kendoView = new kendo.View(HTML, { model: {}, template: false, wrap: false });
    layout.showIn("#bottom-row", kendoView);
});

router.route("/email", async function() {
	var HTML = await $.get(`${Config.templateApi}browse/email`);
	var kendoView = new kendo.View(HTML, { model: {}, template: false, wrap: false });
    layout.showIn("#bottom-row", kendoView);
});

router.start();
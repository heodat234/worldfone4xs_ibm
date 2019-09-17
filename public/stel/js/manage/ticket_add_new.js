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
});

router.start();

async function addForm(fromPage) {
	var formHtml = await $.ajax({
		url: Config.templateApi + Config.collection + "/form",
		error: errorDataSource
	});
	var model = Object.assign(Config.observable, {
		fromPage: fromPage,
		ticketInfo: {}
	});
	kendo.destroy($("#right-form"));
	$("#right-form").empty();
	var kendoView = new kendo.View(formHtml, { wrap: false, model: model, evalTemplate: false });
	kendoView.render($("#right-form"));
	kendo.bind("#right-form", kendo.observable(model.ticketInfo));
}

document.onkeydown = function(evt) {
    evt = evt || window.event;
    if (evt.keyCode == 27) {
    	router.navigate(`/`);
    	layoutViewModel.init();
    }
};

function dataSourceService(level=1, parent_id=null) {
    return new kendo.data.DataSource({
        transport: {
            read: {
                url: `${ENV.restApi}servicelevel`,
                data: {id: parent_id, "lv": level}
            },
            parameterMap: parameterMap
        }
    })
}
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
	diallistList: [],
	addDiallistList: function(diallist) {
		var link = ENV.currentUri + '/#/detail/' + diallist.id;
		var check = this.diallistList.find(obj => obj.id == diallist.id);
		if(!check) {
			this.diallistList.push({id: diallist.id, url: link, name: diallist.name, active: true})
		}
		for (var i = 0; i < this.diallistList.length; i++) {
			this.set(`diallistList[${i}].active`, (this.diallistList[i].id == diallist.id) ? true : false);
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
	var HTML = await $.get(`${Config.templateApi}diallist/overview`);
	var kendoView = new kendo.View(HTML, { model: {}, template: false, wrap: false });
    layout.showIn("#bottom-row", kendoView);
});

router.route("/create", async function() {
	var HTML = await $.get(`${Config.templateApi}diallist/create`);
	var kendoView = new kendo.View(HTML, {model: {}});
    layout.showIn("#bottom-row", kendoView);
});

router.route("/detail/:id", async function(id) {
	layoutViewModel.setActive(1);
	var dataItemFull = await $.get(`${ENV.restApi}diallist/${id}`);
	if(!dataItemFull) {
		notification.show("Can't find diallist", "error");
		return;
	}
	layoutViewModel.addDiallistList(dataItemFull);
	layoutViewModel.set("breadcrumb", `${dataItemFull.name}`);
	var HTML = await $.get(`${Config.templateApi}diallist/detail?id=${id}`);
	var model = {
	}
	var kendoView = new kendo.View(HTML, { model: model, template: false, wrap: false });
    layout.showIn("#bottom-row", kendoView);
});

router.route("/import/:id", async function(id) {
	var dataItemFull = await $.get(`${ENV.restApi}diallist/${id}`);
	if(!dataItemFull) {
		notification.show("Can't find diallist", "error");
		return;
	}
	layoutViewModel.setActive(-1);
	layoutViewModel.set("breadcrumb", (dataItemFull.name || "").toString());
	var HTML = await $.get(`${Config.templateApi}diallist/import`);
	var model = {
		file: {},
		item: dataItemFull,
		extensions: [],
		group: {members: []},
		visibleData: false,
		data: new kendo.data.DataSource(),
		originalDataColumns: [],
		dataColumns: [],
		moveDataColumns: function(oldIndex, newIndex) {
			var columns = this.dataColumns.slice(0);
			var column = columns[oldIndex];
			if(newIndex > oldIndex) {
				columns.splice(newIndex + 1, 0, column);
				columns.splice(oldIndex, 1);
			} else {
				columns.splice(oldIndex, 1);
				columns.splice(newIndex, 0, column);
			}
			columns.map((ele, idx) => {
				ele.index = idx; ele.title = this.originalDataColumns[idx].title + ` (${ele.field})`;
			});
			this.set("dataColumns", columns);
			return columns;
		},
		visibleAssign: false,
		goToAssign: function(e) {
			this.set("visibleAssign", true);
			this.goToAssignAsync();
		},
		goToAssignAsync: async function() {
			var group = await $.get(ENV.restApi + "group/" + dataItemFull.group_id);
			this.set("extensions", group.members);
			this.set("group", group);
		},
		import: function() {
			swal({
                title: "Are you sure?",
                text: `Import this data.`,
                icon: "warning",
                buttons: true,
                dangerMode: false,
            })
            .then(sure => {
            	if(sure) {
					var fieldArray = this.item.columns.slice(0).map(ele => ele.field),
						dataColumns = this.get("dataColumns"),
						data = this.data.data().toJSON(),
						file = {},
						extensions = this.get("extensions");
					["lastModified", "name", "size", "type"].forEach(field => {
						if(this.file[field])
							file[field] = this.file[field];
					});
					data.map((doc, idx) => {
						for (var index = 0; index < dataColumns.length; index++) {
							if(dataColumns[index].field) {
								doc[fieldArray[index]] = doc[dataColumns[index].field];
								delete doc[dataColumns[index].field];
							}
						}
						doc.diallist_id = id;
						doc.assign = extensions[idx % extensions.length];
						doc.imported_file_info = file;
					})
					this.save(data);
				}
			})
		},
		save: function(data) {
			$.ajax({
				url: `${ENV.restApi}diallist_detail`,
				type: "PATCH",
				data: kendo.stringify(data),
				contentType: "application/json; charset=utf-8",
				success: function() {
					syncDataSource();
					router.navigate(`/detail/${id}`);
				},
				error: errorDataSource
			})
		}
	};
	var kendoView = new kendo.View(HTML, {model: kendo.observable(model)});
    layout.showIn("#bottom-row", kendoView);
});

router.route("/config", async function() {
	var HTML = await $.get(`${Config.templateApi}diallist/config`);
	var kendoView = new kendo.View(HTML, {model: {}});
    layout.showIn("#bottom-row", kendoView);
});

router.start();

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
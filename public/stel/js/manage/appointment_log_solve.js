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
    var HTML = await $.get(`${Config.templateApi}${Config.collection}/overview`);
    var kendoView = new kendo.View(HTML, { model: {}, template: false, wrap: false });
    layout.showIn("#bottom-row", kendoView);
    var widget = await $.get(`${Config.templateApi}${Config.collection}/widget`);
    await $("#page-widget").html(widget);
});

router.route("/detail/:id", async function(id) {
    layoutViewModel.setActive(1);
    var dataItemFull = await $.get(`${ENV.vApi}dealer/importHistoryById/${id}`);
    if(!dataItemFull) {
        notification.show("Can't find ticket", "error");
        return;
    }
    layoutViewModel.addDetail(dataItemFull);
    layoutViewModel.set("breadcrumb", `${dataItemFull.file_name}`);
    var HTML = await $.get(`${Config.templateApi}${Config.collection}/detail?id=${id}`);
    var model = {
    }
    var kendoView = new kendo.View(HTML, { model: model, template: false, wrap: false });
    layout.showIn("#bottom-row", kendoView);
});

router.route("/setting", async function() {
    var HTML = await $.get(`${Config.templateApi}${Config.collection}/setting`);
    var kendoView = new kendo.View(HTML, {model: {}});
    layout.showIn("#bottom-row", kendoView);
});

router.route("/import", async function() {
    var HTML = await $.get(`${Config.templateApi}${Config.collection}/import`);
    var kendoView = new kendo.View(HTML, {model: {}});
    layout.showIn("#bottom-row", kendoView);
});

router.route("/history", async function() {
    var HTML = await $.get(`${Config.templateApi}${Config.collection}/history`);
    var kendoView = new kendo.View(HTML, {model: {}});
    layout.showIn("#bottom-row", kendoView);
});

router.start();

async function addForm() {
    console.log(Config);
    var formHtml = await $.ajax({
        url: Config.templateApi + Config.collection + "/form",
        error: errorDataSource
    });
    var model = Object.assign(Config.observable, {
        item: {},
        save: function() {
            var item = this.get('item');
            var appointment_date = new Date(this.item.appointment_date);
            appointment_date.setHours(0, 0, 0, 0);
            item.appointment_date = appointment_date.getTime() / 1000;
            $.ajax({
                url: ENV.vApi + "appointment_log_solve/create",
                data: kendo.stringify(item.toJSON()),
                error: errorDataSource,
                contentType: "application/json; charset=utf-8",
                type: "PUT",
                success: function() {
                    closeForm();
                    Table.dataSource.sync().then(() => {Table.dataSource.read()});
                }
            });
        }
    });
    kendo.destroy($("#right-form"));
    $("#right-form").empty();
    var kendoView = new kendo.View(formHtml, { wrap: false, model: model, evalTemplate: false });
    kendoView.render($("#right-form"));
}

function deleteDataItem(ele) {
    swal({
        title: "@Are you sure@?",
        text: "@Once deleted, you will not be able to recover this document@!",
        icon: "warning",
        buttons: true,
        dangerMode: true,
    })
        .then((willDelete) => {
            if (willDelete) {
                var uid = $(ele).data('uid');
                var dataItem = Table.dataSource.getByUid(uid);
                Table.dataSource.remove(dataItem);
                Table.dataSource.sync();
            }
        });
}

document.onkeydown = function(evt) {
    evt = evt || window.event;
    if (evt.keyCode == 27) {
        router.navigate(`/`);
        layoutViewModel.init();
    }
};
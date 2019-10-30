<style>
    .badge.badge-pill:hover {
        background-color: #1bbae1;
    }

    #top-row .list-group-item {
        display: inline-block;
        padding: 1px 5px;
    }
</style>
<script>
    var Config = {
        crudApi: `${ENV.restApi}`,
        templateApi: `${ENV.templateApi}`,
        observable: {
            scrollTo: function(e) {
                var id = $(e.currentTarget).data('id');
                $("#main-form").animate({scrollTop: $("#"+id).position().top + $("#main-form").scrollTop()});
            },
            searchField: function(e) {
                var search = e.currentTarget.value;
                var formGroup = $("#main-form .form-group");
                for (var i = 0; i < formGroup.length; i++) {
                    var regex = new RegExp(search, "i");
                    var test = regex.test($(formGroup[i]).data("field")) ? true : false;
                    if(test) 
                        $(formGroup[i]).show();
                    else $(formGroup[i]).hide();
                }
            },
            otherPhonesOpen: function(e) {
                e.preventDefault();
                var widget = e.sender;
                widget.input[0].onkeyup = function(ev) {
                    if(ev.keyCode == 13) {
                        var values = widget.value();
                        values.push(this.value);
                        widget.dataSource.data(values);
                        widget.value(values);
                        widget.trigger("change");
                    }
                }
            }
        },
        filterable: KENDO.filterable
    }
    window.onload = function() {
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
            addCustomerList: function(telesaleList) {
                var link = ENV.currentUri + '/#/detail_customer/' + telesaleList.id;
                var check = this.telesaleList.find(obj => obj.id == telesaleList.id);
                if(!check) {
                    this.telesaleList.push({id: telesaleList.id, url: link, name: telesaleList.customer_name, active: true})
                }
                for (var i = 0; i < this.telesaleList.length; i++) {
                    this.set(`telesaleList[${i}].active`, (this.telesaleList[i].id == telesaleList.id) ? true : false);
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
            var HTML = await $.get(`${Config.templateApi}lawsuit_report/import`);
            var kendoView = new kendo.View(HTML, { model: {}, template: false, wrap: false });
            layout.showIn("#bottom-row", kendoView);
        });

        router.route("/history", async function() {
            var HTML = await $.get(`${Config.templateApi}lawsuit_report/history`);
            var kendoView = new kendo.View(HTML);
            layout.showIn("#bottom-row", kendoView);
        });

        router.start();

    
    }
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
</script>

<script id="layout" type="text/x-kendo-template">
    <ul class="breadcrumb breadcrumb-top">
        <li>@Manage@</li>
        <li>@Lawsuit Report@</li>
        <li data-bind="text: breadcrumb"></li>
        <li class="pull-right none-breakcrumb" id="top-row">
        	<div class="btn-group btn-group-sm">
                <button href="#/import" class="btn btn-alt btn-default" data-bind="click: goTo, css: {active: activeArray[0]}">@Import@</button>
                <button href="#/history" class="btn btn-alt btn-default" data-bind="click: goTo, css: {active: activeArray[1]}">@Import History@</button>
            </div>
        </li>
    </ul>
	<div class="container-fluid">
        <div class="row" id="bottom-row"></div>
    </div>
</script>
<script id="detail-dropdown-template" type="text/x-kendo-template">
	<li data-bind="css: {dropdown-header: active}"><a data-bind="click: goTo, text: name, attr: {href: url}"></a></li>
</script>
<script type="text/x-kendo-template" id="diallist-detail-field-template">
	<div class="item">
        <span style="margin-left: 10px" data-bind="text: title"></span>
        <i class="fa fa-arrow-circle-o-right text-success" style="float: right; margin-top: 10px"></i>
    </div>
</script>
<script type="text/x-kendo-template" id="data-field-template">
	<div class="item">
		<span class="handler text-center"><i class="fa fa-arrows-v"></i></span>
        <span data-bind="text: field"></span>
    </div>
</script>

<style>
    #top-row .list-group-item {
        display: inline-block;
        padding: 1px 5px;
    }
</style>
<script>
    var Config = {
        crudApi: `${ENV.vApi}`,
        templateApi: `${ENV.templateApi}`,
        collection: "wfpbx",
        observable: {
        }
    }
</script>
<script id="layout" type="text/x-kendo-template">
    <ul class="breadcrumb breadcrumb-top">
        <li>@Manage@</li>
        <li>@Monitor@</li>
        <li data-bind="text: breadcrumb"></li>
        <li class="pull-right none-breakcrumb" id="top-row">
            <div class="btn-group btn-group-sm">
                <button href="#/" class="btn btn-alt btn-default" data-bind="click: goTo, css: {active: activeArray[0]}">@Overview@</button>
                <button href="#/callin" class="btn btn-alt btn-default" data-bind="click: goTo, css: {active: activeArray[1]}">@Call in@</button>
                <button href="#/callout" class="btn btn-alt btn-default" data-bind="click: goTo, css: {active: activeArray[2]}">@Call out@</button>
                <button href="#/activity" class="btn btn-alt btn-default" data-bind="click: goTo, css: {active: activeArray[3]}">@Activity@</button>
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
<script type="text/x-kendo-template" id="data-field-template">
    <div class="item">
        <span class="handler text-center"><i class="fa fa-arrows-v"></i></span>
        <span data-bind="text: field"></span>
    </div>
</script>   

<script type="text/javascript">
window.onload = function() {
    var layoutViewModel = window.layoutViewModel = kendo.observable({
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
                // Clear interval if exists
                if(typeof window.overviewInterval1 != 'undefined') clearInterval(window.overviewInterval1);
                if(typeof window.overviewInterval2 != 'undefined') clearInterval(window.overviewInterval2);
                if(typeof window.intervalCallStatistic != 'undefined') clearInterval(window.intervalCallStatistic);
                // Navigate
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
            var link = '#/detail/' + customer.id;
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
    var router = window.router = new kendo.Router({routeMissing: function(e) { router.navigate("/") }});

    router.bind("init", function() {
        layout.render($("#page-content"));
    });

    router.route("/", async function() {
        var HTML = await $.get(`${Config.templateApi}monitor/loan/overview`);
        var model = {
            toggleFavorite: function(e) {
                var $target     = $(e.currentTarget),
                    id          = $target.data("id"),
                    extension   = $target.data("extension"),
                    add         = !$target.hasClass("text-danger");
                swal({
                    title: "@Favorite@ @extension@",
                    text: add ? `@Add@ ${extension} @to@ @favorite list@.` : `@Remove@ ${extension} @from@ @favorite list@.`,
                    icon: "warning",
                    buttons: true,
                    dangerMode: false,
                })
                .then((sure) => {
                    if (sure) {
                        $.ajax({
                            url: `${ENV.restApi}user/${id}`,
                            type: "PUT",
                            contentType: "application/json; charset=utf-8",
                            data: JSON.stringify({favorite: add}),
                            success: syncDataSource,
                            error: errorDataSource
                        })
                    }
                });
            },
            changeStatusExtension: function(e) {
                changeStatusExtension(e);
            },
            groupFilterChange: function(e){
                var selectedItems   = e.sender.select(),
                    currentFilter   = List.dataSource.filter();
                if(selectedItems.length) {
                    if(!selectedItems.hasClass("selected")) {
                        selectedItems.addClass("selected")
                        var dataItem  = e.sender.dataItem(selectedItems).toJSON(),
                            members   = dataItem.members;
                        var filters = members.map(extension => {
                            return {
                                field: "extension",
                                operator: "eq",
                                value: extension
                            }
                        })
                        if(currentFilter) {
                            var hasFilterGroup = false;
                            currentFilter.filters.forEach((filter, index) => {
                                if(filter.type == "group") {
                                    currentFilter.filters[index].filters = filters;
                                    hasFilterGroup = true;
                                }
                            });
                            if(!hasFilterGroup) {
                                currentFilter.filters.push({
                                    type: "group",
                                    logic: "or",
                                    filters: filters
                                });
                            }
                        } else {
                            currentFilter = {
                                logic: "and",
                                filters: [
                                    {logic: "or", filters: filters, type: "group"}
                                ]
                            }
                        }
                    } else {
                        selectedItems.removeClass("selected");
                        e.sender.clearSelection();
                        if(currentFilter) {
                            currentFilter.filters = currentFilter.filters.filter(doc => doc.type != "group");
                        }
                    }
                    List.dataSource.filter(currentFilter);
                }
            },
            filterFavorite: function(e) {
                var currentFilter   = List.dataSource.filter(),
                    $currentTarget  = $(e.currentTarget),
                    value           = Number($currentTarget.data("value"));

                if(!$currentTarget.hasClass("label-danger")) {
                    $(".filter-favorite").removeClass("label-danger").addClass("label-default");
                    $currentTarget.removeClass("label-default").addClass("label-danger");
                    if(currentFilter) {
                        currentFilter.filters = currentFilter.filters.filter(doc => doc.field != "favorite");
                        currentFilter.filters.push({
                            field: "favorite",
                            operator: value ? "eq" : "neq",
                            value: true
                        })
                    } else {
                        currentFilter = {
                            logic: "and",
                            filters: [
                                {
                                    field: "favorite",
                                    operator: value ? "eq" : "neq",
                                    value: true
                                }
                            ]
                        }
                    }
                } else {
                    $currentTarget.removeClass("label-danger").addClass("label-default");
                    if(currentFilter) {
                        currentFilter.filters = currentFilter.filters.filter(doc => doc.field != "favorite");
                    }
                }
                List.dataSource.filter(currentFilter);
            },
            displayGrid: Boolean(localStorage.getItem("displayGrid") == "true"),
            displayChange: function(e) {
                var selectedItems = e.sender.select(),
                    displayGrid = e.sender.dataItem(selectedItems).value;
                this.set("displayGrid", displayGrid);
                localStorage.setItem("displayGrid", displayGrid);
            }
        };
        var kendoView = new kendo.View(HTML, { model: model, evalTemplate: false, wrap: false });
        layout.showIn("#bottom-row", kendoView);
        $displayView = $("[data-template=display-list-template]");
        if(model.displayGrid) 
            $displayView.find("span.displayGrid").addClass("k-state-selected");
        else $displayView.find("span:not(.displayGrid)").addClass("k-state-selected");
    });

    router.route("/callin", async function(id) {
        var HTML = await $.get(`${Config.templateApi}monitor/loan/callin`);
        var model = {
            dataSource: new kendo.data.DataSource({
                serverAggregates: true,
                aggregate: [],
                transport: {
                    read: `${ENV.vApi}monitor/callin`,
                    parameterMap: parameterMap
                },
                schema: {
                    aggregates: "aggregates",
                    data: "data",
                    parse: function(response) {
                        response.aggregates = {did: "Total", waiting: 0, talking: 0, totalofferedcall: 0, totalabandonedcall: 0};
                        response.data.map(function(doc){
                            doc.abandonedcallrate = doc.totalofferedcall ? Math.floor(10000 * doc.totalabandonedcall / doc.totalofferedcall) / 100 : 0;
                            ["waiting","talking","totalofferedcall","totalabandonedcall"].forEach(field => {
                                response.aggregates[field] += doc[field];
                            })
                        }) 
                        response.aggregates.abandonedcallrate = response.aggregates.totalofferedcall ? Math.floor(10000 * response.aggregates.totalabandonedcall / response.aggregates.totalofferedcall) / 100 : 0;
                        return response;
                    }
                }
            }),
            abandonedDataSource: new kendo.data.DataSource({
                page: 1,
                serverPaging: true,
                serverFiltering: true,
                transport: {
                    read: `${ENV.vApi}monitor/abandonedcall`,
                    parameterMap: parameterMap
                },
                filter: [
                    {field: "direction", operator: "eq", value: "inbound"}
                ],
                schema: {
                    data: "data",
                    total: "total"
                }
            })
        };
        var kendoView = new kendo.View(HTML, {model: kendo.observable(model), evalTemplate: false, wrap: false });
        layout.showIn("#bottom-row", kendoView);
        window.intervalCallStatistic = setInterval(function(){
            model.dataSource.read();
            model.abandonedDataSource.read();
        }, 30000);
    });

    router.route("/callout", async function(id) {
        var HTML = await $.get(`${Config.templateApi}monitor/loan/callout`);
        var model = {
            dataSource: new kendo.data.DataSource({
                serverAggregates: true,
                aggregate: [],
                transport: {
                    read: `${ENV.vApi}monitor/callout`,
                    parameterMap: parameterMap
                },
                schema: {
                    aggregates: "aggregates",
                    data: "data",
                    parse: function(response) {
                        response.aggregates = {did: "Total", waiting: 0, talking: 0, totalofferedcall: 0, totalabandonedcall: 0};
                        response.data.map(function(doc){
                            doc.abandonedcallrate = doc.totalofferedcall ? Math.floor(10000 * doc.totalabandonedcall / doc.totalofferedcall) / 100 : 0;
                            ["waiting","talking","totalofferedcall","totalabandonedcall"].forEach(field => {
                                response.aggregates[field] += doc[field];
                            })
                        }) 
                        response.aggregates.abandonedcallrate = response.aggregates.totalofferedcall ? Math.floor(10000 * response.aggregates.totalabandonedcall / response.aggregates.totalofferedcall) / 100 : 0;
                        return response;
                    }
                }
            }),
            abandonedDataSource: new kendo.data.DataSource({
                page: 1,
                serverPaging: true,
                serverFiltering: true,
                transport: {
                    read: `${ENV.vApi}monitor/abandonedcall`,
                    parameterMap: parameterMap
                },
                filter: [
                    {field: "direction", operator: "eq", value: "outbound"}
                ],
                schema: {
                    data: "data",
                    total: "total"
                }
            })
        };
        var kendoView = new kendo.View(HTML, {model: kendo.observable(model)});
        layout.showIn("#bottom-row", kendoView);
        window.intervalCallStatistic = setInterval(function(){
            model.dataSource.read();
            model.abandonedDataSource.read();
        }, 30000);
    });

    router.route("/activity", async function(id) {
        var HTML = await $.get(`${Config.templateApi}monitor/loan/activity`);
        var kendoView = new kendo.View(HTML);
        layout.showIn("#bottom-row", kendoView);
    });

    router.start();
}

document.onkeydown = function(evt) {
    evt = evt || window.event;
    if (evt.keyCode == 27) {
        router.navigate(`/`);
        layoutViewModel.init();
    }
};
</script>
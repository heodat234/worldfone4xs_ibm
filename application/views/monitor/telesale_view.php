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
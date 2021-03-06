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
        collection: "sc",
        observable: {
        },
        filterable: KENDO.filterable,
    }
</script>
<script id="layout" type="text/x-kendo-template">
    <?php if(empty($only_main_content)) { ?>
        <ul class="breadcrumb breadcrumb-top">
            <li>@Manage@</li>
            <li>@SC Management@</li>
            <li>SC</li>
            <li data-bind="text: breadcrumb"></li>
            <li class="pull-right none-breakcrumb" id="top-row">
                <div class="btn-group btn-group-sm">
                    <button href="#/" class="btn btn-alt btn-default" data-bind="click: goTo, css: {active: activeArray[0]}">@Overview@</button>
                    <button href="#/import" data-type="action/import" class="btn btn-alt btn-default" data-type="action/import" data-bind="click: goTo, css: {active: activeArray[1]}">@Import@</button>
                    <button href="#/history" data-type="action/importhistory" class="btn btn-alt btn-default" data-type="action/import" data-bind="click: goTo, css: {active: activeArray[2]}">@Import history@</button>
                    <button class="btn btn-alt btn-default" data-type="create" onclick="openForm({title: '@Add@ @SC@', width: 500}); addForm(this)">@Create@</button>
                </div>
            </li>
        </ul>
    <?php } ?>
    <div class="container-fluid">
        <div id="bottom-row"></div>
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
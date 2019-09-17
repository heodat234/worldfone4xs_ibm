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
        crudApi: `${ENV.vApi}`,
        templateApi: `${ENV.templateApi}`,
        collection: "ticket_solve",
        fromvApi: true,
        observable: {
        },
        filterable: KENDO.filterable,
        userRole: "<?= $userRole ?>"
    }
</script>
<div id="page-content">
    <script id="layout" type="text/x-kendo-template">
        <?php if(empty($only_main_content)) { ?>
        <ul class="breadcrumb breadcrumb-top">
            <li>@Solve@</li>
            <li>@Ticket@</li>
            <li data-bind="text: breadcrumb"></li>
            <li class="pull-right none-breakcrumb" id="top-row">
                <div class="btn-group btn-group-sm">
                    <button href="#/" class="btn btn-alt btn-default" data-bind="click: goTo, css: {active: activeArray[0]}">@List@</button>
                    <button class="btn btn-alt btn-default" data-bind="css: {active: activeArray[1]}, visible: hasDetail" data-toggle="dropdown" id="btn-detail">@Detail@ (<span data-bind="text: detailList.length"></span>) <span class="caret"></span></button>
                    <ul class="dropdown-menu dropdown-custom dropdown-options" data-bind="source: detailList" data-template="detail-dropdown-template">
                    </ul>
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
</div>
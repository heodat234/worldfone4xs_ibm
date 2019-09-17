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
    }
</script>
<script id="layout" type="text/x-kendo-template">
    <ul class="breadcrumb breadcrumb-top">
        <li>@Manage@</li>
        <li>@Diallist@</li>
        <li data-bind="text: breadcrumb"></li>
        <li class="pull-right none-breakcrumb" id="top-row">
        	<div class="btn-group btn-group-sm">
                <button href="#/" class="btn btn-alt btn-default" data-bind="click: goTo, css: {active: activeArray[0]}">@Overview@</button>
                <button class="btn btn-alt btn-default" data-bind="css: {active: activeArray[1]}, visible: hasDetail" data-toggle="dropdown" id="btn-detail">@Detail@ (<span data-bind="text: diallistList.length"></span>) <span class="caret"></span></button>
                <ul class="dropdown-menu dropdown-custom dropdown-options" data-bind="source: diallistList" data-template="detail-dropdown-template">
			    </ul>
                <button href="#/create" class="btn btn-alt btn-default" data-bind="click: goTo, css: {active: activeArray[2]}">@Create@</button>
                <button href="#/config" class="btn btn-alt btn-default hidden" data-bind="click: goTo, css: {active: activeArray[3]}">@Config@</button>
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
<script id="column-template" type="text/x-kendo-template">
    <div class="form-group">
		<label class="control-label col-sm-4"><span data-bind="text: field"></span></label>
		<div class="col-sm-7">
			<input class="k-textbox" style="width: 100%" data-bind="value: title">
		</div>
	</div>
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
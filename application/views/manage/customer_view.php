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
        collection: "customer",
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
</script>
<script id="layout" type="text/x-kendo-template">
	<?php if(empty($only_main_content)) { ?>
    <ul class="breadcrumb breadcrumb-top">
        <li>@Manage@</li>
        <li>@Customer@</li>
        <li data-bind="text: breadcrumb"></li>
        <li class="pull-right none-breakcrumb" id="top-row">
        	<div class="btn-group btn-group-sm">
                <button href="#/" class="btn btn-alt btn-default" data-bind="click: goTo, css: {active: activeArray[0]}">@Overview@</button>
                <button class="btn btn-alt btn-default" data-bind="css: {active: activeArray[1]}, visible: hasDetail" data-toggle="dropdown" id="btn-detail">@Detail@ (<span data-bind="text: customerDetailList.length"></span>) <span class="caret"></span></button>
                <ul class="dropdown-menu dropdown-custom dropdown-options" data-bind="source: customerDetailList" data-template="detail-dropdown-template">
			    </ul>
			    <button href="#/import" class="btn btn-alt btn-default" data-bind="click: goTo, css: {active: activeArray[2]}">@Import@</button>
                <button class="btn btn-alt btn-default" onclick="openForm({title: '@Add@ @Customer@', width: 700}); addForm(this)">@Create@</button>
            </div>
        </li>
    </ul>
	<?php } ?>
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
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
    <ul class="breadcrumb breadcrumb-top">
        <li>@Manage@</li>
        <li>SMS & EMAIL</li>
        <li data-bind="text: breadcrumb"></li>
        <li class="pull-right none-breakcrumb" id="top-row">
        	<div class="btn-group btn-group-sm">
        		<button href="#/" class="btn btn-alt btn-default" data-bind="click: goTo, css: {active: activeArray[0]}">@Overview@</button>
                <button href="#/sms" class="btn btn-alt btn-default" data-bind="click: goTo, css: {active: activeArray[1]}">SMS</button>
			    <button href="#/email" class="btn btn-alt btn-default" data-bind="click: goTo, css: {active: activeArray[2]}">Email</button>
            </div>
        </li>
    </ul>
	<div class="container-fluid">
        <div class="row" id="bottom-row"></div>
    </div>
</script>
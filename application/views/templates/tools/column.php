<script id="column-template" type="text/x-kendo-template">
	<div class="col-xs-12" data-bind="visible: field, attr: {data-field: field}" style="padding-bottom: 5px; padding-top: 5px">
		<label style="line-height: 1.5">
	    	<input class="custom-checkbox" type="checkbox" data-bind="value: field, checked: visible, events: {change: change}">
	    	<span data-bind="html: title"></span>
	    </label>
	    <input data-role="slider"
           data-min="0"
           data-max="300"
           data-small-step="5"
           data-large-step="10"
           data-bind="value: width, events: {change: changeWidth}"
           style="width: 120px; float: right">
	</div>
</script>
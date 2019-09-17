<script id="column-template" type="text/x-kendo-template">
	<div class="checkbox col-xs-12" data-bind="visible: field" style="padding-bottom: 5px; padding-top: 5px">
	    <label style="line-height: 1.5"><input type="checkbox" data-bind="value: field, checked: visible, events: {change: change}"><span data-bind="html: title"></span></label>
	</div>
</script>
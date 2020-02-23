<style>
	.timeline-header {
		font-size: 13px;
	}
</style>
<div class="containter-fluid activity-mvvm">
</div>
<script type="text/x-kendo-template" id="activity-template">
    <div class="timeline block-content-full">
        <div class="timeline-header">
        	<div class="pull-left" style="font-size: 18px">
        		<span>(<span data-bind="text: total"></span>) </span>
        		<span>@Recent activities@&nbsp;</span>
        		<a href="javascript:void(0)" data-bind="click: refresh"><i class="gi gi-refresh"></i></a>
        	</div>
        	<div class="pull-right">
        		<label>@Filter@:</label>
        		<input data-role="dropdownlist"
        			data-value-primitive="true" 
        			data-text-field="text"
        			data-value-field="value"
        			data-bind="value: groupsAndExtensions, source: groupsAndExtensionsDataSource, events: {change: filterChange}"
        			/>
        	</div>
    	</div>
        <ul class="timeline-list timeline-hover" data-template="activity-timeline-template" data-bind="source: activityDataSource">
        </ul>
        <div class="text-center" style="margin-bottom: 10px" data-bind="visible: visibleViewMore">
            <a href="javascript:void(0)" data-bind="click: viewMore">@Show more@</a>
        </div>
    </div>
</script>
<script type="text/javascript">
	var activityDataSource = new kendo.data.DataSource({
			serverSorting: true,
			serverFiltering: true,
			serverPaging: true,
			sort: {field: "createdAt", dir: "desc"},
			pageSize: 5,
			transport: {
				read: ENV.vApi + "monitor/readActivity",
				parameterMap: parameterMap
			},
			schema: {
				data: "data",
				total: "total",
				parse: function(res) {
					res.data.map(doc => {
						doc.createdAtText = gridTimestamp(doc.createdAt);
						doc.ajaxs = [];
					})
					if(window.activityObservable) {
						window.activityObservable.set("visibleViewMore", Boolean(res.total > res.data.length));
						window.activityObservable.set("total", res.data.length);
					}
					return res;
				}
			}
		});

	activityDataSource.read().then(() => {
		var activityObservable = window.activityObservable = kendo.observable({
			total: activityDataSource.data().length,
			visibleViewMore: Boolean(activityDataSource.total() > activityDataSource.data().length),
			activityDataSource: activityDataSource,
			groupsAndExtensions: null,
			groupsAndExtensionsDataSource: new kendo.data.DataSource({
				transport: {
					read: ENV.vApi + "select/groups_and_extensions"
				},
				schema: {
					parse: function(res) {
						res.unshift({text: "@All@", value: null});
						return res;
					}
				}
			}),
			filterChange: function(e) {
				var values = e.sender.dataItem().value;
				if(values)
					this.activityDataSource.filter({field: "extension", operator: "in", value: values});
				else this.activityDataSource.filter({});
			},
			refresh: function() {
				this.activityDataSource.read();
			},
			viewMore: function() {
				this.activityDataSource.pageSize(this.activityDataSource.pageSize() + 5);
			},
			viewAjaxs: function(e) {
				var id = $(e.target).closest("li.active").data("id");
				$.get(ENV.vApi + "monitor/readActivityAjax", {id: id}, (res) => {
					if(res.total) {
						this.activityDataSource.get(id).set("ajaxs", res.data);
					}
				})
			}
		})
		var kendoView = new kendo.View($("#activity-template") , { model: activityObservable, template: false, wrap: false });
		kendoView.render($(".activity-mvvm"));
	})
	
</script>

<script type="text/x-kendo-template" id="activity-timeline-template">
    <li data-bind="css: {active: ajaxs_elapsed_time}, attr: {data-id: id}">
        <a #if(data.ajaxs_elapsed_time){# href="javascript:void(0)" data-bind="click: viewAjaxs" #}#>
            <div class="timeline-icon">
                <i class="#if(data.icon){##: data.icon ##}else{#fa fa-television#}#"></i>
            </div>
        </a>
        <div class="timeline-time">
            <span class="text-muted" data-bind="text: createdAtText"></span>
        </div>
        <div class="timeline-content row">
        	<div class="col-sm-4">
	            <p class="push-bit">
	            	<strong data-bind="text: extension"></strong>
	            	<span>-</span>
	            	<strong data-bind="text: agentname"></strong>
	            </p>
	            <p class="push-bit">
	                <span data-bind="text: definition"></span>
	            </p>
        	</div>
        	<div class="col-sm-8" style="border-left: 2px solid \#f0f0f0" data-template="ajax-template" data-bind="source: ajaxs"></div>
        </div>
    </li>
</script>
<script type="text/x-kendo-template" id="ajax-template">
	<p style="margin-bottom: 5px">
		<i>#= gridTimestamp(data.createdAt) #</i> - <b data-bind="text: definition"></b>
	</p>
</script>
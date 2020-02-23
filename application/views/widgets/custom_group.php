<li id="dropdown-group" class="dropdown" data-toggle="tooltip" data-placement="left" title="@Choose manual group@">
	<a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown" data-bind="click: refreshList">
    	<b><?= $this->session->userdata("group_name") ? $this->session->userdata("group_name") : "@Choose manual group@" ?></b>
    	<span class="caret"></span>
	</a>
	<ul class="dropdown-menu dropdown-custom dropdown-options" data-template="header-group-list-template" data-bind="source: groupListData" style="min-width: 180px; max-height: 80vh;">
	</ul>
</li>
<script id="header-group-list-template" type="text/x-kendo-template">
	<li data-bind="css: {dropdown-header: active, disabled: active}"><a href="javascript:void(0)" data-bind="text: name, click: chooseGroup, attr: {data-id: id, data-name: name}"></a></li>
</script>
<script>
var groupListFunction = function () {
	var group_id = '<?= $this->session->userdata("group_id") ?>';
    var $groupListElement = $("#dropdown-group");
    var groupListObservable = kendo.observable({
        groupListData: new kendo.data.DataSource({
        	serverFiltering: true,
        	filter: {
        		logic: "and",
        		filters: [
        			{field: "type", operator: "eq", value: "custom"},
        			{
        				logic: "or",
        				filters: [
        					{field: "lead", operator: "eq", value: ENV.extension},
        					{field: "members", operator: "in", value: ENV.extension}
        				]
        			}
        		]
        	},
            transport: {
                read: ENV.restApi + "group",
                parameterMap: parameterMap
            },
            schema: {
                data: "data",
                total: "total",
                parse: function(res) {
                	res.data.map(doc => {
                		doc.active = Boolean(doc.id == group_id);
                	})
                    return res;
                }
            }
        }),
        refreshList: function(e) {
            if(!$groupListElement.hasClass("open"))
                this.groupListData.read();
        },
        chooseGroup: function(e) {
        	$currentTarget = $(e.currentTarget);
        	if($currentTarget.closest("li").hasClass("disabled")) {
        		return;
        	}
        	let id = $currentTarget.data("id"),
        		name = $currentTarget.data("name");
        	$.ajax({
        		url: ENV.vApi + "widget/updateManualGroup",
        		data: {id: id, name: name},
        		type: "POST",
        		success: function(res) {
        			if(res.status)
        				location.reload();
        		}
        	})
        }
    });
    kendo.bind($groupListElement, groupListObservable);
}()
</script>
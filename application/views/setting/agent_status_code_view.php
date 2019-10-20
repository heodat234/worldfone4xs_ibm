<script>
var Config = {
    crudApi: `${ENV.vApi}`,
    templateApi: `${ENV.templateApi}`,
    collection: "agentstatuscode",
    observable: {
    	trueVar: true,
    	editSub: function(e) {
    		let data = this.dataSource.data(),
    			id = $(e.currentTarget).closest("div.view-container").data("id");
    		data.map(doc => doc.visibleEdit = Boolean(doc.id == id) )
    		this.dataSource.data(data);
    	},
    	cancelEdit: function(e) {
    		let data = this.dataSource.data();
    		data.map(doc => doc.visibleEdit = false )
    		this.dataSource.data(data);
    	},
    	saveEdit: function(e) {
    		var id = $(e.currentTarget).closest("div.view-container").data("id");
    		var text = $(e.currentTarget).closest("div.edit-container").find("input[name=text]").val();
            var sub = $(e.currentTarget).closest("div.edit-container").find("select[name=sub]").data("kendoMultiSelect").value();
            swal({
                title: "@Are you sure@?",
                text: `@Save this change@.`,
                icon: "warning",
                buttons: true,
                dangerMode: false,
            })
            .then((sure) => {
                if (sure) {
                    $.ajax({
                        url: `${ENV.vApi}agentstatuscode/update/${id}`,
                        type: "PUT",
                        contentType: "application/json; charset=utf-8",
                        data: JSON.stringify({text: text, sub: sub}),
                        success: () => {
                        	syncDataSource();
                        	this.dataSource.read();
                        },
                        error: errorDataSource
                    })
                }
            });
    	},
    	arrayOpen: function(e) {
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
    model: {
        id: "id",
        fields: {
            sub: {type: "object"}
        }
    }
}; 
</script>

<!-- Page content -->
<div id="page-content">
    <!-- Table Styles Header -->
    <ul class="breadcrumb breadcrumb-top">
        <li>@Setting@</li>
        <li>@Call status@</li>
    </ul>
    <!-- END Table Styles Header -->

    <div class="container-fluid">
        <h4 class="fieldset-legend" style="margin: 10px 0 30px"><span style="font-weight: 500">AGENT STATUS</span></h4>
        <div class="row">
            <div class="col-sm-12" style="height: 80vh; overflow-y: auto; padding: 0">
                <!-- Table Styles Content -->
                <div data-role="listview" id="listview"
                 data-template="template"
                 data-bind="source: dataSource"></div>
                <!-- END Table Styles Content -->
            </div>
        </div>
    </div>

    <div id="action-menu">
        <ul>
            <a href="javascript:void(0)" data-type="update" onclick="openForm({title: `Edit Group`,width: 400}); editForm(this)"><li><i class="fa fa-pencil-square-o text-warning"></i><span>Edit</span></li></a>
            <a href="javascript:void(0)" data-type="delete" onclick="deleteDataItem(this)"><li><i class="fa fa-times-circle text-danger"></i><span>Delete</span></li></a>
        </ul>
    </div>
    <!-- END Page Content -->
    <!-- <input type="checkbox" data-bind="checked: default"> -->
    <script id="template" type="text/x-kendo-template">
        <div class="view-container" data-id="#: data.id #"> 
        	<div data-bind="invisible: visibleEdit">
        		<h4 class="group-name"><i class="#: data.iconClass #" style="line-height: 1; vertical-align: 0"></i>&nbsp;<span data-bind="text: text"></span></h4>
        		<br>
	            <label data-bind="invisible: visibleEdit">Sub: </label>
	            <span class="member-array" data-bind="visible: sub">#= gridArray(data.sub) #</span>&nbsp;
	            <small class="text-danger" data-bind="invisible: sub">None</small>
	            <a href="javascript:void(0)" data-bind="click: editSub"><i class="fa fa-pencil"></i></a>
        	</div>
            <div class="edit-container" data-bind="visible: visibleEdit">
            	<input name="text" class="k-textbox" data-bind="value: text" style="width: 100%; font-size: 18px">
            	<select name="sub" data-role="multiselect" data-value-primitive="true" data-bind="value: sub, source: sub, events: {open: arrayOpen}, visible: sub"></select>
            	<a href="javascript:void(0)" class="k-button" data-bind="click: saveEdit">@Save@</a>&nbsp;
            	<a href="javascript:void(0)" class="k-button" data-bind="click: cancelEdit">@Cancel@</a>
        	</div>
        </div>
    </script>
    <style type="text/css">
        .view-container {
        	border-radius: 5px;
            border: 1px solid lightgray;
            padding: 10px 20px;
            margin: 10px;
            width: 220px;
            min-height: 80px; 
            float: left;
        }
        #listview {
            border: 0;
        }
        .member-array span {
    		font-size: 12px;
    		vertical-align: 1px;
    	}
    	.check-active {
    		border-radius: 7px;
    		border: 1px dashed gray;
    		padding: 1px 3px;
    	}
    </style>
</div>
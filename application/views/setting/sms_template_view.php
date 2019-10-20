<script>
    var Config = {
        crudApi: `${ENV.restApi}`,
        templateApi: `${ENV.templateApi}`,
        collection: "sms_template",
        observable: {
            currentNode: null,
            selectedItem: {},
            onSelect: function(e) {
                this.set("currentNode", e.node);
                var dataItem = e.sender.dataItem(e.node);
                this.set("selectedItem", dataItem);
                this.set("hasChanges", false);
            },
            hasChanges: false,
            onChange: function() {
                this.set("hasChanges", true);
            },
            refreshNode: function() {
                hierarchicalDataSource.read();
            },
            addRootNode: function(e) {
                var treeview = $("#treeview").data("kendoTreeView"),
                    that = this;
                $.ajax({
                    url: Config.crudApi + Config.collection,
                    type: "POST",
                    contentType: "application/json; charset=utf-8",
                    data: kendo.stringify({
                        name: "@Template@ " + (hierarchicalDataSource.total() + 1),
                        value: "@Empty@!",
                        active: true,
                    }),
                    success: function(result) {
                        if(result.status) {
                            var newNode = treeview.append(result.data);
                            kendo.bind(newNode, that);
                            var top = newNode.offset().top;
                            $("#left-col").animate({ scrollTop: top });
                        }
                    },
                    error: errorDataSource
                })
            },
            removeNode: function(e) {
                var treeview = $("#treeview").data("kendoTreeView"),
                    selectedNode = treeview.select(),
                    dataItem = treeview.dataItem(selectedNode);
                swal({
                    title: "@Are you sure@?",
                    text: "@Once deleted, you will not be able to recover this document@!",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })
                .then((willDelete) => {
                    if (willDelete) {
                        $.ajax({
                            url: Config.crudApi + Config.collection + "/" + dataItem.id,
                            type: "DELETE",
                            success: function(result) {
                                treeview.detach(selectedNode);
                            },
                            error: errorDataSource
                        })
                    }
                });
            },
            updateNode: function(e) {
                var treeview = $("#treeview").data("kendoTreeView"),
                    selectedNode = treeview.select(),
                    dataItem = treeview.dataItem(selectedNode).toJSON(),
                    that = this,
                    kendoValidator = $("#right-col").kendoValidator().data("kendoValidator");
                if (kendoValidator.validate()) {
                	delete dataItem.selected;
                    $.ajax({
                        url: Config.crudApi + Config.collection + "/" + dataItem.id,
                        type: "PUT",
                        contentType: "application/json; charset=utf-8",
                        data: kendo.stringify(dataItem),
                        success: function(result) {
                            that.set("hasChanges", false);
                            syncDataSource();
                        },
                        error: errorDataSource
                    })
                } else {
                    var errors = kendoValidator.errors();
                    swal({
                      title: "Not valid data!",
                      text: errors.join('. '),
                      icon: "warning",
                      button: {
                        className: "btn-primary"
                      }
                    });
                }
            },
            onDragstart: function(e) {
                e.sender.select($());
            },
            onDragend: function(e) {
                var destiDataItem = e.sender.dataItem(e.destinationNode),
                    sourceDataItem = e.sender.dataItem(e.sourceNode);
                console.log(e.dropPosition);
                if(e.dropPosition == "over") {
                    // Drag element become child of element
                    notification.show("@Error@", "error");
                    return false;
                } else {
                    var items = e.sender.items(),
                        that = this,
                        check = true;
                    for(var i = 0 ; i < items.length; i++) {
                        var dataItem = e.sender.dataItem(items[i]);
                        //console.log(dataItem);
                        dataItem.pos = i;
                        if(check) {
                            $.ajax({
                                url: `${Config.crudApi + Config.collection}/${dataItem.id}`,
                                type: "PUT",
                                contentType: "application/json; charset=utf-8",
                                data: kendo.stringify(dataItem.toJSON()),
                                error: function() {
                                    swal({text: "Error!"});
                                }
                            })
                        }
                    }
                    
                    if(destiDataItem.parent_id != sourceDataItem.parent_id) {
                        // Drag element become parent of element
                        sourceDataItem.parent_id = destiDataItem.parent_id;
                        $.ajax({
                            url: `${Config.crudApi + Config.collection}/${destiDataItem.id}`,
                            type: "PUT",
                            contentType: "application/json; charset=utf-8",
                            data : kendo.stringify(destiDataItem.toJSON()),
                            success: syncDataSource,
                            error: function() {
                                swal({text: "Error!"});
                            }
                        })
                    }
                }
                $.ajax({
                    url: `${Config.crudApi + Config.collection}/${sourceDataItem.id}`,
                    type: "PUT",
                    contentType: "application/json; charset=utf-8",
                    data : kendo.stringify(sourceDataItem.toJSON()),
                    success: syncDataSource,
                    error: function() {
                        swal({text: "Error!"});
                    }
                });
                this.refreshNode();
            },
            insertKey: function(e) {
            	// get current text of the input
            	var input = document.getElementById("template-value");
				var value = input.value;

				// save selection start and end position
				var start = input.selectionStart;
				var end = input.selectionEnd;

				var buttons = {};
		        Config.observable.listKey.forEach(doc => {
		            buttons[doc.key] = doc.text;
		        })
		        buttons.cancel = true;
		        swal({
		            title: "@Insert@ @key@",
		            icon: "info",
		            buttons: buttons
		        })
		        .then((key) => {
		            if (key !== null && key !== false) {
		                textToInsert = "{" + key + "}";
		                // update the value with our text inserted
						this.set("selectedItem.value", value.slice(0, start) + textToInsert + value.slice(end));

						// update cursor to be at the end of insertion
						input.selectionStart = input.selectionEnd = start + textToInsert.length;
						input.focus();
		            }
		        });
            },
            listKey: [
            	{key: "name", text: "@Name@"},
            	{key: "address", text: "@Address@"}
            ]
        }
    };

    window.onload = function() {
    	window.hierarchicalDataSource = new kendo.data.HierarchicalDataSource({
    		serverSorting: true,
    		sort: {field: "pos", dir: "asc"},
		    transport: {
		        read: Config.crudApi + Config.collection,
		        parameterMap: parameterMap
		    },
		    schema: {
		    	data: "data",
		        total: "total",
		        model: {
		            id: "id"
		        }
		    },
		    error: errorDataSource
		});

        var viewModel = kendo.observable(Object.assign(Config.observable, {
		    files: hierarchicalDataSource
		}));

		kendo.bind($("#allview"), viewModel);

		$.get(`${ENV.vApi}model/read`, {
            q: JSON.stringify({filter: {
            	login: "and",
            	filters: [
            	{field: "collection", operator: "eq", value: (ENV.type ? ENV.type + "_" : "") + "Customer"},
            	{field: "type", operator: "in", value: ["string","int","name","phone"]}
            	]
            }, sort: {field: "index", dir: "asc"}})
        }, function(customerModel) {
        	var listKey = [];
			customerModel.data.forEach(doc => {
				listKey.push({key: doc.field, text: doc.title});
			});
			Config.observable.listKey = listKey;
        });	
    }
</script>
<div id="page-content">
    <!-- Table Styles Header -->
    <ul class="breadcrumb breadcrumb-top">
        <li>@Setting@</li>
        <li>SMS @Template@</li>
    </ul>
    <!-- END Table Styles Header -->
    <div id="allview" class="fluid-container after-breadcrumb">
        <div class="row">
            <div class="col-sm-4" id="left-col">
                <h3>
                    @LIST@
                    <div class="pull-right" style="margin-right: 10px">
                        <button data-bind="click: addRootNode" data-toggle="tooltip" title="@Add@" class="btn btn-sm btn-default" ><i class="fa fa-plus"></i></button>
                        <button data-bind="click: refreshNode" data-toggle="tooltip" title="@Refresh@" class="btn btn-sm btn-default"><i class="fa fa-refresh"></i></button>
                    </div>
                </h3>
                <div class="files" id="treeview"
                 data-role="treeview"
                 data-drag-and-drop="true"
                 data-template="treeViewTemplate"
                 data-text-field="name"
                 data-bind="source: files,
                events: { select: onSelect, dragend: onDragend, dragstart: onDragstart}"></div>
            </div>
            <div class="col-sm-8" id="right-col" data-bind="visible: selectedItem.name">
                <h3>@EDIT@</h3>
                <form class="form-horizontal">
                    <div class="form-group">
                        <label class="control-label col-sm-3">@Name@</label>
                        <div class="col-sm-5">
                            <input class="k-textbox" style="width: 250px" required validationMessage="Please fill name"
                            data-bind="value: selectedItem.name, events: {change: onChange}">
                        </div>
                        <div class="col-sm-4 checkbox text-left">
                            <label>
                                <input type="checkbox" data-bind="checked: selectedItem.active, events: {change: onChange}">
                                <span>@Active@</span>
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3">@Template@ @content@<br>
                        	<i class="text-danger">(<span data-bind="text: selectedItem.value.length"></span> @characters@)</i>
                        </label>
                        <div class="col-sm-9">
                            <textarea class="k-textbox" style="width: 400px" id="template-value"
                            data-bind="value: selectedItem.value, events: {change: onChange}"></textarea>
                            <a data-role="button" data-bind="click: insertKey" style="width: 400px">@Insert@ @key@</a>
                        </div>
                    </div>
                    <div class="form-group text-center">
                        <button type="button" data-bind="css: {btn-alert: hasChanges}, events: {click: updateNode}" data-role="button"><b>@Save@</b></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
<script type="text/x-kendo-template" id="treeViewTemplate">
	<i class="# if(item.active) { ##: 'fa fa-check text-success' ## } else { ##: 'fa fa-times text-danger' ## } #"></i>
    <span>#: item.name #</span>
    <a role="button" href="javascript:void(0)" title="@Deleted@" data-bind="events: {click: removeNode}" class="btn btn-xs btn-delete" style="margin-left: 5px"><i class="fa fa-times-circle text-danger"></i></a>
</script>

<script type="text/x-kendo-template" id="key-template">
    <li>
    	<b data-bind="text: key"></b>: <i data-bind="text: text"></i>
    </li>
</script>
</div>
<style type="text/css">
	.k-treeview span.k-in {
	    cursor: n-resize;
	}
</style>
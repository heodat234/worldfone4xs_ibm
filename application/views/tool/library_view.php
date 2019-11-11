<style type="text/css">
    .k-sprite {
        font-size: 16px;
        line-height: 16px;
    }
</style>
<script>
    var Config = {
        crudApi: `${ENV.restApi}`,
        templateApi: `${ENV.templateApi}`,
        collection: "library",
        observable: {
            currentNode: null,
            selectedItem: {},
            onSelect: function(e) {
                this.set("currentNode", e.node);
                var dataItem = e.sender.dataItem(e.node);
                if(!dataItem.attachments) dataItem.attachments = [];
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
                        name: "New item",
                        visible: true,
                        icon: "",
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
            addNode: function(e) {
                var treeview = $("#treeview").data("kendoTreeView"),
                    selectedNode = treeview.select(),
                    dataItem = treeview.dataItem(selectedNode),
                    that = this;
                $.ajax({
                    url: Config.crudApi + Config.collection,
                    type: "POST",
                    contentType: "application/json; charset=utf-8",
                    data: kendo.stringify({
                        name: "New item",
                        parent_id: dataItem.id,
                        visible: true,
                        icon: "",
                    }),
                    success: function(result) {
                        if(result.status) {
                            var selectedNode = treeview.select();
                            var newNode = treeview.append(result.data, selectedNode);
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
                    title: "Are you sure?",
                    text: "Once deleted, you will not be able to recover this document!",
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
            iconOption: function() {
                return dataSourceJsonData(["Navigators","icon"], function(response) {
                    response.data.unshift({text: "", value: ""});
                    return response;
                })
            },
            removeAttach: function(e) {
                var filename = $(e.currentTarget).data("filename");
                this.set("selectedItem.attachments",
                    this.get("selectedItem.attachments").filter(doc => doc.filename != filename)
                );
            },
            uploadSuccess: function(e) {
                notification.show(e.response.message, e.response.status ? "success" : "error");
                e.sender.clearAllFiles();
                if(!this.selectedItem.attachments) {
                    this.set("selectedItem.attachments", []);
                }
                // Check exists
                var attachments = this.get("selectedItem.attachments").toJSON();
                var exists = false;
                if(attachments.length) {
                    attachments.forEach(doc => {
                        if(doc.filepath == e.response.filepath) {
                            exists = true;
                        }
                    })
                }
                if(!exists) {
                    this.selectedItem.attachments.push({filepath: e.response.filepath, filename: e.response.filename, size: e.response.size});
                }
            },
        }
    };

    window.onload = function() {
    	window.hierarchicalDataSource = new kendo.data.HierarchicalDataSource({
		    transport: {
		        read: {
		            url: Config.crudApi + Config.collection
		        },
		        parameterMap: parameterMap
		    },
		    schema: {
		        model: {
                    hasChildren: "hasChild",
                    id: "id"
                }
		    },
		    error: errorDataSource
		});

		var viewModel = window.viewModel = kendo.observable(Object.assign({
		    files: hierarchicalDataSource,
		}, Config.observable));

		kendo.bind($("#allview"), viewModel);
    }
</script>
<!-- Table Styles Header -->
<ul class="breadcrumb breadcrumb-top">
    <li>@Manage@</li>
    <li>@Library@</li>
</ul>
<!-- END Table Styles Header -->
<div id="allview" class="container-fluid after-breadcrumb">
    <div class="row">
        <div class="col-sm-3" id="left-col">
            <h3>
                @LIST@
                <div class="pull-right" style="margin-right: 10px">
                    <button data-bind="click: addRootNode" data-toggle="tooltip" title="@Add@ @root@"  class="btn btn-sm btn-default" ><i class="fa fa-plus"></i></button>
                    <button data-bind="click: refreshNode" data-toggle="tooltip" title="@Refresh@" class="btn btn-sm btn-default"><i class="fa fa-refresh"></i></button>
                </div>
            </h3>
            <div class="files" id="treeview"
                 data-role="treeview"
                 data-template="tree-view-template"
                 data-text-field="name"
                 data-spritecssclass-field="icon"
                 data-bind="source: files,
                events: { select: onSelect, dragend: onDragend, dragstart: onDragstart}"></div>
        </div>
        <div class="col-sm-9" id="right-col" data-bind="visible: selectedItem.name">
            <h3>@EDIT@</h3>
            <form class="form-horizontal">
                <div class="form-group">
                    <label class="control-label col-sm-3">@Name@</label>
                    <div class="col-sm-6">
                        <input class="k-textbox" style="width: 100%" required validationMessage="Please fill name"
                        data-bind="value: selectedItem.name, events: {change: onChange}">
                    </div>
                    <div class="col-sm-3 checkbox text-left" style="padding-left: 50px">
                        <label>
                            <input type="checkbox" data-bind="checked: selectedItem.visible, events: {change: onChange}">
                            <span>@Visible@</span>
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-3">@Attach@</label>
                    <div class="col-sm-4" style="margin-top: 6px">
                    	<input name="file" type="file" id="upload-attachments"
                           data-role="upload"
                           data-multiple="false"
                           data-async="{ saveUrl: '/api/v1/upload/library', autoUpload: true }"
                           data-bind="events: { success: uploadSuccess }">
                        <br>
                        <ul data-template="library-attach-template" data-bind="source: selectedItem.attachments"></ul>
                    </div>
                    <div class="col-sm-5" style="margin-top: 6px">
                        <div style="width: 80px; float: left;">
                            <input data-role="dropdownlist"
                            data-value-primitive="true"  
                            data-text-field="text"
                            data-value-field="value"   
                            data-template="iconValueTemplate"
                            data-value-template="iconValueTemplate"                
                            data-bind="value: selectedItem.icon, source: iconOption, events: {change: onChange}" style="width: 100%"/>
                        </div>
                        <div style="width: 185px; float: left; margin-left: 10px">
                            <input class="k-textbox" data-bind="value: selectedItem.icon" style="width: 100%">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-3">@Title@</label>
                    <div class="col-sm-9">
                        <input class="k-textbox" style="width: 550px" 
                        data-bind="value: selectedItem.title, events: {change: onChange}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-3">@Content@</label>
                    <div class="col-sm-9">
                        <textarea data-role="editor" style="width: 550px"
                        data-tools="[
							'fontName', 'fontSize',
			                'bold',
			                'italic',
			                'underline',
			                'strikethrough',
			                'justifyLeft',
			                'justifyCenter',
			                'justifyRight',
			                'justifyFull',
			                'insertUnorderedList',
			                'insertOrderedList',
			                'indent',
			                'outdent',
			                'foreColor',
			                'backColor',
			                'viewHtml'
			            ]" 
                        data-bind="value: selectedItem.content, events: {change: onChange}"></textarea>
                    </div>
                </div>
                <div class="form-group text-center">
                    <button type="button" data-bind="css: {btn-alert: hasChanges}, events: {click: updateNode}" data-role="button"><b>@Save@</b></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/x-kendo-template" id="library-attach-template">
    <li>
        <i class="fa fa-file-text"></i>&nbsp;<a data-bind="text: filename, attr: {href: filepath}" download></a> (<i><span data-bind="text: size"></span> bytes</i>) <a href="javascript:void(0)" data-bind="click: removeAttach, attr: {data-filename: filename}"><i class="fa fa-times text-danger"></i></a>
    </li>
</script>
    
<script type="text/x-kendo-template" id="tree-view-template">
    <span>#: item.name #</span>
    <a role="button" href="javascript:void(0)" title="@Add@" data-bind="events: {click: addNode}" class="btn btn-xs btn-add" style="margin-left: 5px"><i class="fa fa-plus-circle text-success"></i></a>
    <a role="button" href="javascript:void(0)" title="@Delete@" data-bind="invisible: hasChildren, events: {click: removeNode}" class="btn btn-xs btn-delete" style="margin-left: 5px"><i class="fa fa-times-circle text-danger"></i></a>
</script>

<script type="text/x-kendo-template" id="iconValueTemplate">
    <i class="#= data.value #"></i>
</script>
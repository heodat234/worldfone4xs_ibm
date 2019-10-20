<style type="text/css">
    .k-sprite {
        font-size: 16px;
        line-height: 16px;
    }

    #servicelevel-search-list .text-name {
        white-space: nowrap; 
        width: 80%; 
        overflow: hidden;
        text-overflow: ellipsis; 
    }
</style>
<script>
    var Config = {
        crudApi: `${ENV.restApi}`,
        templateApi: `${ENV.templateApi}`,
        collection: "servicelevel",
        observable: {
            currentNode: null,
            selectedItem: {},
            onSelect: function(e) {
                this.set("currentNode", e.node);
                var dataItem = e.sender.dataItem(e.node);
                this.set("selectedItem", dataItem);
                this.set("hasChanges", false);
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
                    sourceDataItem.parent_id = destiDataItem.id;
                    sourceDataItem.lv = destiDataItem.lv + 1;
                    destiDataItem.hasChild = true;
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
                        name: "New service level",
                        pos: hierarchicalDataSource.total() + 1,
                        lv: 1
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
                        name: "New service level",
                        parent_id: dataItem.id,
                        hasChild: false,
                        lv: Number(dataItem.lv) + 1
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
                    text: dataItem.hasChild ? "After delete, All childs of this item will get parent of this item" :"Once deleted, you will not be able to recover this document!",
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
                                if(dataItem.hasChild)
                                    hierarchicalDataSource.read();
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
            }
        }
    };
</script>
<div id="page-content">
    <!-- Table Styles Header -->
    <ul class="breadcrumb breadcrumb-top">
        <li>@Setting@</li>
        <li>Service level</li>
    </ul>
    <!-- END Table Styles Header -->
    <div id="allview" class="fluid-container">
        <div class="row">
            <div class="col-sm-4" id="left-col">
                <h3>
                    SERVICE LEVEL 
                    <div class="pull-right" style="margin-right: 10px">
                        <button data-bind="click: addRootNode" data-toggle="tooltip" title="Add Root"  class="btn btn-sm btn-default" ><i class="fa fa-plus"></i></button>
                        <button data-bind="click: refreshNode" data-toggle="tooltip" title="Refresh" class="btn btn-sm btn-default"><i class="fa fa-refresh"></i></button>
                    </div>
                </h3>
                <input data-role="autocomplete" id="servicelevel-search"
                               data-placeholder="@Search@.."
                               data-value-primitive="true"
                               data-value-field="id"
                               data-text-field="name"
                               data-filter="contains"
                               data-template="searchTemplate"
                               data-bind="value: searchText,
                                          source: dataSearch,
                                          events: {
                                            select: onSearch,
                                            filtering: onFiltering,
                                            change: onChangeSearch
                                          }"
                               style="width: 100%"
                        />
                <hr>
                <div class="files" id="treeview"
                 data-role="treeview"
                 data-drag-and-drop="true"
                 data-template="treeViewTemplate"
                 data-text-field="name"
                 data-spritecssclass-field="icon"
                 data-bind="source: files,
                events: { select: onSelect, dragend: onDragend, dragstart: onDragstart}"></div>
            </div>
            <div class="col-sm-8" id="right-col" data-bind="visible: selectedItem.name">
                <h3>@EDIT@ <span class="label label-info pull-right">Level <b data-bind="text: selectedItem.lv"></b></span></h3>
                <form class="form-horizontal">
                    <div class="form-group">
                        <label class="control-label col-sm-3">Name</label>
                        <div class="col-sm-9">
                            <input class="k-textbox" style="width: 400px" required validationMessage="Please fill name"
                            data-bind="value: selectedItem.name, events: {change: onChange}">
                        </div>
                    </div>
                    <div class="form-group" data-bind="visible: selectedItem.parent_id">
                        <label class="control-label col-sm-3">Parent</label>
                        <div class="col-sm-9">
                            <input data-role="dropdownlist"
                            data-value-primitive="true"  
                            data-text-field="name"
                            data-value-field="id"                   
                            data-bind="value: selectedItem.parent_id, source: parentOption, events: {change: onChange}" style="width: 400px" disabled="" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3">Description</label>
                        <div class="col-sm-9">
                            <textarea class="k-textbox" data-bind="value: selectedItem.description, events: {change: onChange}" style="width: 400px"></textarea>
                        </div>
                    </div>
                    <div class="form-group text-center">
                        <button type="button" data-bind="css: {btn-alert: hasChanges}, events: {click: updateNode}" data-role="button"><b>Save</b></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
<script type="text/x-kendo-template" id="treeViewTemplate">
    <b data-bind="visible: fit">#: item.name #</b>
    <span data-bind="invisible: fit">#: item.name #</span>
    <a role="button" href="javascript:void(0)" title="Thêm thư mục/tập tin con" data-bind="events: {click: addNode}" class="btn btn-xs btn-add" style="margin-left: 5px"><i class="fa fa-plus-circle text-success"></i></a>
    <a role="button" href="javascript:void(0)" title="Xóa thư mục/tập tin" data-bind="invisible: hasChild, events: {click: removeNode}" class="btn btn-xs btn-delete" style="margin-left: 5px"><i class="fa fa-times-circle text-danger"></i></a>
</script>

<script type="text/x-kendo-template" id="searchTemplate">
    <div class="pull-left text-name">#if(typeof name != 'undefined'){##: name ##}#</div>
    <div class="pull-right text-muted">Level #if(typeof lv != 'undefined'){##: lv ##}#</div>
</script>

<script type="text/x-kendo-template" id="iconValueTemplate">
    <i class="#= data.value #"></i>
</script>

<script type="text/x-kendo-template" id="extension-template">
    <span class="label label-success" data-bind="text: this"></span>
</script>
<script type="text/javascript">
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
	        },
            parse: function(response) {
                var data = [];
                response.forEach(doc => {
                    doc.expanded = false;
                    if(viewModel.get("searchText") == doc.name) {
                        doc.fit = true;
                        data.unshift(doc);
                    } else data.push(doc);
                });
                return data;
            }
	    },
	    error: errorDataSource
	});

	var viewModel = kendo.observable(Object.assign({
	    files: hierarchicalDataSource,
        dataSearch: new kendo.data.DataSource({
            serverFiltering: true,
            transport: {
                read: {
                    url: ENV.vApi + Config.collection + "/read",
                },
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
        }),
        onSearch: function(e) {
            if(e.dataItem.parent_id) {                
                hierarchicalDataSource.read({id: e.dataItem.parent_id})
            } else hierarchicalDataSource.read();
            this.set("selectedItem", {});
        },
        onFiltering: function(e) {
            e.preventDefault();
            e.sender.dataSource.filter({field: "name", operator: "contains", value: e.filter.value, ignoreCase: true});
        },
        onChangeSearch: function(e) {
            var value = e.sender.value();
            if(!value) {
                this.set("searchText", "");
                hierarchicalDataSource.read()
            }
        },
	    parentOption: dataSourceDropDownList("Service_level", ["name"], null, res => res, 500)
	}, Config.observable));

	kendo.bind($("#allview"), viewModel);
}
</script>
</div>
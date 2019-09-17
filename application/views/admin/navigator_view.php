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
        collection: "navigator",
        observable: {
            currentNode: null,
            selectedItem: {},
            onSelect: function(e) {
                this.set("currentNode", e.node);
                var dataItem = e.sender.dataItem(e.node);
                this.set("selectedItem", dataItem);
                this.set("hasChanges", false);
                this.parentOption.read();
                //
                this.asyncModuleCascade({});
            },
            moduleCascade: function(e) {
                this.asyncModuleCascade(e);
            },
            onlyAdminChange: function(e) {
                this.asyncModuleCascade(e);
            },
            asyncModuleCascade: async function(e) {
                let id = e.sender ? (e.sender.value() || "").toString() : this.get("selectedItem.module_id");
                    data = {id: id};
                if(id) this.set("selectedItem.only_admin", false);
                if(this.get("selectedItem.only_admin")) data.only_admin = 1;
                let access = await $.ajax({
                        url: ENV.vApi + "permission/access_from_module_id",
                        data: data
                    });
                if(access.length)
                    this.set("accessExtensions", access[0].extensions ? access[0].extensions : [] );
                else this.set("accessExtensions", []);
                this.onChange();
            },
            onDragstart: function(e) {
                e.sender.select($());
            },
            onDragend: function(e) {
                var destiDataItem = e.sender.dataItem(e.destinationNode),
                    sourceDataItem = e.sender.dataItem(e.sourceNode);
                console.log(e.dropPosition, sourceDataItem, destiDataItem);
                if(e.dropPosition == "over") {
                    // Drag element become child of element
                    sourceDataItem.parent_id = destiDataItem.id;
                    sourceDataItem.module_id = destiDataItem.module_id;
                    sourceDataItem.only_admin = destiDataItem.only_admin;
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
                    } else {
                        sourceDataItem.pos = destiDataItem.pos + (e.dropPosition == "after" ? 1 : -1);
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
                        name: "New Page",
                        visible: true,
                        module_id: null,
                        icon: "",
                        uri: "",
                        pos: hierarchicalDataSource.total() + 1
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
                        name: "New Page",
                        parent_id: dataItem.id,
                        module_id: dataItem.module_id ? dataItem.module_id : null,
                        only_admin: Boolean(dataItem.only_admin),
                        visible: true,
                        icon: "",
                        uri: ""
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
            },
            iconOption: function() {
                return dataSourceJsonData(["Navigators","icon"], function(response) {
                    response.data.unshift({text: "", value: ""});
                    return response;
                })
            },
            moduleOption: function() {
                return dataSourceDropDownListPrivate("Module", ["name"], function(response) {
                    response.data.unshift({id: null, name: "Not belong to any module (Auto access)"});
                    return response;
                })
            },
            uriOption: function() {
                return new kendo.data.DataSource({
                    transport: {
                        read: ENV.vApi + "select/path/view"  
                    },
                    schema: {
                        data: "data",
                        response: function(response) {
                            response.data.unshift("parent", "header");
                            return response;
                        }
                    }
                })
            },
            apiOption: function() {
                return new kendo.data.DataSource({
                    transport: {
                        read: ENV.vApi + "select/path/api"  
                    },
                    schema: {
                        data: "data"
                    }
                })
            }
        }
    };
</script>
<div id="page-content">
    <!-- Table Styles Header -->
    <ul class="breadcrumb breadcrumb-top">
        <li>Admin</li>
        <li>Navigators</li>
    </ul>
    <!-- END Table Styles Header -->
    <div id="allview" class="fluid-container">
        <div class="row">
            <div class="col-sm-4" id="left-col">
                <h3>
                    NAVIGATORS 
                    <div class="pull-right" style="margin-right: 10px">
                        <button data-bind="click: addRootNode" data-toggle="tooltip" title="Add Root"  class="btn btn-sm btn-default" ><i class="fa fa-plus"></i></button>
                        <button data-bind="click: refreshNode" data-toggle="tooltip" title="Refresh" class="btn btn-sm btn-default"><i class="fa fa-refresh"></i></button>
                    </div>
                </h3>
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
                <h3>EDIT</h3>
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
                        <label class="control-label col-sm-3">Module</label>
                        <div class="col-sm-9">
                            <input data-role="dropdownlist" name="module_id"
                            data-value-primitive="true"  
                            data-text-field="name"
                            data-value-field="id"                   
                            data-bind="value: selectedItem.module_id, source: moduleOption, events: {cascade: moduleCascade}, disabled: selectedItem.hasChild" style="width: 400px"/>
                        </div>
                    </div>
                    <div class="form-group" data-bind="invisible: selectedItem.hasChild">
                        <label class="control-label col-sm-3">User can access</label>
                        <div class="col-sm-9" style="padding-top: 7px">
                            <div data-template="extension-template" data-bind="source: accessExtensions"></div>
                        </div>
                    </div>
                    <div class="form-group" data-bind="invisible: selectedItem.module_id">
                        <label class="control-label col-sm-3">Only sysadmin</label>
                        <div class="col-sm-9 checkbox">
                            <label>
                                <input type="checkbox" data-bind="checked: selectedItem.only_admin, disabled: selectedItem.parent_id, events: {change: onlyAdminChange}">
                            </label>
                        </div>
                    </div>
                    <div class="form-group" data-bind="invisible: selectedItem.hasChild">
                        <label class="control-label col-sm-3">Uri</label>
                        <div class="col-sm-9">
                            <input data-role="autocomplete"
                            data-value-primitive="true"
                            data-filter="contains" 
                            data-bind="value: selectedItem.uri, source: uriOption, events: {change: onChange}" style="width: 400px">
                        </div>
                    </div>
                    <div class="form-group" data-bind="invisible: selectedItem.hasChild">
                        <label class="control-label col-sm-3">Api</label>
                        <div class="col-sm-9">
                            <select data-role="multiselect" name="apis" multiple="multiple"
                            data-value-primitive="true"    
                            data-filter="contains"             
                            data-bind="value: selectedItem.apis, source: apiOption, events: {change: onChange}" 
                            style="width: 400px"></select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3">Icon</label>
                        <div class="col-sm-9">
                            <div style="width: 100px; float: left;">
                                <input data-role="dropdownlist"
                                data-value-primitive="true"  
                                data-text-field="text"
                                data-value-field="value"   
                                data-template="iconValueTemplate"
                                data-value-template="iconValueTemplate"                
                                data-bind="value: selectedItem.icon, source: iconOption, events: {change: onChange}" style="width: 100%"/>
                            </div>
                            <div style="width: 290px; float: left; margin-left: 10px">
                                <input class="k-textbox" data-bind="value: selectedItem.icon" style="width: 100%">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3">Visible</label>
                        <div class="col-sm-9 checkbox">
                            <label>
                                <input type="checkbox" data-bind="checked: selectedItem.visible, events: {change: onChange}">
                            </label>
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
    <span>#: item.name #</span>
    <a role="button" href="javascript:void(0)" title="Thêm thư mục/tập tin con" data-bind="invisible: parent_id, events: {click: addNode}" class="btn btn-xs btn-add" style="margin-left: 5px"><i class="fa fa-plus-circle text-success"></i></a>
    <a role="button" href="javascript:void(0)" title="Xóa thư mục/tập tin" data-bind="invisible: hasChildren, events: {click: removeNode}" class="btn btn-xs btn-delete" style="margin-left: 5px"><i class="fa fa-times-circle text-danger"></i></a>
</script>

<script type="text/x-kendo-template" id="iconValueTemplate">
    <i class="#= data.value #"></i>
</script>

<script type="text/x-kendo-template" id="extension-template">
    <span class="label label-success" data-bind="text: this"></span>
</script>
</div>
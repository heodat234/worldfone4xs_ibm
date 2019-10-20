<style type="text/css">
    .k-sprite {
        font-size: 16px;
        line-height: 16px;
    }
    .badge.badge-pill:hover {
        background-color: #1bbae1;
    }
</style>
<script>
    var Config = {
        crudApi: `${ENV.vApi}`,
        collection: "changelog",
        observable: {
            currentNode: null,
            selectedItem: {},
            onSelect: function(e) {
                this.set("currentNode", e.node);
                var dataItem = e.sender.dataItem(e.node);
                this.set("selectedItem", dataItem);
            },
            refreshNode: function() {
                hierarchicalDataSource.read();
            },
            removeNode: function(e) {
                var treeview = $("#treeview").data("kendoTreeView"),
                    selectedNode = treeview.select(),
                    dataItem = treeview.dataItem(selectedNode);
                swal({
                    title: "Are you sure?",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })
                .then((willDelete) => {
                    if (willDelete) {
                        $.ajax({
                            url: Config.crudApi + Config.collection + "/delete/" + dataItem.id,
                            type: "DELETE",
                            success: function(result) {
                                treeview.detach(selectedNode);
                            },
                            error: errorDataSource
                        })
                    }
                });
            },
            dataSearch: () => new kendo.data.DataSource({
                serverFiltering: true,
                transport: {
                    read: {
                        url: ENV.vApi + "changelog/file"
                    }
                },
                schema: {
                    data: "data",
                    model: {
                        id: "id"
                    }
                },
                error: errorDataSource          
            }),
            onSearch: function(e) {
                if(e.dataItem) {
                    hierarchicalDataSource.read({id: e.dataItem.parent_id, highlight: e.dataItem.file_name})
                }
            },
            onChange: function(e) {
                if(!e.sender.value()) {
                    hierarchicalDataSource.read({});
                }
            },
            onDataBound: function(e) {
                var treeview = $("#treeview").data("kendoTreeView");
                var data = e.sender.dataSource.data();
                for (var i = 0; i < data.length; i++) {
                    if(data[i].highlight) {
                        var select_string = `li[data-uid=${data[i].uid}]`;
                        treeview.select(select_string);
                        this.onSelect({node: $(select_string).get(0), sender: e.sender});
                    }
                }
            },
            openView: function(e) {
                e.sender.wrapper.css({ top: 100 });
            },
            view: {},
            viewCode: function(e) {
                var index = e.currentTarget.dataset.index;
                $("#view-code-window").data("kendoWindow").center().open();
                var logs = this.get("selectedItem.logs");
                this.set("view", logs[index]);
                var filepath = this.get("selectedItem.app_path");
                $.get(ENV.vApi + "changelog/readfile", {filepath: filepath}, (content) => {
                    this.set("contentFile", content);
                });
            },
            viewFile: function(e) {
                var filepath = this.get("selectedItem.app_path");
                var access_time = this.get("selectedItem.access_time");
                var modify_time = this.get("selectedItem.modify_time");
                openForm({title: "Edit file"});
                $.ajax({
                    url: ENV.templateApi + "code/form",
                    data: {doc: {filepath: filepath, access_time: access_time, modify_time: modify_time}},
                    error: errorDataSource,
                    success: (formHtml) => {
                        $rightForm = $("#right-form");
                        kendo.destroy($rightForm);
                        $rightForm.empty();
                        $rightForm.append(formHtml);
                    }
                });
            },
            changeVisibleCode: function(e) {
                this.set("view.visibleCode", e.currentTarget.checked);
            },
            changePermissions: function(e) {
                swal({
                    title: "Change permissions to 0755?",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })
                .then((ok) => {
                    if (ok) {
                        $.ajax({
                            url: Config.crudApi + Config.collection + "/chmod",
                            type: "POST",
                            data: {filepath: this.get("selectedItem.file_path")},
                            success: function(res) {
                                if(res.status) {
                                    syncDataSource();
                                } else notification.show(res.message, "error");
                            },
                            error: errorDataSource
                        })
                    }
                });
            },
            save: function() {
                var treeview = $("#treeview").data("kendoTreeView"),
                    selectedNode = treeview.select(),
                    dataItem = treeview.dataItem(selectedNode).toJSON(),
                    that = this,
                    kendoValidator = $("#right-col").kendoValidator().data("kendoValidator");
                if (kendoValidator.validate()) {
                    $.ajax({
                        url: Config.crudApi + Config.collection + "/update/" + dataItem.id,
                        type: "PUT",
                        contentType: "application/json; charset=utf-8",
                        data: JSON.stringify(dataItem),
                        success: function(result) {
                            syncDataSource();
                            hierarchicalDataSource.read({id: dataItem.parent_id, highlight: dataItem.file_name})
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
        <li>Admin</li>
        <li>Changelog</li>
    </ul>
    <!-- END Table Styles Header -->
    <div id="allview" class="fluid-container">
        <div class="row">
            <div class="col-sm-4" id="left-col">
                <h3>
                    CHANGE LOG 
                    <div class="pull-right" style="margin-right: 10px">
                        <button data-bind="click: refreshNode" data-toggle="tooltip" title="Refresh" class="btn btn-sm btn-default"><i class="fa fa-refresh"></i></button>
                    </div>
                </h3>
                <input data-role="autocomplete"
                       data-placeholder="Search"
                       data-value-primitive="true"
                       data-value-field="id"
                       data-text-field="app_path"
                       data-filter="contains"
                       data-bind="source: dataSearch,
                                  events: {
                                    select: onSearch,
                                    change: onChange
                                  }"
                       style="width: 350px;"/>
                <div class="files" id="treeview"
                 data-role="treeview"
                 data-template="treeViewTemplate"
                 data-text-field="name"
                 data-spritecssclass-field="icon"
                 data-bind="source: files,
                events: { select: onSelect, dataBound: onDataBound}"></div>
            </div>
            <div class="col-sm-8" id="right-col" data-bind="visible: selectedItem.name">
                <h3>EDIT</h3>
                <form class="form-horizontal">
                    <div class="form-group">
                        <label class="control-label col-sm-2">Name</label>
                        <div class="col-sm-4">
                            <span data-bind="text: selectedItem.name" style="line-height: 32px"></span>
                            <a data-bind="click: changePermissions" class="text-warning" href="javascript:void(0)"> /<i data-bind="text: selectedItem.permissions"></i>/</a>
                            <a data-bind="visible: selectedItem.exists, click: viewFile" href="javascript:void(0)"><i class="fa fa-check text-success"></i></a>
                            <span data-bind="invisible: selectedItem.exists"><i class="fa fa-times text-danger"></i></span>
                        </div>
                        <label class="control-label col-sm-2">Last update</label>
                        <div class="col-sm-4">
                            <span data-bind="text: selectedItem.modify_timeText" style="line-height: 32px"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-2">Path</label>
                        <div class="col-sm-4">
                            <span data-bind="text: selectedItem.app_path" style="line-height: 32px"></span>
                        </div>
                        <label class="control-label col-sm-2">Last access</label>
                        <div class="col-sm-4">
                            <span data-bind="text: selectedItem.access_timeText" style="line-height: 32px"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-2">Change</label>
                        <div class="col-sm-10">
                            <div class="list-group" data-bind="source: selectedItem.logs, visible: selectedItem.logs" data-template="log-template" style="padding-top: 7px; width: 90%"></div>
                        </div>
                    </div>
                    <div class="form-group" data-bind="invisible: selectedItem.is_dir">
                        <label class="control-label col-sm-2">Note</label>
                        <div class="col-sm-10">
                            <textarea data-role="editor" data-bind="value: selectedItem.change" style="width: 90%"
                            data-tools="[
                                'bold',
                                'italic',
                                'underline',
                                'strikethrough',
                                'insertUnorderedList',
                                'insertOrderedList',
                                'indent',
                                'outdent',
                                'foreColor',
                                'backColor',
                            ]"></textarea>
                        </div>
                    </div>
                    <div class="form-group text-center">
                        <button type="button" data-bind="css: {btn-alert: hasChanges}, events: {click: save}" data-role="button"><b>Save</b></button>
                    </div>
                </form>
            </div>
        </div>
        <div style="display: none">
            <div data-role="window" id="view-code-window"
                data-title="Change"
                data-width="700"
                data-actions="['Minimize', 'Maximize', 'Close']"
                data-position="{'top': 20}"
                data-bind="events: {open: openView}"
                data-visible="false" style="padding: 2px, top: 100px">
                <div class="container-fluid form-horizontal" style="padding-top: 10px">
                    <div class="form-group">
                        <div class="col-sm-2"><label>Time: </label></div>
                        <div class="col-sm-4">
                            <span class="label label-warning" data-bind="text: view.timeText"></span>
                        </div>
                        <div class="col-sm-6" style="margin-top: -4px" data-bind="invisible: selectedItem.is_dir">
                            <input type="checkbox" data-bind="events: {change: changeVisibleCode">
                            <span style="vertical-align: 1px">View code</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-2"><label>Change: </label></div>
                        <div class="col-sm-10" data-bind="html: view.change">
                        </div>
                    </div>
                    <div class="form-group" data-bind="visible: view.visibleCode">
                        <div class="col-sm-6"><label>Before</label></div>
                        <div class="col-sm-6"><label>Current</label></div>
                        <div class="col-sm-6">
                              <textarea class="k-textbox" data-bind="text: view.code, disabled: view.visibleCode" style="width: 100%; min-height: 300px"></textarea>
                        </div>
                        <div class="col-sm-6">
                              <textarea class="k-textbox" data-bind="text: contentFile, disabled: view.visibleCode" style="width: 100%; min-height: 300px"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script type="text/x-kendo-template" id="treeViewTemplate">
    <span>#: item.name #</span>
    # if(!item.exists) { #
    <a role="button" href="javascript:void(0)" title="Remove data" data-bind="events: {click: removeNode}" class="btn btn-xs btn-delete" style="margin-left: 5px"><i class="fa fa-times-circle text-danger"></i></a>
    # } #
</script>

<script type="text/x-kendo-template" id="log-template1">
    <a class="list-group-item" data-bind="click: viewCode, attr: {data-index: index}" style="cursor: pointer">
        <div data-bind="html: change"></div>
        <span class="badge badge-primary badge-pill" data-bind="text: timeText"></span>
    </a>
</script>
<script type="text/x-kendo-template" id="log-template">
    <a class="badge badge-primary badge-pill" data-bind="text: timeText, click: viewCode, attr: {data-index: index}"></a>
</script>
</div>
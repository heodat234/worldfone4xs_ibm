<script>
    var Config = {
        crudApi: `${ENV.restApi}`,
        templateApi: `${ENV.templateApi}`,
        collection: "config",
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
                        name: "@Config@ " + (hierarchicalDataSource.total() + 1),
                        active: false,
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
                            window.hierarchicalDataSource.read();
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
            removeAttach: function(e) {
                var filename = $(e.currentTarget).data("filename");
                this.set("selectedItem.email_signature_cid_attachments",
                    this.get("selectedItem.email_signature_cid_attachments").filter(doc => doc.filename != filename)
                );
            },
            uploadSuccess: function(e) {
                e.sender.clearAllFiles();
                if(!this.selectedItem.email_signature_cid_attachments) {
                    this.set("selectedItem.email_signature_cid_attachments", []);
                }
                this.selectedItem.email_signature_cid_attachments.push({filepath: e.response.filepath, filename: e.response.filename, size: e.response.size});
                var editor = $("#signature-content-editor").data("kendoEditor");
                editor.exec("insertHtml", {value: `<img src="${e.response.filepath}"/>`});
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
                data: "data",
                total: "total",
                model: {
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

    function execInsertImage(e) {
        $("#upload-cid-attach").click();
    }
</script>
<!-- Table Styles Header -->
<ul class="breadcrumb breadcrumb-top">
    <li>@Setting@</li>
    <li>@Config@</li>
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
             data-template="treeViewTemplate"
             data-text-field="name"
             data-bind="source: files,
            events: { select: onSelect}"></div>
        </div>
        <div class="col-sm-9" id="right-col" data-bind="visible: selectedItem.name">
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
                    <div class="col-sm-10 col-sm-offset-1 title-row">
                        <span class="text-default" style="background-color: ghostwhite">SMS</span>
                        <hr>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-3">@Brandname@</label>
                    <div class="col-sm-9">
                        <input class="k-textbox" style="width: 500px" data-bind="value: selectedItem.sms_brandname, events: {change: onChange}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-3">@Username@</label>
                    <div class="col-sm-9">
                        <input class="k-textbox" style="width: 500px" data-bind="value: selectedItem.sms_username, events: {change: onChange}">
                        <i class="fa fa-check-circle text-success" data-bind="visible: selectedItem.has_sms_password"></i>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-9 col-sm-offset-3">
                        <label class="checkbox-inline">
                            <input type="checkbox" autocomplete="off" data-bind="checked: changePasswordSms">
                            <span>@Change password@ sms</span>
                        </label>
                    </div>
                </div>
                <div class="form-group" data-bind="visible: changePasswordSms">
                    <label class="control-label col-sm-3">@Pasword@</label>
                    <div class="col-sm-9">
                        <input class="k-textbox" type="password" style="width: 500px" data-bind="value: selectedItem.sms_password, events: {change: onChange}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-3">API</label>
                    <div class="col-sm-9">
                        <input class="k-textbox" style="width: 500px" data-bind="value: selectedItem.sms_api, events: {change: onChange}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-3">@Prefix@ sms</label>
                    <div class="col-sm-9">
                        <input class="k-textbox" style="width: 500px" data-bind="value: selectedItem.sms_sub, events: {change: onChange}">
                    </div>
                </div>
                 <div class="form-group">
                    <div class="col-sm-10 col-sm-offset-1 title-row">
                        <span class="text-default" style="background-color: ghostwhite">EMAIL</span>
                        <hr>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-3">@Email name@</label>
                    <div class="col-sm-9">
                        <input class="k-textbox" style="width: 500px" data-bind="value: selectedItem.email_name, events: {change: onChange}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-3">@Email@</label>
                    <div class="col-sm-9">
                        <input class="k-textbox" style="width: 500px" data-bind="value: selectedItem.email_address, events: {change: onChange}">
                        <i class="fa fa-check-circle text-success" data-bind="visible: selectedItem.has_email_password"></i>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-9 col-sm-offset-3">
                        <label class="checkbox-inline">
                            <input type="checkbox" autocomplete="off" data-bind="checked: changePasswordEmail">
                            <span>@Change password@ email</span>
                        </label>
                    </div>
                </div>
                <div class="form-group" data-bind="visible: changePasswordEmail">
                    <label class="control-label col-sm-3">@Pasword@</label>
                    <div class="col-sm-9">
                        <input class="k-textbox" type="password" style="width: 500px" data-bind="value: selectedItem.email_password, events: {change: onChange}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-3">Host</label>
                    <div class="col-sm-9">
                        <input class="k-textbox" style="width: 500px" data-bind="value: selectedItem.email_host, events: {change: onChange}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-3">Port</label>
                    <div class="col-sm-9">
                        <input class="k-textbox" style="width: 500px" data-bind="value: selectedItem.email_port, events: {change: onChange}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-3">@Encrypted@</label>
                    <div class="col-sm-9">
                        <input class="k-textbox" style="width: 500px" data-bind="value: selectedItem.email_encrypted, events: {change: onChange}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-3">@Attach@</label>
                    <div class="col-sm-9" style="margin-top: 6px">
                        <ul data-template="signature-cid-attachments-template" data-bind="source: selectedItem.email_signature_cid_attachments"></ul>
                    </div>
                    <div class="hidden">
                        <input name="file" type="file" id="upload-cid-attach" 
                           data-role="upload"
                           data-multiple="false"
                           data-async="{ saveUrl: '/api/v1/upload/attachment', autoUpload: true }"
                           data-bind="events: { success: uploadSuccess }">
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-3">@Signature@</label>
                    <div class="col-sm-9">
                        <textarea data-role="editor" style="width: 500px" id="signature-content-editor"
                        data-tools="[{name: 'image-insert', tooltip: '@Insert@ @image@', 
                                exec: execInsertImage
                            },'bold', 'italic', 'underline', 'fontSize', 'foreColor', 'backColor','viewHtml']" 
                        data-bind="value: selectedItem.email_signature"></textarea>
                    </div>
                </div>
                <div class="form-group text-center">
                    <button type="button" data-bind="css: {btn-alert: hasChanges}, events: {click: updateNode}" data-role="button"><b>@Save@</b></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/x-kendo-template" id="signature-cid-attachments-template">
    <li>
        <i class="fa fa-file-text"></i>&nbsp;<a data-bind="text: filename, attr: {href: filepath}" download></a> (<i><span data-bind="text: size"></span> bytes</i>) <a href="javascript:void(0)" data-bind="click: removeAttach, attr: {data-filename: filename}"><i class="fa fa-times text-danger"></i></a>
    </li>
</script>
    
<script type="text/x-kendo-template" id="treeViewTemplate">
    <i class="# if(item.active) { ##: 'fa fa-check text-success' ## } else { ##: 'fa fa-times text-danger' ## } #"></i>
    <span>#: item.name #</span>
    <a role="button" href="javascript:void(0)" title="Xóa thư mục/tập tin" data-bind="events: {click: removeNode}" class="btn btn-xs btn-delete" style="margin-left: 5px"><i class="fa fa-times-circle text-danger"></i></a>
</script>

<script type="text/x-kendo-template" id="iconValueTemplate">
    <i class="#= data.value #"></i>
</script>

<script type="text/x-kendo-template" id="extension-template">
    <span class="label label-success" data-bind="text: this"></span>
</script>
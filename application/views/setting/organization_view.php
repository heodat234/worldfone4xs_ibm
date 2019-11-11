<script>
    var Config = {
        crudApi: `${ENV.restApi}`,
        templateApi: `${ENV.templateApi}`,
        collection: "organization",
        observable: {
            currentNode: null,
            selectedItem: {},
            onSelect: function(e) {
                this.set("currentNode", e.node);
                var dataItem = e.sender.dataItem(e.node);
                this.set("selectedItem", dataItem);
                this.set("hasChanges", false);
                var diagram = $("#diagram").getKendoDiagram();
                var shape = diagram.getShapeByModelId(dataItem.id);
    			diagram.select(shape);
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
                        name: "Root",
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
                        name: "New one",
                        parent_id: dataItem.id,
                        color: dataItem.color,
                        active: true
                    }),
                    success: function(result) {
                        if(result.status) {
                            var selectedNode = treeview.select();
                            var newNode = treeview.append(result.data, selectedNode);
                            kendo.bind(newNode, that);
                            var top = newNode.offset().top;
                            $("#left-col").animate({ scrollTop: top });
                            $("#diagram").data("kendoDiagram").dataSource.read();
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
                                $("#diagram").data("kendoDiagram").dataSource.read();
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
                            $("#diagram").data("kendoDiagram").dataSource.read();
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

<style type="text/css">
	.selected-value {
        display: inline-block;
        vertical-align: middle;
        width: 18px;
        height: 18px;
        background-size: 100%;
        margin-right: 5px;
        border-radius: 50%;
    }

    span.k-in a.btn {
	    display: none;
	}
	span.k-in.k-state-selected a.btn {
	    display: inline-block;
	}
</style>
<!-- Table Styles Header -->
<ul class="breadcrumb breadcrumb-top">
    <li>@Setting@</li>
    <li>@Organization@</li>
</ul>
<!-- END Table Styles Header -->
<div id="allview" class="container-fluid after-breadcrumb">
    <div class="row">
        <div class="col-sm-3" id="left-col">
            <h3>
                @EDIT@
                <div class="pull-right" style="margin-right: 10px">
                	<button data-bind="click: addRootNode" data-toggle="tooltip" title="@Add@"  class="btn btn-sm btn-default" ><i class="fa fa-plus"></i></button>
                    <button data-bind="click: refreshNode" data-toggle="tooltip" title="@Refresh@" class="btn btn-sm btn-default"><i class="fa fa-refresh"></i></button>
                </div>
            </h3>
            <div class="files" id="treeview"
             data-role="treeview"
             data-template="treeViewTemplate"
             data-text-field="agentname"
             data-bind="source: files,
                            events: {select: onSelect}"></div>
        </div>
        <div class="col-sm-9" id="right-col" style="border-left: 1px solid lightgray">
            <h3 class="text-center">@DIAGRAM@</h3>
        	<div id="diagram" data-role="diagram"
             data-layout='{"type": "tree", "subtype": "tipover", "horizontalSeparation": 30, "verticalSeparation": 30, "underneathHorizontalOffset": 140}'
             data-zoom="0.75" data-zoom-min="0.5" data-zoom-max="1.5" data-editable="false"   
             data-shape-defaults='{"width": 40, "height": 40, "visual": visualTemplate}'
             data-bind="source: dataSource, events: {select: onDiagramSelect}"></div>
        </div>
    </div>
</div>

<div id="form-organization-container" style="display: none"></div>
    
<script type="text/x-kendo-template" id="treeViewTemplate">
	<i class="fa fa-circle" style="color: #: (item.color || "\#75be16").toString() #" id="#: item.id #"></i>
    <span><b>#: item.name #</b></span>
    <a role="button" href="javascript:void(0)" title="@Edit@" data-bind="click: openFormNode" class="btn btn-xs btn-edit" style="margin-left: 5px"><i class="fa fa-pencil text-warning"></i></a>
    <a role="button" href="javascript:void(0)" title="@Add@ @child@" data-bind="events: {click: addNode}" class="btn btn-xs btn-add" style="margin-left: 5px"><i class="fa fa-plus-circle text-success"></i></a>
    <a role="button" href="javascript:void(0)" title="@Delete@" data-bind="invisible: hasChildren, events: {click: removeNode}" class="btn btn-xs btn-delete" style="margin-left: 5px"><i class="fa fa-times-circle text-danger"></i></a>
</script>

<script type="text/javascript">
    function visualTemplate(options) {
        var dataviz = kendo.dataviz;
        var g = new dataviz.diagram.Group();
        var dataItem = options.dataItem;
        var colorScheme = dataItem.color ? dataItem.color : "#75be16";
        if (!dataItem.parent_id) {
            g.append(new dataviz.diagram.Circle({
                radius: 60,
                stroke: {
                    width: 0
                },
                fill: {
	                gradient: {
	                    type: "linear",
	                    stops: [{
	                        color: colorScheme,
	                        offset: 0,
	                        opacity: 0.5
	                    }, {
	                        color: colorScheme,
	                        offset: 1,
	                        opacity: 1
	                    }]
	                }
	            }
            }));
            g.append(new dataviz.diagram.TextBlock({
	            text: dataItem.name,
	            x: 30,
	            y: 45,
	            fill: "#fff",
	            fontSize: 24,
	            fontWeight: 700
	        }));
        } else {
        	if(dataItem.hasChild) {
	        	g.append(new dataviz.diagram.Rectangle({
	                width: 250,
	                height: 75,
	                stroke: {
	                    width: 0
	                },
	                fill: {
	                    gradient: {
	                        type: "linear",
	                        stops: [{
	                            color: colorScheme,
	                            offset: 0,
	                            opacity: 0.8
	                        }, {
	                            color: colorScheme,
	                            offset: 1,
	                            opacity: 0.4
	                        }]
	                    }
	                }
	            }));

	            if(dataItem.lead) {
			        g.append(new dataviz.diagram.TextBlock({
			            text: dataItem.lead,
			            x: 80,
			            y: 40,
			            fill: "#fff",
			            fontSize: 20,
			            fontWeight: 700
			        }));

			        g.append(new dataviz.diagram.Image({
		                source: "api/v1/avatar/agent/" + dataItem.lead,
		                x: 3,
		                y: 3,
		                width: 68,
		                height: 68
		            }));
			    }

			    g.append(new dataviz.diagram.TextBlock({
		            text: dataItem.name,
		            x: dataItem.lead ? 80 : 15,
		            y: 10,
		            fill: "#444",
		            fontSize: 20,
		            fontWeight: 700
		        }));

	    	} else {
	    		g.append(new dataviz.diagram.Rectangle({
	                width: 300,
	                height: 67,
	                stroke: {
	                    width: 0
	                },
	                fill: "#e8eff7"
	            }));

	            g.append(new dataviz.diagram.Rectangle({
	                width: 8,
	                height: 67,
	                fill: colorScheme,
	                stroke: {
	                    width: 0
	                }
	            }));

	            if(dataItem.lead) {
			        g.append(new dataviz.diagram.TextBlock({
			            text: dataItem.lead,
			            x: 245,
			            y: 10,
			            fill: "#1E90FF",
			            fontSize: 20,
			            fontWeight: 700
			        }));

			        g.append(new dataviz.diagram.Image({
		                source: "api/v1/avatar/agent/" + dataItem.lead,
		                x: 212,
		                y: 6,
		                width: 26,
		                height: 26
		            }));
			    }

			    g.append(new dataviz.diagram.TextBlock({
		            text: dataItem.name,
		            x: 15,
		            y: 10,
		            fill: "#777",
		            fontSize: 20,
		            fontWeight: 700
		        }));

		        if(dataItem.members) {
			        g.append(new dataviz.diagram.TextBlock({
			            text: dataItem.members.join(", "),
			            x: 15,
			            y: 40,
			            fill: "#333",
			            fontSize: 16
			        }));
			    }
	    	}
    	}
        return g;
    }

    function createDigaram() {

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
                parse: function(res) {
                	res.map(doc => {
                		doc.expanded = doc.hasChild;
                	});
                	return res;
                }
            },
            error: errorDataSource
        });

        var viewModel = kendo.observable(Object.assign({
            dataSource: new kendo.data.HierarchicalDataSource({
                transport: {
	                read: ENV.vApi + Config.collection + "/readAll"
	            },
                schema: {
                    model: {
                        children: "items"
                    }
                },
	            error: errorDataSource
            }),
            onDiagramSelect: function(e) {
            	if(e.selected.length) {
	            	var selectedItem = e.selected[0],
	            		id = selectedItem.dataItem.id;
	            	$("#treeview").data("kendoTreeView").select($(`#${id}`).closest("li"));
	            	this.set("selectedItem", selectedItem.dataItem);
            	}
            },
            files: hierarchicalDataSource,
            openFormNode: function(e) {
            	if($("#form-organization-popup").data("kendoWindow")) {
                    $("#form-organization-popup").data("kendoWindow").destroy();
                }
                notification.show("@Wait a minute@");
                var item = this.selectedItem.toJSON();
            	var model = {
                    item: item,
                    organizationOption: dataSourceDropDownList("Organization", ["name"], null),
                    leadOption: dataSourceDropDownListPrivate("User", ["extension", "agentname"], {role_name: {$ne: "Agent"}}, function(res) {
                        res.data.unshift({extension: null, agentname: "@None@"});
                        return res;
                    }),
                    userOption: dataSourceDropDownListPrivate("User", ["extension", "agentname"], null, function(res) {
                    	return res;
                    }),
                    close: function(e) {
                    	$("#form-organization-popup").data("kendoWindow").close();
                    },
                    save: function(e) {
                    	var dataItem = this.item.toJSON(),
                        	treeview = $("#treeview").data("kendoTreeView"),
		                    that = this;
		                $.ajax({
	                        url: Config.crudApi + Config.collection + "/" + dataItem.id,
	                        type: "PUT",
	                        contentType: "application/json; charset=utf-8",
	                        data: kendo.stringify(dataItem),
	                        success: function(result) {
	                            that.set("hasChanges", false);
	                            syncDataSource();
	                            window.hierarchicalDataSource.read();
	                            $("#diagram").data("kendoDiagram").dataSource.read();
	                            $("#form-organization-popup").data("kendoWindow").close();
	                        },
	                        error: errorDataSource
	                    })
                    }
                };
            	var kendoView = new kendo.View("form-organization-template", {model: model, wrap: false});
                kendoView.render("#form-organization-container");
                $("#form-organization-popup").data("kendoWindow").center().open();
            }
        }, Config.observable));


        kendo.bind($("#allview"), viewModel);
    }

    window.onload = function() {

        var diagram = kendo.dataviz.diagram;
        var Shape = diagram.Shape;
        var Connection = diagram.Connection;
        var Point = diagram.Point; 

        $(document).ready(createDigaram).bind("kendo:skinChange", createDigaram);
    }
</script>

<script type="text/x-kendo-template" id="form-organization-template">
    <div data-role="window" id="form-organization-popup" style="padding: 14px 0"
         data-title="@Edit@ @Organization@"
         data-visible="false"
         data-actions="['Close']"
         data-bind="">
        <div class="k-edit-form-container" style="width: 540px">
            <div class="k-edit-label" style="width: 20%">
                <label>@Name@</label>
            </div>
            <div class="k-edit-field" style="width: 70%">
                <input class="k-textbox" data-bind="value: item.name" style="width: 100%">
            </div>
            <div class="k-edit-label" style="width: 20%">
                <label>@Superior@</label>
            </div>
            <div class="k-edit-field" style="width: 70%">
                <input style="width: 100%"
                data-role="dropdownlist"
                data-value-primitive="true"
                data-value-field="id" data-text-field="name" 
                data-bind="value: item.parent_id, source: organizationOption">
            </div>
            <div class="k-edit-label" style="width: 20%">
                <label>@Lead@</label>
            </div>
            <div class="k-edit-field" style="width: 70%">
                <input style="width: 100%"
                data-role="dropdownlist"
                data-value-primitive="true"
                data-value-field="extension" data-text-field="agentname"
                data-template="itemGroupTemplate"
                data-value-template="itemGroupTemplate"
                data-bind="value: item.lead, source: leadOption">
            </div>
            <div class="k-edit-label" style="width: 20%" data-bind="invisible: item.hasChild">
                <label>@Members@</label>
            </div>
            <div class="k-edit-field" style="width: 70%" data-bind="invisible: item.hasChild">
                <select style="width: 100%"
                data-role="multiselect"
                data-value-primitive="true"
                data-value-field="extension" data-text-field="agentname" 
                data-item-template="itemGroupTemplate"
				data-tag-template="tagGroupTemplate"
                data-bind="value: item.members, source: userOption"></select>
            </div>
            <div class="k-edit-label" style="width: 20%">
                <label>@Color@</label>
            </div>
            <div class="k-edit-field" style="width: 70%">
            	<input data-role="colorpicker"
                   data-bind="value: item.color">
            </div>
            <div class="k-edit-buttons k-state-default">
                <a class="k-button k-primary k-scheduler-update" data-bind="click: save">@Save@</a>
                <a class="k-button k-scheduler-cancel" href="#" data-bind="click: close">@Cancel@</a>
            </div>
        </div>
    </div>
</script>

<script id="itemGroupTemplate" type="text/x-kendo-template">
    <span class="selected-value" style="background-image: url('/api/v1/avatar/agent/#: data.extension #')"></span><span><b>#: (data.extension || "") #</b> (#:data.agentname#)</span>
</script>

<script id="tagGroupTemplate" type="text/x-kendo-template">
    <span class="selected-value" style="background-image: url('/api/v1/avatar/agent/#: data.extension #')"></span><span><b>#: (data.extension || "") #</b> (#:data.agentname#)</span>
</script>
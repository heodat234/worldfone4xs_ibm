<!-- Table Styles Header -->
<ul class="breadcrumb breadcrumb-top">
    <li>Admin</li>
    <li>Database</li>
</ul>
<!-- END Table Styles Header -->
<div class="container-fluid">
    <div class="row" style="padding-top: 20px">
        <div class="col-md-4">
            <!-- Web Server Block -->
            <div class="block full">
                <!-- Web Server Title -->
                <div class="block-title">
                    <div class="block-options pull-right">
                        <a role="button" class="btn btn-sm btn-alt btn-info btn-waring" href="javascript:void(0)" data-bind="click: backupDatabase"><i class="gi gi-coins"></i> <b>Dump</b></a>
                    </div>
                    <h2><strong data-bind="click: openJsDB">Database</strong></h2>
                </div>
                <!-- END Web Server Title -->
                <?php $_mongo_db = $this->config->item("_mongo_db"); ?>
                <div>
                    <button class="btn btn-sm btn-alt btn-success btn-database" href="javascript:void(0)" data-name="<?= substr($_mongo_db, 1) ?>" data-bind="click: selectDatabase"><b><?= substr($_mongo_db, 1) ?></b></button>
                    <button role="button" class="btn btn-sm btn-alt btn-success btn-database" href="javascript:void(0)" data-name="<?= $_mongo_db ?>" data-bind="click: selectDatabase"><b><?= $_mongo_db ?></b></button>
                </div>
                <div style="margin-top: 10px">
                    <label class="checkbox-inline">
                        <input type="checkbox" autocomplete="off" data-bind="checked: showFile, events: {change: showFileChange}">
                        <span>Show file</span>
                    </label>
                    <label class="checkbox-inline">
                        <input type="checkbox" autocomplete="off" data-bind="checked: visibleData,  events: {change: showDataChange}">
                        <span>Show data</span>
                    </label>
                </div>
            </div>
            <!-- END Web Server Block -->
        </div>

        <div class="col-md-8" data-bind="visible: dbname">
            <!-- Web Server Block -->
            <div class="block full">
                <!-- Web Server Title -->
                <div class="block-title">
                    <div class="block-options pull-right">
                        <a role="button" class="btn btn-sm btn-alt btn-success" data-bind="click: createCollection"><i class="fa fa-plus"></i></a>
                        <input data-role="autocomplete" data-placeholder="Search" 
                        data-text-field="name"  data-value-field="name"
                        data-filter="contains" style="width: 240px"
                        data-bind="source: collections, events: {change: searchChange, select: searchSelect}" style="margin-right: 100px" />
                        <a role="button" class="btn btn-sm btn-alt btn-warning" href="javascript:void(0)" data-bind="click: deleteManyCollection, visible: item.srcCollection"><i class="hi hi-remove-circle"></i> <b>Delete many</b></a>
                        <a role="button" class="btn btn-sm btn-alt btn-danger" href="javascript:void(0)" data-bind="click: dropCollection, visible: item.srcCollection"><i class="hi hi-remove-circle"></i> <b>Drop</b></a>
                    </div>
                    <h2><strong>Collection</strong></h2>
                </div>
                <!-- END Web Server Title -->

                <div>
                    <div data-template="collection-template" data-bind="source: collections" class="list-group"></div>
                    <div data-role="pager" data-bind="source: collections"></div>
                </div>

                <div style="margin-top: 20px" data-bind="visible: item.srcCollection">
                    <label>Destination collection</label>
                    <input class="k-textbox" data-bind="value: item.desCollection"/>
                    <label class="checkbox-inline">
                        <input type="checkbox" autocomplete="off" data-bind="checked: item.drop">
                        <span>Drop old collection</span>
                    </label>
                    <a role="button" class="btn btn-sm btn-alt btn-warning" href="javascript:void(0)" data-bind="click: restoreCollection, visible: item.desCollection"><i class="fa fa-recycle"></i> <b>Restore</b></a>
                </div>
            </div>
            <!-- END Web Server Block -->
        </div>
    </div>

    <div class="row" style="padding-top: 20px" data-bind="visible: visibleData">
        <div class="col-md-12">
            <!-- Web Server Block -->
            <div class="block full">
                <!-- Web Server Title -->
                <div class="block-title">
                    <h2><strong>Data</strong> <span data-bind="visible: item.srcCollection">-</span> <i data-bind="text: item.srcCollection"></i></h2>
                    <div class="block-options pull-right">
                        <span id="list-indexes"></span>
                        <a role="button" class="btn btn-sm btn-alt btn-success" href="javascript:void(0)" data-bind="click: createIndex"><i class="fa fa-sort"></i> <b>Add Index</b></a>
                        <a role="button" class="btn btn-sm btn-alt btn-success" href="javascript:void(0)" data-bind="click: createColumn"><i class="fa fa-columns"></i> <b>Add Column</b></a>
                        <a role="button" class="btn btn-sm btn-alt btn-success" href="javascript:void(0)" data-bind="click: importDocument"><i class="fa fa-database"></i> <b>Import</b></a>
                        <a href="javascript:void(0)" class="btn btn-alt btn-sm btn-primary" data-toggle="block-toggle-fullscreen"><i class="fa fa-desktop"></i></a>
                    </div>
                </div>
                <!-- END Web Server Title -->

                <div>
                    <div id="json-file-list" data-bind="visible: importJsonVisible" style="margin-bottom: 10px"></div>
                    <div id="grid"></div>
                </div>

            </div>
            <!-- END Web Server Block -->
        </div>
    </div>

    <div id="add-index-container"></div>
</div>
<style type="text/css">
    button.btn-collection {
        font-size: 14px; 
        padding: 2px 4px; 
        line-height: 1.6;
    }
    button.btn-database.selected, button.btn-collection.selected, button.btn-collection:hover {
        background-color: #7db831;
        border-color: #7db831;
        color: #ffffff;
    }
</style>
<script id="collection-template" type="text/x-kendo-template">
    <button class="btn btn-default btn-sm btn-alt btn-collection" href="javascript:void(0)"
     data-bind="click: selectCollection, css: {selected: selected}, attr: {data-collection: name}">
        # if(data.options.capped) {# <i class="fa fa-balance-scale"></i> #}#
        <span data-bind="text: name"></span>
    </button>
</script>

<script type="text/javascript">
var Config = {
    crudApi: `${ENV.reportApi}database/`,
    templateApi: `${ENV.templateApi}`,
    database: "",
    collection: "",
    observable: {
    },
    model: {
        id: "id",
        fields: {}
    },
    parse: function(res) {
        res.data.map(doc => {
            if(doc.data) doc.field_data = doc.data;
            if(doc.uid) doc.field_uid = doc.uid;
            delete doc.data;
            delete doc.uid;
        })
        return res;
    },
    columns: [],
    filterable: KENDO.filterable,
    scrollable: true
}; 
var Table = function() {
    return {
        dataSource: {},
        columns: Config.columns,
        customColumns: [],
        init: function() {
            var dataSource = this.dataSource = new kendo.data.DataSource({
                serverFiltering: true,
                serverPaging: true,
                serverSorting: true,
                serverGrouping: false,
                pageSize: 10,
                batch: false,
                schema: {
                    data: "data",
                    total: "total",
                    groups: "groups",
                    model: Config.model,
                    parse: Config.parse ? Config.parse : res => res
                },
                transport: {
                    read: {
                        url: Config.crudApi + "data/" + Config.database + "/" + Config.collection
                    },
                    destroy: {
                        url: function(data) {
                            return Config.crudApi + "delete/" + Config.database + "/" + Config.collection + "/" + data.id;
                        },
                        type: "DELETE",
                    },
                    parameterMap: parameterMap
                },
                sync: syncDataSource,
                error: errorDataSource
            });

            var grid = this.grid = $("#grid").kendoGrid({
                dataSource: dataSource,
                excel: {allPages: true},
                excelExport: function(e) {
                  var sheet = e.workbook.sheets[0];

                  for (var rowIndex = 1; rowIndex < sheet.rows.length; rowIndex++) {
                    var row = sheet.rows[rowIndex];
                    for (var cellIndex = 0; cellIndex < row.cells.length; cellIndex ++) {
                        if(row.cells[cellIndex].value instanceof Date) {
                            row.cells[cellIndex].format = "dd-MM-yy hh:mm:ss"
                        }
                    }
                  }
                },
                resizable: true,
                pageable: {
                    refresh: true,
                    pageSizes: [10,20,50,100],
                    input: true,
                    messages: KENDO.pageableMessages ? KENDO.pageableMessages : {}
                },
                sortable: true,
                reorderable: true,
                scrollable: Boolean(Config.scrollable),
                columns: this.columns,
                filterable: Config.filterable ? Config.filterable : true,
                editable: false,
                detailTemplate: kendo.template($("#detail-template").html()),
                detailInit:  function(e) {
                    var container = $(e.detailCell).find(".jsoneditor"); 
                    var options = {
                        mode: 'code',
                        modes: ['tree','code']
                    };
                    var jsonEditor = new JSONEditor(container[0], options);
                    jsonEditor.set(e.data);
                },
                noRecords: {
                    template: `<h2 class='text-danger'>${KENDO.noRecords}</h2>`
                }
            }).data("kendoGrid");

            grid.selectedKeyNames = function() {
                var items = this.select(),
                    that = this,
                    checkedIds = [];
                $.each(items, function(){
                    if(that.dataItem(this))
                        checkedIds.push(that.dataItem(this).uid);
                })
                return checkedIds;
            }

            /*
             * Right Click Menu
             */
            var menu = $("#action-menu");
            if(!menu.length) return;
            
            $("html").on("click", function() {menu.hide()});

            $(document).on("contextmenu", "#grid tr[role=row]", function(e){
                //prevent default context menu for right click
                e.preventDefault();
                showMenu(e, this);
            });

            function showMenu(e, that) {
                //hide menu if already shown
                menu.hide(); 

                //Get id value of document
                var uid = $(that).data('uid');
                if(uid)
                {
                    menu.find("a").data('uid', uid);

                    //get x and y values of the click event
                    var pageX = e.pageX;
                    var pageY = e.pageY;

                    //position menu div near mouse cliked area
                    menu.css({top: pageY , left: pageX});

                    var mwidth = menu.width();
                    var mheight = menu.height();
                    var screenWidth = $(window).width();
                    var screenHeight = $(window).height();

                    //if window is scrolled
                    var scrTop = $(window).scrollTop();

                    //if the menu is close to right edge of the window
                    if(pageX+mwidth > screenWidth){
                    menu.css({left:pageX-mwidth});
                    }

                    //if the menu is close to bottom edge of the window
                    if(pageY+mheight > screenHeight+scrTop){
                    menu.css({top:pageY-mheight});
                    }

                    //finally show the menu
                    menu.show();     
                }
            }
        }
    }
}();
</script>
<script type="text/javascript">
    window.onload = async function() {
        kendo.bind($("#page-content"), kendo.observable({
            item: {},
            showFile: false,
            showFileChange: function(e) {
                var showFile = e.currentTarget.checked;
                var dbname = this.get("dbname");
                if(dbname)
                    this.collections.read({db: dbname, file: Number(showFile)});
            },
            showDataChange: function(e) {
                var showData = e.currentTarget.checked;
                var dbname = this.get("dbname");
                var collectionName = this.get("item.srcCollection");
                if(showData && dbname && collectionName) {
                    detailData(dbname, collectionName);
                }
            },
            collections: new kendo.data.DataSource({
                pageSize: 20,
                transport: {
                    read: ENV.reportApi + "database/collections",
                },
                schema: {
                    data: "data",
                    total: "total"
                }
            }),
            searchChange: function() {
            },
            searchSelect: function(e) {
                var collectionName = e.dataItem.name;
                this.set("item.srcCollection", collectionName);
                var collectionData = this.collections.data().toJSON();
                collectionData.map(doc => {
                    if(doc.name == collectionName) {
                        doc.selected = true;
                    } else doc.selected = false;
                })
                this.collections.data(collectionData);
                if(this.get("visibleData"))
                    detailData(this.get("dbname"), collectionName);
            },
            selectDatabase: function(e) {
                $(".btn-database").removeClass("selected");
                $(e.currentTarget).addClass("selected");
                var dbname = $(e.currentTarget).data("name");
                var showFile = this.get("showFile");
                this.set("dbname", dbname);
                this.collections.read({db: dbname, file: Number(showFile)});
            },
            backupDatabase: function(e) {
                var dbname = this.get('dbname');
                if(dbname) {
                    swal({
                        title: `Are you sure?`,
                        text: `Backup database ${dbname}`,
                        icon: "warning",
                        buttons: true,
                        dangerMode: false,
                    })
                    .then((sure) => {
                        if (sure) {
                            $.ajax({
                                url: ENV.reportApi + "database/mongodump/" + dbname,
                                success: (res) => {
                                    if(res.status)
                                        notification.show("Success", "success");
                                    else notification.show("Error", "error");
                                }
                            })
                        }
                    });
                } else {
                    notification.show("Please select db");
                }
            },
            selectCollection: function(e) {
                $currentTarget = $(e.currentTarget);
                var collectionName = $currentTarget.data("collection");
                var collectionData = this.collections.data().toJSON();
                this.set("item.srcCollection", collectionName);
                this.set("item.desCollection", collectionName);
                collectionData.map(doc => {
                    if(doc.name == collectionName) {
                        doc.selected = true;
                    } else doc.selected = false;
                })
                this.collections.data(collectionData);
                this.set("importJsonVisible", false);
                Table.customColumns = [];
                if(this.get("visibleData"))
                    detailData(this.get("dbname"), collectionName);
            },
            restoreCollection: function(e) {
                var dbname = this.get('dbname');
                var item = this.get('item').toJSON();
                swal({
                    title: `Are you sure?`,
                    text: `Restore collection ${item.desCollection} from ${item.srcCollection} ${item.drop ? "with option drop" : ""}`,
                    icon: "warning",
                    buttons: true,
                    dangerMode: false,
                })
                .then((sure) => {
                    if (sure) {
                        $.ajax({
                            url: ENV.reportApi + "database/mongorestore_collection/" + dbname,
                            data: item,
                            success: (res) => {
                                if(res.status) {
                                    notification.show(res.message, "success");
                                    this.collections.read({db: dbname});
                                } else notification.show("Error", "error");
                            }
                        })
                    }
                });
            },
            dropCollection: function(e) {
                var dbname = this.get('dbname');
                var item = this.get('item').toJSON();
                swal({
                    title: `Are you sure?`,
                    text: `Drop collection ${item.srcCollection}.`,
                    icon: "warning",
                    buttons: true,
                    dangerMode: false,
                })
                .then((sure) => {
                    if (sure) {
                        $.ajax({
                            url: ENV.reportApi + "database/drop_collection/" + dbname,
                            data: {collection: item.srcCollection},
                            success: (res) => {
                                if(res.status) {
                                    syncDataSource();
                                    this.collections.read({db: dbname});
                                } else notification.show("Error", "error");
                            }
                        })
                    }
                });
            },
            deleteManyCollection: function(e) {
                var dbname = this.get('dbname');
                var item = this.get('item').toJSON();
                swal({
                    title: `Delete many in collection ${item.srcCollection}.`,
                    text: `Please type your condition (Json text) to delete many documents. Empty to delete all.`,
                    icon: "warning",
                    content: "input",
                    buttons: true,
                    dangerMode: false,
                })
                .then((condition) => {
                    if(condition === null) return;
                    $.ajax({
                        url: ENV.reportApi + "database/delete_many/" + dbname,
                        data: {collection: item.srcCollection, where: condition ? JSON.stringify(condition) : []},
                        success: (res) => {
                            if(res.status) {
                                syncDataSource();
                                this.collections.read({db: dbname});
                            } else notification.show("Error", "error");
                        }
                    })
                });
            },
            openJsDB: function() {
                openForm({title: "Run js db"});
                kendo.destroy($("#right-form"));
                var HTML = $("#js-template").html();
                var kendoView = new kendo.View(HTML, {wrap: false, evalTemplate: false, model: {
                    run: function() {
                        console.log(this.get("code"));
                    }
                }});
                kendoView.render($("#right-form"));
            },
            createIndex: function() {
                if($("#add-index-popup").data("kendoWindow")) {
                    $("#add-index-popup").data("kendoWindow").destroy();
                }
                var model = {
                    item: {},
                    database: this.get("dbname"),
                    collection: this.get("item.srcCollection"),
                    arrayOpen: function(e) {
                        e.preventDefault();
                        var widget = e.sender;
                        widget.input[0].onkeyup = function(ev) {
                            if(ev.keyCode == 13 && this.value) {
                                var values = widget.value();
                                values.push(this.value);
                                widget.dataSource.data(values);
                                widget.value(values);
                                widget.trigger("change");
                            } else {
                                // Use for onblur event
                                window.temp_5dce0f6b1ef2b406222fd053 = this.value;
                            }
                        }
                        widget.input[0].onblur = function(ev) {
                            if(window.temp_5dce0f6b1ef2b406222fd053) {
                                var values = widget.value();
                                values.push(window.temp_5dce0f6b1ef2b406222fd053);
                                widget.dataSource.data(values);
                                widget.value(values);
                                widget.trigger("change");
                            }
                        }
                    },
                    close: function(e) {
                        $("#add-index-popup").data("kendoWindow").close();
                    },
                    save: function(e) {
                        var database = this.get("database"),
                            collection = this.get("collection");

                        var item = this.get("item").toJSON();
                        if(item.fields) {
                            data = {keys: {}, unique: item.unique};
                            item.fields.forEach(field => {
                                data.keys[field] = Boolean(item.sort);
                            })
                            if(item.name) data.name = item.name; 
                            $.ajax({
                                url: ENV.reportApi + `database/add_index/${database}/${collection}`,
                                type: "POST",
                                contentType: "application/json; charset=utf-8",
                                data: JSON.stringify(data),
                                success: (res) => {
                                    if(res.status) {
                                        syncDataSource();
                                        listIndexes(database, collection);
                                        this.close();
                                    } else notification.show(res.message, "error");
                                }
                            })
                        } else if(item.isExpireIndex) {
                            $.ajax({
                                url: ENV.reportApi + `database/add_expire_index/${database}/${collection}`,
                                type: "POST",
                                contentType: "application/json; charset=utf-8",
                                data: JSON.stringify(item),
                                success: (res) => {
                                    if(res.status) {
                                        syncDataSource();
                                        listIndexes(database, collection);
                                        this.close();
                                    } else notification.show(res.message, "error");
                                }
                            })
                        }
                    }
                };
                var kendoView = new kendo.View("add-index-template", {model: model, wrap: false});
                kendoView.render("#add-index-container");
                $("#add-index-popup").data("kendoWindow").center().open();
            },
            createColumn: function() {
                if($("#add-column-popup").data("kendoWindow")) {
                    $("#add-column-popup").data("kendoWindow").destroy();
                }
                var model = {
                    item: {width: 100, type: "string"},
                    dataTypeOption: ["string", "number", "date", "boolean"],
                    close: function(e) {
                        $("#add-column-popup").data("kendoWindow").close();
                    },
                    add: function(e) {
                        var dataItem = this.get("item").toJSON();
                        Table.customColumns.push(dataItem);
                        Config.model.fields[dataItem.field] = {type: dataItem.type}
                        detailData(Config.database, Config.collection);
                        this.close();
                    }
                };
                var kendoView = new kendo.View("add-column-template", {model: model, wrap: false});
                kendoView.render("#add-index-container");
                $("#add-column-popup").data("kendoWindow").center().open();
            },
            importDocument: function() {
                this.set("importJsonVisible", true);
                var db = this.get("dbname"),
                    collection = this.get("item.srcCollection");
                $jsonFileList = $("#json-file-list");
                if($jsonFileList.data("kendoGrid")) {
                    $jsonFileList.data("kendoGrid").destroy();
                    $jsonFileList.empty();
                }
                $("#json-file-list").kendoGrid({
                    columns: [
                        {field: "file", title: "File", width: 320},
                        {field: "content", title: "Content", template: data => gridLongText(data.content, 70)},
                        {field: "time", title: "Last access", width: 140, template: data => gridTimestamp(data.time)},
                        {title: "Action", command: [{name: "import", text: "Import", click: importFile}, {name: "importthendelete", text: "Import then Delete", click: importFileThenDelete}], width: 120}
                    ],
                    sortable: true,
                    filterable: KENDO.filterable,
                    serverPaging: false,
                    pageable: {refresh: true, pageSizes: [5, 10, 20, 50, 100, "all"], pageSize: 5},
                    dataSource: {
                        transport: {
                            read: ENV.reportApi + `database/json_file_list/${db}/${collection}`,
                        },
                        schema: {
                            data: "data",
                            total: "total"
                        }
                    },
                    detailTemplate: kendo.template($("#detail-template").html()),
                    detailInit:  function(e) {
                        var container = $(e.detailCell).find(".jsoneditor"); 
                        var options = {
                            mode: 'code',
                            modes: ['tree','code']
                        };
                        var jsonEditor = new JSONEditor(container[0], options);
                        jsonEditor.set(JSON.parse(e.data.content));
                    },
                });
            },
            createCollection: function() {
                if($("#add-collection-popup").data("kendoWindow")) {
                    $("#add-collection-popup").data("kendoWindow").destroy();
                }
                var model = {
                    item: {},
                    database: this.get("dbname"),
                    collection: this.get("item.srcCollection"),
                    close: function(e) {
                        $("#add-collection-popup").data("kendoWindow").close();
                    },
                    add: function(e) {
                        var data = this.get("item").toJSON(),
                            database = this.get("database");
                        $.ajax({
                            url: ENV.reportApi + `database/add_collection/${database}`,
                            type: "POST",
                            contentType: "application/json; charset=utf-8",
                            data: JSON.stringify(data),
                            success: (res) => {
                                if(res.status) {
                                    notification.show("Create success", "success");
                                    this.close();
                                } else notification.show(res.message, "error");
                            }
                        })
                    }
                };
                var kendoView = new kendo.View("add-collection-template", {model: model, wrap: false});
                kendoView.render("#add-index-container");
                $("#add-collection-popup").data("kendoWindow").center().open();
            }
        }));    
    }

    function detailData(database, collection, columns = []) {
        if(Table.grid) {
            Table.grid.destroy();
            Table.grid = false;
            $("#grid").empty();
        }
        var collectionFields = new kendo.data.DataSource({
            serverFiltering: true,
            serverSorting: true,
            serverPaging: true,
            pageSize: 10,
            transport: {
                read: `${Config.crudApi}data/${database}/${collection}`,
                parameterMap: parameterMap
            },
            schema: {
                data: "data",
            }
        })
        collectionFields.read().then(() => {
            var data = collectionFields.data().toJSON();
            if(data[0]) {
                var listedProp = [];
                data.forEach((doc, idx) => {
                    for(var prop in doc) {
                        if(listedProp.indexOf(prop) == -1) {
                            columns.push({field: prop, width: 140});
                            listedProp.push(prop);
                        }
                    }
                })
                Config.database = database;
                Config.collection = collection;
                Table.columns = Table.customColumns.concat(columns);
                Table.init();
                listIndexes(database, collection);
            }
        })
    }

    function listIndexes(database, collection) {
        $.ajax({
            url: ENV.reportApi + `database/list_indexes/${database}/${collection}`,
            success: (res) => {
                var indexesHtmlArr = [];
                if(res.total) {
                    res.data.forEach(doc => {
                        var docHtmlArr = [];
                        for(var prop in doc.key) {
                            docHtmlArr.push(`<b>${prop}</b>&nbsp;${(doc.key[prop] == 1) ? '<i class="fa fa-sort-alpha-asc"></i>' : '<i class="fa fa-sort-alpha-desc"></i>'}&nbsp;`)
                            if(doc.unique) {
                                docHtmlArr.push('<i class="fa fa-thumbs-up text-muted"></i>');
                            }
                            if(doc.expireAfterSeconds != undefined) {
                                docHtmlArr.push(`<span class="text-muted"><i class="fa fa-clock-o"></i> <i>${doc.expireAfterSeconds}s</i></span>`);
                            }
                        }
                        indexesHtmlArr.push('<span class="label label-info">' + docHtmlArr.join('') + (doc.name != "_id_" ? `<a href="javascript:void(0)" onclick="dropIndex('${database}', '${collection}', '${doc.name}')"><i class="fa fa-times text-danger"></i></a></span>` : "</span>"));
                    })
                }
                $("#list-indexes").html(indexesHtmlArr.join("&nbsp;"));
            }
        })
    }

    function dropIndex(database, collection, name) {
        swal({
            title: "Are you sure?",
            text: "Delete this index!",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        })
        .then((willDelete) => {
            if (willDelete) {
                $.ajax({
                    url: ENV.reportApi + `database/remove_index/${database}/${collection}`,
                    data: {name: name},
                    success: (res) => {
                        if(res.status) listIndexes(database, collection);
                        else notification.show(res.message, "error");
                    }
                })
            }
        });
    }

    function deleteDataItem(ele) {
        swal({
            title: "Are you sure?",
            text: "Once deleted, you will not be able to recover this document!",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        })
        .then((willDelete) => {
            if (willDelete) {
                var dataItem = Table.dataSource.getByUid($(ele).data("uid"));
                Table.dataSource.remove(dataItem);
                Table.dataSource.sync();
            }
        });
    }

    function exportDataItem(ele) {
        swal({
            title: "Are you sure?",
            text: "Export this document to JSON file!",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        })
        .then((sure) => {
            if (sure) {
                var dataItem = Table.dataSource.getByUid($(ele).data("uid"));
                $.ajax({
                    url: ENV.reportApi + `database/export_document`,
                    data: {db: Config.database, collection: Config.collection, id: dataItem.id},
                    type: "POST",
                    success: (res) => {
                        if(res.status) {
                            notification.show(res.message, "success");
                        } else notification.show(res.message, "error");
                    }
                })
            }
        });
    }

    function importFile(e) {
        e.preventDefault();
        var dataItem = this.dataItem($(e.currentTarget).closest("tr"));
        $.post({
            url: ENV.reportApi + "database/import_document",
            data: {file: dataItem.file},
            success: function(res) {
                if(res.status) {
                    Table.dataSource.read();
                    notification.show(res.message, "success");
                    $("#json-file-list").data("kendoGrid").dataSource.read();
                } else notification.show(res.message, "error");
            }
        })
    }

    function importFileThenDelete(e) {
        e.preventDefault();
        var dataItem = this.dataItem($(e.currentTarget).closest("tr"));
        $.post({
            url: ENV.reportApi + "database/import_document/delete",
            data: {file: dataItem.file},
            success: function(res) {
                if(res.status) {
                    Table.dataSource.read();
                    notification.show(res.message, "success");
                    $("#json-file-list").data("kendoGrid").dataSource.read();
                } else notification.show(res.message, "error");
            }
        })
    }
</script>

<div id="action-menu">
    <ul>
        <a href="javascript:void(0)" data-type="export" onclick="exportDataItem(this)"><li><i class="fa fa-file-code-o text-success"></i><span>Export</span></li></a>
        <li class="devide"></li>
        <a href="javascript:void(0)" data-type="delete" onclick="deleteDataItem(this)"><li><i class="fa fa-times-circle text-danger"></i><span>Delete</span></li></a>
    </ul>
</div>

<script type="text/x-kendo-template" id="detail-template">
    <div class="jsoneditor" style="width: 100%; height: 400px;"></div>
</script>

<script type="text/x-kendo-template" id="js-template">
    <div class="container-fluid" style="min-height: 90vh">
        <textarea class="k-textbox" data-bind="value: code"></textarea>
        <a role="button" class="k-textbox" data-bind="click: run"></a>
    </div>
</script>

<script type="text/x-kendo-template" id="add-index-template">
    <div data-role="window" id="add-index-popup" style="padding: 14px 0"
         data-title="Add index"
         data-visible="false"
         data-actions="['Close']"
         data-bind="">
        <div class="k-edit-form-container" style="width: 480px">
            <div class="k-edit-label" style="width: 20%">
                <label>Name</label>
            </div>
            <div class="k-edit-field" style="width: 70%">
                <input class="k-textbox" data-bind="value: item.name" style="width: 100%">
            </div>
            <div class="k-edit-label" style="width: 20%">
                <label>TTL index</label>
            </div>
            <div class="k-edit-field" style="width: 70%">
                <div class="onoffswitch" id="expire-index-switch">
                    <input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" id="expire-switch" data-bind="checked: item.isExpireIndex">
                    <label class="onoffswitch-label" for="expire-switch">
                        <span class="onoffswitch-inner"></span>
                        <span class="onoffswitch-switch"></span>
                    </label>
                </div>
            </div>
            <div class="k-edit-label" style="width: 20%" data-bind="invisible: item.isExpireIndex">
                <label>Fields</label>
            </div>
            <div class="k-edit-field" style="width: 70%" data-bind="invisible: item.isExpireIndex">
                <select style="width: 100%" data-role="multiselect"
                data-bind="value: item.fields, source: item.fields, events: {open: arrayOpen}"></select>
            </div>
            <div class="k-edit-label" style="width: 20%" data-bind="invisible: item.isExpireIndex">
                <label>Sort</label>
            </div>
            <div class="k-edit-field" style="width: 70%" data-bind="invisible: item.isExpireIndex">
                <div class="onoffswitch" id="sort-index-switch">
                    <input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" id="myonoffswitch" data-bind="checked: item.sort">
                    <label class="onoffswitch-label" for="myonoffswitch">
                        <span class="onoffswitch-inner"></span>
                        <span class="onoffswitch-switch"></span>
                    </label>
                </div>
            </div>
            <div class="k-edit-label" style="width: 20%" data-bind="invisible: item.isExpireIndex">
                <label>Unique</label>
            </div>
            <div class="k-edit-field" style="width: 70%" data-bind="invisible: item.isExpireIndex">
                <div class="onoffswitch" id="unique-index-switch">
                    <input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" id="unique-switch" data-bind="checked: item.unique">
                    <label class="onoffswitch-label" for="unique-switch">
                        <span class="onoffswitch-inner"></span>
                        <span class="onoffswitch-switch"></span>
                    </label>
                </div>
            </div>
            <div class="k-edit-label" style="width: 20%" data-bind="visible: item.isExpireIndex">
                <label>Field</label>
            </div>
            <div class="k-edit-field" style="width: 70%" data-bind="visible: item.isExpireIndex">
                <input class="k-textbox" data-bind="value: item.expireField" style="width: 100%">
            </div>
            <div class="k-edit-label" style="width: 20%" data-bind="visible: item.isExpireIndex">
                <label>After seconds</label>
            </div>
            <div class="k-edit-field" style="width: 70%" data-bind="visible: item.isExpireIndex">
                <input data-role="numerictextbox" data-bind="value: item.expireAfterSeconds" style="width: 100%">
            </div>
            <div class="k-edit-buttons k-state-default">
                <a class="k-button k-primary k-scheduler-update" data-bind="click: save">Save</a>
                <a class="k-button k-scheduler-cancel" href="#" data-bind="click: close">Cancel</a>
            </div>
        </div>
    </div>
</script>

<script type="text/x-kendo-template" id="add-column-template">
    <div data-role="window" id="add-column-popup" style="padding: 14px 0"
         data-title="Add column"
         data-visible="false"
         data-actions="['Close']"
         data-bind="">
        <div class="k-edit-form-container" style="width: 360px">
            <div class="k-edit-label" style="width: 20%">
                <label>Field</label>
            </div>
            <div class="k-edit-field" style="width: 70%">
                <input class="k-textbox" data-bind="value: item.field" style="width: 100%">
            </div>
            <div class="k-edit-label" style="width: 20%">
                <label>Width</label>
            </div>
            <div class="k-edit-field" style="width: 70%">
                <input data-role="numerictextbox" data-format="n0" data-bind="value: item.width" style="width: 100%">
            </div>
            <div class="k-edit-label" style="width: 20%">
                <label>Type</label>
            </div>
            <div class="k-edit-field" style="width: 70%">
                <input data-role="dropdownlist" data-bind="value: item.type, source: dataTypeOption" style="width: 100%">
            </div>
            <div class="k-edit-buttons k-state-default">
                <a class="k-button k-primary k-scheduler-update" data-bind="click: add">Add</a>
                <a class="k-button k-scheduler-cancel" href="#" data-bind="click: close">Cancel</a>
            </div>
        </div>
    </div>
</script>

<script type="text/x-kendo-template" id="add-collection-template">
    <div data-role="window" id="add-collection-popup" style="padding: 14px 0"
         data-title="Create collection"
         data-visible="false"
         data-actions="['Close']"
         data-bind="">
        <div class="k-edit-form-container" style="width: 360px">
            <div class="k-edit-label" style="width: 20%">
                <label>Name</label>
            </div>
            <div class="k-edit-field" style="width: 70%">
                <input class="k-textbox" data-bind="value: item.create" style="width: 100%">
            </div>
            <div class="k-edit-label" style="width: 20%">
                <label>Capped</label>
            </div>
            <div class="k-edit-field" style="width: 70%">
                <div class="onoffswitch">
                    <input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" id="is-capped-switch" data-bind="checked: item.capped">
                    <label class="onoffswitch-label" for="is-capped-switch">
                        <span class="onoffswitch-inner"></span>
                        <span class="onoffswitch-switch"></span>
                    </label>
                </div>
            </div>
            <div class="k-edit-label" style="width: 20%" data-bind="visible: item.capped">
                <label>Size</label>
            </div>
            <div class="k-edit-field" style="width: 70%" data-bind="visible: item.capped">
                <input data-role="numerictextbox" data-bind="value: item.size" style="width: 100%">
            </div>
            <div class="k-edit-label" style="width: 20%" data-bind="visible: item.capped">
                <label>Max</label>
            </div>
            <div class="k-edit-field" style="width: 70%" data-bind="visible: item.capped">
                <input data-role="numerictextbox" data-bind="value: item.max" style="width: 100%">
            </div>
            <div class="k-edit-label" style="width: 20%" data-bind="visible: item.capped">
            </div>
            <div class="k-edit-field" style="width: 70%" data-bind="visible: item.capped">
                <a href="https://docs.mongodb.com/manual/core/capped-collections/" target="_blank">
                    <i class="fa fa-balance-scale"></i> View document
                </a>
            </div>
            <div class="k-edit-buttons k-state-default">
                <a class="k-button k-primary k-scheduler-update" data-bind="click: add">Add</a>
                <a class="k-button k-scheduler-cancel" href="#" data-bind="click: close">Cancel</a>
            </div>
        </div>
    </div>
</script>

<style type="text/css">
    #sort-index-switch .onoffswitch-inner:before {content: "ASC";}
    #sort-index-switch .onoffswitch-inner:after {content: "DESC";}
    #expire-index-switch .onoffswitch-inner:before {content: "YES";}
    #expire-index-switch .onoffswitch-inner:after {content: "NO";}
    #unique-index-switch .onoffswitch-inner:before {content: "YES";}
    #unique-index-switch .onoffswitch-inner:after {content: "NO";}
</style>
<div class="col-sm-6 mvvm">
    <div data-bind="invisible: visibleData">
        <h4 class="fieldset-legend" style="margin: 0 0 20px"><span style="font-weight: 500">@IMPORT@</span></h4>
        <div id="popup-tabstrip" data-role="tabstrip" style="margin-top: 2px">
            <ul>
                <li class="k-state-active">
                    EXCEL
                </li>
                <li>
                    CSV
                </li>
                <li>
                    FTP
                </li>
            </ul>
            <div>
                <div class="container-fluid">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-sm-12 text-center" style="padding: 10px">
                                <a data-role="button" data-bind="click: uploadExcel">@Upload@ excel</a>
                                <div class="hidden">
                                    <input name="file" type="file" id="upload-excel"
                                           data-role="upload"
                                           data-multiple="false"
                                           data-async="{ saveUrl: '/api/v1/upload/excel', autoUpload: true }"
                                           data-bind="events: { success: uploadExcelSuccess }">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-12 text-center" style="padding: 10px">
                            <a data-role="button" data-bind="click: uploadExcel">@Upload@ csv</a>
                            <div class="hidden">
                                <input name="file" type="file" id="upload-csv"
                                       data-role="upload"
                                       data-multiple="false"
                                       data-async="{ saveUrl: '/api/v1/upload/csv', autoUpload: true }"
                                       data-bind="events: { success: uploadExcelSuccess }">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <div class="container-fluid">
                    <div id="ftp-grid" data-role="grid"
                         data-pageable="true"
                         data-filterable="true"
                         data-columns="[{
                            field: 'filename',
                            title: 'File name',
                            template: '<a role=\'button\' href=\'javascript:void(0)\' class=\'btn btn-sm\' onclick=\'import_ftp(this)\'><b>#= filename #</b></a>'
                         }]"
                         data-bind="source: ftpData"></div>
                </div>
            </div>
        </div>
    </div>
    <div data-bind="visible: visibleData">
        <div style="padding: 0; height: 70vh; overflow-y: scroll;">
            <h4 class="fieldset-legend" style="margin: 0 0 20px"><span style="font-weight: 500">@FIELD MATCH COLUMN@</span></h4>
            <div class="col-xs-6" style="padding: 2px">
                <div data-template="field-template" data-bind="source: columns"></div>
            </div>
            <div class="col-xs-6">
                <div data-template="data-field-template" data-bind="source: dataColumns" id="data-field"></div>
            </div>
        </div>
        <div class="text-center">
            <button data-role="button" data-bind="click: import">@Import@</button>
        </div>
    </div>
</div>
<div class="col-sm-12 mvvm" data-bind="visible: visibleData">
    <h4 class="fieldset-legend" style="margin: 0 0 20px"><span style="font-weight: 500">@DATA@ @FILE@ (<b data-bind="text: visibleData"></b>)</span></h4>
    <div>
        <div data-role="grid" id="data-grid"
             data-sortable="true"
             data-editable="true"
             data-resizable="true"
             data-pageable="true"></div>
    </div>
</div>
<style>
    .item {
        margin: 2px;
        padding: 0 10px 0 0;
        min-width: 50px;
        background-color: #fff;
        border: 1px solid rgba(0,0,0,.1);
        border-radius: 3px;
        font-size: 1em;
        line-height: 2.5em;
    }
    .handler {
        display: inline-block;
        width: 30px;
        margin-right: 10px;
        border-radius: 3px 0 0 3px;
        background-color: deepskyblue;
    }

    .item .item-name {
        display: block;
        width:  calc(100% - 40px);
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
        float: right;
    }
</style>
<script type="text/javascript">
    var customerFields = new kendo.data.DataSource({
        serverFiltering: true,
        serverSorting: true,
        transport: {
            read: `${ENV.vApi}model/read`,
            parameterMap: parameterMap
        },
        schema: {
            data: "data",
            parse: function(response) {
                response.data.map(doc => {
                    if(doc.sub_type)
                        doc.subType = JSON.parse(doc.sub_type);
                    else doc.subType = {};
                })
                response.data = response.data.filter(function(doc) {
                    return doc.subType.import;
                })
                response.data.sort((a,b) => Number(a.subType.import) - Number(b.subType.import));
                return response;
            }
        },
        filter: {
            field: "collection",
            operator: "eq",
            value: (ENV.type ? ENV.type + "_" : "") + "Appointment"
        },
        sort: {field: "index", dir: "asc"}
    })
    customerFields.read().then(function(){
        var columns = customerFields.data().toJSON();
        var columnModel = arrayColumn(columns, 'type', 'field');
        var model = {
            file: {},
            columns: columns,
            visibleData: 0,
            data: new kendo.data.DataSource({
                pageSize: 5,
                transport: {
                    read: {
                        url: ENV.reportApi + "excecuteExcel/read",
                        data: {
                            limit_column: "P",
                            pageSize: null
                        }
                    }
                },
                schema: {
                    data: "data",
                    total: "total"
                },
            }),
            dataColumns: [],
            uploadExcel: function(e) {
                $("#upload-excel").click();
            },
            uploadExcelSuccess: function(e) {
                swal({
                    title: "@Are you sure@?",
                    text: `@Import this data@.`,
                    icon: "warning",
                    buttons: true,
                    dangerMode: false,
                })
                .then((sure) => {
                    $.ajax({
                        url: `${ENV.vApi}${Config.collection}/importExcel`,
                        type: "PATCH",
                        contentType: "application/json; charset=utf-8",
                        data: kendo.stringify({filepath: e.response.filepath, import_type: 'manual', import_file_type: 'excel'}),
                        success: function(res) {
                            if(res.status == 1) {
                                syncDataSource();
                                router.navigate(`/`);
                            }
                            else if(res.status == 2) {
                                notification.show(res.message, "warning");
                            }
                            else {
                                notification.show("Đã có lỗi trong quá trình nhập dữ liệu. Xin vui lòng kiểm tra lại trong lịch sử nhập dữ liệu", "error");
                            }
                        },
                        error: errorDataSource
                    })
                })
            },
            import: function() {
                swal({
                    title: "@Are you sure@?",
                    text: `@Import this data@.`,
                    icon: "warning",
                    buttons: true,
                    dangerMode: false,
                })
                    .then((sure) => {
                        if(sure) {
                            var columns = this.columns.toJSON(),
                                dataColumns = this.get("dataColumns").toJSON(0),
                                colToField = {};
                            for (var i = 0; i < dataColumns.length; i++) {
                                if(columns[i])
                                    colToField[dataColumns[i].field.substr(1)] = columns[i].field;
                            }
                            this.save(this.file.filepath, colToField, $("#data-grid").data("kendoGrid").dataSource.total(), columnModel);
                        }
                    })
            },
            save: function(filepath, convert, totaldata, columnModel) {
                $.ajax({
                    url: `${ENV.vApi}${Config.collection}/importExcel`,
                    type: "PATCH",
                    contentType: "application/json; charset=utf-8",
                    data: kendo.stringify({filepath: filepath, convert: convert, import_type: 'manual', import_file_type: 'excel', total_data: totaldata, columnModel: columnModel}),
                    success: function(res) {
                        console.log(res);
                        if(res.status === 1) {
                            syncDataSource();
                            router.navigate(`/`);
                        }
                        else if(res.status === 2) {
                            notification.show(res.message, "warning");
                        }
                        else {
                            if(res.messsage) {
                                notification.show(res.message, "error");
                            }
                            else {
                                notification.show("Đã có lỗi trong quá trình nhập dữ liệu. Xin vui lòng kiểm tra lại trong lịch sử nhập dữ liệu", "error");
                            }
                        }
                    },
                    error: errorDataSource
                })
            },
            ftpData: new kendo.data.DataSource({
                pageSize: 5,
                transport: {
                    read: {
                        url: ENV.vApi + "appointment/listFileFTP",
                        data: function() {
                            return {
                                'ftp_filepath': Config.ftp_filepath,
                            }
                        }
                    },
                    parameterMap: parameterMap
                },
                schema: {
                    data: "data",
                    total: "total"
                },
            }),
        };
        kendo.bind(".mvvm", kendo.observable(model));
    });

    function uploadExcelSuccess(filepath) {
        swal({
            title: "@Are you sure@?",
            text: `@Import this data@.`,
            icon: "warning",
            buttons: true,
            dangerMode: false,
        })
        .then((sure) => {
            $.ajax({
                url: `${ENV.vApi}${Config.collection}/importFTP`,
                type: "PATCH",
                contentType: "application/json; charset=utf-8",
                data: kendo.stringify({filepath: filepath, import_type: 'FTP', import_file_type: 'excel'}),
                success: function(res) {
                    if(res.status == 1) {
                        syncDataSource();
                        router.navigate(`/`);
                    }
                    else if(res.status == 2) {
                        notification.show(res.message, "warning");
                    }
                    else {
                        notification.show("Đã có lỗi trong quá trình nhập dữ liệu. Xin vui lòng kiểm tra lại trong lịch sử nhập dữ liệu", "error");
                    }
                },
                error: errorDataSource
            })
        })
    }

    function import_ftp(ele) {
        var ftp_grid = $("#ftp-grid").data("kendoGrid");
        var selectedNode = ftp_grid.select(),
            dataItem = ftp_grid.dataItem($(ele).closest("tr"));
        uploadExcelSuccess(dataItem.filepath);
    }
</script>

<script type="text/x-kendo-template" id="field-template">
    <div class="item">
        <span style="margin-left: 10px" data-bind="text: title"></span>
        <i class="fa fa-arrow-circle-o-right text-success" style="float: right; margin-top: 10px"></i>
    </div>
</script>

<script type="text/x-kendo-template" id="data-field-template">
    <div class="item">
        <span class="handler text-center"><i class="fa fa-arrows-v"></i></span>
        <span class="item-name" data-bind="text: title"></span>
    </div>
</script>
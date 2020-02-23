<?php $id = $this->input->get("id") ?>
<div class="col-sm-3" style="margin: 10px 0" id="page-widget"></div>
<div class="col-sm-9 change-mvvm" style=" margin: 10px 0;">
    <div class="col-sm-1"><a role="button" data-type="action/delete" class="btn btn-alt btn-sm btn-primary" data-toggle="dropdown" onclick="removeRow(this)"><i class="fa fa-remove"></i> <b>@Remove@</b></a></div>
    <div class="col-sm-2" style="text-align: right;margin-top: 6px;"><label>Re-Assign</label></div>
    <div class="col-sm-4" class="form-group">
        <input data-role="dropdownlist"
               data-text-field="agentname"
               data-value-field="extension"
                    data-value-primitive="true"
                    data-bind="source: userListData" style="width: 100%" id="changeAssign">
    </div>
    <div class="col-sm-1">
        <button class="btn btn-sm btn-primary btn-save" data-type="action/reAssign" onclick="saveChangeAssign()">@Save@</button>
    </div>
</div>
<div class="row" style="margin: 10px 0">
	<div class="col-sm-2" id="page-widget"></div>
	<div class="col-sm-9 filter-mvvm" style="display: none"></div>
    <div class="col-sm-1" style=" margin: 10px 0; float: right;">
        <a style="display: none" role="button" class="btn btn-sm" onclick="saveAsExcel()"><i class="fa fa-file-excel-o"></i> <b>@Export@</b></a>
    </div>
</div>
<!-- <div class="col-sm-12 filter-mvvm" style="display: none; margin: 10px 0"></div> -->
<div class="col-sm-12" style="overflow-y: auto; padding: 0">
	<div id="grid"></div>
</div>
<div id="action-menu">
    <ul>
        <a href="javascript:void(0)" onclick="approve(this)"><li><i class="fa fa-check text-info"></i><span>Approve</span></li></a>
        <a href="javascript:void(0)" onclick="reject(this)"><li><i class="fa fa-times text-danger"></i><span>Reject</span></li></a>
    </ul>
</div>
<style type="text/css">
    .change-mvvm {
        display: none;
    }
</style>
<script>
    var Config = Object.assign(Config, {
        filter: '<?= $id ?>' != '' ? {field: "id_import", operator: "eq", value: '<?= $id ?>'} : null,
        crudApi: `${ENV.restApi}`,
        vApi: `${ENV.vApi}`,
        templateApi: `${ENV.templateApi}`,
        collection: "telesalelist_assigning",
        observable: {
            scrollTo: function(e) {
                var id = $(e.currentTarget).data('id');
                $("#main-form").animate({scrollTop: $("#"+id).position().top + $("#main-form").scrollTop()});
            },
            searchField: function(e) {
                var search = e.currentTarget.value;
                var formGroup = $("#main-form .form-group");
                for (var i = 0; i < formGroup.length; i++) {
                    var regex = new RegExp(search, "i");
                    var test = regex.test($(formGroup[i]).data("field")) ? true : false;
                    if(test) 
                        $(formGroup[i]).show();
                    else $(formGroup[i]).hide();
                }
            }
        },
        filterable: KENDO.filterable,
        scrollable: true,
        model: {
            id: "id",
            fields: {
                request_time: {type: "date"},
                accept_time: {type: "date"},
                decline_time: {type: "date"},
            }
        },
        parse: function (response) {
            if(response.data) {
                response.data.map(function(doc) {
                    doc.request_time = doc.request_time ? new Date(doc.request_time * 1000): null;
                    doc.accept_time = doc.accept_time ? new Date(doc.accept_time * 1000) : null;
                    doc.decline_time = doc.decline_time ? new Date(doc.decline_time * 1000) : null;
                    return doc;
                })
            }
            return response;
        },
        columns: []
    }); 
</script>

<script type="text/javascript">
    kendo.culture("vi-VN");
    function girdBoolean(data) {
        return '<input type="checkbox"'+ ( data ? 'checked="checked"' : "" )+ 'class="chkbx" disabled />';
    }
    var router = new kendo.Router({routeMissing: function(e) { router.navigate("/") }});
    var telesaleFields = new kendo.data.DataSource({
        serverPaging: true,
        serverFiltering: true,
        serverSorting: true,
        transport: {
            read: `${ENV.vApi}model/read`,
            parameterMap: parameterMap
        },
        schema: {
            data: "data",
           
        },
        filter: [{
            field: "collection",
            operator: "eq",
            value: (ENV.type ? ENV.type + "_" : "") + "Telesalelist_assigning"
        }, {
            field: 'sub_type',
            operator: 'isnotempty'
        }],
        page: 1,
        pageSize: 30,
        sort: {field: "index", dir: "asc"}
    })
    telesaleFields.read().then(function(){
        var columns = telesaleFields.data().toJSON();
        console.log(columns);
        columns.map(col => {
            col.width = 130;
            switch (col.type) {
                case "name":
                    col.template = (dataItem) => gridName(dataItem[col.field]);
                    break;
                case "phone": case "arrayPhone":
                    col.template = (dataItem) => gridPhone(dataItem[col.field],dataItem['id'],'customer');
                    break;
                case "array":
                    col.template = (dataItem) => gridArray(dataItem[col.field]);
                    break;
                case "timestamp":
                    col.template = (dataItem) => gridDate(dataItem[col.field],"dd/MM/yyyy HH:mm:ss");
                    break;
                case "boolean":
                    col.template = (dataItem) => girdBoolean(dataItem[col.field]);
                    break;
                default:
                    break;
            }
        });

        columns.push({template: '<a role="button" class="btn btn-sm btn-circle btn-action btn-primary" data-uid="#: uid #"><i class="fa fa-ellipsis-v"></i></a>', width: 50})
        
        Config.columns = columns;

        var s = document.createElement("script");
        s.type = "text/javascript";
        s.src = "<?= STEL_PATH ?>js/table.js";
        $("header").append(s);
        Table.init();
        Table.grid.bind("change", grid_change);
    })

    $( document ).ready(function() {
        $('.change-mvvm').hide();
    });
    var select = [];
    
    function grid_change(arg) {
        var selectUid = this.selectedKeyNames();
        if (selectUid.length > 0) {
            //hiên Re-Assign
            select = [];
            $('.change-mvvm').show();
            for(var i in selectUid){
                var item = Table.dataSource.getByUid(selectUid[i]);
                select.push(item.id);
            }
        }else{
            //an Re-Assign
            $('.change-mvvm').hide();
        }
        // console.log(this.selectedKeyNames());
    }
    
    var $userListElement = $(".change-mvvm");
    var userListObservable = kendo.observable({
        userListData: new kendo.data.DataSource({
            transport: {
                read: ENV.vApi + "widget/user_list",
                parameterMap: parameterMap
            },
            schema: {
                data: "data",
                total: "total",
               
            }
        })
    });
    kendo.bind($userListElement, userListObservable);

    function saveChangeAssign() {
        var assign = $('#changeAssign').val();
        if (assign == '') {
            swal({
                title: "Please choose an assign",
                icon: "warning",
                dangerMode: true,
            })
        }else{
            $.ajax({
                url: Config.vApi + 'assign/changeAssign',
                type: 'POST',
                data: {assign: assign, select: select},
                beforeSend: function(){
                  if(HELPER.loaderHtml) $("#form-loader").html(HELPER.loaderHtml).show();
               },
               complete: function(){
                  if(HELPER.loaderHtml) $("#form-loader").html("").hide();
               },
               success: function(response) {
                  if(response.status) {
                    notification.show("@Success@", "success");
                    Table.dataSource.read();
                  } else notification.show("@No success@", "error");
               },
               error: errorDataSource
            })
        }
            
    }

    function removeRow() {
        $('#grid').data("kendoGrid").select().each(function () {
            Table.grid.removeRow($(this).closest("tr"));
            Table.dataSource.sync();
        });
       
    }

    function detailData(ele) {
        var uid = $(ele).data('uid');
        var dataItem = Table.dataSource.getByUid(uid);
        router.navigate(`/detail_customer/${dataItem.id}`);
    }

	$(document).on("click", ".grid-name", function() {
		detailData($(this).closest("tr"));
    })
    
    function saveAsExcel() {
        $.ajax({
            url: ENV.vApi + 'telesalelist/exportExcel',
            type: 'GET',
            success: function(response) {
                window.open(response.data, '_blank'); // <- This is what makes it open in a new window.
            }
        })
    }

    function approve(ele) {
        var uid = $(ele).data("uid"),
            dataItem = Table.dataSource.getByUid(uid);
        console.log(dataItem);
    }

    function reject(ele) {
        var uid = $(ele).data("uid"),
            dataItem = Table.dataSource.getByUid(uid);
        swal({
            title: "@Are you sure@?",
            text: `Reject this customer: ${dataItem.id_no} ${dataItem.name}.`,
            icon: "warning",
            buttons: true,
            dangerMode: true,
        })
        .then((code) => {
            if (code) {
                $.ajax({
                    url: ENV.vApi + "telesalelist_assigning/reject",
                    type: "PUT",
                    contentType: "application/json; charset=utf-8",
                    data: kendo.stringify(dataItem),
                    success: (response) => {
                        if(response.status) {
                            notification.show("@Success@", "success");
                            Table.dataSource.read();
                        } else notification.show("@No success@", "error");
                    },
                    error: errorDataSource
                });
            }
        });
    }

    function approve(ele) {
        var uid = $(ele).data("uid"),
            dataItem = Table.dataSource.getByUid(uid);
        swal({
            title: "@Are you sure@?",
            text: `Approve this customer: ${dataItem.id_no} ${dataItem.name}.`,
            icon: "warning",
            buttons: true,
            dangerMode: true,
        })
        .then((code) => {
            if (code) {
                $.ajax({
                    url: ENV.vApi + "telesalelist_assigning/approve",
                    type: "PUT",
                    contentType: "application/json; charset=utf-8",
                    data: kendo.stringify(dataItem),
                    success: (response) => {
                        console.log(response);
                        if(response.status) {
                            notification.show("@Success@", "success");
                            Table.dataSource.read();
                        } else notification.show("@No success@", "error");
                    },
                    error: errorDataSource
                });
            }
        });
    }
</script>
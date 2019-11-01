<div class="col-sm-3" style="margin: 10px 0" id="page-widget"></div>
<div class="col-sm-9 change-mvvm" style=" margin: 10px 0;">
    <div class="col-sm-2"><label>Re-Assign</label></div>
    <div class="col-sm-4" class="form-group">
        <input data-role="dropdownlist"
               data-text-field="agentname"
               data-value-field="extension"
                    data-value-primitive="true"
                    data-bind="source: userListData" style="width: 100%" id="changeAssign">
    </div>
    <div class="col-sm-1">
        <button class="btn btn-sm btn-primary btn-save" onclick="saveChangeAssign()">@Save@</button>
    </div>
</div>
<div class="col-sm-12" style="overflow-y: auto; padding: 0">
	<div id="grid"></div>
</div>
<!-- <div id="action-menu">
    <ul>
    	<a href="javascript:void(0)" data-type="detail" onclick="detailData(this)"><li><i class="fa fa-exclamation-circle text-info"></i><span>Detail</span></li></a>
    	<a href="javascript:void(0)" data-type="import" onclick="importData(this)"><li><i class="fa fa-download text-success"></i><span>Import</span></li></a>
    	<li class="devide"></li>
        <a href="javascript:void(0)" data-type="update" onclick="openForm({title: 'Edit assign', width: 300}); editForm(this)"><li><i class="fa fa-pencil-square-o text-warning"></i><span>Edit Assign</span></li></a>
        
    </ul>
</div> -->
<style type="text/css">
    .change-mvvm {
        display: none;
    }
</style>
<script>
    var Config = Object.assign(Config, {
        crudApi: `${ENV.restApi}`,
        vApi: `${ENV.vApi}`,
        templateApi: `${ENV.templateApi}`,
        collection: "Telesalelist_solve",
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
                createdAt: {type: "date"},
                exporting_date: {type: "date"},
                date_of_birth: {type: "date"},
                date_send_data: {type: "date"},
                date_receive_data: {type: "date"},
                updatedAt: {type: "date"},
            }
        },
        parse: function (response) {
            response.data.map(function(doc) {
                doc.createdAt = new Date(doc.createdAt * 1000);
                doc.exporting_date = doc.exporting_date ? new Date(doc.exporting_date * 1000) : null;
                doc.date_of_birth = doc.date_of_birth ? new Date(doc.date_of_birth * 1000) : null;
                doc.date_send_data = doc.date_send_data ? new Date(doc.date_send_data * 1000) : null;
                doc.date_receive_data = doc.date_receive_data ? new Date(doc.date_receive_data * 1000) : null;
                doc.updatedAt = doc.updatedAt ? new Date(doc.updatedAt * 1000) : null;
                return doc;
            })
            return response;
        },
        columns: []

    }); 
</script>
<script src="<?= STEL_PATH.'js/table.js' ?>"></script>
<script type="text/javascript">
    function gridPhone(data,id,type) {
        var html = "<span></span>";
        if(data) {
            if(typeof data == "string") {
                html = `<a href="javascript:void(0)" class="label label-info" onclick="makeCallWithDialog('${data}','${id}','${type}')" title="Call now" data-role="tooltip" data-position="top">${data}</a>`;
            } else {
                if(data.length) {
                    template = $.map($.makeArray(data), function(value, index) {
                        return `<a href="javascript:void(0)" class="label label-default" data-index="${index}" onclick="makeCallWithDialog('${value}','${id}','${type}')" title="Call now" data-role="tooltip" data-position="top">${value}</a>`;
                    });;
                    html = template.join(' ');
                }
            }
        }
        return html;
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
        filter: {
            field: "collection",
            operator: "eq",
            value: (ENV.type ? ENV.type + "_" : "") + "Telesalelist"
        },
        page: 1,
        pageSize: 30,
        sort: {field: "index", dir: "asc"}
    })
    telesaleFields.read().then(function(){
        var columns = telesaleFields.data().toJSON();
        columns.map(col => {
            col.width = 130;
            if (col.field != 'assign') {
                col.filterable = false;
            }
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
                    col.template = (dataItem) => gridDate(dataItem[col.field]);
                    break;
                default:
                    break;
            }
        });
        columns.unshift({
            selectable: true,
            width: 32,
            locked: true
        });
        // columns.push({
        //     // Use uid to fix bug data-uid of row undefined
        //     title: `<a class='btn btn-sm btn-circle btn-action btn-primary' onclick='return deleteDataItemChecked();'><i class='fa fa-times-circle'></i></a>`,
        //     template: '<a role="button" class="btn btn-sm btn-circle btn-action btn-primary" data-uid="#: uid #"><i class="fa fa-ellipsis-v"></i></a>',
        //     width: 32
        // });

        Table.columns = columns;
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
                url: Config.vApi + 'Assign/changeAssign',
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

    function detailData(ele) {
        var uid = $(ele).data('uid');
        var dataItem = Table.dataSource.getByUid(uid);
        router.navigate(`/detail_customer/${dataItem.id}`);
    }

	$(document).on("click", ".grid-name", function() {
		detailData($(this).closest("tr"));
	})
	
</script>
<?php $id = $this->input->get("id") ?>
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
    var Config = {
        filter: '<?= $id ?>' != '' ? {field: "id_import", operator: "eq", value: '<?= $id ?>'} : null,
        crudApi: `${ENV.restApi}`,
        templateApi: `${ENV.templateApi}`,
        collection: "Telesalelist",
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
        columns: [
            {
                selectable: true, 
                width: "50px" 
            },
            {
                field: "source",
                title: "@Source@",
                width: 150,
                filterable: false
                // locked: true
            },{
                field: "exporting_date",
                title: "@Exporting Date@",
                template: function(dataItem) {
                    return (kendo.toString(dataItem.date_of_birth, "dd/MM/yyyy") ||  "").toString();
                },
                width: 150,
                filterable: false
                // locked: true

            },{
                field: "contract_no",
                title: "@Contract No.(Latest Loan)@",
                width: 150,
                filterable: false
                // locked: true
            },{
                field: "cif",
                title: "@CIF@",
                width: 150,
                filterable: false
                // locked: true
            },{
                field: "customer_name",
                title: "@Customer Name@",
                width: 150,
                filterable: false
                // locked: true
            },{
                field: "date_of_birth",
                title: "@Date of birth@",
                template: function(dataItem) {
                    return (kendo.toString(dataItem.date_of_birth, "dd/MM/yyyy") ||  "").toString();
                },
                width: 150,
                filterable: false
            },{
                field: "id_no",
                title: "@ID No@",
                width: 150,
                filterable: false
            },{
                field: "mobile_phone_no",
                title: "@Mobile Phone No.@",
                width: 150,
                filterable: false
            },{
                field: "product",
                title: "@Product(MB/CE/PL)@",
                width: 150,
                filterable: false
            },{
                field: "interest_rate",
                title: "@Interest Rate(Latest Loan)@",
                width: 150,
                filterable: false
            },{
                field: "first_due_date",
                title: "@First due date(Latest Loan)@",
                width: 150,
                filterable: false
            },{
                field: "term",
                title: "@Term(Latest Loan)@",
                width: 150,
                filterable: false
            },{
                field: "balance",
                title: "@Balance(Latest Loan)@",
                width: 150,
                filterable: false
            },{
                field: "debt_group",
                title: "@Debt group@",
                width: 150,
                filterable: false
            },{
                field: "no_of_late_1",
                title: "@No. of late(10-29 days)@",
                width: 150,
                filterable: false
            },{
                field: "no_of_late_2",
                title: "@No. of late( > 30 days)@",
                width: 150,
                filterable: false
            },{
                field: "pl_interest_rate",
                title: "@PL-Interest Rate@",
                width: 150,
                filterable: false
            },{
                field: "note",
                title: "@Note@",
                width: 150,
                filterable: false
            },{
                field: "assign",
                title: "@Assign@",
                width: 150,
            },{
                field: "date_send_data",
                title: "@Date send Data@",
                template: function(dataItem) {
                    return (kendo.toString(dataItem.date_of_birth, "dd/MM/yyyy") ||  "").toString();
                },
                width: 150,
                filterable: false
            },{
                field: "date_receive_data",
                title: "@Date receive Data@",
                template: function(dataItem) {
                    return (kendo.toString(dataItem.date_receive_data, "dd/MM/yyyy") ||  "").toString();
                },
                width: 150,
                filterable: false
            },{
                field: "code",
                title: "@Code@",
                width: 150,
                filterable: false
            },{
                field: "area_pl",
                title: "@Area PL@",
                width: 150,
                filterable: false
            },{
                field: "createdAt",
                title: "@Created At@",
                template: function(dataItem) {
                    return (kendo.toString(dataItem.createdAt, "dd/MM/yy H:mm:ss") ||  "").toString();
                },
                width: 150,
                filterable: false
            },{
                field: "updatedAt",
                title: "@Last Modified@",
                template: function(dataItem) {
                    return (kendo.toString(dataItem.updatedAt, "dd/MM/yyyy H:mm:ss") ||  "").toString();
                },
                width: 150,
                filterable: false
            },{
                field: "assigned_by",
                title: "@Assigned by@",
                width: 150,
                filterable: false
            }
            // ,{
            //     // Use uid to fix bug data-uid of row undefined
            //     template: '<a role="button" class="btn btn-sm btn-circle btn-action" data-uid="#: uid #"><i class="fa fa-ellipsis-v"></i></a>',
            //     locked:true,
            //     width: 40
            // }
        ]
    }; 
</script>
<script src="<?= STEL_PATH.'js/table.js' ?>"></script>
<script type="text/javascript">
    $( document ).ready(function() {
        $('.change-mvvm').hide();
        Table.init();
    });

    var select = [];
    Table.grid.bind("change", grid_change);
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
                url: Config.crudApi + Config.collection + '/changeAssign',
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
                response.data = response.data.filter(function(doc) {
                    if(doc.sub_type) 
                        doc.subType = JSON.parse(doc.sub_type);
                    else doc.subType = {};
                    return doc.subType.gridShow;
                })
                return response;
            }
        },
        filter: {
            field: "collection",
            operator: "eq",
            value: (ENV.type ? ENV.type + "_" : "") + Config.collection
        },
        sort: {field: "index", dir: "asc"}
    })
    customerFields.read().then(function(){
        var columns = customerFields.data().toJSON();
        columns.map(col => {
            col.width = 150;
            switch (col.type) {
                case "name":
                    col.template = (dataItem) => gridName(dataItem[col.field]);
                    break;
                case "phone": case "arrayPhone":
                    col.template = (dataItem) => gridPhone(dataItem[col.field]);
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
        // columns.unshift({
        //     selectable: true,
        //     width: 32,
        //     locked: true
        // });
        // columns.push({
        //     // Use uid to fix bug data-uid of row undefined
        //     title: `<a class='btn btn-sm btn-circle btn-action btn-primary' onclick='return deleteDataItemChecked();'><i class='fa fa-times-circle'></i></a>`,
        //     template: '<a role="button" class="btn btn-sm btn-circle btn-action btn-primary" data-uid="#: uid #"><i class="fa fa-ellipsis-v"></i></a>',
        //     width: 32
        // });
        Table.columns = columns;
        // Table.init();
    })

	$(document).on("click", ".grid-name", function() {
		detailData($(this).closest("tr"));
	})
	
</script>
<?php $id = $this->input->get("id") ?>
<div class="col-sm-3" style="margin: 10px 0" id="page-widget"></div>
<!-- <div class="col-sm-9 filter-mvvm" style="display: none; margin: 10px 0"></div> -->
<div class="col-sm-12" style="overflow-y: auto; padding: 0">
	<div id="grid"></div>
</div>
<!-- <div id="action-menu">
    <ul>
    	<a href="javascript:void(0)" data-type="detail" onclick="detailData(this)"><li><i class="fa fa-exclamation-circle text-info"></i><span>Detail</span></li></a>
    	<a href="javascript:void(0)" data-type="import" onclick="importData(this)"><li><i class="fa fa-download text-success"></i><span>Import</span></li></a>
    	<li class="devide"></li>
        <a href="javascript:void(0)" data-type="update" onclick="openForm({title: 'Edit diallist', width: 1000}); editForm(this)"><li><i class="fa fa-pencil-square-o text-warning"></i><span>Edit</span></li></a>
        <a href="javascript:void(0)" data-type="delete" onclick="deleteDataItem(this)"><li><i class="fa fa-times-circle text-danger"></i><span>Delete</span></li></a>
    </ul>
</div> -->
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
        scrollable: false,
        model: {
            id: "id",
            fields: {
                createdAt: {type: "date"},
                exporting_date: {type: "date"},
                date_of_birth: {type: "date"},
                date_send_data: {type: "date"},
                date_receive_data: {type: "date"},
                last_modified: {type: "date"}
            }
        },
        parse: function (response) {
            response.data.map(function(doc) {
                doc.createdAt = new Date(doc.createdAt * 1000);
                doc.exporting_date = doc.exporting_date ? new Date(doc.exporting_date * 1000) : null;
                doc.date_of_birth = doc.date_of_birth ? new Date(doc.date_of_birth * 1000) : null;
                doc.date_send_data = doc.date_send_data ? new Date(doc.date_send_data * 1000) : null;
                doc.date_receive_data = doc.date_receive_data ? new Date(doc.date_receive_data * 1000) : null;
                doc.last_modified = doc.last_modified ? new Date(doc.last_modified * 1000) : null;
                return doc;
            })
            return response;
        },
        columns: [
            {
                field: "source",
                title: "@Source@",
            },{
                field: "exporting_date",
                title: "@Exporting Date@",
                width: 30
            },{
                field: "contract_no",
                title: "@Contract No.@",
                width: 30
            },{
                field: "cif",
                title: "@CIF@",
            },{
                field: "customer_name",
                title: "@Customer Name@",
            },{
                field: "date_of_birth",
                title: "@Date of birth@",
                // template: function(dataItem) {
                //     return (kendo.toString(dataItem.date_of_birth, "dd/MM/yyyy") ||  "").toString();
                // }
            },{
                field: "id_no",
                title: "@ID No@"
            },{
                field: "mobile_phone_no",
                title: "@Mobile Phone No.@",
            },{
                field: "product",
                title: "@Product(MB/CE/PL)@",
            },{
                field: "interest_rate",
                title: "@Interest Rate(Latest Loan)@",
            },{
                field: "first_due_date",
                title: "@First due date(Latest Loan)@",
            },{
                field: "term",
                title: "@Term(Latest Loan)@",
            },{
                field: "balance",
                title: "@Balance(Latest Loan)@",
            },{
                field: "debt_group",
                title: "@Debt group@",
            },{
                field: "no_of_late_1",
                title: "@No. of late(10-29 days)@",
            },{
                field: "no_of_late_2",
                title: "@No. of late( > 30 days)@",
            },{
                field: "pl_interest_rate",
                title: "@PL-Interest Rate@",
            },{
                field: "note",
                title: "@Note@",
            },{
                field: "assign",
                title: "@assign@",
            },{
                field: "date_send_data",
                title: "@date send data@",
            },{
                field: "date_receive_data",
                title: "@date receive data@",
                // template: function(dataItem) {
                //     return (kendo.toString(dataItem.date_receive_data, "dd/MM/yyyy") ||  "").toString();
                // }
            },{
                field: "code",
                title: "@code@",
            },{
                field: "area_pl",
                title: "@Area PL@",
            },{
                field: "createdAt",
                title: "@Created At@",
                template: function(dataItem) {
                    return (kendo.toString(dataItem.createdAt, "dd/MM/yy H:mm:ss") ||  "").toString();
                }
            },{
                field: "last_modified",
                title: "@Last Modified@",
                template: function(dataItem) {
                    return (kendo.toString(dataItem.last_modified, "dd/MM/yyyy") ||  "").toString();
                }
            },{
                field: "assigned_by",
                title: "@Assigned by@",
            },
            // },{
            //     // Use uid to fix bug data-uid of row undefined
            //     template: '<a role="button" class="btn btn-sm btn-circle btn-action" data-uid="#: uid #"><i class="fa fa-ellipsis-v"></i></a>',
            //     width: 20
            // }
        ]
    }; 
</script>
<script src="<?= STEL_PATH.'js/table.js' ?>"></script>
<script type="text/javascript">
    $( document ).ready(function() {
        Table.init();
    });
	
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
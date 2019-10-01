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
        scrollable: true,
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
                width: 150,
                // locked: true
            },{
                field: "exporting_date",
                title: "@Exporting Date@",
                // width: 100,
                template: function(dataItem) {
                    return (kendo.toString(dataItem.date_of_birth, "dd/MM/yyyy") ||  "").toString();
                },
                width: 150,
                // locked: true

            },{
                field: "contract_no",
                title: "@Contract No.(Latest Loan)@",
                width: 150,
                // locked: true
            },{
                field: "cif",
                title: "@CIF@",
                width: 150,
                // locked: true
            },{
                field: "customer_name",
                title: "@Customer Name@",
                width: 150,
                // locked: true
            },{
                field: "date_of_birth",
                title: "@Date of birth@",
                template: function(dataItem) {
                    return (kendo.toString(dataItem.date_of_birth, "dd/MM/yyyy") ||  "").toString();
                },
                width: 150,
            },{
                field: "id_no",
                title: "@ID No@",
                width: 150,
            },{
                field: "mobile_phone_no",
                title: "@Mobile Phone No.@",
                template: dataItem => gridCallResult(dataItem.mobile_phone_no),
                width: 150,
            },{
                field: "product",
                title: "@Product(MB/CE/PL)@",
                width: 150,
            },{
                field: "interest_rate",
                title: "@Interest Rate(Latest Loan)@",
                width: 150,
            },{
                field: "first_due_date",
                title: "@First due date(Latest Loan)@",
                width: 150,
            },{
                field: "term",
                title: "@Term(Latest Loan)@",
                width: 150,
            },{
                field: "balance",
                title: "@Balance(Latest Loan)@",
                width: 150,
            },{
                field: "debt_group",
                title: "@Debt group@",
                width: 150,
            },{
                field: "no_of_late_1",
                title: "@No. of late(10-29 days)@",
                width: 150,
            },{
                field: "no_of_late_2",
                title: "@No. of late( > 30 days)@",
                width: 150,
            },{
                field: "pl_interest_rate",
                title: "@PL-Interest Rate@",
                width: 150,
            },{
                field: "note",
                title: "@Note@",
                width: 150,
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
            },{
                field: "date_receive_data",
                title: "@Date receive Data@",
                template: function(dataItem) {
                    return (kendo.toString(dataItem.date_receive_data, "dd/MM/yyyy") ||  "").toString();
                },
                width: 150,
            },{
                field: "code",
                title: "@Code@",
                width: 150,
            },{
                field: "area_pl",
                title: "@Area PL@",
                width: 150,
            },{
                field: "createdAt",
                title: "@Created At@",
                template: function(dataItem) {
                    return (kendo.toString(dataItem.createdAt, "dd/MM/yy H:mm:ss") ||  "").toString();
                },
                width: 150,
            },{
                field: "last_modified",
                title: "@Last Modified@",
                template: function(dataItem) {
                    return (kendo.toString(dataItem.last_modified, "dd/MM/yyyy") ||  "").toString();
                },
                width: 150,
            },{
                field: "assigned_by",
                title: "@Assigned by@",
                width: 150,
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
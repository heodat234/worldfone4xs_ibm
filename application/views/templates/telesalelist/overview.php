<div class="col-sm-3" style="margin: 10px 0" id="page-widget"></div>
<!-- <div class="col-sm-9 filter-mvvm" style="display: none; margin: 10px 0"></div> -->
<div class="col-sm-12" style="overflow-y: auto; padding: 0">
	<div id="grid"></div>
</div>
<div id="action-menu">
    <ul>
    	<a href="javascript:void(0)" data-type="detail" onclick="detailData(this)"><li><i class="fa fa-exclamation-circle text-info"></i><span>Detail</span></li></a>
    	<a href="javascript:void(0)" data-type="import" onclick="importData(this)"><li><i class="fa fa-download text-success"></i><span>Import</span></li></a>
    	<li class="devide"></li>
        <a href="javascript:void(0)" data-type="update" onclick="openForm({title: 'Edit diallist', width: 1000}); editForm(this)"><li><i class="fa fa-pencil-square-o text-warning"></i><span>Edit</span></li></a>
        <a href="javascript:void(0)" data-type="delete" onclick="deleteDataItem(this)"><li><i class="fa fa-times-circle text-danger"></i><span>Delete</span></li></a>
    </ul>
</div>
<script>
var Config = {
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
    model: {
        id: "id",
        fields: {
            createdAt: {type: "date"},
            Exporting_Date: {type: "date"},
            Date_of_birth: {type: "date"},
            date_send_data: {type: "date"},
            date_receive_data: {type: "date"},
            Last_Modified: {type: "date"}
        }
    },
    parse: function (response) {
        response.data.map(function(doc) {
            doc.createdAt = new Date(doc.createdAt * 1000);
            doc.Exporting_Date = doc.Exporting_Date ? new Date(doc.Exporting_Date * 1000) : null;
            doc.Date_of_birth = doc.Date_of_birth ? new Date(doc.Date_of_birth * 1000) : null;
            doc.date_send_data = doc.date_send_data ? new Date(doc.date_send_data * 1000) : null;
            doc.date_receive_data = doc.date_receive_data ? new Date(doc.date_receive_data * 1000) : null;
            doc.Last_Modified = doc.Last_Modified ? new Date(doc.Last_Modified * 1000) : null;
            return doc;
        })
        return response;
    },
    columns: [{
            field: "Source",
            title: "@Source@",
            locked: true,
        },{
            field: "Exporting_Date",
            title: "@Exporting Date@",
            locked: true,
            template: function(dataItem) {
                return (kendo.toString(dataItem.Exporting_Date, "dd/MM/yyyy") ||  "").toString();
            }
        },{
            field: "Contract_No",
            title: "@Contract No.@",
            locked: true,
        },{
            field: "CIF",
            title: "@CIF@",
            locked: true,
        },{
            field: "Customer_name",
            title: "@Customer name@",
            locked: true,
        },{
            field: "Date_of_birth",
            title: "@Date of birth@",
            template: function(dataItem) {
                return (kendo.toString(dataItem.Date_of_birth, "dd/MM/yyyy") ||  "").toString();
            }
        },{
            field: "ID_No",
            title: "@ID No@"
        },{
            field: "Mobile_Phone_No",
            title: "@Mobile Phone No.@",
        },{
            field: "Product",
            title: "@Product(MB/CE/PL)@",
        },{
            field: "Interest_Rate",
            title: "@Interest Rate(Latest Loan)@",
        },{
            field: "First_due_date",
            title: "@First due date(Latest Loan)@",
        },{
            field: "Term",
            title: "@Term(Latest Loan)@",
        },{
            field: "Balance",
            title: "@Balance(Latest Loan)@",
        },{
            field: "Debt_group",
            title: "@Debt group@",
        },{
            field: "No_of_late_1",
            title: "@No. of late(10-29 days)@",
        },{
            field: "No_of_late_2",
            title: "@No. of late( > 30 days)@",
        },{
            field: "PL-Interest_Rate",
            title: "@PL-Interest Rate@",
        },{
            field: "Note",
            title: "@Note@",
        },{
            field: "ownership",
            title: "@ownership@",
        },{
            field: "date_send_data",
            title: "@date send data@",
            template: function(dataItem) {
                return (kendo.toString(dataItem.date_send_data, "dd/MM/yyyy") ||  "").toString();
            }
        },{
            field: "date_receive_data",
            title: "@date receive data@",
            template: function(dataItem) {
                return (kendo.toString(dataItem.date_receive_data, "dd/MM/yyyy") ||  "").toString();
            }
        },{
            field: "code",
            title: "@code@",
        },{
            field: "Area_PL",
            title: "@Area PL@",
        },{
            field: "createdAt",
            title: "@Created At@",
            template: function(dataItem) {
                return (kendo.toString(dataItem.createdAt, "dd/MM/yy H:mm:ss") ||  "").toString();
            }
        },{
            field: "Last_Modified",
            title: "@Last Modified@",
            template: function(dataItem) {
                return (kendo.toString(dataItem.Last_Modified, "dd/MM/yyyy") ||  "").toString();
            }
        },{
            field: "Assigned_by",
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
	// async function editForm(ele) {
	// 	var dataItem = Table.dataSource.getByUid($(ele).data("uid")),
	//         dataItemFull = await $.ajax({
	//             url: `${Config.crudApi+Config.collection}/${dataItem.id}`,
	//             error: errorDataSource
	//         }),
	// 	    formHtml = await $.ajax({
	//     	    url: Config.templateApi + Config.collection + "/form",
	//     	    error: errorDataSource
	//     	});
	// 	var model = Object.assign({
	// 		item: dataItemFull,
	// 		save: function() {
	//             $.ajax({
	//                 url: `${Config.crudApi+Config.collection}/${dataItem.id}`,
	//                 data: this.item.toJSON(),
	//                 error: errorDataSource,
	//                 type: "PUT",
	//                 success: function() {
	//                     Table.dataSource.read()
	//                 }
	//             })
	// 		}
	// 	}, Config.observable);
	// 	kendo.destroy($("#right-form"));
	// 	$("#right-form").empty();
	// 	var kendoView = new kendo.View(formHtml, { wrap: false, model: model, evalTemplate: false });
	// 	kendoView.render($("#right-form"));
	// }

	// async function addForm() {
	// 	var formHtml = await $.ajax({
	// 	    url: Config.templateApi + Config.collection + "/form",
	// 	    error: errorDataSource
	// 	});
	// 	var model = Object.assign({
	// 		item: {},
	// 		save: function() {
	// 			Table.dataSource.add(this.item);
	// 			Table.dataSource.sync().then(() => {Table.dataSource.read()});
	// 		}
	// 	}, Config.observable);
	// 	kendo.destroy($("#right-form"));
	// 	$("#right-form").empty();
	// 	var kendoView = new kendo.View(formHtml, { wrap: false, model: model, evalTemplate: false });
	// 	kendoView.render($("#right-form"));
	// }

	// function deleteDataItem(ele) {
	// 	swal({
	// 	    title: "Are you sure?",
	// 	    text: "Once deleted, you will not be able to recover this document!",
	// 	    icon: "warning",
	// 	    buttons: true,
	// 	    dangerMode: true,
	//     })
	//     .then((willDelete) => {
	// 		if (willDelete) {
	// 			var uid = $(ele).data('uid');
	// 			var dataItem = Table.dataSource.getByUid(uid);
	// 		    Table.dataSource.remove(dataItem);
	// 		    Table.dataSource.sync();
	// 		}
	//     });
	// }
	// function importData(ele) {
	// 	var uid = $(ele).data('uid');
	// 	var dataItem = Table.dataSource.getByUid(uid);
	// 	router.navigate(`/import/${dataItem.id}`);
	// }

	// function detailData(ele) {
	// 	var uid = $(ele).data('uid');
	// 	var dataItem = Table.dataSource.getByUid(uid);
	// 	router.navigate(`/detail/${dataItem.id}`);
	// }

	// $(document).on("click", ".grid-name", function() {
	// 	detailData($(this).closest("tr"));
	// })
	
</script>
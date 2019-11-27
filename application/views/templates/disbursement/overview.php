<div class="row" style="margin: 10px 0">
	<div class="col-sm-2" id="page-widget"></div>
	<div class="col-sm-9 filter-mvvm" style="display: none"></div>
    <div class="col-sm-1" style=" margin: 10px 0; float: right;">
        <a role="button" class="btn btn-sm" onclick="Table.grid.saveAsExcel()"><i class="fa fa-file-excel-o"></i> <b>@Export@</b></a>
    </div>
</div>
<div class="row">
	<div class="col-sm-12" style="height: 80vh;">
	    <!-- Table Styles Content -->
	    <div id="grid"></div>
	    <!-- END Table Styles Content -->
	</div>
</div>
<div id="action-menu">
    <ul>
        <a href="javascript:void(0)" data-type="update" onclick="openForm({title: '@Edit@', width: 500}); editForm(this)"><li><i class="fa fa-pencil-square-o text-warning"></i><span>@Edit@</span></li></a>
    	<!-- <li class="devide"></li>
        <a href="javascript:void(0)" data-type="delete" onclick="deleteDataItem(this)"><li><i class="fa fa-times-circle text-danger"></i><span>@Delete@</span></li></a> -->
    </ul>
</div>

<script type="text/x-kendo-template" id="status-group-template">
    <li data-bind="css: {active: active}">
        <a href="javascript:void(0)" data-bind="click: filterStatus, attr: {data-value: idFields}">
            <span class="badge pull-right" data-bind="text: count">250</span>
            <i class="#: data.iconClass #"></i> <strong data-bind="text: idFields">Closed</strong>
        </a>
    </li>
</script>

<script>
var Config = Object.assign(Config, {
    model: {
        id: "id",
        fields: {
            released_date: {type: "date"},
            disbursed_date: {type: "date"},
            issued_date: {type: "date"},
            created_at: {type: "date"},
            updated_at: {type: "date"},
        }
    },
    parse(response) {
        response.data.map(function(doc) {
            doc.released_date = doc.released_date ? new Date(doc.released_date * 1000) : undefined;
            doc.disbursed_date = doc.disbursed_date ? new Date(doc.disbursed_date * 1000) : undefined;
            doc.issued_date = doc.issued_date ? new Date(doc.issued_date * 1000) : undefined;
            doc.created_at = doc.created_at ? new Date(doc.created_at * 1000) : undefined;
            doc.updated_at = doc.updated_at ? new Date(doc.updated_at * 1000) : undefined;
            return doc;
        });
        return response;
    },
    scrollable: true,
    sort: [{field: 'created_at', dir: 'desc'}],
    columns: [{
        selectable: true,
        width: 32,
    }, {
        // Use uid to fix bug data-uid of row undefined
        title: `<a data-type='action/delete_all' class='btn btn-sm btn-circle btn-action btn-primary' onclick='return deleteDataItemChecked();'><i class='fa fa-times-circle'></i></a>`,
        template: `<a data-type='action/delete_all' role="button" class="btn btn-sm btn-circle btn-action btn-primary" data-uid="#: uid #"><i class="fa fa-ellipsis-v"></i></a>`,
        width: 48
    }, {
        field: "dealer_code",
        title: "@Dealer code@",
        width: '150px',
        headerAttributes: { style: "white-space: normal"},
        filterable: true,
    },{
        field: "dealer_name",
        title: "@Dealer name@",
        width: '200px',
        headerAttributes: { style: "white-space: normal"},
        filterable: true,
    },{
        field: "location",
        title: "@Location@",
        width: '200px',
        headerAttributes: { style: "white-space: normal"},
        filterable: true,
    },{
        field: "disbursement",
        title: "@Disbursement@",
        width: '200px',
        headerAttributes: { style: "white-space: normal"},
        filterable: true,
    },{
        field: "cif",
        title: "CIF",
        width: '200px',
        headerAttributes: { style: "white-space: normal"},
        filterable: true,
    },{
        field: "acc_no",
        title: "@Account number@",
        width: '200px',
        headerAttributes: { style: "white-space: normal"},
        filterable: true,
    },{
        field: "cus_name",
        title: "@Customer name@",
        width: '200px',
        headerAttributes: { style: "white-space: normal"},
        filterable: true,
    },{
        field: "released_date",
        title: "@Released date@",
        width: '150px',
        headerAttributes: { style: "white-space: normal"},
        format: "{0: dd/MM/yy HH:mm}",
        filterable: true,
    },{
        field: "disbursed_date",
        title: "@Disbursed date@",
        width: '150px',
        headerAttributes: { style: "white-space: normal"},
        format: "{0: dd/MM/yy HH:mm}",
        filterable: true,
    },{
        field: "loan_amount",
        title: "@Loan amount@",
        width: '150px',
        headerAttributes: { style: "white-space: normal"},
        filterable: true,
    },{
        field: "cmnd",
        title: "@ID@",
        width: '150px',
        headerAttributes: { style: "white-space: normal"},
        filterable: true,
    },{
        field: "issued_date",
        title: "@Issued date@",
        width: '150px',
        headerAttributes: { style: "white-space: normal"},
        format: "{0: dd/MM/yy HH:mm}",
        filterable: true,
    },{
        field: "issued_place",
        title: "@Issued place@",
        width: '200px',
        headerAttributes: { style: "white-space: normal"},
        filterable: true,
    },{
        field: "bank_acc",
        title: "@Bank account@",
        width: '200px',
        headerAttributes: { style: "white-space: normal"},
        filterable: true,
    },{
        field: "bank_name",
        title: "@Bank name@",
        width: '200px',
        headerAttributes: { style: "white-space: normal"},
        filterable: true,
    },{
        field: "bank_branch",
        title: "@Branch@",
        width: '200px',
        headerAttributes: { style: "white-space: normal"},
        filterable: true,
    },{
        field: "province",
        title: "@Province@",
        width: '200px',
        headerAttributes: { style: "white-space: normal"},
        filterable: true,
    },{
        field: "phone",
        title: "@Phone@",
        width: '150px',
        headerAttributes: { style: "white-space: normal"},
        filterable: true,
    },{
        field: "old_cus_farmer",
        title: "@Old customer and farmer@",
        width: '150px',
        headerAttributes: { style: "white-space: normal"},
        filterable: true,
    },{
        field: "new_cus",
        title: "@New customer@",
        width: '150px',
        headerAttributes: { style: "white-space: normal"},
        filterable: true,
    },{
        field: "tl_name",
        title: "@Telesale name@",
        width: '200px',
        headerAttributes: { style: "white-space: normal"},
        filterable: true,
    },{
        field: "farmer_collaboration",
        title: "@Farmer collaboration@",
        width: '200px',
        headerAttributes: { style: "white-space: normal"},
        filterable: true,
    },{
        field: "jivf_staff",
        title: "@JIVF staff@",
        width: '200px',
        headerAttributes: { style: "white-space: normal"},
        filterable: true,
    },{
        field: "int",
        title: "@Interest@",
        width: '150px',
        headerAttributes: { style: "white-space: normal"},
        filterable: true,
    },{
        field: "type",
        title: "@Type@",
        width: '150px',
        headerAttributes: { style: "white-space: normal"},
        filterable: true,
    },{
        field: "check",
        title: "@Check@ (81000)",
        width: '200px',
        headerAttributes: { style: "white-space: normal"},
        filterable: true,
    },{
        field: "created_at",
        title: "@Created at@",
        width: '150px',
        headerAttributes: { style: "white-space: normal"},
        format: "{0: dd/MM/yy HH:mm}",
        filterable: true,
    },{
        field: "updated_at",
        title: "@Last modified@",
        width: '150px',
        headerAttributes: { style: "white-space: normal"},
        format: "{0: dd/MM/yy HH:mm}",
        filterable: true,
    },{
        field: "updated_by",
        title: "@Modified by@",
        width: '150px',
        headerAttributes: { style: "white-space: normal"},
        filterable: true,
    }],
});
</script>

<script src="<?= STEL_PATH.'js/table.js' ?>"></script>

<script type="text/javascript">
	function deleteDataItemChecked() {
		var checkIds = Table.grid.selectedKeyNames();
		if(checkIds.length) {
			swal({
			    title: "@Are you sure@?",
			    text: "@Once deleted, you will not be able to recover these documents@!",
			    icon: "warning",
			    buttons: true,
			    dangerMode: true,
		    })
		    .then((willDelete) => {
				if (willDelete) {
					checkIds.forEach(uid => {
						var dataItem = Table.dataSource.getByUid(uid);
					    Table.dataSource.remove(dataItem);
					    Table.dataSource.sync();
					})
				}
		    });
		} else {
			swal({
				title: "@No row is checked@!",
			    text: "@Please check least one row to remove@",
			    icon: "error"
			});
		}
	}

	function importData(ele) {
		var uid = $(ele).data('uid');
		var dataItem = Table.dataSource.getByUid(uid);
		router.navigate(`/import/${dataItem.id}`);
	}

	function detailData(ele) {
		var uid = $(ele).data('uid');
		var dataItem = Table.dataSource.getByUid(uid);
		router.navigate(`/detail/${dataItem.id}`);
	}

	$(document).on("click", ".grid-name", function() {
		detailData($(this).closest("tr"));
	});

    Table.init();
</script>

<style>
    .k-grid-header th.k-header {
        vertical-align: middle;
    }

    .k-grid-content {
        max-height: 350px;
    }
</style>
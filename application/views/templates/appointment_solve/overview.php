<div class="row" style="margin: 10px 0">
	<div class="col-sm-2" id="page-widget"></div>
	<div class="col-sm-9 filter-mvvm" style="display: none"></div>
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
    	<li class="devide"></li>
        <a href="javascript:void(0)" data-type="delete" onclick="deleteDataItem(this)"><li><i class="fa fa-times-circle text-danger"></i><span>@Delete@</span></li></a>
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
    },
    parse(response) {
        response.data.map(function(doc) {
            return doc;
        });
        return response;
    },
    scrollable: true,
    columns: [{
        title: "@Telesale code@",
        field: "tl_code",
        width: "150px",
        headerAttributes: { style: "white-space: normal"},
        filterable: false,
    },{
        field: "tl_name",
        title: "@Telesale name@",
        width: "200px",
        headerAttributes: { style: "white-space: normal"},
        filterable: false,
    },{
        field: "status",
        title: "@Status@",
        width: "150px",
        headerAttributes: { style: "white-space: normal"},
        filterable: false,
    },{
        field: "cmnd",
        title: "@ID@",
        width: "150px",
        headerAttributes: { style: "white-space: normal"},
        filterable: false,
    },{
        field: "cif",
        title: "CIF",
        width: "200px",
        headerAttributes: { style: "white-space: normal"},
        filterable: false,
    },{
        field: "cus_name",
        title: "@Customer name@",
        width: "200px",
        headerAttributes: { style: "white-space: normal"},
        filterable: false,
    },{
        field: "cus_phone",
        title: "@MP no.@",
        width: "150px",
        headerAttributes: { style: "white-space: normal"},
        filterable: false,
    },{
        field: "loan_amount",
        title: "@Loan amount@",
        width: "150px",
        headerAttributes: { style: "white-space: normal"},
        filterable: false,
    },{
        field: "prod_name",
        title: "@Product name@",
        width: "200px",
        headerAttributes: { style: "white-space: normal"},
        filterable: false,
    },{
        field: "dealer_name",
        title: "@Dealer name@",
        width: "200px",
        headerAttributes: { style: "white-space: normal"},
        filterable: false,
    },{
        field: "sc_name",
        title: "@Sc name@",
        width: "200px",
        headerAttributes: { style: "white-space: normal"},
        filterable: false,
    },{
        field: "sc_phone",
        title: "@Sc phone@",
        width: "150px",
        headerAttributes: { style: "white-space: normal"},
        filterable: false,
    },{
        field: "loan_history",
        title: "@Loan history@ - @Econ@",
        width: "250px",
        headerAttributes: { style: "white-space: normal"},
        filterable: false,
    },{
        field: "dc_code",
        title: "@DC code@",
        width: "150px",
        headerAttributes: { style: "white-space: normal"},
        filterable: false,
    },{
        field: "is_code",
        title: "@IS code@",
        width: "150px",
        headerAttributes: { style: "white-space: normal"},
        filterable: false,
    },
    // {
    //     field: "created_at",
    //     title: "@Created at@",
    //     width: "150px",
    //     headerAttributes: { style: "white-space: normal"},
    // },{
    //     field: "updated_at",
    //     title: "@Last modified@",
    //     width: "150px",
    //     headerAttributes: { style: "white-space: normal"},
    // },{
    //     field: "assigned_by",
    //     title: "@Assigned by@",
    //     width: "150px",
    //     headerAttributes: { style: "white-space: normal"},
    // },
    // {
    //     // Use uid to fix bug data-uid of row undefined
    //     title: `<a class='btn btn-sm btn-circle btn-action btn-primary' onclick='return deleteDataItemChecked();'><i class='fa fa-times-circle'></i></a>`,
    //     template: '<a role="button" class="btn btn-sm btn-circle btn-action btn-primary" data-uid="#: uid #"><i class="fa fa-ellipsis-v"></i></a>',
    //     width: "50px"
    // }
    ],
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
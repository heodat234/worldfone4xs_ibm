<div class="row" style="margin: 10px 0">
	<div class="col-sm-2" id="page-widget"></div>
	<div class="col-sm-9 filter-mvvm" style="display: none"></div>
</div>
<div class="row">
	<div class="col-sm-12" style="min-height: 80vh;">
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
            doc.customer_info = (typeof doc.customer_info != 'undefined' && doc.customer_info) ? doc.customer_info : {};
            return doc;
        });
        return response;
    },
    scrollable: true,
    columns: [{
        title: "@Dealer code@",
        field: "dealer_code",
        width: "150px",
        headerAttributes: { style: "white-space: normal"},
        filterable: true,
    },{
        field: "dealer_name",
        title: "@Dealer name@",
        width: "200px",
        headerAttributes: { style: "white-space: normal"},
        filterable: true,
    },{
        field: "cus_id",
        title: "@Customer id@",
        width: "150px",
        headerAttributes: { style: "white-space: normal"},
        filterable: false,
        hidden: true
    },{
        field: "account_number",
        title: "@Account number@",
        width: "150px",
        headerAttributes: { style: "white-space: normal"},
        filterable: true,
    },{
        field: "cus_name",
        title: "@Customer name@",
        width: "200px",
        headerAttributes: { style: "white-space: normal"},
        filterable: true,
    },{
        field: "payment_advance",
        title: "@Payment advance@",
        width: "200px",
        headerAttributes: { style: "white-space: normal"},
        filterable: false,
    },{
        field: "approved_limit",
        title: "@Approved limit@",
        width: "150px",
        headerAttributes: { style: "white-space: normal"},
        filterable: false,
    },{
        field: "interest_rate",
        title: "@Interest rate@",
        width: "200px",
        headerAttributes: { style: "white-space: normal"},
        filterable: false,
    },{
        field: "tenor",
        title: "@Tenor@",
        width: "150px",
        headerAttributes: { style: "white-space: normal"},
        filterable: true,
    },{
        field: "application_date",
        title: "@Appication date@",
        width: "200px",
        headerAttributes: { style: "white-space: normal"},
        filterable: false,
    },{
        field: "released_date",
        title: "@Released date@",
        width: "200px",
        headerAttributes: { style: "white-space: normal"},
        filterable: false,
    },{
        field: "product_model",
        title: "@Product model@",
        width: "200px",
        headerAttributes: { style: "white-space: normal"},
        filterable: true,
    },{
        field: "sale_consultant_code",
        title: "@Sale consultant code@",
        width: "150px",
        headerAttributes: { style: "white-space: normal"},
        filterable: true,
    },{
        field: "sale_consultant_name",
        title: "@Sale consultant name@",
        width: "250px",
        headerAttributes: { style: "white-space: normal"},
        filterable: true,
    },{
        field: "staff_code_in_charge_released",
        title: "@Staff code in charge released@",
        width: "150px",
        headerAttributes: { style: "white-space: normal"},
        filterable: true,
    },{
        field: "staff_name_in_charge_release",
        title: "@Staff name in charge released@",
        width: "150px",
        headerAttributes: { style: "white-space: normal"},
        filterable: true,
    },{
        field: "status",
        title: "@Status@",
        width: "150px",
        headerAttributes: { style: "white-space: normal"},
        filterable: true,
    },{
        field: "birthday",
        title: "@Birthday@",
        width: "150px",
        headerAttributes: { style: "white-space: normal"},
        filterable: false,
    },{
        field: "temp_address",
        title: "@Temp address@",
        width: "150px",
        headerAttributes: { style: "white-space: normal"},
        filterable: true,
    },{
        field: "temp_district",
        title: "@District@",
        width: "150px",
        headerAttributes: { style: "white-space: normal"},
        filterable: true,
    },{
        field: "temp_province",
        title: "@Province@",
        width: "150px",
        headerAttributes: { style: "white-space: normal"},
        filterable: true,
    },{
        field: "address",
        title: "@Address@",
        width: "150px",
        headerAttributes: { style: "white-space: normal"},
        filterable: true,
    },{
        field: "district",
        title: "@District@",
        width: "150px",
        headerAttributes: { style: "white-space: normal"},
        filterable: true,
    },{
        field: "province",
        title: "@Province@",
        width: "150px",
        headerAttributes: { style: "white-space: normal"},
        filterable: true,
    },
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
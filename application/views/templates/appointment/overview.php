<div class="row" style="margin: 10px 0">
	<div class="col-sm-2" id="page-widget"></div>
	<div class="col-sm-9 filter-mvvm" style="display: none"></div>
</div>
<div class="row">
	<div class="col-sm-12">
	    <!-- Table Styles Content -->
	    <div id="grid_1"></div>
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
        fields: {
            last_modified: {type: 'date'},
            created_at: {type: 'date'},
            updated_at: {type: 'date'}
        }
    },
    parse(response) {
        response.data.map(function(doc) {
            doc.created_at = doc.created_at ? new Date(doc.created_at * 1000) : undefined;
            doc.updated_at = doc.updated_at ? new Date(doc.updated_at * 1000) : undefined;
            doc.last_modified = doc.last_modified ? new Date(doc.last_modified * 1000): undefined;
            return doc;
        });
        return response;
    },
    scrollable: true,
    columns: [{
        title: "@Telesale code@",
        field: "assign",
        width: "150px",
        headerAttributes: { style: "white-space: normal"},
        filterable: true,
    },{
        field: "assign_name",
        title: "@Telesale name@",
        width: "200px",
        headerAttributes: { style: "white-space: normal"},
        filterable: true,
    },{
        field: "status",
        title: "@Status@",
        width: "150px",
        headerAttributes: { style: "white-space: normal"},
        filterable: true,
    },{
        field: "cmnd",
        title: "@ID@",
        width: "150px",
        headerAttributes: { style: "white-space: normal"},
        filterable: true,
    },{
        field: "cif",
        title: "CIF",
        width: "200px",
        headerAttributes: { style: "white-space: normal"},
        filterable: true,
    },{
        field: "tele_name",
        title: "@Customer name@",
        width: "200px",
        headerAttributes: { style: "white-space: normal"},
        filterable: true,
    },{
        field: "tele_phone",
        title: "@MP no.@",
        width: "150px",
        headerAttributes: { style: "white-space: normal"},
        filterable: true,
        template: function(dataItem) {
            return (typeof dataItem['tele_phone'] !== 'undefined' && typeof dataItem['tele_id'] !==' undefined') ? gridPhone(dataItem['tele_phone'], dataItem['tele_id'], 'customer') : '<span></span>';
        }
    },{
        field: "tele_note",
        title: "@Customer note@",
        width: "200px",
        headerAttributes: { style: "white-space: normal"},
        filterable: true,
    },{
        field: "loan_amount",
        title: "@Loan amount@",
        width: "150px",
        headerAttributes: { style: "white-space: normal"},
        filterable: true,
    },{
        field: "prod_name",
        title: "@Product name@",
        width: "200px",
        headerAttributes: { style: "white-space: normal"},
        filterable: true,
    },{
        field: "dealer_name",
        title: "@Dealer name@",
        width: "200px",
        headerAttributes: { style: "white-space: normal"},
        filterable: true,
    },{
        field: "sc_name",
        title: "@Sc name@",
        width: "200px",
        headerAttributes: { style: "white-space: normal"},
        filterable: true,
    },{
        field: "sc_phone",
        title: "@Sc phone@",
        width: "150px",
        headerAttributes: { style: "white-space: normal"},
        filterable: true,
    },{
        field: "loan_history",
        title: "@Loan history@ - @Econ@",
        width: "250px",
        headerAttributes: { style: "white-space: normal"},
        filterable: true,
    },{
        field: "dc_code",
        title: "@DC code@",
        width: "150px",
        headerAttributes: { style: "white-space: normal"},
        filterable: true,
    },{
        field: "is_code",
        title: "@IS code@",
        width: "150px",
        headerAttributes: { style: "white-space: normal"},
        filterable: true,
    },{
        field: "last_modified",
        title: "@Last modified@",
        width: "150px",
        headerAttributes: { style: "white-space: normal"},
        filterable: true,
        template: (dataItem) => {
            return gridDate(dataItem.last_modified, "dd/MM/yyyy HH:mm:ss");
        }
    },],
});
</script>

<script src="<?= STEL_PATH.'js/tablev3.js' ?>"></script>

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
    kendo.culture("vi-VN");
    Table.init();
</script>
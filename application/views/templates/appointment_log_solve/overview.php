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
            console.log(doc);
            doc.appointment_date = doc.appointment_date ? new Date(doc.appointment_date * 1000) : undefined;
            doc.createdAt = doc.createdAt ? new Date(doc.createdAt * 1000) : undefined;
            return doc;
        });
        return response;
    },
    scrollable: true,
    columns: [{
        title: "@Created at@",
        field: "createdAt",
        headerAttributes: { style: "white-space: normal"},
        width: "110px",
        filterable: false,
        template: data => gridDate(data.createdAt),
    },{
        title: "@Telesale code@",
        field: "tl_code",
        headerAttributes: { style: "white-space: normal"},
        width: "110px",
        filterable: false
    },{
        field: "tl_name",
        title: "@Telesale name@",
        headerAttributes: { style: "white-space: normal"},
        width: "110px",
        filterable: false
    },{
        title: "@Customer@",
        columns: [{
            field: "customer_info.id_no",
            title: "@National ID@",
            headerAttributes: { style: "white-space: normal"},
            width: "110px",
            filterable: false
        }, {
            field: "customer_info.customer_name",
            title: "@Name@",
            width: "200px",
            headerAttributes: { style: "white-space: normal"},
            filterable: false
        }, {
            field: "customer_info.mobile_phone_no",
            title: "@Phone@",
            width: "150px",
            headerAttributes: { style: "white-space: normal"},
            filterable: false
        }]
    },{
        field: "appointment_date",
        title: "@Appointment date@",
        headerAttributes: { style: "white-space: normal"},
        width: "150px",
        template: data => gridDate(data.appointment_date, "dd/MM/yyyy"),
        filterable: false
    },{
        title: "@Loan Counter@",
        columns: [{
            field: "dealer_code",
            title: "@Code@",
            headerAttributes: { style: "white-space: normal"},
            width: "100px",
            filterable: false
        }, {
            field: "dealer_name",
            title: "@Name@",
            headerAttributes: { style: "white-space: normal"},
            width: "200px",
            filterable: false
        }, {
            field: "dealer_address",
            title: "@Address@",
            headerAttributes: { style: "white-space: normal"},
            width: "250px",
            filterable: false
        }]
    },{
        title: "SC",
        columns: [{
            field: "sc_code",
            title: "@Code@",
            headerAttributes: { style: "white-space: normal"},
            width: "100px",
            filterable: false
        }, {
            field: "sc_name",
            title: "@Name@",
            headerAttributes: { style: "white-space: normal"},
            width: "200px",
            filterable: false
        }, {
            field: "sc_phone",
            title: "@Phone@",
            headerAttributes: { style: "white-space: normal"},
            width: "150px",
            filterable: false
        }]
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
        text-align: center;
    }
</style>
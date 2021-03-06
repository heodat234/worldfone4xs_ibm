<div class="row" style="margin: 10px 0">
	<div class="col-sm-2" id="page-widget"></div>
	<div class="col-sm-9 filter-mvvm" style="display: none"></div>
</div>
<div class="row">
	<div class="col-sm-12">
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
            appointment_date: {type: 'date'},
            created_at: {type: 'date'},
            updated_at: {type: 'date'}
        } 
    },
    parse(response) {
        response.data.map(function(doc) {
            doc.appointment_date = doc.appointment_date ? new Date(doc.appointment_date * 1000) : undefined;
            doc.created_at = doc.created_at ? new Date(doc.created_at * 1000) : undefined;
            doc.customer_info = (typeof doc.customer_info !== 'undefined') ? doc.customer_info : {};
            doc.updated_at = doc.updated_at ? new Date(doc.updated_at * 1000) : undefined;
            return doc;
        });
        return response;
    },
    scrollable: true,
    sort: [{field: 'appointment_date', dir: 'desc'}],
    columns: [{
        // Use uid to fix bug data-uid of row undefined
        title: ``,
        template: '<a role="button" class="btn btn-sm btn-circle btn-action btn-primary" data-uid="#: uid #"><i class="fa fa-ellipsis-v"></i></a>',
        width: 40
    },{
        title: "@Created at@",
        field: "created_at",
        headerAttributes: { style: "white-space: normal"},
        width: "110px",
        filterable: true,
        template: data => gridDate(data.created_at),
    },{
        title: "@Telesale code@",
        field: "tl_code",
        headerAttributes: { style: "white-space: normal"},
        width: "110px",
        filterable: true
    },{
        field: "tl_name",
        title: "@Telesale name@",
        headerAttributes: { style: "white-space: normal"},
        width: "110px",
        filterable: true
    },{
        title: "@Customer@",
        columns: [{
            field: "id_no",
            title: "@National ID@",
            headerAttributes: { style: "white-space: normal"},
            width: "110px",
            filterable: true
        }, {
            field: "name",
            title: "@Name@",
            width: "200px",
            headerAttributes: { style: "white-space: normal"},
            filterable: true
        }, {
            field: "customer_info.phone",
            title: "@Phone@",
            width: "150px",
            headerAttributes: { style: "white-space: normal"},
            filterable: false,
            template: function(dataItem) {
                return gridPhone(dataItem['phone'], dataItem['id'], 'customer');
            }
        }, {
            field: "customer_info.note",
            title: "@Note@",
            width: "200px",
            headerAttributes: { style: "white-space: normal"},
            filterable: false
        }]
    },{
        field: "appointment_date",
        title: "@Appointment date@",
        headerAttributes: { style: "white-space: normal"},
        width: "150px",
        template: data => gridDate(data.appointment_date, "dd/MM/yyyy"),
        filterable: true
    },{
        title: "@Loan Counter@",
        columns: [{
            field: "dealer_code",
            title: "@Code@",
            headerAttributes: { style: "white-space: normal"},
            width: "100px",
            filterable: true
        }, {
            field: "dealer_name",
            title: "@Name@",
            headerAttributes: { style: "white-space: normal"},
            width: "200px",
            filterable: true
        }, {
            field: "dealer_address",
            title: "@Address@",
            headerAttributes: { style: "white-space: normal"},
            width: "250px",
            filterable: true
        }]
    },{
        title: "SC",
        columns: [{
            field: "sc_code",
            title: "@Code@",
            headerAttributes: { style: "white-space: normal"},
            width: "100px",
            filterable: true
        }, {
            field: "sc_name",
            title: "@Name@",
            headerAttributes: { style: "white-space: normal"},
            width: "200px",
            filterable: true
        }, {
            field: "sc_phone",
            title: "@Phone@",
            headerAttributes: { style: "white-space: normal"},
            width: "150px",
            filterable: true,
            template: function(dataItem) {
                if(typeof dataItem['sc_id'] != 'undefined' && dataItem['sc_id'] && typeof dataItem['sc_phone'] != 'undefined' && dataItem['sc_phone']) {
                    return gridPhone(dataItem['sc_phone'].replace(/\s/g, ''), dataItem['sc_id'], 'sc');
                }
                else {
                    if(typeof dataItem['sc_phone'] != 'undefined' && dataItem['sc_phone']) {
                        return dataItem['sc_phone'].replace(/\s/g, '');
                    }
                    else {
                        return '<span></span>';
                    }
                }
            }
        }]
    }, {
        field: "updated_at",
        title: "@Last modified@",
        width: "150px",
        headerAttributes: { style: "white-space: normal"},
        filterable: true,
        template: (dataItem) => {
            return gridDate(dataItem.updated_at, 'dd/MM/yyyy HH:mm:ss');
        }
    },],
});
</script>

<script src="<?= STEL_PATH.'js/tablev1.js' ?>"></script>

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

    async function addForm() {
        var formHtml = await $.ajax({
            url: Config.templateApi + Config.collection + "/form",
            error: errorDataSource
        });
        var model = Object.assign(Config.observable, {
            item: {},
            save: function() {
                var item = this.get('item');
                if(typeof this.item.appointment_date === 'undefined' || this.item.appointment_date === '' || this.item.appointment_date === null) {
                    notification.show("Xin vui lòng chọn ngày lịch hẹn.", 'error');
                    return false;
                }
                var appointment_date = new Date(this.item.appointment_date);
                appointment_date.setHours(0, 0, 0, 0);
                item.appointment_date = appointment_date.getTime() / 1000;
                $.ajax({
                    url: ENV.vApi + "appointment_log/create",
                    data: kendo.stringify(item.toJSON()),
                    error: errorDataSource,
                    contentType: "application/json; charset=utf-8",
                    type: "PUT",
                    success: function() {
                        closeForm();
                        Table.dataSource.sync().then(() => {Table.dataSource.read()});
                    }
                });
            }
        });
        kendo.destroy($("#right-form"));
        $("#right-form").empty();
        var kendoView = new kendo.View(formHtml, { wrap: false, model: model, evalTemplate: false });
        kendoView.render($("#right-form"));
    }

    async function editForm(ele) {
        var dataItem = Table.dataSource.getByUid($(ele).data("uid")),
            dataItemFull = await $.ajax({
                url: `${Config.crudApi+Config.collection}/detail/${dataItem.id}`,
                error: errorDataSource
            }),
            formHtml = await $.ajax({
                url: Config.templateApi + Config.collection + "/form",
                error: errorDataSource
            });
            dataItemFull['appointment_date'] = (dataItem['appointment_date']) ? kendo.toString(dataItem['appointment_date'], 'dd/MM/yyyy') : '';
        var model = Object.assign(Config.observable, {
            item: dataItemFull,
            save: function() {
                if(typeof this.item.appointment_date === 'undefined' || this.item.appointment_date === '' || this.item.appointment_date === null) {
                    notification.show("Xin vui lòng chọn ngày lịch hẹn.", 'error');
                    return false;
                }
                var item = this.get('item');
                if(typeof item.appointment_date == 'string') {
                    appointment_date_raw = item.appointment_date.split('/');
                    var appointment_date = new Date(appointment_date_raw[2], appointment_date_raw[1] - 1, appointment_date_raw[0]);
                }
                else {
                    var appointment_date = item.appointment_date
                }
                appointment_date.setHours(0, 0, 0, 0);
                item.appointment_date = appointment_date.getTime() / 1000;
                $.ajax({
                    url: ENV.vApi + "appointment_log_solve/update/" + dataItemFull['id'],
                    data: kendo.stringify(this.item.toJSON()),
                    error: errorDataSource,
                    contentType: "application/json; charset=utf-8",
                    type: "PUT",
                    success: function() {
                        closeForm();
                        Table.dataSource.sync().then(() => {Table.dataSource.read()});
                    }
                })
            }
        });
        kendo.destroy($("#right-form"));
        $("#right-form").empty();
        var kendoView = new kendo.View(formHtml, { wrap: false, model: model, evalTemplate: false });
        kendoView.render($("#right-form"));
    }
</script>

<style>
    .k-grid-header th.k-header {
        vertical-align: middle;
        text-align: center;
    }
</style>
<?php $id = $this->input->get("id") ?>
<div class="col-sm-2" style="margin: 10px 0" id="page-widget"></div>
<div class="col-sm-10 change-remove" style=" margin: 10px 0;">
    <div class="col-sm-1"><a role="button" data-type="action/delete" class="btn btn-alt btn-sm btn-primary" data-toggle="dropdown" onclick="removeRow(this)"><i class="fa fa-remove"></i> <b>@Remove@</b></a></div>
</div>
<div class="col-sm-12 filter-mvvm" style="display: none; margin: 10px 0"></div>
<div class="col-sm-12" style="overflow-y: auto; padding: 0">
	<div id="grid"></div>
</div>
<style type="text/css">
    .change-remove {
        display: none;
    }
</style>
<script>
    var Config = {
        filter: '<?= $id ?>' != '' ? {field: "id_import", operator: "eq", value: '<?= $id ?>'} : null,
        crudApi: `${ENV.restApi}`,
        templateApi: `${ENV.templateApi}`,
        collection: "data_library",
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
                first_due_date: {type: "date"},
            }
        },
        parse: function (response) {
            response.data.map(function(doc) {
                doc.createdAt = new Date(doc.createdAt * 1000);
                doc.exporting_date = doc.exporting_date ? new Date(doc.exporting_date * 1000) : null;
                doc.date_of_birth = doc.date_of_birth ? new Date(doc.date_of_birth * 1000) : null;
                doc.date_send_data = doc.date_send_data ? new Date(doc.date_send_data * 1000) : null;
                doc.date_receive_data = doc.date_receive_data ? new Date(doc.date_receive_data * 1000) : null;
                doc.first_due_date = doc.first_due_date ? new Date(doc.first_due_date * 1000) : null;
                return doc;
            })
            return response;
        },
        autoBind: false,
        refresh: false,
    }; 
</script>
<script src="<?= STEL_PATH.'js/tablev4.js' ?>"></script>
<script type="text/javascript">
	var router = new kendo.Router({routeMissing: function(e) { router.navigate("/") }});
    var customerFields = new kendo.data.DataSource({
        serverPaging: true,
        serverFiltering: true,
        serverSorting: true,
        transport: {
            read: `${ENV.vApi}model/read`,
            parameterMap: parameterMap
        },
        schema: {
            data: "data",
            
        },
        pageSize: 30,
        filter: {
            field: "collection",
            operator: "eq",
            value: (ENV.type ? ENV.type + "_" : "") + 'Datalibrary'
        },
        sort: {field: "index", dir: "asc"},
    })
    customerFields.read().then(function(){
        var columns = customerFields.data().toJSON();
        columns.map(col => {
            col.width = 130;
            col.filterable = false;
            switch (col.type) {
                // case "name":
                //     col.template = (dataItem) => gridName(dataItem[col.field]);
                //     break;
                // case "phone": case "arrayPhone":
                //     col.template = (dataItem) => gridPhone(dataItem[col.field]);
                //     break;
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
        columns.unshift({
            selectable: true,
            width: 32,
            hidden: (PERMISSION.actions.includes("delete")) ? false : true
        });
        Table.columns = columns;
        Table.init();
        Table.grid.bind("change", grid_change);
        showFilter();
    })
    // $( document ).ready(function() {
    //     $('.change-remove').hide();
    // });
    var select = [];
    
    function grid_change(arg) {
        var selectUid = this.selectedKeyNames();
        if (selectUid.length > 0) {
            //hiên Re-Assign
            select = [];
            $('.change-remove').show();
            for(var i in selectUid){
                var item = Table.dataSource.getByUid(selectUid[i]);
                select.push(item.id);
            }
        }else{
            //an Re-Assign
            $('.change-remove').hide();
        }
        // console.log(this.selectedKeyNames());
    }

    function removeRow() {
        $('#grid').data("kendoGrid").select().each(function () {
            Table.grid.removeRow($(this).closest("tr"));
            Table.dataSource.sync();
        });
       
    }

    function detailData(ele) {
        var uid = $(ele).data('uid');
        var dataItem = Table.dataSource.getByUid(uid);
        router.navigate(`/detail_customer/${dataItem.id}`);
    }

	$(document).on("click", ".grid-name", function() {
		detailData($(this).closest("tr"));
	})
    
    function showFilter() {
        var element = document.getElementById('data-library');
        if(element) {
            element.click();
        }
        else {
            setTimeout(showFilter, 100)
        }
    }
</script>
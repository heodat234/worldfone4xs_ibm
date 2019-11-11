<script>
function typeEditor(container, options) {
    let field = options.field;
    var select = $(`<input name="${field}"/>`)
        .appendTo(container)
        .kendoDropDownList({
            valuePrimitive: true,
            dataTextField: 'value', 
            dataValueField: 'value',
            filter: "contains",
            dataSource: dataSourceDropDownListPrivate("DataType", ["value"]),
            select: function(e) {
                options.model.set("type", e.dataItem.value);
            }
        }).data("kendoDropDownList");
    select.open();
}; 

var Config = {
    crudApi: `${ENV.restApi}`,
    templateApi: `${ENV.templateApi}`,
    collection: "model",
    observable: {
    },
    model: {
        id: "id",
        fields: {
            type: {type: "string", defaultValue: "string"},
            index: {type: "number"}
        }
    },
    columns: [{
            selectable: true,
            width: 32,
            locked: true
        },{
            field: "index",
            title: "#",
            width: 70
        },{
            field: "collection",
            title: "Collection",
            width: 140
        },{
            field: "field",
            title: "Field",
            width: 140
        },{
            field: "title",
            title: "Title",
            width: 160
        },{
            field: "type",
            title: "Type",
            editor: typeEditor,
            width: 120
        },{
            field: "sub_type",
            title: "Sub Type"
        },{
            title: `<a class='btn btn-sm btn-circle btn-action' onclick='return deleteDataItemChecked();'><i class='fa fa-times-circle'></i></a>`,
            command: ["edit", "destroy"],
            width: 200
        }]
}; 

function duplicateForDepartment() {
    $.get(`${ENV.restApi}configtype`).then(
      function(response) {
        if(!response.data) return;
        let data = response.data;
        let buttons = {cancel: true};
                    
        for (let i = 0; i < data.length; i++) {
            buttons[data[i].type] = {text: data[i].type};
        }

        swal({
          title: "Duplicate",
          text: 'Duplicate data from deparment?',
          icon: "warning",
          buttons: buttons
        })
        .then(fromDepartment => {
            if(!fromDepartment) return;
            swal({
              title: "Duplicate",
              text: 'Duplicate data for new deparment.',
              icon: "warning",
              buttons: buttons
            })
            .then(toDepartment => {
                if(!toDepartment) return;
                $.ajax({
                    url: `${ENV.vApi}${Config.collection}/duplicate`,
                    data: {fromDepartment: fromDepartment, toDepartment: toDepartment},
                    success: function(e) {
                        if(e.status) {
                            Table.dataSource.read();
                            notification.show("Duplicate success", "success");
                        } else notification.show("Duplicate not success " + e.message, "error");
                    },
                    error: errorDataSource
                })
            })
        })
      }, errorDataSource
    ); 
}

function deleteDataItemChecked() {
    var checkIds = Table.grid.selectedKeyNames();
    if(checkIds.length) {
        swal({
            title: "Are you sure?",
            text: "Once deleted, you will not be able to recover these documents!",
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
            title: "No row is checked!",
            text: "Please check least one row to remove",
            icon: "error"
        });
    }
}
</script>

<!-- Page content -->
<div id="page-content">
    <!-- Table Styles Header -->
    <ul class="breadcrumb breadcrumb-top">
        <li>Admin</li>
        <li>Model</li>
        <li class="pull-right none-breakcrumb" id="top-row">
            <div class="btn-group btn-group-sm">
                <button class="btn btn-alt btn-default" onclick="duplicateForDepartment(this)">Duplicate for department</button>
            </div>
        </li>
    </ul>
    <!-- END Table Styles Header -->

    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12" style="height: 80vh; overflow-y: auto; padding: 0">
                <!-- Table Styles Content -->
                <div id="grid"></div>
                <!-- END Table Styles Content -->
            </div>
        </div>
    </div>
</div>
<!-- END Page Content -->
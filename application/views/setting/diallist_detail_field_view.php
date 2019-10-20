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

function subtypeEditor(container, options) {
    let field = options.field;
    var select = $(`<input name="${field}"/>`)
        .appendTo(container)
        .kendoDropDownList({
            valuePrimitive: true,
            dataTextField: 'text', 
            dataValueField: 'value',
            dataSource: dataSourceJsonData(["Diallist", "type"], res => {
                res.data.unshift({text: "@Common@", value: null});
                return res;
            }),
            select: function(e) {
                options.model.set("type", e.dataItem.value);
            }
        }).data("kendoDropDownList");
    select.open();
};  

var Config = {
    crudApi: `${ENV.restApi}`,
    templateApi: `${ENV.templateApi}`,
    collection: "diallistdetailfield",
    observable: {
    },
    model: {
        id: "id",
        fields: {
        	index: {type: "number"},
        	collection: {defaultValue: "Diallist_detail"}
        }
    },
    columns: [{
            field: "index",
            title: "#",
            width: 70
        },{
            field: "title",
            title: "Title",
            width: 220
        },{
            field: "field",
            title: "Field",
            width: 220
        },{
            field: "type",
            title: "Type",
            editor: typeEditor
        },{
            field: "sub_type",
            title: "Diallist type",
            editor: subtypeEditor
        },{
            command: ["edit", "destroy"],
            width: 200
        }]
}; 
</script>

<!-- Page content -->
<div id="page-content">
    <!-- Table Styles Header -->
    <ul class="breadcrumb breadcrumb-top">
        <li>Setting</li>
        <li>Diallist detail field</li>
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
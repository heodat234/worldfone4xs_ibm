<script>
var Config = {
    crudApi: `${ENV.restApi}`,
    templateApi: `${ENV.templateApi}`,
    collection: "module",
    observable: {
        arrayOpen: function(e) {
            e.preventDefault();
            var widget = e.sender;
            widget.input[0].onkeyup = function(ev) {
                if(ev.keyCode == 13) {
                    var values = widget.value();
                    values.push(this.value);
                    widget.dataSource.data(values);
                    widget.value(values);
                    widget.trigger("change");
                }
            }
        }
    },
    model: {
        id: "id",
        fields: {
            name: {type: "string", defaultValue: ""},
            active: {type: "boolean"}
        }
    }
}; 
</script>


<!-- Table Styles Header -->
<ul class="breadcrumb breadcrumb-top">
    <li>Admin</li>
    <li>Modules</li>
    <li class="pull-right none-breakcrumb">
        <a role="button" onclick="openForm({title: `Add Module`,width: 400}); addForm(this)" href="javascript:void(0)" class="btn btn-sm"><b>Add</b></a>
    </li>
</ul>
<!-- END Table Styles Header -->

<div class="container-fluid after-breadcrumb">
    <h4 class="fieldset-legend" style="margin: 10px 0 30px"><span style="font-weight: 500">YOUR MODULES</span></h4>
    <div class="row mvvm">
        <div class="col-sm-12" style="height: 60vh; overflow-y: auto; padding: 0">
            <!-- Table Styles Content -->
            <div data-role="listview" id="listview"
             data-template="template"
             data-bind="source: dataSource"></div>
            <!-- END Table Styles Content -->
        </div>
        <div class="col-sm-12">
            <div data-role="pager" data-bind="source: dataSource"></div>
        </div>
    </div>
</div>

<div id="action-menu">
    <ul>
        <a href="javascript:void(0)" data-type="update" onclick="openForm({title: `Edit Module`,width: 400}); editForm(this)"><li><i class="fa fa-pencil-square-o text-warning"></i><span>Edit</span></li></a>
        <a href="javascript:void(0)" data-type="delete" onclick="deleteDataItem(this)"><li><i class="fa fa-times-circle text-danger"></i><span>Delete</span></li></a>
    </ul>
</div>
<!-- END Page Content -->
<!-- <input type="checkbox" data-bind="checked: default"> -->
<script id="template" type="text/x-kendo-template">
    <div class="view-container">
        <span class="check-active">#= gridBoolean(data.active) #</span>
        <span data-bind="text: name"></span>
        <div class="pull-right">
            <a href="javascript:void(0)" class="btn-action"><i class="fa fa-plug fa-2x"></i></a>
        </div>
    </div>
</script>
<style type="text/css">
    .view-container {
        border: 1px solid lightgray;
        padding: 10px 20px;
        margin: 10px;
        width: 240px;
        float: left;
    }
    .view-container span {
        font-size: 20px;
    }
    #listview {
        border: 0;
    }
    .check-active {
        border-radius: 7px;
        border: 1px dashed gray;
        padding: 1px 3px;
    }
</style>

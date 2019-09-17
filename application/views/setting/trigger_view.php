<script>
    var Config = {
        crudApi: `${ENV.restApi}`,
        templateApi: `${ENV.templateApi}`,
        collection: "trigger",
        currentTrigger: 'create',
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
            },
            cellClose: function(e) {
                this.set("item.data", e.sender.dataSource.data().toJSON())
            }
        },
        model: {
            id: "id",
        },
        parse: function(response) {
            response.data.map(doc => {
                doc.createdAt = new Date(doc.createdAt * 1000);
            });
            console.log(response);
            return response;
        },
        columns: [{
            field: "name",
            title: "Name",
            headerAttributes: { style: "white-space: normal"},
            width: 250
        },{
            field: "description",
            title: "Description",
            headerAttributes: { style: "white-space: normal"},
            width: 500
        },{
            field: "active",
            title: "",
            template: "<input class='customClass' #if (active) { # checked='checked' # } # type='checkbox' />",
            width: 100
        },{
            field: "createBy",
            title: "Created by",
        },{
            field: "createdAt",
            title: "Created time",
            template: (data) => gridDate(data.createdAt),
        },{
            field: "updateBy",
            title: "Update by",
        },{
            field: "updateTime",
            title: "Update time",
        },{
            // Use uid to fix bug data-uid of row undefined
            template: '<a role="button" class="btn btn-sm btn-circle btn-action" data-uid="#: uid #"><i class="fa fa-ellipsis-v"></i></button>',
            width: 20
        }]
    };
</script>

<!-- Page content -->
<div id="page-content">
    <!-- Table Styles Header -->
    <ul class="breadcrumb breadcrumb-top">
        <li><a href="<?= base_url("admin") ?>">Admin</a></li>
        <li>Jsondata</li>
        <li class="pull-right none-breakcrumb">
<!--            <div class="input-group-btn">-->
<!--                <a role="button" class="btn btn-sm dropdown-toggle" data-toggle="dropdown"><b>Action</b> <span class="caret"></span></a>-->
<!--                <ul class="dropdown-menu">-->
<!--                    <li><a href="javascript:void(0)">Import</a></li>-->
<!--                    <li><a data-type="create" onclick="openForm({title: 'Add JSON Data', width: 1000}); addForm(this)" href="javascript:void(0)">Add</a></li>-->
<!--                    <li class="divider"></li>-->
<!--                    <li><a href="javascript:void(0)">Separated link</a></li>-->
<!--                </ul>-->
<!--            </div>-->
            <a data-toggle="tooltip" title="Trigger for CREATION" role="button" class="btn btn-sm" onclick="changeTriggerType('create')"><i class="fa fa-plus"></i> <b>@Create@</b></a>
            <a data-toggle="tooltip" title="Trigger for UPDATE" role="button" class="btn btn-sm" onclick="changeTriggerType('update')"><i class="fa fa-pencil"></i> <b>@Update@</b></a>
            <a data-toggle="tooltip" title="Trigger for TIME" role="button" class="btn btn-sm" onclick="changeTriggerType('time')"><i class="fa fa-clock-o"></i> <b>@Time@</b></a>
        </li>
    </ul>
    <!-- END Table Styles Header -->

    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">@Rules that run on@</div>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <h1 data-bind="text: currentTrigger"></h1> <h1>TRIGGERS</h1>
            </div>
            <div class="col-sm-6">
                <div class="pull-right">
                    <a data-toggle="tooltip" title="New rule" role="button" class="btn btn-sm" onclick="addForm()"><i class="fa fa-plus"></i> <b>@Create@</b></a>
                    <a data-toggle="tooltip" title="Reorder" role="button" class="btn btn-sm"><i class="fa fa-bars"></i> <b>@Reorder@</b></a>
                </div>
            </div>
            <div class="col-sm-12" style="height: 80vh; overflow-y: auto; padding: 0">
                <!-- Table Styles Content -->
                <div id="grid"></div>
                <!-- END Table Styles Content -->
            </div>
        </div>
    </div>

    <div id="action-menu">
        <ul>
            <a href="javascript:void(0)" onclick="openForm({title: 'Edit Trigger Data', width: 1000}); editForm(this)"><li><i class="fa fa-pencil-square-o text-warning"></i><span>Edit</span></li></a>
            <a href="javascript:void(0)" onclick="cloneDataItem(this)"><li><i class="fa fa-clipboard text-info"></i><span>Clone</span></li></a>
            <li class="devide"></li>
            <a href="javascript:void(0)" onclick="deleteDataItem(this)"><li><i class="fa fa-times-circle text-danger"></i><span>Delete</span></li></a>
        </ul>
    </div>
</div>
<!-- END Page Content -->
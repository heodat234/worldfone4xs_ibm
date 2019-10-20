<script>
var Config = {
    crudApi: `${ENV.restApi}`,
    templateApi: `${ENV.templateApi}`,
    collection: "role",
    observable: {
        setDefault: function(e) {
            var privileges = modules.map(function(value) {
                var ele = {module_id: value.value, view: true, create: true, update: true, delete: true, actions: value.actions};
                return ele;
            })
            this.set("item.privileges", privileges);
            this.getNavigator(privileges);
        },
        addPrivilege: function(e) {
            var privileges = this.get("item.privileges") ? this.get("item.privileges") : [];
            if(window.modules.length) {
                privileges.push({module_id: window.modules[0].value, view: true, create: true, update: true, delete: true, actions: []});
                this.set("item.privileges", privileges);
                console.log(privileges);
                this.getNavigator(privileges);
            }
        },
        privilegesSaveChange: function(e) {
            var privileges = []
            var dataItems = e.sender.dataSource.data().toJSON();
            for (var i = 0; i < dataItems.length; i++) {
                privileges.push(dataItems[i]);
            }
            this.set("item.privileges", privileges);
            this.getNavigator(privileges);
        },
        removePrivileges: function(e) {
            var privileges = []
            var dataItems = e.sender.dataSource.data().toJSON();
            for (var i = 0; i < dataItems.length; i++) {
                if(e.model.module_id != dataItems[i].module_id) {
                    privileges.push(dataItems[i]);
                }
            }
            this.getNavigator(privileges);
        },
        getNavigator: async function(privileges) {
            privileges.map(doc => {
                doc.create = doc.update = doc.delete = doc.action = undefined;
            });
            var navigatorHTML = await $.get(ENV.baseUrl + "template/nav/from_privileges", {q: JSON.stringify(privileges)});
            this.set("navigatorHTML", navigatorHTML);
            handleNavCheck();
        }
    },
    model: {
        id: "id",
        fields: {
            privileges: {type: "object"}
        }
    },
    columns: [{
            selectable: true,
            width: 32
        },{
            field: "name",
            title: "@Name@",
            width: 240
        },{
            field: "description",
            title: "@Description@",
        },{
            template: '<a role="button" class="btn btn-sm btn-circle btn-action"><i class="fa fa-ellipsis-v"></i></button>',
            width: 20
        }]
}; 
</script>

<!-- Page content -->
<div id="page-content">
    <!-- Table Styles Header -->
    <ul class="breadcrumb breadcrumb-top">
        <li>@User@</li>
        <li>@Role@</li>
        <li class="pull-right none-breakcrumb">
            <a role="button" class="btn btn-sm" onclick="openForm({title: `@Add@ @Role@`, width: 1000});  addForm(this);"><i class="fa fa-plus"></i> <b>@Add@</b></a>
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

    <div id="action-menu">
        <ul>
            <a href="javascript:void(0)" data-type="update" onclick="openForm({title: '@Edit@ @Role@', width: 1000}); editForm(this)"><li><i class="fa fa-pencil-square-o text-warning"></i><span>@Edit@</span></li></a>
            <a href="javascript:void(0)" data-type="create" onclick="cloneDataItem(this)"><li><i class="fa fa-clipboard text-info"></i><span>@Clone@</span></li></a>
            <a href="javascript:void(0)" data-type="delete" onclick="deleteDataItem(this)"><li><i class="fa fa-times-circle text-danger"></i><span>@Delete@</span></li></a>
            <li class="devide"></li>
        </ul>
    </div>
</div>
<!-- END Page Content -->
<style type="text/css">
    .user-avatar {
        width: 36px;
        height: 36px;
        border: 1px solid lightgray;
        margin-left: 2px;
        border-radius: 18px;
        background: rgba(255, 255, 255, 0.75);
        overflow: hidden;
    }

    .user-avatar > img {
        width: 36px;
        height: 36px;
        border-radius: 18px;
    }
</style>
<script>
function editRole(e) {
    e.preventDefault();
    var ele = e.currentTarget;
    var uid = $(ele).closest("tr").data("uid");
    ele.dataset.uid = uid;
    openForm({title: '@Edit@ @user role@', width: 1000});
    editForm(ele);
}

var Config = {
    crudApi: `${ENV.restApi}`,
    templateApi: `${ENV.templateApi}`,
    collection: "user",
    observable: {
        roleOption: () => dataSourceDropDownListPrivate("Role", ["name"], null, function(response) {
            response.data.push({name: "Default", id: null});
            return response;
        }),
        roleCascade: function(e) {
            this.set("item.role_name", e.sender.dataItem().name);
            this.asyncRoleCascade(e);
        },
        navigatorHTML: "",
        asyncRoleCascade: async function(e) {
            var id = (e.sender.value() != 'null') ? e.sender.value() : "",
                type = 0,
                parentName = "",
                parentModuleId = "",
                colors = HELPER.bsColors,
                access = await $.get(ENV.vApi + "permission/access_from_role_id/" + id, {lang: ENV.language}),
                accessData = [];
                access.map((ele, idx) => {
                    if(idx > 0 && ele.module_id != access[idx - 1].module_id) {
                        type++;
                    }
                    if(ele.uri == "parent") {
                        parentName = ele.name;
                        parentModuleId = ele.module_id;
                    } else {
                        ele.name = `<span class="label label-${colors[type % colors.length]}">${ele.name}</span>`;
                        if(ele.module_id == parentModuleId) ele.name += ` (${parentName})`;
                        accessData.push(ele);
                    } 
                });
            this.set("accessData", accessData);

            // Navigator view
            var navigatorHTML = await $.get(ENV.baseUrl + "template/nav/from_role_id/" + id);
            this.set("navigatorHTML", navigatorHTML);
            handleNavCheck();
        }
    },
    model: {
        id: "id",
        fields: {
        	sipuser: {type: "string"},
        	extension: {type: "string"},
        	agentname: {type: "string"},
        	issupervisor: {type: "boolean"},
        	isadmin: {type: "boolean"}
        }
    },
    columns: [{
            width: 60,
            headerTemplate: `<div class="text-center">@Image@</div>`,
            template: function(data) {
                return `<div class="user-avatar">
                            <img src="/api/v1/avatar/agent/${data.extension}" id="avatar-img" alt="avatar">
                        </div>`;
            }
        },{
            field: "extension",
            title: "@Extension@",
            width: 100
        },{
            field: "agentname",
            title: "@Agent name@",
            width: 160
        },{
            field: "isadmin",
            title: "@Admin@",
            width: 90,
            hidden: true,
            template: "#=gridBoolean(isadmin)#"
        },{
            field: "role_name",
            title: "@Role@",
            width: 150
        },{
            field: "lastpingtime",
            title: "@Last access@",
            template: data => gridTimestamp(data.lastpingtime, "dd/MM/yy H:mm:ss"),
            width: 150
        },{
            field: "fullname",
            title: "@Full name@"
        },{
            command: [{name: "edit", text: "@Edit@", click: editRole}],
            width: 120
        }]
}; 
</script>

<!-- Page content -->
<div id="page-content">
    <!-- Table Styles Header -->
    <ul class="breadcrumb breadcrumb-top">
        <li>@User@</li>
        <li>@Browse@</li>
        <li class="pull-right none-breakcrumb">
            <div class="input-group-btn column-widget">
                <a role="button" class="btn btn-sm dropdown-toggle" data-toggle="dropdown" onclick="editColumns(this)"><i class="fa fa-calculator"></i> <b>@Edit Columns@</b></a>
                <ul class="dropdown-menu dropdown-menu-right" style="width: 300px">
                    <li class="dropdown-header text-center">@Choose columns will show@</li>
                    <li class="filter-container" style="padding-bottom: 15px">
                        <div class="form-horizontal" data-bind="source: columns" data-template="column-template"/>
                    </li>
                </ul>
            </div>
        </li>
    </ul>
    <!-- END Table Styles Header -->

    <div class="container-fluid after-breadcrumb">
        <div class="row">
            <div class="col-sm-12" style="overflow-y: auto; padding: 0">
                <!-- Table Styles Content -->
                <div id="grid"></div>
                <!-- END Table Styles Content -->
            </div>
        </div>
    </div>

    <div id="action-menu">
    </div>
</div>
<!-- END Page Content -->
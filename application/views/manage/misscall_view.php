<script>
var Config = {
    crudApi: `${ENV.restApi}`,
    templateApi: `${ENV.templateApi}`,
    collection: "misscall",
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
    model: {
        id: "id",
        fields: {
            starttime: {type: "date"},
            billduration: {type: "number"},
            show_popup: {type: "boolean"}
        }
    },
    columns: [{
            selectable: true,
            width: 32,
            locked: true
        },{
            field: "starttime",
            title: "@Time@",
            width: 140,
            template: function(dataItem) {
                return (kendo.toString(dataItem.starttime, "dd/MM/yy H:mm:ss") ||  "").toString();
            }
        },{
            field: "userextension",
            title: "Queue/@Extension@",
            width: 160
        },{
            field: "customernumber",
            title: "@Phone@",
            template: function(dataItem) {
                return gridPhone(dataItem.customernumber);
            },
            width: 200
        },{
            field: "extension_available",
            title: "@Available Extensions@",
            template: (dataItem) => gridArray(dataItem.extension_available)
        },{
            field: "glide_extension",
            title: "@Glide Extensions@",
            template: (dataItem) => gridArray(dataItem.glide_extension)
        },{
            field: "assign",
            title: "@Assign@",
            width: 140
        }, {
            field: "assignBy",
            title: "@Assign by@",
            width: 140
        }, {
            field: "assignAt",
            title: "@Assign at@",
            width: 140,
            template: function(dataItem) {
                return dataItem.assignAt ? kendo.toString(new Date(dataItem.assignAt * 1000), "dd/MM/yy H:mm:ss") : "";
            }
        }]
}; 
function assignExtension() {
    var checkIds = Table.grid.selectedKeyNames();
    if(checkIds.length) {
        var buttons = {};
        for(var ext in convertExtensionToAgentname) {
            buttons[ext] = convertExtensionToAgentname[ext] + ` (${ext})`;
        }
        buttons.cancel = true;
        
        swal({
            title: "@Assign@",
            text: "@Assign these misscalls for extension@!",
            icon: "warning",
            buttons: buttons
        })
        .then((ext) => {
            if (ext !== null && ext !== false) {
                var date = new Date();
                var timestamp = date.getTime();
                checkIds.forEach(uid => {
                    var dataItem = Table.dataSource.getByUid(uid);
                    $.ajax({
                        url: Config.crudApi + "misscall/" + dataItem.id,
                        type: "PUT",
                        contentType: "application/json; charset=utf-8",
                        data: JSON.stringify({assign: ext, assignBy: '<?=@$this->session->userdata("extension")?>', assignAt: timestamp}),
                        success: syncDataSource,
                        error: errorDataSource
                    })
                })
                Table.dataSource.read();
            }
        });
    } else {
        swal({
            title: "@No row is checked@!",
            icon: "error"
        });
    }
}
</script>

<!-- Table Styles Header -->
<ul class="breadcrumb breadcrumb-top">
    <li>@Manage@</li>
    <li>@Misscall@</li>
    <li class="pull-right none-breakcrumb">
        <a role="button" class="btn btn-sm" onclick="assignExtension()"><i class="fa fa-anchor"></i> <b>@Assign@</b></a>
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
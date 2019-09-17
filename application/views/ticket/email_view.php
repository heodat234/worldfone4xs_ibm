<script>
var Config = {
    crudApi: `${ENV.restApi}`,
    templateApi: `${ENV.templateApi}`,
    collection: "agentsign",
    observable: {
    },
    model: {
        id: "id",
        fields: {
            signintime: {type: "date"},
            signouttime: {type: "date"},
            lastpingtime: {type: "date"},
            statuscode : {type: "number"}
        }
    },
    parse: function (response) {
        response.data.map(function(doc) {
            doc.signintime = new Date(doc.signintime * 1000);
            doc.signouttime = doc.signouttime ? new Date(doc.signouttime * 1000) : null;
            doc.lastpingtime = new Date(doc.lastpingtime * 1000);
            return doc;
        })
        return response;
    },
    columns: [{
            field: "signintime",
            title: "Sign in at",
            template: function(dataItem) {
                return (kendo.toString(dataItem.signintime, "dd/MM/yy H:mm:ss") ||  "").toString();
            },
            width: 140
        },{
        	field: "signouttime",
            title: "Sign out at",
            template: function(dataItem) {
                return (kendo.toString(dataItem.signouttime, "dd/MM/yy H:mm:ss") ||  "").toString();
            },
            width: 140
        },{
            field: "lastpingtime",
            title: "Last ping",
            template: function(dataItem) {
                return (kendo.toString(dataItem.lastpingtime, "dd/MM/yy H:mm:ss") ||  "").toString();
            },
            width: 140
        },{
            field: "extension",
            title: "Extension",
            width: 100
        },{
            field: "endnote",
            title: "End note"
        }]
}; 
</script>

<!-- Page content -->
<div id="page-content">
    <!-- Table Styles Header -->
    <ul class="breadcrumb breadcrumb-top">
        <li>Report</li>
        <li>Agent sign in - sign out</li>
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
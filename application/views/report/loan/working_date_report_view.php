<!-- Table Styles Header -->
<ul class="breadcrumb breadcrumb-top">
    <li>@Working Date Report@</li>

</ul>
<!-- END Table Styles Header -->

<div class="container-fluid">
    <div class="row filter-mvvm" style="display: none; margin: 10px 0">
    </div>
    <div class="row">
        <div class="col-sm-12" style="padding: 0">
            <!-- Table Styles Content -->
            <div id="grid"></div>
            <!-- END Table Styles Content -->
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $("#grid").kendoGrid({
        dataSource: {
            type: "json",
            transport: {
                read: "api/report/loan/working_date_report/read"
            },
            schema: {
                data: function(response) {
                    return response.data;
                }
            }
        },

        height: 550,
        sortable: true,
        pageable: {
            pageSizes: true,
        },
        columns: [{
            field: "filename",
            title: "@File Name@",
            width: 100
        }, {
            field: "file_path",
            title: "@Download@",
            template: `<button class="btn btn-alt btn-default" onclick=download_file('#: file_path #')>@Download@</button>`,
            width: 100
        }],
    });
})

function download_file(file_path) {
    var res = encodeURI(file_path);
    window.open(`api/report/loan/working_date_report/download?data=${res}`);
}
</script>
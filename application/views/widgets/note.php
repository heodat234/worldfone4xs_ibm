<li>
    <a href="javascript:void(0)" class="btn btn-alt btn-sm btn-default" data-toggle="tooltip" title="@Note@" data-placement="bottom" onclick="openForm({title: '@Note@', width: 300}); noteGlobal();">
        <i class="gi gi-pen"></i>
    </a>
</li>
<script type="text/javascript">	
async function noteGlobal() {
    var formHtml = await $.ajax({
        url: ENV.templateApi + "note/form",
        error: errorDataSource
    });
    kendo.destroy($("#right-form"));
    $("#right-form").empty();
    $("#right-form").append(formHtml);
}
</script>
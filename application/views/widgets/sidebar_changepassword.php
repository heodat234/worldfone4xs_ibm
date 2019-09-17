<a href="javascript:void(0)" onclick="openForm({title: '@Change password@', width: 300}); changePassword(this)" data-toggle="tooltip" data-placement="bottom" title="@Change password@"><i class="gi gi-user"></i></a>

<script type="text/javascript">
	async function changePassword() {
	    $rightForm = $("#right-form");
	    var formHtml = await $.ajax({
	        url: ENV.templateApi + "user/changepassword",
	        error: errorDataSource
	    });
	    kendo.destroy($rightForm);
	    $rightForm.empty();
	    $rightForm.append(formHtml);
	}
</script>
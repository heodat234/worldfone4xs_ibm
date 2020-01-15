<li>
    <a href="javascript:void(0)" class="btn btn-alt btn-sm btn-default" data-toggle="tooltip" title="@Quick chat@" data-placement="bottom" onclick="quickChat();">
        <i class="gi gi-conversation"></i>
    </a>
</li>

<script type="text/javascript">
	function quickChat(room_id = "") {
		openForm({title: '@Quick chat@', width: 500});
		let url = "/tool/chat?omc=1" + (room_id ? `#${room_id}` : ``),
			html = `
		<div class="container-fluid">
			<div class="row">
				<div class="col-xs-12" id="main-form" style="margin: 0; padding: 0; overflow-y: hidden; height: 97vh">
					<iframe src="${url}" style="width: 100%; min-height: 100%; border: 0; overflow-y: hidden"></iframe>
				</div>
			</div>
		</div>`;
	    $("#right-form").empty();
	    $("#right-form").append(html);
	}
</script>
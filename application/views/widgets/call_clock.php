<li>
	<a href="javascript:void(0)">
		<b id="call-clock" class="text-danger"></b>
    	<b id="clock" data-toggle="tooltip" data-placement="bottom"></b>
	</a>
</li>

<script type="text/javascript">
	function clockWidget(e) {
		var data = JSON.parse(e.data);
		var ele = document.getElementById("clock");
		if(ele && data.time) {
			ele.dataset.originalTitle = kendo.toString(new Date(data.time * 1000), "dd/MM/yy");
			ele.textContent = kendo.toString(new Date(data.time * 1000), "H:mm:ss");
		}
	}

</script>
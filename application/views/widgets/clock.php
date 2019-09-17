<li>
	<a href="javascript:void(0)">
    	<b id="clock" data-toggle="tooltip" data-placement="bottom">__:__:__</b>
	</a>
</li>

<script type="text/javascript">
	function clockWidget(e) {
		var data = JSON.parse(e.data);
		var ele = document.getElementById("clock");
		if(ele && data && data.time) {
			ele.dataset.originalTitle = kendo.toString(new Date(data.time * 1000), "dd/MM/yy");
			ele.textContent = kendo.toString(new Date(data.time * 1000), "H:mm:ss");
		}
	}

</script>
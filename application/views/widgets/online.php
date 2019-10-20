<li>
	<a href="javascript:void(0)">
    	<i class="fa fa-wifi" id="online-point" data-toggle="tooltip" data-placement="bottom"></i>
	</a>
</li>

<script type="text/javascript">
	var testPingTime = 0;
	var onlinePoint = document.getElementById("online-point");
	function onlineWidget(e) {
		var data = JSON.parse(e.data);
		var ele = document.getElementById("online-point");
		if(!ele) return;
		if(!data) {
			ele.dataset.originalTitle = "@Session expired@";
			ele.classList.remove("text-success", "text-danger");
			ele.classList.add("text-warning");
			
			if(testPingTime != -1) notification.show(NOTIFICATION.sessionExpire + "!", "warning");
			testPingTime = -1;
		} else if(data.time) {
			ele.dataset.originalTitle = "@Connected@";
			ele.classList.remove("text-danger", "text-warning");
			ele.classList.add("text-success");
			testPingTime = 0;
		} else {
			ele.dataset.originalTitle = "@Disconnected@";
			ele.classList.remove("text-success", "text-warning");
			ele.classList.add("text-danger");

			if(testPingTime != -1) notification.show(NOTIFICATION.disconnectServer + "!", "warning");
			testPingTime = -1;
		}
	}
	setInterval(() => {
		if(testPingTime >= 0)
		{
			testPingTime += 1;
			onlinePoint.style.filter = `grayscale(${testPingTime}%)`;
		} else onlinePoint.style.filter = `grayscale(0%)`;
	}, 100);
</script>
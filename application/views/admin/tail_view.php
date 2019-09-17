<style type="text/css">
	#loadarea {
		width: 100%;
		border: 0;
		min-height: 80vh;
	}
</style>
<ul class="breadcrumb breadcrumb-top">
    <li>@Admin@</li>
    <li>Tail</li>
</ul>
<div>
	<center style="padding: 10px 0">
		<label>File: </label>
		<input class="k-textbox" style="width: 300px" id="filepath">
		<label>Amount: </label>
		<input class="k-textbox" style="width: 100px" id="amount" value = "100">
		<div class="btn-group btn-group-sm">
        	<a type="button" class="k-button" onclick="tail();"><b>Start</b></a>
        	<a type="button" class="k-button" onclick="kill();"><b>Clear</b></a>
    	</div>
	</center>
	<iframe id="loadarea" scrolling="yes"></iframe>
</div>
<script type="text/javascript">
function tail() {
	var filepath = $("#filepath").val();
	var amount = $("#amount").val();
	var queryString = $.param({filepath: filepath, amount: amount});
    document.getElementById('loadarea').src = ENV.reportApi + 'server/tail?' + queryString;
    window.tailload = setInterval(function() {
	  var elem = document.getElementById('loadarea');
	  elem.contentWindow.scrollTo( 0, 999999 );
	}, 500);
}
function kill() {
    document.getElementById('loadarea').src = "";
    clearInterval(window.tailload);
}
</script>

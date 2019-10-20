<!-- Main Sidebar Toggle Button -->
<li>
    <a href="javascript:void(0)" data-toggle="tooltip" data-placement="bottom" title="@Collapse menu@" onclick="App.sidebar('toggle-sidebar');this.blur(); storeCollapseMenuStatus()">
        <i class="fa fa-bars fa-fw"></i>
    </a>
</li>
<!-- END Main Sidebar Toggle Button -->
<script type="text/javascript">
	function storeCollapseMenuStatus() {
		localStorage.setItem('collapseMenuStatus', $("#page-container").attr("class"));
	}
	if(localStorage.getItem('collapseMenuStatus') != null) {
		$("#page-container").removeClass().addClass(localStorage.getItem('collapseMenuStatus'))
	}
</script>
<li>
    <a href="javascript:void(0)" class="btn btn-alt btn-sm btn-default" data-toggle="tooltip" title="@Open saved popup@" data-placement="bottom" id="saved-popup-btn">
        <i class="hi hi-new_window"></i>
    </a>
</li>
<script type="text/javascript">
	var savedPopupBtn = document.getElementById("saved-popup-btn");
	savedPopupBtn.addEventListener("click", function(e){
	  var id = localStorage.getItem("saved_popup_id");
	  if(id) {
	  	rePopup(id);
	  	//$(e.target).removeClass("btn-warning");
	  	//localStorage.removeItem("saved_popup_id");
	  } else {
	  	swal({
	  		title: "@No popup was saved@.",
            text: `@You only can use this function after you save popup@.`,
            icon: "warning"
	  	})
	  }
	});
	if(localStorage.getItem("saved_popup_id")) {
		$(savedPopupBtn).addClass("btn-warning");
	}
</script>
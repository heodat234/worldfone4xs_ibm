var navPromise = $.ajax({
	url: ENV.baseUrl + "template/nav?currentUri=" + ENV.currentUri,
	dataType: "html"
})

var headerPromise = $.ajax({
	url: ENV.baseUrl + "template/widget/headerbar",
	dataType: "html"
})

var sidebarWidgetPromise = $.ajax({
	url: ENV.baseUrl + "template/widget/sidebar",
	dataType: "html"
})

Promise.all([navPromise, headerPromise, sidebarWidgetPromise]).then(values => {
	$("#sidebar-nav-contain").html(values[0]);
	$("#main-container header.navbar").html(values[1]);
	$("#sidebar-widget").html(values[2]);
	App.init();
})

var checkPermisssion = function() {
	if(!PERMISSION.isadmin) {
		["create","update","delete"].forEach(function(value){
			if(!PERMISSION[value]) $(`[data-type=${value}]`).remove();
		})
		// Check actions
		var btnActions = $("[data-type^=action]");
		if(btnActions.length) {
			for (var i = 0; i < btnActions.length; i++) {
				var dataType = btnActions[i].dataset.type,
					typeSplit = dataType.split("/");
				if(typeSplit.length == 2) {
					var typeAction = typeSplit[1];
					if(!PERMISSION.actions) {
						$(btnActions[i]).remove();
					} else if(PERMISSION.actions.indexOf(typeAction) === -1) {
						$(btnActions[i]).remove();
					} 
				}
			}
		}
	}
}()
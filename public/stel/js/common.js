if(ENV.avatar) {
	var $avatar = $("#avatar-img");
	var originalAvatar = $avatar.attr("src");
	$avatar.attr("src", ENV.avatar)
	    .on('error', function() { $(this).attr("src", originalAvatar) }) 
}

if(ENV.brandLogo) {
	var $favicon = $("link[rel='shortcut icon']");
	var originalFavicon = $favicon.attr("href");
	$favicon.attr("href", ENV.brandLogo);
}

$(document).on("keyup", ".upper-case-input", function(){
    var start = this.selectionStart;
    var end = this.selectionEnd;
    this.value = this.value.toUpperCase();
    this.setSelectionRange(start, end);
})

$(document).on("click", "#ext-col", function(){
	if($("#page-content").hasClass("open-col")) {
		// Close
		$("#page-content").removeClass("open-col");
		$(this).find("i").removeClass("fa-outdent").addClass("fa-indent");
	} else {
		// Open
		$("#page-content").addClass("open-col");
		$(this).find("i").removeClass("fa-indent").addClass("fa-outdent");
	}	
})

$(document).on("click", ".copy-item", function(e){
	copyToClipboard(e.target);
})

$(document).on("click", ".k-widget a[href='#']", function(e) {
    e.preventDefault();
})

$(document).on("click", "a[href^=mailto]", function(e) {
	e.preventDefault();
	var email = $(e.currentTarget).attr("href").replace("mailto:", "");
    openForm({title: "Email"});
    emailForm({doc: {email: email}});
})

$('.dropdown, .input-group-btn').on('hide.bs.dropdown', function (e) {
    var button = $(e.target).find("[data-toggle=dropdown]");
    if(button.hasClass("keepopen"))
        return false;
    else return true;
});

var notification = $("#notification").kendoNotification({
	autoHideAfter: 5000,
	stacking: "up",
	position: {
		left: 220,
		bottom: 20
	},
    show: function (e) {
        e.element.parent().css({
          zIndex: 10100
        });
    }
}).data("kendoNotification");

function closeForm() {
	$("#sidebar-alt").css("width", "");
	$('#page-container').removeClass('sidebar-alt-visible-lg')
}

function openForm(paramObject) {
	var options = Object.assign({
		title: "Form",
		width: 700,
		toggle: false
	}, paramObject);
	$("#sidebar-alt").css("width", options.width + 40).data("width", options.width + 40);
	$("#right-title").html(options.title);
	$("#right-form").html(HELPER.loaderHtml);
	$('#page-container').addClass('sidebar-alt-visible-lg');
	$("#sidebar-alt .toggle-form").css("display", options.toggle ? "absolute" : "none");
	document.onkeydown = function(evt) {
	    evt = evt || window.event;
	    if (evt.keyCode == 27) {
	        closeForm();
	    }
	};
}

$("#right-title").draggable({
	axis: "x",
	drag: function( event, ui ) {
		var changeX = ui.position.left,
			$sidebar = $("#sidebar-alt"),
			width = $sidebar.width();
		$sidebar.width(width - changeX);
		setTimeout(() => {
			$(event.target).css("left", 0);	
		}, 5);
	}
});

$("#bg-sidebar-alt").on("click", function(event) {
	if (!$(event.target).is(".sidebar-content")) {
		swal({
	        title: `${NOTIFICATION.checkSure}?`,
	        text: `${NOTIFICATION.closeThisForm}`,
	        icon: "warning",
	        buttons: true,
	        dangerMode: false,
	    })
	    .then((sure) => {
	        if (sure) {
	            closeForm();
	        }
	    });
    }
})

function toggleForm() {
	if($(".toggle-form").hasClass("expand-form")) {
		$(".toggle-form").removeClass("expand-form");
		$("#sidebar-alt").css("width", $("#sidebar-alt").data("width"));
		$("#side-form").show();
		$("#main-form").css("width", $("#main-form").data('width'));
	} else {
		$(".toggle-form").addClass("expand-form");
		$("#sidebar-alt").width($("#sidebar-alt").width() - $("#side-form").width());
		$("#side-form").hide();
		$("#main-form").css("width", '100%');
	}
}

function ajaxStart() {
	if(HELPER.loaderHtml) $("#bg-loader").html(HELPER.loaderHtml).show();
}

function ajaxComplete(event, xhr) {
	if(HELPER.loaderHtml) $("#bg-loader").html("").hide();
}

$( document ).ajaxStart(ajaxStart);

$( document ).ajaxComplete(ajaxComplete);

if(ENV) {
	$.ajaxSetup({
	    beforeSend: function (xhr)
	    {
	       xhr.setRequestHeader("currentUri", ENV.currentUri);   
	    }
	});
}


if(notificationAfterRefreshData = JSON.parse(sessionStorage.getItem("notificationAfterRefresh"))) {
	notification.show(notificationAfterRefreshData.content, notificationAfterRefreshData.type);
	sessionStorage.removeItem("notificationAfterRefresh");
}


if(ENV.sound_effect) {
	$(document).on("click", "a[role=button], a.btn, button", function(e) {playSound("btn-click");})
}

var ISO_8601_FULL = /^\d{4}-\d\d-\d\dT\d\d:\d\d:\d\d(\.\d+)?(([+-]\d\d:\d\d)|Z)?$/i

kendo.myClass = kendo.Class.extend({
    init: function(key, dataItem = {}) {
    	for(let prop in dataItem) {
    		if(ISO_8601_FULL.test(dataItem[prop])) {
				dataItem[prop] = new Date(dataItem[prop]);
			}
    	}
        this[key] = dataItem;
    }
});
<!-- Page content -->
<div id="page-content">
    <!-- Table Styles Header -->
    <ul class="breadcrumb breadcrumb-top">
        <li>Admin</li>
        <li>Configuration</li>
        <li class="pull-right none-breakcrumb">
        	<a role="button" href="javascript:void(0)" class="btn btn-sm" data-bind="click: clearLog"><b>Clear log</b></a>
            <a role="button" href="javascript:void(0)" class="btn btn-sm" data-bind="click: save"><b>Save</b></a>
        </li>
    </ul>
    <!-- END Table Styles Header -->
	<div class="container-fluid">
		<div class="row" style="margin: 0 10px">
			<h4 class="fieldset-legend" style="margin: 0 0 20px"><span style="font-weight: 500; line-height: 1">FOR SYSTEM</span></h4>
		</div>
		<div class="row" style="margin: 10px 30px 0 60px">
			<form class="form-horizontal">
				<div class="col-md-6">
				    <div class="form-group">
				        <label class="control-label col-sm-3">Version</label>
				        <div class="col-sm-3">
				            <input class="k-textbox" style="width: 100%" data-bind="value: item.wff_version">
				        </div>
				        <label class="control-label col-sm-3">Environment</label>
				        <div class="col-sm-3">
				        	<input data-role="dropdownlist"               
		                    data-bind="value: item.wff_env, source: envOption" style="width: 100%">
				        </div>
				    </div>
				    <div class="form-group">
				        <label class="control-label col-sm-3">Unique login</label>
				        <div class="col-sm-3">
				        	<label class="switch switch-primary">
						        <input type="checkbox" data-bind="checked: item.wff_unique_login"><span></span>
						    </label>
				        </div>
				        <label class="control-label col-sm-3">Redirect auth</label>
				        <div class="col-sm-3">
				            <label class="switch switch-primary">
						        <input type="checkbox" data-bind="checked: item.wff_auth_redirect"><span></span>
						    </label>
				        </div>
				    </div>
				    <div class="form-group">
				        <label class="control-label col-sm-3">Record event</label>
				        <div class="col-sm-3">
				        	<label class="switch switch-primary">
						        <input type="checkbox" data-bind="checked: item.record_event"><span></span>
						    </label>
				        </div>
				    </div>
				</div>
				<div class="col-md-6">
				    <div class="form-group">
				        <label class="control-label col-sm-3">Time cache</label>
				        <div class="col-sm-3">
				            <input data-role="numerictextbox" style="width: 100%" data-bind="value: item.wff_time_cache">
				        </div>
				        <label class="control-label col-sm-3">Use worker</label>
				        <div class="col-sm-3">
				        	<label class="switch switch-primary">
						        <input type="checkbox" data-bind="checked: item.use_worker"><span></span>
						    </label>
				        </div>
				    </div>
				    <div class="form-group">
				        <label class="control-label col-sm-3">Loader layer</label>
				        <div class="col-sm-3">
				        	<label class="switch switch-primary">
						        <input type="checkbox" data-bind="checked: item.loader_layer"><span></span>
						    </label>
				        </div>
				        <label class="control-label col-sm-3">Record activity</label>
				        <div class="col-sm-3">
				        	<label class="switch switch-primary">
						        <input type="checkbox" data-bind="checked: item.record_activity"><span></span>
						    </label>
				        </div>
				    </div>
				</div>
			</form>
		</div>
		<div class="row hidden" style="margin: 0 10px">
			<h4 class="fieldset-legend" style="margin: 0 0 20px"><span style="font-weight: 500; line-height: 1">FOR DISPLAY DATA</span></h4>
		</div>
		<div class="row hidden" style="margin: 10px 30px 0 60px">
			<form class="form-horizontal">
				<div class="col-md-6">
				    <div class="form-group">
				        <label class="control-label col-sm-3">Show customer</label>
				        <div class="col-sm-9">
				        	<input data-role="dropdownlist"
							data-text-field="text"
							data-value-field="value"
		                    data-value-primitive="true"                 
		                    data-bind="value: item.show_customer, source: showCustomerOption" style="width: 100%">
				        </div>
				    </div>
				</div>
				<div class="col-md-6">
				    <div class="form-group">
				        <label class="control-label col-sm-3">Show CDR</label>
				        <div class="col-sm-9">
				        	<input data-role="dropdownlist"
							data-text-field="text"
							data-value-field="value"
		                    data-value-primitive="true"                 
		                    data-bind="value: item.show_cdr, source: showCDROption" style="width: 100%">
				        </div>
				    </div>
				</div>
			</form>
		</div>
		<div class="row" style="margin: 0 10px">
			<h4 class="fieldset-legend" style="margin: 0 0 20px"><span style="font-weight: 500; line-height: 1">BRAND</span></h4>
		</div>
		<div class="row" style="margin: 10px 30px 0 60px">
			<form class="form-horizontal">
				<div class="col-md-6">
				    <div class="form-group">
				        <label class="control-label col-sm-3">Title</label>
				        <div class="col-sm-9">
				        	<input class="k-textbox" style="width: 100%" data-bind="value: item.brand_title, events: {change: titleChange}">
				        </div>
				    </div>
				    
				</div>
				<div class="col-md-6">
					<div class="form-group">
				        <label class="control-label col-sm-3">Avatar <br><a href="javascript:void(0)" data-bind="click: defaultLogo"><small>Default</small></a></label>
				        <div class="col-sm-3" style="padding-top: 5px">
				        	<img src="<?= STEL_PATH ?>img/logo-stel.png" data-bind="invisible: item.brand_logo, click: uploadBrandLogo" class="preview-avatar">
				        	<img data-bind="attr: {src: item.brand_logo}, visible: item.brand_logo, click: uploadBrandLogo" class="preview-avatar">
				        	<div class="hidden" style="padding-top: 5px">
					        	<input name="file" type="file" id="upload-logo"
			                   data-role="upload"
			                   data-multiple="false"
			                   data-async="{ saveUrl: '/api/v1/upload/avatar/logo', autoUpload: true }"
			                   data-bind="events: { success: uploadSuccessLogo }">
					        </div>
				        </div>
				        <div class="col-sm-6">
				        	<div style="width: 215px" class="label-primary">
					        	<a href="javascript:void(0)" class="sidebar-brand" style="padding: 0 10px 0 5px;">
			                        <img data-bind="attr: {src: item.brand_logo}" alt="icon" style="vertical-align: -4px" width="30" height="30">
			                        <span class="sidebar-nav-mini-hide" id="brand-title-view" style="font-size: 30px" data-bind="text: item.brand_title">Worldfone</span>
			                    </a>
					        </div>
				        </div>
				    </div>
				</div>
			</form>
		</div>
		<div class="row" style="margin: 0 10px">
			<h4 class="fieldset-legend" style="margin: 0 0 20px"><span style="font-weight: 500; line-height: 1">PHONE CONFIG</span></h4>
		</div>
		<div class="row" style="margin: 10px 30px 0 60px">
			<form class="form-horizontal">
				<div class="col-md-6">
				    <div class="form-group">
				        <label class="control-label col-sm-3">Phone</label>
				        <div class="col-sm-9">
				        	<input data-role="dropdownlist"
							data-text-field="text"
							data-value-field="value"
		                    data-value-primitive="true"                 
		                    data-bind="value: item.phone_type, source: phoneTypeOption, events: {cascade: changePhoneType}" style="width: 100%">
				        </div>
				    </div>
				    <div class="form-group">
				        <label class="control-label col-sm-3">IP SIP Server</label>
				        <div class="col-sm-9">
				        	<input class="k-textbox" data-bind="value: item.ip_sip_server" style="width: 100%"/>
				        </div>
				    </div>
				</div>
				<div class="col-md-6" data-bind="visible: visibleIPPhoneConfig">
					<div class="form-group">
						<label class="control-label col-sm-3"></label>
				        <div class="col-sm-9">
				        	<label class="switch switch-primary">
						        <input type="checkbox" data-bind="checked: item.login_logout_ipphone"><span></span>
						    </label>
						    <b>Auto login-logout IP Phone</b>
				        </div>
					</div>
					<div class="form-group">
						<label class="control-label col-sm-3"></label>
				        <div class="col-sm-9">
				        	<label class="switch switch-primary">
						        <input type="checkbox" data-bind="checked: item.short_key_ipphone"><span></span>
						    </label>
						    <b>Short key IP phone</b>
				        </div>
					</div>
				</div>
			</form>
		</div>
		<div class="row" style="margin: 0 10px">
			<h4 class="fieldset-legend" style="margin: 0 0 20px"><span style="font-weight: 500; line-height: 1">PAGE LOGIN</span></h4>
		</div>
		<div class="row" style="margin: 10px 30px 0 60px">
			<form class="form-horizontal">
				<div class="col-md-6">
					<div class="form-group">
				        <label class="control-label col-sm-3">@Brand image@ <br><a href="javascript:void(0)" data-bind="click: defaultBrandImage"><small>Default</small></a></label>
				        <div class="col-sm-9">
				        	<img class="img-thumbnail" data-bind="attr: {src: item.login_brand_img}, click: uploadBrandImage, visible: item.login_brand_img" alt="@Brand image@" style="max-height: 40px; cursor: pointer;">
				        	<img class="img-thumbnail" src="<?= STEL_PATH. 'img/logo-viewbill.png' ?>" data-bind="invisible: item.login_brand_img, click: uploadBrandImage" alt="@Brand image@" style="max-height: 40px; cursor: pointer;">
				        </div>
				        <div class="hidden">
				        	<input name="file" type="file" id="upload-brand-image"
		                   data-role="upload"
		                   data-multiple="false"
		                   data-async="{ saveUrl: '/api/v1/upload/avatar/brandimage', autoUpload: true }"
		                   data-bind="events: { success: uploadSuccessBrandImage }">
				        </div>
				    </div>
				    <div class="form-group">
				        <label class="control-label col-sm-3">@Background type@ <br><a href="javascript:void(0)" data-bind="click: defaultBackground"><small>Default</small></a></label>
				        <div class="col-sm-3">
				        	<div class="onoffswitch" id="background-switch-container">
							    <input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" id="background-switch" data-bind="checked: item.login_background_img, events: {change: loginBackgroundChange}">
							    <label class="onoffswitch-label" for="background-switch">
							        <span class="onoffswitch-inner"></span>
							        <span class="onoffswitch-switch"></span>
							    </label>
							</div>
				        </div>
				    </div>
				    <div class="form-group" data-bind="invisible: item.login_background_img">
				        <label class="control-label col-sm-3">@Color@</label>
				        <div class="col-sm-9">
				        	<input data-role="colorpicker"
                  			 data-bind="value: item.login_background_color, events: {change: changeBgColor}">
				        </div>
				    </div>
				    <div class="form-group" data-bind="visible: item.login_background_img">
				        <label class="control-label col-sm-3">@Background image@</label>
				        <div class="col-sm-9">
				        	<img class="img-thumbnail" data-bind="attr: {src: item.login_background_img_url}, click: uploadBackgroundImage" alt="@Background image@" style="max-height: 70px; cursor: pointer;">
				        </div>
				        <div class="hidden">
				        	<input name="file" type="file" id="upload-background-image"
		                   data-role="upload"
		                   data-multiple="false"
		                   data-async="{ saveUrl: '/api/v1/upload/avatar/background', autoUpload: true }"
		                   data-bind="events: { success: uploadSuccessBackground }">
				        </div>
				    </div>
				</div>
				<div class="col-md-6">
					<div style="width: 400px; height: 400px; padding: 0; overflow: hidden; margin-top: -20px">
						<iframe id="frame-login-view"></iframe>
					</div>
				</div>
			</form>
		</div>
	</div>
	<style>
		.preview-avatar {
			height: 35px;
			border: 2px solid lightgray;
			cursor: pointer;
		}

		#background-switch-container {width: 100px;}
	    #background-switch-container .onoffswitch-inner:before {content: "@Image@";}
	    #background-switch-container .onoffswitch-inner:after {content: "@Color@";}

	    #frame-login-view { width: 1200px; height: 650px; border: 0; }
	    #frame-login-view {
	        -ms-zoom: 0.3;
	        -moz-transform: scale(0.3);
	        -moz-transform-origin: 0 0;
	        -o-transform: scale(0.3);
	        -o-transform-origin: 0 0;
	        -webkit-transform: scale(0.3);
	        -webkit-transform-origin: 0 0;
	    }
	</style>
	<script type="text/javascript">
		window.onload = async function() {
			var frameElement = document.getElementById("frame-login-view");
			var item = await $.get(ENV.vApi + "config/detail");
			frameElement.src = "/page/signin_view?" + httpBuildQuery({
				bg_image: item.login_background_img ? item.login_background_img_url : "",
				bg_color: !item.login_background_img ? item.login_background_color.replace("#", "") : "",
				brand_img: item.login_brand_img
			});
			kendo.bind($("#page-content"), kendo.observable({
				item: item,
				envOption: ["DEV","UAT","LIVE"],
				showCustomerOption: [{text: "All", value: "ALL"}, {text: "Only created by agent", value: "ONLY"}],
				showCDROption: [{text: "All", value: "ALL"}, {text: "Only created by agent", value: "ONLY"}],
				phoneTypeOption: [
					{text: "Other", value: ""},
					{text: "Zoiper", value: "zoiper"},
					{text: "XLite", value: "xlite"},
					{text: "MicroSIP", value: "microsip"},
					{text: "IPPhone", value: "ipphone"}
				],
				changePhoneType: function(e) {
					if(e.sender.value() == "ipphone") {
						this.set("visibleIPPhoneConfig", true);
					} else this.set("visibleIPPhoneConfig", false);
				},
				defaultLogo: function(e) {
					this.set("item.brand_logo", "");
				},
				uploadBrandLogo: function(e) {
					$("#upload-logo").click();
				},
				uploadSuccessLogo: function(e) {
					notification.show(e.response.message, e.response.status ? "success" : "error");
      				e.sender.clearAllFiles();
      				if(e.response.filepath) {
      					this.set("item.brand_logo", e.response.filepath);
      				}
				},
				titleChange: function(e) {
					var $brandTitle = $("#brand-title-view");
					var title = e.currentTarget.value;
					if(title.length > 9 && title.length < 18) $brandTitle.css("font-size", 20);
                	else if(title.length > 18) $brandTitle.css("font-size", 13);
                	else $brandTitle.css("font-size", 30);
				},
				loginBackgroundChange: function(e) {
					var params = getUrlParams(frameElement.src);
					if(e.currentTarget.checked) {
						delete params.bg_color;
						params.bg_image = this.get("item.login_background_img_url");
						frameElement.src = "/page/signin_view?" + httpBuildQuery(params);
					} else {
						delete params.bg_image;
						var bg_color = this.get("item.login_background_color");
						params.bg_color = bg_color ? bg_color.replace("#", "") : "";
						frameElement.src = "/page/signin_view?" + httpBuildQuery(params);
					}
				},
				defaultBrandImage: function(e) {
					this.set("item.login_brand_img", "");
					var params = getUrlParams(frameElement.src);
					delete params.brand_img;
					frameElement.src = "/page/signin_view?" + httpBuildQuery(params);
				},
				defaultBackground: function(e) {
					this.set("item.login_background_img", false);
					this.set("item.login_background_color", null);
					var params = getUrlParams(frameElement.src);
					delete params.bg_color; delete params.bg_image;
					frameElement.src = "/page/signin_view?" + httpBuildQuery(params);
				},
				changeBgColor: function(e) {
					var params = getUrlParams(frameElement.src);
					params.bg_color = e.value.replace("#", "");
					frameElement.src = "/page/signin_view?" + httpBuildQuery(params);
				},
				uploadBackgroundImage: function() {
					$("#upload-background-image").click();
				},
				uploadSuccessBackground: function(e) {
					notification.show(e.response.message, e.response.status ? "success" : "error");
      				if(e.response.filepath) {
      					this.set("item.login_background_img_url", e.response.filepath);
      					var params = getUrlParams(frameElement.src);
      					params.bg_image = e.response.filepath;
      					frameElement.src = "/page/signin_view?" + httpBuildQuery(params);
      				}
				},
				uploadBrandImage: function(e) {
					$("#upload-brand-image").click();
				},
				uploadSuccessBrandImage: function(e) {
					notification.show(e.response.message, e.response.status ? "success" : "error");
      				if(e.response.filepath) {
      					this.set("item.login_brand_img", e.response.filepath);
      					var params = getUrlParams(frameElement.src);
      					params.brand_img = e.response.filepath
      					frameElement.src = "/page/signin_view?" + httpBuildQuery(params);
      				}
				},
				save: function() {
					var data = this.item.toJSON();
					$.ajax({
						url: ENV.vApi + "config/update",
						type: "POST",
                    	contentType: "application/json; charset=utf-8",
						data: kendo.stringify(data),
						success: function() {
							syncDataSource();
							location.reload();
						},
						error: errorDataSource
					})
				},
				clearLog: function() {
					$.ajax({
						url: ENV.vApi + "config/clear_logs",
						success: function(response) {
							if(response.status) {
								notification.show(`Clear ${response.count} file log`, "success");
							} else notification.show(`Cannot clear file log`, "error");
						},
						error: errorDataSource
					})
				}
			}));
		}
	</script>
</div>
<!-- END Page Content -->
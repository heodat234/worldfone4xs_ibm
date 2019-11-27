<style>
    .badge.badge-pill:hover {
        background-color: #1bbae1;
    }

    #top-row .list-group-item {
        display: inline-block;
        padding: 1px 5px;
    }
</style>
<script>
    var Config = {
        crudApi: `${ENV.restApi}`,
        templateApi: `${ENV.templateApi}`,
    }
</script>
<div id="page-content">
	<script id="layout" type="text/x-kendo-template">
	    <ul class="breadcrumb breadcrumb-top">
	        <li>@Manage@</li>
	        <li>@Diallist@</li>
	        <li data-bind="text: breadcrumb"></li>
            <li class="pull-right none-breakcrumb" id="top-row">
            	<div class="btn-group btn-group-sm">
                    <button href="#/" class="btn btn-alt btn-default" data-bind="click: goTo, css: {active: activeArray[0], disabled: activeArray[0]}, visible: hasDetail">@Overview@</button>
                    <button class="btn btn-alt btn-default" data-bind="css: {active: activeArray[1]}, visible: hasDetail" data-toggle="dropdown" id="btn-detail">Detail (<span data-bind="text: diallistList.length"></span>) <span class="caret"></span></button>
                    <ul class="dropdown-menu dropdown-custom dropdown-options" data-bind="source: diallistList" data-template="detail-dropdown-template">
				    </ul>
                </div>
            </li>
	    </ul>
		<div class="container-fluid">
	        <div class="row" id="bottom-row"></div>
	    </div>
	</script>
	<script id="detail-dropdown-template" type="text/x-kendo-template">
		<li data-bind="css: {dropdown-header: active}"><a data-bind="click: goTo, text: name, attr: {href: url}"></a></li>
	</script>
	<script id="column-template" type="text/x-kendo-template">
	    <div class="form-group">
			<label class="control-label col-sm-4"><span data-bind="text: field"></span></label>
			<div class="col-sm-7">
				<input class="k-textbox" style="width: 100%" data-bind="value: title">
			</div>
		</div>
	</script>
	<script type="text/x-kendo-template" id="diallist-detail-field-template">
		<div class="item">
            <span style="margin-left: 10px" data-bind="text: title"></span>
            <i class="fa fa-arrow-circle-o-right text-success" style="float: right; margin-top: 10px"></i>
        </div>
	</script>
	<script type="text/x-kendo-template" id="data-field-template">
		<div class="item">
			<span class="handler text-center"><i class="fa fa-arrows-v"></i></span>
            <span data-bind="text: field"></span>
        </div>
	</script>	
</div>

<script type="text/javascript">
window.onload = function() {
	var layoutViewModel = window.layoutViewModel = kendo.observable({
		breadcrumb: "",
		activeArray: [],
		buttonSelect: "#top-row .btn-group",
		init: function() {
			var hash = (window.location.hash || "#/").toString(),
				$currentTarget = $(this.buttonSelect).find(`button[href='${hash}']`),
				index = $(this.buttonSelect).find("button").index($currentTarget);
			this.set("activeArray", new Array($(this.buttonSelect).find("button").length));
			this.set("breadcrumb", $currentTarget.text());
			this.setActive(index);
		},
		goTo: function(e) {
			var $currentTarget = $(e.currentTarget);
			var index = $(this.buttonSelect).find("button").index($currentTarget);
			var nav = $currentTarget.attr("href");
			if(nav) {
				router.navigate(nav);

				this.set("breadcrumb", $currentTarget.text());
				if(index > -1) this.setActive(index);
			}
		},
		setActive: function(index) {
			for (var i = 0; i < this.activeArray.length; i++) {
				if(i == index)
					this.set(`activeArray[${i}]`, true);
				else this.set(`activeArray[${i}]`, false);
			}
		},
		hasDetail: false,
		diallistList: [],
		addDiallistList: function(diallist) {
			var link = ENV.currentUri + '/#/detail/' + diallist.id;
			var check = this.diallistList.find(obj => obj.id == diallist.id);
			if(!check) {
				this.diallistList.push({id: diallist.id, url: link, name: diallist.name, active: true})
			}
			for (var i = 0; i < this.diallistList.length; i++) {
				this.set(`diallistList[${i}].active`, (this.diallistList[i].id == diallist.id) ? true : false);
			}
			this.set("hasDetail", true);
		}
	})

	// views, layouts
	var layout = new kendo.Layout(`layout`, {model: layoutViewModel, wrap: false , init: layoutViewModel.init.bind(layoutViewModel)});

	// routing
	var router = window.router = new kendo.Router({routeMissing: function(e) { router.navigate("/") }});

	router.bind("init", function() {
	    layout.render($("#page-content"));
	});

	router.route("/", async function() {
		var HTML = await $.get(`${Config.templateApi}my_diallist/overview`);
		var kendoView = new kendo.View(HTML, { model: {}, template: false, wrap: false });
	    layout.showIn("#bottom-row", kendoView);
	});

	router.route("/detail/:id", async function(id) {
		layoutViewModel.setActive(1);
		var dataItemFull = await $.get(`${ENV.restApi}my_diallist/${id}`);
		if(!dataItemFull) {
			notification.show("Can't find diallist", "error");
			return;
		}
		layoutViewModel.addDiallistList(dataItemFull);
		layoutViewModel.set("breadcrumb", `${dataItemFull.name}`);
		var HTML = await $.get(`${Config.templateApi}my_diallist/detail?id=${id}`);
		var model = {
		}
		var kendoView = new kendo.View(HTML, { model: model, template: false, wrap: false });
	    layout.showIn("#bottom-row", kendoView);
	});

	router.start();

	document.onkeydown = function(evt) {
	    evt = evt || window.event;
	    if (evt.keyCode == 27) {
	    	router.navigate(`/`);
	    	layoutViewModel.init();
	    }
	};

	function gridPhoneDiallist(data, id) {
	    var html = "<span></span>";
	    var type = "dialmode_1";
	    if(data) {
	        if(typeof data == "string") {
	            html = `<a href="javascript:void(0)" class="label label-info" onclick="makeCall('${data}', '${id}', '${type}')" title="Call now" data-role="tooltip" data-position="top">${data}</a>`;
	        } else {
	            if(data.length) {
	                template = $.map($.makeArray(data), function(value, index) {
	                    return `<a href="javascript:void(0)" class="label label-default" data-index="${index}" onclick="makeCall('${value}', '${id}')" title="Call now" data-role="tooltip" data-position="top">${value}</a>`;
	                });;
	                html = template.join(' ');
	            }
	        }
	    }
	    return html;
	}

	function gridCallResult(data) {
		var htmlArr = [];
	    if(data) {
	    	data.forEach(doc => {
	    		htmlArr.push(`<a href="javascript:void(0)" class="label label-${(doc.disposition == "ANSWERED")?'success':'warning'}" 
	    			title="${kendo.toString(new Date(doc.starttime * 1000), "dd/MM/yy H:mm:ss")}">${doc.disposition}</a>`);
	    	})
	    }
	    return htmlArr.join("<br>");
	}
}
</script>
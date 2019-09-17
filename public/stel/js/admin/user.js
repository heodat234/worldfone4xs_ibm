var Table = {
    dataSource: {},
    grid: {},
    columns: Config.columns,
    init: function() {
        var dataSource = this.dataSource = new kendo.data.DataSource({
            serverFiltering: true,
            serverPaging: true,
            serverSorting: true,
            serverGrouping: false,
            pageSize: 10,
            batch: false,
            schema: {
                data: "data",
                total: "total",
                groups: "groups",
                model: Config.model,
            },
            transport: {
                read: {
                    url: Config.crudApi + Config.collection,
                },
                update: {
                    url: function(data) {
                        return Config.crudApi + Config.collection + "/" + data.id;
                    },
                    type: "PUT",
                    contentType: "application/json; charset=utf-8"
                },
                create: {
                    url: Config.crudApi + Config.collection,
                    type: "POST",
                    contentType: "application/json; charset=utf-8"
                },
                destroy: {
                    url: function(data) {
                        return Config.crudApi + Config.collection + "/" + data.id;
                    },
                    type: "DELETE"
                },
                parameterMap: parameterMap
            },
            sync: syncDataSource,
            error: errorDataSource
        });

        var grid = this.grid = $("#grid").kendoGrid({
        	editable: false,
            dataSource: dataSource,
            resizable: true,
            pageable: {
                refresh: true
            },
            sortable: true,
            scrollable: false,
            columns: this.columns,
            filterable: false,
        }).data("kendoGrid");

        grid.selectedKeyNames = function() {
            var items = this.select(),
                that = this,
                checkedIds = [];
            $.each(items, function(){
                if(that.dataItem(this))
                    checkedIds.push(that.dataItem(this).uid);
            })
            return checkedIds;
        }

        /*
         * Right Click Menu
         */
        var menu = $("#action-menu");

        $("html").on("click", function() {menu.hide()});

        $(document).on("click", "#grid tr[role=row] a.btn-action", function(e){
            // Fix bug data-uid of row undefined
            let row = $(e.target);
            e.pageX -= 20;
            showMenu(e, row);
        });

        function showMenu(e, that) {
            //hide menu if already shown
            menu.hide(); 
            //Get id value of document
            var uid = $(that).data('uid');
            if(uid)
            {
                menu.find("a[data-type=convert], a[data-type=update], a[data-type=delete], a[data-type=duplicate]").data('uid',uid);

                //get x and y values of the click event
                var pageX = e.pageX;
                var pageY = e.pageY;

                //position menu div near mouse cliked area
                menu.css({top: pageY , left: pageX});

                var mwidth = menu.width();
                var mheight = menu.height();
                var screenWidth = $(window).width();
                var screenHeight = $(window).height();

                //if window is scrolled
                var scrTop = $(window).scrollTop();

                //if the menu is close to right edge of the window
                if(pageX+mwidth > screenWidth){
                menu.css({left:pageX-mwidth});
                }

                //if the menu is close to bottom edge of the window
                if(pageY+mheight > screenHeight+scrTop){
                menu.css({top:pageY-mheight});
                }

                //finally show the menu
                menu.show();     
            }
        }
    }
};

Table.init();

async function editForm(ele) {
	var dataItem = Table.dataSource.getByUid($(ele).data("uid")),
        dataItemFull = await $.ajax({
            url: `${Config.crudApi+Config.collection}/${dataItem.id}`,
            error: errorDataSource
        }),
	    formHtml = await $.ajax({
    	    url: Config.templateApi + Config.collection + "/form",
    	    error: errorDataSource
    	});
	var model = Object.assign({
		item: dataItemFull,
		save: function() {
            $.ajax({
                url: `${Config.crudApi+Config.collection}/${dataItem.id}`,
                data: JSON.stringify(this.item.toJSON()),
                error: errorDataSource,
                type: "PUT",
                contentType: "application/json; charset=utf-8",
                success: function() {
                    Table.dataSource.read()
                }
            })
		}
	}, Config.observable);
	kendo.destroy($("#right-form"));
	$("#right-form").empty();
	var kendoView = new kendo.View(formHtml, { wrap: false, model: model, evalTemplate: false });
	kendoView.render($("#right-form"));
}

var handleNavCheck = function() {
    // Animation Speed, change the values for different results
    var page        = $('#page-container');
    var upSpeed     = 250;
    var downSpeed   = 250;

    // Get all vital links
    var menuLinks       = $('.check-sidebar .sidebar-nav-menu');
    var submenuLinks    = $('.check-sidebar .sidebar-nav-submenu');

    // Primary Accordion functionality
    menuLinks.click(function(){
        var link = $(this);

        if (page.hasClass('sidebar-mini') && page.hasClass('sidebar-visible-lg-mini') && (getWindowWidth() > 991)) {
            if (link.hasClass('open')) {
                link.removeClass('open');
            }
            else {
                $('.sidebar-nav-menu.open').removeClass('open');
                link.addClass('open');
            }
        }
        else if (!link.parent().hasClass('active')) {
            if (link.hasClass('open')) {
                link.removeClass('open').next().slideUp(upSpeed);
            }
            else {
                $('.sidebar-nav-menu.open').removeClass('open').next().slideUp(upSpeed);
                link.addClass('open').next().slideDown(downSpeed);
            }
        }

        link.blur();

        return false;
    });
};
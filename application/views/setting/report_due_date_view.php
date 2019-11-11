<script>
var Config = {
    crudApi: `${ENV.restApi}`,
    templateApi: `${ENV.templateApi}`,
    collection: "report_due_date",
    observable: {
    },
    model: {
        id: "id",
        fields: {
        	createdAt: {type: "date", editable: false},
        	due_date: {type: "date", editable: true}
        }
    },
    parse: function(res) {
    	res.data.map(doc => {
    		doc.createdAt = new Date(doc.createdAt * 1000);
    		doc.off_date = new Date(doc.off_date * 1000);
    	})
    	return res;
    },
    filterable: KENDO.filterable,
    columns: [{
            field: "due_date",
            title: "@Due date@",
            format: "{0: dd/MM/yyyy}",
            editor: function(container, options) {
                var dateString = kendo.toString(options.model.date, "dd/MM/yyyy" );
                $('<input id="off-date" data-text-field="' + options.field + '" data-value-field="' + options.field + '" data-bind="value:' + options.field + '" data-format="' + options.format + '"/>')
                    .appendTo(container)
                    .kendoDatePicker({});
            },
        },{
            field: "debt_group",
            title: "@Debt group@",
            editor: function(container, options) {
                $('<input id="off-date" data-text-field="' + options.field + '" data-value-field="' + options.field + '" data-bind="value:' + options.field + '" data-format="' + options.format + '"/>')
                    .appendTo(container)
                    .kendoDropDownList({
                        autoBind: false,
                        filter: "contains",
                        dataTextField: "group",
                        dataValueField: "id",
                        template: '<span class="k-state-default"></span><span class="k-state-default"><span style="text-transform: uppercase">#: data.type #</span> <span >#: data.group #</span ></span>',
                        valueTemplate: '<span class="k-state-default"></span><span class="k-state-default"><span style="text-transform: uppercase">#: data.type #</span> <span >#: data.group #</span ></span>',
                        dataSource: dataSourceDropDownList('Debt_group', [], null, res => res, 1000),
                        optionLabel: "@Choose@ @debt group@"
                    });
            },
        },{
            field: "createdBy",
            title: "@Created by@",
            width: 100,
            editor: (data) => readOnly,
        },{
            field: "createdAt",
            title: "@Created at@",
            format: "{0: dd/MM/yy HH:mm}",
            editor: (data) => readOnly
        },{
            title: `@Action@`,
            command: [{name: "edit", text: "@Edit@"}, {name: "destroy", text: "@Delete@"}],
            width: 200
        }]
}; 
</script>

<!-- Table Styles Header -->
<ul class="breadcrumb breadcrumb-top">
    <li>@Setting@</li>
    <li>@Report@ @off system date@</li>
    <li class="pull-right none-breakcrumb" id="top-row">
    	<div class="btn-group btn-group-sm">
            <button class="btn btn-alt btn-default" onclick="addForm(this)">@Create@</button>
        </div>
    </li>
</ul>
<!-- END Table Styles Header -->

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12" style="height: 80vh; overflow-y: auto; padding: 0">
            <!-- Table Styles Content -->
            <div id="grid"></div>
            <!-- END Table Styles Content -->
        </div>
    </div>
</div>

<script type="text/javascript">
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
                parse: Config.parse ? Config.parse : res => res
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
            error: errorDataSource,
            change: function(e) {
                if(e.action == "sync") {
                    e.sender.read();
                }
            }
        });

        var grid = this.grid = $("#grid").kendoGrid({
        	editable: "inline",
            dataSource: dataSource,
            resizable: true,
            pageable: {
                refresh: true,
                pageSizes: true,
                input: true,
                messages: KENDO.pageableMessages ? KENDO.pageableMessages : {}
            },
            sortable: true,
            scrollable: false,
            columns: this.columns,
            filterable: Config.filterable ? Config.filterable : true,
            noRecords: {
                template: `<h2 class='text-danger'>${KENDO.noRecords}</h2>`
            }
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

function addForm(ele) {
	Table.grid.addRow();
}
</script>

<script type="text/javascript">
	window.onload = function() {
		Table.init();
	}
</script>
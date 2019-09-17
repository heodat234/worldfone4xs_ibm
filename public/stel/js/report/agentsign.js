function detailInit(e) {
    var detailRow = e.detailRow;
    detailRow.find(".tabstrip").kendoTabStrip({
        animation: {
            open: { effects: "fadeIn" }
        }
    });
    detailRow.find(".agent-status-grid").kendoGrid({
        pageable: {refresh: true},
    	columns: [
    		{field: "starttime", title: "Start", template: function(dataItem) {
                return (kendo.toString(dataItem.starttime, "dd/MM/yy H:mm:ss") ||  "").toString();
            	}
            },
    		{field: "endtime", title: "End", template: function(dataItem) {
                return (kendo.toString(dataItem.endtime, "dd/MM/yy H:mm:ss") ||  "").toString();
            	}
            },
    		{field: "status.text", title: "Status"},
    		{field: "note", title: "Start note"},
    		{field: "endnote", title: "End note"}
    	],
    	dataSource: {
    		serverFiltering: true,
    		serverSorting: true,
    		serverPaging: true,
    		pageSize: 5,
    		filter: [
    			{field: "extension", operator: "eq", value: e.data.extension},
    			{field: "my_session_ids", operator: "eq", value: e.data.my_session_id}
    		],
    		transport: {
    			read: ENV.restApi + "agentstatus",
                parameterMap: parameterMap
    		},
    		schema: {
    			data: "data",
    			total: "total",
    			model: {
    				starttime: {type: "date"},
		            endtime: {type: "date"},
		            statuscode : {type: "number"},
    			},
    			parse: function (response) {
			        response.data.map(function(doc) {
			            doc.starttime = new Date(doc.starttime * 1000);
			            doc.endtime = doc.endtime ? new Date(doc.endtime * 1000) : null;
			            return doc;
			        })
			        return response;
			    }
    		},
            error: errorDataSource
    	},
    	sortable: true,
        scrollable: true,
    })
    detailRow.find(".sip-status-grid").kendoGrid({
        pageable: {refresh: true},
    	columns: [
    		{field: "starttime", title: "Start", template: function(dataItem) {
                return (kendo.toString(dataItem.starttime, "dd/MM/yy H:mm:ss") ||  "").toString();
            	}
            },
    		{field: "endtime", title: "End", template: function(dataItem) {
                return (kendo.toString(dataItem.endtime, "dd/MM/yy H:mm:ss") ||  "").toString();
            	}
            },
    		{field: "status", title: "Status"},
    		{field: "note", title: "Start note"},
    		{field: "endnote", title: "End note"}
    	],
    	dataSource: {
    		serverFiltering: true,
    		serverSorting: true,
    		serverPaging: true,
    		pageSize: 5,
    		filter: [
    			{field: "extension", operator: "eq", value: e.data.extension},
    			{field: "my_session_ids", operator: "eq", value: e.data.my_session_id}
    		],
    		transport: {
    			read: ENV.restApi + "agentstate",
                parameterMap: parameterMap
    		},
    		schema: {
    			data: "data",
    			total: "total",
    			model: {
    				starttime: {type: "date"},
		            endtime: {type: "date"}
    			},
    			parse: function (response) {
			        response.data.map(function(doc) {
			            doc.starttime = new Date(doc.starttime * 1000);
			            doc.endtime = doc.endtime ? new Date(doc.endtime * 1000) : null;
			            return doc;
			        })
			        return response;
			    }
    		},
            error: errorDataSource
    	},
    	sortable: true,
        scrollable: true,
    });
    detailRow.find(".queue-status-grid").kendoGrid({
        pageable: {refresh: true},
        columns: [
            {field: "starttime", title: "Start", template: function(dataItem) {
                return (kendo.toString(dataItem.starttime, "dd/MM/yy H:mm:ss") ||  "").toString();
                }
            },
            {field: "endtime", title: "End", template: function(dataItem) {
                return (kendo.toString(dataItem.endtime, "dd/MM/yy H:mm:ss") ||  "").toString();
                }
            },
            {field: "name", title: "Name"},
            {field: "type", title: "Type"},
            {field: "paused", title: "Pause"},
            {field: "endnote", title: "End note"}
        ],
        dataSource: {
            serverFiltering: true,
            serverSorting: true,
            serverPaging: true,
            pageSize: 5,
            filter: [
                {field: "extensions", operator: "eq", value: e.data.extension},
                {field: "my_session_ids", operator: "eq", value: e.data.my_session_id}
            ],
            transport: {
                read: ENV.restApi + "queuestatus",
                parameterMap: parameterMap
            },
            schema: {
                data: "data",
                total: "total",
                model: {
                    starttime: {type: "date"},
                    endtime: {type: "date"},
                    paused: {type: "boolean"}
                },
                parse: function (response) {
                    response.data.map(function(doc) {
                        doc.starttime = new Date(doc.starttime * 1000);
                        doc.endtime = doc.endtime ? new Date(doc.endtime * 1000) : null;
                        return doc;
                    })
                    return response;
                }
            },
            error: errorDataSource
        },
        sortable: true,
        scrollable: true,
    });
    detailRow.find(".browser-tab-grid").kendoGrid({
        pageable: {refresh: true},
        columns: [
            {field: "starttime", title: "Start", template: function(dataItem) {
                return (kendo.toString(dataItem.starttime, "dd/MM/yy H:mm:ss") ||  "").toString();
                }
            },
            {field: "endtime", title: "End", template: function(dataItem) {
                return (kendo.toString(dataItem.endtime, "dd/MM/yy H:mm:ss") ||  "").toString();
                }
            }
        ],
        dataSource: {
            serverFiltering: true,
            serverSorting: true,
            serverPaging: true,
            pageSize: 5,
            filter: [
                {field: "my_session_id", operator: "eq", value: e.data.my_session_id}
            ],
            transport: {
                read: ENV.restApi + "browsertab",
                parameterMap: parameterMap
            },
            schema: {
                data: "data",
                total: "total",
                model: {
                    starttime: {type: "date"},
                    endtime: {type: "date"}
                },
                parse: function (response) {
                    response.data.map(function(doc) {
                        doc.starttime = new Date(doc.starttime * 1000);
                        doc.endtime = doc.endtime ? new Date(doc.endtime * 1000) : null;
                        return doc;
                    })
                    return response;
                }
            },
            error: errorDataSource
        },
        sortable: true,
        scrollable: true,
    })

    detailRow.find(".browser-page-grid").kendoGrid({
        pageable: {refresh: true},
        columns: [
            {field: "starttime", title: "Start", template: function(dataItem) {
                return (kendo.toString(dataItem.starttime, "dd/MM/yy H:mm:ss") ||  "").toString();
                }
            },
            {field: "endtime", title: "End", template: function(dataItem) {
                return (kendo.toString(dataItem.endtime, "dd/MM/yy H:mm:ss") ||  "").toString();
                }
            },
            {field: "uri", title: "Page"}
        ],
        dataSource: {
            serverFiltering: true,
            serverSorting: true,
            serverPaging: true,
            pageSize: 5,
            filter: [
                {field: "my_session_id", operator: "eq", value: e.data.my_session_id}
            ],
            transport: {
                read: ENV.restApi + "browserpage",
                parameterMap: parameterMap
            },
            schema: {
                data: "data",
                total: "total",
                model: {
                    starttime: {type: "date"},
                    endtime: {type: "date"}
                },
                parse: function (response) {
                    response.data.map(function(doc) {
                        doc.starttime = new Date(doc.starttime * 1000);
                        doc.endtime = doc.endtime ? new Date(doc.endtime * 1000) : null;
                        return doc;
                    })
                    return response;
                }
            },
            error: errorDataSource
        },
        sortable: true,
        scrollable: true,
    })
}

var Table = {
    dataSource: {},
    grid: {},
    columns: Config.columns,
    init: async function() {
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
                    type: "PUT"
                },
                create: {
                    url: Config.crudApi + Config.collection,
                    type: "POST"
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

        var grid = this.grid = await $("#grid").kendoGrid({
            dataSource: dataSource,
            excel: {allPages: true},
            resizable: true,
            pageable: {
                refresh: true
            },
            sortable: true,
            scrollable: false,
            columns: this.columns,
            filterable: true,
            detailTemplate: `
            	<div class="tabstrip">
                    <ul>
                        <li class="k-state-active">
                        	Agent status
                        </li>
                        <li>
                        	Sip status
                        </li>
                        <li>
                            Queue status
                        </li>
                        <li>
                            Browser tab
                        </li>
                        <li>
                            Browser page
                        </li>
                    </ul>
                    <div>
                        <div class="agent-status-grid"></div>
                    </div>
                    <div>
                        <div class="sip-status-grid">
                        </div>
                    </div>
                    <div>
                        <div class="queue-status-grid">
                        </div>
                    </div>
                    <div>
                        <div class="browser-tab-grid">
                        </div>
                    </div>
                    <div>
                        <div class="browser-page-grid">
                        </div>
                    </div>
                </div>
            `,
            detailInit: detailInit,
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
$(document).ready(function() {
    var customFilterVar = $('#custom-filter');
    customFilterVar.click();
});

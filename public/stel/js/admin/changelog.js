var hierarchicalDataSource = new kendo.data.HierarchicalDataSource({
    transport: {
        read: {
            url: Config.crudApi + Config.collection + "/read"
        },
        parameterMap: function(options, operation) {
            return options;
        }
    },
    schema: {
        model: {
            hasChildren: "is_dir",
            id: "id",
        },
        parse: function(response) {
        	response.map(function(doc) {
        		doc.modify_timeText = kendo.toString(new Date(doc.modify_time * 1000), "dd/MM/yy H:mm:ss");
        		doc.access_timeText = kendo.toString(new Date(doc.access_time * 1000), "dd/MM/yy H:mm:ss");
        		if(doc.logs) {
        			doc.logs.map(function(log, index) {
        				log.index = index;
        				log.timeText = kendo.toString(new Date(log.change_time * 1000), "dd/MM/yy H:mm:ss");
        			})
        		}
        	})
        	return response;
        }
    },
    error: errorDataSource
});

var viewModel = kendo.observable(Object.assign({
    files: hierarchicalDataSource,
}, Config.observable));

kendo.bind($("#allview"), viewModel);
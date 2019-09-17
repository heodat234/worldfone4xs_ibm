var hierarchicalDataSource = new kendo.data.HierarchicalDataSource({
    transport: {
        read: {
            url: Config.crudApi + Config.collection
        },
        parameterMap: parameterMap
    },
    schema: {
        model: {
        	data: "data",
        	total: "total",
            id: "id"
        }
    },
    error: errorDataSource
});

var viewModel = kendo.observable(Object.assign({
    files: hierarchicalDataSource,
}, Config.observable));

kendo.bind($("#allview"), viewModel);
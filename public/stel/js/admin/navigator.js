var hierarchicalDataSource = new kendo.data.HierarchicalDataSource({
    transport: {
        read: {
            url: Config.crudApi + Config.collection
        },
        parameterMap: parameterMap
    },
    schema: {
        model: {
            hasChildren: "hasChild",
            id: "id"
        }
    },
    error: errorDataSource
});

var viewModel = kendo.observable(Object.assign({
    files: hierarchicalDataSource,
    parentOption: dataSourceDropDownListPrivate("Navigator", ["name"], null, res => res, 200)
}, Config.observable));

kendo.bind($("#allview"), viewModel);
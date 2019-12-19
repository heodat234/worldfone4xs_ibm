function dataSourceDropDownListPrivate(collection, field, match = null, parse = res => res, pageSize = 1000) {
    if(typeof match === "function") {
        parse = match;
        match = null;
    }
    return new kendo.data.DataSource({
        serverPaging: true,
        serverFiltering: true,
        serverSorting: true,
        pageSize : pageSize,
        sort: {field: "_id", dir: "asc"},
        transport: {
            read: {
                url: ENV.vApi + `select/foreign_private/${collection}`,
                data: {field: field, match: match}
            },
            parameterMap: parameterMap
        },
        schema: {
            data: "data",
            parse: parse
        },
        error: errorDataSource
    })
}
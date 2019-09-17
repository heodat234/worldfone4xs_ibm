<div id="col-filter">
    <div style="padding: 5px; background-color: ghostwhite">
        <h5 class="text-center text-success"><b style="font-size: 16px"><span class="k-icon k-i-filter"></span> Filter widget</b></h5>
        <input data-role="combobox"
                   data-placeholder="Search your filter..."
                   data-value-primitive="true"
                   data-filter="contains"
                   data-text-field="name"
                   data-value-field="name"
                   data-bind="source: filterDataSource"
                   style="width: 220px"
            />
        <div style="margin-left: 2px" class="pull-right">
            <button data-role="button" data-icon="filter-clear" data-bind="click: refreshFilter"></button>
            <button data-role="button" data-icon="save" data-bind="click: saveFilter" style="margin-left: 5px"></button>
        </div>
    </div>
    <div data-role="listview" class="list-group" id="filterList"
             data-template="filterListTemplate"
             data-selectable="true"
             data-bind="source: filterDataSource, events: {change: filterListChange}"></div>
</div>
<script type="text/javascript">
    var columnsConvert = {}
    Config.columns.map(function(col) {
        if(col.field != undefined)
            columnsConvert[col.field] = col.title;
    })
    var operatorConvert = {
        eq: "equal to",
        neq: "not equal to",
        isnull: "is equal to null",
        isnotnull: "is not equal to null",
        lt: "less than",
        lte: "less than or equal to",
        gt: "greater than",
        gte: "greater than or equal to",
        startswith: "start with",
        endswith: "end with",
        contains: 'contains',
        doesnotcontain: "does not contain",
        isempty: "is empty",
        isnotempty: "is not empty"
    }
</script>
<script type="text/x-kendo-template" id="filterListTemplate">
    <div class="dropdown">
        <a href="javascript:void(0)" class="list-group-item dropdown-toggle" data-toggle="dropdown">
            <div class="pull-right"><button data-role="button" data-icon="close" data-id="#: _id #" data-bind="click: removeFilter"></button></div>
            <h4 class="list-group-item-heading" data-bind="text: name"></h4>
            <p class="list-group-item-text" data-bind="text: description"></p>
        </a>
        <ul class="dropdown-menu dropdown-menu-right" style="width: 320px">
            <li class="dropdown-header text-center">
                Filter Detail 
            </li>
            <li class="filter-container" style="padding: 10px 20px">
                <span class="label label-default upper-case" data-bind="text: filter.logic"></span>
                <ul data-template="filterDetailTemplate" data-bind="source: filter.filters" class="list-group"></ul>
            </li>
        </ul>
    </div>
</script>
<script type="text/x-kendo-template" id="filterDetailTemplate1">
    # if(typeof logic != 'undefined' && typeof filters != 'undefined') { #
        <li class="list-group-item">
            <span class="label label-primary upper-case">#: logic #</span>
        # for(var i = 0; i < filters.length; i++) { #
            <ul class="list-group">
                <li class="list-group-item"><label>Field: </label><span class="label label-warning badge">#: columnsConvert[filters[i].field] #</span></li>
                <li class="list-group-item"><label>Operator: </label><span class="label label-info badge">#: operatorConvert[filters[i].operator] #</span></li>
                <li class="list-group-item"><label>Value: </label><span class="label label-success badge">#: filters[i].value #</span></li>
            </ul>
        # } #
        </li>
    # } else {#
        <li class="list-group-item"><label>Field: </label><span class="label label-warning badge">#: columnsConvert[field] #</span></li>
        <li class="list-group-item"><label>Operator: </label><span class="label label-info badge">#: operatorConvert[operator] #</span></li>
        <li class="list-group-item"><label>Value: </label><span class="label label-success badge">#: value #</span></li>
    # } #
</script>

<script type="text/x-kendo-template" id="filterDetailTemplate">
    # if(typeof logic != 'undefined' && typeof filters != 'undefined') { #
        <li class="list-group-item">
            <span class="label label-primary upper-case">#: logic #</span>
        # for(var i = 0; i < filters.length; i++) { #
            <ul class="list-group">
                <li class="list-group-item">
                    <span class="label label-warning">#: columnsConvert[filters[i].field] #</span>&nbsp;
                    <span class="label label-info">#: operatorConvert[filters[i].operator] #</span>&nbsp;
                    <span class="label label-success">#: filters[i].value #</span>
                </li>
            </ul>
        # } #
        </li>
    # } else {#
        <li class="list-group-item">
            <span class="label label-warning">#: columnsConvert[field] #</span>&nbsp;
            <span class="label label-info">#: operatorConvert[operator] #</span>&nbsp;
            <span class="label label-success">#: value #</span>
        </li>
    # } #
</script>
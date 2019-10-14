<div class="input-group-btn column-widget">
	<a role="button" class="btn btn-alt btn-sm btn-primary dropdown-toggle" data-toggle="dropdown" onclick="editColumns(this)"><i class="fa fa-calculator"></i> <b>@Edit Columns@</b></a>
    <ul class="dropdown-menu dropdown-menu-right" style="width: 300px">
        <li class="dropdown-header text-center">@Choose columns will show@</li>
        <li class="filter-container" style="padding-bottom: 15px">
            <div class="form-horizontal" data-bind="source: columns" data-template="column-template"/>
        </li>
    </ul>
	<a role="button" class="btn btn-alt btn-sm btn-primary" data-field="createdAt" onclick="customFilter(this, Table.dataSource)"><i class="fa fa-filter"></i> <b>@Custom Filter@</b></a>
</div>
<div data-role="splitter"
             data-panes="[
                { collapsible: true, min: '700px'},
                { collapsible: true, min: '300px', size: '320px' },
             ]"
             data-orientation="horizontal" style="height: 80vh; overflow-y: auto;">
    <div class="col-sm-9" id="left-col">
        <h4 class="fieldset-legend" style="margin: 0 0 20px"><span style="font-weight: 500;  line-height: 1">@STATISTIC CALL IN TODAY@</span></h4>
        <!-- Table Styles Content -->
        <table class="metrotable">
            <thead>
                <tr>
                    <th>@DID@</th>
                    <th>@Waiting@</th>
                    <th>@Talking@</th>
                    <th>@Misscall@</th>
                    <th>@Offered call@</th>
                    <th>@Misscall@ @rate@</th>
                </tr>
            </thead>
            <tbody data-template="row-template"
             data-bind="source: dataSource"></tbody>
            <tfoot>
                <tr>
                    <td>@Total@</td>
                    <td data-bind="text: dataSource._aggregateResult.waiting"></td>
                    <td data-bind="text: dataSource._aggregateResult.talking"></td>
                    <td data-bind="text: dataSource._aggregateResult.totalabandonedcall"></td>
                    <td data-bind="text: dataSource._aggregateResult.totalofferedcall"></td>
                    <td><span data-bind="text: dataSource._aggregateResult.abandonedcallrate"></span><span>%</span></td>
                </tr>
            </tfoot>
        </table>
        <!-- END Table Styles Content -->
    </div>
    <div class="col-sm-3" style="height: 80vh; overflow-y: auto; overflow-x: hidden; padding: 0;" id="right-col">
        <div style="padding: 10px">
            <h4 class="text-center" style="margin-top: 7px; margin-bottom: 12px">@MISS CALL TODAY@</h4>
            <table class="customertable">
                <thead>
                    <tr>
                        <th>@Phone@ <i class="fa fa-arrow-circle-o-right"></i></th>
                        <th>@Available@</th>
                        <th>@Wait Duration@</th>
                    </tr>
                </thead>
                <tbody data-template="misscall-template"
                data-bind="source: abandonedDataSource"></tbody>
                <tfoot>
                    <tr>
                        <td colspan="3">
                            <div data-role="pager" data-bind="source: abandonedDataSource"></div>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<style type="text/css">
    .metrotable > thead > tr > th {
        padding: 1em 1em 0 0;
        text-align: left;
        font-size: 2em;
        font-weight: lighter;
        color: #bbb;
        border-bottom: 1px solid #ccc;
    }

    .metrotable > tbody > tr > td {
        padding: .5em 1em .5em 0;
        text-align: left;
        font-size: 1.5em;
        font-weight: lighter;
        color: #787878;
        border-bottom: 1px solid #e1e1e1;
    }

    .metrotable > tfoot > tr > td {
        padding: .5em 1em .5em 0;
        text-align: left;
        font-size: 1.6em;
        font-weight: lighter;
        color: #000;
        border-bottom: 1px solid #e1e1e1;
    }

    .customertable {
        width: 100%;
    }

    .customertable > thead > tr > th {
        padding: 1em 1em 0 0;
        text-align: left;
        font-size: 1.2em;
        font-weight: lighter;
        color: #bbb;
        border-bottom: 1px solid #ccc;
    }

    .customertable > tbody > tr > td {
        padding: .5em 1em .5em 0;
        text-align: left;
        font-size: 0.9em;
        font-weight: lighter;
        color: #787878;
        border-bottom: 1px solid #e1e1e1;
    }

</style>

<script id="row-template" type="text/x-kendo-template">
    <tr>
        <td data-bind="text: did"></td>
        <td data-bind="text: waiting"></td>
        <td data-bind="text: talking"></td>
        <td data-bind="text: totalabandonedcall"></td>
        <td data-bind="text: totalofferedcall"></td>
        <td><span data-bind="text: abandonedcallrate"></span><span>%</span></td>
    </tr>
</script>

<script id="misscall-template" type="text/x-kendo-template">
    <tr>
        <td data-bind="text: customernumber"></td>
        <td># if(typeof glide_extension != 'undefined'){ ##= gridArray(glide_extension) ##}#</td>
        <td data-bind="text: totalduration"></td>
    </tr>
</script>
<li data-toggle="tooltip" title="@Call in queue@" id="wait-in-queue" data-placement="right">
    <a href="javascript:void(0)" class="btn btn-alt btn-sm btn-default" data-toggle="dropdown">
        <i class="hi hi-phone_alt"></i>
        <span class="label label-primary label-indicator animation-floating" data-bind="text: item.totalText"></span>
    </a>
    <ul class="dropdown-menu dropdown-custom dropdown-options" style="left: -180px; width: 380px" data-template="header-wait-in-queue-template">
    	<li class="dropdown-header text-center">
    		<b style="font-size: 18px">@Call in queue@</b>
    	</li>
    	<li data-bind="visible: item.total">
            <table class="text-left table table-hover" style="width: 100%; margin-bottom: 0">
            	<thead>
                    <tr>
                        <td>@Phone@</td>
                        <td>@Waiting time@</td>
                        <td>@DID@</td>
                        <td>@Available Extensions@</td>
                    </tr>
                </thead>
                <tbody data-template="header-wait-in-queue-template" data-bind="source: item.data">
                </tbody>
            </table>
        </li>
        <li data-bind="invisible: item.total" class="text-center"><a>@Not any call@</a></li>
    </ul>
</li>

<script id="header-wait-in-queue-template" type="text/x-kendo-template"> 
    <tr>
    	<td data-bind="text: customernumber"></td>
    	<td class="text-center">
    		<span class="label label-warning animation-pulse" data-bind="text: waitingTime"></span>
    		&nbsp;<b>s</b>
    	</td>
    	<td class="text-center"><span data-bind="text: dnis, attr: {title: queue}"></span></td>
    	<td>#= gridArray(data.extension_available) #</td>
    </tr>
</script>

<script type="text/javascript">
	function waitInQueueWidget(e) {
		$headerWaitInQueue = $("#wait-in-queue");

        var waiInQueueWidgetObservable = kendo.observable({
            item: {data: [], total: 0}
        });
        kendo.bind($headerWaitInQueue, waiInQueueWidgetObservable);
        if(!e.data) return;
        var item = JSON.parse(e.data);
        if(item.total) {
            item.data.map((doc, index) => {
                if(index % 2 != 0)
                    doc.odd = true;
               	doc.waitingTime = item.time - doc.starttime;
            })
            item.totalText = item.total.toString();
        } else item.totalText = "";
        waiInQueueWidgetObservable.set("item", item);
	}
</script>
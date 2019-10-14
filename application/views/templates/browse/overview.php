<div class="col-xs-12">
	<h4 class="fieldset-legend" style="margin: 0 0 20px"><span style="font-weight: 500; background-color: #eaedf1; line-height: 1">SMS & EMAIL PENDING</span></h4>
	<div class="row mvvm">
		<div class="col-sm-4" style="margin-top: 10px">
		    <div class="alert alert-info" style="cursor: pointer;" data-bind="click: goTo" data-page="Sms" data-index="1">
		    	<h4>SMS</h4>
		        <p><b>@Total@ @pending@: </b><span data-bind="text: totalSmsPending"></span></p>
		        <p class="text-right text-muted"><b>@Last@ @pending@: </b><span data-bind="text: lastSmsPendingAt"></span></p>
		    </div>
		</div>
		<div class="col-sm-4" style="margin-top: 10px">
		    <div class="alert alert-danger" style="cursor: pointer;" data-bind="click: goTo" data-page="Email" data-index="2">
		    	<h4>Email</h4>
		        <p><b>@Total@ @pending@: </b><span data-bind="text: totalEmailPending"></span></p>
		        <p class="text-right text-muted"><b>@Last@ @pending@: </b><span data-bind="text: lastEmailPendingAt"></span></p>
		    </div>
		</div>
	</div>
</div>
<div class="col-xs-12">
	<h4 class="fieldset-legend" style="margin: 0 0 20px"><span style="font-weight: 500; background-color: #eaedf1; line-height: 1">SMS LOGS</span></h4>
	<div class="row mvvm">
		<div class="col-xs-12">
			<div data-role="grid"
                data-pageable="{refresh: true}"
                data-no-records="{
                    template: `<h2 class='text-danger'>NO DATA</h2>`
                }"
                data-columns="[
                    {
                        field: 'phone',
                        title: '@Phone@',
                        width: 120,
                    },{
                        field: 'content',
                        title: '@Content@',
                    },
                    {field:'createdBy', title: '@Created by@'},
                    {field:'createdAtText', title: '@Created at@'}
                    ]"
              data-bind="source: smsData"></div>
      	</div>
	</div>
</div>
<div class="col-xs-12">
	<h4 class="fieldset-legend" style="margin: 0 0 20px"><span style="font-weight: 500; background-color: #eaedf1; line-height: 1">EMAIL LOGS</span></h4>
	<div class="row mvvm">
		<div class="col-xs-12">
			<div data-role="grid"
                data-pageable="{refresh: true}"
                data-no-records="{
                    template: `<h2 class='text-danger'>NO DATA</h2>`
                }"
                data-columns="[
                    {
                        field: 'email',
                        title: '@Email@',
                        width: 200,
                    },{
                        field: 'subject',
                        title: '@Subject@',
                        width: 170,
                    },
                    {field:'createdBy', title: '@Created by@'},
                    {field:'createdAtText', title: '@Created at@'}
                    ]"
              data-bind="source: emailData"></div>
      	</div>
	</div>
</div>

<script type="text/javascript">
	var init = async function() {
		var smsPendingData = await $.ajax(ENV.restApi + `sms_pending`, {q: JSON.stringify({sort: {field: "createdAt", dir: "desc"}})
		});
		var emailPendingData = await $.ajax(ENV.restApi + `email_pending`, {q: JSON.stringify({sort: {field: "createdAt", dir: "desc"}})
		});
		var observable = kendo.observable({
			totalSmsPending: smsPendingData.total,
			lastSmsPendingAt: smsPendingData.data.length ? gridTimestamp(smsPendingData.data[0].createdAt) : "",
			totalEmailPending: emailPendingData.total,
			lastEmailPendingAt: emailPendingData.data.length ? gridTimestamp(emailPendingData.data[0].createdAt) : "",
			goTo: function(e) {
				$currentTarget = $(e.currentTarget);
				var page = $currentTarget.data("page");
				var index = $currentTarget.data("index");
				router.navigate("/" + page.toLowerCase());
				layoutViewModel.set("breadcrumb", page);
				layoutViewModel.setActive(index);
			},
			smsData: new kendo.data.DataSource({
				serverPaging: true,
				pageSize: 5,
				transport: {
					read: ENV.restApi + "sms_logs",
					parameterMap: parameterMap
				},
				schema: {
					data: "data",
					total: "total",
					parse: function(response) {
	                    response.data.map(doc =>  {
	                        doc.createdAtText = gridTimestamp(doc.createdAt);
	                    })
	                    return response
	                }
				}
			}),
			emailData: new kendo.data.DataSource({
				serverPaging: true,
				pageSize: 5,
				transport: {
					read: ENV.restApi + "email_logs",
					parameterMap: parameterMap
				},
				schema: {
					data: "data",
					total: "total",
					parse: function(response) {
	                    response.data.map(doc =>  {
	                        doc.createdAtText = gridTimestamp(doc.createdAt);
	                    })
	                    return response
	                }
				}
			})
		});
		kendo.bind($(".mvvm"), observable);
	}();
</script>
<!-- Table Styles Header -->
<ul class="breadcrumb breadcrumb-top">
    <li>@Setting@</li>
    <li>@Department@</li>
    <li data-bind="text: item.typename, visible: item.typename"></li>
    <li class="pull-right none-breakcrumb">
        <a role="button" href="javascript:void(0)" class="btn btn-sm" data-bind="click: save"><b>@Save@</b></a>
    </li>
</ul>
<!-- END Table Styles Header -->
<div class="container-fluid after-breadcrumb">
	<div class="row" style="margin: 0 10px">
		<h4 class="fieldset-legend" style="margin: 0 0 20px"><span style="font-weight: 500; line-height: 1">@BASIC@</span></h4>
	</div>
	<div class="row" style="margin: 10px 30px 0 60px;y">
		<form class="form-horizontal">
			<div class="col-md-6" style="border-right: 1px dashed lightgray">
				<div class="form-group">
			        <label class="control-label col-sm-6">@Name@</label>
			        <div class="col-sm-6">
			        	<span class="label label-primary" data-bind="text: item.typename" style="font-size: 20px"></span>
			        </div>
			    </div>
			    <div class="form-group">
			        <label class="control-label col-sm-6">@Duration@ ACW</label>
			        <div class="col-sm-6">
			        	<input data-role="numerictextbox" data-format="n0"
			        	style="width: 70px" 
			        	data-bind="value: item.acw_duration">
			        	<i>@seconds@</i>
			        </div>
			    </div>
			</div>
			<div class="col-md-6">
				<div class="form-group">
			        <label class="control-label col-sm-6">@Auto delete misscall@</label>
			        <div class="col-sm-6">
			        	<label class="switch switch-primary">
			        		<input type="checkbox" data-bind="checked: item.auto_delete_misscall"><span></span>
			        	</label>
			        </div>
			    </div>
			    <div class="form-group">
			        <label class="control-label col-sm-6">@Auto delete followup@</label>
			        <div class="col-sm-6">
			        	<label class="switch switch-primary">
			        		<input type="checkbox" data-bind="checked: item.auto_delete_followup"><span></span>
			        	</label>
			        </div>
			    </div>
			</div>
		</form>
	</div>
</div>
<script type="text/javascript">
	window.onload = async function() {
		var item = await $.get(ENV.vApi + "configtype/detail/" + ENV.type);
		kendo.bind($("#page-content"), kendo.observable({
			item: item,
			save: function() {
				var data = this.item.toJSON();
					data.ringtone = (data.ringtone || '');
				$.ajax({
					url: ENV.vApi + "configtype/update/" + data.id,
					type: "POST",
					contentType: "application/json; charset=utf-8",
					data: JSON.stringify(data),
					success: function() {
						syncDataSource();
					},
					error: errorDataSource
				})
			}
		}));
	}
</script>
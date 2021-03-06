<div class="input-group-btn column-widget">
	<a style="visibility: hidden;" id="data-library" role="button" class="btn btn-alt btn-sm btn-primary" data-field1="account_no" data-field2="cif" data-field3="cus_name" onclick="customFilter(this, Table.dataSource)"><i class="fa fa-filter"></i> <b>@Custom Filter@</b></a>
</div>
<script>
	async function customFilter(ele, dataSource, dateRange = 1) {
		var field_1 = $(ele).data("field1"),field_2 = $(ele).data("field2"),
			field_3 = $(ele).data("field3"),
			mvvmSelector = ".filter-mvvm";
	    if(!$(ele).hasClass("data-bound")) {
	        var date =  new Date(),
	            timeZoneOffset = date.getTimezoneOffset() * 60000;
	            date.setHours(- timeZoneOffset / 3600000, 0, 0 ,0);
	        var model = kendo.observable({
	            account_no: '',
	            cif: '',
	            cus_name: '',
	            search: function() {
	            	var filter = dataSource.filter();
	                var cif = $('#account_no').val();
	                var loanContract = $('#cif').val();
	                var nationalID = $('#cus_name').val();
	                
	                var filter_1 = filter_2 = filter_3 = '';
	                if (cif != '') {
	                	filter_1 = {field: field_1, operator: "eq", value: cif};
	                }else{
	                	filter_1 = {field: field_1, operator: "neq", value: cif};
	                }
	                if (loanContract != '') {
	                	filter_2 = {field: field_2, operator: "eq", value: loanContract};
	                }else{
	                	filter_2 = {field: field_2, operator: "neq", value: loanContract};
	                }
	                if (nationalID != '') {
	                	filter_3 = {field: field_3, operator: "contains", value: nationalID};
	                }else{
	                	filter_3 = {field: field_3, operator: "neq", value: nationalID};
					}
					if(cif || loanContract || nationalID) {
						var filterTime = {
							logic: "and",
							filters: [
								filter_1,filter_2,filter_3
							]
						};
						dataSource.filter(filterTime);
					}
	            }
	        })
	        var template = await $.get(`${Config.templateApi}sibs/customfilter`);
	        var kendoView = new kendo.View(template, {model: model, wrap: false, template: false});
			//kendo.bind($(mvvmSelector), model);
			
	        kendoView.render($(mvvmSelector));
	        $(ele).addClass("data-bound");
			$(mvvmSelector).fadeIn();
			
	    } else {
	        if($(mvvmSelector).is(":visible")) {
	            $(mvvmSelector).fadeOut();
	        } else {
	            $(mvvmSelector).fadeIn();
	        }
		}
	}

	function onKeyPressTextBox(e) {
		if(e.keyCode == 13) {
			document.getElementById("filter-datalibrary").click();
		}
	}
</script>
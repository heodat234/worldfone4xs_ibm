<div class="input-group-btn column-widget">
	<!-- <a role="button" class="btn btn-alt btn-sm btn-primary dropdown-toggle" data-toggle="dropdown" onclick="editColumns(this)"><i class="fa fa-calculator"></i> <b>@Edit Columns@</b></a>
    <ul class="dropdown-menu dropdown-menu-right" style="width: 300px">
        <li class="dropdown-header text-center">@Choose columns will show@</li>
        <li class="filter-container" style="padding-bottom: 15px">
            <div class="form-horizontal" data-bind="source: columns" data-template="column-template"/>
        </li>
    </ul> -->
	<a role="button" class="btn btn-alt btn-sm btn-primary" data-field1="cif" data-field2="contract_no" data-field3="id_no" onclick="customFilter(this, Table.dataSource)"><i class="fa fa-filter"></i> <b>@Custom Filter@</b></a>
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
	            cif: '',
	            loanContract: '',
	            nationalID: '',
	            cifChange: function(e) {
					// console.log(e.data.cif);

	                
	            },
	            loanContractChange: function(e) {
	            	
	            },
	            nationalIDChange: function(e) {},
	            search: function() {
	            	var filter = dataSource.filter();
	                var cif = $('#cif_id').val();
	                var loanContract = $('#loan_id').val();
	                var nationalID = $('#national').val();
	                
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
	                	filter_3 = {field: field_3, operator: "eq", value: nationalID};
	                }else{
	                	filter_3 = {field: field_3, operator: "neq", value: nationalID};
	                }
	                var filterTime = {
	                    logic: "and",
	                    filters: [
	                        filter_1,filter_2,filter_3
	                    ]
	                };
	                if (cif == '' && loanContract == '' && nationalID == '') {
	                	var flag = false;
	                }else{
	                	var flag = true;
	                }
	                if(filter && flag) {
	                	var setFlag = false;
	                	filter.filters.map((subFilters, index) => {
	            			if(subFilters.filters) {
	            				for (var i = 0; i < subFilters.filters.length; i++) {
	            					if(subFilters.filters[i].field == field_1) {
	            						subFilters.filters = [];
	            						break;
	            					}
	            					if(subFilters.filters[i].field == field_2) {
	            						subFilters.filters = [];
	            						break;
	            					}
	            					if(subFilters.filters[i].field == field_3) {
	            						subFilters.filters = [];
	            						break;
	            					}
	            				}
	            				if(!subFilters.filters.length) {
	            					filter.filters[index] = filterTime;
	            					setFlag = true;
	            				}
	            			} else {
	            				if(subFilters[index] && subFilters[index].length)
	            					subFilters[index] = subFilters[index].filter(doc => doc.field != field);
	            			}
	            		})
	                	if(!setFlag) filter.filters.push(filterTime);
	                } else {
	                	filter = {
	                		logic: "and",
	                		filters: []
	                	};
	                	filter.filters.push(filterTime);
	                }
	                dataSource.filter(filter);
	            }
	        })
	        var template = await $.get(`${Config.templateApi}data_library/customfilter`);
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
</script>
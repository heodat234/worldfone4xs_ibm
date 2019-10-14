<?php $id = $this->input->get("id") ?>
<div class="col-md-12">
	<div class="col-md-12">
		<div class="col-md-1" style="padding: 14px">
			<img src="http://192.168.16.130:7777/public/picture/agent/Amy.jpg" width="80px" alt="avatar">
		</div>
		<div class="col-md-11">
			<div>
				<h2>You want to divide list to TeleSale' box</h2>
				<p style="opacity: .5">Please select who will receive</p>
			</div>
		</div>
	</div>
	<div class="col-md-12" style="border: 2px solid #867b7b; border-radius: 20px; height: 500px;background: #fff">
		<div id="grid_2"></div>
		<div style="text-align: center;    margin-top: 15px;">
			<button id="saveChanges" class="btn btn-warning" style="width: 150px;margin-right: 10px">Confirm</button>
			<button class="btn btn-default" onclick="cancelChanges()">Cancel</button>
		</div>
	</div>
</div>
<style type="text/css">
	.k-grid, .k-grid * {
	    /*background: white !important;*/
	    border: 0 !important;
	}
	tr.k-footer-template td
	{
	    background: #f9e4e4
	}
	 
	tr.k-group-footer td
	{
	    background: #f9e4e4
	}
	.border-row{
		border-bottom: 1px solid #ddd !important;
		height: 40px !important;
	}
	.k-grid table {
	     border-collapse: collapse; 
	}
	/*.chkbx{
		height: 20px;
		width: 20px;
	}*/
	.container-chkbx {
	  display: block;
	  position: relative;
	  padding-left: 35px;
	  margin-bottom: 25px;
	  cursor: pointer;
	  font-size: 22px;
	  -webkit-user-select: none;
	  -moz-user-select: none;
	  -ms-user-select: none;
	  user-select: none;
	}

	/* Hide the browser's default checkbox */
	.container-chkbx input {
	  position: absolute;
	  opacity: 0;
	  cursor: pointer;
	  height: 0;
	  width: 0;
	}

	/* Create a custom checkbox */
	.checkmark {
	  position: absolute;
	  top: 0;
	  left: 0;
	  height: 25px;
	  width: 25px;
	  background-color: #fff;
	  border: 1px solid #000 !important;
	}

	/* On mouse-over, add a grey background color */
	.container-chkbx:hover input ~ .checkmark {
	  background-color: #ccc;
	}

	/* When the checkbox is checked, add a blue background */
	.container-chkbx input:checked ~ .checkmark {
	  background-color: #fff;
	}

	/* Create the checkmark/indicator (hidden when not checked) */
	.checkmark:after {
	  content: "";
	  position: absolute;
	  display: none;
	  color: #000;
	}

	/* Show the checkmark when checked */
	.container-chkbx input:checked ~ .checkmark:after {
	  display: block;
	}

	/* Style the checkmark/indicator */
	.container-chkbx .checkmark:after {
	  left: 9px;
	  top: 5px;
	  width: 5px;
	  height: 10px;
	  border: solid black;
	  border-width: 0 3px 3px 0;
	  -webkit-transform: rotate(45deg);
	  -ms-transform: rotate(45deg);
	  transform: rotate(45deg);
	}
</style>
<script>
    var Config = {
    	id: '<?= $id ?>',
        crudApi: `${ENV.restApi}`,
        templateApi: `${ENV.templateApi}`,
        collection: "assign",
        observable: {}
    };
</script>
<script>

    dataSource = new kendo.data.DataSource({
    	serverFiltering: true,
        serverPaging: true,
        serverSorting: true,
        transport: {
            read: {
                url: Config.crudApi + Config.collection + "/read/" + Config.id,
            },
            update: {
                url: function(data) {
                    return Config.crudApi + Config.collection + "/update";
                },
                type: "PUT",
                contentType: "application/json; charset=utf-8"
            },
            parameterMap: parameterMap
        },
        sync: syncDataSource,
        error: errorDataSource,
        schema: {
        	data: "data",
            total: "total",
            model: {
                id: "id",
                fields: {
                    id: {
                        editable: false,
                        nullable: true
                    },
                    checked: {
                        type: "number"
                    },
                    agentname: {
                    },
                    extension: {
                        type: "string",
                    },
                    count_detail: {
                        type: "number"
                    },
                    random: {
                        type: "number"
                    },
                    total: {
                        type: "number"
                    }
                }
            },
            parse: function (response) {
            	// var random = 0
            	// if (response.count_random >= response.total) {
            	// 	random = kendo.parseInt(response.count_random / response.total);
            	// 	response.count_random = kendo.parseInt(response.count_random % response.total);
            	// }
		        response.data.map(function(doc) {
		        	// doc.random = random;
		        	// if (response.count_random < response.total && i < response.count_random) {
		        	// 	doc.random += 1;
		        	// 	i ++;
		        	// }
		        	doc.random = 0;
		            doc.total = doc.count_detail + doc.random;
		            return doc;
		        })
		        return response;
		    },
        },
        aggregate: [ { field: "count_detail", aggregate: "sum" },
	                  { field: "random", aggregate: "sum" },
	                  { field: "total", aggregate: "sum" }]
    });

    this.gridOptions = Object.assign({
        dataSource: dataSource,
        height: 430,
        selectable: "multiple, row",
        columns: [
        	{ 
        		template: '<label class="container-chkbx">\
  							<input class="chkbx" id="id_#= uid #" type="checkbox" #= checked ? \'checked="checked"\' : "" # value="#= checked #" >\
  							<span class="checkmark"></span>\
							</label>',
        		width: "50px"
        	},{
        		field: "agentname",
            	title: "TeleSales' Name",
            	width: "150px",
            	footerTemplate: "TOTAL"
        	}, {
	            field: "extension",
	            title: "TeleSales' Code",
	            width: "100px"
            }, {
	            field: "total_fixed",
	            title: "Team",
	            width: "100px"
            }, {
	            field: "count_detail",
	            title: "Fixed from List",
	            width: "100px",
	            aggregates: ["sum"],
	            footerTemplate: " #=sum#"
            }, {
	            field: "random",
	            title: "Random",
	            width: "100px",
	            aggregates: ["sum"],
	            footerTemplate: " #=sum#"
            }, {
	            field: "total",
	            title: "Total",
	            width: "100px",
	            aggregates: ["sum"],
	            footerTemplate: " #=sum#"
            }
        ],
        dataBound: onDataBound,
          
    });
    var grid = this.grid = $("#grid_2").kendoGrid(this.gridOptions).data("kendoGrid");

    $("#grid_2 .k-grid-content").on("change", "input.chkbx", function(e) {
    	var checkedIds = [];
		var data = dataSource.data();

        dataItem = grid.dataItem($(e.target).closest("tr"));
        var count_random = dataItem.count_random;
        data.map(function(doc) {	
        	if (doc.uid == dataItem.uid) {
        		if (doc.checked == 1) {
        			doc.set("checked",0);
        		}else{
        			doc.set("checked",1);
        		}
        		doc.random = 0;
				doc.set("total",doc.count_detail);
        	}
        });
		var allSelected = $("input.chkbx");
		$.each(allSelected, function(e){
		  var row = $(this);
		  if (row.is(':checked')) {
		  	var dataItem1 = grid.dataItem(row.closest("tr"));
		  	checkedIds.push(dataItem1.uid);
		  }
		  		 
		});
        var total_user = checkedIds.length;

 		var random = i = 0;
    	if (count_random >= total_user) {
    		random = parseInt(count_random / total_user);
    		count_random = parseInt(count_random % total_user);
    	}
        checkedIds.map(function(row) {
            data.map(function(doc) {	
            	if (doc.uid == row) {
            		var a = random;
		        	if (count_random < total_user && i < count_random) {
		        		a += 1;
		        		i ++;
		        	}
		            b = doc.count_detail + a;
		            doc.set("random",a);
		            doc.set("total",b);
            	}
 
            });
        });

    });

  	function onDataBound(e) {
  		var rows = e.sender.element.find("tbody tr"); // get all rows
        for (var i = 0; i < rows.length; i++) {
            var row = rows[i];
                $(row).addClass("border-row");
        }
	   
	}
   	
    $("#saveChanges").kendoButton({
        click: function(e) {
            grid.saveChanges();
            notification.show("Confirm successfully", "success");
        }
    })
    $("#saveChanges").removeClass('k-button');

    function cancelChanges() {
    	grid.cancelChanges();
    }
    
</script>
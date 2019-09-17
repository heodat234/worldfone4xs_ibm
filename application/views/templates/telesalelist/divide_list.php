<div class="col-md-12">
	<div class="col-md-12">
		<div class="col-md-1">
			
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
		<button class="btn btn-warning" onclick="confirmDivide()">Confirm</button>

	</div>
</div>
<style type="text/css">
	.k-grid, .k-grid * {
	    background: white !important;
	    border: 0 !important;
	}
</style>
<script>
var Config = {
    crudApi: `${ENV.restApi}`,
    templateApi: `${ENV.templateApi}`,
    collection: "Import_history"
}; 
</script>
<script>

    var crudServiceBaseUrl = "https://demos.telerik.com/kendo-ui/service",
    dataSource = new kendo.data.DataSource({
        transport: {
            read: {
                url: crudServiceBaseUrl + "/Products",
                dataType: "jsonp"
            },
            update: {
                url: crudServiceBaseUrl + "/Products/Update",
                dataType: "jsonp"
            },
            destroy: {
                url: crudServiceBaseUrl + "/Products/Destroy",
                dataType: "jsonp"
            },
            create: {
                url: crudServiceBaseUrl + "/Products/Create",
                dataType: "jsonp"
            },
            parameterMap: function (options, operation) {
                if (operation !== "read" && options.models) {
                    return {
                        models: kendo.stringify(options.models)
                    };
                }
            }
        },
        batch: true,
        pageSize: 20,
        schema: {
            model: {
                id: "ProductID",
                fields: {
                    ProductID: {
                        editable: false,
                        nullable: true
                    },
                    ProductName: {
                        validation: {
                            required: true
                        }
                    },
                    UnitPrice: {
                        type: "number",
                        validation: {
                            required: true,
                            min: 1
                        }
                    },
                    Discontinued: {
                        type: "boolean"
                    },
                    UnitsInStock: {
                        type: "number",
                        validation: {
                            min: 0,
                            required: true
                        }
                    }
                }
            }
        }
    });

    this.gridOptions = Object.assign({
        dataSource: dataSource,
        height: 430,
        pageable: false,
        sortable: false,
        columns: [{ template: "<input type='checkbox' class='checkbox' />" },
        "ProductName", {
            field: "UnitPrice",
            title: "Unit Price",
            format: "{0:c}",
            width: "100px"
            }, {
            field: "UnitsInStock",
            title: "Units In Stock",
            width: "100px"
            }, {
            field: "Discontinued",
            width: "100px"
            }],
        editable: false,
        
    });

    var grid = this.grid = $("#grid_2").kendoGrid(this.gridOptions).data("kendoGrid");

    grid.selectedKeyNames = function() {
        var items = this.select(),
            that = this,
            checkedIds = [];
        $.each(items, function(){
            if(that.dataItem(this))
                checkedIds.push(that.dataItem(this).uid);
        })
        return checkedIds;
    }
        
    function confirmDivide() {
	    var checkIds = grid.selectedKeyNames();
	    console.log(checkIds);
	    // if(checkIds.length) {
	    //     swal({
	    //         title: "Are you sure?",
	    //         text: "Once deleted, you will not be able to recover these documents!",
	    //         icon: "warning",
	    //         buttons: true,
	    //         dangerMode: true,
	    //     })
	    //     .then((willDelete) => {
	    //         if (willDelete) {
	    //             checkIds.forEach(uid => {
	    //                 var dataItem = Table.dataSource.getByUid(uid);
	    //                 Table.dataSource.remove(dataItem);
	    //                 Table.dataSource.sync();
	    //             })
	    //         }
	    //     });
	    // } else {
	    //     swal({
	    //         title: "No row is checked!",
	    //         text: "Please check least one row to remove",
	    //         icon: "error"
	    //     });
	    // }
	}
</script>
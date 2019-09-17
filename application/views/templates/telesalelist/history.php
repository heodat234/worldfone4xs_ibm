<div class="col-sm-3" style="margin: 10px 0" id="page-widget"></div>
<div class="col-sm-9 filter-mvvm" style="display: none; margin: 10px 0"></div>
<div class="col-sm-12" style="overflow-y: auto; padding: 0">
	<div id="grid_1"></div>
</div>
<div id="action-menu">
    <ul>
    	<a href="javascript:void(0)" data-type="detail" onclick="detailData(this)"><li><i class="fa fa-exclamation-circle text-info"></i><span>View Detail</span></li></a>
    	<a href="javascript:void(0)" onclick="divideList(this)"><li><i class="fa fa-exclamation-circle text-info"></i><span>Divide List</span></li></a>
    	<li class="devide"></li>
        <a href="javascript:void(0)" data-type="import" onclick="re_Upload(this)"><li><i class="fa fa-exclamation-circle text-info"></i><span>Re-Upload</span></li></a>
        
    </ul>
</div>
<script>
var Config = {
    crudApi: `${ENV.restApi}`,
    templateApi: `${ENV.templateApi}`,
    collection: "Import_history",
    observable: {
    },
    model: {
        id: "id",
        fields: {
           	begin_import: {type: "date"},
            complete_import: {type: "date"},
        }
    },
    parse: function (response) {
        response.data.map(function(doc) {
            doc.begin_import = new Date(doc.begin_import * 1000);
            doc.complete_import = doc.complete_import ? new Date(doc.complete_import * 1000) : null;
            return doc;
        })
        return response;
    },
    columns: [{
            field: "begin_import",
            title: "Begin Imported",
            template: function(dataItem) {
                return (kendo.toString(dataItem.begin_import, "dd/MM/yy H:mm:ss") ||  "").toString();
            }
        },{
            field: "complete_import",
            title: "Finish Imported",
            template: function(dataItem) {
                return (kendo.toString(dataItem.complete_import, "dd/MM/yy H:mm:ss") ||  "").toString();
            }
        },{
            field: "file_name",
            title: "File Name",
            locked: true,
        },{
            field: "source",
            title: "Source",
            locked: true,
        },{
            field: "status",
            title: "Status",
            locked: true,
            template: function(dataItem) {
            	if (dataItem.status == 1) {
            		return '<h4 style="font-weight: bold">Success</h4>';
            	}else if (dataItem.status == 0) {
            		return '<h4 style="font-weight: bold">Fail</h4>';
            	}
                
            }
        },{
            // Use uid to fix bug data-uid of row undefined
            template: '<a role="button" class="btn btn-sm btn-circle btn-action" style="background: yellow;" data-uid="#: uid #"><i class="fa fa-ellipsis-v"></i></a>',
            width: 20
        }
        ]
}; 
</script>
<script src="<?= STEL_PATH.'js/table2.js' ?>"></script>
<script type="text/javascript">
	

	function re_Upload(ele) {
		swal({
		    title: "Do you want to Re-Upload this file?",
		    // text: "Once deleted, you will not be able to recover this document!",
		    icon: "warning",
		    buttons: {
		    	ftp: {text:"By FTP",value:"ftp"},
		    	confirm: {text:"By Manual", value:"manual"},
			    cancel: "Cancel"
			},
		 	dangerMode: true,
	    })
	    .then((value) => {
	    	var uid = $(ele).data('uid');
			var dataItem = Table.dataSource.getByUid(uid);
		  	switch (value) {
		    	case "ftp":
		    		console.log(dataItem);
	      			swal("Pikachu fainted!");
		      		break;
		    	case "manual":
		      		swal("Gotcha!", "Pikachu was caught!", "success");
		      		break;
		    	default:
		     
		  	}
		});
	    
	}
	function divideList(ele) {
		var uid = $(ele).data('uid');
		var dataItem = Table.dataSource.getByUid(uid);
		router.navigate(`/divide`);
	}

	function detailData(ele) {
		var uid = $(ele).data('uid');
		var dataItem = Table.dataSource.getByUid(uid);
		router.navigate(`/detail/${dataItem.id}`);
	}

	$(document).on("click", ".grid-name", function() {
		detailData($(this).closest("tr"));
	})
	$( document ).ready(function() {
        Table.init();
    });
</script>
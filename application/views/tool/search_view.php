<style type="text/css">
	#library-container a.accordion-toggle:not(.collapsed) ~ i,
	#troubleshoot-container a.accordion-toggle:not(.collapsed) ~ i {
		/* Safari */
		-webkit-transform: rotate(90deg);

		/* Firefox */
		-moz-transform: rotate(90deg);

		/* IE */
		-ms-transform: rotate(90deg);

		/* Opera */
		-o-transform: rotate(90deg);
	}
</style>
<script type="text/x-kendo-template" id="overview-template">
	<div class="panel panel-default">
		<div class="panel-heading">
			<h4 class="panel-title">
				<a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="\#overview-container" href="\#overview-#: index #" data-bind="click: viewIframe"><b data-bind="text: name"></b></a>
				<span><a href="javascript:void(0)" data-bind="attr: {href: href}"><i>@Go to@ @page@</i></a></span> 
			</h4>
		</div>
		<div id="overview-#: index #" class="panel-collapse collapse">
			<div class="panel-body" style="padding: 0">
				<iframe style="width: 100%; min-height: 70vh; border: 0"></iframe>
			</div>
		</div>
	</div>
</script>

<script type="text/x-kendo-template" id="troubleshoot-template">
	<div class="panel panel-default">
		<div class="panel-heading">
			<h4 class="panel-title">
				<a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="\#troubleshoot-container" href="\#troubleshoot-#: id #" data-bind="text: problem"></a>
				&nbsp;
				<i class="fa fa-angle-right"></i> 
			</h4>
		</div>
		<div id="troubleshoot-#: id #" class="panel-collapse collapse">
			<div class="panel-body" data-bind="html: solution">
			</div>
		</div>
	</div>
</script>

<script type="text/x-kendo-template" id="library-attach-template">
    <span>
        <i class="fi fi-#: filename.lastIndexOf('.') ? filename.substr(filename.lastIndexOf('.') + 1) : "file" # text-info"></i>&nbsp;<a data-bind="text: filename, attr: {href: filepath}" download></a> (<i><span>#: humanFileSize(size) #</span></i>)
    </span>
</script>

<script type="text/x-kendo-template" id="library-template">
	<div class="panel panel-default">
		<div class="panel-heading">
			<h4 class="panel-title">
				<a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="\#library-container" href="\#library-#: id #"><b data-bind="text: name"></b></a>
				&nbsp;
				<i class="fa fa-angle-right"></i>
				<div class="pull-right" data-template="library-attach-template" data-bind="source: attachments">
				</div>
				&nbsp;
				<span data-bind="visible: parent_name">(@Parent@: <a href="javascript:void(0)" data-bind="click: filterParentLibrary"><i data-bind="text: parent_name"></i></a>)</span> 
			</h4>
		</div>
		<div id="library-#: id #" class="panel-collapse collapse">
			<div class="panel-body">
				<h2 data-bind="text: title"></h2>
				<div data-bind="html: content, visible: content"></div>
			</div>
			<div data-template="sub-library-template" data-bind="source: children"></div>
		</div>
	</div>
</script>

<script type="text/x-kendo-template" id="sub-library-template">
	<div class="panel panel-default">
		<div class="panel-heading">
			<h4 class="panel-title">
				<a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="\#library-#: data.id #" href="\#sub-library-#: id #"><i>@Child@</i>: <b data-bind="text: name"></b></a>
				&nbsp;
				<i class="fa fa-angle-right"></i>
				<div class="pull-right" data-template="library-attach-template" data-bind="source: attachments">
				</div>
				&nbsp;
				<span data-bind="visible: parent_name">(@Parent@: <a href="javascript:void(0)" data-bind="click: filterParentLibrary"><i data-bind="text: parent_name"></i></a>)</span> 
			</h4>
		</div>
		<div id="sub-library-#: id #" class="panel-collapse collapse">
			<div class="panel-body">
				<h2 data-bind="text: title"></h2>
				<div data-bind="html: content, visible: content"></div>
			</div>
		</div>
	</div>
</script>

<script type="text/x-kendo-template" id="imageSelectTemplate">
    <img style="width: 20px" data-bind="attr: {src: filepath}" alt="@Image@">
    <span data-bind="text: name" style="text-indent: 10px"></span>
</script>

<script type="text/x-kendo-template" id="picture-template">
	<div class="col-sm-2 gallery-image animation-fadeInQuickInv" data-bind="attr: {title: name}" style="height: 200px; overflow: hidden">
        <img data-bind="attr: {src: filepath}" alt="@Image@">
        <div class="gallery-image-options text-center">
            <div class="btn-group btn-group-sm">
                <a data-bind="attr: {href: filepath}" class="gallery-link btn btn-sm btn-alt btn-default" data-toggle="tooltip" title="#if(typeof createdAt != 'undefined'){##: gridTimestamp(createdAt, 'dd/MM/yy H:mm:ss') ##}#"><i class="gi gi-zoom_in"></i></a>
                <a data-bind="attr: {href: filepath}" class="btn btn-sm btn-alt btn-default" data-toggle="tooltip" title="@Download@" download><i class="fa fa-download"></i></a>
            </div>
        </div>
    </div>
</script>

<script type="text/x-kendo-template" id="file-template">
    <div class="col-sm-6 col-md-4 col-lg-2">
		<div class="media-items animation-fadeInQuickInv">
			<div class="media-items-options text-left">
				<a data-bind="attr: {href: filepath}" class="btn btn-xs btn-primary" title="@Download@" download><i class="fa fa-download"></i></a>
			</div>
			<div class="media-items-content">
				<i class="fi fi-#: extension.toLowerCase() # fa-5x text-info"></i>
			</div>
			<h4>
				<strong data-bind="text: name" style="white-space: nowrap; text-overflow: ellipsis;"></strong><br>
				<small>#: humanFileSize(size) #</small>
			</h4>
		</div>
	</div>
</script>

<!-- Search Results Header -->
<ul class="breadcrumb breadcrumb-top">
    <li>@Tool@</li>
    <li>@Advanced search@</li>
</ul>
<!-- END Search Results Header -->
<div class="container-fluid after-breadcrumb">
	<div class="row">
		<!-- Search Styles Block -->
		<div class="block">
		    <!-- Search Styles Title -->
		    <div class="block-title">
		        <ul class="nav nav-tabs" data-toggle="tabs">
		        	<li class="active"><a href="#search-tab-overview">@Overview@</a></li>
		        	<li><a href="#search-tab-troubleshoot">@Troubleshoot@</a></li>
		            <li><a href="#search-tab-library">@Library@</a></li>
		            <li><a href="#search-tab-images">@Image@</a></li>
		            <li><a href="#search-tab-files">@File@</a></li>
		        </ul>
		    </div>
		    <!-- END Search Styles Title -->

		    <!-- Search Styles Content -->
		    <div class="tab-content" style="margin-bottom: 20px">
		    	<div class="tab-pane active" id="search-tab-overview">
		    		<div>
		    			<div class="block-section clearfix">
							<ul class="pagination remove-margin pull-right" data-role="pager" data-auto-bind="false" data-bind="source: overviewData">
		                	</ul>
							<ul class="pagination remove-margin">
								<li class="active"><span><strong data-bind="text: overviewData.total"></strong> @results@</span></li>
							</ul>
							<ul class="pagination remove-margin" style="padding-left: 200px">
								<div class="input-group" style="width: 400px; margin: 0 auto;">
									<input type="text" data-role="autocomplete" 
									data-filter="contains" data-value-primitive="true" data-text-field="name" data-value-field="name" data-bind="value: keyword, source: overviewData" style="width: 300px; font-size: 16px" class="k-textbox">
									<span class="input-group-btn">
										<button type="button" class="btn btn-primary" data-bind="click: searchKeyword"><i class="fa fa-search"></i> @Search@</button>
									</span>
								</div>
							</ul>
						</div>
			    		<div data-template="overview-template" data-auto-bind="false" data-bind="source: overviewData">
						</div>
					</div>
		        </div>

		    	<div class="tab-pane" id="search-tab-troubleshoot">
		    		<!-- Search Info - Pagination -->
		            <div class="block-section clearfix">
		                <ul class="pagination remove-margin pull-right" data-role="pager" data-bind="source: troubleshootData">
		                </ul>
		                <ul class="pagination remove-margin">
		                    <li class="active"><span><strong data-bind="text: troubleshootData.total"></strong> @results@</span></li>
		                </ul>
		                <ul class="pagination remove-margin" style="padding-left: 200px">
							<div class="input-group" style="width: 400px; margin: 0 auto;">
								<input type="text" data-role="autocomplete" 
								data-filter="contains" data-value-primitive="true" data-text-field="name" data-value-field="filepath" data-bind="source: troubleshootData" style="width: 360px; font-size: 16px" class="k-textbox">
								<span class="input-group-addon"><i class="fa fa-search"></i></span>
							</div>
						</ul>
		            </div>
		            <!-- END Search Info - Pagination -->
		    		<div id="troubleshoot-container" class="panel-group" data-template="troubleshoot-template" data-bind="source: troubleshootData">
					</div>
		        </div>
		        <!-- Projects Search -->
		        <div class="tab-pane" id="search-tab-library">
		        	<!-- Search Info - Pagination -->
		            <div class="block-section clearfix">
		                <ul class="pagination remove-margin pull-right" data-role="pager" data-bind="source: libraryData">
		                </ul>
		                <ul class="pagination remove-margin">
		                    <li class="active"><span><strong data-bind="text: libraryData.total"></strong> @results@</span></li>
		                </ul>
		                <ul class="pagination remove-margin" style="padding-left: 200px">
							<div class="input-group" style="width: 400px; margin: 0 auto;">
								<input type="text" data-role="autocomplete" 
								data-filter="contains" data-value-primitive="true" data-text-field="name" data-value-field="name" data-bind="value: keywordLibrary, source: libraryData" style="width: 360px; font-size: 16px" class="k-textbox">
								<span class="input-group-addon"><i class="fa fa-search"></i></span>
							</div>
						</ul>
		            </div>
		            <!-- END Search Info - Pagination -->
		    		<div id="library-container" class="panel-group" data-template="library-template" data-bind="source: libraryData">
					</div>
		        </div>
		        <!-- END Projects Search -->

		        <!-- Images Search -->
		        <div class="tab-pane" id="search-tab-images">
		            <!-- Search Info - Pagination -->
		            <div class="block-section clearfix">
		                <ul class="pagination remove-margin pull-right" data-role="pager" data-bind="source: pictureData">
		                </ul>
		                <ul class="pagination remove-margin">
		                    <li class="active"><span><strong data-bind="text: pictureData.total"></strong> @results@</span></li>
		                </ul>
		                <ul class="pagination remove-margin" style="padding-left: 200px">
							<div class="input-group" style="width: 400px; margin: 0 auto;">
								<input type="text" data-role="autocomplete"
								data-template="imageSelectTemplate"
	                            data-value-template="imageSelectTemplate"
								data-filter="contains" data-text-field="name" data-value-field="filepath" data-bind="source: pictureData" style="width: 360px; font-size: 16px" class="k-textbox">
								<span class="input-group-addon"><i class="fa fa-search"></i></span>
							</div>
						</ul>
		            </div>
		            <!-- END Search Info - Pagination -->

		            <!-- Images Results -->
		            <div class="gallery" data-toggle="lightbox-gallery">
		                <div class="row" data-auto-bind="false" data-template="picture-template" data-bind="source: pictureData">
		                </div>
		            </div>
		            <!-- END Images Results -->
		        </div>
		        <!-- END Images Search -->

		        <!-- Images Search -->
		        <div class="tab-pane" id="search-tab-files">
		            <!-- Search Info - Pagination -->
		            <div class="block-section clearfix">
		                <ul class="pagination remove-margin pull-right" data-role="pager" data-bind="source: fileData">
		                </ul>
		                <ul class="pagination remove-margin">
		                    <li class="active"><span><strong data-bind="text: fileData.total"></strong> @results@</span></li>
		                </ul>
		                <ul class="pagination remove-margin" style="padding-left: 200px">
							<div class="input-group" style="width: 400px; margin: 0 auto;">
								<input type="text" data-role="autocomplete" data-filter="contains" data-value-primitive="true" data-text-field="name" data-value-field="filepath" data-bind="source: fileData" style="width: 360px; font-size: 16px" class="k-textbox">
								<span class="input-group-addon"><i class="fa fa-search"></i></span>
							</div>
						</ul>
		            </div>
		            <!-- END Search Info - Pagination -->

		            <!-- Images Results -->
		            <div class="gallery" data-toggle="lightbox-gallery">
		                <div class="row" data-auto-bind="false" data-template="file-template" data-bind="source: fileData">
		                </div>
		            </div>
		            <!-- END Images Results -->
		        </div>
		        <!-- END Images Search -->
		    </div>
		    <!-- END Search Styles Content -->
		</div>
		<!-- END Search Styles Block -->
	</div>
</div>
<script type="text/javascript">
	window.onload = function() {
		var advancedSearchObservable = {
			keyword: "<?= $this->input->get('q') ?>",
			searchKeyword: function(e) {
				this.overviewData.read({keyword: this.get("keyword")});
			},
			overviewData: new kendo.data.DataSource({
				pageSize: 10,
				serverPaging: true,
	            serverFiltering: true,
	            serverSorting: true,
	            sort: [{field: "createdAt", dir: "desc"}],
	            transport: {
	                read: `${ENV.vApi}search/page`,
	                parameterMap: parameterMap
	            },
	            schema: {
	                data: "data",
	                total: "total",
	                parse: function(response) {
	                    response.data.map(function(doc, idx){
	                    	switch(doc.type) {
	                    		case "Customer":
	                    			doc.href = ENV.baseUrl + "manage/customer/#/detail/" + doc.id;
	                    			doc.iframeUrl = ENV.baseUrl + "manage/customer/?omc=1#/detail/" + doc.id;
	                    			break;
	                    		default:
	                    			doc.href = ENV.baseUrl + doc.uri;
	                    			doc.iframeUrl = ENV.baseUrl + doc.uri + "?omc=1";
	                    			break;
	                    	}
	                        doc.index = idx;
	                    })
	                    return response;
	                }
	            }
	        }),
	        viewIframe: function(e) {
	        	var contentId = $(e.currentTarget).attr("href");
	        	$(contentId).find("iframe").attr("src", e.data.iframeUrl);
	        },
			troubleshootData: new kendo.data.DataSource({
				pageSize: 3,
				serverPaging: true,
	            serverFiltering: true,
	            serverSorting: true,
	            sort: [{field: "createdAt", dir: "desc"}],
	            transport: {
	                read: `${ENV.restApi}troubleshoot`,
	                parameterMap: parameterMap
	            },
	            schema: {
	                data: "data",
	                total: "total",
	                parse: function(response) {
	                    response.data.map(function(doc, idx){
	                        doc.index = idx;
	                    })
	                    return response;
	                }
	            }
	        }),
	        fileData: new kendo.data.DataSource({
				pageSize: 18,
				serverPaging: true,
	            serverFiltering: true,
	            serverSorting: true,
	            sort: [{field: "createdAt", dir: "desc"}],
	            transport: {
	                read: `${ENV.restApi}file`,
	                parameterMap: parameterMap
	            },
	            schema: {
	                data: "data",
	                total: "total",
	                parse: function(response) {
	                    return response;
	                }
	            }
	        }),
	        pictureData: new kendo.data.DataSource({
				pageSize: 18,
				serverPaging: true,
	            serverFiltering: true,
	            serverSorting: true,
	            sort: [{field: "createdAt", dir: "desc"}],
	            transport: {
	                read: `${ENV.restApi}picture`,
	                destroy: {
                        url: function(data) {
                            return ENV.restApi + "picture/" + data.id;
                        },
                        type: "DELETE",
                    },
	                parameterMap: parameterMap
	            },
	            schema: {
	                data: "data",
	                total: "total",
	                model: {
	                	id: "id"
	                },
	                parse: function(response) {
	                	response.data.map(function(doc, idx){
	                        doc.index = idx;
	                    });
	                    return response;
	                }
	            }
	        }),
	        libraryData: new kendo.data.DataSource({
				pageSize: 10,
				serverPaging: true,
	            serverFiltering: true,
	            serverSorting: true,
	            sort: [{field: "pos", dir: "asc"}],
	            transport: {
	                read: `${ENV.vApi}search/library`,
	                parameterMap: parameterMap
	            },
	            schema: {
	            	data: "data",
	                total: "total",
	                model: {
	                	id: "id"
	                },
	                parse: function(response) {
	                    response.data.map(function(doc, idx){
	                        doc.index = idx;
	                    })
	                    return response;
	                }
	            }
	        }),
	        keywordLibrary: "",
	        filterParentLibrary: function(e) {
	        	var name = e.data.parent_name;
	        	this.set("keywordLibrary", name);
	        	this.libraryData.filter({field: "name", operator: "eq", value: name})
	        }
		};
		kendo.bind("#search-tab-overview", kendo.observable(advancedSearchObservable));
		$('ul[data-toggle="tabs"]').on('shown.bs.tab', (e) => {
		 	var target = $(e.target).attr("href");
			kendo.bind(target, kendo.observable(advancedSearchObservable));
		});
	}
</script>
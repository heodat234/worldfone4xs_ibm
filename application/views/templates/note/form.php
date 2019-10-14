<div class="container-fluid" style="min-height: 95vh;">
	<div class="row">
		<textarea class="k-textbox" style="width: 100%" rows="5" data-bind="events: {keyup: addNote}" placeholder="@Type then enter to note@"></textarea>
	</div>
	<div class="row">
		<div class="container-fluid" style="margin-top: 10px; overflow-y: auto; height: 70vh" data-template="note-template" data-bind="source: noteData">
		</div>
	</div>
</div>
<script type="text/x-kendo-template" id="note-template">
    <div class="row">
        <div class="alert alert-#= HELPER.bsColors[index % HELPER.bsColors.length] #">
            <button type="button" class="close" aria-hidden="true" data-bind="click: removeNote" data-id="#= id #">×</button>
            <p data-bind="text: content"></p>
            <p class="text-right text-muted" data-bind="text: createdAtText"></p>
        </div>
    </div>
</script>
<script type="text/javascript">
	var noteObservable = {
		noteData: new kendo.data.DataSource({
            serverFiltering: true,
            serverSorting: true,
            filter: {field: "foreign_id", operator: "eq", value: ENV.extension},
            sort: [{field: "createdAt", dir: "desc"}],
            transport: {
                read: `${ENV.restApi}note`,
                parameterMap: parameterMap
            },
            schema: {
                data: "data",
                total: "total",
                parse: function(response) {
                    response.data.map(function(doc, idx){
                        doc.index = idx;
                        doc.createdAtText = (kendo.toString(new Date(doc.createdAt * 1000), "dd/MM/yy H:mm") ||  "").toString();
                    })
                    return response;
                }
            }
        }),
        addNote: function(e) {
            if(e.keyCode == 13) {
                var content = e.currentTarget.value.replace("\n", "");
                swal({
                    title: "@Are you sure@?",
                    text: `@Save this note@.`,
                    icon: "warning",
                    buttons: true,
                    dangerMode: false,
                })
                .then((sure) => {
                    if (sure) {
                        e.currentTarget.value = "";
                        $.ajax({
                            url: `${ENV.restApi}note`,
                            type: "POST",
                            contentType: "application/json; charset=utf-8",
                            data: kendo.stringify({
                                foreign_id: ENV.extension,
                                content: content
                            }),
                            success: function() {
                                syncDataSource();
                                noteObservable.noteData.read();
                            },
                            error: errorDataSource
                        })
                    }
                });
            }
        },
        removeNote: function(e) {
            var id = $(e.currentTarget).data("id");
            swal({
                title: "Are you sure?",
                text: `Remove this note.`,
                icon: "warning",
                buttons: true,
                dangerMode: false,
            })
            .then((sure) => {
                if(sure) {
                    $.ajax({
                        url: `${ENV.restApi}note/${id}`,
                        type: "DELETE",
                        success: function() {
                            syncDataSource();
                            noteObservable.noteData.read();
                        },
                        error: errorDataSource
                    })
                }
            })
        }
	};
	kendo.bind("#right-form", kendo.observable(noteObservable));
</script>
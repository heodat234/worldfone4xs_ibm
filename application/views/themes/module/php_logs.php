<!-- User Settings, modal which opens from Settings link (found in top right user menu) and the Cog link (found in sidebar user info) -->
<div id="modal-php-logs" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header text-center">
            	<button type="button" class="close" data-dismiss="modal">&times;</button>
                <h2 class="modal-title"><i class="fa fa-bug"></i> PHP LOG <span class="text-primary"><?= $file_name ?></span></h2>
            </div>
            <!-- END Modal Header -->

            <!-- Modal Body -->
            <div class="modal-body">
                <pre>
                    <?= isset($content) ? $content : ""  ?>
                </pre>
            </div>
            <!-- END Modal Body -->
        </div>
    </div>
</div>
<script type="text/javascript">
    $('#modal-php-logs').modal('show');
</script>
<!-- END User Settings -->
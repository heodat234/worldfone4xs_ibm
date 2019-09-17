<!-- User Settings, modal which opens from Settings link (found in top right user menu) and the Cog link (found in sidebar user info) -->
<div id="modal-output-test" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header text-center">
            	<button type="button" class="close" data-dismiss="modal">&times;</button>
                <h2 class="modal-title"><i class="fa fa-bug"></i> OUTPUT TEST</h2>
            </div>
            <!-- END Modal Header -->

            <!-- Modal Body -->
            <div class="modal-body">
                <form class="form-bordered" onsubmit="return false;">
                    <?php if(isset($output_test)) { ?>
                    <div class="form-group">
                        <label class="control-label"><?php if(isset($output_test["name"])) echo $output_test["name"] ?></label>
                        <textarea class="form-control" rows="20"><?php 
                        		if(isset($output_test["value"])) {
	                        		$value = $output_test["value"];
		                        	if( is_object($value) || is_array($value) ) {
										print_r($value);
									} else echo $value;
								}
                        ?></textarea>
                    </div>
                    <?php } ?>
                </form>
            </div>
            <!-- END Modal Body -->
        </div>
    </div>
</div>
<?php if(isset($output_test)) { ?>
<script type="text/javascript">
    window.onload = function() {
        $('#modal-output-test').modal('show');
    }
</script>
<?php } ?>
<!-- END User Settings -->
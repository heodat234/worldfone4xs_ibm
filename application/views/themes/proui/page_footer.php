            </div>
            <!-- END Main content -->
            <!-- Footer -->
            <?php if(empty($only_main_content)) { ?>
            <footer class="clearfix">
                <div class="pull-left">
                    <span id="year-copy"></span> &copy; <a href="https://nghiamotor.vn" target="_blank"><?php echo $template['name'] . ' ' . $template['version']; ?></a>
                </div>
                <div class="pull-right text-success hidden" style="margin-right: 10px">
                    Product of <a href="https://southtelecom.vn" target="_blank">South Telecom</a>
                </div>
            </footer>
            <?php } ?>
            <!-- END Footer -->
        </div>
        <!-- END Main Container -->
    </div>
    <!-- END Page Container -->
</div>
<!-- END Page Wrapper -->

<!-- Scroll to top link, initialized in js/app.js - scrollToTop() -->
<a href="#" id="to-top"><i class="fa fa-angle-double-up"></i></a>

<!-- Notification kendo in common.js -->
<div id="notification"></div>
<!-- Popup container -->
<div id="popup-contain"></div>
<!-- Notification kendo in record.js -->
<div id="play-notification"></div>
<!-- Phone ring -->
<div id="phone-ring-button" class="phone-ring-container" style="display: none">
    <div class="phonering-alo-phone phonering-alo-green">
        <div class="phonering-alo-ph-circle"></div>
        <div class="phonering-alo-ph-circle-fill"></div>
        <a class="pps-btn-img">
            <div class="phonering-alo-ph-img-circle" title="Ring Ring" data-toggle="tooltip" data-placement="top"></div>
        </a>
    </div>
</div>

<script>
const PERMISSION = <?= isset($permission) ? json_encode($permission) : "{}" ?>;
</script>
<?php if(!empty($only_main_content)) { ?>
    <style>
        html {
            overflow: hidden;
        }
        #sidebar {
            width: 0 !important;
        }
        #main-container {
            margin-left: 0 !important;
        }
    </style>
<?php } ?>
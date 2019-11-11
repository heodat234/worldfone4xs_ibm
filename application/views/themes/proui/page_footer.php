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

<?php if(ENVIRONMENT != "production") { ?>
<div class="husky-container">
    <img src="<?= STEL_PATH ?>img/husky.png" ?>
    <div class="husky-speech-bubble">Hello, I'm Taco assistant.<br>This is <?= ENVIRONMENT ?> environment.</div>
</div>
<script type="text/javascript">
    function huskyPrompt(ele) {
        swal({
          text: 'Can i help you?',
          icon: $(ele).find("img").attr("src"),
          content: "input",
          button: {
            text: "Go!",
            closeModal: true,
          },
        })
        .then(name => {
          if (!name) return;
          swal({
            title: "Sorry! I can't help.",
          });
        })
    }
</script>
<style type="text/css">
    .husky-container {
        position: fixed; 
        bottom: -50px; 
        left: 60px; 
        cursor: pointer;
    }
    .husky-container:hover,
    .husky-container.show-speech {
        bottom: -10px;
    }
    .husky-container:hover .husky-speech-bubble,
    .husky-container.show-speech .husky-speech-bubble {
        display: inline-block;
    }
    .husky-speech-bubble{
        background-color: #F2F2F2;
        border-radius: 5px;
        box-shadow: 0 0 6px #B2B2B2;
        padding: 10px 18px;
        position: relative;
        vertical-align: top;
        display: none;
    }

    .husky-speech-bubble::before {
        background-color: #F2F2F2;
        content: "\00a0";
        display: block;
        height: 16px;
        position: absolute;
        top: 14px;
        transform:             rotate( 29deg ) skew( -35deg );
            -moz-transform:    rotate( 29deg ) skew( -35deg );
            -ms-transform:     rotate( 29deg ) skew( -35deg );
            -o-transform:      rotate( 29deg ) skew( -35deg );
            -webkit-transform: rotate( 29deg ) skew( -35deg );
        width:  20px;

        box-shadow: -2px 2px 2px 0 rgba( 178, 178, 178, .4 );
        left: -9px;
    }
</style>
<?php } ?>
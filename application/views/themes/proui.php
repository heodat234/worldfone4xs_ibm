<!DOCTYPE html>
<html class="no-js" lang="vi">
    <head>
        <meta charset="utf-8">
        <title><?= $template['title'] ?></title>

        <meta name="description" content="<?= $template['description'] ?>">
        <meta name="author" content="<?= $template['author'] ?>">
        <meta name="robots" content="<?= $template['robots'] ?>">

        <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1.0">

        <base href="<?= base_url() ?>">
        <!-- Icons -->
        <!-- The following icons can be replaced with your own, they are used by desktop and mobile browsers -->
        <link rel="shortcut icon" href="<?= STEL_PATH ?>img/favicon.ico">
        <!-- END Icons -->

        <!-- Stylesheets -->
        <?php foreach($css as $file){ echo '<link rel="stylesheet" href="'.$file.'" type="text/css">'; } ?>

        <!-- Include a specific file here from css/themes/ folder to alter the default theme of the template -->
        <?php if($template['theme']) { ?><link id="theme-link" rel="stylesheet" href="<?= PROUI_PATH . 'css/themes/'.$template['theme'].'.css' ?>"><?php } ?>
        <!-- END Stylesheets -->

        <?php if(isset($currentUri)) { ?>
        <script src="<?= base_url('/js/env?currentUri=').$currentUri ?>"></script>
        <?php } ?>

        <!-- jQuery first for code jQuery in Main -->

        <?php if(!empty($js_nodefer)) { foreach($js_nodefer as $file){ echo '<script type="text/javascript" src="'.$file.'"></script>'; } } ?>

        <!-- JS -->
        <?php foreach($js as $file){ echo '<script type="text/javascript" defer src="'.$file.'"></script>'; } ?>
        <!-- END JS -->
    </head>
    
    <body>
    <!-- Top of HTML include sidebar, toolbar.. -->
    <?php if(isset($page_head)) echo $page_head ?>
    <!-- END Top of HTML -->

    <?= $output ?>

    <!-- Bottom of HTML include modal, copyright.. -->
    <?php if(isset($page_footer)) echo $page_footer ?>

    <!-- Output test -->
    <?php if(isset($modal_output_test)) echo $modal_output_test ?>
    </body>
</html>
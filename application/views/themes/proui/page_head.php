<!-- Page Wrapper -->
<!-- In the PHP version you can set the following options from inc/config file -->
<!--
    Available classes:

    'page-loading'      enables page preloader
-->
<div id="page-wrapper"<?php if ($template['page_preloader']) { echo ' class="page-loading"'; } ?>>
    <!-- Preloader -->
    <!-- Preloader functionality (initialized in js/app.js) - pageLoading() -->
    <!-- Used only if page preloader is enabled from inc/config (PHP version) or the class 'page-loading' is added in #page-wrapper element (HTML version) -->
    <div class="preloader themed-background">
        <h1 class="push-top-bottom text-light text-center"><i><?= $template['name'] ?></i></h1>
        <div class="inner">
            <h3 class="text-light visible-lt-ie9 visible-lt-ie10"><strong>Loading..</strong></h3>
            <div class="preloader-spinner hidden-lt-ie9 hidden-lt-ie10"></div>
        </div>
    </div>
    <!-- END Preloader -->

    <!-- Page Container -->
    <!-- In the PHP version you can set the following options from inc/config file -->
    <!--
        Available #page-container classes:

        '' (None)                                       for a full main and alternative sidebar hidden by default (> 991px)

        'sidebar-visible-lg'                            for a full main sidebar visible by default (> 991px)
        'sidebar-partial'                               for a partial main sidebar which opens on mouse hover, hidden by default (> 991px)
        'sidebar-partial sidebar-visible-lg'            for a partial main sidebar which opens on mouse hover, visible by default (> 991px)
        'sidebar-mini sidebar-visible-lg-mini'          for a mini main sidebar with a flyout menu, enabled by default (> 991px + Best with static layout)
        'sidebar-mini sidebar-visible-lg'               for a mini main sidebar with a flyout menu, disabled by default (> 991px + Best with static layout)

        'sidebar-alt-visible-lg'                        for a full alternative sidebar visible by default (> 991px)
        'sidebar-alt-partial'                           for a partial alternative sidebar which opens on mouse hover, hidden by default (> 991px)
        'sidebar-alt-partial sidebar-alt-visible-lg'    for a partial alternative sidebar which opens on mouse hover, visible by default (> 991px)

        'sidebar-partial sidebar-alt-partial'           for both sidebars partial which open on mouse hover, hidden by default (> 991px)

        'sidebar-no-animations'                         add this as extra for disabling sidebar animations on large screens (> 991px) - Better performance with heavy pages!

        'style-alt'                                     for an alternative main style (without it: the default style)
        'footer-fixed'                                  for a fixed footer (without it: a static footer)

        'disable-menu-autoscroll'                       add this to disable the main menu auto scrolling when opening a submenu

        'header-fixed-top'                              has to be added only if the class 'navbar-fixed-top' was added on header.navbar
        'header-fixed-bottom'                           has to be added only if the class 'navbar-fixed-bottom' was added on header.navbar

        'enable-cookies'                                enables cookies for remembering active color theme when changed from the sidebar links
    -->
    <?php
        $page_classes = '';

        if ($template['header'] == 'navbar-fixed-top') {
            $page_classes = 'header-fixed-top';
        } else if ($template['header'] == 'navbar-fixed-bottom') {
            $page_classes = 'header-fixed-bottom';
        }

        if ($template['sidebar']) {
            $page_classes .= (($page_classes == '') ? '' : ' ') . $template['sidebar'];
        }

        if ($template['main_style'] == 'style-alt')  {
            $page_classes .= (($page_classes == '') ? '' : ' ') . 'style-alt';
        }

        if ($template['footer'] == 'footer-fixed')  {
            $page_classes .= (($page_classes == '') ? '' : ' ') . 'footer-fixed';
        }

        if (!$template['menu_scroll'])  {
            $page_classes .= (($page_classes == '') ? '' : ' ') . 'disable-menu-autoscroll';
        }

        if ($template['cookies'] === 'enable-cookies') {
            $page_classes .= (($page_classes == '') ? '' : ' ') . 'enable-cookies';
        }
    ?>
    <div id="page-container"<?php if ($page_classes) { echo ' class="' . $page_classes . '"'; } ?>>
        <!-- Alternative Sidebar -->
        <div id="sidebar-alt">
            <!-- Wrapper for scrolling functionality -->
            <div id="sidebar-alt-scroll">
                <!-- Sidebar Content -->
                <div class="sidebar-content">
                    <!-- Chat -->
                    <!-- Chat demo functionality initialized in js/app.js -> chatUi() -->
                    <a class="sidebar-title">
                        <strong id="right-title"></strong>
                        <button class="btn btn-sm btn-close pull-right" onclick="closeForm()">
                            <i class="fa fa-times fa-lg"></i>
                        </button> 
                    </a>
                    <!-- Chat Users -->
                    <div class="clearfix">
                        <div id="right-form"><div class="loader-container"><div class="loader"></div></div></div>
                    </div>
                    <!-- END Chat Users -->
                </div>
                <!-- END Sidebar Content -->
                <a href="javascript:void(0)" onclick="toggleForm()" class="toggle-form">
                    <i class="gi gi-chevron-right"></i>
                </a>
            </div>
            <!-- END Wrapper for scrolling functionality -->
        </div>
        <!-- END Alternative Sidebar -->

        <!-- Background loader -->
        <div id="bg-sidebar-alt"></div>
        <div id="bg-loader"></div>
        <!-- END Background loader -->

        <!-- Main Sidebar -->
        <div id="sidebar">
            <!-- Wrapper for scrolling functionality -->
            <div id="sidebar-scroll">
                <!-- Sidebar Content -->
                <div class="sidebar-content">
                    <!-- Brand -->
                    <a href="<?= base_url() ?>" class="sidebar-brand">
                        <img src="<?=STEL_PATH?>img/logo-stel.png" alt="icon" height="30" style="vertical-align: -4px">
                        <span class="sidebar-nav-mini-hide" style="font-size: 30px"><b style="font-weight: bolder">Worldfone</b></span>
                    </a>
                    <!-- END Brand -->

                    <!-- User Info -->
                    <div class="sidebar-section sidebar-user clearfix sidebar-nav-mini-hide">
                        <div class="sidebar-user-avatar">
                            <a href="<?= base_url('/setting/preference') ?>">
                                <img src="<?=PROUI_PATH?>img/placeholders/avatars/avatar.jpg" id="avatar-img" alt="avatar">
                            </a>
                        </div>
                        <div class="sidebar-user-name" data-toggle="tooltip"></div>
                        <div class="sidebar-user-role" style="font-size: 12px"></div>
                        <div class="sidebar-user-links" id="sidebar-widget"></div>
                    </div>
                    <!-- END User Info -->

                    <div id="sidebar-nav-contain">
                        <div class="loader-container"><div class="loader"></div></div>
                    </div>

                </div>
                <!-- END Sidebar Content -->
            </div>
            <!-- END Wrapper for scrolling functionality -->
        </div>
        <!-- END Main Sidebar -->

        <script>
            var sidebarInit = function() {
                let agentName = (ENV.agentname || '');
                $("div.sidebar-user-name").text(agentName).prop("title", agentName);
                $("div.sidebar-user-role").text((ENV.role_name || ''));
                if(ENV.typename) {
                    ENV.brandTitle = ENV.typename;
                    $("a.sidebar-brand").addClass("text-center");
                }
                if(ENV.brandTitle) {
                    $brandTitle = $("a.sidebar-brand span.sidebar-nav-mini-hide");
                    $brandTitle.text(ENV.brandTitle);
                    document.title = ENV.brandTitle;
                    if(ENV.brandTitle.length > 7 && ENV.brandTitle.length < 14) $brandTitle.css("font-size", 23);
                    else if(ENV.brandTitle.length >= 14) $brandTitle.css("font-size", 13);
                }
                if(ENV.brandLogo) {
                    $("a.sidebar-brand img").attr("src", ENV.brandLogo);
                    $("link[rel='shortcut icon']").attr("href", ENV.brandLogo);
                }
            }();
        </script>

        <!-- Main Container -->
        <div id="main-container">
            <!-- Header -->
            <!-- In the PHP version you can set the following options from inc/config file -->
            <!--
                Available header.navbar classes:

                'navbar-default'            for the default light header
                'navbar-inverse'            for an alternative dark header

                'navbar-fixed-top'          for a top fixed header (fixed sidebars with scroll will be auto initialized, functionality can be found in js/app.js - handleSidebar())
                    'header-fixed-top'      has to be added on #page-container only if the class 'navbar-fixed-top' was added

                'navbar-fixed-bottom'       for a bottom fixed header (fixed sidebars with scroll will be auto initialized, functionality can be found in js/app.js - handleSidebar()))
                    'header-fixed-bottom'   has to be added on #page-container only if the class 'navbar-fixed-bottom' was added
            -->
            <?php if(empty($only_main_content)) { ?>
            <header class="navbar<?php if ($template['header_navbar']) { echo ' ' . $template['header_navbar']; } ?><?php if ($template['header']) { echo ' '. $template['header']; } ?>">
            </header>
            <?php } ?>
            <!-- END Header -->
            <!-- Page Main content -->
            <div id="page-content">
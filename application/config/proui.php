<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * config.php
 *
 * Author: pixelcave
 *
 * Configuration file. It contains variables used in the template as well as the primary navigation array from which the navigation is created
 *
 */

/* Template variables */
$config['template'] = array(
    'name'              => 'WorldfoneCMS',
    'version'           => '1.0',
    'author'            => 'dung.huynh@southtelecom.vn',
    'robots'            => 'noindex, nofollow',
    'title'             => 'WorldfoneCMS',
    'description'       => '',
    // true                     enable page preloader
    // false                    disable page preloader
    'page_preloader'    => true,
    // true                     enable main menu auto scrolling when opening a submenu
    // false                    disable main menu auto scrolling when opening a submenu
    'menu_scroll'       => true,
    // 'navbar-default'         for a light header
    // 'navbar-inverse'         for a dark header
    'header_navbar'     => 'navbar-default',
    // ''                       empty for a static layout
    // 'navbar-fixed-top'       for a top fixed header / fixed sidebars
    // 'navbar-fixed-bottom'    for a bottom fixed header / fixed sidebars
    'header'            => '',
    // ''                                               for a full main and alternative sidebar hidden by default (> 991px)
    // 'sidebar-visible-lg'                             for a full main sidebar visible by default (> 991px)
    // 'sidebar-partial'                                for a partial main sidebar which opens on mouse hover, hidden by default (> 991px)
    // 'sidebar-partial sidebar-visible-lg'             for a partial main sidebar which opens on mouse hover, visible by default (> 991px)
    // 'sidebar-mini sidebar-visible-lg-mini'           for a mini main sidebar with a flyout menu, enabled by default (> 991px + Best with static layout)
    // 'sidebar-mini sidebar-visible-lg'                for a mini main sidebar with a flyout menu, disabled by default (> 991px + Best with static layout)
    // 'sidebar-alt-visible-lg'                         for a full alternative sidebar visible by default (> 991px)
    // 'sidebar-alt-partial'                            for a partial alternative sidebar which opens on mouse hover, hidden by default (> 991px)
    // 'sidebar-alt-partial sidebar-alt-visible-lg'     for a partial alternative sidebar which opens on mouse hover, visible by default (> 991px)
    // 'sidebar-partial sidebar-alt-partial'            for both sidebars partial which open on mouse hover, hidden by default (> 991px)
    // 'sidebar-no-animations'                          add this as extra for disabling sidebar animations on large screens (> 991px) - Better performance with heavy pages!
    'sidebar'           => 'sidebar-mini sidebar-visible-lg',
    // ''                       empty for a static footer
    // 'footer-fixed'           for a fixed footer
    'footer'            => '',
    // ''                       empty for default style
    // 'style-alt'              for an alternative main style (affects main page background as well as blocks style)
    'main_style'        => '',
    // ''                           Disable cookies (best for setting an active color theme from the next variable)
    // 'enable-cookies'             Enables cookies for remembering active color theme when changed from the sidebar links (the next color theme variable will be ignored)
    'cookies'           => '',
    // 'night', 'amethyst', 'modern', 'autumn', 'flatie', 'spring', 'fancy', 'fire', 'coral', 'lake',
    // 'forest', 'waterlily', 'emerald', 'blackberry' or '' leave empty for the Default Blue theme
    'theme'             => '',
    // ''                       for default content in header
    // 'horizontal-menu'        for a horizontal menu in header
    // This option is just used for feature demostration and you can remove it if you like. You can keep or alter header's content in page_head.php
    'header_content'    => '',
    /*'active_page'       => basename($_SERVER['PHP_SELF'])*/
);

/* Primary navigation array (the primary navigation will be created automatically based on this array, up to 3 levels deep) */
/*
$config['primary_nav'] = array(
    array(
        'name'  => 'Trang chủ',
        'url'   => '',
        'icon'  => 'gi gi-stopwatch'
    ),
    array(
        'name'  => 'Leads',
        'url'   => 'leads',
        'icon'  => 'fa fa-user-secret'
    ),
    array(
        'name'  => 'Contacts',
        'url'   => 'contacts',
        'icon'  => 'fa fa-user'
    ),
    array(
        'name'  => 'Companies',
        'url'   => 'companies',
        'icon'  => 'fa fa-building'
    ),
    array(
        'name'  => 'Deals',
        'url'   => 'deals',
        'icon'  => 'fa fa-dollar'
    ),
    array(
        'name'  => 'Assets',
        'icon'  => 'gi gi-coins',
        'url' => '',
        'sub'   => array(
            array(
                'name'  => 'Inventory',
                'icon'  => 'gi gi-cargo',
                'url'   => 'assets/inventory',
            ),
            array(
                'name'  => 'Contracts',
                'icon'  => 'gi gi-paperclip',
                'url'   => 'assets/contracts',
            )
        )
    ),
    array(
        'name'  => 'Sales',
        'icon'  => 'gi gi-cart_in',
        'url' => '',
        'sub'   => array(
            array(
                'name'  => 'Products',
                'icon'  => 'gi gi-music',
                'url'   => 'sales/products',
            ),
            array(
                'name'  => 'Services',
                'icon'  => 'gi gi-music',
                'url'   => 'sales/services',
            ),
        )
    ),
    array(
        'name'  => 'User Management',
        'icon'  => 'gi gi-old_man',
        'url' => '',
        'sub'   => array(
            array(
                'name'  => 'Users',
                'icon'  => 'gi gi-group',
                'url'   => 'users',
            ),
            array(
                'name'  => 'Roles',
                'icon'  => 'fa fa-sitemap',
                'url'   => 'users/roles',
            ),
            array(
                'name'  => 'Profiles',
                'icon'  => 'fa fa-sitemap',
                'url'   => 'users/profiles',
            )
        )
    ),
    array(
        'name'  => 'Modules Management',
        'url'   => 'modules',
        'icon'  => 'gi gi-tags'
    ),
    array(
        'name'  => 'Admin Management',
        'url'   => 'admin',
        'icon'  => 'gi gi-tags',
        'sub'   => array(
            array(
                'name'  => 'Navigators',
                'icon'  => 'fa fa-sitemap',
                'url'   => 'admin/navigators',
            ),
            array(
                'name'  => 'Jsondata',
                'icon'  => 'fa fa-sitemap',
                'url'   => 'admin/jsondata',
            )
        )
    )
);
*/
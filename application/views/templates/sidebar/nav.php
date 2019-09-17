<!-- Sidebar Navigation -->
<ul class="sidebar-nav">
    <?php if($primary_nav) foreach( $primary_nav as $key => $link ) {
        $link_class = '';
        $li_active  = '';
        $menu_link  = '';

        // Get 1st level link's vital info
        $uri        = (isset($link['uri']) && $link['uri']) ? $link['uri'] : '';
        $active     = (isset($link['uri']) && ($template['active_page'] == trim($link['uri'], "/"))) ? 'active' : '';
        $icon       = (isset($link['icon']) && $link['icon']) ? '<i class="' . $link['icon'] . ' sidebar-nav-icon"></i>' : '';

        // Check if the link has a submenu
        if (isset($link['sub']) && $link['sub']) {
            // Since it has a submenu, we need to check if we have to add the class active
            // to its parent li element (only if a 2nd or 3rd level link is active)
            foreach ($link['sub'] as $sub_link) {
                if(isset($sub_link['uri'])) 
                {
                    if ($template['active_page'] == trim($sub_link['uri'], "/")) {
                        $li_active = ' class="active"';
        
                    }
                    // Check sub_nav visible
                    if (isset($sub_link['visible']) && $sub_link['visible']) {
                        $menu_link = 'sidebar-nav-menu';
                    }

                    // 3rd level links
                    if (isset($sub_link['sub']) && $sub_link['sub']) {
                        foreach ($sub_link['sub'] as $sub2_link) {
                            if(isset($sub2_link['uri']))
                            {
                                if ($template['active_page'] == $sub2_link['uri']) {
                                    $li_active = ' class="active"';
                                }
                                // Check sub2_nav visible
                                if (isset($sub2_link['visible']) && $sub2_link['visible']) {
                                    $menu_link = 'sidebar-nav-menu';
                                }
                            }
                        }
                    }
                }
            }
        }

        // Create the class attribute for our link
        if ($menu_link || $active) {
            $link_class = ' class="'. $menu_link . $active .'"';
        }
        ?>
        <?php if (isset($link['visible']) && $link['visible']) { ?>
            <?php if ($uri == 'header') { // if it is a header and not a link ?>
            <li class="sidebar-header">
                <?php if (isset($link['icon']) && $link['icon']) { // If the header has options set ?>
                <span class="sidebar-header-options clearfix"><?php echo $icon; ?></span>
                <?php } ?>
                <span class="sidebar-header-title"><?php echo $link['name']; ?></span>
            </li>
            <?php } else { // If it is a link ?>
            <li<?php echo $li_active; ?> data-toggle="tooltip" title="<?php if(isset($link['description'])) echo $link['description'] ?>" data-placement="<?php if(isset($link['sub']) && $link['sub']) echo 'top'; else echo 'right' ?>">
                <a href="<?= base_url($uri) ?>"<?php echo $link_class; ?>><?php if (isset($link['sub']) && $link['sub']) { // if the link has a submenu ?><i class="fa fa-angle-left sidebar-nav-indicator sidebar-nav-mini-hide"></i><?php } echo $icon; ?><span class="sidebar-nav-mini-hide"><?php echo $link['name']; ?></span></a>
                <?php if (isset($link['sub']) && $link['sub']) { // if the link has a submenu ?>
                <ul>
                    <?php foreach ($link['sub'] as $sub_link) {
                        $link_class = '';
                        $li_active = '';
                        $submenu_link = '';

                        // Get 2nd level link's vital info
                        $uri        = (isset($sub_link['uri']) && $sub_link['uri']) ? $sub_link['uri'] : '#';
                        $active     = (isset($sub_link['uri']) && ($template['active_page'] == trim($sub_link['uri'], "/"))) ? ' active' : '';

                        // Check if the link has a submenu
                        if (isset($sub_link['sub']) && $sub_link['sub']) {
                            // Since it has a submenu, we need to check if we have to add the class active
                            // to its parent li element (only if a 3rd level link is active)
                            foreach ($sub_link['sub'] as $sub2_link) {
                                if (in_array($template['active_page'], $sub2_link)) {
                                    $li_active = ' class="active"';
                                    break;
                                }
                                // Check sub2_nav visible
                                if (isset($sub2_link['visible']) && $sub2_link['visible']) {
                                    $submenu_link = 'sidebar-nav-submenu';
                                }
                            }
                        }

                        if ($submenu_link || $active) {
                            $link_class = ' class="'. $submenu_link . $active .'"';
                        }
                    ?>
                    <?php if (isset($sub_link['visible']) && $sub_link['visible']) { ?>
                        <li<?php echo $li_active; ?> data-toggle="tooltip" title="<?php if(isset($sub_link['description'])) echo $sub_link['description'] ?>" data-placement="right">
                            <a href="<?= base_url($uri) ?>" <?php echo $link_class; ?>><?php if(isset($sub_link['icon'])) echo "<i class='{$sub_link['icon']} sidebar-nav-icon'></i>" ?><?php if ($submenu_link) { ?><i class="fa fa-angle-left sidebar-nav-indicator"></i><?php } echo $sub_link['name']; ?></a>
                            <?php if (isset($sub_link['sub']) && $sub_link['sub']) { ?>
                                <ul>
                                    <?php foreach ($sub_link['sub'] as $sub2_link) {
                                        // Get 3rd level link's vital info
                                        $uri    = (isset($sub2_link['uri']) && $sub2_link['uri']) ? $sub2_link['uri'] : '#';
                                        $active = (isset($sub2_link['uri']) && ($template['active_page'] == $sub2_link['uri'])) ? ' class="active"' : '';
                                    ?>
                                        <?php if (isset($sub2_link['visible']) && $sub2_link['visible']) { ?>
                                        <li>
                                            <a href="<?php echo base_url($uri); ?>" <?= $active ?>><?php echo $sub2_link['name']; ?></a>
                                        </li>
                                        <?php } ?>
                                    <?php } ?>
                                </ul>
                            <?php } ?>
                        </li>
                        <?php } ?>
                    <?php } ?>
                </ul>
                <?php } ?>
            </li>
            <?php } ?>
        <?php } ?>
    <?php } ?>
</ul>
<!-- END Sidebar Navigation -->
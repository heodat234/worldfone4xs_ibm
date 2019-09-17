<!-- User Dropdown -->
    <li class="dropdown">
        <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown">
            <img src="<?= PROUI_PATH ?>img/placeholders/avatars/avatar2.jpg" alt="avatar"> <i class="fa fa-angle-down"></i>
        </a>
        <ul class="dropdown-menu dropdown-custom dropdown-menu-right">
            <li class="dropdown-header text-center">Account</li>
            <li>
                <a href="page_ready_timeline.php">
                    <i class="fa fa-clock-o fa-fw pull-right"></i>
                    <span class="badge pull-right">10</span>
                    Updates
                </a>
                <a href="page_ready_inbox.php">
                    <i class="fa fa-envelope-o fa-fw pull-right"></i>
                    <span class="badge pull-right">5</span>
                    Messages
                </a>
                <a href="page_ready_pricing_tables.php"><i class="fa fa-magnet fa-fw pull-right"></i>
                    <span class="badge pull-right">3</span>
                    Subscriptions
                </a>
                <a href="page_ready_faq.php"><i class="fa fa-question fa-fw pull-right"></i>
                    <span class="badge pull-right">11</span>
                    FAQ
                </a>
            </li>
            <li class="divider"></li>
            <li>
                <a href="page_ready_user_profile.php">
                    <i class="fa fa-user fa-fw pull-right"></i>
                    Profile
                </a>
                <!-- Opens the user settings modal that can be found at the bottom of each page (page_footer.php in PHP version) -->
                <a href="#modal-user-settings" data-toggle="modal">
                    <i class="fa fa-cog fa-fw pull-right"></i>
                    Settings
                </a>
            </li>
            <li class="divider"></li>
            <li>
                <a href="page_ready_lock_screen.php"><i class="fa fa-lock fa-fw pull-right"></i> Lock Account</a>
                <a href="<?= base_url("page/signout") ?>"><i class="fa fa-ban fa-fw pull-right"></i> Logout</a>
            </li>
            <li class="dropdown-header text-center">Activity</li>
            <li>
                <div class="alert alert-success alert-alt">
                    <small>5 min ago</small><br>
                    <i class="fa fa-thumbs-up fa-fw"></i> You had a new sale (10)
                </div>
                <div class="alert alert-info alert-alt">
                    <small>10 min ago</small><br>
                    <i class="fa fa-arrow-up fa-fw"></i> Upgraded to Pro plan
                </div>
                <div class="alert alert-warning alert-alt">
                    <small>3 hours ago</small><br>
                    <i class="fa fa-exclamation fa-fw"></i> Running low on space<br><strong>18GB in use</strong> 2GB left
                </div>
                <div class="alert alert-danger alert-alt">
                    <small>Yesterday</small><br>
                    <i class="fa fa-bug fa-fw"></i> <a href="javascript:void(0)" class="alert-link">New bug submitted</a>
                </div>
            </li>
        </ul>
    </li>
    <!-- END User Dropdown -->
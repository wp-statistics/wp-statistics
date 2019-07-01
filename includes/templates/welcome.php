<div class="wrap wps-wrap about-wrap full-width-layout">
    <div class="wp-statistics-welcome">
        <h1><?php printf( __( 'Welcome to WP-Statistics&nbsp;%s', 'wp-statistics' ), WP_Statistics::$reg['version'] ); ?></h1>

        <p class="about-text">
			<?php printf( __( 'Thank you for updating to the latest version! We encourage you to submit a %srating and review%s over at WordPress.org. Your feedback is greatly appreciated!', 'wp-statistics' ), '<a href="https://wordpress.org/support/plugin/wp-statistics/reviews/?rate=5#new-post" target="_blank">', '</a>' ); ?>
			<?php _e( 'Submit your rating:', 'wp-statistics' ); ?>
            <a href="https://wordpress.org/support/plugin/wp-statistics/reviews/?rate=5#new-post" target="_blank"><img src="<?php echo plugins_url( 'wp-statistics/assets/images/stars.png' ); ?>"/></a>
        </p>

        <div class="wp-badge"><?php printf( __( 'Version %s', 'wp-statistics' ), WP_Statistics::$reg['version'] ); ?></div>

        <h2 class="nav-tab-wrapper wp-clearfix">
            <a href="<?php echo add_query_arg( array( 'page' => 'wps_welcome', 'tab' => 'whats-news' ), admin_url( 'admin.php' ) ); ?>" class="nav-tab <?php echo( ( ! isset( $_GET['tab'] ) || ( isset( $_GET['tab'] ) and $_GET['tab'] == "whats-news" ) ) ? "nav-tab-active" : "" ); ?>" data-tab="whats-news"><?php _e( 'New Product', 'wp-statistics' ); ?></a>
            <a href="<?php echo add_query_arg( array( 'page' => 'wps_welcome', 'tab' => 'addons' ), admin_url( 'admin.php' ) ); ?>" class="nav-tab <?php echo( ( isset( $_GET['tab'] ) and $_GET['tab'] == "addons" ) ? "nav-tab-active" : "" ); ?>" data-tab="addons"><?php _e( 'Add-Ons', 'wp-statistics' ); ?></a>
            <a href="<?php echo add_query_arg( array( 'page' => 'wps_welcome', 'tab' => 'credit' ), admin_url( 'admin.php' ) ); ?>" class="nav-tab <?php echo( ( isset( $_GET['tab'] ) and $_GET['tab'] == "credit" ) ? "nav-tab-active" : "" ); ?>" data-tab="credit"><?php _e( 'Credits', 'wp-statistics' ); ?></a>
            <a href="<?php echo add_query_arg( array( 'page' => 'wps_welcome', 'tab' => 'changelog' ), admin_url( 'admin.php' ) ); ?>" class="nav-tab <?php echo( ( isset( $_GET['tab'] ) and $_GET['tab'] == "changelog" ) ? "nav-tab-active" : "" ); ?>" data-tab="changelog"><?php _e( 'Changelog', 'wp-statistics' ); ?></a>
            <a href="https://wp-statistics.com/donate/" class="nav-tab donate" data-tab="link" target="_blank"><?php _e( 'Donate', 'wp-statistics' ); ?></a>
        </h2>

		<?php if ( ! isset( $_GET['tab'] ) || ( isset( $_GET['tab'] ) and $_GET['tab'] == "whats-news" ) ) { ?>
            <div data-content="whats-news" class="tab-content current">
                <section class="center-section">
                    <div class="left">
                        <div class="content-padding">
                            <h2><?php _e( 'WP-Telegram Notifications', 'wp-statistics' ); ?></h2>
                            <h4><?php echo sprintf( __( 'A new plugin from <a href="%s" target="_blank">VeronaLabs</a>.', 'wp-statistics' ), 'https://veronalabs.com' ); ?></h4>

                            <a href="https://wp-telegram.com/purchase/" target="_blank">
                                <img src="<?php echo plugins_url( 'wp-statistics/assets/images/welcome/what-is-new/coupon-code.png' ); ?>"/>
                            </a>
                        </div>
                    </div>
                </section>

                <section class="normal-section">
                    <div class="left">
                        <div class="content-padding">
                            <h2><?php _e( 'Connect with customers', 'wp-statistics' ); ?></h2>
                            <p><?php _e( 'Your customers can easily send their message using the box placed on your website.', 'wp-statistics' ); ?></p>
                        </div>
                    </div>

                    <style>
                        div#wp-telegram-chatbox img {
                            width: 270px;
                            display: inline-block;
                        }
                    </style>

                    <div class="right text-center" id="wp-telegram-chatbox">
                        <img src="<?php echo plugins_url( 'wp-statistics/assets/images/welcome/what-is-new/wp-telegram-chatbox.gif' ); ?>"/>
                        <img src="<?php echo plugins_url( 'wp-statistics/assets/images/welcome/what-is-new/wp-telegram-iphone-2.png' ); ?>"/>
                    </div>
                </section>

                <section class="normal-section">
                    <div class="right">
                        <div class="content-padding">
                            <h2><?php _e( 'Send messages to your channels/Groups', 'wp-statistics' ); ?></h2>
                            <p><?php _e( 'Simply send any message through the WordPress admin panel to your channels or groups on the telegram. No need to add a person as an admin to the channel/group to add send messages.', 'wp-statistics' ); ?></p>
                        </div>
                    </div>

                    <div class="left text-center">
                        <img src="<?php echo plugins_url( 'wp-statistics/assets/images/welcome/what-is-new/wp-telegram-send.png' ); ?>"/>
                    </div>
                </section>

                <section class="normal-section">
                    <div class="left">
                        <div class="content-padding">
                            <h2><?php _e( 'Integrations & Notifications', 'wp-statistics' ); ?></h2>
                            <p><?php _e( 'Integrate with famous plugins such as WooCommerce, CF7, GravityForms, Quforms and EDD.', 'wp-statistics' ); ?></p>
                            <p><?php _e( 'For example: Receive the text of the messages sent in a form created with GravityForm on a particular channel or group. ', 'wp-statistics' ); ?></p>
                        </div>
                    </div>

                    <div class="right text-center">
                        <img src="<?php echo plugins_url( 'wp-statistics/assets/images/welcome/what-is-new/wp-telegram-settings.png' ); ?>"/>
                    </div>
                </section>

                <section class="normal-section">
                    <div class="right">
                        <div class="content-padding">
                            <h2><?php _e( 'Supports multiple channels or groups', 'wp-statistics' ); ?></h2>
                            <p><?php _e( 'You can add as many channels/groups as you want to the panel and arrange different tasks in different channels/groups.', 'wp-statistics' ); ?></p>
                            <p><?php _e( 'For example, different groups for sales, marketing, support or technical team.', 'wp-statistics' ); ?></p>
                        </div>
                    </div>

                    <div class="left text-center">
                        <img src="<?php echo plugins_url( 'wp-statistics/assets/images/welcome/what-is-new/wp-telegram-channels.png' ); ?>"/>
                    </div>
                </section>

                <section class="center-section">
                    <div class="left">
                        <div class="content-padding">
                            <a href="https://wp-telegram.com/purchase/" target="_blank">
                                <img src="<?php echo plugins_url( 'wp-statistics/assets/images/welcome/what-is-new/coupon-code.png' ); ?>"/>
                            </a>
                        </div>
                    </div>
                </section>

                <section class="center-section">
                    <div class="left">
                        <div class="content-padding">
                            <h3>Follow us on Social Media</h3>
                        </div>

                        <a href="https://github.com/veronalabs" target="_blank"><img src="<?php echo plugins_url( 'wp-statistics/assets/images/welcome/icon-github.png' ); ?>"/></a>
                        <a href="https://www.instagram.com/veronalabs/" target="_blank"><img src="<?php echo plugins_url( 'wp-statistics/assets/images/welcome/icon-instagram.png' ); ?>"/></a>
                        <a href="https://www.linkedin.com/company/veronalabs/" target="_blank"><img src="<?php echo plugins_url( 'wp-statistics/assets/images/welcome/icon-linkedin.png' ); ?>"/></a>
                        <a href="https://twitter.com/veronalabs" target="_blank"><img src="<?php echo plugins_url( 'wp-statistics/assets/images/welcome/icon-twitter.png' ); ?>"/></a>
                    </div>
                </section>
            </div>
		<?php } ?>

		<?php if ( isset( $_GET['tab'] ) and $_GET['tab'] == "addons" ) { ?>
            <div data-content="addons" class="tab-content current">
                <section class="center-section">
					<?php include( WP_Statistics::$reg['plugin-dir'] . "includes/templates/add-ons.php" ); ?>
                </section>
            </div>
		<?php } ?>

		<?php if ( isset( $_GET['tab'] ) and $_GET['tab'] == "credit" ) { ?>
            <div data-content="credit" class="tab-content current">
                <div class="about-wrap-content">
                    <p class="about-description"><?php echo sprintf( __( 'WP-Statistics is created by some people and is one of the <a href="%s" target="_blank">VeronaLabs.com</a> projects.', 'wp-statistics' ), 'https://veronalabs.com' ); ?></p>
                    <h3 class="wp-people-group"><?php _e( 'Project Leaders', 'wp-statistics' ); ?></h3>
                    <ul class="wp-people-group ">
                        <li class="wp-person">
                            <a href="https://profiles.wordpress.org/mostafas1990" class="web"><?php echo get_avatar( 'mst404@gmail.com', 62, '', '', array( 'class' => 'gravatar' ) ); ?><?php _e( 'Mostafa Soufi', 'wp-statistics' ); ?></a>
                            <span class="title"><?php _e( 'Original Author', 'wp-statistics' ); ?></span>
                        </li>
                    </ul>
                    <h3 class="wp-people-group"><?php _e( 'Other Contributors', 'wp-statistics' ); ?></h3>
                    <ul class="wp-people-group">
                        <li class="wp-person">
                            <a href="https://profiles.wordpress.org/mehrshaddarzi" class="web"><?php echo get_avatar( 'mehrshad198@gmail.com', 62, '', '', array( 'class' => 'gravatar' ) ); ?><?php _e( 'Mehrshad Darzi', 'wp-statistics' ); ?></a>
                            <span class="title"><?php _e( 'Core Contributor', 'wp-statistics' ); ?></span>
                        </li>
                        <li class="wp-person">
                            <a href="https://profiles.wordpress.org/ghasemi71ir" class="web"><?php echo get_avatar( 'ghasemi71ir@gmail.com', 62, '', '', array( 'class' => 'gravatar' ) ); ?><?php _e( 'Mohammad Ghasemi', 'wp-statistics' ); ?></a>
                            <span class="title"><?php _e( 'Core Contributor', 'wp-statistics' ); ?></span>
                        </li>
                        <li class="wp-person">
                            <a href="https://profiles.wordpress.org/gregross" class="web"><?php echo get_avatar( 'greg@toolstack.com', 62, '', '', array( 'class' => 'gravatar' ) ); ?><?php _e( 'Greg Ross', 'wp-statistics' ); ?></a>
                            <span class="title"><?php _e( 'Core Contributor', 'wp-statistics' ); ?></span>
                        </li>
                        <li class="wp-person">
                            <a href="https://profiles.wordpress.org/dedidata" class="web"><?php echo get_avatar( 'dedidata.com@gmail.com', 62, '', '', array( 'class' => 'gravatar' ) ); ?><?php _e( 'Farhad Sakhaei', 'wp-statistics' ); ?></a>
                            <span class="title"><?php _e( 'Core Contributor', 'wp-statistics' ); ?></span>
                        </li>
                        <li class="wp-person">
                            <a href="https://profiles.wordpress.org/pedromendonca" class="web"><?php echo get_avatar( 'ped.gaspar@gmail.com', 62, '', '', array( 'class' => 'gravatar' ) ); ?><?php _e( 'Pedro Mendonça', 'wp-statistics' ); ?></a>
                            <span class="title"><?php _e( 'Language Contributor', 'wp-statistics' ); ?></span>
                        </li>
                    </ul>

                    <p class="clear"><?php echo sprintf( __( 'WP-Statistics is being developed on GitHub, if you’re interested in contributing to the plugin, please look at the <a href="%s" target="_blank">GitHub page</a>.', 'wp-statistics' ), 'https://github.com/wp-statistics/wp-statistics' ); ?></p>
                    <h3 class="wp-people-group"><?php _e( 'External Libraries', 'wp-statistics' ); ?></h3>
                    <p class="wp-credits-list">
                        <a target="_blank" href="https://maxmind.com/">MaxMind</a>,
                        <a target="_blank" href="https://www.chartjs.org/">Chart.js</a>,
                        <a target="_blank" href="https://whichbrowser.net/">WhichBrowser</a>.</p>
                </div>
            </div>
		<?php } ?>

		<?php if ( isset( $_GET['tab'] ) and $_GET['tab'] == "changelog" ) { ?>
            <div data-content="changelog" class="tab-content current">
				<?php WP_Statistics_Welcome::show_change_log(); ?>
            </div>
		<?php } ?>

        <hr style="clear: both;">
        <div class="wps-return-to-dashboard">
            <a href="<?php echo WP_Statistics_Admin_Pages::admin_url( 'overview' ); ?>"><?php _e( 'Go to Statistics &rarr; Overview', 'wp-statistics' ); ?></a>
        </div>
    </div>
</div>

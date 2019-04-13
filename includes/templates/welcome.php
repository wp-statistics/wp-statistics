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
            <a href="#" class="nav-tab nav-tab-active" data-tab="whats-news"><?php _e( 'What&#8217;s New', 'wp-statistics' ); ?></a>
            <a href="#" class="nav-tab" data-tab="addons"><?php _e( 'Add-Ons', 'wp-statistics' ); ?></a>
            <a href="#" class="nav-tab" data-tab="credit"><?php _e( 'Credits', 'wp-statistics' ); ?></a>
            <a href="#" class="nav-tab" data-tab="changelog"><?php _e( 'Changelog', 'wp-statistics' ); ?></a>
            <a href="https://wp-statistics.com/donate/" class="nav-tab donate" data-tab="link" target="_blank"><?php _e( 'Donate', 'wp-statistics' ); ?></a>
        </h2>

        <div data-content="whats-news" class="tab-content current">
            <section class="center-section">
                <div class="left">
                    <div class="content-padding">
                        <h2><?php _e( 'Improvement update for WP-Statistics', 'wp-statistics' ); ?></h2>
                    </div>
                </div>
            </section>

            <section class="normal-section">
                <div class="left">
                    <div class="content-padding">
                        <h2><?php _e( 'Online Users Widget', 'wp-statistics' ); ?></h2>
                        <p><?php _e( 'A cool widget to show current online users!', 'wp-statistics' ); ?></p>
                    </div>
                </div>

                <div class="right text-center">
                    <img src="<?php echo plugins_url( 'wp-statistics/assets/images/welcome/usersonline-widget.png' ); ?>"/>
                </div>
            </section>

            <section class="normal-section">
                <div class="right">
                    <div class="content-padding">
                        <h2><?php _e( 'Top Referring Sites', 'wp-statistics' ); ?></h2>
                        <p><?php _e( 'Site icon, Server IP and minor improvements.', 'wp-statistics' ); ?></p>
                    </div>
                </div>

                <div class="left text-center">
                    <img src="<?php echo plugins_url( 'wp-statistics/assets/images/welcome/topreferring-widget.png' ); ?>"/>
                </div>
            </section>

            <section class="normal-section">
                <div class="left">
                    <div class="content-padding">
                        <h2><?php _e( 'Top 10 Counties', 'wp-statistics' ); ?></h2>
                        <p><?php _e( 'You can see all visitor of a country!', 'wp-statistics' ); ?></p>
                    </div>
                </div>

                <div class="right text-center">
                    <img src="<?php echo plugins_url( 'wp-statistics/assets/images/welcome/topcountry-widget.png' ); ?>"/>
                </div>
            </section>

            <section class="normal-section" style="border-bottom: 0px none;">
                <div class="left">
                    <div class="content-padding">
                        <h2 style="margin-top: 0;"><?php _e( 'New changes', 'wp-statistics' ); ?></h2>
                    </div>
                </div>

                <div class="right">
                    <ul>
                        <li>Fixed date picker issue in Top Visitors page.</li>
                        <li>Improved: Minor issues.</li>
                    </ul>
                </div>
            </section>
        </div>

        <div data-content="addons" class="tab-content">
            <section class="center-section">
                <?php include( WP_Statistics::$reg['plugin-dir'] . "includes/templates/add-ons.php" ); ?>
            </section>
        </div>

        <div data-content="credit" class="tab-content">
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

        <div data-content="changelog" class="tab-content">
            <?php WP_Statistics_Welcome::show_change_log(); ?>
        </div>
        <hr style="clear: both;">
        <div class="wps-return-to-dashboard">
            <a href="<?php echo WP_Statistics_Admin_Pages::admin_url( 'overview' ); ?>"><?php _e( 'Go to Statistics &rarr; Overview', 'wp-statistics' ); ?></a>
        </div>
    </div>
</div>

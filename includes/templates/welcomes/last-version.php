<div class="wrap about-wrap full-width-layout">
    <div class="wp-statistics-welcome">
        <h1><?php printf( __( 'Welcome to WP-Statistics&nbsp;%s', 'wp-statistics' ), WP_Statistics::$reg['version'] ); ?></h1>

        <p class="about-text">
			<?php printf(
				__(
					'Thank you for updating to the latest version! We encourage you to submit a %srating and review%s over at WordPress.org. Your feedback is greatly appreciated!',
					'wp-statistics'
				),
				'<a href="https://wordpress.org/support/plugin/wp-statistics/reviews/?rate=5#new-post" target="_blank">',
				'</a>'
			); ?>

            <?php _e( 'Submit your rating:', 'wp-statistics' ); ?> <a href="https://wordpress.org/support/plugin/wp-statistics/reviews/?rate=5#new-post" target="_blank"><img src="<?php echo plugins_url( 'wp-statistics/assets/images/stars.png' ); ?>"/></a>
        </p>

        <div class="wp-badge"><?php printf( __( 'Version %s', 'wp-statistics' ), WP_Statistics::$reg['version'] ); ?></div>

        <h2 class="nav-tab-wrapper wp-clearfix">
            <a href="#" class="nav-tab nav-tab-active"
               data-tab="whats-news"><?php _e( 'What&#8217;s New', 'wp-statistics' ); ?></a>
            <a href="#" class="nav-tab" data-tab="credit"><?php _e( 'Credits', 'wp-statistics' ); ?></a>
            <a href="#" class="nav-tab" data-tab="changelog"><?php _e( 'Changelog', 'wp-statistics' ); ?></a>
        </h2>

        <div data-content="whats-news" class="tab-content current">
            <section class="normal-section">
                <div class="right">
                    <div class="content-padding">
                        <h2><?php _e( 'GDPR compliance', 'wp-statistics' ); ?></h2>
                        <h4><?php _e( 'New Privacy tab added in the Setting page and you can see Opt-out option in this section.', 'wp-statistics' ); ?></h4>
                    </div>
                </div>

                <div class="left text-center">
                    <img src="<?php echo plugins_url( 'wp-statistics/assets/images/welcome/gdpr.png' ); ?>"/>
                </div>
            </section>

            <section class="normal-section">
                <div class="left">
                    <div class="content-padding">
                        <h2><?php _e( 'Chart.js Updated!', 'wp-statistics' ); ?></h2>
                        <h4><?php printf( __( 'The Chart.js library was updated to %s', 'wp-statistics' ), 'v2.7.2' ); ?></h4>
                    </div>
                </div>

                <div class="right text-center">
                    <img src="<?php echo plugins_url( 'wp-statistics/assets/images/welcome/chartjs.png' ); ?>"/>
                </div>
            </section>

            <section class="normal-section">
                <div class="left">
                    <div class="content-padding">
                        <h2><?php _e( 'Add-Ons!', 'wp-statistics' ); ?></h2>
                        <h4><?php _e( 'These extensions add functionality to your WP-Statistics.', 'wp-statistics' ); ?></h4>
                    </div>
                </div>

                <div class="right text-center addons-item">
                    <a href="https://wp-statistics.com/downloads/wp-statistics-widgets/" title="Widgets" target="_blank"><img src="<?php echo plugins_url( 'wp-statistics/assets/images/welcome/add-on-widgets.png' ); ?>"/></a>
                    <a href="https://wp-statistics.com/downloads/wp-statistics-mini-chart/" title="Mini Chart" target="_blank"><img src="<?php echo plugins_url( 'wp-statistics/assets/images/welcome/add-on-mini-chart.png' ); ?>"/></a>
                    <a href="https://wp-statistics.com/downloads/wp-statistics-advanced-reporting/" title="Advanced Reporting" target="_blank"><img src="<?php echo plugins_url( 'wp-statistics/assets/images/welcome/add-on-advanced-reporting.png' ); ?>"/></a>
                    <a href="https://wp-statistics.com/downloads/wp-statistics-realtime-stats/" title="Realtime stats" target="_blank"><img src="<?php echo plugins_url( 'wp-statistics/assets/images/welcome/add-on-realtime-stats.png' ); ?>"/></a>
                </div>
            </section>

            <section class="center-section">
                <img src="<?php echo plugins_url( 'wp-statistics/assets/images/welcome/icon-love.png' ); ?>"/>
                <h4><?php echo sprintf( __( 'To help us, you can make <a href="%s" target="_blank">donate</a> or <a href="%s" target="_blank">purchase</a> Add-Ons. 😊', 'wp-statistics' ), 'https://wp-statistics.com/donate/', 'https://wp-statistics.com/add-ons/' ); ?></h4>
            </section>
        </div>

        <div data-content="credit" class="tab-content">
            <div class="about-wrap-content">
                <p class="about-description"><?php echo sprintf( __( 'WP-Statistics is created by some people and is one of the <a href="%s" target="_blank">VeronaLabs.com</a> projects.', 'wp-statistics' ), 'http://veronalabs.com' ); ?></p>
                <h3 class="wp-people-group"><?php _e( 'Project Leaders', 'wp-statistics' ); ?></h3>
                <ul class="wp-people-group ">
                    <li class="wp-person">
                        <a href="https://profiles.wordpress.org/mostafas1990/"
                           class="web"><?php echo get_avatar( 'mst404@gmail.com', 62, '', '', array( 'class' => 'gravatar' ) ); ?><?php _e( 'Mostafa Soufi', 'wp-statistics' ); ?></a>
                        <span class="title"><?php _e( 'Original Author', 'wp-statistics' ); ?></span>
                    </li>
                </ul>
                <h3 class="wp-people-group"><?php _e( 'Other Contributors', 'wp-statistics' ); ?></h3>
                <ul class="wp-people-group">
                    <li class="wp-person">
                        <a href="https://profiles.wordpress.org/gregross/"
                           class="web"><?php echo get_avatar( 'greg@toolstack.com', 62, '', '', array( 'class' => 'gravatar' ) ); ?><?php _e( 'Greg Ross', 'wp-statistics' ); ?></a>
                        <span class="title"><?php _e( 'Core Contributor', 'wp-statistics' ); ?></span>
                    </li>
                    <li class="wp-person">
                        <a href="https://profiles.wordpress.org/dedidata/"
                           class="web"><?php echo get_avatar( 'dedidata.com@gmail.com', 62, '', '', array( 'class' => 'gravatar' ) ); ?><?php _e( 'Farhad Sakhaei', 'wp-statistics' ); ?></a>
                        <span class="title"><?php _e( 'Core Contributor', 'wp-statistics' ); ?></span>
                    </li>
                    <li class="wp-person">
                        <a href="https://profiles.wordpress.org/pedromendonca/"
                           class="web"><?php echo get_avatar( 'ped.gaspar@gmail.com', 62, '', '', array( 'class' => 'gravatar' ) ); ?><?php _e( 'Pedro Mendonça', 'wp-statistics' ); ?></a>
                        <span class="title"><?php _e( 'Language Contributor', 'wp-statistics' ); ?></span>
                    </li>
                </ul>

                <p class="clear"><?php echo sprintf( __( 'WP-Statistics is being developed on GitHub, if you’re interested in contributing to the plugin, please look at the <a href="%s" target="_blank">GitHub page</a>.', 'wp-statistics' ), 'https://github.com/wp-statistics/wp-statistics' ); ?></p>
                <h3 class="wp-people-group"><?php _e( 'External Libraries', 'wp-statistics' ); ?></h3>
                <p class="wp-credits-list">
                    <a href="http://www.maxmind.com/">MaxMind</a>,
                    <a href="https://browscap.org/">Browscap</a>,
                    <a href="http://www.chartjs.org/">Chart.js</a>.</p>
            </div>
        </div>

        <div data-content="changelog" class="one-col tab-content">
			<?php WP_Statistics_Welcome::show_change_log(); ?>
        </div>

        <hr>

        <div class="return-to-dashboard">
            <a href="<?php echo admin_url( 'admin.php?page=wps_overview_page' ); ?>"><?php _e( 'Go to Statistics &rarr; Overview', 'wp-statistics' ); ?></a>
        </div>
    </div>
</div>

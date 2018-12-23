<div class="wrap about-wrap full-width-layout">
    <div class="wp-statistics-welcome">
        <h1><?php printf( __( 'Welcome to WP-Statistics&nbsp;%s', 'wp-statistics' ), WP_Statistics::$reg['version'] ); ?></h1>

        <p class="about-text">
			<?php printf(__('Thank you for updating to the latest version! We encourage you to submit a %srating and review%s over at WordPress.org. Your feedback is greatly appreciated!','wp-statistics'),'<a href="https://wordpress.org/support/plugin/wp-statistics/reviews/?rate=5#new-post" target="_blank">','</a>'); ?>
			<?php _e( 'Submit your rating:', 'wp-statistics' ); ?>
            <a href="https://wordpress.org/support/plugin/wp-statistics/reviews/?rate=5#new-post" target="_blank"><img src="<?php echo plugins_url( 'wp-statistics/assets/images/stars.png' ); ?>"/></a>
        </p>

        <div class="wp-badge"><?php printf( __( 'Version %s', 'wp-statistics' ), WP_Statistics::$reg['version'] ); ?></div>

        <h2 class="nav-tab-wrapper wp-clearfix">
            <a href="#" class="nav-tab nav-tab-active" data-tab="whats-news"><?php _e( 'What&#8217;s New', 'wp-statistics' ); ?></a>
            <a href="#" class="nav-tab" data-tab="credit"><?php _e( 'Credits', 'wp-statistics' ); ?></a>
            <a href="#" class="nav-tab" data-tab="changelog"><?php _e( 'Changelog', 'wp-statistics' ); ?></a>
        </h2>

        <div data-content="whats-news" class="tab-content current">
            <section class="center-section">
                <div class="left">
                    <div class="content-padding">
                        <h2><?php _e( 'Great update for all WP-Statistics Add-Ons', 'wp-statistics' ); ?></h2>
                    </div>
                </div>
            </section>

            <section class="normal-section">
                <div class="left">
                    <div class="content-padding">
                        <h2 style="margin-top: 0"><?php _e( 'View Live Report data with new dashboard', 'wp-statistics' ); ?></h2>
                        <p><?php _e( '<span style="font-weight: bold;">New:</span> Redesign & Optimized for with on the big screen', 'wp-statistics' ); ?></p>
                        <p><?php _e( '<span style="font-weight: bold;">New:</span> Visitor on Map', 'wp-statistics' ); ?></p>
                        <p><?php _e( '<span style="font-weight: bold;">New:</span> Live Chart', 'wp-statistics' ); ?></p>
                        <p><?php _e( '<span style="font-weight: bold;">New:</span> Better Performance', 'wp-statistics' ); ?></p>
                        <p><?php _e( 'And much more!', 'wp-statistics' ); ?></p>

                        <div class="col">
                            <a class="button button-primary button-hero" href="https://wp-statistics.com/downloads/wp-statistics-realtime-stats/" target="_blank">Get Real-Time Stats</a>
                        </div>
                    </div>
                </div>

                <div class="right text-center">
                    <img src="<?php echo plugins_url( 'wp-statistics/assets/images/welcome/addon-realltime-stats.png' ); ?>"/>
                </div>
            </section>

            <section class="normal-section">
                <div class="right">
                    <div class="content-padding">
                        <h2 style="margin-top: 0"><?php _e( 'Advanced Reporting', 'wp-statistics' ); ?></h2>
                        <p><?php _e( 'Now, Fully customized and new options!', 'wp-statistics' ); ?></p>
                        <p><?php _e( 'New: Ability to customize reports based on data widgets.', 'wp-statistics' ); ?></p>
                        <p><?php _e( 'Ability to send preview & test email.', 'wp-statistics' ); ?></p>
                        <p><?php _e( 'Added “Top Countries” stats for sending with the report.', 'wp-statistics' ); ?></p>

                        <div class="col">
                            <a class="button button-primary button-hero" href="https://wp-statistics.com/downloads/wp-statistics-advanced-reporting/" target="_blank">Get Advanced Reporting</a>
                        </div>
                    </div>
                </div>

                <div class="left text-center">
                    <img src="<?php echo plugins_url( 'wp-statistics/assets/images/welcome/addon-advanced-reporting.png' ); ?>"/>
                </div>
            </section>

            <section class="normal-section">
                <div class="left">
                    <div class="content-padding">
                        <h2><?php _e( 'Widgets', 'wp-statistics' ); ?></h2>
                        <p><?php _e( 'The new & beautiful chart includes a caching mechanism and time range settings', 'wp-statistics' ); ?></p>

                        <div class="col">
                            <a class="button button-primary button-hero" href="https://wp-statistics.com/downloads/wp-statistics-widgets/" target="_blank">Get Widgets</a>
                        </div>
                    </div>
                </div>

                <div class="right text-center">
                    <img src="<?php echo plugins_url( 'wp-statistics/assets/images/welcome/addon-widgets.png' ); ?>"/>
                </div>
            </section>

            <section class="normal-section">
                <div class="right">
                    <div class="content-padding">
                        <h2><?php _e( 'Mini Chart', 'wp-statistics' ); ?></h2>
                        <p><?php _e( 'Now include customization and show based on post type settings', 'wp-statistics' ); ?></p>

                        <div class="col">
                            <a class="button button-primary button-hero" href="https://wp-statistics.com/downloads/wp-statistics-mini-chart/" target="_blank">Get Mini Chart</a>
                        </div>
                    </div>
                </div>

                <div class="left text-center">
                    <img src="<?php echo plugins_url( 'wp-statistics/assets/images/welcome/addons-mini-chart.png' ); ?>"/>
                </div>
            </section>

            <section class="center-section">
                <div class="left">
                    <div class="content-padding">
                        <h2>Add-Ons Bundle</h2>
                        <p>Buying a bundle is always a good way to access all add-ons with lower money.<br>According to your requests we have defined all 5 add-ons, in a package with a good discount.</p>
                        <p style="margin: 32px 0;"><a href="https://wp-statistics.com/downloads/add-ons-bundle/" target="_blank"><img src="https://wp-statistics.com/wp-content/uploads/2018/12/welcome-page-bundle.png"/></a></p>

                        <div class="col">
                            <a class="button button-primary button-hero" href="https://wp-statistics.com/downloads/add-ons-bundle/" target="_blank">Get Add-Ons Bundle!</a>
                        </div>
                    </div>
                </div>
            </section>

            <section class="center-section logo">
                <a href="https://veronalabs.com" target="_blank" title="WordPress Solutions and Services"><img src="http://bit.ly/2FsmZlq"/></a>
                <p><?php echo __( 'WP-Statistics is one of the VeronaLabs.com projects.', 'wp-statistics' ); ?></p>
            </section>
        </div>

        <div data-content="credit" class="tab-content">
            <div class="about-wrap-content">
                <p class="about-description"><?php echo sprintf( __( 'WP-Statistics is created by some people and is one of the <a href="%s" target="_blank">VeronaLabs.com</a> projects.', 'wp-statistics' ), 'http://veronalabs.com' ); ?></p>
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

        <div data-content="changelog" class="one-col tab-content">
			<?php WP_Statistics_Welcome::show_change_log(); ?>
        </div>

        <hr>

        <div class="wps-return-to-dashboard">
            <a href="<?php echo admin_url( 'admin.php?page=wps_overview_page' ); ?>"><?php _e( 'Go to Statistics &rarr; Overview', 'wp-statistics' ); ?></a>
        </div>
    </div>
</div>

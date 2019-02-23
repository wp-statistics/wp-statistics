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
            <a href="#" class="nav-tab" data-tab="credit"><?php _e( 'Credits', 'wp-statistics' ); ?></a>
            <a href="#" class="nav-tab" data-tab="changelog"><?php _e( 'Changelog', 'wp-statistics' ); ?></a>
        </h2>

        <div data-content="whats-news" class="tab-content current">
            <section class="center-section">
                <div class="left">
                    <div class="content-padding">
                        <h2><?php _e( 'Great update for all WP-Statistics', 'wp-statistics' ); ?></h2>
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
                            <a class="button button-primary button-hero" href="https://wp-statistics.com/downloads/wp-statistics-realtime-stats/" target="_blank">Get
                                Real-Time Stats</a>
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
                        <h2><?php _e( 'Mini Chart', 'wp-statistics' ); ?></h2>
                        <p><?php _e( 'Now include customization and show based on post type settings', 'wp-statistics' ); ?></p>

                        <div class="col">
                            <a class="button button-primary button-hero" href="https://wp-statistics.com/downloads/wp-statistics-mini-chart/" target="_blank">Get
                                Mini Chart</a>
                        </div>
                    </div>
                </div>

                <div class="left text-center">
                    <img src="<?php echo plugins_url( 'wp-statistics/assets/images/welcome/addons-mini-chart.png' ); ?>"/>
                </div>
            </section>

            <section class="center-section">
                <div class="content-padding">
                    <h2><?php _e( 'Add-Ons', 'wp-statistics' ); ?></h2>
                </div>
                <div id="poststuff" class="wp-statistics-plugins">
                    <div id="post-body" class="metabox-holder">
                        <p><?php _e( 'These extensions add functionality to your WP-Statistics.', 'wp-statistics' ); ?></p><br/>
                        <div class="wp-list-table widefat widefat plugin-install">
                            <div id="the-list">
					            <?php foreach ( $plugins->items as $plugin ) : ?>
                                    <div class="plugin-card">
							            <?php if ( $plugin->is_feature and $plugin->featured_label ) : ?>
                                            <div class="cover-ribbon">
                                                <div class="cover-ribbon-inside"><?php echo $plugin->featured_label; ?></div>
                                            </div>
							            <?php endif; ?>

                                        <div class="plugin-card-top">
                                            <div class="name column-name">
                                                <h3>
                                                    <a target="_blank" href="<?php echo $plugin->url; ?>" class="thickbox open-plugin-details-modal">
											            <?php echo $plugin->name; ?>
                                                        <img src="<?php echo $plugin->icon; ?>" class="plugin-icon" alt="">
                                                    </a>
                                                </h3>
                                            </div>

                                            <div class="desc column-description">
                                                <p><?php echo wp_trim_words( $plugin->description, 15 ); ?></p>
                                            </div>
                                        </div>
                                        <div class="plugin-card-bottom">
                                            <div class="column-downloaded">
                                                <strong><?php _e( 'Version:', 'wp-statistics' ); ?></strong><?php echo ' ' .
									                                                                                   $plugin->version; ?>
                                                <p><strong><?php _e( 'Status:', 'wp-statistics' ); ?></strong>
										            <?php
										            if ( is_plugin_active( $plugin->slug . '/' . $plugin->slug . '.php' ) ) {
											            _e( 'Active', 'wp-statistics' );
										            } else if ( file_exists(
											            WP_PLUGIN_DIR . '/' . $plugin->slug . '/' . $plugin->slug . '.php'
										            ) ) {
											            _e( 'Inactive', 'wp-statistics' );
										            } else {
											            _e( 'Not installed', 'wp-statistics' );
										            }
										            ?>
                                                </p>
                                            </div>
                                            <div class="column-compatibility">
									            <?php if ( is_plugin_active( $plugin->slug . '/' . $plugin->slug . '.php' ) ) { ?>
                                                    <a href="<?php echo WP_Statistics_Admin_Pages::admin_url( 'plugins', array( 'action' => 'deactivate', 'plugin' => $plugin->slug ) ); ?>" class="button"><?php _e( 'Deactivate Add-On', 'wp-statistics' ); ?></a>
									            <?php } else { ?><?php if ( file_exists(
										            WP_PLUGIN_DIR . '/' . $plugin->slug . '/' . $plugin->slug . '.php'
									            ) ) { ?>
                                                    <a href="<?php echo WP_Statistics_Admin_Pages::admin_url( 'plugins', array( 'action' => 'activate', 'plugin' => $plugin->slug ) ); ?>" class="button"><?php _e( 'Activate Add-On', 'wp-statistics' ); ?></a>
									            <?php } else { ?>
                                                    <div class="column-price">
                                                        <strong>$<?php echo $plugin->price; ?></strong>
                                                    </div>
                                                    <a target="_blank" href="<?php echo $plugin->url; ?>" class="button-primary"><?php _e( 'Buy Add-On', 'wp-statistics' ); ?></a>
									            <?php } ?><?php } ?>
                                            </div>
                                        </div>
                                    </div>
					            <?php endforeach; ?>
                            </div>
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

        <div data-content="changelog" class="tab-content">
			<?php WP_Statistics_Welcome::show_change_log(); ?>
        </div>
        <hr>
        <div class="wps-return-to-dashboard">
            <a href="<?php echo WP_Statistics_Admin_Pages::admin_url( 'overview' ); ?>"><?php _e( 'Go to Statistics &rarr; Overview', 'wp-statistics' ); ?></a>
        </div>
    </div>
</div>

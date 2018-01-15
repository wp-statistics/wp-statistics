<div class="wrap">
    <h2><?php esc_html_e( 'Extensions for WP-Statistics', 'wp-statistics' ); ?></h2>

    <div id="poststuff" class="wp-statistics-plugins">
        <div id="post-body"
             class="metabox-holder columns-<?php echo 1 == get_current_screen()->get_columns() ? '1' : '2'; ?>">
            <p><?php _e( 'These extensions add functionality to your WP-Statistics.', 'wp-statistics' ); ?></p>

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
                                        <a target="_blank" href="<?php echo $plugin->url; ?>"
                                           class="thickbox open-plugin-details-modal">
											<?php echo $plugin->name; ?>
                                            <img src="<?php echo $plugin->icon; ?>" class="plugin-icon" alt="">
                                        </a>
                                    </h3>
                                </div>

                                <div class="desc column-description">
                                    <p><?php echo wp_trim_words( $plugin->description, 20 ); ?></p>
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
                                        <a href="admin.php?page=wps_plugins_page&action=deactivate&plugin=<?php echo $plugin->slug; ?>"
                                           class="button"><?php _e( 'Deactivate Add-On', 'wp-statistics' ); ?></a>
									<?php } else { ?>
										<?php if ( file_exists(
											WP_PLUGIN_DIR . '/' . $plugin->slug . '/' . $plugin->slug . '.php'
										) ) { ?>
                                            <a href="admin.php?page=wps_plugins_page&action=activate&plugin=<?php echo $plugin->slug; ?>"
                                               class="button"><?php _e( 'Activate Add-On', 'wp-statistics' ); ?></a>
										<?php } else { ?>
                                            <div class="column-price">
                                                <strong>$<?php echo $plugin->price; ?></strong>
                                            </div>
                                            <a target="_blank" href="<?php echo $plugin->url; ?>"
                                               class="button-primary"><?php _e( 'Buy Add-On', 'wp-statistics' ); ?></a>
										<?php } ?>
									<?php } ?>
                                </div>
                            </div>
                        </div>
					<?php endforeach; ?>
                </div>
            </div>

            <div id="postbox-container-1" class="postbox-container">
                <div class="meta-box-sortables ui-sortable">
                    <div id="wps-plugins-support" class="postbox">
						<?php $paneltitle = __( 'Join to Market!', 'wp-statistics' ); ?>
                        <button class="handlediv" type="button" aria-expanded="true">
							<span class="screen-reader-text"><?php printf(
									__( 'Toggle panel: %s', 'wp-statistics' ),
									$paneltitle
								); ?></span>
                            <span class="toggle-indicator" aria-hidden="true"></span>
                        </button>
                        <h2 class="hndle"><span><?php echo $paneltitle; ?></span></h2>

                        <div class="inside">
                            <p><?php _e(
									'We welcome practical extensions for the WP-Statistics plugin. In case you\'re a WordPress programmer and developer and plan to sell extension in this page, please contact us through the following link.',
									'wp-statistics'
								); ?></p>
                            <a href="http://wp-statistics.com/add-ons/submit" target="_blank" class="button"><?php _e(
									'Submit Add-on',
									'wp-statistics'
								); ?></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
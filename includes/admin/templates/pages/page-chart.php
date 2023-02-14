<div class="postbox-container" id="wps-big-postbox">
    <div class="metabox-holder">
        <div class="meta-box-sortables">
            <div class="postbox" id="<?php echo \WP_STATISTICS\Meta_Box::getMetaBoxKey('pages-chart'); ?>">
                <div class="inside">
                    <!-- Do Js -->
                </div>
            </div>
        </div>
    </div>
</div>

<div id="wps-postbox-container-1" style="float: right" class="postbox-container">
    <div id="side-sortables" class="meta-box-sortables ui-sortable">
        <div class="postbox" id="wp-statistics-pages-widget">
            <div class="postbox-header postbox-toggle">
                <h2 class="hndle wps-d-inline-block"><span><?php _e('Top Browsers', 'wp-statistics'); ?></span></h2>
                <button class="handlediv" type="button" aria-expanded="true">
                    <span class="screen-reader-text"><?php _e('Toggle panel: Top Browsers', 'wp-statistics'); ?></span>
                    <span class="toggle-indicator" aria-hidden="true"></span>
                </button>
            </div>
            <div class="inside wps-wrap">
                <?php echo wp_kses_post($browsers); ?>
            </div>
        </div>

        <div class="postbox" id="wp-statistics-pages-widget">
            <div class="postbox-header postbox-toggle">
                <h2 class="hndle wps-d-inline-block"><span><?php _e('Top Platforms', 'wp-statistics'); ?></span></h2>
                <button class="handlediv" type="button" aria-expanded="true">
                    <span class="screen-reader-text"><?php _e('Toggle panel: Top Platforms', 'wp-statistics'); ?></span>
                    <span class="toggle-indicator" aria-hidden="true"></span>
                </button>
            </div>
            <div class="inside wps-wrap">
                <?php echo wp_kses_post($platforms); ?>
            </div>
        </div>

        <div class="postbox" id="wp-statistics-pages-widget">
            <div class="postbox-header postbox-toggle">
                <h2 class="hndle wps-d-inline-block"><span><?php _e('Top Countries', 'wp-statistics'); ?></span></h2>
                <button class="handlediv" type="button" aria-expanded="true">
                    <span class="screen-reader-text"><?php _e('Toggle panel: Top Countries', 'wp-statistics'); ?></span>
                    <span class="toggle-indicator" aria-hidden="true"></span>
                </button>
            </div>
            <div class="inside wps-wrap">
                <?php echo wp_kses_post($countries); ?>
            </div>
        </div>
    </div>
</div>

<div id="wps-postbox-container-2" style="float: left; margin-left: 0" class="postbox-container">
    <div id="normal-sortables" class="meta-box-sortables ui-sortable">
        <div class="postbox" id="wp-statistics-pages-widget">
            <div class="postbox-header postbox-toggle">
                <h2 class="hndle wps-d-inline-block"><span><?php _e('Visitors Map', 'wp-statistics'); ?></span></h2>
                <button class="handlediv" type="button" aria-expanded="true">
                    <span class="screen-reader-text"><?php _e('Toggle panel: Visitors Map', 'wp-statistics'); ?></span>
                    <span class="toggle-indicator" aria-hidden="true"></span>
                </button>
            </div>
            <div class="inside wps-wrap">
                <?php echo wp_kses_post($visitors_map); ?>
            </div>
        </div>

        <div class="postbox" id="wp-statistics-pages-widget">
            <div class="postbox-header postbox-toggle">
                <h2 class="hndle wps-d-inline-block"><span><?php _e('Online Users', 'wp-statistics'); ?></span></h2>
                <button class="handlediv" type="button" aria-expanded="true">
                    <span class="screen-reader-text"><?php _e('Toggle panel: Online Users', 'wp-statistics'); ?></span>
                    <span class="toggle-indicator" aria-hidden="true"></span>
                </button>
            </div>
            <div class="inside wps-wrap">
                <?php echo wp_kses_post($useronline); ?>
            </div>
        </div>

        <div class="postbox" id="wp-statistics-pages-widget">
            <div class="postbox-header postbox-toggle">
                <h2 class="hndle wps-d-inline-block"><span><?php _e('Latest Visitors', 'wp-statistics'); ?></span></h2>
                <button class="handlediv" type="button" aria-expanded="true">
                    <span class="screen-reader-text"><?php _e('Toggle panel: Latest Visitors', 'wp-statistics'); ?></span>
                    <span class="toggle-indicator" aria-hidden="true"></span>
                </button>
            </div>
            <div class="inside wps-wrap">
                <?php echo wp_kses_post($visitors); ?>
            </div>
        </div>

        <div class="postbox" id="wp-statistics-pages-widget">
            <div class="postbox-header postbox-toggle">
                <h2 class="hndle wps-d-inline-block"><span><?php _e('Top Visitors', 'wp-statistics'); ?></span></h2>
                <button class="handlediv" type="button" aria-expanded="true">
                    <span class="screen-reader-text"><?php _e('Toggle panel: Top Visitors', 'wp-statistics'); ?></span>
                    <span class="toggle-indicator" aria-hidden="true"></span>
                </button>
            </div>
            <div class="inside wps-wrap">
                <?php echo wp_kses_post($top_visitors); ?>
            </div>
        </div>

        <div class="postbox" id="wp-statistics-pages-widget">
            <div class="postbox-header postbox-toggle">
                <h2 class="hndle wps-d-inline-block"><span><?php _e('Top Referring', 'wp-statistics'); ?></span></h2>
                <button class="handlediv" type="button" aria-expanded="true">
                    <span class="screen-reader-text"><?php _e('Toggle panel: Top Referring', 'wp-statistics'); ?></span>
                    <span class="toggle-indicator" aria-hidden="true"></span>
                </button>
            </div>
            <div class="inside wps-wrap">
                <?php echo wp_kses_post($referring); ?>
            </div>
        </div>
    </div>
</div>
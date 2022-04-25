<div class="postbox-container" id="wps-big-postbox">
    <div class="metabox-holder">
        <div class="meta-box-sortables">
            <div class="postbox" id="<?php echo \WP_STATISTICS\Meta_Box::getMetaBoxKey('pages-chart'); ?>">
                <div class="inside">
                    <!-- Do Js -->
                </div>
            </div>
        </div>
    </div
</div>

<div class="postbox-container wps-postbox-full">
    <div class="metabox-holder">
        <div class="meta-box-sortables">
            <div class="postbox" id="wp-statistics-pages-widget">
                <button class="handlediv" type="button" aria-expanded="true">
                    <span class="screen-reader-text">Toggle panel: Top Pages</span>
                    <span class="toggle-indicator" aria-hidden="true"></span>
                </button>

                <h2 class="hndle wps-d-inline-block"><span>Visitors</span></h2>
                <div class="inside wps-wrap">
                    <?php echo $visitors; ?>
                </div>
            </div>
        </div>
    </div>
</div>
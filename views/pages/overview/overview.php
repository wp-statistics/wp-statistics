<div class="metabox-holder" id="overview-widgets">
    <div class="postbox-container" id="wps-postbox-container-1">
        <?php do_meta_boxes(get_current_screen(), 'side', ''); ?>
    </div>

    <div class="postbox-container" id="wps-postbox-container-2">
        <?php do_meta_boxes(get_current_screen(), 'normal', ''); ?>
    </div>
</div>

<?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false); ?>
<?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false); ?>

<script type="text/javascript">
    jQuery(document).ready(function () {
        jQuery('.if-js-closed').removeClass('if-js-closed').addClass('closed');
        postboxes.add_postbox_toggles('<?php echo esc_js(get_current_screen()->id); ?>');
    });
</script>
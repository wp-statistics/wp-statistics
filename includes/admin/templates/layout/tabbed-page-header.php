<div class="wps-wrap__main">
    <div class="wps-page-header">
        <h2 class="wps_title"><?php echo(isset($title) ? esc_attr($title) : (function_exists('get_admin_page_title') ? get_admin_page_title() : '')); ?></h2>
        <?php do_action('wp_statistics_after_admin_page_title'); ?>
        <div class="wp-clearfix"></div>
        <div class="wps-datepicker">
            <!-- datepicker -->
        </div>
        <?php if (!empty($tabs) && is_array($tabs)) { ?>
            <ul class="wps-tabs">
                <?php foreach ($tabs as $tab) { ?>
                    <li class="wps-tab-link <?php echo $tab['class'] ?>"><a href="<?php echo $tab['link'] ?>"><?php echo $tab['title'] ?></a></li>
                <?php } ?>
            </ul>
        <?php } ?>
    </div>
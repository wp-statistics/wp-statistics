<div class="wps-wrap__main">
    <div class="wps-page-header">
        <h2 class="wps_title">
            <span><?php echo(isset($title) ? esc_html($title) : (function_exists('get_admin_page_title') ? get_admin_page_title() : '')); ?></span>
            <?php if (!empty($tooltip)) : ?>
                <span class="wps-tooltip" title="<?php echo esc_attr($tooltip); ?>"><i class="wps-tooltip-icon info"></i></span>
            <?php endif; ?>
        </h2>

        <p class="wps_description"><?php echo !empty($description) ? esc_html($description) : '' ?></p>

        <?php do_action('wp_statistics_after_admin_page_title'); ?>
        <div class="wp-clearfix"></div>
        <div class="wps-datepicker">
            <?php include 'date.range.php'; ?>
        </div>
        <?php if (!empty($tabs) && is_array($tabs)) { ?>
            <ul class="wps-tabs">
                <?php foreach ($tabs as $tab) { ?>
                    <li class="wps-tab-link <?php echo esc_attr($tab['class']); ?>">
                        <a href="<?php echo esc_attr($tab['link']); ?>">
                            <?php echo esc_html($tab['title']); ?>
                            <?php if (!empty($tab['tooltip'])) : ?>
                                <span class="wps-tooltip" title="<?php echo esc_attr($tab['tooltip']); ?>"><i class="wps-tooltip-icon info"></i></span>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php } ?>
            </ul>
        <?php } ?>
    </div>
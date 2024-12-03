<?php

use WP_STATISTICS\Helper;

$queryKey = 'pt';
?>

<div class="wps-filter-post-type wps-head-filters__item loading">
    <div class="wps-dropdown">
        <label class="selectedItemLabel"><?php echo esc_html($title) ?>: </label>
        <button type="button" class="dropbtn"><span><?php echo $selectedOption ? esc_html(Helper::getPostTypeName($selectedOption)) : esc_html__('All', 'wp-statistics'); ?></span></button>
        <div class="dropdown-content">
            <a href="<?php echo esc_url($baseUrl) ?>" data-index="0" class="<?php echo !$selectedOption ? 'selected' : '' ?>"><?php esc_html_e('All', 'wp-statistics'); ?></a>
            <?php foreach ($data as $key => $item) : ?>
                <?php
                $url  = add_query_arg([$queryKey => $item], $baseUrl);
                $name = Helper::getPostTypeName($item);

                $class   = [];
                $class[] = $selectedOption == $item ? 'selected' : '';

                if ($type === 'post-types') {
                    $class[] = !empty($lockCustomPostTypes) && !Helper::isAddOnActive('data-plus') && Helper::isCustomPostType($postType) ? 'disabled' : '';
                } else {
                    $class[] = Helper::isCustomPostType($item) && !Helper::isAddOnActive('data-plus') ? 'disabled' : '';
                }

                ?>
                <?php if (Helper::isCustomPostType($item) && !Helper::isAddOnActive('data-plus')): ?>
                    <a data-target="wp-statistics-data-plus" title="<?php echo esc_attr($name) ?>" class="js-wps-openPremiumModal <?php echo esc_attr(implode(' ', $class)) ?>">
                        <?php echo esc_html(ucwords($name)) ?>
                    </a>
                <?php else: ?>
                    <a href="<?php echo esc_url($url) ?>" data-index="<?php echo esc_attr($key) ?>" title="<?php echo esc_attr($name) ?>" class="<?php echo esc_attr(implode(' ', $class)) ?>">
                        <?php echo esc_html($name) ?>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>
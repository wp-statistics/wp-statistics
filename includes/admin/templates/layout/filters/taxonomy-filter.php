<?php
use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Request;

$queryKey         = 'tx';
$selectedOption   = Request::get($queryKey, 'category');
$taxonomies       = Helper::get_list_taxonomy(true);
?>

<div class="wps-filter-taxonomy wps-head-filters__item loading">
    <div class="wps-dropdown">
        <label class="selectedItemLabel"><?php esc_html_e('Taxonomy:', 'wp-statistics'); ?> </label>
        <button type="button" class="dropbtn"><span><?php echo isset($taxonomies[$selectedOption]) ? esc_html(ucwords($taxonomies[$selectedOption])) : '—'; ?></span></button>

        <div class="dropdown-content">
            <?php $index = 0; ?>
            <?php foreach ($taxonomies as $key => $name) : ?>
                <?php $url = add_query_arg([$queryKey => $key]); ?>

                <?php
                $common_class = $selectedOption == $key ? 'selected' : '';
                $disabled_class = Helper::isAddOnActive('data-plus') || Helper::isCustomTaxonomy($key) ? 'disabled' : '';
                ?>

                <a href="<?php echo esc_url($url) ?>" data-index="<?php echo esc_attr($index) ?>" title="<?php echo esc_attr($name) ?>" class="<?php echo esc_attr(trim("$common_class $disabled_class")) ?>">
                    <?php echo esc_html(ucwords($name)) ?>
                </a>

                <?php $index++; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>
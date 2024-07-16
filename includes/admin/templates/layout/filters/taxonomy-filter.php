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
            <?php 
                $index = 0;
                foreach ($taxonomies as $key => $name) : 
                    $url     = add_query_arg([$queryKey => $key]); 

                    $class   = [];
                    $class[] = $selectedOption == $key ? 'selected' : '';
                    $class[] = Helper::isCustomTaxonomy($key) && !Helper::isAddOnActive('data-plus') ? 'disabled' : '';
                    ?>
                        <a href="<?php echo esc_url($url) ?>" data-index="<?php echo esc_attr($index) ?>" title="<?php echo esc_attr($name) ?>" class="<?php echo esc_attr(implode(' ', $class)) ?>">
                            <?php echo esc_html(ucwords($name)) ?>
                        </a>
                    <?php 

                    $index++; 
                endforeach; 
            ?>
        </div>
    </div>
</div>
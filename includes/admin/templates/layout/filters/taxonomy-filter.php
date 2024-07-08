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
                
                <?php if (!Helper::isAddOnActive('data-plus') && !Helper::isCustomTaxonomy($key)) : ?>
                    <a href="<?php echo esc_url($url) ?>" data-index="<?php echo esc_attr($index) ?>" title="<?php echo esc_attr($name) ?>" class="<?php echo $selectedOption == $key ? 'selected' : '' ?>">
                        <?php echo esc_html(ucwords($name)) ?>
                    </a>
                <?php else : ?>
                    <a href="<?php echo esc_url($url) ?>" data-index="<?php echo esc_attr($index) ?>" title="<?php echo esc_attr($name) ?>" class="<?php echo $selectedOption == $key ? 'selected' : '' ?>">
                        <?php echo esc_html(ucwords($name)) ?>
                        
                        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="12" viewBox="0 0 11 12" fill="none">
                            <path d="M9.33333 4.66667H9.91667C10.2388 4.66667 10.5 4.92783 10.5 5.25V11.0833C10.5 11.4055 10.2388 11.6667 9.91667 11.6667H0.583333C0.26117 11.6667 0 11.4055 0 11.0833V5.25C0 4.92783 0.26117 4.66667 0.583333 4.66667H1.16667V4.08333C1.16667 1.82817 2.99484 0 5.25 0C7.50517 0 9.33333 1.82817 9.33333 4.08333V4.66667ZM8.16667 4.66667V4.08333C8.16667 2.47251 6.86082 1.16667 5.25 1.16667C3.63917 1.16667 2.33333 2.47251 2.33333 4.08333V4.66667H8.16667ZM4.66667 7V9.33333H5.83333V7H4.66667Z" fill="#93979F"/>
                        </svg>
                    </a>
                <?php endif; ?>

                <?php $index++; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>
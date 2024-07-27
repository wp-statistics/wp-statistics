<?php

use WP_STATISTICS\Helper;
use WP_Statistics\Models\ViewsModel;
use WP_Statistics\Utils\Request;

$isDataPlusActive = Helper::isAddOnActive('data-plus');

$queryKey   = 'qp';
$selected   = Request::get($queryKey);
$baseUrl    = remove_query_arg([$queryKey]);

$postId = Request::get('post_id', '', 'number');

$viewsModel     = new ViewsModel();
$filterOptions  = $viewsModel->getViewedPageUri(['id' => $postId]);

$pageSlug = get_page_uri($postId);

if ($selected) {
    $selectedIndex  = array_search($selected, array_column($filterOptions, 'page_id'));
    $selectedTitle  = $selectedIndex !== false ? $filterOptions[$selectedIndex]->uri : false;
}
?>

<div class="wps-filter-query-params wps-head-filters__item loading <?php echo !$isDataPlusActive ? 'disabled' : '' ?>">
    <div class="wps-dropdown">
        <label class="selectedItemLabel"><?php esc_html_e('Query Parameter:', 'wp-statistics'); ?> </label>
        <button type="button" class="dropbtn"><span><?php echo !empty($selectedTitle) ? esc_html($selectedTitle) : esc_html__('All', 'wp-statistics'); ?></span></button>

        <?php if ($isDataPlusActive) : ?>
            <div class="dropdown-content">
                <input type="text" class="wps-search-dropdown">
                <a href="<?php echo esc_url($baseUrl) ?>" data-index="0" class="<?php echo !$selected ? 'selected' : '' ?>"><?php esc_html_e('All', 'wp-statistics'); ?></a>
    
                <?php foreach ($filterOptions as $key => $item) : ?>
                    <?php 
                        $title  = preg_replace('/^.*' . preg_quote($pageSlug, '/') . '/', '', $item->uri);
                        $url    = add_query_arg([$queryKey => $item->page_id], $baseUrl);
                    ?>
    
                    <a href="<?php echo esc_url($url) ?>" data-index="<?php echo esc_attr($key + 1) ?>" title="<?php echo esc_attr($item->uri) ?>" class="dropdown-item <?php echo $selected == $item->page_id ? 'selected' : '' ?>">
                        <?php echo !empty($title) ? esc_html($title) : esc_html($item->uri) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
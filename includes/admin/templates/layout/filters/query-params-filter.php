<?php
use WP_Statistics\Utils\Query;
use WP_Statistics\Utils\Request;

$queryKey   = 'qp';
$selected   = Request::get($queryKey);
$baseUrl    = remove_query_arg([$queryKey]);

$options    = Query::select([
        'uri',
        'page_id',
        'SUM(count) AS total'
    ])
    ->from('pages')
    ->where('id', '=', Request::get('post_id'))
    ->groupBy('uri')
    ->orderBy('total')
    ->getAll();
?>

<div class="wps-filter-query-params wps-head-filters__item loading">
    <div class="wps-dropdown">
        <label class="selectedItemLabel"><?php esc_html_e('Query Parameter:', 'wp-statistics'); ?> </label>
        <button type="button" class="dropbtn"><span><?php echo $selected ? esc_html($selected) : esc_html__('All', 'wp-statistics'); ?></span></button>
        <div class="dropdown-content">
            <a href="<?php echo esc_url($baseUrl) ?>" data-index="0" class="<?php echo !$selected ? 'selected' : '' ?>"><?php esc_html_e('All', 'wp-statistics'); ?></a>

            <?php foreach ($options as $key => $item) : ?>
                <?php $url = add_query_arg([$queryKey => $item->page_id], $baseUrl); ?>

                <a href="<?php echo esc_url($url) ?>" data-index="<?php echo esc_attr($key + 1) ?>" title="<?php echo esc_attr($item->uri) ?>" class="<?php echo $selected == $item->page_id ? 'selected' : '' ?>">
                    <?php echo esc_html($item->uri) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>
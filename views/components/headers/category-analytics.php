<?php

use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Request;

$termId = Request::get('term_id');
$term   = get_term($termId);

$termName = Helper::getTaxonomyName($term->taxonomy, true);
$termLink = get_term_link($term);
?>
<div class="wps-category-analytics--header">
    <div class="wps-category-analytics--header__title">
        <h2 class="wps_title"><span><?php echo esc_html($termName) . ': '; ?></span>"<?php echo esc_html($term->name); ?>"</h2>
        <a href="<?php echo esc_url($termLink); ?>" target="_blank" title="<?php echo esc_html($term->name); ?>"></a>
    </div>
    <div class="wps-category-analytics--header__tags">
        <a href="<?php echo esc_url($termLink); ?>" target="_blank"><?php echo esc_html($termName); ?></a>
    </div>
</div>
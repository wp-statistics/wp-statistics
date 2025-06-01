<?php
use WP_Statistics\Utils\Request;
$uri = Request::get('uri', '', 'raw');
$uri = rawurldecode($uri);
?>

<div class="wps-content-analytics-header wps-content-analytics-header__single-resource">
    <div>
        <div class="wps-content-analytics-header__title">
            <h2 class="wps_title"><?php echo esc_html($uri); ?></h2>
            <a href="<?php echo esc_url(home_url($uri)); ?>" target="_blank" title="<?php echo esc_attr($uri); ?>"></a>
        </div>
    </div>
</div>
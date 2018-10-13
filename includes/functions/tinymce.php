<?php
if ( ! defined( 'ABSPATH' ) )
    exit;

function wp_statistic_tinymce_plugin_translation() {
    $lang = WP_Statistics_TinyMCE::lang();
    $translated = $lang['translate'];
    return $translated;
}
$strings = wp_statistic_tinymce_plugin_translation();
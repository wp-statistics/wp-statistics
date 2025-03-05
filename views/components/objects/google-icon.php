<a href="<?= isset($link) ? esc_url($link) : '' ?>"
   target="_blank"
   class="<?= isset($title) ? 'wps-tooltip' : '' ?>"
    <?= isset($title) ? 'title="' . esc_html__('Last updated:', 'wp-statistics') . ' ' .esc_html($title) . '"' : '' ?>>
    <span class="wps_search-console_icon"></span>
</a>
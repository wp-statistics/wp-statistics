<a href="<?= isset($link) ? esc_url($link) : '' ?>"
   target="_blank"
   class="<?= isset($title) || isset($tooltip) ? 'wps-tooltip' : '' ?>"
    <?= isset($title) ? 'title="' . esc_html__('Last updated:', 'wp-statistics') . ' ' .esc_html($title) . '"' : '' ?>
    <?= isset($tooltip) ? 'title="' .esc_html($tooltip). '"' : '' ?>
    >
    <span class="wps_search-console_icon"></span>
    <p class="screen-reader-text"><?php echo esc_html__('Search Console', 'wp-statistics') ?></p>
</a>
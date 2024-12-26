<?php
/**
 * Filter Lists Component
 *
 * @param array $filters Array of filters. Each filter should have optional 'title' and 'content'.
 */
if (!isset($filters) || empty($filters)) {
    return;
}
?>
<div class="wps-postbox-tracker__filter-lists">
    <?php foreach ($filters as $filter): ?>
        <div class="wps-postbox-tracker__filter-list">
            <?php if (!empty($filter['title'])): ?>
                <h3 class="wps-postbox-tracker__filter-title"><?php echo esc_html($filter['title']); ?>:</h3>
            <?php endif; ?>
            <div class="wps-postbox-tracker__filter-content">
                <?php echo wp_kses_post($filter['content']); ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
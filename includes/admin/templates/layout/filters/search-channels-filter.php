<?php
use WP_STATISTICS\Helper;
use WP_Statistics\Service\Analytics\Referrals\SourceChannels;
use WP_Statistics\Utils\Request;

$selected       = Request::get('source_channel');
$channels       = Helper::filterArrayByKeys(SourceChannels::getList(), ['search', 'paid_search']);
$selectedTitle  = $channels[$selected] ?? null;
?>

<div class="wps-head-filters__item loading">
    <div class="wps-dropdown">
        <label class="selectedItemLabel"><?php esc_html_e('Source Category', 'wp-statistics'); ?>: </label>
        <button type="button" class="dropbtn"><span><?php echo !empty($selectedTitle) ? esc_html($selectedTitle) : esc_html__('All', 'wp-statistics'); ?></span></button>

        <div class="dropdown-content">
            <input type="text" class="wps-search-dropdown">
            <a href="<?php echo esc_url(remove_query_arg('source_channel')); ?>" data-index="0" class="<?php echo !isset($selected) ? 'selected' : '' ?>"><?php esc_html_e('All', 'wp-statistics'); ?></a>

            <?php foreach ($channels as $key => $value) : ?>
                <a href="<?php echo esc_url(add_query_arg('source_channel', $key)); ?>" class="dropdown-item <?php echo $selected === $key ? 'selected' : ''; ?>">
                    <?php echo esc_html($value); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>
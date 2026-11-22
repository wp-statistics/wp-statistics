<?php
/**
 * Shared header of single reports: breadcrumb, short title, facts line.
 * Rendered by includes/admin/templates/layout/title.php; build the array with
 * WP_Statistics\Service\Admin\PageHeader\PageHeader::resolve() (shape documented there).
 *
 * Args:
 *  - header (array) Normalized header.
 */

use WP_Statistics\Components\View;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (empty($header) || !is_array($header)) {
    return;
}

$wpsPageHeaderSvg = [
    'clock'    => '<path d="M12 8v4l2.5 2.5"/><circle cx="12" cy="12" r="8"/>',
    'calendar' => '<rect x="4" y="5" width="16" height="15" rx="2"/><path d="M4 10h16M9 3v4M15 3v4"/>',
    'user'     => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
    'lock'     => '<rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/>',
];
?>
<div class="wps-page-header">
    <?php if (!empty($header['crumbs'])) : ?>
        <nav class="wps-page-header__crumbs" aria-label="<?php esc_attr_e('Breadcrumb', 'wp-statistics'); ?>">
            <ol>
                <?php foreach ($header['crumbs'] as $crumb) : ?>
                    <li>
                        <?php if (!empty($crumb['url'])) : ?>
                            <a href="<?php echo esc_url($crumb['url']); ?>"><?php echo esc_html($crumb['label']); ?></a>
                        <?php else : ?>
                            <span><?php echo esc_html($crumb['label']); ?></span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ol>
        </nav>
    <?php endif; ?>

    <div class="wps-page-header__heading">
        <?php if (!empty($header['icon'])) : ?>
            <img class="wps-page-header__icon wps-page-header__icon--<?php echo esc_attr($header['icon']['shape'] ?? 'logo'); ?>" src="<?php echo esc_url($header['icon']['src']); ?>" alt="<?php echo esc_attr($header['icon']['alt'] ?? ''); ?>">
        <?php endif; ?>
        <h2 class="wps_title wps-page-header__title" title="<?php echo esc_attr($header['title']); ?>"><?php echo esc_html($header['title']); ?></h2>
        <?php if (!empty($header['link'])) {
            View::load('components/objects/new-tab-link', ['url' => $header['link'], 'label' => $header['link_label'] ?? '', 'class' => 'wps-page-header__link']);
        } ?>
        <?php if (!empty($header['tag'])) {
            View::load('components/objects/pill', ['text' => $header['tag'], 'variant' => 'tag', 'class' => 'wps-page-header__tag']);
        } ?>
        <?php if (!empty($header['tooltip'])) : ?>
            <span class="wps-tooltip" title="<?php echo esc_attr($header['tooltip']); ?>"><i class="wps-tooltip-icon info"></i></span>
        <?php endif; ?>
    </div>

    <?php if (!empty($header['facts'])) : ?>
        <ul class="wps-page-header__facts">
            <?php foreach ($header['facts'] as $fact) :
                $classes = 'wps-page-header__fact' . (!empty($fact['tag']) ? ' wps-page-header__fact--tag' : '');
                $inner   = '';

                if (!empty($fact['icon'])) {
                    $inner .= sprintf('<img class="wps-page-header__fact-icon wps-page-header__fact-icon--%s" src="%s" alt="">', esc_attr($fact['shape'] ?? 'logo'), esc_url($fact['icon']));
                } elseif (!empty($fact['svg']) && isset($wpsPageHeaderSvg[$fact['svg']])) {
                    $inner .= '<svg class="wps-page-header__fact-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $wpsPageHeaderSvg[$fact['svg']] . '</svg>';
                }

                $inner .= '<span>' . esc_html($fact['text']) . '</span>';
                $title  = !empty($fact['title']) ? ' title="' . esc_attr($fact['title']) . '"' : '';
                ?>
                <li class="<?php echo esc_attr($classes); ?>"<?php echo $title; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
                    <?php if (!empty($fact['tag'])) : ?>
                        <?php View::load('components/objects/pill', ['text' => $fact['text'], 'url' => $fact['url'] ?? '', 'variant' => 'tag']); ?>
                    <?php elseif (!empty($fact['url'])) : ?>
                        <a href="<?php echo esc_url($fact['url'], ['http', 'https', 'mailto']); ?>"<?php echo !empty($fact['target']) ? ' target="' . esc_attr($fact['target']) . '" rel="noopener"' : ''; ?>><?php echo $inner; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
                    <?php else : ?>
                        <?php echo $inner; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

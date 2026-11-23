<?php
/**
 * Pages cell for visitor tables: the entry page on line 1, the exit page on line 2,
 * each after a small icon (no "In"/"Out" words; the icon carries the label as a title
 * and as screen-reader text). Each line cuts with an ellipsis; the full title shows on
 * hover (assets/dev/javascript/components/title-cell.js).
 *
 *  - Same entry and exit page: one line with the combined icon, plus a "1 page" tag
 *    when the visitor had one view.
 *  - Exit not recorded: the second line says "Not recorded" (muted).
 *  - Neither page known: the unknown "-" value.
 *  - A utm_campaign on the entry URL adds the campaign tag after the entry page.
 *
 * Args (all optional except a visitor or the pages):
 *  - visitor     (VisitorDecorator) Reads entry/exit pages and views from it.
 *  - entry, exit (array|null)       Page arrays from Visitor::get_page_by_id() (report, title, query),
 *                                   used when no visitor is given.
 *  - views       (int)              Views of the visit, for the "1 page" tag.
 *  - exit_label  (string)           Accessible name of the exit icon (default "Exit page";
 *                                   the Online table passes "Current page").
 *
 * Example:
 *  View::load('components/objects/visitor-pages', ['visitor' => $visitor]);
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_STATISTICS\Admin_Template;
use WP_STATISTICS\Helper;
use WP_Statistics\Components\View;
use WP_Statistics\Decorators\VisitorDecorator;
use WP_Statistics\Utils\Url;

$entryPage = null;
$exitPage  = null;
$samePage  = false;
$viewCount = isset($views) ? (int)$views : 0;

if (!empty($visitor) && $visitor instanceof VisitorDecorator) {
    $entryPage = $visitor->getFirstPage();
    $exitPage  = $visitor->getLastPage();
    $viewCount = isset($views) ? (int)$views : (int)$visitor->getHits(true);
    $samePage  = $visitor->hasSameEntryAndExit();
} else {
    $entryPage = !empty($entry) && is_array($entry) ? $entry : null;
    $exitPage  = !empty($exit) && is_array($exit) ? $exit : null;
}

if (!$samePage && !empty($entryPage) && !empty($exitPage)) {
    $samePage = ($entryPage['report'] ?? '') === ($exitPage['report'] ?? '') && ($entryPage['title'] ?? '') === ($exitPage['title'] ?? '');
}

if (empty($entryPage) && empty($exitPage)) {
    echo Admin_Template::UnknownColumn(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    return;
}

$entryLabel = __('Entry page', 'wp-statistics');
$exitLabel  = !empty($exit_label) ? (string)$exit_label : __('Exit page', 'wp-statistics');
$bothLabel  = __('Entry and exit page', 'wp-statistics');

// 24px stroke icons: an arrow going into a frame (entry) and out of one (exit).
$iconEntry = '<svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M3 12h11M10 7l5 5-5 5M17 4h3v16h-3"/></svg>';
$iconExit  = '<svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M7 4H4v16h3M10 12h11M17 7l5 5-5 5"/></svg>';

$icon = function ($type, $label, $svg) {
    return '<span class="wps-visitor-pages__icon wps-visitor-pages__icon--' . esc_attr($type) . '" title="' . esc_attr($label) . '">' . $svg . '<span class="screen-reader-text">' . esc_html($label) . '</span></span>';
};

$link = function ($page, $withQuery = false) {
    View::load('components/objects/internal-link', [
        'url'     => $page['report'] ?? '',
        'title'   => $page['title'] ?? '',
        'tooltip' => $withQuery && !empty($page['query']) ? "?{$page['query']}" : '',
    ]);
};

$campaign = !empty($entryPage['query']) ? sanitize_text_field((string)Url::getParam('?' . $entryPage['query'], 'utm_campaign')) : '';
$notSet   = '<span class="wps-visitor-pages__none">' . esc_html__('Not recorded', 'wp-statistics') . '</span>';
?>
<div class="wps-visitor-pages<?php echo $samePage ? ' wps-visitor-pages--single' : ''; ?>">
    <div class="wps-visitor-pages__line wps-visitor-pages__line--entry">
        <?php
        if ($samePage) {
            echo $icon('both', $bothLabel, $iconEntry . $iconExit); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        } else {
            echo $icon('entry', $entryLabel, $iconEntry); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }

        if (!empty($entryPage)) {
            $link($entryPage, true);
        } else {
            echo $notSet; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }
        ?>
        <?php if ($campaign) : ?>
            <span class="wps-campaign-label wps-tooltip" title="<?php echo esc_attr__('Campaign:', 'wp-statistics') . ' ' . esc_attr(Helper::plainText($campaign)); ?>"><?php echo esc_html(Helper::plainText($campaign)); ?></span>
        <?php endif; ?>
        <?php if ($samePage && $viewCount === 1) : ?>
            <?php /* translators: %s: number of pages the visitor saw (always 1 here). */ ?>
            <span class="wps-visitor-pages__tag"><?php echo esc_html(sprintf(_n('%s page', '%s pages', 1, 'wp-statistics'), number_format_i18n(1))); ?></span>
        <?php endif; ?>
    </div>
    <?php if (!$samePage) : ?>
        <div class="wps-visitor-pages__line wps-visitor-pages__line--exit">
            <?php
            echo $icon('exit', $exitLabel, $iconExit); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

            if (!empty($exitPage)) {
                $link($exitPage);
            } else {
                echo $notSet; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            }
            ?>
        </div>
    <?php endif; ?>
</div>

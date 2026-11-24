<?php
/**
 * Journey cell for visitor tables: where the visit started and where it ended, from data
 * the visitor row already has (entry page, exit page, number of views). No extra queries.
 *
 *  - Several pages: line 1 is a filled dot + the entry page; line 2 is a small "└" connector
 *    and the exit page. When the visitor ended where they started, line 2 says "Back to start"
 *    instead of repeating the title. No page count: the Views column already shows it.
 *  - One page: one line with a hollow dot, the page and an amber "Bounced" pill (the pill
 *    only when the view count is exactly 1).
 *  - Exit not recorded: line 2 says "Not recorded" (muted).
 *  - Neither page known: the unknown "-" value.
 *  - A utm_campaign on the entry URL adds the campaign tag after the entry page.
 *
 * Each title cuts with an ellipsis; the full title shows on hover
 * (assets/dev/javascript/components/title-cell.js). The dot and connector are drawn in CSS
 * with logical properties, so the connector mirrors in RTL. Screen readers hear
 * "Entry page" / "Exit page" before each title.
 *
 * Args (all optional except a visitor or the pages):
 *  - visitor     (VisitorDecorator) Reads entry/exit pages and views from it.
 *  - entry, exit (array|null)       Page arrays from Visitor::get_page_by_id() (report, title, query),
 *                                   used when no visitor is given.
 *  - views       (int)              Views of the visit (the visitor's views when no visit count exists).
 *  - exit_label  (string)           Accessible name of the exit line (default "Exit page";
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

// One page: entry and exit are the same page (or only the entry is known) and at most one view.
$onePage = ($samePage || empty($exitPage)) && !empty($entryPage) && $viewCount <= 1;
$bounced = $onePage && $viewCount === 1;

$entryLabel = __('Entry page', 'wp-statistics');
$exitLabel  = !empty($exit_label) ? (string)$exit_label : __('Exit page', 'wp-statistics');

$srText = function ($label) {
    return '<span class="screen-reader-text">' . esc_html($label) . ': </span>';
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
<div class="wps-visitor-pages <?php echo $onePage ? 'wps-visitor-pages--single' : 'wps-visitor-pages--multi'; ?>">
    <div class="wps-visitor-pages__line wps-visitor-pages__line--entry">
        <span class="wps-visitor-pages__dot<?php echo $onePage ? ' wps-visitor-pages__dot--hollow' : ''; ?>" aria-hidden="true"></span>
        <?php
        echo $srText($entryLabel); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

        if (!empty($entryPage)) {
            $link($entryPage, true);
        } else {
            echo $notSet; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }
        ?>
        <?php if ($campaign) : ?>
            <span class="wps-campaign-label wps-tooltip" title="<?php echo esc_attr__('Campaign:', 'wp-statistics') . ' ' . esc_attr(Helper::plainText($campaign)); ?>"><?php echo esc_html(Helper::plainText($campaign)); ?></span>
        <?php endif; ?>
        <?php if ($bounced) : ?>
            <span class="wps-visitor-pages__bounced" title="<?php esc_attr_e('Left after one page', 'wp-statistics'); ?>"><?php echo esc_html_x('Bounced', 'visitor left after one page', 'wp-statistics'); ?></span>
        <?php endif; ?>
    </div>
    <?php if (!$onePage) : ?>
        <div class="wps-visitor-pages__line wps-visitor-pages__line--exit">
            <span class="wps-visitor-pages__connector" aria-hidden="true"></span>
            <?php
            echo $srText($exitLabel); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

            if ($samePage) {
                echo '<span class="wps-visitor-pages__back">' . esc_html__('Back to start', 'wp-statistics') . '</span>';
            } elseif (!empty($exitPage)) {
                $link($exitPage);
            } else {
                echo $notSet; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            }
            ?>
        </div>
    <?php endif; ?>
</div>

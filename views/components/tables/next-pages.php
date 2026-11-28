<?php
/**
 * "Where readers go next" card on the single content report.
 *
 * Args (from ViewsModel::getNextPages()):
 *  - pages     (array) Objects with page_id and visitors, most visitors first.
 *  - readers   (int)   Visitors who viewed this post in the range.
 *  - left      (int)   Of those, visitors whose view of this post was their last view.
 *  - left_rate (float) left / readers, in percent.
 *  - unlocked  (bool)  Data Plus is active (top 10 instead of top 3, no lock line).
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_STATISTICS\Visitor;
use WP_Statistics\Components\View;
use WP_Statistics\Service\Admin\LicenseManagement\UpsellLink;

$pages    = !empty($pages) ? $pages : [];
$readers  = isset($readers) ? intval($readers) : 0;
$unlocked = !empty($unlocked);
$surface  = 'next-pages';
?>

<div class="wps-card wps-next-pages">
    <div class="wps-card__title">
        <h2>
            <?php esc_html_e('Where readers go next', 'wp-statistics'); ?>
            <span class="wps-tooltip" title="<?php esc_attr_e('Pages visitors opened right after this one.', 'wp-statistics'); ?>"><i class="wps-tooltip-icon info"></i></span>
        </h2>
    </div>
    <div class="inside">
        <?php if ($readers > 0) : ?>
            <p class="wps-next-pages__left">
                <?php
                /* translators: %s: percentage of readers, e.g. "42%" */
                printf(esc_html__('%s of readers left after this page', 'wp-statistics'), '<strong>' . esc_html(number_format_i18n((float)$left_rate, 1) . '%') . '</strong>'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped parts
                ?>
            </p>
        <?php endif; ?>

        <?php if (!empty($pages)) : ?>
            <div class="o-table-wrapper">
                <table width="100%" class="o-table wps-new-table wps-new-table__next-pages">
                    <thead>
                    <tr>
                        <th scope="col" class="wps-pd-l"><?php esc_html_e('Next Page', 'wp-statistics'); ?></th>
                        <th scope="col" class="wps-pd-l wps-col-num"><?php esc_html_e('Visitors', 'wp-statistics'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($pages as $page) :
                        $pageInfo = Visitor::get_page_by_id($page->page_id);
                        ?>
                        <tr>
                            <td class="wps-pd-l">
                                <span class="wps-title-cell">
                                    <?php View::load('components/objects/internal-link', [
                                        'url'   => $pageInfo['report'],
                                        'title' => $pageInfo['title']
                                    ]); ?>
                                    <?php View::load('components/objects/new-tab-link', ['url' => $pageInfo['link']]); ?>
                                </span>
                            </td>
                            <td class="wps-pd-l wps-col-num"><?php echo esc_html(number_format_i18n($page->visitors)); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if (!$unlocked) : ?>
                <p class="wps-next-pages__lock">
                    <a class="wps-next-pages__lock-link js-wps-openPremiumModal" href="<?php echo esc_url(UpsellLink::addon('wp-statistics-data-plus', $surface)); ?>"<?php echo UpsellLink::popupAttributes('wp-statistics-data-plus', $surface); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the helper ?>>
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true" focusable="false"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                        <span><?php esc_html_e('See the top 10 with Data Plus', 'wp-statistics'); ?></span>
                    </a>
                </p>
            <?php endif; ?>
        <?php else : ?>
            <?php View::load('components/objects/empty-state'); ?>
        <?php endif; ?>
    </div>
</div>

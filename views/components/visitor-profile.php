<?php
/**
 * Single visitor profile card (layout A): the page header carries who it is (avatar, name, login tag, last seen);
 * this card carries the facts. Pills (location, browser, OS, device, IP state), the source with its referrer,
 * the campaign block, the user (logged in only), visits and first seen. Nothing is shown in both places.
 *
 * Args:
 *  - visitor (VisitorDecorator) Required.
 *  - summary (array) VisitorJourney::summarize() result: visits, views, first_seen, last_seen.
 *  - limit   (int) Max other visits read (the count says "51+" when it is reached).
 */

use WP_STATISTICS\Admin_Template;
use WP_STATISTICS\Menus;
use WP_Statistics\Components\View;
use WP_Statistics\Decorators\VisitorDecorator;
use WP_Statistics\Service\Admin\PageHeader\SingleHeaders;
use WP_Statistics\Utils\Url;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/** @var VisitorDecorator $visitor */
if (empty($visitor)) {
    return;
}

$summary  = wp_parse_args($summary ?? [], ['visits' => 1, 'views' => 0, 'first_seen' => null, 'last_seen' => null]);
$limit    = isset($limit) ? (int)$limit : 0;
// Same privacy rule as the visitor card and the page header (VisitorDecorator::getVisibleUser() / isIpVisible()).
$user      = $visitor->getVisibleUser();
$isHashed  = $visitor->isHashedIP();
$ipVisible = $visitor->isIpVisible();
$location = SingleHeaders::visitorLocation($visitor);
$browser  = $visitor->getBrowser();
$os       = $visitor->getOs();
$device   = $visitor->getDevice();
$referral = $visitor->getReferral();
$notSet   = __('(not set)', 'wp-statistics');

$isKnown = function ($value) use ($notSet) {
    return !empty($value) && $value !== $notSet && !Admin_Template::isUnknown($value);
};

$visitsCount = number_format_i18n((int)$summary['visits']) . ($limit && (int)$summary['visits'] > $limit ? '+' : '');

if ($user) {
    /* translators: %s: Number of visits. */
    $visitsText = sprintf(_n('%s from this user', '%s from this user', (int)$summary['visits'], 'wp-statistics'), $visitsCount);
} elseif ($isHashed || !$ipVisible) {
    $visitsText = $visitsCount;
} else {
    /* translators: %s: Number of visits. */
    $visitsText = sprintf(_n('%s from this IP', '%s from this IP', (int)$summary['visits'], 'wp-statistics'), $visitsCount);
}
?>
<div class="wps-card wps-visitor-profile">
    <div class="wps-visitor-profile__pills">
        <?php
        if ($location) {
            View::load('components/objects/pill', [
                'text'  => $location,
                'icon'  => $visitor->getLocation()->getCountryFlag(),
                'shape' => 'flag',
                'url'   => Menus::admin_url('geographic', ['type' => 'single-country', 'country' => $visitor->getLocation()->getCountryCode()]),
                'title' => implode(', ', array_filter([$isKnown($visitor->getLocation()->getRegion()) ? $visitor->getLocation()->getRegion() : '', $visitor->getLocation()->getCountryName()])),
            ]);
        }

        if ($isKnown($browser->getName())) {
            $version = $browser->getVersion();
            View::load('components/objects/pill', [
                'text' => trim($browser->getName() . ' ' . ($isKnown($version) ? $version : '')),
                'icon' => $browser->getLogo(),
                'url'  => Menus::admin_url('visitors', ['tab' => 'visitors', 'agent' => $browser->getRaw()]),
            ]);
        }

        if ($isKnown($os->getName())) {
            View::load('components/objects/pill', [
                'text' => $os->getName(),
                'icon' => $os->getLogo(),
                'url'  => Menus::admin_url('visitors', ['tab' => 'visitors', 'platform' => $os->getName()]),
            ]);
        }

        if ($isKnown($device->getType())) {
            $model = $device->getModel();
            View::load('components/objects/pill', [
                'text' => trim($device->getType() . ($isKnown($model) ? ' · ' . $model : '')),
                'icon' => $device->getLogo(),
            ]);
        }

        // IP state (moved here from the page header). Logged-in users show it in the facts below.
        if (!$user) {
            if ($isHashed) {
                /* translators: %s: Short hash of the visitor's IP, e.g. #e7b398f9. */
                $ipText = $visitor->getIP() ? sprintf(__('%s · hashed', 'wp-statistics'), $visitor->getIP()) : __('Hashed IP', 'wp-statistics');
            } elseif (!$ipVisible) {
                $ipText = __('IP hidden', 'wp-statistics');
            } else {
                $ipText = (string)$visitor->getIP();
            }
            View::load('components/objects/pill', ['text' => $ipText, 'class' => 'wps-pill--ip']);
        }
        ?>
    </div>

    <dl class="wps-visitor-profile__facts">
        <?php if ($user && ($isHashed || $ipVisible)) : ?>
            <div>
                <dt><?php echo $isHashed ? esc_html__('Hash', 'wp-statistics') : esc_html__('IP', 'wp-statistics'); ?></dt>
                <dd title="<?php echo esc_attr($visitor->getIP()); ?>"><bdi><?php echo esc_html($visitor->getIP()); ?></bdi></dd>
            </div>
        <?php endif; ?>
        <?php if ($user) : ?>
            <div>
                <dt><?php esc_html_e('User', 'wp-statistics'); ?></dt>
                <dd><a href="<?php echo esc_url(get_edit_user_link($user->getId())); ?>"><?php echo esc_html($user->getDisplayName()); ?></a><?php
                    $roleSlug  = $user->getRole();
                    $roleNames = wp_roles()->get_names();
                    echo $roleSlug ? ' <span class="wps-visitor-profile__none">· ' . esc_html(isset($roleNames[$roleSlug]) ? translate_user_role($roleNames[$roleSlug]) : ucfirst($roleSlug)) . '</span>' : '';
                    ?></dd>
            </div>
        <?php endif; ?>
        <div>
            <dt><?php esc_html_e('Source', 'wp-statistics'); ?></dt>
            <dd class="wps-visitor-profile__source">
                <span><?php echo $referral->getSourceChannel() ? esc_html($referral->getSourceChannel()) : esc_html__('Direct', 'wp-statistics'); ?></span>
                <?php if (!empty($referral->getReferrer())) : ?>
                    <span class="wps-visitor-profile__none" aria-hidden="true">·</span>
                    <?php View::load('components/objects/external-link', ['url' => $referral->getReferrer(), 'title' => $referral->getRawReferrer()]); ?>
                <?php elseif (!empty($referral->getSourceName()) && $referral->getRawSourceChannel() !== 'direct' && $referral->getSourceName() !== $referral->getSourceChannel()) : ?>
                    <span class="wps-visitor-profile__none" aria-hidden="true">·</span>
                    <span title="<?php echo esc_attr($referral->getSourceName()); ?>"><?php echo esc_html($referral->getSourceName()); ?></span>
                <?php endif; ?>
            </dd>
        </div>
        <?php
        // The campaign the visit arrived with: the UTM tags on its entry page, as one block.
        $wpsEntry = $visitor->getFirstPage();
        if (!empty($wpsEntry['query']) && Url::getParam('?' . ltrim($wpsEntry['query'], '?'), 'utm_campaign')) : ?>
            <div class="wps-visitor-profile__rule" role="presentation"></div>
            <div class="wps-visitor-profile__campaign">
                <dt class="screen-reader-text"><?php esc_html_e('Campaign', 'wp-statistics'); ?></dt>
                <dd><?php View::load('components/objects/campaign-label', ['query' => $wpsEntry['query'], 'variant' => 'block', 'surface' => 'visitor-profile']); ?></dd>
            </div>
        <?php endif; ?>
        <div class="wps-visitor-profile__rule" role="presentation"></div>
        <div>
            <dt><?php esc_html_e('Visits', 'wp-statistics'); ?></dt>
            <dd><?php echo esc_html($visitsText); ?></dd>
        </div>
        <?php if (!empty($summary['first_seen'])) : ?>
            <div>
                <dt><?php esc_html_e('First seen', 'wp-statistics'); ?></dt>
                <dd><?php echo esc_html(SingleHeaders::formatDateTime($summary['first_seen'])); ?></dd>
            </div>
        <?php endif; ?>
    </dl>

    <?php if ($isHashed && !$user) : ?>
        <p class="wps-visitor-profile__note"><?php esc_html_e('IP addresses are hashed with a key that changes every day, so visits on other days cannot be linked to this one.', 'wp-statistics'); ?></p>
    <?php endif; ?>
</div>

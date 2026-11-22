<?php
/**
 * Single visitor profile card: who (IP, hash or user), facts as pills, source, and totals across visits.
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
    <div class="wps-visitor-profile__head">
        <div class="wps-visitor-profile__avatar<?php echo $user ? ' wps-visitor-profile__avatar--user' : ' wps-visitor-profile__avatar--anonymous'; ?>">
            <?php if ($user) :
                $userName = trim((string)$user->getDisplayName());
                $initial  = function_exists('mb_substr') ? mb_strtoupper(mb_substr($userName, 0, 1)) : strtoupper(substr($userName, 0, 1));
                ?>
                <span class="wps-visitor-profile__initial" aria-hidden="true"><?php echo esc_html($initial); ?></span>
                <img src="<?php echo esc_url(get_avatar_url($user->getId(), ['size' => 96])); ?>" alt="" width="48" height="48">
            <?php else : ?>
                <img class="wps-visitor-profile__anonymous" src="<?php echo esc_url(WP_STATISTICS_URL . 'assets/images/anonymous.svg'); ?>" alt="" width="22" height="17">
            <?php endif; ?>
            <?php if ($isKnown($visitor->getLocation()->getCountryName())) : ?>
                <span class="wps-visitor-profile__badge"><img src="<?php echo esc_url($visitor->getLocation()->getCountryFlag()); ?>" alt="" width="16" height="12"></span>
            <?php endif; ?>
        </div>
        <div class="wps-visitor-profile__name">
            <?php if ($user) : ?>
                <a class="wps-visitor-profile__title" href="<?php echo esc_url(get_edit_user_link($user->getId())); ?>"><?php echo esc_html($user->getDisplayName()); ?></a>
                <span><?php esc_html_e('Logged-in user', 'wp-statistics'); ?><?php
                    $roleSlug  = $user->getRole();
                    $roleNames = wp_roles()->get_names();
                    echo $roleSlug ? ' · ' . esc_html(isset($roleNames[$roleSlug]) ? translate_user_role($roleNames[$roleSlug]) : ucfirst($roleSlug)) : '';
                    ?></span>
            <?php else : ?>
                <strong class="wps-visitor-profile__title" title="<?php echo esc_attr($visitor->getDisplayLabel()); ?>"><bdi><?php echo esc_html($visitor->getDisplayLabel()); ?></bdi></strong>
                <span><?php echo $isHashed ? esc_html__('Anonymous Visitor · hashed IP', 'wp-statistics') : ($ipVisible ? esc_html__('Anonymous Visitor · not logged in', 'wp-statistics') : esc_html__('Not logged in · IP hidden', 'wp-statistics')); ?></span>
            <?php endif; ?>
        </div>
    </div>

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
        ?>
    </div>

    <dl class="wps-visitor-profile__facts">
        <?php if ($user && ($isHashed || $ipVisible)) : ?>
            <div>
                <dt><?php echo $isHashed ? esc_html__('Hash', 'wp-statistics') : esc_html__('IP', 'wp-statistics'); ?></dt>
                <dd title="<?php echo esc_attr($visitor->getIP()); ?>"><bdi><?php echo esc_html($visitor->getIP()); ?></bdi></dd>
            </div>
        <?php endif; ?>
        <div>
            <dt><?php esc_html_e('Source', 'wp-statistics'); ?></dt>
            <dd><?php echo $referral->getSourceChannel() ? esc_html($referral->getSourceChannel()) : esc_html__('Direct', 'wp-statistics'); ?></dd>
        </div>
        <div>
            <dt><?php esc_html_e('Referrer', 'wp-statistics'); ?></dt>
            <dd>
                <?php if (!empty($referral->getReferrer())) :
                    View::load('components/objects/external-link', ['url' => $referral->getReferrer(), 'title' => $referral->getRawReferrer()]);
                elseif (!empty($referral->getSourceName()) && $referral->getRawSourceChannel() !== 'direct') : ?>
                    <span title="<?php echo esc_attr($referral->getSourceName()); ?>"><?php echo esc_html($referral->getSourceName()); ?></span>
                <?php else : ?>
                    <span class="wps-visitor-profile__none"><?php esc_html_e('None', 'wp-statistics'); ?></span>
                <?php endif; ?>
            </dd>
        </div>
        <div class="wps-visitor-profile__rule" role="presentation"></div>
        <div>
            <dt><?php esc_html_e('Visits', 'wp-statistics'); ?></dt>
            <dd><?php echo esc_html($visitsText); ?></dd>
        </div>
        <div>
            <dt><?php esc_html_e('Views', 'wp-statistics'); ?></dt>
            <dd><?php
                /* translators: %s: Number of views. */
                echo esc_html(sprintf(__('%s in total', 'wp-statistics'), number_format_i18n((int)$summary['views'])));
                ?></dd>
        </div>
        <?php if (!empty($summary['first_seen'])) : ?>
            <div>
                <dt><?php esc_html_e('First seen', 'wp-statistics'); ?></dt>
                <dd><?php echo esc_html(SingleHeaders::formatDateTime($summary['first_seen'])); ?></dd>
            </div>
        <?php endif; ?>
        <?php if (!empty($summary['last_seen'])) : ?>
            <div>
                <dt><?php esc_html_e('Last seen', 'wp-statistics'); ?></dt>
                <dd><?php echo esc_html(SingleHeaders::formatDateTime($summary['last_seen'])); ?></dd>
            </div>
        <?php endif; ?>
    </dl>

    <?php if ($isHashed && !$user) : ?>
        <p class="wps-visitor-profile__note"><?php esc_html_e('IP addresses are hashed with a key that changes every day, so visits on other days cannot be linked to this one.', 'wp-statistics'); ?></p>
    <?php endif; ?>
</div>

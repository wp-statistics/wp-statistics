<?php

namespace WP_STATISTICS;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Decorators\VisitorDecorator;

/**
 * Visitor Information cell.
 *
 * A small box: the top line shows who the visitor is, the bottom line four equal cells for
 * country, OS, device and browser. Top line: an icon (blue person = logged-in user, grey person =
 * guest whose IP is shown, full or anonymized, incognito hat = IP hashed or not stored) and the
 * name, the IP, the short hash or "Anonymous". The box is one button that opens the visitor card
 * (assets/dev/javascript/components/visitor-card.js) with location, browser, OS, device, IP and the
 * actions Copy IP, Filter by IP (or by country) and Visitor Journey.
 *
 * Args:
 *  - visitor (VisitorDecorator) required.
 *
 * Classes stay compatible with add-ons and older CSS: ul.wps-visitor__information--container >
 * li.wps-visitor__information (who, country, OS, device, browser).
 * The card is rendered hidden (attribute + inline style) so PDFs, prints and emails never show it.
 *
 * Privacy: the user only when user tracking (visitors_log) is on (VisitorDecorator::getVisibleUser());
 * the IP only when "Hash IP Addresses" is off and the stored value is a real IP (VisitorDecorator::isIpVisible(),
 * so rows saved before hashing was switched on stay hidden too); email and role only for people who can list users.
 *
 * Online now: when the visitor is online (VisitorDecorator::isOnline(), same window as the Online
 * Visitors report), the icon gets a green dot on its corner (span.wps-visitor-info__online)
 * with the hover text "Online now · on <page>", and the box's accessible name says it too.
 */

/** @var VisitorDecorator $visitor */
if (empty($visitor)) {
    return;
}

$notSet  = __('(not set)', 'wp-statistics');
$isKnown = function ($value) use ($notSet) {
    return !empty($value) && $value !== $notSet && !Admin_Template::isUnknown($value);
};

$location    = $visitor->getLocation();
$countryCode = $location->getCountryCode();
$countryName = $location->getCountryName();
$countryFlag = $location->getCountryFlag();
$isUnknown   = empty($countryCode) || $countryCode === Country::$unknown_location || strpos($countryFlag, '/flags/000.svg') !== false;
$region      = $location->getRegion();
$city        = $location->getCity();

$browser     = $visitor->getBrowser();
$browserName = $browser->getName();
$browserVer  = $browser->getVersion();
$os          = $visitor->getOs();
$osName      = $os->getName();
$device      = $visitor->getDevice();
$deviceType  = $device->getType();
$deviceModel = $device->getModel();

$visitorUrl   = Menus::admin_url('visitors', ['type' => 'single-visitor', 'visitor_id' => $visitor->getId()]);
$user         = $visitor->getVisibleUser();
$isHashed     = $visitor->isHashedIP();
$ip           = $visitor->getIP();
$ipVisible    = $visitor->isIpVisible();
$showIp       = $ipVisible;
$isAnonymized = $ipVisible && Option::get('anonymize_ips') && $visitor->isIpAnonymized();
$canSeeUsers  = current_user_can('list_users');

// Login status (logged-in user or anonymous visitor), used for classes and the card.
$status = $user ? 'user' : 'anonymous';

// Icon on the top line: blue person for a logged-in user, grey person for a guest whose IP is
// shown (full or anonymized), the incognito hat when the IP is hashed or not stored.
$iconType = $user ? 'user' : ((($showIp) && !empty($ip)) ? 'guest' : 'anonymous');

// Online now marker on the status slot.
$isOnline    = $visitor->isOnline();
$onlineLabel = $isOnline ? $visitor->getOnlineLabel() : '';

// Card text.
$placeText = implode(', ', array_unique(array_filter([
    $isKnown($city) ? $city : '',
    $isKnown($region) ? $region : '',
])));
$browserText = $isKnown($browserName) ? trim($browserName . ($isKnown($browserVer) ? ' ' . $browserVer : '')) : $notSet;
$deviceText  = $isKnown($deviceType) ? $deviceType . ($isKnown($deviceModel) ? ', ' . $deviceModel : '') : $notSet;
$hits        = (int)$visitor->getHits(true);
/* translators: %s: Number of views. */
$viewsText = $hits > 0 ? sprintf(_n('%s view', '%s views', $hits, 'wp-statistics'), number_format_i18n($hits)) : '';

if ($user) {
    $title    = $user->getDisplayName();
    $roleSlug = $user->getRole();
    $roles    = wp_roles()->get_names();
    $roleName = $roleSlug ? (isset($roles[$roleSlug]) ? translate_user_role($roles[$roleSlug]) : ucfirst($roleSlug)) : '';
    /* translators: %s: User ID. */
    $userIdText = sprintf(__('user #%s', 'wp-statistics'), $user->getId());
    $subtitle   = $canSeeUsers ? implode(' · ', array_filter([$roleName, $userIdText])) : $viewsText;
    $initial    = function_exists('mb_substr') ? mb_strtoupper(mb_substr(trim($title), 0, 1)) : strtoupper(substr(trim($title), 0, 1));
    $avatar     = get_option('show_avatars') ? get_avatar_url($user->getId(), ['size' => 72, 'default' => 'blank']) : '';
} else {
    $title    = __('Anonymous Visitor', 'wp-statistics');
    $subtitle = $viewsText;
}

// Accessible name of the trigger: the same facts the icons show.
$labelParts = [];
$labelParts[] = !$isUnknown ? implode(', ', array_filter([$placeText, $countryName])) : __('Location unknown', 'wp-statistics');
if ($isKnown($browserName) || $isKnown($osName)) {
    /* translators: 1: Browser name, 2: Operating system name. */
    $labelParts[] = sprintf(__('%1$s on %2$s', 'wp-statistics'), $isKnown($browserName) ? $browserName : $notSet, $isKnown($osName) ? $osName : $notSet);
}
if ($isKnown($deviceType)) {
    $labelParts[] = $deviceType;
}
if ($status === 'user') {
    /* translators: %s: User display name. */
    $labelParts[] = sprintf(__('Logged-in user: %s', 'wp-statistics'), $user->getDisplayName());
} else {
    $labelParts[] = __('Anonymous Visitor', 'wp-statistics');
}
if ($isOnline) {
    $labelParts[] = $onlineLabel;
}
// The identity shown on the top line, so screen readers can tell visitors apart too.
if (!$user && $showIp && !empty($ip)) {
    /* translators: %s: IP address. */
    $labelParts[] = sprintf(__('IP %s', 'wp-statistics'), $ip);
} elseif (!$user && $isHashed && !empty($ip)) {
    /* translators: %s: short hashed IP, for example #e7b398f9. */
    $labelParts[] = sprintf(__('Hashed IP %s', 'wp-statistics'), $ip);
}
if ((!$ipVisible && !($isHashed && !empty($ip))) || $isAnonymized) {
    $labelParts[] = $isAnonymized ? __('IP anonymized', 'wp-statistics') : __('IP hidden', 'wp-statistics');
}
/* translators: %s: Short visitor summary (place, browser, device). */
$triggerLabel = sprintf(__('Visitor details: %s', 'wp-statistics'), implode(', ', $labelParts));

$cardId     = 'wps-visitor-card-' . wp_unique_id() . '-' . substr(md5(uniqid('', true)), 0, 6);
$countryUrl = Menus::admin_url('geographic', ['type' => 'single-country', 'country' => $countryCode]);
$agentUrl   = Menus::admin_url('visitors', ['tab' => 'visitors', 'agent' => $browser->getRaw()]);
$osUrl      = Menus::admin_url('visitors', ['tab' => 'visitors', 'platform' => $osName]);

// 16px line icons (stroke = currentColor).
$svg  = function ($paths, $size = 16) {
    return '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $paths . '</svg>';
};
$icon = [
    'globe'  => '<circle cx="12" cy="12" r="9.25"/><path d="M2.75 12h18.5M12 2.75c2.5 2.6 3.75 5.7 3.75 9.25S14.5 18.65 12 21.25M12 2.75C9.5 5.35 8.25 8.45 8.25 12s1.25 6.65 3.75 9.25"/>',
    'ip'     => '<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M8 9v6M12 15V9h2.5a2 2 0 0 1 0 4H12"/>',
    'lock'   => '<rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/>',
    'mail'   => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/>',
    'copy'   => '<rect x="9" y="9" width="12" height="12" rx="2"/><path d="M5 15H4a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v1"/>',
    'filter' => '<path d="M3 5h18l-7 8.5V19l-4 2v-7.5z"/>',
    'route'  => '<circle cx="6" cy="19" r="2.5"/><circle cx="18" cy="5" r="2.5"/><path d="M8.5 19H16a3.5 3.5 0 0 0 0-7H8a3.5 3.5 0 0 1 0-7h7.5"/>',
    'close'  => '<path d="M6 6l12 12M18 6L6 18"/>',
];
// Login status icons: the filled shapes of assets/images/user-visitor.svg and anonymous.svg
// (Logged-in Users widget), drawn in currentColor.
$statusIcon = function ($type, $width = 14) {
    if ($type === 'user') {
        $box   = '0 0 13 12';
        $paths = '<path d="M6.5 5.41406C7.88071 5.41406 9 4.29477 9 2.91406C9 1.53335 7.88071 0.414062 6.5 0.414062C5.11929 0.414062 4 1.53335 4 2.91406C4 4.29477 5.11929 5.41406 6.5 5.41406Z"/><path d="M10.9678 9.07199C10.9678 10.4601 10.9678 11.5854 6.49951 11.5854C2.03125 11.5854 2.03125 10.4601 2.03125 9.07199C2.03125 7.68387 4.03176 6.55859 6.49951 6.55859C8.96727 6.55859 10.9678 7.68387 10.9678 9.07199Z"/>';
        $ratio = 12 / 13;
    } else {
        $box   = '0 0 13 10';
        $paths = '<path fill-rule="evenodd" clip-rule="evenodd" d="M1.92718 2.73416L1.68294 3.7111H0.429688C0.19238 3.7111 0 3.90349 0 4.14079C0 4.37809 0.19238 4.57048 0.429688 4.57048H11.888C12.1253 4.57048 12.3177 4.37809 12.3177 4.14079C12.3177 3.90349 12.1253 3.7111 11.888 3.7111H10.6348L10.3905 2.73416C10.0779 1.48366 9.9216 0.858416 9.45536 0.494391C8.98912 0.130371 8.34465 0.130371 7.05564 0.130371H5.26207C3.97306 0.130371 3.32858 0.130371 2.86234 0.494391C2.39612 0.858416 2.2398 1.48366 1.92718 2.73416ZM3.00781 9.86996C3.93775 9.86996 4.71984 9.23694 4.94666 8.37825L5.58239 8.0604C5.94527 7.87895 6.37244 7.87895 6.73532 8.0604L7.37103 8.37825C7.59785 9.23694 8.37994 9.86996 9.3099 9.86996C10.4173 9.86996 11.3151 8.97219 11.3151 7.86475C11.3151 6.7573 10.4173 5.85954 9.3099 5.85954C8.3588 5.85954 7.56239 6.52171 7.35642 7.41014L7.11969 7.29177C6.51481 6.98933 5.8029 6.98933 5.19802 7.29177L4.96127 7.41014C4.75534 6.52171 3.95891 5.85954 3.00781 5.85954C1.90036 5.85954 1.0026 6.7573 1.0026 7.86475C1.0026 8.97219 1.90036 9.86996 3.00781 9.86996Z"/>';
        $ratio = 10 / 13;
    }
    return '<svg class="wps-visitor-info__status-icon wps-visitor-info__status-icon--' . $type . '" width="' . $width . '" height="' . round($width * $ratio, 1) . '" viewBox="' . $box . '" fill="currentColor" aria-hidden="true" focusable="false">' . $paths . '</svg>';
};
// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- $svg() returns static SVG markup.
?>
<ul class="wps-visitor__information--container wps-visitor-info<?php echo $status ? ' wps-visitor-info--' . esc_attr($status) : ''; ?><?php echo $isOnline ? ' wps-visitor-info--online' : ''; ?>" role="link" tabindex="0" aria-haspopup="dialog" aria-expanded="false" aria-controls="<?php echo esc_attr($cardId) ?>" aria-label="<?php echo esc_attr($triggerLabel) ?>" data-wps-visitor-card data-wps-visitor-url="<?php echo esc_url($visitorUrl) ?>">
    <li class="wps-visitor__information wps-visitor-info__who" role="none">
        <span class="wps-visitor-info__status wps-visitor-info__slot--status wps-visitor-info__slot--<?php echo esc_attr($iconType) ?>"<?php echo $isOnline ? ' title="' . esc_attr($onlineLabel) . '"' : ''; ?>>
            <?php echo $statusIcon($iconType === 'guest' ? 'user' : $iconType); ?>
            <?php if ($isOnline) : ?>
                <span class="wps-visitor-info__online"><span class="screen-reader-text"><?php echo esc_html($onlineLabel); ?></span></span>
            <?php endif; ?>
        </span>
        <?php if ($user) : ?>
            <span class="wps-visitor-info__label wps-visitor-info__label--user"><span class="wps-visitor-info__name"><?php echo esc_html($user->getDisplayName()) ?></span></span>
        <?php elseif (($showIp || $isHashed) && !empty($ip)) : ?>
            <span class="wps-visitor-info__label wps-visitor-info__label--ip"><?php echo esc_html($ip); ?></span>
        <?php else : ?>
            <span class="wps-visitor-info__label wps-visitor-info__label--none"><?php esc_html_e('Anonymous', 'wp-statistics'); ?></span>
        <?php endif; ?>
    </li>

    <li class="wps-visitor__information wps-visitor-info__slot wps-visitor-info__slot--country" role="none">
        <span>
            <?php if ($isUnknown) : ?>
                <span class="wps-visitor-info__unknown flag" role="img" aria-label="<?php echo esc_attr($countryName) ?>"><?php echo $svg($icon['globe'], 14); ?></span>
            <?php else : ?>
                <img src="<?php echo esc_url($countryFlag) ?>" class="flag" alt="<?php echo esc_attr($countryName) ?>" width="16" height="12">
            <?php endif; ?>
        </span>
    </li>

    <li class="wps-visitor__information wps-visitor-info__slot wps-visitor-info__slot--os" role="none">
        <span><img src="<?php echo esc_url($os->getLogo()) ?>" alt="<?php echo esc_attr($osName) ?>" width="14" height="14"></span>
    </li>

    <li class="wps-visitor__information wps-visitor-info__slot wps-visitor-info__slot--device" role="none">
        <span><img src="<?php echo esc_url($device->getLogo()) ?>" alt="<?php echo esc_attr($deviceType) ?>" width="14" height="14"></span>
    </li>

    <li class="wps-visitor__information wps-visitor-info__slot wps-visitor-info__slot--browser" role="none">
        <span><img src="<?php echo esc_url($browser->getLogo()) ?>" alt="<?php echo esc_attr($browserName) ?>" width="14" height="14"></span>
    </li>

</ul>
<div class="wps-visitor-card" id="<?php echo esc_attr($cardId) ?>" role="dialog" aria-label="<?php esc_attr_e('Visitor details', 'wp-statistics'); ?>" data-copied="<?php esc_attr_e('Copied', 'wp-statistics'); ?>" hidden style="display:none">
    <div class="wps-visitor-card__head">
        <span class="wps-visitor-card__avatar<?php echo $user ? ' wps-visitor-card__avatar--user' : ''; ?>" aria-hidden="true">
            <?php if ($user) : ?>
                <span class="wps-visitor-card__initial"><?php echo esc_html($initial) ?></span>
                <?php if ($avatar) : ?>
                    <img class="wps-visitor-card__photo" src="<?php echo esc_url($avatar) ?>" alt="" width="36" height="36" loading="lazy">
                <?php endif; ?>
            <?php else : ?>
                <?php echo $statusIcon('anonymous', 18); ?>
            <?php endif; ?>
        </span>
        <span class="wps-visitor-card__name">
            <strong><?php echo esc_html($title) ?></strong>
            <?php if (!empty($subtitle)) : ?>
                <span><?php echo esc_html($subtitle) ?></span>
            <?php endif; ?>
        </span>
        <span class="wps-visitor-card__tag wps-visitor-card__tag--<?php echo $user ? 'user' : 'anonymous'; ?>"><?php echo $user ? esc_html__('Logged in', 'wp-statistics') : esc_html__('Anonymous', 'wp-statistics'); ?></span>
        <button type="button" class="wps-visitor-card__close" data-wps-card-close aria-label="<?php esc_attr_e('Close', 'wp-statistics'); ?>"><?php echo $svg($icon['close']); ?></button>
    </div>

    <ul class="wps-visitor-card__facts">
        <li>
            <span class="wps-visitor-card__icon" aria-hidden="true">
                <?php if ($isUnknown) : ?>
                    <?php echo $svg($icon['globe']); ?>
                <?php else : ?>
                    <img class="wps-visitor-card__flag" src="<?php echo esc_url($countryFlag) ?>" alt="" width="16" height="12" loading="lazy">
                <?php endif; ?>
            </span>
            <span class="wps-visitor-card__text">
                <span class="screen-reader-text"><?php esc_html_e('Location:', 'wp-statistics'); ?></span>
                <?php if ($isUnknown) : ?>
                    <span class="wps-visitor-card__muted"><?php echo $placeText !== '' ? esc_html($placeText) : esc_html__('Location unknown', 'wp-statistics'); ?></span>
                <?php else : ?>
                    <?php if ($placeText !== '') : ?><?php echo esc_html($placeText) ?>, <?php endif; ?><a href="<?php echo esc_url($countryUrl) ?>"><?php echo esc_html($countryName) ?></a>
                <?php endif; ?>
            </span>
        </li>
        <li>
            <span class="wps-visitor-card__icon" aria-hidden="true"><img src="<?php echo esc_url($browser->getLogo()) ?>" alt="" width="16" height="16" loading="lazy"></span>
            <span class="wps-visitor-card__text">
                <span class="screen-reader-text"><?php esc_html_e('Browser:', 'wp-statistics'); ?></span>
                <?php if ($isKnown($browserName)) : ?>
                    <a href="<?php echo esc_url($agentUrl) ?>"><?php echo esc_html($browserText) ?></a>
                <?php else : ?>
                    <?php echo esc_html($browserText) ?>
                <?php endif; ?>
                <span class="wps-visitor-card__sep" aria-hidden="true">·</span>
                <span class="screen-reader-text"><?php esc_html_e('Operating system:', 'wp-statistics'); ?></span>
                <?php if ($isKnown($osName)) : ?>
                    <a href="<?php echo esc_url($osUrl) ?>"><?php echo esc_html($osName) ?></a>
                <?php else : ?>
                    <?php echo esc_html($notSet) ?>
                <?php endif; ?>
            </span>
        </li>
        <li>
            <span class="wps-visitor-card__icon" aria-hidden="true"><img src="<?php echo esc_url($device->getLogo()) ?>" alt="" width="16" height="16" loading="lazy"></span>
            <span class="wps-visitor-card__text"><span class="screen-reader-text"><?php esc_html_e('Device:', 'wp-statistics'); ?></span><?php echo esc_html($deviceText) ?></span>
        </li>
        <?php if ($ipVisible) : ?>
            <li>
                <span class="wps-visitor-card__icon" aria-hidden="true"><?php echo $svg($icon['ip']); ?></span>
                <span class="wps-visitor-card__text">
                    <span class="screen-reader-text"><?php esc_html_e('IP:', 'wp-statistics'); ?></span>
                    <span class="wps-visitor-card__ip"><?php echo esc_html($ip) ?></span>
                    <?php if ($isAnonymized) : ?><span class="wps-visitor-card__sep" aria-hidden="true">·</span><span class="wps-visitor-card__muted"><?php esc_html_e('anonymized', 'wp-statistics'); ?></span><?php endif; ?>
                </span>
            </li>
        <?php else : ?>
            <li class="wps-visitor-card__muted">
                <span class="wps-visitor-card__icon" aria-hidden="true"><?php echo $svg($icon['lock']); ?></span>
                <?php if ($isHashed && !empty($ip)) : ?>
                    <span class="wps-visitor-card__text"><span class="screen-reader-text"><?php esc_html_e('Hashed IP:', 'wp-statistics'); ?></span><span class="wps-visitor-card__ip"><?php echo esc_html($ip) ?></span><span class="wps-visitor-card__sep" aria-hidden="true">·</span><?php esc_html_e('hashed', 'wp-statistics'); ?></span>
                <?php else : ?>
                    <span class="wps-visitor-card__text"><?php echo $isHashed ? esc_html__('IP hidden (hashed)', 'wp-statistics') : (!empty($ip) ? esc_html__('IP hidden', 'wp-statistics') : esc_html__('IP not stored', 'wp-statistics')); ?></span>
                <?php endif; ?>
            </li>
        <?php endif; ?>
        <?php if ($user && $canSeeUsers && $user->getEmail()) : ?>
            <li>
                <span class="wps-visitor-card__icon" aria-hidden="true"><?php echo $svg($icon['mail']); ?></span>
                <span class="wps-visitor-card__text"><span class="screen-reader-text"><?php esc_html_e('Email:', 'wp-statistics'); ?></span><?php echo esc_html($user->getEmail()) ?></span>
            </li>
        <?php endif; ?>
    </ul>

    <?php
    $actions = [];
    if ($ipVisible) {
        $actions[] = '<button type="button" data-wps-copy="' . esc_attr($ip) . '">' . $svg($icon['copy']) . '<span data-wps-copy-label>' . esc_html__('Copy IP', 'wp-statistics') . '</span></button>';
        $actions[] = '<a href="' . esc_url(Menus::admin_url('visitors', ['tab' => 'visitors', 'ip' => $ip, 'exact_ip' => 1])) . '">' . $svg($icon['filter']) . '<span>' . esc_html__('Filter by IP', 'wp-statistics') . '</span></a>';
    } elseif (!$isUnknown) {
        $actions[] = '<a href="' . esc_url(Menus::admin_url('visitors', ['tab' => 'visitors', 'location' => $countryCode])) . '">' . $svg($icon['filter']) . '<span>' . esc_html__('Filter by country', 'wp-statistics') . '</span></a>';
    }
    $actions[] = '<a class="wps-visitor-card__primary" href="' . esc_url($visitorUrl) . '">' . str_replace('<svg ', '<svg class="wps-visitor-card__route" ', $svg($icon['route'])) . '<span>' . esc_html__('Visitor Journey', 'wp-statistics') . '</span></a>';
    ?>
    <div class="wps-visitor-card__actions" style="--wps-card-actions:<?php echo count($actions); ?>">
        <?php echo implode('', $actions); ?>
    </div>
    <span class="screen-reader-text" aria-live="polite" data-wps-copy-status></span>
</div>
<?php // phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped ?>

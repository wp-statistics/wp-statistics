<?php

use WP_STATISTICS\Admin_Template;
use WP_Statistics\Decorators\VisitorDecorator;
use WP_STATISTICS\Menus;
use WP_Statistics\Components\View;
?>

<?php
if ($visitor instanceof VisitorDecorator) {
    $isHashedIP     = $visitor->isHashedIP();
    $browserVersion = $visitor->getBrowser()->getVersion();

    $countryCode = $visitor->getLocation()->getCountryCode();
    $countryName = $visitor->getLocation()->getCountryName();
    $countryFlag = $visitor->getLocation()->getCountryFlag();
    $city        = $visitor->getLocation()->getCity();
    $regionName  = $visitor->getLocation()->getRegion();

    $initialView          = $visitor->getFirstView() ?? $visitor->getLastCounter();
    $initialResource      = $visitor->getFirstPage();
    $initialResourceLink  = $initialResource['link'];
    $initialResourceTitle = $initialResource['title'];
    $initialesourceQuery  = $initialResource['query'] ? "?{$initialResource['query']}" : '';
} else {
    $isHashedIP     = $visitor->isHashedIP();
    $browserVersion = $visitor->getBrowserVersion()->getVersion();

    $countryCode = $visitor->getCountry()->getCode();
    $countryName = $visitor->getCountry()->getName();
    $countryFlag = $visitor->getCountry()->getFlag();
    $city        = $visitor->getCity()->getName();
    $regionName  = $visitor->getCity()->getRegionName();

    $initialView          = $visitor->getInitialView()->getViewedAt();
    $initialResource      = $visitor->getInitialView()->getResource();
    $initialResourceLink  = $initialResource->getUrl();
    $initialResourceTitle = $initialResource->getTitle();
    $initialesourceQuery  = $visitor->getParameter($initialResource->getId())->getFull();
}
?>

<div class="wps-visitor__visitors-details">
    <div class="wps-visitor__visitors-detail--row">
        <?php if ($isHashedIP) : ?>
            <span>
                <?php esc_html_e('Daily Visitor Hash', 'wp-statistics'); ?>
            </span>
        <?php else : ?>
            <span>
                <?php esc_html_e('IP Address', 'wp-statistics'); ?>
            </span>
        <?php endif; ?>

        <div>
            <span title="<?php echo esc_attr($visitor->getIP()); ?>"><?php echo esc_html($visitor->getIP()); ?></span>
        </div>
    </div>

    <div class="wps-visitor__visitors-detail--row">
        <span><?php esc_html_e('Referrer', 'wp-statistics'); ?></span>
        <div class="wps-visitor__visitors-detail--link wps-ellipsis-parent">
            <?php if (!empty($visitor->getReferral()->getReferrer())) :
                View::load("components/objects/external-link", ['url' => $visitor->getReferral()->getReferrer(), 'title' => $visitor->getReferral()->getRawReferrer()]);
            else : ?>
                <?php echo Admin_Template::UnknownColumn() ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="wps-visitor__visitors-detail--row">
        <span><?php esc_html_e('Source Category', 'wp-statistics'); ?></span>
        <div class="wps-visitor__visitors-detail--link wps-ellipsis-parent">
            <?php if (!empty($visitor->getReferral()->getSourceChannel())) : ?>
                <span><?php echo esc_html($visitor->getReferral()->getSourceChannel()) ?></span>
            <?php else : ?>
                <?php echo Admin_Template::UnknownColumn() ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="wps-visitor__visitors-detail--row">
        <span><?php esc_html_e('Browser', 'wp-statistics'); ?></span>
        <div class="wps-browsers__flag">
            <a href="<?php echo Menus::admin_url('visitors', ['agent' => $visitor->getBrowser()->getRaw()]) ?>"><img src="<?php echo esc_url($visitor->getBrowser()->getLogo()); ?>" alt="<?php echo esc_attr($visitor->getBrowser()->getName()) ?>" width="15" height="15"></a>

            <?php if ($visitor->getBrowser()->getName() !== 'Unknown') : ?>
                <span 
                    title="<?php echo esc_attr("{$visitor->getBrowser()->getName()} v{$browserVersion}") ?>">
                    <?php echo esc_html("{$visitor->getBrowser()->getName()} v{$browserVersion}") ?>
                </span>
            <?php else : ?>
                <span title="<?php echo esc_attr($visitor->getBrowser()->getName()) ?>"><?php echo esc_html($visitor->getBrowser()->getName()) ?></span>
            <?php endif; ?>
        </div>
    </div>

    <div class="wps-visitor__visitors-detail--row">
        <span><?php esc_html_e('Operating System', 'wp-statistics'); ?></span>
        <div class="wps-os__flag">
            <a href="<?php echo Menus::admin_url('visitors', ['platform' => $visitor->getOs()->getName()]) ?>"><img src="<?php echo esc_url($visitor->getOs()->getLogo()) ?>" alt="<?php echo esc_attr($visitor->getOs()->getName()) ?>" width="15" height="15"></a>
            <span title="<?php echo esc_attr($visitor->getOs()->getName()) ?>"><?php echo esc_html($visitor->getOs()->getName()) ?></span>
        </div>
    </div>

    <div class="wps-visitor__visitors-detail--row">
        <span><?php esc_html_e('Country', 'wp-statistics'); ?></span>
        <div class="wps-country__flag">
            <a 
                href="<?php echo esc_url(Menus::admin_url(
                    'geographic', [
                        'type' => 'single-country',
                        'country' => $countryCode
                    ])
                ) ?>"
                class="wps-country__flag"
            >
                <img 
                    src="<?php echo esc_url($countryFlag) ?>" 
                    alt="<?php echo esc_attr($countryName) ?>"
                    width="19"
                    height="15"
                >
            </a>
            <span title="<?php echo esc_attr($countryName) ?>"><?php echo esc_html($countryName) ?></span>
        </div>
    </div>

    <div class="wps-visitor__visitors-detail--row">
        <span><?php esc_html_e('City', 'wp-statistics'); ?></span>
        <div class="wps-ellipsis-parent">
            <span 
                title="<?php echo Admin_Template::unknownToNotSet($city) ?>"
            >
                <?php echo Admin_Template::unknownToNotSet($city) ?>
            </span>
        </div>
    </div>

    <div class="wps-visitor__visitors-detail--row">
        <span><?php esc_html_e('Region', 'wp-statistics'); ?></span>
        <div class="wps-ellipsis-parent">
            <span 
                title="<?php echo Admin_Template::unknownToNotSet($regionName) ?>"
            >
                <?php echo Admin_Template::unknownToNotSet($regionName) ?>
            </span>
        </div>
    </div>

    <div class="wps-visitor__visitors-detail--row">
        <span><?php esc_html_e('First View', 'wp-statistics'); ?>&nbsp;</span>
        <div class="wps-ellipsis-parent">
            <span><?php echo esc_html($initialView) ?></span>
        </div>
    </div>

    <div class="wps-visitor__visitors-detail--row">
        <span><?php esc_html_e('Entry Page', 'wp-statistics'); ?></span>
        <div>
            <?php
            if (!empty($initialResource)) :
                View::load("components/objects/external-link", [
                    'url'     => $initialResourceLink,
                    'title'   => $initialResourceTitle,
                    'tooltip' => $initialesourceQuery
                ]);
            else :
                echo Admin_Template::UnknownColumn();
            endif;
            ?>
        </div>
    </div>
</div>
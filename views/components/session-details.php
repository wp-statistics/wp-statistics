<?php

use WP_STATISTICS\Admin_Template;
use WP_Statistics\Decorators\VisitorDecorator;
use WP_STATISTICS\Menus;

?>

<?php /** @var VisitorDecorator $visitor */ ?>

<div class="wps-visitor__visitors-details">
    <div class="wps-visitor__visitors-detail--row">
        <?php if ($visitor->isHashedIP()) : ?>
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
            <?php if (!empty($visitor->getReferral()->getReferrer())) : ?>
                <a href="<?php echo esc_url($visitor->getReferral()->getReferrer()) ?>">
                    <?php echo esc_html($visitor->getReferral()->getRawReferrer()) ?>
                </a>
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
                <span title="<?php echo esc_attr("{$visitor->getBrowser()->getName()} v{$visitor->getBrowser()->getVersion()}") ?>"><?php echo esc_html("{$visitor->getBrowser()->getName()} v{$visitor->getBrowser()->getVersion()}") ?></span>
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
            <a href="<?php echo esc_url(Menus::admin_url('geographic', ['type' => 'single-country', 'country' => $visitor->getLocation()->getCountryCode()])) ?>" class="wps-country__flag">
                <img src="<?php echo esc_url($visitor->getLocation()->getCountryFlag()) ?>" alt="<?php echo esc_attr($visitor->getLocation()->getCountryName()) ?>" width="19" height="15">
            </a>
            <span title="<?php echo esc_attr($visitor->getLocation()->getCountryName()) ?>"><?php echo esc_html($visitor->getLocation()->getCountryName()) ?></span>
        </div>
    </div>

    <div class="wps-visitor__visitors-detail--row">
        <span><?php esc_html_e('City', 'wp-statistics'); ?></span>
        <div class="wps-ellipsis-parent">
            <span title="<?php echo Admin_Template::unknownToNotSet($visitor->getLocation()->getCity()) ?>"><?php echo Admin_Template::unknownToNotSet($visitor->getLocation()->getCity()) ?></span>
        </div>
    </div>

    <div class="wps-visitor__visitors-detail--row">
        <span><?php esc_html_e('Region', 'wp-statistics'); ?></span>
        <div class="wps-ellipsis-parent">
            <span title="<?php echo Admin_Template::unknownToNotSet($visitor->getLocation()->getRegion()) ?>"><?php echo Admin_Template::unknownToNotSet($visitor->getLocation()->getRegion()) ?></span>
        </div>
    </div>

    <div class="wps-visitor__visitors-detail--row">
        <span><?php esc_html_e('First session', 'wp-statistics'); ?>&nbsp;</span>
        <div class="wps-ellipsis-parent">
            <span><?php echo esc_html($visitor->getFirstView()) ?></span>
        </div>
    </div>

    <div class="wps-visitor__visitors-detail--row">
        <span><?php esc_html_e('Number of visits', 'wp-statistics'); ?></span>
        <div><?php echo esc_html($visitor->getHits()) ?></div>
    </div>
</div>
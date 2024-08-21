<?php

use WP_STATISTICS\Admin_Template;
use WP_STATISTICS\Country;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Option;
use WP_STATISTICS\Referred;
use WP_STATISTICS\UserAgent;

?>

<div class="wps-visitor__visitors-details">
    <div class="wps-visitor__visitors-detail--row">

        <?php if (Option::get('hash_ips')) : ?>
            <span><?php esc_html_e('Daily Visitor Hash', 'wp-statistics'); ?></span>
            <div>
                <span title="<?php echo esc_attr($visitor->ip) ?>"><?php echo esc_html(substr($visitor->ip, 6, 10)) ?></span>
            </div>
        <?php else : ?>
            <span><?php esc_html_e('IP Address', 'wp-statistics'); ?></span>
            <div>
                <span title="<?php echo esc_attr($visitor->ip) ?>"><?php echo esc_html($visitor->ip) ?></span>
            </div>
        <?php endif; ?>
        
    </div>
    <div class="wps-visitor__visitors-detail--row">
        <span><?php esc_html_e('Referrer', 'wp-statistics'); ?></span>
        <div>
            <?php echo Referred::get_referrer_link($visitor->referred, '', true) ?>
        </div>
    </div>
    <div class="wps-visitor__visitors-detail--row">
        <span><?php esc_html_e('Browser', 'wp-statistics'); ?></span>
        <div class="wps-browsers__flag">
            <img src="<?php echo esc_url(UserAgent::getBrowserLogo($visitor->agent)) ?>" alt="<?php echo esc_attr($visitor->agent) ?>" width="15" height="15">
            <span title="<?php echo esc_attr($visitor->agent) ?>"><?php echo esc_html($visitor->agent) ?></span>
        </div>
    </div>
    <div class="wps-visitor__visitors-detail--row">
        <span><?php esc_html_e('Operating System', 'wp-statistics'); ?></span>
        <div class="wps-os__flag">
            <img src="<?php echo esc_url(UserAgent::getPlatformLogo($visitor->platform)) ?>" alt="<?php echo esc_attr($visitor->platform) ?>" width="15" height="15">
            <span title="<?php echo esc_attr($visitor->platform) ?>"><?php echo esc_html($visitor->platform) ?></span>
        </div>
    </div>
    <div class="wps-visitor__visitors-detail--row">
        <span><?php esc_html_e('Country', 'wp-statistics'); ?></span>
        <div class="wps-country__flag">
            <img src="<?php echo esc_url(Country::flag($visitor->location)) ?>" alt="<?php echo esc_attr(Country::getName($visitor->location)) ?>" width="19" height="15">
            <span title="<?php echo esc_attr(Country::getName($visitor->location)) ?>"><?php echo esc_html(Country::getName($visitor->location)) ?></span>
        </div>
    </div>
    <div class="wps-visitor__visitors-detail--row">
        <span><?php esc_html_e('City', 'wp-statistics'); ?></span>
        <div class="wps-ellipsis-parent">
            <?php if ($visitor->city) : ?>
                <span title="<?php echo esc_attr($visitor->city) ?>"><?php echo esc_attr($visitor->city) ?></span>
            <?php else : ?>
                <?php echo Admin_Template::UnknownColumn(); ?>
            <?php endif; ?>
        </div>
    </div>
    <div class="wps-visitor__visitors-detail--row">
        <span><?php esc_html_e('Region', 'wp-statistics'); ?></span>
        <div class="wps-ellipsis-parent">
            <?php if ($visitor->region) : ?>
                <span title="<?php echo esc_attr($visitor->region) ?>"><?php echo esc_attr($visitor->region) ?></span>
            <?php else : ?>
                <?php echo Admin_Template::UnknownColumn(); ?>
            <?php endif; ?>
        </div>
    </div>
    <div class="wps-visitor__visitors-detail--row">
        <span><?php esc_html_e('First session ', 'wp-statistics'); ?></span>
        <div class="wps-ellipsis-parent">
            <span><?php echo esc_html(date_i18n(Helper::getDefaultDateFormat(true), strtotime($visitor->first_hit))) ?></span>
        </div>
    </div>
    <div class="wps-visitor__visitors-detail--row">
        <span><?php esc_html_e('Number of visits', 'wp-statistics'); ?></span>
        <div><?php echo esc_html($visitor->hits) ?></div>
    </div>
</div>
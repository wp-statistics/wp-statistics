<?php

use WP_STATISTICS\Admin_Template;
use WP_Statistics\Decorators\VisitorDecorator;
use WP_STATISTICS\Menus;
use WP_Statistics\Components\View;
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
            <a href="<?php echo Menus::admin_url('visitors', ['tab' => 'visitors','agent' => $visitor->getBrowser()->getRaw()]) ?>"><img src="<?php echo esc_url($visitor->getBrowser()->getLogo()); ?>" alt="<?php echo esc_attr($visitor->getBrowser()->getName()) ?>" width="15" height="15"></a>

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
            <a href="<?php echo Menus::admin_url('visitors', ['tab' => 'visitors','platform' => $visitor->getOs()->getName()]) ?>"><img src="<?php echo esc_url($visitor->getOs()->getLogo()) ?>" alt="<?php echo esc_attr($visitor->getOs()->getName()) ?>" width="15" height="15"></a>
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
        <span><?php esc_html_e('First View', 'wp-statistics'); ?>&nbsp;</span>
        <div class="wps-ellipsis-parent">
            <span><?php echo esc_html($visitor->getFirstView() ?? $visitor->getLastCounter()) ?></span>
        </div>
    </div>

    <div class="wps-visitor__visitors-detail--row">
        <span><?php esc_html_e('Entry Page', 'wp-statistics'); ?></span>
        <div>
            <?php
            $page = $visitor->getFirstPage();

            if (!empty($page)) :
                View::load("components/objects/internal-link", [
                    'url'     => $page['report'],
                    'title'   => $page['title'],
                    'tooltip' => $page['query'] ? "?{$page['query']}" : ''
                ]) ;
            else :
                echo Admin_Template::UnknownColumn();
            endif;
            ?>
        </div>
    </div>

    <div class="wps-visitor__visitors-detail--row">
        <span><?php esc_html_e('Exit Page', 'wp-statistics'); ?>&nbsp;</span>
        <div>
            <?php
            $page = $visitor->getLastPage();

            if (!empty($page)) :
                View::load("components/objects/internal-link", [
                    'url'     => $page['report'],
                    'title'   => $page['title']
                ]);
            else :
                echo Admin_Template::UnknownColumn();
            endif;
            ?>
        </div>
    </div>

    <div class="wps-visitor__visitors-detail--row">
        <span><?php esc_html_e('Total Views', 'wp-statistics'); ?> <span class="wps-tooltip" title="<?php esc_html_e('Total views for a single day. Privacy rules assign users a new ID daily, so visits on different days are counted separately.', 'wp-statistics'); ?>"><i class="wps-tooltip-icon"></i></span></span>
        <div>
            <span><?php echo esc_html($visitor->getHits()) ?></span>
        </div>
    </div>
</div>
<?php
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Decorators\VisitorDecorator;
use WP_STATISTICS\Menus;
use WP_Statistics\Components\View;

/** @var VisitorDecorator $firstSession $lastSession */
$lastSession  = reset($data['sessions'])['session'];
$firstSession = end($data['sessions'])['session'];

$hits = array_map(function ($data) {
    return $data['session']->getHitsRaw();
}, $data['sessions']);
$hits = array_sum($hits);
?>

<div class="wps-visitor__visitors-details">
    <div class="wps-visitor__visitors-detail--row">
        <span><?php esc_html_e('First Referrer', 'wp-statistics'); ?></span>
        <div class="wps-visitor__visitors-detail--link wps-ellipsis-parent">
            <?php if (!empty($firstSession->getReferral()->getReferrer())) :
                View::load("components/objects/external-link", ['url' => $firstSession->getReferral()->getReferrer(), 'title' => $firstSession->getReferral()->getRawReferrer()]);
            else : ?>
                <?php echo Admin_Template::UnknownColumn() ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="wps-visitor__visitors-detail--row">
        <span><?php esc_html_e('Source Category', 'wp-statistics'); ?></span>
        <div class="wps-visitor__visitors-detail--link wps-ellipsis-parent">
            <?php if (!empty($lastSession->getReferral()->getSourceChannel())) : ?>
                <span><?php echo esc_html($lastSession->getReferral()->getSourceChannel()) ?></span>
            <?php else : ?>
                <?php echo Admin_Template::UnknownColumn() ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="wps-visitor__visitors-detail--row">
        <span><?php esc_html_e('Browser', 'wp-statistics'); ?></span>
        <div class="wps-browsers__flag">
            <a href="<?php echo Menus::admin_url('visitors', ['agent' => $lastSession->getBrowser()->getRaw()]) ?>"><img src="<?php echo esc_url($lastSession->getBrowser()->getLogo()); ?>" alt="<?php echo esc_attr($lastSession->getBrowser()->getName()) ?>" width="15" height="15"></a>

            <?php if ($lastSession->getBrowser()->getName() !== 'Unknown') : ?>
                <span title="<?php echo esc_attr("{$lastSession->getBrowser()->getName()} v{$lastSession->getBrowser()->getVersion()}") ?>"><?php echo esc_html("{$lastSession->getBrowser()->getName()} v{$lastSession->getBrowser()->getVersion()}") ?></span>
            <?php else : ?>
                <span title="<?php echo esc_attr($lastSession->getBrowser()->getName()) ?>"><?php echo esc_html($lastSession->getBrowser()->getName()) ?></span>
            <?php endif; ?>
        </div>
    </div>

    <div class="wps-visitor__visitors-detail--row">
        <span><?php esc_html_e('Operating System', 'wp-statistics'); ?></span>
        <div class="wps-os__flag">
            <a href="<?php echo Menus::admin_url('visitors', ['platform' => $lastSession->getOs()->getName()]) ?>"><img src="<?php echo esc_url($lastSession->getOs()->getLogo()) ?>" alt="<?php echo esc_attr($lastSession->getOs()->getName()) ?>" width="15" height="15"></a>
            <span title="<?php echo esc_attr($lastSession->getOs()->getName()) ?>"><?php echo esc_html($lastSession->getOs()->getName()) ?></span>
        </div>
    </div>

    <div class="wps-visitor__visitors-detail--row">
        <span><?php esc_html_e('Country', 'wp-statistics'); ?></span>
        <div class="wps-country__flag">
            <a href="<?php echo esc_url(Menus::admin_url('geographic', ['type' => 'single-country', 'country' => $lastSession->getLocation()->getCountryCode()])) ?>" class="wps-country__flag">
                <img src="<?php echo esc_url($lastSession->getLocation()->getCountryFlag()) ?>" alt="<?php echo esc_attr($lastSession->getLocation()->getCountryName()) ?>" width="19" height="15">
            </a>
            <span title="<?php echo esc_attr($lastSession->getLocation()->getCountryName()) ?>"><?php echo esc_html($lastSession->getLocation()->getCountryName()) ?></span>
        </div>
    </div>

    <div class="wps-visitor__visitors-detail--row">
        <span><?php esc_html_e('City', 'wp-statistics'); ?></span>
        <div class="wps-ellipsis-parent">
            <span title="<?php echo Admin_Template::unknownToNotSet($lastSession->getLocation()->getCity()) ?>"><?php echo Admin_Template::unknownToNotSet($lastSession->getLocation()->getCity()) ?></span>
        </div>
    </div>

    <div class="wps-visitor__visitors-detail--row">
        <span><?php esc_html_e('Region', 'wp-statistics'); ?></span>
        <div class="wps-ellipsis-parent">
            <span title="<?php echo Admin_Template::unknownToNotSet($lastSession->getLocation()->getRegion()) ?>"><?php echo Admin_Template::unknownToNotSet($lastSession->getLocation()->getRegion()) ?></span>
        </div>
    </div>

    <div class="wps-visitor__visitors-detail--row">
        <span><?php esc_html_e('First View', 'wp-statistics'); ?>&nbsp;</span>
        <div class="wps-ellipsis-parent">
            <span><?php echo esc_html($firstSession->getFirstView() ?? $firstSession->getLastCounter()) ?></span>
        </div>
    </div>

    <div class="wps-visitor__visitors-detail--row">
        <span><?php esc_html_e('Entry Page', 'wp-statistics'); ?></span>
        <div>
            <?php
            $page = $firstSession->getFirstPage();

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
            $page = $lastSession->getLastPage();

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
        <span><?php esc_html_e('Total Views', 'wp-statistics'); ?></span>
        <div>
            <span><?php echo esc_html(number_format_i18n($hits)); ?></span>
        </div>
    </div>
</div>
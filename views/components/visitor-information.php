<?php
namespace WP_STATISTICS;

use WP_Statistics\Service\Analytics\VisitorDecorator;

?>

<?php /** @var VisitorDecorator $visitor */ ?>
<ul class="wps-visitor__information--container">
    <li class="wps-visitor__information">
        <div class="wps-tooltip" title="<?php echo esc_attr("{$visitor->getBrowserName()} v{$visitor->getBrowserVersion()}") ?>">
            <a href="<?php echo esc_url(Menus::admin_url('visitors', ['agent' => $visitor->getBrowser()])) ?>">
                <img src="<?php echo esc_url($visitor->getBrowserLogo()) ?>" alt="<?php echo esc_attr($visitor->getBrowserName()) ?>" width="15" height="15">
            </a>
        </div>
    </li>

    <li class="wps-visitor__information">
        <div class="wps-tooltip" title="<?php echo esc_attr($visitor->getOs()) ?>">
            <a href="<?php echo esc_url(Menus::admin_url('visitors', ['platform' => $visitor->getOs()])) ?>">
                <img src="<?php echo esc_url($visitor->getOsLogo()) ?>" alt="<?php echo esc_attr($visitor->getOs()) ?>" width="15" height="15">
            </a>
        </div>
    </li>

    <?php if ($visitor->isLoggedInUser()) : ?>
        <li class="wps-visitor__information">
            <div>
                <?php if (Option::get('visitors_log')): ?>
                    <a href="<?php echo esc_url(Menus::admin_url('visitors', ['type' => 'single-visitor', 'visitor_id' => $visitor->getId()])); ?>">
                        <span class="wps-visitor__information__user-img"></span>
                    </a>
                    <a class="wps-visitor__information__user-text" href="<?php echo esc_url(Menus::admin_url('visitors', ['type' => 'single-visitor', 'visitor_id' => $visitor->getId()])); ?>">
                        <span><?php echo esc_html($visitor->getUserName()) ?></span>
                        <span>#<?php echo esc_html($visitor->getUserId()) ?></span>
                    </a>
                <?php else: ?>
                    <div class="wps-tooltip" data-tooltip-content="#tooltip_user_id">
                        <a href="<?php echo esc_url(Menus::admin_url('visitors', ['type' => 'single-visitor', 'visitor_id' => $visitor->getId()])); ?>"><span class="wps-visitor__information__user-img"></span></a>
                    </div>
                    <div class="wps-tooltip_templates">
                        <div id="tooltip_user_id">
                            <div>
                                <?php esc_html_e('ID:', 'wp-statistics'); ?>
                                <?php echo esc_html($visitor->getUserId()); ?>
                            </div>

                            <div>
                                <?php esc_html_e('Name:', 'wp-statistics'); ?>
                                <?php echo esc_html($visitor->getUserName()); ?>
                            </div>

                            <div>
                                <?php esc_html_e('Email:', 'wp-statistics'); ?>
                                <?php echo esc_html($visitor->getUserEmail()); ?>
                            </div>

                            <div>
                                <?php
                                    echo sprintf(
                                        $visitor->isHashedIP() ? esc_html__('Daily Visitor Hash: %s', 'wp-statistics') : esc_html__('IP: %s', 'wp-statistics'),
                                        $visitor->getIP()
                                    );
                                ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </li>
    <?php else : ?>
        <li class="wps-visitor__information">
            <div>
                <?php if (Option::get('visitors_log')): ?>
                    <a href="<?php echo esc_url(Menus::admin_url('visitors', ['type' => 'single-visitor', 'visitor_id' => $visitor->getId()])) ?>">
                        <span class="wps-visitor__information__incognito-img"></span>
                    </a>
                    <span class="wps-visitor__information__incognito-text">
                        <?php echo $visitor->getIP(); ?>
                    </span>
                <?php else: ?>
                    <div class="wps-tooltip" title="<?php echo sprintf($visitor->isHashedIP() ? esc_html__('Daily Visitor Hash: %s', 'wp-statistics') : esc_html__('IP: %s', 'wp-statistics'), $visitor->getIP()) ?>">
                        <a href="<?php echo esc_url(Menus::admin_url('visitors', ['type' => 'single-visitor', 'visitor_id' => $visitor->getId()])) ?>"><span class="wps-visitor__information__incognito-img"></span></a>
                    </div>
                <?php endif; ?>
            </div>
        </li>
    <?php endif; ?>
</ul>
<?php if (!empty($visitor->user_id) && Option::get('visitors_log')) : ?>
    <div class="wps-visitor__information__user-more-info">
        <div>
            <?php esc_html_e('Email:', 'wp-statistics') ?>
            <?php echo esc_html($visitor->getUserEmail()) ?>
        </div>

        <div>
            <?php esc_html_e('Role:', 'wp-statistics') ?>
            <span class="c-capitalize"><?php echo esc_html($visitor->getUserRole()) ?></span>
        </div>
    </div>
<?php endif; ?>
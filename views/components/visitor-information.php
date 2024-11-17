<?php
namespace WP_STATISTICS;

use WP_Statistics\Decorators\VisitorDecorator;

?>

<?php /** @var VisitorDecorator $visitor */ ?>
<ul class="wps-visitor__information--container">


    <li class="wps-visitor__information">
        <div class="wps-tooltip" title="<?php echo esc_attr($visitor->getOs()->getName()) ?>">
            <a href="<?php echo esc_url(Menus::admin_url('visitors', ['platform' => $visitor->getOs()->getName()])) ?>">
                <img src="<?php echo esc_url($visitor->getOs()->getLogo()) ?>" alt="<?php echo esc_attr($visitor->getOs()->getName()) ?>" width="15" height="15">
            </a>
        </div>
    </li>

    <li class="wps-visitor__information">
        <div class="wps-tooltip" title="<?php echo $visitor->getBrowser()->getName() !== '(not set)' ? esc_attr("{$visitor->getBrowser()->getName()} v{$visitor->getBrowser()->getVersion()}") : $visitor->getBrowser()->getName(); ?>">
            <a href="<?php echo esc_url(Menus::admin_url('visitors', ['agent' => $visitor->getBrowser()->getRaw()])) ?>">
                <img src="<?php echo esc_url($visitor->getBrowser()->getLogo()) ?>" alt="<?php echo esc_attr($visitor->getBrowser()->getName()) ?>" width="15" height="15">
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
                    <a class="wps-visitor__information__user-text wps-tooltip" title="<?php echo esc_html($visitor->getUser()->getEmail()) ?> (<?php echo esc_html($visitor->getUser()->getRole()) ?>)" href="<?php echo esc_url(Menus::admin_url('visitors', ['type' => 'single-visitor', 'visitor_id' => $visitor->getId()])); ?>">
                        <span title="<?php echo esc_html($visitor->getUser()->getDisplayName()) ?>"><?php echo esc_html($visitor->getUser()->getDisplayName()) ?></span>
                        <span>#<?php echo esc_html($visitor->getUser()->getId()) ?></span>
                    </a>
                <?php else: ?>
                    <div class="wps-tooltip" data-tooltip-content="#tooltip_user_id">
                        <a href="<?php echo esc_url(Menus::admin_url('visitors', ['type' => 'single-visitor', 'visitor_id' => $visitor->getId()])); ?>"><span class="wps-visitor__information__user-img"></span></a>
                    </div>
                    <div class="wps-tooltip_templates">
                        <div id="tooltip_user_id">
                            <div>
                                <?php esc_html_e('ID', 'wp-statistics'); ?>:
                                <?php echo esc_html($visitor->getUser()->getId()); ?>
                            </div>

                            <div>
                                <?php esc_html_e('Name', 'wp-statistics'); ?>:
                                <?php echo esc_html($visitor->getUser()->getDisplayName()); ?>
                            </div>

                            <div>
                                <?php esc_html_e('Email', 'wp-statistics'); ?>:
                                <?php echo esc_html($visitor->getUser()->getEmail()); ?>
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

                    <a class="wps-visitor__information__incognito-text" href="<?php echo esc_url(Menus::admin_url('visitors', ['type' => 'single-visitor', 'visitor_id' => $visitor->getId()])) ?>">
                        <?php echo esc_html($visitor->getIP()); ?>
                    </a>

                <?php else: ?>
                    <div class="wps-tooltip" title="<?php echo sprintf($visitor->isHashedIP() ? esc_html__('Daily Visitor Hash: %s', 'wp-statistics') : esc_html__('IP: %s', 'wp-statistics'), $visitor->getIP()) ?>">
                        <a href="<?php echo esc_url(Menus::admin_url('visitors', ['type' => 'single-visitor', 'visitor_id' => $visitor->getId()])) ?>"><span class="wps-visitor__information__incognito-img"></span></a>
                    </div>
                <?php endif; ?>
            </div>
        </li>
    <?php endif; ?>
</ul>
<?php

namespace WP_STATISTICS;

use WP_Statistics\Decorators\VisitorDecorator;

?>

    <?php /** @var VisitorDecorator $visitor */ ?>

    <?php if (!empty($visitor)) : ?>
    <ul class="wps-visitor__information--container">
        <li class="wps-visitor__information">
            <?php
            $countryName = $visitor->getLocation()->getCountryName();
            $location = Admin_Template::locationColumn($visitor->getLocation()->getCountryCode(), $visitor->getLocation()->getRegion(), $visitor->getLocation()->getCity());
            $locationWithCountry = $countryName !== __('(not set)', 'wp-statistics') ? $countryName . ', ' . $location : $location;
            ?>
            <div class="wps-tooltip" title="<?php echo esc_attr($locationWithCountry) ?>">
                <a href="<?php echo esc_url(Menus::admin_url('geographic', ['type' => 'single-country', 'country' => $visitor->getLocation()->getCountryCode()])) ?>">
                    <img src="<?php echo esc_url($visitor->getLocation()->getCountryFlag()) ?>" alt="<?php echo esc_attr($visitor->getLocation()->getCountryName()) ?>" width="16" height="14" class="flag">
                </a>
            </div>
        </li>
        <li class="wps-visitor__information">
            <div class="wps-tooltip" title="<?php echo esc_attr($visitor->getOs()->getName()) ?>">
                <a href="<?php echo esc_url(Menus::admin_url('visitors', ['platform' => $visitor->getOs()->getName()])) ?>">
                    <img src="<?php echo esc_url($visitor->getOs()->getLogo()) ?>" alt="<?php echo esc_attr($visitor->getOs()->getName()) ?>" width="15" height="15">
                </a>
            </div>
        </li>

        <li class="wps-visitor__information">
            <div class="wps-tooltip" title="<?php echo esc_attr($visitor->getDevice()->getType()) ?>">
                <img src="<?php echo esc_url($visitor->getDevice()->getLogo()) ?>" alt="<?php echo esc_attr($visitor->getDevice()->getType()) ?>" width="15" height="15">
            </div>
        </li>

        <li class="wps-visitor__information">
            <div class="wps-tooltip" title="<?php echo $visitor->getBrowser()->getName() !== __('(not set)', 'wp-statistics') ? esc_attr("{$visitor->getBrowser()->getName()} v{$visitor->getBrowser()->getVersion()}") : $visitor->getBrowser()->getName(); ?>">
                <a href="<?php echo esc_url(Menus::admin_url('visitors', ['agent' => $visitor->getBrowser()->getRaw()])) ?>">
                    <img src="<?php echo esc_url($visitor->getBrowser()->getLogo()) ?>" alt="<?php echo esc_attr($visitor->getBrowser()->getName()) ?>" width="15" height="15">
                </a>
            </div>
        </li>

        <?php if ($visitor->isLoggedInUser() && Option::get('visitors_log')) : ?>
            <li class="wps-visitor__information">
                <div>
                    <a aria-label="Visitor Information" href="<?php echo esc_url(Menus::admin_url('visitors', ['type' => 'single-visitor', 'visitor_id' => $visitor->getId()])); ?>">
                        <span class="wps-visitor__information__user-img"></span>
                    </a>
                    <a class="wps-visitor__information__user-text wps-tooltip" title="<?php echo esc_html($visitor->getUser()->getEmail()) ?> (<?php echo esc_html($visitor->getUser()->getRole()) ?>)" href="<?php echo esc_url(Menus::admin_url('visitors', ['type' => 'single-visitor', 'visitor_id' => $visitor->getId()])); ?>">
                        <span title="<?php echo esc_html($visitor->getUser()->getDisplayName()) ?>"><?php echo esc_html($visitor->getUser()->getDisplayName()) ?></span>
                        <span>#<?php echo esc_html($visitor->getUser()->getId()) ?></span>
                    </a>
                </div>
            </li>
        <?php else : ?>
            <li class="wps-visitor__information">
                <div>
                    <a aria-label="visitor information" href="<?php echo esc_url(Menus::admin_url('visitors', ['type' => 'single-visitor', 'visitor_id' => $visitor->getId()])) ?>">
                        <span class="wps-visitor__information__incognito-img"></span>
                    </a>

                    <a class="wps-visitor__information__incognito-text" href="<?php echo esc_url(Menus::admin_url('visitors', ['type' => 'single-visitor', 'visitor_id' => $visitor->getId()])) ?>">
                        <?php echo esc_html($visitor->getIP()); ?>
                    </a>
                </div>
            </li>
        <?php endif; ?>
    </ul>
<?php endif; ?>
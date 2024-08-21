<?php 
use WP_STATISTICS\Admin_Template;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Country;
use WP_STATISTICS\Menus;
use WP_STATISTICS\Referred;
use WP_STATISTICS\UserAgent;
use WP_STATISTICS\Visitor;
?>

<div class="inside">
    <?php if (!empty($data)) : ?>
        <div class="o-table-wrapper">
            <table width="100%" class="o-table wps-new-table">
                <thead>
                    <tr>
                        <th class="wps-pd-l">
                            <span class="wps-order"><?php esc_html_e('Last View', 'wp-statistics') ?></span>
                        </th>
                        <th class="wps-pd-l">
                            <span><?php esc_html_e('Online For', 'wp-statistics') ?></span>
                        </th>
                        <th class="wps-pd-l">
                            <?php esc_html_e('Visitor Information', 'wp-statistics') ?>
                        </th>
                        <th class="wps-pd-l">
                            <?php esc_html_e('Location', 'wp-statistics') ?>
                        </th>
                        <th class="wps-pd-l">
                            <?php esc_html_e('Referrer', 'wp-statistics') ?>
                        </th>
                        <th class="wps-pd-l">
                            <?php echo esc_html__('Latest Page', 'wp-statistics'); ?>
                        </th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($data as $visitor) :  
                        $page = Visitor::get_page_by_id($visitor->page_id);
                    ?>
                        <tr>
                            <td class="wps-pd-l"><?php echo esc_html(date_i18n(Helper::getDefaultDateFormat(true), strtotime($visitor->date))) ?></td>

                            <td class="wps-pd-l"><?php echo esc_html(date_i18n('H:i:s', $visitor->timestamp - $visitor->created)) ?></td>

                            <td class="wps-pd-l">
                                <ul class="wps-browsers__flags">
                                    <?php if (!empty($visitor->user_id)) : ?>
                                        <li class="wps-browsers__flag">
                                            <div class="wps-tooltip" data-tooltip-content="#tooltip_user_id">
                                                <a href="<?php echo esc_url(Menus::admin_url('visitors', ['user_id' => $visitor->user_id])) ?>"><img src="<?php echo esc_url(WP_STATISTICS_URL . 'assets/images/user-icon.svg') ?>" alt="user" width="15" height="15"></a>
                                            </div>
                                            <div class="wps-tooltip_templates">
                                                <div id="tooltip_user_id">
                                                    <div><?php esc_html_e('ID: ', 'wp-statistics') ?> <?php echo esc_html($visitor->user_id) ?></div>
                                                    <div><?php esc_html_e('Name: ', 'wp-statistics') ?> <?php echo esc_html($visitor->display_name) ?></div>
                                                    <div><?php esc_html_e('Email: ', 'wp-statistics') ?> <?php echo esc_html($visitor->user_email) ?></div>
                                                </div>
                                            </div>
                                        </li>
                                    <?php else : ?>
                                        <li class="wps-browsers__flag">
                                            <div class="wps-tooltip" title="<?php esc_attr_e('Incognito', 'wp-statistics') ?>">
                                                <a href=""><img src="<?php echo esc_url(WP_STATISTICS_URL . 'assets/images/incognito-user.svg') ?>" alt="<?php esc_attr_e('Incognito', 'wp-statistics') ?>" width="15" height="15"></a>
                                            </div>
                                        </li>
                                    <?php endif; ?>

                                    <li class="wps-browsers__flag">
                                        <div class="wps-tooltip" title="<?php echo esc_attr($visitor->agent) ?>">
                                            <a href="<?php echo esc_url(Menus::admin_url('visitors', ['agent' => $visitor->agent])) ?>"><img src="<?php echo esc_url(UserAgent::getBrowserLogo($visitor->agent)) ?>" alt="<?php echo esc_attr($visitor->agent) ?>" width="15" height="15"></a>
                                        </div>
                                    </li>

                                    <li class="wps-browsers__flag">
                                        <div class="wps-tooltip" title="<?php echo esc_attr($visitor->platform) ?>">
                                            <a href="<?php echo esc_url(Menus::admin_url('visitors', ['platform' => $visitor->platform])) ?>"><img src="<?php echo esc_url(UserAgent::getPlatformLogo($visitor->platform)) ?>" alt="<?php echo esc_attr($visitor->platform) ?>" width="15" height="15"></a>
                                        </div>
                                    </li>
                                </ul>
                            </td>

                            <td class="wps-pd-l">
                                <div class="wps-country-flag wps-ellipsis-parent">
                                    <div class="wps-tooltip" title="<?php echo esc_attr(Country::getName($visitor->location)) ?>">
                                        <img src="<?php echo esc_url(Country::flag($visitor->location)) ?>" alt="<?php echo esc_attr("{$visitor->region}, {$visitor->city}") ?>" width="15" height="15">
                                    </div>
                                    <?php 
                                        $region = empty($visitor->region) ? esc_html__('Unknown', 'wp-statistics') : $visitor->region;
                                        $city   = empty($visitor->city) ? esc_html__('Unknown', 'wp-statistics') : $visitor->city;
                                    ?>
                                    <span class="wps-ellipsis-text" title="<?php echo esc_attr("$region, $city") ?>"><?php echo esc_html("$region, $city") ?></span>
                                </div>
                            </td>

                            <td class="wps-pd-l">
                                <?php if (!empty($visitor->referred)) : ?>
                                    <span class="wps-link-arrow"><?php echo Referred::get_referrer_link($visitor->referred, '', true); ?></span>
                                <?php else : ?>
                                    <?php echo Admin_Template::UnknownColumn() ?>
                                <?php endif; ?>
                            </td>

                            <td class="wps-pd-l">
                                <?php if (!empty($page)) : ?>
                                    <a target="_blank" href="<?php echo esc_url($page['link']) ?>" title="<?php echo esc_attr($page['title']) ?>" class="wps-link-arrow">
                                        <span><?php echo esc_html($page['title']) ?></span>
                                    </a>
                                <?php else : ?>
                                    <?php echo Admin_Template::UnknownColumn() ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else : ?>
        <div class="o-wrap o-wrap--no-data wps-center">
            <?php esc_html_e('No recent data available.', 'wp-statistics') ?>
        </div>
    <?php endif; ?>
</div>
<?php echo isset($pagination) ? $pagination : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
?>
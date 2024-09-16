<?php

use WP_Statistics\Components\View;

?>
<div class="wps-wrap__main">
    <div class="postbox-container wps-postbox-addon-container">
        <div class="metabox-holder">
            <div class="meta-box-sortables">
                <div class="wps-postbox-addon">
                    <div>
                        <h2 class="wps-postbox-addon__title"><?php esc_html_e('Active Add-Ons', 'wp-statistics'); ?></h2>
                        <div class="wps-postbox-addon__items">
                            <?php
                            $data_plus_args = [
                                'title'              => esc_html__('Data Plus', 'wp-statistics'),
                                'version'            => '2.3.2',
                                'icon'               => 'data-plus.svg',
                                'status_text'        => esc_html__('Activated', 'wp-statistics'),
                                'status_class'       => 'success',
                                'has_license_btn'    => true,
                                'setting_link'       => '#',
                                'detail_link'        => '#',
                                'change_log_link'    => '#',
                                'documentation_link' => '#',
                                'description'        => esc_html__('Track custom post types, taxonomies, links, and download tracker.', 'wp-statistics')
                            ];
                            View::load("components/addon-box", $data_plus_args);

                            $real_time_args = [
                                'title'              => esc_html__('Real-time Stats', 'wp-statistics'),
                                'version'            => '1.2',
                                'icon'               => 'real-time.svg',
                                'status_text'        => esc_html__('Activated', 'wp-statistics'),
                                'status_class'       => 'success',
                                'has_license_btn'    => true,
                                'setting_link'       => '#',
                                'detail_link'        => '#',
                                'change_log_link'    => '#',
                                'documentation_link' => '#',
                                'description'        => esc_html__('Monitor visitors and online users in real-time without refreshing.', 'wp-statistics')
                            ];
                            View::load("components/addon-box", $real_time_args);

                            $advance_report_args = [
                                'title'              => esc_html__('Advanced Reporting', 'wp-statistics'),
                                'version'            => '2.6.1',
                                'icon'               => 'advance-report.svg',
                                'status_text'        => esc_html__('Activated', 'wp-statistics'),
                                'status_class'       => 'success',
                                'label_text'         => esc_html__('New', 'wp-statistics'),
                                'label_class'        => 'new',
                                'has_license_btn'    => true,
                                'setting_link'       => '#',
                                'detail_link'        => '#',
                                'change_log_link'    => '#',
                                'documentation_link' => '#',
                                'description'        => esc_html__('Automated performance stats delivered directly to your inbox.', 'wp-statistics')
                            ];
                            View::load("components/addon-box", $advance_report_args);

                            $mini_chart_args = [
                                'title'              => esc_html__('Mini Chart', 'wp-statistics'),
                                'version'            => '2.6.1',
                                'icon'               => 'mini-chart.svg',
                                'status_text'        => esc_html__('Activated', 'wp-statistics'),
                                'status_class'       => 'success',
                                'label_text'         => esc_html__('Updated', 'wp-statistics'),
                                'label_class'        => 'updated',
                                'has_license_btn'    => true,
                                'setting_link'       => '#',
                                'detail_link'        => '#',
                                'change_log_link'    => '#',
                                'documentation_link' => '#',
                                'description'        => esc_html__('Analyze post and page performance with customizable charts.', 'wp-statistics')
                            ];
                            View::load("components/addon-box", $mini_chart_args);
                            ?>
                        </div>
                    </div>
                    <div>
                        <h2 class="wps-postbox-addon__title"><?php esc_html_e('Inactive Add-Ons', 'wp-statistics'); ?></h2>
                        <div class="wps-postbox-addon__items">


                            <?php
                            $customization_args = [
                                'title'              => esc_html__('Customization', 'wp-statistics'),
                                'version'            => '4.1',
                                'icon'               => 'customization.svg',
                                'status_text'        => esc_html__('Needs License', 'wp-statistics'),
                                'status_class'       => 'danger',
                                'has_license_btn'    => true,
                                'detail_link'        => '#',
                                'change_log_link'    => '#',
                                'documentation_link' => '#',
                                'description'        => esc_html__('Manage admin menus, edit plugin header, and customize plugin.', 'wp-statistics')
                            ];
                            View::load("components/addon-box", $customization_args);

                            $rest_api_args = [
                                'title'              => esc_html__('REST API', 'wp-statistics'),
                                'version'            => '4.1',
                                'icon'               => 'rest-api.svg',
                                'status_text'        => esc_html__('Installed', 'wp-statistics'),
                                'status_class'       => 'primary',
                                'active_link'        => '#',
                                'detail_link'        => '#',
                                'change_log_link'    => '#',
                                'documentation_link' => '#',
                                'description'        => esc_html__('Enable WP Statistics endpoints in the REST API.', 'wp-statistics')
                            ];
                            View::load("components/addon-box", $rest_api_args);


                            $advance_args = [
                                'title'              => esc_html__('Advanced Widget', 'wp-statistics'),
                                'version'            => '3.5',
                                'icon'               => 'advance.svg',
                                'status_text'        => esc_html__('Not Installed', 'wp-statistics'),
                                'status_class'       => 'disable',
                                'detail_link'        => '#',
                                'change_log_link'    => '#',
                                'documentation_link' => '#',
                                'description'        => esc_html__('Use Gutenberg blocks or theme widgets to display statistical data.', 'wp-statistics')
                            ];
                            View::load("components/addon-box", $advance_args);

                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

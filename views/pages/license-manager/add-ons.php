<?php

use WP_Statistics\Components\View;

$activeAddOns   = [];
$inactiveAddOns = [];
if (!empty($data['addons'])) {
    foreach ($data['addons'] as $addOn) {
        if ($addOn['isActive']) {
            $activeAddOns[] = $addOn;
        } else {
            $inactiveAddOns[] = $addOn;
        }
    }
}
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
                            foreach ($activeAddOns as $addOn) {
                                // @todo Dynamic value for these.
                                $labelText  = esc_html__('Updated', 'wp-statistics');
                                $labelClass = 'updated';

                                $args = [
                                    'title'              => esc_html($addOn['name']),
                                    'version'            => esc_html($addOn['version']),
                                    'icon'               => 'data-plus.svg', // @todo Dynamic icon URL.
                                    'status_text'        => esc_html__('Activated', 'wp-statistics'),
                                    'status_class'       => 'success',
                                    'label_text'         => $labelText,
                                    'label_class'        => $labelClass,
                                    'has_license_btn'    => true,
                                    'setting_link'       => '#',
                                    'detail_link'        => '#',
                                    'change_log_link'    => '#',
                                    'documentation_link' => '#',
                                    'description'        => esc_html($addOn['description']),
                                ];
                                View::load("components/addon-box", $args);
                            }
                            ?>
                        </div>
                    </div>
                    <div>
                        <h2 class="wps-postbox-addon__title"><?php esc_html_e('Inactive Add-Ons', 'wp-statistics'); ?></h2>
                        <div class="wps-postbox-addon__items">
                            <?php
                            foreach ($inactiveAddOns as $addOn) {
                                // @todo Add "Needs License" status.
                                $statusText  = $addOn['isInstalled'] ? esc_html__('Installed', 'wp-statistics') : esc_html__('Not Installed', 'wp-statistics');
                                $statusClass = $addOn['isInstalled'] ? 'primary' : 'disable';

                                // @todo Dynamic value for these.
                                $labelText   = esc_html__('Updated', 'wp-statistics');
                                $labelClass  = 'updated';

                                $args = [
                                    'title'              => esc_html($addOn['name']),
                                    'version'            => esc_html($addOn['version']),
                                    'icon'               => 'data-plus.svg', // @todo Dynamic icon URL.
                                    'status_text'        => $statusText,
                                    'status_class'       => $statusClass,
                                    'label_text'         => $labelText,
                                    'label_class'        => $labelClass,
                                    'has_license_btn'    => true,
                                    'setting_link'       => '#',
                                    'detail_link'        => '#',
                                    'change_log_link'    => '#',
                                    'documentation_link' => '#',
                                    'description'        => esc_html($addOn['description']),
                                ];
                                View::load("components/addon-box", $args);
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
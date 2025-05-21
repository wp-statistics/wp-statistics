<?php

use WP_Statistics\Components\View;
use WP_Statistics\Service\Admin\ModalHandler\Modal;

View::load('components/objects/share-anonymous-notice');
?>
<div class="wps-wrap__main">
    <div class="wp-header-end"></div>
    <div id="poststuff" class="wps-wrap__settings">
        <?php
        Modal::render('setting-confirmation', [
            'title'                => __('Confirmation', 'wp-statistics'),
            'description'          => __('Are you sure you want to permanently delete this data?', 'wp-statistics'),
            'primaryButtonText'    => __('Yes', 'wp-statistics'),
            'primaryButtonStyle'   => 'danger',
            'secondaryButtonText'  => __('Cancel', 'wp-statistics'),
            'secondaryButtonStyle' => 'cancel',
            'showCloseButton'      => true,
            'alert'                => __('This action cannot be undone.', 'wp-statistics'),
            'actions'              => [
                'primary'   => 'resolve',
                'secondary' => 'closeModal',
            ],
        ]);
        ?>
        <div id="post-body" class="metabox-holder wps-optimizationPageFlex">
            <button class="wps-gototop" aria-label="Go to top of page"></button>
            <?php include WP_STATISTICS_DIR . 'includes/admin/templates/layout/menu-optimization.php'; ?>

            <div class="wp-list-table widefat wps-optimizationBox">
                <div class="wp-statistics-container">
                    <div id="resources" class="tab-content current">
                        <?php include(WP_STATISTICS_DIR . 'includes/admin/templates/optimization/resources.php'); ?>
                    </div>
                    <div id="export" class="tab-content">
                        <?php include(WP_STATISTICS_DIR . 'includes/admin/templates/optimization/export.php'); ?>
                    </div>
                    <div id="purging" class="tab-content">
                        <?php include(WP_STATISTICS_DIR . 'includes/admin/templates/optimization/purging.php'); ?>
                    </div>
                    <div id="updates" class="tab-content">
                        <?php include(WP_STATISTICS_DIR . 'includes/admin/templates/optimization/updates.php'); ?>
                    </div>
                    <div id="historical" class="tab-content">
                        <?php include(WP_STATISTICS_DIR . 'includes/admin/templates/optimization/historical.php'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
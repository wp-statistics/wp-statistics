<?php
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Service\Admin\ModalHandler\Modal;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;

$isCompliant = $data['compliance_status']['percentage_ready'] == 100;

Modal::render('privacy-audit-confirmation', [
    'title'               => __('Are you sure you want manually resolve this item?', 'wp-statistics'),
    'primaryButtonText'   => __('Yes, Resolve', 'wp-statistics'),
    'primaryButtonStyle'  => 'danger',
    'secondaryButtonText' => __('Cancel', 'wp-statistics'),
    'secondaryButtonStyle'=> 'cancel',
    'showCloseButton'     => true,
    'description'         => __('By manually resolving this item, please ensure your website’s privacy policy is updated to accurately reflect this setting. This is essential for maintaining compliance and transparency with your users.', 'wp-statistics'),
    'actions'             => [
        'primary'   => 'resolve',
        'secondary' => 'closeModal',
    ],
]);
?>
<div class="wps-privacy-audit wps-privacy-audit--<?php echo $isCompliant ? 'success' : 'warning'; ?>">
    <?php
        $notice = __('<b>Disclaimer</b><span>This audit only checks WP Statistics plugin settings and helps improve privacy. It doesn\'t guarantee compliance with laws like GDPR and doesn\'t review other plugins or third-party tools. For full compliance, please consult a privacy expert. Remember, you\'re responsible for your site\'s privacy.</span>', 'wp-statistics');
        Notice::renderNotice($notice, 'disclaimer_privacy_audit', 'error privacy-notice');

        Admin_Template::get_template(['layout/privacy-audit/privacy-status', 'layout/privacy-audit/privacy-audits', 'layout/privacy-audit/privacy-faqs'], ['isCompliant' => $isCompliant]);
    ?>
</div>

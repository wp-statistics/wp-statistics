<div class="wps-privacy-audit">
    <?php
    // Info box
    use WP_STATISTICS\Admin_Template;

    $args['infoTitle'] = __('Disclaimer', 'wp-statistics');
    $args['infoText']  = __('<b>Important: </b> This audit only checks WP Statistics plugin settings and helps improve privacy. It doesn\'t guarantee compliance with laws like GDPR and doesn\'t review other plugins or third-party tools. For full compliance, please consult a privacy expert. Remember, you\'re responsible for your site\'s privacy.', 'wp-statistics');
    Admin_Template::get_template(['layout/info-box', 'layout/privacy-audit/compliance-status', 'layout/privacy-audit/privacy-audits', 'layout/privacy-audit/privacy-faqs'], $args);
    ?>
</div>

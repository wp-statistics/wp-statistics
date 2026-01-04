<div class="wps-audit-cards wps-privacy-questions">
    <h2 class="wps-audit-cards__title wps-audit-cards__title--dark">
        <?php esc_html_e('FAQS', 'wp-statistics'); ?>
    </h2>
    <div class="wps-audit-cards__container loading">
        <!-- load faq items using js -->
    </div>
</div>
<div class="wps-privacy-audit__links">
    <h3><?php esc_html_e('Useful Privacy Resources and References', 'wp-statistics'); ?></h3>
    <ul>
        <li>
            <?php echo _e('<a href="'. esc_url(WP_STATISTICS_SITE_URL . '/resources/avoiding-pii-data-collection/?utm_source=wp-statistics&utm_medium=link&utm_campaign=privacy').'" target="blank">Configuring for Maximum Privacy: A Guide to Avoiding PII Data Collection</a>', 'wp-statistics'); ?>
        </li>
        <li>
            <?php echo _e('<a href="'. esc_url(WP_STATISTICS_SITE_URL . '/resources/what-we-collect/?utm_source=wp-statistics&utm_medium=link&utm_campaign=privacy').'" target="blank">GDPR, CCPA and cookie law compliant site analytics</a>', 'wp-statistics'); ?>
        </li>
        <li>
            <?php echo _e('<a href="'. esc_url(WP_STATISTICS_SITE_URL . '/resources/counting-unique-visitors-without-cookies/?utm_source=wp-statistics&utm_medium=link&utm_campaign=privacy').'" target="blank">Counting Unique Visitors Without Cookies</a>', 'wp-statistics'); ?>
        </li>
        <li>
            <?php echo _e('<a href="'. esc_url(WP_STATISTICS_SITE_URL . '/resources/compliant-with-wordpress-data-export-and-erasure/?utm_source=wp-statistics&utm_medium=link&utm_campaign=privacy').'" target="blank">Compliant with WordPress Data Export and Erasure</a>', 'wp-statistics'); ?>
        </li>
        <li>
            <?php echo _e('<a href="'. esc_url(WP_STATISTICS_SITE_URL . '/resources/wp-consent-level-integration/?utm_source=wp-statistics&utm_medium=link&utm_campaign=privacy').'" target="blank">WP Consent Level Integration</a>', 'wp-statistics'); ?>
        </li>
    </ul>
</div>
<div class="wps-mb-16"><?php echo esc_html__('Our system has found that User IDs have previously been recorded in your database, which may have occurred while the “Record User Page Visits” feature was active. To ensure the privacy and security of your users, we recommend removing these User IDs from your database.') ?></div>
<div class="wps-audit-card__suggestion wps-mb-16">
    <div class="wps-audit-card__suggestion-head"><?php echo esc_html__('How to Remove User IDs?', 'wp-statistics') ?></div>
    <div class="wps-audit-card__suggestion-text">
        <?php echo __('<ol>
                <li>Go to the <b>Optimization</b> tab in the WP Statistics settings.</li>
                <li>Click on <b>Data Cleanup</b>.</li>
                <li>Select <b>Remove User IDs</b> to start the removal process.</li>
            </ol>
            <p>Initiating this process will delete all previously stored User IDs, further securing user data and aligning your site with best privacy practices.</p>', 'wp-statistics') ?>
    </div>
</div>
<div class="wps-audit-card__suggestion">
    <div class="wps-audit-card__suggestion-head"><?php echo esc_html__('Need More Information?', 'wp-statistics') ?></div>
    <div class="wps-audit-card__suggestion-text">
        <?php echo __('<p>For step-by-step instructions and additional details on the importance of removing User IDs, please consult our guide: <a target="_blank" href="https://wp-statistics.com/resources/removing-user-ids-from-your-database/?utm_source=wp-statistics&utm_medium=link&utm_campaign=privacy">Removing User IDs from Your Database</a>.</p>', 'wp-statistics') ?>
    </div>
</div>
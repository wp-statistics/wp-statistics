<div class="wps-mb-16"><?php echo esc_html__('Our system has detected remnants of full user agent strings in your database, indicating that the “Store Entire User Agent String” feature was enabled at some point in the past. To align with best practices for user privacy, we recommend clearing this data if it is no longer necessary for diagnostic purposes.') ?> </div>
<div class="wps-audit-card__suggestion wps-mb-16">
    <div class="wps-audit-card__suggestion-head"><?php echo esc_html__('How to Clear User Agent String Data?', 'wp-statistics') ?></div>
    <div class="wps-audit-card__suggestion-text">
        <?php echo __('<ol>
                <li>Navigate to the <b>Optimization</b> tab.</li>
                <li>Select <b>Data Cleanup</b>.</li>
                <li>Click on <b>Clear User Agent Strings</b> to initiate the cleanup process.</li>
            </ol>
            <p>This action will remove all previously stored full user agent strings from your database, enhancing privacy and data protection on your website.</p>', 'wp-statistics') ?>
    </div>
</div>
<div class="wps-audit-card__suggestion">
    <div class="wps-audit-card__suggestion-head"><?php echo esc_html__('Need More Information?', 'wp-statistics') ?></div>
    <div class="wps-audit-card__suggestion-text">
        <?php echo __('<p>For detailed instructions and further information on the importance of this cleanup process, please visit our dedicated resource: <a target="_blank" href="https://wp-statistics.com/resources/how-to-clear-user-agent-strings/?utm_source=wp-statistics&utm_medium=link&utm_campaign=privacy">How to Clear User Agent Strings</a>.</p>', 'wp-statistics') ?>
    </div>
</div>
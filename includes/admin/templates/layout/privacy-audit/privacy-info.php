<div class="wps-flex wps-privacy-head">
    <div class="postbox-container wps-half-card">
        <div class="postbox wps-postbox-wrap wps-privacy-questions">
            <div class="postbox-header">
                <h2><?php esc_html_e('Popular Questions', 'wp-statistics'); ?></h2>
                <p><?php esc_html_e('Audit List: Monitor Compliance Status', 'wp-statistics'); ?></p>
            </div>
            <div class="wps-privacy-list__items">
                <div class="wps-privacy-list__item wps-privacy-list__item--success">
                    <div class="wps-privacy-list__title">
                        <span class="wps-privacy-list__icon wps-privacy-list__icon--success"></span>
                        <div>
                            <span><?php esc_html_e('Does WP Statistics require consent?', 'wp-statistics'); ?></span>
                            <span><?php esc_html_e('User Consent Not Required.', 'wp-statistics'); ?></span>
                        </div>
                    </div>
                    <div class="wps-privacy-list__content">
                        <p>
                            <?php esc_html_e('Based on your current configuration, WP Statistics is not recording any personal data. Consequently, under these settings, your use of WP Statistics does not require obtaining user consent. This approach aligns with privacy-focused analytics, minimizing compliance burdens while respecting user privacy.', 'wp-statistics'); ?>
                        </p>
                    </div>
                </div>
                <div class="wps-privacy-list__item wps-privacy-list__item--warning">
                    <div class="wps-privacy-list__title">
                        <span class="wps-privacy-list__icon wps-privacy-list__icon--warning-square"></span>
                        <div>
                            <span><?php esc_html_e('Does WP Statistics require consent?', 'wp-statistics'); ?></span>
                            <span><?php esc_html_e('User Consent Required.', 'wp-statistics'); ?></span>
                        </div>
                    </div>
                    <div class="wps-privacy-list__content">
                        <p>
                            <?php echo _e('Your current settings indicate that WP Statistics is configured to collect personal data. In this case, it is essential to obtain user consent to comply with privacy laws and regulations. For detailed information on which settings may necessitate user consent and how to adjust them, please refer to the <b>Privacy Audit</b> section of this page.', 'wp-statistics'); ?>
                        </p>
                    </div>
                </div>
                <div class="wps-privacy-list__item wps-privacy-list__item--success">
                    <div class="wps-privacy-list__title">
                        <span class="wps-privacy-list__icon wps-privacy-list__icon--success"></span>
                        <div>
                            <span><?php esc_html_e('Does WP Statistics require a cookie banner?', 'wp-statistics'); ?></span>
                            <span><?php  echo __('<b>No</b>, WP Statistics does not require a cookie banner.', 'wp-statistics'); ?></span>
                        </div>
                    </div>
                    <div class="wps-privacy-list__content">
                        <p>
                            <?php esc_html_e('Unlike many analytics solutions that rely on cookies to track users across a website, WP Statistics employs a method of counting unique visitors that does not involve the use of cookies. This approach ensures privacy compliance and minimizes the need for user consent related to cookie usage.', 'wp-statistics'); ?>
                        </p>
                        <p>
                            <?php esc_html_e('Why a Cookie Banner is Not Required', 'wp-statistics'); ?>
                        </p>
                        <p>
                            <?php esc_html_e('WP Statistics distinguishes itself by utilizing a cookieless tracking mechanism. This means the plugin can provide accurate analytics insights without storing any data on visitors’ devices, thereby respecting user privacy and reducing regulatory burdens for website owners.', 'wp-statistics'); ?>
                        </p>
                        <p>
                            <?php echo _e('More Information for a comprehensive understanding of how WP Statistics counts unique visitors without cookies, and the advantages of this approach, please refer to our detailed documentation: <a target="_blank" href="  https://wp-statistics.com/resources/counting-unique-visitors-without-cookies/?utm_source=wp-statistics&utm_medium=link&utm_campaign=privacy" title="Counting Unique Visitors Without Cookies"> Counting Unique Visitors Without Cookies</a>.', 'wp-statistics'); ?>
                        </p>
                    </div>
                </div>
                <div class="wps-privacy-list__item wps-privacy-list__item--success">
                    <div class="wps-privacy-list__title">
                        <span class="wps-privacy-list__icon wps-privacy-list__icon--success"></span>
                        <div>
                            <span><?php esc_html_e(' Does WP Statistics transfer data outside the EU?', 'wp-statistics'); ?></span>
                            <span><?php  echo __('<b>No</b>, WP Statistics is designed to store all analytics data directly within your WordPress database.', 'wp-statistics'); ?></span>
                        </div>
                    </div>
                    <div class="wps-privacy-list__content">
                        <p>
                            <?php esc_html_e('This means that the data resides on the same server as your website, following the same data storage practices. Since WP Statistics does not transfer data outside of your database, the location of your data is determined by your web hosting service. If your hosting servers are located within the EU, your data does not leave the EU. It is essential to be aware of your hosting provider’s data center locations to understand where your website’s data, including that collected by WP Statistics, is physically stored.', 'wp-statistics'); ?>
                        </p>
                    </div>
                </div>
                <div class="wps-privacy-list__item wps-privacy-list__item--success">
                    <div class="wps-privacy-list__title">
                        <span class="wps-privacy-list__icon wps-privacy-list__icon--success"></span>
                        <div>
                            <span><?php esc_html_e('Do I need to mention WP Statistics in my privacy policy?', 'wp-statistics'); ?></span>
                            <span><?php esc_html_e('Mentioning Not Strictly Necessary', 'wp-statistics'); ?></span>
                        </div>
                    </div>
                    <div class="wps-privacy-list__content">
                        <p>
                            <?php esc_html_e('According to your current setup, WP Statistics is not configured to record any personal data. This means that technically, you do not need to mention WP Statistics in your privacy policy. However, to foster an environment of utmost transparency with your users, we still encourage mentioning the use of WP Statistics. This helps inform users about the analytics tools employed by your site, reinforcing trust through transparency.', 'wp-statistics'); ?>
                        </p>
                    </div>
                </div>
                <div class="wps-privacy-list__item wps-privacy-list__item--warning">
                    <div class="wps-privacy-list__title">
                        <span class="wps-privacy-list__icon wps-privacy-list__icon--warning-square"></span>
                        <div>
                            <span><?php esc_html_e('Do I need to mention WP Statistics in my privacy policy?', 'wp-statistics'); ?></span>
                            <span><?php esc_html_e('Mentioning Required', 'wp-statistics'); ?></span>
                        </div>
                    </div>
                    <div class="wps-privacy-list__content">
                        <p>
                            <?php esc_html_e('Your configuration indicates that WP Statistics collects personal data. In this scenario, it is crucial to mention WP Statistics in your privacy policy. This should include information on the type of data collected, its purpose, and how it is processed. Being transparent about the use of WP Statistics and its data handling practices is essential to comply with privacy regulations and to maintain trust with your website visitors.', 'wp-statistics'); ?>
                        </p>
                        <p><?php echo _e('For more information on adjusting your settings to enhance privacy and for specifics on what to include in your  <b>privacy policy</b>, please see the Privacy Audit section of this page.', 'wp-statistics'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="postbox-container wps-half-card">
        <div class="postbox wps-postbox-wrap wps-privacy-resources">
            <div class="postbox-header">
                <h2><?php esc_html_e('Useful Privacy Resources and References', 'wp-statistics'); ?></h2>
                <p><?php esc_html_e('Discover essential resources, laws, and articles to help you navigate the complexities of online privacy and ensure your site\'s compliance', 'wp-statistics'); ?></p>
            </div>
            <div class="postbox-content">
                <ul>
                    <li>
                        <?php echo _e('<a href="https://wp-statistics.com/resources/avoiding-pii-data-collection/?utm_source=wp-statistics&utm_medium=link&utm_campaign=privacy?" target="blank">Configuring for Maximum Privacy: A Guide to Avoiding PII Data Collection</a>', 'wp-statistics'); ?>
                    </li>
                    <li>
                        <?php echo _e('<a href="" target="blank">GDPR, CCPA and cookie law compliant site analytics</a>', 'wp-statistics'); ?>
                    </li>
                    <li>
                        <?php echo _e('<a href="https://wp-statistics.com/resources/counting-unique-visitors-without-cookies/?utm_source=wp-statistics&utm_medium=link&utm_campaign=privacy" target="blank">Counting Unique Visitors Without Cookies</a>', 'wp-statistics'); ?>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
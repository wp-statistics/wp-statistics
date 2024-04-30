<div class="postbox wps-postbox-wrap wps-privacy-list">
    <div class="postbox-header">
        <h2><?php esc_html_e('Privacy Audit', 'wp-statistics'); ?></h2>
        <p><?php esc_html_e('Audit List: Monitor Compliance Status', 'wp-statistics'); ?></p>
    </div>
    <div class="wps-privacy-list__items loading">
        <?php
            // $privacy_items = [
            //     [
            //         'type_class'   => 'success',
            //         'icon_class'   => 'success',
            //         'title_text'   => esc_html__('The “Record User Page Visits” feature is currently disabled on your website.', 'wp-statistics'),
            //         'button_class' => 'success',
            //         'button_text'  => esc_html__('Passed', 'wp-statistics'),
            //         'content'      => __('<p> This status indicates that individual user page visits and WordPress user IDs are not being tracked. Your privacy settings are configured to prioritize user privacy in alignment with applicable laws and regulations.</p><p>Why is this important?</p><p>Keeping this feature disabled ensures that your website minimally impacts user privacy, aligning with best practices for data protection and compliance with privacy laws such as GDPR and CCPA. If your operational or analytical needs change, please review our Guide to <a target="_blank" href="https://wp-statistics.com/resources/avoiding-pii-data-collection/?utm_source=wp-statistics&utm_medium=link&utm_campaign=privacy">Avoiding PII Data Collection</a> to ensure compliance and user transparency before enabling this feature.</p>', 'wp-statistics'),
            //     ],
            //     [
            //         'type_class'   => 'warning',
            //         'icon_class'   => 'warning',
            //         'title_text'   => esc_html__('The “Record User Page Visits” feature is currently enabled on your website.', 'wp-statistics'),
            //         'button_class' => 'undo',
            //         'button_text'  => esc_html__('Undo', 'wp-statistics'),
            //         'content'      => __('<p>This status means that individual user page visits and WordPress user IDs are being actively tracked. While this functionality provides valuable insights into user behavior, it’s important to handle the collected data responsibly.</p><p>Why is this important?</p>
            //                 <p>Enabling this feature necessitates a careful approach to privacy and data protection. To maintain compliance with privacy laws such as GDPR and CCPA, and to uphold user trust, please ensure the following:</p>
            //                <ol>
            //                     <li><b>Transparency:</b> Your website’s privacy policy should clearly describe the data collection practices, including the specific types of data collected and their intended use.</li>
            //                     <li><b>Informed Consent:</b> Adequate measures are in place to inform users about the data collection and to obtain their consent where necessary. This may include consent banners, notifications, or other user interfaces that clearly communicate this information.</li>
            //                     <li><b>Review and Action:</b> Regularly review the necessity of keeping this feature enabled. If the feature is no longer needed, or if you wish to enhance user privacy, consider disabling it. Refer to our guide on <a href="https://chat.openai.com/c/42e80126-57c8-4608-9440-b13d86b8bf5a#" target="_blank">Adjusting Your Privacy Settings</a> for detailed instructions on managing this feature.</li>
            //                  </ol>
            //             ', 'wp-statistics'),
            //     ],
            //     [
            //         'type_class'   => 'success',
            //         'icon_class'   => 'success',
            //         'title_text'   => esc_html__('The “Anonymize IP Addresses” feature is currently enabled on your website.', 'wp-statistics'),
            //         'button_class' => 'success',
            //         'button_text'  => esc_html__('Passed', 'wp-statistics'),
            //         'content'      => __('<p>his setting ensures that the IP addresses of your visitors are anonymized by masking the last segment of their IP addresses before any processing or storage occurs. This significantly reduces the risk of personally identifying your users through their IP addresses.</p><p>How It Works</p>
            //                <ol>
            //                     <li><b>IPv4 Anonymization:</b> An IP address like 192.168.1.123 is transformed into 192.168.1.0.</li>
            //                     <li><b>IPv6 Anonymization:</b> An IPv6 address like 2001:0db8:85a3:0000:0000:8a2e:0370:7334 becomes 2001:0db8:85a3::.</li>
            //                     <li><b>Enhanced Privacy:</b> After anonymization, a hashing process is applied to the IP address, further securing user data and making re-identification through IP addresses highly unlikely.
            //                     Best Practices</li>
            //                     <li><b>Privacy-First Approach:</b> Keeping this feature enabled is strongly recommended as it aligns with best data protection practices and compliance with various privacy laws and regulations.</li>
            //                     <li><b>Transparency:</b> Ensure your privacy policy reflects this practice, enhancing trust with your site visitors.</li>
            //                 </ol>
            //             ', 'wp-statistics'),
            //     ],
            //     [
            //         'type_class'   => 'warning',
            //         'icon_class'   => 'warning-square',
            //         'title_text'   => esc_html__('The “Anonymize IP Addresses” feature is currently disabled on your website.', 'wp-statistics'),
            //         'button_class' => null,
            //         'button_text'  => null,
            //         'content'      => __('<p>This setting means that IP addresses could be stored or processed in their complete form, potentially allowing for the identification of individual users based on their IP addresses.</p>
            //             <p>Implications</p>
            //             <ol>
            //                 <li><b>Privacy Risks:</b> Without anonymization, IP addresses are considered Personally Identifiable Information (PII) and could pose privacy risks to your users.</li>
            //                 <li><b>Legal Compliance:</b> Storing complete IP addresses may affect your compliance with privacy laws such as GDPR, requiring careful consideration and potentially additional safeguards.
            //                 Recommendations.</li>
            //                 <li><b>Enable Anonymization:</b> We recommend enabling the “Anonymize IP Addresses” feature to enhance user privacy and align with privacy laws and best practices.</li>
            //                 <li><b>Review Privacy Practices:</b> If you have specific reasons for keeping this feature disabled, ensure you have adequate measures in place to protect user data and comply with applicable laws. This might include obtaining explicit consent from users for processing their complete IP addresses.</li>
            //             </ol>
            //             ', 'wp-statistics'),
            //     ],
            //     [
            //         'type_class'   => 'success',
            //         'icon_class'   => 'success',
            //         'title_text'   => esc_html__('The “Hash IP Addresses” feature is currently enabled on your website. ', 'wp-statistics'),
            //         'button_class' => 'success',
            //         'button_text'  => esc_html__('Passed', 'wp-statistics'),
            //         'content'      => sprintf(__('<p>This setting applies a secure, irreversible hashing process to IP addresses, transforming them into unique, non-reversible strings. This method of pseudonymization protects user privacy by preventing the possibility of tracing the hash back to the original IP address.</p><p>How It Works</p>
            //             <ol>
            //                 <li><b>Unique Visitor Counting: </b> The system counts unique visitors by hashing a combination of the IP address, User-Agent string, and a daily-changing salt. This ensures each visitor’s identifier is unique and secure for that day.</li>
            //                 <li><b>Privacy Enhancement: </b> Through this process, WP Statistics supports privacy compliance by anonymizing visitor data, thus aligning with stringent privacy regulations.
            //                 Recommendations.</li>
            //                 <li><b>Maintain Enabled Status: </b> Keeping this feature enabled is recommended to uphold the highest standards of user privacy and security. This default setting ensures that all IP addresses are hashed from the start, offering a robust privacy-first approach.</li>
            //                 <li><b>Retroactive Hashing: </b> For users seeking to enhance privacy for previously stored data, WP Statistics offers guidance on converting existing IP addresses to hashes, further strengthening privacy measures.</li>
            //             </ol>'), 'wp-statistics'),
            //     ],
            //     [
            //         'type_class'   => 'warning',
            //         'icon_class'   => 'warning',
            //         'title_text'   => esc_html__('The “Hash IP Addresses” feature is currently disabled on your website. ', 'wp-statistics'),
            //         'button_class' => 'resolve loading',
            //         'button_text'  => esc_html__('Resolved', 'wp-statistics'),
            //         'content'      => __('<p>With this setting deactivated, IP addresses are not subjected to the secure, irreversible hashing process and may be stored in their original form. This could potentially allow for the identification of individual users, impacting user privacy and your site’s compliance with privacy laws.</p>
            //           <p>Implications</p>
            //           <ol>
            //           <li><b>Reduced Privacy:</b> Disabling hashing reduces the level of privacy protection for user data, as IP addresses can be stored in a form that may be traceable to individuals.</li>
            //           <li><b>Compliance Risks:</b> Operating without this layer of data protection may affect your website’s alignment with privacy regulations, necessitating additional safeguards or disclosures.
            //                 Recommendations</li>
            //           <li><b>Consider Re-Enabling:</b> To enhance user privacy and ensure compliance with privacy laws, it is advisable to re-enable the “Hash IP Addresses” feature.</li>
            //           <li><b>Disclosure:</b> If there are specific reasons for keeping hashing disabled, ensure transparent communication with your users by clearly disclosing this in your privacy policy, including the implications for their data privacy.</li>
            //           </ol>
            //         ', 'wp-statistics'),
            //     ],
            //     [
            //         'type_class'   => 'warning',
            //         'icon_class'   => 'warning',
            //         'title_text'   => esc_html__('The “Store Entire User Agent String” feature is currently enabled on your website. ', 'wp-statistics'),
            //         'button_class' => 'resolve',
            //         'button_text'  => esc_html__('Resolved', 'wp-statistics'),
            //         'content'      => __('<p>This setting allows for the collection of complete user agent strings from your visitors, offering detailed insights into their browsing devices and environments. While invaluable for debugging and optimizing user experience, this feature gathers detailed user information, warranting careful use and consideration for privacy.</p>
            //           <p>Privacy Considerations</p>
            //           <ol>
            //               <li><b>Temporary Activation:</b> Intended for short-term diagnostic purposes, it’s recommended to disable this feature once specific issues have been resolved to minimize the collection of extensive user data.</li>
            //               <li><b>Privacy Compliance:</b> The activation of this feature necessitates clear disclosure within your privacy policy about the collection of full user agent strings and their purpose.
            //                 Management Recommendations.</li>
            //               <li><b>Selective Use:</b> Enable this feature only as needed for troubleshooting or enhancing website functionality.</li>
            //               <li><b>Disabling After Use:</b> Remember to deactivate this setting after debugging processes to ensure unnecessary data is not collected.</li>
            //               <li><b>Data Removal:</b> For instructions on deleting previously stored user agent data, refer to our guide here.</li>
            //           </ol>
            //         ', 'wp-statistics'),
            //     ],
            //     [
            //         'type_class'   => 'success',
            //         'icon_class'   => 'success',
            //         'title_text'   => esc_html__('The “Store Entire User Agent String” feature is currently disabled on your website.', 'wp-statistics'),
            //         'button_class' => 'success',
            //         'button_text'  => esc_html__('Passed', 'wp-statistics'),
            //         'content'      => sprintf(__('<p>This default setting ensures that extensive details about your visitors’ browsing environments are not recorded, aligning with best practices for user privacy and data minimization.</p><p>Why This Matters</p>
            //             <ol>
            //                 <li><b>Privacy Preservation: </b> Disabling this feature helps prevent the collection of data that could potentially identify individuals, fostering a safer and more private browsing experience.</li>
            //                 <li><b>Compliance with Privacy Laws: </b> Keeping this setting disabled by default supports compliance with stringent privacy regulations by avoiding the unnecessary collection of detailed user information.
            //                     Recommendations for Use.</li>
            //                 <li><b>Considerations for Enabling: </b> Should you need to enable this feature for debugging or optimization purposes, ensure it’s used judiciously and for a limited time only.</li>
            //                 <li><b>Transparency with Users: </b> If activated, update your privacy policy to reflect the temporary collection of full user agent strings, including the purpose and scope of data collection.</li>
            //             </ol>'), 'wp-statistics'),
            //     ],
            //     [
            //         'type_class'   => 'warning',
            //         'icon_class'   => 'warning',
            //         'title_text'   => esc_html__('Previous Use of “Store Entire User Agent String” Detected ', 'wp-statistics'),
            //         'button_class' => 'warning loading',
            //         'button_text'  => esc_html__('Action Required', 'wp-statistics'),
            //         'content'      => __('<p>Our system has detected remnants of full user agent strings in your database, indicating that the “Store Entire User Agent String” feature was enabled at some point in the past. To align with best practices for user privacy, we recommend clearing this data if it is no longer necessary for diagnostic purposes.</p>
            //           <p>How to Clear User Agent String Data</p>
            //           <ol>
            //               <li>Navigate to the <b>Optimization</b> tab.</li>
            //               <li>Select <b>Data Cleanup</b>.</li>
            //               <li>Click on <b>Clear User Agent Strings</b> to initiate the cleanup process.</li>
            //           </ol>
            //           <p>This action will remove all previously stored full user agent strings from your database, enhancing privacy and data protection on your website.</p>
            //           <p>Need More Information?</p>
            //           <p>For detailed instructions and further information on the importance of this cleanup process, please visit our dedicated resource: <a target="_blank" href="https://wp-statistics.com/resources/how-to-clear-user-agent-strings/?utm_source=wp-statistics&utm_medium=link&utm_campaign=privacy">How to Clear User Agent Strings</a>.</p>
            //         ', 'wp-statistics'),
            //     ],
            //     [
            //         'type_class'   => 'warning',
            //         'icon_class'   => 'warning',
            //         'title_text'   => esc_html__('Unhashed IP Addresses Detected', 'wp-statistics'),
            //         'button_class' => 'warning',
            //         'button_text'  => esc_html__('Action Required', 'wp-statistics'),
            //         'content'      => __('<p>Our system has identified that raw IP addresses are stored in your database, likely due to the “Hash IP Addresses” feature being disabled in the past. To enhance data protection and align with privacy best practices, converting these IP addresses to a hashed format is strongly recommended.</p>
            //           <p>How to Convert IP Addresses to Hash</p>
            //           <ol>
            //               <li>Go to the <b>Optimization</b> section.</li>
            //               <li>Select <b>Plugin Maintenance</b>.</li>
            //               <li>Choose <b>Convert IP Addresses to Hash</b> to start the conversion process.</li>
            //           </ol>
            //           <p>This step will transform all existing raw IP addresses in your database into hashed formats, significantly improving user privacy and your website’s compliance with data protection regulations.</p>
            //           <p>Need More Information?</p>
            //           <p>For a comprehensive guide on this process and to understand the benefits of IP address hashing, please refer to our detailed documentation: <a target="_blank" href="https://wp-statistics.com/resources/converting-ip-addresses-to-hash/?utm_source=wp-statistics&utm_medium=link&utm_campaign=privacy">Converting IP Addresses to Hash</a>.</p>
            //         ', 'wp-statistics'),
            //     ],
            //     [
            //         'type_class'   => 'warning',
            //         'icon_class'   => 'warning',
            //         'title_text'   => esc_html__('Previous Recording of User IDs Detected', 'wp-statistics'),
            //         'button_class' => 'warning',
            //         'button_text'  => esc_html__('Action Required', 'wp-statistics'),
            //         'content'      => __('<p>Our system has found that User IDs have previously been recorded in your database, which may have occurred while the “Record User Page Visits” feature was active. To ensure the privacy and security of your users, we recommend removing these User IDs from your database.</p>
            //           <p>How to Remove User IDs</p>
            //           <ol>
            //               <li>Go to the <b>Optimization</b> tab in the WP Statistics settings.</li>
            //               <li>Click on <b>Data Cleanup</b>.</li>
            //               <li>Select <b>Remove User IDs</b> to start the removal process.</li>
            //           </ol>
            //           <p>Initiating this process will delete all previously stored User IDs, further securing user data and aligning your site with best privacy practices.</p>
            //           <p>Need More Information?</p>
            //           <p>For step-by-step instructions and additional details on the importance of removing User IDs, please consult our guide: <a target="_blank" href="https://wp-statistics.com/resources/removing-user-ids-from-your-database/?utm_source=wp-statistics&utm_medium=link&utm_campaign=privacy">Removing User IDs from Your Database.</a>.</p>
            //         ', 'wp-statistics'),
            //     ]
            // ];

            // foreach ($privacy_items as $args) {
            //     Admin_Template::get_template(['layout/privacy-audit/privacy-audit-section'], $args);
            // }
        ?>
    </div>
</div>



=== WP Statistics ===
Contributors: mostafa.s1990, kashani, veronalabs, GregRoss
Donate link: https://wp-statistics.com/donate/
Tags: analytics, stats, statistics, visit, visitors, hits, chart, geoip, location
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 14.5
Requires PHP: 5.6
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

This plugin gives you the complete information on your website's visitors.

== Description ==
= WP Statistics: THE #1 WORDPRESS STATISTICS PLUGIN =
Do you need a simple tool to know your website statistics? Do you need to represent these statistics? Are you caring about your users’ privacy while analyzing who are interested in your business or website? With WP Statistics you can know your website statistics without any need to send your users’ data anywhere. You can know how many people visit your personal or business website, where they’re coming from, what browsers and search engines they use, and which of your contents, categories, tags and users get more visits.

[Checkout Demo](https://wp-statistics.com/demo)

= Data Privacy =
WP Statistics stores all data, including IP addresses, safely on your server. WP Statistics respects user privacy and is GDPR, CCPA compliant, as detailed on our [GDPR, CCPA and cookie law compliant](https://wp-statistics.com/resources/what-we-collect/) page. It anonymizes IPs, uses IP hashing with random daily Salt Mechanism for extra security, and follows Do Not Track (DNT) requests from browsers. This keeps user information private while giving you insights into your website traffic.

= ACT BETTER  BY KNOWING WHAT YOUR USERS ARE LOOKING FOR =
* Anonymize IP to Better Privacy
* Enhance IP Hashing with Random Daily Salt Mechanism
* Respect for User Privacy with Do Not Track (DNT) Compliance
* Visitor Data Records including IP, Referring Site, Browser, Search Engine, OS, Country and City
* Stunning Graphs and Visual Statistics
* Visitor’s Country & City Recognition
* The number of Visitors coming from each Search Engine
* The number of Referrals from each Referring Site
* Top 10 common browsers; Top 10 countries with most visitors; Top 10 most-visited pages; Top 10 referring sites
* Hits Time-Based Filtering
* Statistics on Contents based on Categories, Tags, and Writers
* Widget Support for showing Statistics
* Data Export in TSV, XML, and CSV formats
* Statistical Reporting Emails
* Statistical of pages with query strings and UTM parameters
* [Premium] [Data Plus](https://wp-statistics.com/product/wp-statistics-data-plus?utm_source=wp_statistics&utm_medium=display&utm_campaign=wordpress)
* [Premium] [More Advanced reporting](http://bit.ly/2MjZE3l)
* And much more information represented in graphs & charts along with data filtering

= NOTE =
Some advanced features are Premium, which means you need to buy extra add-ons to unlock those features. You can get [Premium add-ons](http://bit.ly/2x6tGly) here!

= REPORT BUGS =
If you encounter any bug, please create an issue on [GitHub](https://github.com/wp-statistics/wp-statistics/issues/new) where we can act upon them more efficiently. Since [Github](https://github.com/wp-statistics/wp-statistics) is not a support forum, just bugs are welcomed, and any other request will be closed.

== Installation ==
1. Upload `wp-statistics` to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Make sure the Date and Time are set correctly in WordPress.
4. Go to the plugin settings page and configure as required (note this will also include downloading the GeoIP database for the first time).

== Frequently Asked Questions ==
= GDPR Compliant? =
The greatest advantage of WP Statistics is that all the data is saved locally in WordPress.
This helps a lot while implementing the new GDPR restrictions; because it’s not necessary to create a data processing contract with an external company! [Read more about WP Statistics compliance with GDPR](https://wp-statistics.com/resources/what-we-collect/).

= Does WP Statistics support Multisite? =
WP Statistics doesn't officially support the multisite feature; however, it does have limited functionally associated with it and should function without any issue. However, no support is provided at this time.
Version 8.8 is the first release that can be installed, upgraded and removed correctly on multi-site. It also has some basic support for the network admin menu. This should not be taken as an indication that WP Statistics fully supports the multisite, but only should be considered as a very first step.

= Does WP Statistics work with caching plugins? =
Yes, the cache support added in v12.5.1

If you're using a plugin cache:
* Don't forget to clear your enabled plugin cache.
* You should enable the plugin cache option in the Settings page.
* Making sure the below endpoint registered in your WordPress.
http://yourwebsite.com/wp-json/wpstatistics/v1

To register, go to the Permalink page and update the permalink with press Save Changes.

= What’s the difference between Visits and Visitors? =
Visits is the number of page hits your site has received.
Visitors is the number of unique users which have visited your site.
Visits should always be greater than Visitors (though, there are a few cases when this won’t be true due to having low visits).
The average number of pages a visitor views on your site is Visits/Visitors.

= Are All visitors’ locations set to ‘unknown’? =
Make sure you’ve downloaded the GeoIP database and the GeoIP code is enabled.
Also, if you are running an internal test site with non-routable IP addresses (like 192.168.x.x or 172.28.x.x or 10.x.x.x), these addresses will be always shown as ‘unknown’. You can define a location IP for these IP addresses in the “Country code for private IP addresses” setting.

= I’m using another statistics plugin/service and get different numbers from them, why? =
Probably, each plugin/service is going to give you different statistics on visits and visitors; there are several reasons for this:

* Web crawler detections
* Detection methods (Javascript vs. Server Side PHP)
* Centralized exclusions

Services that use centralized databases for spam and robot detections , such as Google Analytics, have better detection than WP Statistics.

= Not all referrals are showing up in the search words list, why? =
Search Engine Referrals and Words are highly dependent on the search engines providing the information to us. Unfortunately, we can’t do anything about it; we report everything we receive.

= Does WP Statistics support the UTM parameters? =
Yes, It does! WP Statistics logs all query strings in the URL such as UTM parameters.

= PHP v8.0 Support? =
WP Statistics is PHP 8.0 compliant.

= IPv6 Support? =
WP Statistics supports IPv6 as of version 11.0; however, PHP must be compiled with IPv6 support enabled; otherwise you may see warnings when a visitor from an IPv6 address hits your site.

You can check if IPv6 support is enabled in PHP by visiting the Optimization > Resources/Information->Version Info > PHP IPv6 Enabled section.

If IPv6 is not enabled, you may see a warning like:

	Warning: inet_pton() [function.inet-pton]: Unrecognized address 2003:0006:1507:5d71:6114:d8bd:80c2:1090

= What 3rd party services does the plugin use? =
IP location services are provided by data created by [MaxMind](https://www.maxmind.com/), to detect the Visitor's location (Country & City) the plugin downloads the GeoLite2 Database created by [MaxMind](https://www.maxmind.com/) on your server locally and use it.

Referrer spam blacklist is provided by Matomo, available from https://github.com/matomo-org/referrer-spam-blacklist

== Screenshots ==
1. Overview
2. Browsers Statistics
3. Top Countries
4. Hit Statistics
5. Top pages
6. Category Statistics
7. Search Engine Referral Statistics
8. Last Search Words
9. Dashboard widgets
10. Theme widget
11. Page Statistics Overview

== Upgrade Notice ==
= 14.0 =
**IMPORTANT NOTE**
Welcome to WP Statistics v14.0, our biggest update!
Thank you for being part of our community. We’ve been working hard for one year to develop this version and make WP Statistics better for you. after updating, please update all Add-Ons to tha latest version as well.

If you encounter any bug, please create an issue on [GitHub](https://github.com/wp-statistics/wp-statistics/issues/new) where we can act upon them more efficiently. Since [GitHub](https://github.com/wp-statistics/wp-statistics) is not a support forum, just bugs are welcomed, and any other request will be closed.

== Changelog ==
= 14.5 - 24.02.2024 =
* Feature: Added 'Allowed Query Parameters' option for specifying permissible URL query parameters. [Read more](https://wp-statistics.com/resources/managing-url-query-parameters/?utm_source=plugin&utm_medium=changelog&utm_campaign=settings).
* Fixes: Resolved issue with displaying the home page in Top Pages.
* Improvement: Updated name and description settings for better clarity and coherence.
* Improvement: Enabled JavaScript tracking by default instead of relying on HTTP requests.
* Deprecate: Removed 'Strip URI Parameter' option from the plugin.

= 14.4.4 - 15.02.2024 =
* Fixes: Resolved issue with latest visitor when option 'Record User Page Visits' is disabled.

= 14.4.3 - 14.02.2024 =
* Improvement: Introduced a close button for easily dismissing the admin notice regarding database cleanup.

= 14.4 - 13.02.2024 =
We’re delighted to roll out WP Statistics 14.4, bringing advanced privacy features and performance enhancements to your WordPress experience. This update includes a robust Random Daily Salt Mechanism for IP hashing, options for data anonymization, and numerous optimizations for better site efficiency. For a deep dive into all the new features and improvements, check out our detailed [blog post](https://wp-statistics.com/2024/02/13/wp-statistics-14-4-elevating-privacy-and-performance/)

* Feature: Added option to removal `user_id` and `UAString` from visitor table.
* Feature: Enhance IP Hashing with [Random Daily Salt Mechanism](https://wp-statistics.com/2024/02/13/enhancing-privacy-with-our-updated-ip-hashing-mechanism/).
* Feature: Included browser version information in 'Latest Visitor' data.
* Feature: Added the link for IPs that are hashed.
* Feature: Introduced a new notification for database cleanup and performance optimization.
* Improvement: Set 'Purge Old Data Daily' to 180 days for fresh installation.
* Improvement: Extended 'Keep User Online' duration to 5 minutes for enhanced performance.
* Improvement: Upgraded `visitor_relationships` table to support efficient handling of record updates or insertions
* Improvement: Refined sorting algorithm to prioritize visitors by their most recent visits.
* Improvement: Enhanced the relationship deletion process in purge schedule.
* Improvement: Modified purge conditions to encompass a 30-day threshold.
* Improvement: Removed redundant `page_id` index from historical data.
* Improvement: Implemented logic to avoid using the same current URL for visitors with no referrer.
* Improvement: Ensured compatibility with PHP v8.2.
* Improvement: Enhanced error responses for GeoIP failures.
* Improvement: Error handling added for WP Statistics upload directory creation
* Improvement: Improved GeoIP error message notice
* Fixes: Corrected issue where `user_id` was not updating for existing records.
* Fixes: The IP detector in settings page and set to Ipify.org
* Fixes: Prevent duplicate color generation in charts
* Fixes: Resolved issue with closing donation ajax requests.
* Development: Added new filters `wp_statistics_notice_db_row_threshold`, `wp_statistics_schedule_db_maint_days`, and `wp_statistics_ajax_list` for advanced customization.

= v14.3.5 - 17.01.2024 =
* Improvement: Set Requires at least to v5.0
* Improvement: Remove storing the filter data in browser local storage
* Improvement: Change date format to international date system
* Improvement: Refactor IP Anonymization Logic to Use `wp_privacy_anonymize_ip()` Function to make more compatible with GDPR
* Development: Added filters `wp_statistics_pages_countries_args`, `wp_statistics_data_export_base_query` and `wp_statistics_data_export_query`
* Fixes: Compatibility WhichBrowser with PHP v8.3

= v14.3.4 - 30.12.2023 =
* Fixes: Improved layout of the settings page tabs in tablet-view.
* Improvement: Implemented asynchronous requests for performance enhancement.

= v14.3.3 - 28.12.2023 =
* Fixes: Display issue with screen option on overview page now corrected.
* Fixes: Warning issues in author features resolved.
* Fixes: Type Error in User Online Subtraction Operation addressed.
* Fixes: Loading the filter modal in admin area.
* Development: Added `wp_statistics_author_items` filter for extended functionality.
* Improvement: Admin styles enhanced for better user interface.
* Improvement: Tooltips added to settings page sections for improved clarity.
* Improvement: Hit request now initiates after page loads for optimized performance.

= v14.3.2 - 22.12.2023 =
* Feature: New pagination on countries page for easier navigation.
* Feature: Added informative tooltips to overview components for better understanding.
* Fixes: Resolved issue where the table was being altered with every update.
* Fixes: Corrected display of decimal numbers on the y-axis in charts.
* Improvement: Updated settings page with new styles and a more user-friendly sidebar.
* Improvement: Enhanced security by properly handling variables in the countries page.
* Improvement: Updated admin area with the latest versions of plugins for better performance.
* Improvement: Ensured compatibility with PHP version 8.2.
* Development: Introduced `wp_statistics_meta_box_{metabox}_args` filter for developers.
* Development: Added `wp_statistics_after_user_column` action for extended functionality.

= v14.2 - 09.10.2023 =
* Feature: Save default date range filter for overview components
* Feature: Add filter `wp_statistics_search_engine_list` to modify the search engines.
* Feature: Add User column to the Online Users component
* Feature: Add last month option to date range picker
* Feature: Add total post hits in single taxonomy
* Improvement: Improve top posts list template in single taxonomy
* Improvement: Update GeoIP functionality to support conditional loading
* Feature: Add abbreviated format to `wpstatistics` shortcode like `[wpstatistics stat=visits time=total format=abbreviated]`
* Fixed: The page URL issue in UTF-8 characters
* Fixes: The visits duplicate entry issue
* Fixes: Fix words field database error
* Improvement: Add XML-RPC, Cross-Site, and Pre Flight request exclusions

= v14.1.6.2 - 18.09.2023 =
* Enhancement: Utilized admin-ajax.php for improved Ad-Blocker compatibility when displaying admin meta boxes with statistics.

= v14.1.6.1 - 11.09.2023 =
* Bugfix: Resolved font loading issues in widgets
* Enhancement: Improved error messages when the plugin endpoints are blocked by Ad-Blockers

= v14.1.6 - 30.08.2023 =
* Bugfix: The missing record logged users in visitor.
* Update: ChartJs to v4.4.0

= v14.1.5 - 15.08.2023 =
* Update: Compatibility with WordPress v6.3
* Bugfix: Resolved compatibility issue between custom post types and WP Statistics MiniChart plugin.
* Bugfix: Included sub-page ID parameter within the pages-chart component for enhanced functionality.
* Development: Introduced a new filter, `wp_statistics_report_email_receivers`, to facilitate customization of report email recipients.
* Development: Implemented an exclusion to the hit response for improved debugging capabilities.

= v14.1.4 - 26.07.2023 =
* Bugfix: Fixed an issue with deprecated WhichBrowser Useragent in PHP v8.2.
* Bugfix: Resolved a bug preventing storage of long URIs in the Pages Table.
* Bugfix: Addressed the home page counting stats issue.
* Development: Added a new filter `wp_statistics_mail_attachments` for enhanced customization.
* Improvement: Refined Plugin Header for better clarity and consistency.

= v14.1.3.1 - 08.07.2023 =
* Improvement: Backward compatibility

= v14.1.3 - 04.07.2023 =
* Feature: Add possibility to load geoip from other sources
* Feature: Integrated a Feedback button powered by [FeedbackBird!](https://feedbackbird.io/) in the admin area to gather user feedback.
* Bugfix: Showing private default post types
* Bugfix: Fix js error on summary once the visits/visitors are not enabled
* Bugfix: Fix calendar issue on top visitors page
* Update: ChartJs to v4.3.0 and add source map
* Improvement: Mirror and backward compatibility

= v14.1.2 - 23.05.2023 =
* Improvement: Fix showing actual page name in post types hit meta boxes
* Improvement: Fix a bug when date filter doesn't affect referring widget data
* Improvement: Fix showing actual page name in post types hit meta boxes header
* Improvement: Add filter `wp_statistics_geo_ip_download_url`

= v14.1.1 - 20.05.2023 =
* Feature: Add `/wp-json/wp-statistics/v2/online` endpoint to maintain real-time user presence on the page.
* Improvement: Enhance `tracker.js` and implement minor optimizations.
* Improvement: Upgrade email template and enhance email functionalities.
* Improvement: Ensure compatibility with PHP v5.6, although we highly recommend upgrading your PHP version for better performance and security.

= v14.1 - 02.05.2023 =
* Improvement: Compatibility with WordPress v6.2
* Improvement: Update ChartJs to the latest version, v4.2.1
* Improvement: Avoid to return cache status before filter statement
* Improvement: Implement post types section
* Improvement: Categorize page hits by query strings in single view statistics for improved tracking
* Bugfix: Fix a bug related to displaying rest API error messages in the meta box AJAX.

= v14.0.2 - 09.03.2023 =
* Bugfix: Compatibility with PHP v7.0
* Bugfix: Fix some Javascript errors that caused to not showing-up the charts.
* Bugfix: Fix search chart height issue
* Update: Updating all screenshots
* Update: Add total visitors and total visits on Summary
* Improvement: Set top pages to 25 per page
* Improvement: Add all data points on the x-axis of charts
* Improvement: Support IPv6 in Settings -> IP configuration by SeeIP.org

= v14.0 - 26.02.2023

**New Feature**

* **Time-Frame Filter:** All widgets now support custom date ranges, giving you greater flexibility in your data analysis.
* **Statistics Hit Link:** A new feature that adds a link to the detailed statistics page in all widgets, making it easier to see the complete data behind your website's traffic.

**Improvements**

* **Widget Admin UI & UX:** We've completely redesigned the widget interface to improve the overall user experience, making it more intuitive and easy to use.
* **Category and Tags Statistical Merge:** We've merged the Category and Tags Statistical into Taxonomy to make it more user-friendly and easier to understand.
* **Assets and Icons Update:** All assets and icons have been updated to enhance the overall look and feel of the plugin.
* **Update License Functionality:** We've made it easier to manage your license under the Add-Ons, so you can keep your plugin up-to-date and running smoothly.
* **Admin Styles & Settings Page:** We've made some updates to the admin styles and settings page to improve the overall usability and functionality of the plugin.
* **Many other improvements:** We've made numerous other improvements to enhance the overall performance and functionality of WP Statistics.

**Add-Ons**

* **Data Plus Add-On:** We recently launched a new Add-On called [Data Plus](https://wp-statistics.com/product/data-plus/) that unlocks advanced analytics features for WP Statistics. [click here](https://wp-statistics.com/2023/01/01/unlock-advanced-analytics-with-data-plus-for-wp-statistics) to get the limited-time offer!

For more information about this update, please [visit our blog post](https://wp-statistics.com/2023/02/26/wp-statistics-gets-a-major-update-version-14-0-breakdown/).

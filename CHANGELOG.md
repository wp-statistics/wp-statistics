= 14.7 - 20.05.2024 =
* Addition: Introduced Privacy Audit Tool to ensure compliance with privacy laws. [More info click here](https://wp-statistics.com/2024/05/12/introducing-privacy-audit-in-wp-statistics-14-7/)
* Addition: Added two new database columns, region and continent, to the visitor table to enhance geographical data.
* Fixes: Resolved issues with the Date Picker filter and Visitor Map.
* Fixes: Fixed bug displaying archive page information on the online and visitors pages.
* Improvement: Improved tooltip display for browser pie charts.
* Improvement: Enhanced email report appearance for better readability and aesthetics.
* Improvement: Updated filters and styles for a better user experience.
* Improvement: Enhanced event scheduling and email handling for improved performance.
* Improvement: Refined language strings and an admin interface for easier use.
* Improvement: Merged REST API and Advanced Widgets settings into the main plugin settings for better management.
* Improvement: Showing the Hash IP properly in HitsMap modal.
* Improvement: Various minor enhancements to boost plugin stability and performance.

= 14.6.4 - 03.05.2024 =
* Fixes: Improved data comparison logic.
* Fixes: Fixed some fields visibility on settings page.
* Fixes: Fixed filter loading on Visitors page.
* Fixes: Fixed and improved the Convert IP Addresses to Hash in Optimization.
* Fixes: Fixed loading Date Picker in Visitors filter.
* Improvement: Updated plugin header and screenshots.
* Improvement: Add-ons settings page now located under Settings for simplicity.
* Improvement: Minor enhancements made.

= 14.6.3 - 18.04.2024 =
* Fixes: Resolved SQL query issues while purging the table from optimization & getting the browsers count.
* Fixes: Addressed the builder scripts problem to minify the `tracker.js`.
* Fixes: Corrected deprecated jQuery event and resolved errors on the overview page.
* Improvement: Changed the autoload setting for the dailySalt option to false to compatible with Cache plugins.
* Improvement: Encoded search query parameter for more consistency in hit request.
* Improvement: Enhanced visitor identification by flagging users as robots when browser and platform data are absent, improving accuracy of statistics.
* Improvement: Enhanced performance by optimizing VisitorProfile handling in class `Pages`

= 14.6.2 - 16.04.2024 =
* Addition: Integrated Chart Zoom Library
* Fixes: Removed self-referred URLs from statistics
* Fixes: Corrected search chart step size issue
* Fixes: Resolved add-on save issue with backward compatibility
* Update: Upgraded ChartJS to v4.4.2
* Improvement: Addressed cron event issue in admin reports
* Improvement: Refined spam referrer exclusion logic and updated spam list URL
* Improvement: Enhanced performance by optimizing VisitorProfile handling in exclusions
* Improvement: General performance enhancements and code clean-up

= 14.6.1 - 13.04.2024 =
* Feature: Introduced a sequential IP detection method in Settings → Basic Tracking
* Fixes: Updated the Compatibility Visits meta-box and Widget to support PHP version 7.0.
* Fixes: Refined and optimized SQL query processes for accurate page count calculations.

= 14.6.0 - 11.04.2024 =
* Updated: A new admin header is needed for easier use and quick access to important links.
* Performance: Moved a `tracker.js` script to footer load last for faster page display.
* Performance: Made scripts smaller to speed up loading.
* Performance: Added fast, in-memory data storage for user profiles.
* Performance: Improved 'Visits' functionality for faster performance.
* Performance: Made general speed improvements.
* Performance: Made city data storage and retrieval faster.
* Fixes: Fixed "Unknown" issue in showing the cities.
* Fixes: Fixed a bug in the Most Visited Pages display.
* Fixes: Fixed the bug when resetting the user online.
* Fixes: Fixed issues with charts not displaying correctly in some browsers.
* Fixes: Fixed a tracking bug on the homepage.
* Fixes: A GeoLite2 download URL to get the latest version of databases.
* Fixes: Made sure dashboard widgets stay visible after updates.
* Improvement: Moved some settings to a more logical place in the menu.
* Improvement: Updated and simplified various parts of the plugin.
* Improvement: Removed outdated "Search Words" feature.
* Improvement: Made setting default options easier in multisite setups.

= 14.5.2 - 12.03.2024 =
* Feature: Added section 'Event Tracking' in the settings page. [More info](https://wp-statistics.com/2024/03/11/big-news-for-data-plus-introducing-link-and-download-tracking)
* Fixes: The last year stats issue.
* Improvement: Tooltip and improvement the admin styles.
* Improvement: Minor Improvements.

= 14.5.1 - 08.03.2024 =
* Development: Action `wp_statistics_record_visitor` added.
* Improvement: Escaped `str_url` in output of page metabox.
* Improvement: Changed `check_online` input type to number & add failsafe for non-numeric values.
* Improvement: Minor Improvements.

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

= v13.2.16 - 03.02.2023 =
* Bugfix: The exclusion cache issue
* Improvement: Populate post type title for archive pages

= v13.2.15 - 13.01.2023 =
* Bugfix: The exclusion issue when user is logged-in
* Bugfix: The issue the API request to hit endpoint when permalink is default

= v13.2.14 - 10.01.2023 =
* Improvement: Compatibility with WordPress < 5.0

= v13.2.13 - 09.01.2023 =
* Bugfix: Fix recent, Top Visitor and Visitor page timeout & querying issue

= v13.2.12 - 08.01.2023 =
* Bugfix: Get top 10 visitor issue has been fixed
* Bugfix: Changing the current language in admin has been fixed

= v13.2.11 - 01.01.2023 =
* Feature: A privacy setting has been added that allows customers to enable Do Not Track mode.
* Improvement: Hardened plugin security and improvement

= v13.2.10 - 24.12.2022 =
* Bugfix: Logs the pages with query strings and UTM parameters

= v13.2.9 - 17.12.2022 =
* Bugfix: The include issue in CLI mode
* Improvement: Error handler for referred that doesn't have any URL
* Improvement: Hardened plugin security and improvement
* Enhancement: Minor Improvements

= v13.2.8 - 03.12.2022 =
* Feature: Respect and compatibility with [Do Not Track](https://en.wikipedia.org/wiki/Do_Not_Track) browsers setting.
* Feature: Add filter `wp_statistics_top_pages_arguments` to change the arguments of top pages.
* Bugfix: Fix the Add-On notice constraint issue.
* Bugfix: Sanitize the input of the URLs in the pages list
* Improvement: Dynamic sending referred in the frontend by JavaScript while Cache is enabled.
* Improvement: Remove `time` and `_nonce` parameters from the URL of the frontend while Cache is enabled.
* Improvement: Compatibility with PHP v8.1
* Improvement: Support method `Helper::get_pages_by_visitor_id()` to get pages by visitor id.

= v13.2.7 - 23.10.2022 =
* Bugfix: The error message while purging all databases
* Update: The ChartJs library updated to v3.9.1
* Improvement: Compatibility with PHP v8.0
* Improvement: Add index for user online & visitor tables
* Improvement: Make anonymous the browser version
* Improvement: Enable Hash & Anonymous IP by default and make anonymize it before hashing

= v13.2.6 - 07.09.2022 =
* Improvement: Compatibility with Apache `security_mode`
* Improvement: Remove coefficient per visitor field from general/visitors settings
* Improvement: Hardened plugin security and improvement
* Improvement: Avoid printing visitor data to the page while caching compatibility

= v13.2.5 - 27.07.2022 =
* Feature: Support plugin in the Privacy Policy content
* Feature: Support plugin in Data Privacy Exporter and Data Eraser
* Bugfix: The Top 5 Trending Pages Error has been fixed
* Bugfix: The URL parameter issue in platform and browsers pages has been fixed
* Bugfix: Total referrers issue has been fixed
* Improvement: Crawler-Detect library has been updated to v1.2.111
* Improvement: Compatibility with PHP v8.0, minor bugfix, and improvement
* Improvement: Prevent showing Unknown entities

= v13.2.4.1 - 25.06.2022 =
* Bugfix: An issue to modify the visitors' table to add type and device model has been fixed

= v13.2.4 - 11.06.2022 =
* Feature: The new device type & device model meta boxes has been added in Overview!
* Bugfix: Enhancements and CSRF protection added to the settings and optimization pages
* Improvement: REST API status checking
* Improvement: The WP-CLI commands is enabled by default and remove Its tab from Settings page
* Improvement: Moved out "Top Referring" & "Online Users" from Overview side to right column

= v13.2.3 - 12.05.2022 =
* Bugfix: Rendering the email reporting with HTML tags has been fixed
* Bugfix: The browser version issue has been fixed
* Update: WhichBrowser library has been updated to v2.1.7
* Update: Requires WordPress version is set to at least v4.4

= v13.2.2.1 - 08.05.2022 =
* Bugfix: Compatibility & fixed the UTF-8 permalinks issue
* Bugfix: Showing the right hits of Total Page Views in the widget

= v13.2.2 - 08.05.2022 =
* Improvement: Downloading 3rd party services, the GeoIP & Referrer Spammer databases now is on [jsDelivr](https://www.jsdelivr.com/)
* Bugfix: Storing & sending HTML tags allowed for notification message body
* Bugfix: Hardened plugin security and improvement

= v13.2.1 - 25.04.2022 =
* Bugfix: Compatibility with PHP version lower than v7.4

= v13.2.0 - 25.04.2022 =
* Enhancement: The admin bar statistical is smarter and shows the Hits based on the current page, category, tag, author, etc.
* Enhancement: The number of page hits in the render hit column has been improvement
* Enhancement: The ChartJs is more user-friendly
* Enhancement: The render column has been updated
* Enhancement: The Hits in publish Metabox has been improvement
* Update: The Hits column added in all visitor's table.
* Update: Styles and assets
* Update: Increase showing statistical items per page up to 25
* Update: GeoIP City & Countries Databases has been updated to the latest version
* Bugfix: Hardened plugin security and improvement
* Bugfix: Showing the widget form bug has been fixed

= v13.1.7 - 18.03.2022 =
* Update: Chart.js library updated to v3.7.1
* Update: New filter `wp_statistics_cache_status` has been added
* Enhancement: Backward compatibility of the widget data to prevent a notice error
* Enhancement: Minor and small issues

= v13.1.6 - 16.02.2022 =
* Bugfix: Hardened plugin security. (Special thanks to Muhammad Zeeshan (Xib3rR4dAr) & WPScan for reporting the issues)

= v13.1.5 - 02.02.2022 =
* Enhancement: Tested up to v5.9
* Enhancement: Disable showing the notices with hidden class in the admin settings page
* Bugfix: A security issue to accepting the correct `exclusion_reason` through request
* Bugfix: The 403 Forbidden Error issue in REST request

= v13.1.4 - 14.01.2022 =
* Enhancement: Datepicker direction issue has been fixed
* Enhancement: UTF-8 referrers URLs has been supported
* Bugfix: The Apache 403 error has been fixed when passing the actual URL as the GET parameter
* Bugfix: Date filter in Top Referring Sites has been fixed

= v13.1.3 - 23.12.2021 =
* Bugfix: The issue for showing the pagination in date range template has been fixed
* Enhancement: Skip undefined `HTTP_HOST` notice error
* Enhancement: Minor Improvements

= v13.1.2 - 09.11.2021 =
* Enhancement: Avoiding altering some tables after every upgrade
* Enhancement: Pages widget table clutter issue fixed when URLs are long
* Bugfix: The variable types bug in Matomo Referrer schedule weekly update fixed
* Bugfix: the Nonce check to active/deactivate add-ons added
* Update: User roles added in online page

= 13.1.1 =
- Fixed exclusions setting page and storing properly data in option page and keep lines in the input data
- Fixed some tweak issues and improved setting pages
- Fixed showing chart in RTL language
- Disable checking the SSL certificate while sending the request to check the plugin's REST API is enabled
- Updated Chart.js to v3.5.1

= 13.1 =
- New admin design!
- Improvement input data in setting and optimization page as well
- Escaping all input data in admin pages, (Special thanks to Vlad Visse)
- Improvement GeoIP enhancements
- Added subdivision names to cities for clarity
- Added links to mapping tools on more pages
- Updated the mapping tool link to a site that doesn't have errors

= 13.0.9 =
- Compatibility with PHP v8 and WordPress v5.8
- Fixed log file path and moved out to wp-content/uploads/wp-statistics/debug.log and protected for the public access as well
- Fixed updating widget and compatibility with block-based
- Improvement Image optimisation with ImageAlpha (png8+alpha) and ImageOptim. Thanks [vicocotea](https://github.com/vicocotea)
- Updated Chart.js to v3.4.1

= 13.0.8 =
- Improvement getting page id & type queries for the admin page
- Added no-cache in the hit endpoint response to compatibility with Cloudflare HTML caching
- Improvement exceptions to make sure working properly

= 13.0.7 =
- Compatibility with WordPress v5.7
- Fixes linking hits page from post meta box
- Support new hooks for email reporting and fix email logging
- Compatibility with Advanced Reporting and fixes tweak issues

= 13.0.6 =
- Improvement the time-out functionality while downloading the GeoIP city database.
- Fixed conflict with custom post-type column.
- Fixed error to passing the wrong argument for implode in WhichBrowser.
- Fixed date range selector in Top Pages.
- Fixed purge cache data after deleting the table.
- Fixed some issues & improvement historical functionality.
- Minor Improvements.

= 13.0.5 =
- Compatibility the ChartJs with some kind of plugins.
- Compatibility with WordPress v5.6
- Improvement error handling with REST API
- Added an option in the Optimization page to optimize & repair the tables.
- Added ability to filter `wp_statistics_get_top_pages()` by post type [#343](https://github.com/wp-statistics/wp-statistics/pull/343)
- Fixed the issue to load Purge class.
- Minor Improvements in SQL queries.

= 13.0.4 =
- Compatibility with PHP v7.2 and adjustment requires PHP version in the Composer to 5.6
- Fixed the issue to get the `Referred::get()` method during the initial plugin.
- Fixed issue to create tables queries in MariaDB v10.3
- Fixed the ChartJs conflict with some plugins.
- Disabled the Cronjob for table optimization in the background process (we're going to create an option on the Optimization page to handle it)
- Minor Improvements.

= 13.0.3 =

**We're very sorry regarding the previous update because we had a lot of changes on v13.0, we worked almost 1 year for this update and considered all situations and many tests, anyway try to update and enjoy the new features!**

- Fixed critical issue when some PHP modules such as bcmath are not enabled. it caused a fatal error, the purpose flag `platform-check` from Composer has been disabled.
- Fixed the "Connect to WordPress RestAPI" message while loading the admin statistics' widgets, the uBlock browser extension was blocking the WP Statistics's requests.
- Fixed the upgrade process issue, one of the previous action was calling and that caused the issue, that's now disabled.
- Disabled some repair and optimization table queries during the initial request.
- Minor Improvements.

= 13.0.2 =

**New Feature**

- Added error logs system
- Added the ability to change visitors’ data based on WordPress hook
- Added the ability to manage the plugin based on WP-CLI
- Added a link to show user’s location’s coordinates on Google Map based on their IP
- Added advanced filters in the page of WordPress website visitors list
- Added the class of sending standard email reports in the WordPress
- Added the ability to get WordPress users in the database record

**Bug Fix**

- Fixed recording visitors data problem when the cache plugin is installed
- Fixed exclusion problem in Ajax requests mode
- Fixed REST-API requests problem in JavaScript mode without jQuery library
- Fixed the issue of limiting the number of database table records
- Fixed the problem of getting WordPress page type in taxonomy mode
- Fixed display of visitor history for yesterday and today

**Improvement**

- Improved widget information based on REST-API
- Optimized and troubleshot database tables after an interval of one day
- Improved plugin information deleting operation
- Improved receiving country and city visitors information based on WordPress cache IP
- Improved display plugin management menus list in WordPress
- Improved search engine display in the mode of referring users from the search engine to the website
- Improved widgets display and Ajax loading capability
- Improved loading of JS files based on plugin-specific pages

= 12.6.1 =
- Added Whip Package for getting visitor's IP address.
- Fixed get the country code when the Hash or Anonymize IP Addresses is enabled.
- Added database upgrade class for update page type.
- Fixed duplicate page list in report pages.
- Fixed bug to get home page title.
- Improvement Sanitize subject for sending email reporting.
- Improvement jQuery Datepicker UI.
- Improvement visitor's hit when there was a broken file in that request.

= 12.6 =
# Added
- Post/Page Select in statistics page reporting according to post Type.
- Online Users widget, A cool widget to show current online users!
- A new table `visitor_relationship` for saving visitors logs.
- `user_id`, `page_id`, `type` columns to `statistics_useronline` table.
- Visitor count column in Top Country widget.

# Improvement
- Improvement MySQL time query in all functions.
- Improvement online users page UI.
- Improvement Top referrals UI.
- Improvement CSV exporter.
- Improvement pagination in admin pages that used the WordPress `paginate_links`.
- Improvement time filter in admin pages stats.
- Improvement  `admin_url` link in all admin pages.
- Improvement text wrap in all meta boxes.
- Fixed reset number online users list in period time.
- Schedule list in statistical reporting.
- Refer Param in Top Referring Sites page.
- Fix method to get IP addresses.
- Fix Page CSS.
- Fix the error of No page title found in the meta box.
- Fix show number refer link from custom URL.
- Fix update option for Piwik blacklist.

# Deprecated
- Remove `WP_Statistics_Pagination` class.
- Deprecate Top Search Words (30 Days) widget.

= 12.5.7 =
* Added: The Edge To Browser List.
* Added: `date_i18n` function in dates for retrieving localized date.
* Improved: The Browsers charts.
* Improved: Minor issues in GeoIP update function.
* Optimized: All png files. (60% Save).

= 12.5.6 =
* Fixed: Counting stats issue in Cache mode.

= 12.5.5 =
* Improved: The WP-Statistics Metaboxes for Gutenberg!
* Improved: The `params()` method.
* Improved: Referrers URL to be valid.

= 12.5.4 =
* Disabled: Notice cache in all admin pages just enabled in the summary and setting of WP-Statistics pages.
* Improved: Some methods. `params()` and `get_hash_string()`.

= 12.5.3 =
* Added: Option for enabling/disabling the hits meta box chart in the edit of all post types page and that option is disabled by default.
* Improved: The responsive problem of Recent Visitors and Latest Search Words widgets in WP Dashboard.
* Improved: Avoid using jQuery in the inline script to for send request when the cache is enabled.
* Improved: The GeoIP updater.
* Improved: The cache process in the plugin.
* Improved: Get location for Anonymize IP Addresses.
* Improved: The query in the Author Statistics page.

= 12.5.2 =
* Improved: Some issues in php v5.4

= 12.5.1 =
* Added: Cache option for support when the cache enabled in the WordPress.
* Added: Visitor's city name with GeoIP, you can enable the city name in Settings > Externals > GeoIP City
* Added: WP-Statistics shortcode in the TinyMCE editor. you can use the shortcode easily in the posts and pages.
* Added: Qwant search engine in the Search Engine Referrals.
* Added: Referrers to WP-Statistics shortcode attributes. e.g. `[wpstatistics stat=referrer time=today top=10]`
* Added: [WhichBrowser](https://whichbrowser.net/) and [CrawlerDetect](https://crawlerdetect.io/). These libraries give us more help in identifying user agents. the Browscap library removed.
* Improved: The Datepicker in the WP-Statistics pages, supported WordPress custom date format.
* Improved: The pagination class.
* Improved: The assets and fixed conflict ChartJS issue, when the Hit Statistics Meta box was enabled in posts/pages.
* Improved: The responsive summary page.
* Improved: Exclude Ajax requests, now compatible with [Related Post by Jetpack](https://jetpack.com/support/related-posts/).
* Improved: Some issues.
* Updated: Chart.js library to v2.7.3
* Enabled: Hit Statistics in posts/pages. the conflict problem solved.
* Disabled: The setting menu when the current user doesn't access.
* Disabled: Baidu search engine by default after installing.

= 12.4.3 =
* Disabled: The welcome page and Travod widget.

= 12.4.1 =
* Implemented: The `do_welcome()` function.
* Updated: Libraries to latest version.
* Added: `delete_transient()` for deleting transients when uninstalling the plugin.

= 12.4.0 =
* Removed: The Opt-Out removed.
* Added: Anonymize IP addresses option in the Setting > Privacy.

= 12.3.6.4 =
* Updated: Libraries to latest version.
* Enabled: The suggestion notice in the log pages.
* Improvement: Counting non-changing collections with `count()`. Thanks [Daniel Ruf](https://github.com/DanielRuf)

= 12.3.6.3 =
* Disabled: The suggestion notice.

= 12.3.6.2 =
* Tested: With PHP v7.2.4
* Added: Suggestion notice in the log pages.
* Added: New option for enable/disable notices.

= 12.3.6.1 =
* Improvement: I18n strings.
* Improvement: GDPR, Supported for DNT-Header.
* Improvement: GDPR, Added new option for delete visitor data with IP addresses.

= 12.3.6 =
* Note: GDPR, We Updated Our [Privacy Policy](https://wp-statistics.com/privacy-and-policy/).
* Added Privacy tab in the setting page and moved Hash IP Addresses and Store entire user agent in this tab.
* Added Opt-out option in the Setting page -> Privacy for GDPR compliance.
* Updated: Chart.js library to v2.7.2
* Fixed: Issue to build search engine queries.

= 12.3.5 =
* Improvement: Isolation Browscap cache processes to reduce memory usage.
* Improvement: Include `file.php` and `pluggable.php` in GeoIP downloader when is not exists.
* Fixed: GeoIP database update problem. Added an alternative server for download database when impossible access to maxmind.com

= 12.3.4 =
* Updated: Browscap to v3.1.0 and fixed some issues.
* Improvement: Memory usage in the plugin when the Browscap is enabled.
* Improvement: Cache system and update Browscap database.

= 12.3.2 =
* Added: New feature! Show Hits on the single posts/pages.
* Added: Pages Dropdown in the page stats.
* Fixed: Menu bar for both frontend & backend.
* Fixed: Issue to create the object of the main class.
* Fixed: Issue to get page title in empty search words option.
* Fixed: Issue to show date range in the charts.

= 12.3.1 =
* We're sorry about last issues. Now you can update to new version to resolve the problems.
* Updated: Composer libraries.
* Fixed: A minor bug in `get_referrer_link`.
* Improvement: `wp_doing_cron` function, Check before call if is not exist.
* Fixed: Issue to get IP in Hits class.
* Fixed: Issue to get prefix table in searched phrases postbox.
* Fixed: Issue in Browscap, Used the original Browscap library in the plugin.
* If you have any problem, don't forget to send the report to our web site's [contact form](https://wp-statistics.com/contact/).

= 12.3 =
* The new version proves itself more than twice as faster because we had a lot of changes in the plugin.
* Improvement: Management processes and front-end have been separated for more speed.
* Improvement: MySQL Queries and used multi-index for `wp_statistics_pages`.
* Improvement: Top Referring widget in Big data. Used Transient cache to build this widget data.
* Fixed: Issue in checking the Cron request.
* Fixed: Issue in i18n strings. The `load_plugin_textdomain` missed.
* Fixed: issue in generating query string in some state pages.
* Fixed: issue in admin widget. The `id` in label missed and used `get_field_id` method to get a correct id.
* Fixed: Admin bar menu icon.
* Updated: Chart.js library to v2.7.1

= 12.2.1 =
* Fixed: Issue to `add_column` callback.

= 12.2 =
* The new version proves itself more than twice as faster because we had a lot of changes in the plugin.
* Improvement: Many functions converted to classes.
* Improvement: Export data on the optimization page.
* Improvement: Constants, Include files.
* Improvement: Setting/Optimization page stylesheet and removed jQuery UI to generate tabs.
* Added: Top Search Words in the plugin.
* Fixed: Some notices error.
* Removed: Some unused variables.
* Removed: Force English option feature in the plugin.
* Thanks [Farhad Sakhaei](https://dedidata.com/) To help us with these changes.

= 12.1.3 =
* We're sorry about last issues. Now you can update to new version to resolve conflict issues.
* Fixed: Chart conflict issues with other libraries.
* Fixed: Chart height issue in css.
* Fixed: Correct numbering for pages > 1 in Top Referring page. [#22](https://github.com/wp-statistics/wp-statistics/pull/22/files)
* Fixed: Don't run the SQL if `$reffer` is not set. [#21](https://github.com/wp-statistics/wp-statistics/pull/21)
* Fixed: Refferer url scheme. [#24](https://github.com/wp-statistics/wp-statistics/pull/24) Thanks [Farhad Sakhaei](https://github.com/Dedi-Data)
* Fixed: Network menu icon.

= 12.1.0 =
* Added: Awesome charts! The Chartjs library used in the plugin for show charts.
* Updated: Missed flags icons. (Curaçao, Saint Lucia, Turkmenistan, Kosovo, Saint Martin, Saint Barthélemy and Mayotte)
* Updated: Countries code.
* Updated: Settings and Optimization page styles.
* Fixed: Showing data on the Browsers, Platforms and browsers version charts.
* Fixed: Postbox container width in Logs page.
* Removed: `WP_STATISTICS_MIN_EXT` define for load `.min` version in css/js.
* Removed: Additional assets and the assets cleaned up.

= 12.0.12.1 =
* Fixed: PHP syntax error for array brackets when the PHP < 5.4

= 12.0.12 =
* Added: Add-ons page! The Add-ons add functionality to your WP-Statistics. [Click here](https://wp-statistics.com/add-ons/) to see current Add-ons.
* Fixed: Translations issue.
* Updated: GeoIP library to v2.6.0
* Updated: admin.min.css

= 12.0.11 =
* Release Date: August 17, 2017
* Fixed: links issue in the last visitors page.
* Fixed: i18n issues (hardcoded strings, missing or incorrect textdomains).
* Updated: admin CSS style. set `with` for Hits column in posts/pages list.
* Updated: Improve consistency, best practices and correct typos in translation strings.
* Updated: More, Reload and Toggle arrow buttons in metaboxes are consistent with WP core widget metaboxes, with screen-reader-text and key navigation. by [Pedro Mendonça](https://profiles.wordpress.org/pedromendonca/).

= 12.0.10 =
* Release Date: July 24, 2017
* Added: UptimeRobot to the default robots list.
* Fixed: Uses `esc_attr()` for cleaning `$_GET` in referrers page.
* Removed: `screen_icon()` function from the plugin. (This function has been deprecated).

= 12.0.9 =
* Release Date: July 3, 2017
* Fixed: XSS issue with agent and ip in visitors page, Thanks Ryan Dewhurst from Dewhurst Security Team.
* Updated: GeoIP library to v2.5.0
* Updated: Maxmind-db reader library to v1.1.3

= 12.0.8.1 =
* Release Date: July 2, 2017
* Fixed: load languages file. please visit [translations page](https://wp-statistics.com/translations/) to help translation.

= 12.0.8 =
* Release Date: June 29, 2017
* Fixed: SQL Injection vulnerability, thanks John Castro for reporting issue from sucuri.net Team.
* Added: new hook (`wp_statistics_final_text_report_email`) in email reporting.
* Removed: all language files from the language folder. Translations have moved to [translate.wordpress.org](https://translate.wordpress.org/projects/wp-plugins/wp-statistics).

= 12.0.7 =
* Release Date: June 8, 2017
* WordPress 4.8 compatibility
* Updated: WP-Statistics logo! Thanks [Arin Hacopian](http://aringrafix.com/) for design the logo.
* Removed: manual file and moved to [wp-statistics.com/category/documentation](http://wp-statistics.com/category/documentation)
* Fixed: items show issue in referring page.
* Fixed: recent visitor link in dashboard widget.

= 12.0.6 =
* Release Date: April 27, 2017
* Fixed: Additional XSS fixes, thanks Plugin Vulnerabilities Team.

= 12.0.5 =
* Release Date: April 6, 2017
* Fixed: Referrers, that are not search engines, are missing from the referrers widget/page.
* Fixed: Additional XSS fixes, thanks Gen Sato who submitted to JPCERT/CC Vulnerability Handling Team.
* Fixed: Updated CSS definition for widgets to avoid overflow only for WP-Statistics widgets instead of all active widgets to avoid conflicts with other plugins.

= 12.0.4 =
* Release Date: April 1, 2017
* Fixed: Additional XSS issue with referrers, thanks Gen Sato who submitted to JPCERT/CC Vulnerability Handling Team.
* Updated: Optimizations for referrers encoding.
* Updated: Logic for detecting invalid referrer types to capture more types.

= 12.0.3 =
* Release Date: March 31, 2017
* Fixed: Additional XSS issue with referrers, thanks Gen Sato who submitted to JPCERT/CC Vulnerability Handling Team.

= 12.0.2 =
* Release Date: March 30, 2017
* Fixed: Top referrer widget was not using the new search table.
* Fixed: On the referrers page, selecting a host would reset the date range.
* Fixed: XSS issue with date range picker, thanks Anon submitter to JPCERT/CC Vulnerability Handling Team.
* Fixed: XSS issue with referrers, thanks Gen Sato who submitted to JPCERT/CC Vulnerability Handling Team.

= 12.0.1 =
* Release Date: March 24, 2017
* Added: Check for BCMath or GMP Math extensions to support newer GeoIP database files.
* Fixed: Robots list not being updated on upgrades properly in some cases.
* Fixed: wp_statistics_get_uri() to handle cases where site and home URI's are different.
* Fixed: wp_statistics_get_uri() to validate what is being removed to make sure we don't remove the wrong things.
* Fixed: Display of individual referring site stats.

= 12.0.0 =
* Release Date: February 18, 2017
* Added: Categories, tags and authors stats pages.
* Added: Option to exclude AJAX calls from the statistics collection.
* Fixed: Removal of settings now uses the defaults and handles a conner case that could cause corrupt settings to be saved during the reset.
* Fixed: URI retrieval of the current page could return an incorrect result in some cases.
* Fixed: Images in the HTML version of the admin manual did not display correctly in Microsoft IE/Edge.
* Fixed: Incorrect variable name on the exclusions page for the robots list.
* Updated: After "removal" the notice on the plugins page is now at the top of the page as an admin notice instead of being embedded in the plugin list.
* Updated: Split change log, form this point forward only the changes for the last two major versions will be included, older entries can be found in the changes.txt file in the plugin root.

= 11.0.3 =
* Release Date: January 13, 2017
* Added: Option to reset plugin options without deleting the data.
* Fixed: If IP hashing as enabled a PHP would be generated during the hashing.
* Fixed: Typo in JavaScript code that would cause some errors not to be displayed.
* Fixed: Make sure the historical table exists before checking the keys on it which would cause extra output to be generated on first install.
* Updated: RTL CSS styles for left/right div's in the admin dashboard, thanks sszdh.

= 11.0.2 =
* Release Date: December 1, 2016
* Fixed: Top visitors page css for date picker.
* Fixed: Incorrect url for link on recent visitors widget.
* Fixed: Make sure the tick intervals are always whole numbers, otherwise the axis ticks won't match up with the data on line charts.
* Fixed: Make sure when looking up a page/post ID for a URL to take the latest visited id instead of the first in case the URI has been reused.
* Fixed: Duplicate display of hit statistics on hits page in some corner cases.

= 11.0.1 =
* Release Date: November 7, 2016
* Fixed: Don't refresh a widget if it's not visible, fixes the widget being replaced by a spinner that never goes away.
* Updated: Minimum PHP version is now 5.4.
* Updated: Additional error checks for new IP code.
* Updated: jqPlot library to version development version and added DST fix.

= 11.0 =
* Release Date: October 28, 2016
* Added: IPv6 Support.
* Added: Time attribute to searches shortcode.
* Added: Basic print styles for the overview and log pages.
* Fixed: Default provider for searches shortcode.
* Fixed: Display of top sites list when the display port is very small would .
* Fixed: CSS for date picker not loading.
* Fixed: Incorrect stats on some pages for date ranges that end in the past.
* Fixed: Date range selector on stats now properly displays a custom range after it has been set.
* Fixed: "Empty" log widget columns could not have widgets added to them.
* Updated: GeoIP library to version 1.1.1.
* Updated: phpUserAgent library to 0.5.2.
* Updated: Language on the front end widget to match the summary widget in the admin.
* Removed: Check for bc math.
* Removed: Last bits of google maps code.

= 10.3 =
* Release Date: August 19, 2016
* Added: Support for minified css/js files and the SCRIPT_DEBUG WordPress define.
* Added: <label> spans around the text for widget fields for easier styling.
* Added: 'AdsBot-Google' to the robots list
* Fixed: Pop up country information on the map dashboard widget will now stay on top of the WordPress dashboard menus.
* Fixed: WP_DEBUG errors in front end widget.
* Updated: JQVMap library to version 1.5.1.
* Updated: jqPlot library to version 1.0.9.
* Updated: GeoIP library to version 2.4.1.

= 10.2 =
* Release Date: August 2, 2016
* Added: Support for use page id in Get_Historical_Data function.
* Updated: jQuery CSS references.
* Fixed: Various WP_DEBUG warnings.
* Fixed: Incorrect URL in quick access widget for some of the totals.
* Fixed: Make sure to escape the post title in the widget otherwise the graph may not be displayed correctly.
* Removed: Google Maps support as Google no longer supports keyless access to the API (http://googlegeodevelopers.blogspot.com.es/2016/06/building-for-scale-updates-to-google.html).

= 10.1 =
* Release Date: April 3, 2016
* Updated: Top pages page to list the stats for the selected date range in the page list.
* Updated: Added check for gzopen() function to the Optimization page as some builds of PHP are broken and do not include it which causes the GeoIP download to fail causing a white screen of death in some cases.
* Updated: Added check to make sure we can write to the upload directory before doing so.
* Updated: User Agent Parser library updated to V0.5.1.
* Updated: MaxMind Reader Library updated to V1.1.
* Fixed: Only display the widgets on the overview page that have their features enabled.
* Fixed: Top pages list failed when there were less than 5 pages to display.
* Fixed: Manual download links did not function.
* Fixed: Typo in function name for purging the database.
* Fixed: Renamed the Czech and Danish translation file names to function correctly.
* Fixed: Ensure we have a valid page id before record the stat to the database to avoid an error being recorded in the PHP error log.

= 10.0.5 =
* Release Date: February 5, 2016
* Fixed: Date range selector display after entering a custom date range.
* Fixed: Date ranges that ended in the past displaying the wrong visit/visitors data.

= 10.0.4 =
* Release Date: January 21, 2016
* Fixed: Recent Visitors widget in the dashboard did not work.
* Fixed: Top Visitors in Overview page would not reload.
* Fixed: Links for yesterday and older visitors count went to wrong page.
* Fixed: Typo in purge code that caused a fatal error.

= 10.0.3 =
* Release Date: January 19, 2016
* Updated: Google map API now always uses https.
* Fixed: Google map error that broken the overview page display of charts and the map.

= 10.0.2 =
* Release Date: January 19, 2016
* Added: Additional error checking on widget load so they will retry if there is a failure.
* Fixed: Added code to flush out invalid widget order user meta.
* Fixed: Include Fatal Error if corrupt data was passed to the ajax widget code.

= 10.0.1 =
* Release Date: January 18, 2016
* Fixed: If you re-ordered the widgets on the overview screen and then reloaded the page, all the widgets would disappear.

= 10.0 =
* Release Date: January 15, 2016
* Added: Widgets now support reloading on overview and dashboard screen.
* Updated: Overview screen now loads widgets dynamically to reduce memory usage.
* Updated: Dashboard widgets now load dynamically.
* Updated: Enabling dashboard widgets now no longer require a page load to display the contents.
* Updated: Replaced the old eye icon and "more..." link on the right of the title on the overview widgets with a new icon on the right beside the open/close icon.
* Fixed: Removed extraneous single quote in SQL statement on referrers page, thanks jhertel.
* Fixed: Order of parameters in referrers page when viewing individual referrers was incorrect and resulted in a blank list.
* Fixed: UpdatedSQL for last post date detection to order by post_date instead of ID as someone could enter a date in the past for their publish date.  Thanks PC1271 for the fix.
* Fixed: The referrers widget would only select the first 100k records due to a limit in PHP/MySQL, it will now select all records.
* Removed: Widget selection and ordering from the settings page, the "Screen Options" tab can now be used on the enabled/disable widgets and drag and drop will remember their location.
* Removed: Overview page memory usage in the optimization page as it is no longer relevant.

= 9.7 =
* Release Date: December 30, 2015
* Added: A date range to the referrers page.
* Added: A Date range selector to browsers page.
* Updated: General SQL cleanups.
* Updated: browscap library to 2.1.1.
* Updated: GeoIP library to 2.3.3.
* Updated: phpUserAgent library to 0.5

= 9.6.6 =
* Release Date: November 1, 2015
* Updated: Use timezone corrected dates for date pickers.
* Updated the get_ip code to return 127.0.0.1 if no IP address is found (can happen when a user runs WordPress from a command line function, like when setting up a scheduled cron job).
* Fixed: Several security related updates, thanks CodeV.

= 9.6.5 =
* Release Date: September 18, 2015
* Updated: Updated support libraries, including browscap (2.0.5) and GeoIP (webservices).
* Updated: The hits column in the post/pages list no longer requires manage permissions but instead view permission.
* Fixed: New browscap.ini format was causing fatal errors in certain circumstances.
* Fixed: Missing close tag on the summary widget's users online link.
* Fixed: When purging data an incorrect column name was used when updating the historical table.

= 9.6.4 =
* Release Date: September 15, 2015
* Updated: Support new browscap.ini file format.

= 9.6.3 =
* Release Date: September 11, 2015
* Updated: The database update nag link to the optimization page instead of the settings page.
* Updated: Handle the case where the downloads haven't happened yet.
* Fixed: In some cases the extenrals tab would show the wrong date for the next scheduled update.
* Fixed: In some cases the Piwik and other features may not be enabled even when the checkboxes were selected.
* Fixed: If no page id was passed in on the pagestats shortcode the wrong default for page id would be used and no stats would be displayed.

= 9.6.2 =
* Release Date: September 5, 2015
* Added: Search table to the empty table list.
* Added: Search table size to the optimization page.
* Added: Updated SQL calls to the pages table to use $wpdb->prepare() to protect against SQL inject attacks.
* Fixed: Check of $wp_roles type as it is an object and not an array which caused only admins to be able to view the statistics.
* Fixed: Top referring only displayed search engines.
* Updated: Layout of the maintenance tab.

= 9.6.1 =
* Release Date: September 4, 2015
* Fixed: Error with undeclared global $WP_Statistics when updating the database.
* Added: Re-validation of the current database updates required when loading the optimization page.

= 9.6 =
* Release Date: September 3, 2015
* Added: New admin notices for if the database requires updates.
* Added: Page/post id field to pagestats shortcode.
* Added: Ask.com to search engine list, disabled by default.
* Fixed: Display of the dashboard referrers widget.
* Fixed: incorrect table name when dropping the old 'AString' field.
* Fixed: Error message if the global $wp_roles hadn't been set when we accessed it.
* Fixed: When exporting, no data was exported.
* Fixed: When excluding countries, multiple entries would not be parsed correctly.
* Updated: Purging code now includes the search table.
* Updated: Search conversion code to limit the number of records retreived to 10000 and then loop through them to ensure we don't run out of memory during the conversion process.
* Updated: Cleaned up the admin notices code.
* Updated: Persian translation. Thanks Ali Zeinali.

= 9.5.3 =
* Release Date: August 19, 2015
* Added: More robust error reporting if a plugin table is missing.
* Added: Support to export the search table.
* Fixed: The install script for older versions of MySQL (5.0.x).
* Fixed: Export script no longer generates errors when exporting an empty table.
* Fixed: WP_Debug error on $crawler when it was an object but didn't have the right properties (aka wasn't the right object).
* Fixed: Sidebar widget works again in WordPress 4.3.

= 9.5.2 =
* Release Date: August 8, 2015
* Fixed: XSS issue with top-referrers page, thanks Swift Security (http://swiftsecurity.swte.ch/).
* Updated: If the GeoIP code is disabled, the warning message was pointing to the old GeoIP tab instead of the new Externals tab.
* Updated: French translation.

= 9.5.1 =
* Release Date: August 4, 2015
* Fixed: Issue with verifying the WP-Statistics tables exist on databases with hyphens in their names.
* Updated: Arabic translation.

= 9.5 =
* Release Date: August 3, 2015
* Added: Referrer Spam exclusions using the Piwik Referrer Spam Blacklist (see Statistics->Settings->Externals to enable).
* Added: Code to remove 'AString' column if it exists in the visitors table during upgrades (bug in a older previous version of WP-Statistics erroneously created it).
* Fixed: Duplicate key name warning during upgrades for 'date_ip_agent' index.
* Fixed: Warning on 'date_ip' index does not exist when trying to drop it during upgrades.
* Updated: Storing of search engine/words data is now in it's own table for better performance.
* Updated: Combined the GeoIP and browscap tabs in settings in to the Externals tab.
* Updated: GeoIP library to V 2.3.1.

= 9.4.1 =
* Release Date: July 9, 2015
* Fixed: SQL injection security issue for users with access to the admin pages.
* Fixed: Bug in code to save new "Treat corrupt browser info as a bot" setting.
* Fixed: Bug in scheduled data pruge code that would not append the correct table prefix.
* Updated: Admin manual.

= 9.4 =
* Release Date: July 3, 2015
* Added: Date selector to top visitors page.
* Added: Option to exclude WordPress's "Not Found" page from the statistics.
* Added: Option to treat corrupt http header information as bots (missing IP addresses or user agents).
* Added: New robots to list; 007ac9, 5bot, advbot, alphabot, anyevent, blexbot, bubing, cliqzbot, dl2bot, duckduckgo, EveryoneSocialBot, findxbot, glbot, linkapediabot, ltx71, mediabot, medialbot, monobot, OrangeBot, owler, pageanalyzer, porkbun, pr-cy, pwbot, r4bot, revip, riddler, rogerbot, sistrix, SputnikBot, u2bot, uni5download, unrulymedia, wsowner, wsr-agent, x100bot and xzybot
* Fixed: Make sure the admin bar only appears for users that have read/manage permissions in WP-Statistics.
* Updated: Split the access and exclusions tabs in settings.

= 9.3.1 =
* Release Date: May 15, 2015
* Fixed: Typo in options name that caused the visitors map to never be displayed.

= 9.3 =
* Release Date: May 15, 2015
* Added: Shortcode UI (aka ShortCake) support.
* Added: Donation menu and dismissble banner on the overview page.
* Added: Applebot, Superfeedr, jetmon, sfFeedReader and feedzirra to the robots list.
* Added: Summary postbox on hit statistics page.
* Added: Summary postbox on exclusions page.
* Added: Date range selector on top countries page.
* Added: Purge data based on visitor's hit count on the optimization page.
* Added: Option to purge data based on visitor's hit count on a daily basis.
* Added: Option to record the page title for search referrals that do not contain a query value.
* Updated: Moved all ajax and pseudo ajax calls to use the standard WordPress ajax and init routines instead of using wp-load.php.
* Updated: Widgets and pages will only be displayed if the associated statistics is being collected, for example the search engine referrals will only be displayed if the visitor tracking option is enabled.
* Fixed: Typo in variable name for one of the dashboard widgets.
* Fixed: PHP error when the $browser object wasn't an object when we checked the crawler property.
* Fixed: Incorrect parameter for get_option() on two option in the settings page.
* Fixed: Widget's didn't translate correctly.

= 9.2 =
* Release Date: April 26, 2015
* Added: Date range selector for charts now supports arbitrary date ranges with JavaScript date selector.
* Added: If the site is using the blogroll for the homepage, use the blog title as the page name instead of leaving it blank.
* Updated: How country codes are loaded for dashboard, widgets and pages.
* Fixed: Incorrect URL in the admin manual.
* Fixed: WP_DEBUG warning if formatting was not specified in the short code.

= 9.1.3 =
* Release Date: April 14, 2015
* Added: Quick link to summary stats.
* Added: Escaped text fields in the settings page with htmlentities() to protect against rouge administrators hijacking other admin sessions, thanks Kaustubh.
* Fixed: Exclusions page had duplicate quotation marks in some JavaScript fields causing errors.
* Fixed: Display of last_counter that is already set to the correct date and doesn't need to be adjusted for timezone.

= 9.1.2 =
* Release Date: March 20, 2015
* Fixed: Removed spurious comma in SQL creation script for Visits table, thanks kitchin.

= 9.1.1 =
* Release Date: March 19, 2015
* Fixed: Verify the $display settings return an array before using it as an array to avoid warning on overview page.

= 9.1 =
* Release Date: March 18, 2015
* Added: Unique index requirement on visits table to avoid race condition creating duplicate entires.
* Added: Option to the optimization page to remove duplicates and add new  unique index to visits table on existing installs.
* Updated: Translations, thanks to all of our translators!
* Updated: Cleanup of some WP Debug warnings.
* Fixed: JavaScript postboxes call was currupted on some pages causing a javascript error.
* Fixed: Change html encode to jason_ecnode for data to be used in javascript to avoid single quotes as part of the translation breaking the javascript array, this change now fixes extended character display in the JavaScript charts.
* Fixed: Verify $WP_Statistics is an object before using it, which was causing a fatal error on some installs.
* Removed: Redudnent e modifier in preg_replace_callback to avoid php warning message.

= 9.0 =
* Release Date: March 12, 2015
* Added: URL exclusions option.
* Added: Swedish translation, thanks ronneborn.
* Added: Kurdish (Sorani) translation, thanks sardar4it.
* Added: Daily wp cron job to create an entry in the visits table for the next day to avoid a race condition.
* Updated: The visits code now uses a SQL UPDATE instead of WP's update() to avoid a race condition.
* Updated: Performance improvements in the last visitors page.
* Updated: Performance improvements in the referrers page.
* Updated: Added missing dash_icon call in online users page.
* Updated: Make sure the $wp_object global variable is an object before using it, just in case, in the hits code.
* Updated: Make sure the $wp_query global variable is an object before using it, just in case, in the hits code.
* Updated: Removed variables from i18n functions for better translation support.
* Updated: Removed requirement for date_default_timezone_set() which conflicted with some other plugins.
* Updated: Make sure to html encode data to be used in javascript to avoid single quotes as part of the translation breaking the javascript array.
* Updated: Change summary widget to be clearer about time frames.
* Updated: Replace deprecated preg_replace (with /e) with preg_replace_callback.  Thanks gbonvehi.
* Updated: Use full path to ensure the require_once finds the purge file in the scheduled db maintenance script.
* Updated: Persian translation.
* Updated: Renamed pagination class to avoid name collisions with other plugins.
* Updated: Date display in recent visitors and search words now uses the WordPress date format setting.
* Updated: Upgrade email is now send at the end of the page load as wp_mail() hasn't been created during the upgrade script.
* Fixed: Export code to handle large tables.
* Fixed: Exclusion display for some 'reasons' always being 0.
* Removed: Replaced use of global $table_prefix with $wpdb->prefix.
* Removed: Use of deprecated $blog_id.  Thanks gbonvehi.

= 8.8.1 =
* Release Date: March 9, 2015
* Updated license to GPL3.

= 8.8 =
* Release Date: January 31, 2015
* Added: Installation/upgrades/removals on WordPress multi-sites now upgrade all sites in the network if the installing user has the appropriate rights.
* Added: RSS feed URL's can now be excluded.
* Added: Option to set the country code for private IP addresses.
* Fixed: Additional WP_DEBUG warning fixes.
* Fixed: Incorrect parameter list in get_home_url() when checking for self referrals.
* Fixed: Single quotes can now be used in the report content without being escaped.
* Fixed: Referrers menu item was misspelled.
* Updated: Italian, French, Polish, Arabic, Persian and Chinese translation.
* Updated: Widget now formats numbers with international standards.
* Updated: Short codes now support three number formatting options; i18n, english or none.
* Updated: Removed old throttling code for hits which is no longer required.
* Updated: IP address exclusions without a subnet mask now assume a single IP address instead of all IP addresses.

= 8.7.2 =
* Release Date: January 6, 2015
* Added: shareaholic-bot to robots list.
* Fixed: Robot threshold setting was not being saved.
* Updated: Italian translation, thanks illatooscuro.
* Updated: Arabic translation, thanks Hammad.
* Updated: Honey pot page title now includes "Pot" in it.

= 8.7.1 =
* Release Date: December 28, 2014
* Fixed: Variable scope for the exclusion match/reason updated to protected from private to allow the GeoIP code to set them.  This could cause various issues including failed uploades depending on the error reporting level set for PHP.

= 8.7 =
* Release Date: December 27, 2014
* Added: Charts with multiple lines now include the data set name in the tooltip.
* Added: Honey pot option to detect crawlers.
* Added: Robot threshold option.
* Added: Hit count for visitors is now recorded and displayed.
* Added: Top Visitors today widget and page.
* Fixed: GeoIP exclusion logic didn't work as the location information was not set before it was applied, moved it to the appropriate location.
* Fixed: Incorrect setting names for country include/excludes as well as hosts.
* Fixed: Page URI length could exceed the database storage limit and cause duplicate entry warnings, URI is now truncated before being stored.
* Updated: Polish and Farsi translations.
* Updated: User agent parser to V0.3.2.
* Updated: GeoIP library to v2.1.1.

= 8.6.3 =
* Release Date: December 11, 2014
* Fixed: Really fix included countries code this time.
* Fixed: Typo in excluded hosts code.

= 8.6.2 =
* Release Date: December 11, 2014
* Fixed: New included countries code incorrectly identified all countries as excluded.

= 8.6.1 =
* Release Date: December 11, 2014
* Added: Code to perform additional clean up of uncommon user agents.
* Fixed: Spurious break statement in GeoIP exclusion code which caused a fatal error in certian cases.

= 8.6 =
* Release Date: December 11, 2014
* Added: Option to remove URI parameters from page tracking.
* Added: GeoIP exclusion options.
* Added: Host name exclusion options.
* Fixed: Map dashboard widget fails when Google is selected as map provider.
* Fixed: Changing the statistical report schedule would not actually change the schedule unless you disabled and then enabled the statistical reports feature.
* Updated: French language.

= 8.5.1 =
* Release Date: December 2, 2014
* Fixed: Typo in last search page causing fatal error in PHP.

= 8.5 =
* Release Date: December 2, 2014
* Added: try/catch condition around browscap call to avoid fatal errors stopping the script.
* Added: Page trend widget to post/page editor.
* Added: Aland Islands Flag icon.
* Added: Option to record all online users regardless if they would otherwise be excluded.
* Added: Option to disable the page editor widget.
* Fixed: Various security fixes, thanks Ryan.
* Fixed: Resolved warnings when natcasesort received a null list, thanks robertalks.
* Fixed: Before updating the browscap.ini cache file, remove stale lock files.
* Fixed: Avoid throwing a fatal error when the shutdown code is called if for some reason the global $WP_Statistics variable has been destroyed during a page load.
* Updated: The online code now uses the same rules to exclude users as the hits code.
* Updated: Minor code cleanups and data return checks.
* Updated: German translations, thanks bios4.
* Updated: Polish and Turkish translations.
* Updated: Use built in WordPress function to translate user roles instead of custom strings in our PO file, thanks bios4.

= 8.4 =
* Release Date: November 26, 2014
* Added: Dashboard widgets for all of the widgets on the overview page.
* Added: Option to disable all dashboard widgets.
* Added: Old dashboard widget upgraded with last 10 days of hits statistics.
* Added: Online users page and time a user has been online.
* Fixed: Fixed missing site_url on top 10 pages in the overview page.
* Fixed: Incorrect url generated for Google map if dashboard was being forced in to https mode.
* Fixed: Properly un-escape quotation marks in report body if magic quotes is enabled.
* Fixed: URL referrer CSS style would 'push' other entires to the next line on small displays.
* Fixed: Various PHP warnings on uninitalized variables, thanks bseddon
* Updated: Polish translations.
* Updated: Default map type now set to JQVMap.

= 8.3.1 =
* Release Date: November 19, 2014
* Updated: Various SQL code clean ups.
* Updated: Varioud data validation clean ups.
* Updated: Various data output encoding updates, thanks Marc.

= 8.3 =
* Release Date: November 14, 2014
* Added: Sanity checks for file size and results to browscap.ini updates, if the new cache file size is wrong or it mis-identifies a common real browser as a crawler the update will be rolled back.
* Added: Option to e-mail a report on browscap.ini, database pruning, upgrades and GeoIP database updates.
* Updated: Polish translations.
* Updated: Added "Notificaitons" tab to the settings page and moved statistical report settings to it.
* Fixed: The historical data table no longer uses reserved keywords as column names which caused issues on older versions of MySQL.
* Fixed: Unable to set visits historical count.
* Fixed: Purging did not record visits/visitors correctly if not already set through the optimization page.
* Fixed: JavaScript bug when a non-administrative user viewed the settings page.
* Removed: Reference to old settings file for the widget.

= 8.2 =
* Release Date: November 6, 2014
* Added: Support for historical data.
* Added: Removal option.
* Updated: Optimized SQL statements to hopefully get rid of duplicate key error/warnings.
* Updated: Persian, Polish, Italian translations.
* Fixed: Duplicate date display on charts due to DST time change.

= 8.1.1 =
* Release Date: October 26, 2014
* Fixed: Bug in browscap.ini update code that could mis-identify all hits as robots.
* Fixed: Bug in the scheduled reports code that failed to process the report content correctly.
* Fixed: Bug in schedule reports that failed to select the current schedule in the drop down.
* Removed: Depricated variables from the report content description.

= 8.1 =
* Release Date: October 18, 2014
* Added: Detected browser information to the optimization page.
* Updated: Re-organized new browscap code to avoid PHP 5.2 or below throwing a parse error.
* Fixed: If the client sent no user agent string a fatal error would be generated, added additional logic to handle this case.
* Removed: Unused code in various log displays.

= 8.0 =
* Release Date: October 16, 2014
* Added: browscap.ini support for robot detection.
* Added: Statistics->Optimization->Database tab now how an option to re-run the install routine in case you have had to delete tables from the database.
* Added: PHP version check, WP-Statistics now requires PHP 5.3 and will no longer execute without it.
* Added: Dashboard widget.
* Updated: Top pages now decode the URL for better readability.
* Updated: GeoIP library from version 0.5 to 2.0.
* Updated: User Agent detection code.
* Updated: Serbian, Polish translations.
* Updated: All missing language strings have been machine translated when possible.
* Updated: IP hashing code has moved out of beta.
* Fixed: Incorrect country name being displayed for Georgia.
* Fixed: Bug in detecting the new index in the Statistics->Optimization->Database tab.
* Fixed: Duplicate closing tag in summary page.
* Fixed: Purging the database did not display the results.
* Removed: Support for old format substitution codes in the statistics reports, upgrade now converts them to short codes.

= 7.4 =
* Release Date: September 19, 2014
* Added: Link URL for referred.
* Updated: Widget code now adhears to WordPress standards.
* Updated: Persian, Arabic and German (thanks Mike) translations.
* Updated: Unique index on visitors table now takes in to account the agent/platform/version information.
* Updated: Line charts now redraw when the legend is clicked to add/remove a line.
* Fixed: Dates on charts with large number of data points now no longer overwrite each other.
* Fixed: Admin bar menu item would use the incorrect admin URL in some circumstances.
* Removed: Screenshots are no longer included in the distribution.

= 7.3 =
* Release Date: September 8, 2014
* Added: Option to delete the admin manual.
* Added: Option to force the robots list to be updated during an upgrade.
* Added: Beta code for not storing IP addresses in the database.
* Fixed: Bug with new JQVMap code not displaying flags correctly.
* Updated: French (fr_FR) language, thanks apeedn.
* Updated: Visitors online code now treats different browsers/platforms from the same IP address as different users (this helps with multiple users behind proxy servers).
* Updated: Visitors code now treats different browsers/platforms from the same IP address as different users (this helps with multiple users behind proxy servers).
* Updated: Persian (fa_IR) language.
* Updated: Tested with WordPress 4.0.

= 7.2 =
* Release Date: August 22, 2014
* Added: Total visitors by country to the push pins on the overview map.
* Added: Statistical reports can now be sent to a custom list of e-mail addresses instead of just the administrator.
* Added: JQVMap option for the overview map.
* Fixed: Additional WP_DEBUG warnings cleaned up.
* Fixed: Google map would sometimes only use part of the area to draw the map in the overview page.
* Updated: Statistical report schedules are now listed by occurrence instead of randomly.
* Updated: Vertical alignment of statistical report option label column now correct.
* Updated: Various grammatical updates.
* Updated: Overview map now limits the number of visitors to five per country.
* Updated: Persian (fa_IR) language.

= 7.1 =
* Release Date: August 13, 2014
* Added: clearch.org search provider, disabled by default.
* Added: Database tab to optimization page to manually add unique index on the visitors table removed in 7.0.3.
* Updated: Additional WP_DEBUG message fixes.
* Updated: Overview widgets no longer overflows on smaller displays.
* Updated: Charts now properly resize when the browser window does.

= 7.0.4 =
* Release Date: August 9, 2014
* Fixed: Typo in table definition of visitor table's UAString field.

= 7.0.3 =
* Release Date: August 8, 2014
* Added: Extra check that the co-efficient setting is valid.
* Updated: Format of the dbDetla scripts to match the guidelines from WordPress, thanks kitchin.
* Updated: Handled some WP_DEBUG warning messages, thanks kitchin.
* Updated: Multiple additional WP_DEBUG warning fixes.
* Updated: Arabic (ar) language.
* Updated: Polish (pl_PL) language.
* Fixed: Typo in variable name which causes the robots list to be overwritten with the defaults incorrectly.
* Fixed: Access role exclusions and search engine exclusions options not displaying correctly in the settings page.
* Removed: Database upgrade code to add the unique index on the visitors table due to issues with multiple users.  Will add back in a future release as a user selectable option.

= 7.0.2 =
* Release Date: August 7, 2014
* Fixed: Database prefix not being used when creating/updating tables correctly.
* Fixed: New installs caused an error in the new upgrade code as the visitor table did not exist yet.
* Fixed: Replaced use of deprecated $table_prefix global during install/update.

= 7.0.1 =
* Release Date: August 5, 2014
* Fixed: Error during new installations due to $wpdb object not being available.

= 7.0 =
* Release Date: August 5, 2014
* Added: New robots to the robots list: aiHitBot, AntivirusPro, BeetleBot, Blekkobot, cbot, clumboot, coccoc, crowsnest.tv, dbot, dotbot, downloadbot, EasouSpider, Exabot, facebook.com, FriendFeedBot, gimme60bot, GroupHigh, IstellaBot, Kraken, LinkpadBot, MojeekBot, NetcraftSurveyAgent, p4Bot, PaperLiBot, Pimonster, scrapy.org, SearchmetricsBot, SemanticBot, SemrushBot, SiteExplorer, Socialradarbot, SpiderLing, uMBot-LN, Vagabondo, vBSEO, WASALive-Bot, WebMasterAid, WeSEE, XoviBot, YoudaoBot,
* Added: Overview page can now be customized for what is displayed on a per user basis.
* Added: Overview tab to the settings page to control what is displayed.  This page is available to any user that has read access to WP-Statistics.
* Added: Dutch (nl_NL) translation, thanks Friso van Wieringen.
* Added: New index on visitor table for existing installs to avoid duplicate entries being created.
* Added: jqPlot javascript library.
* Added: Three new schedule options for statistical reports; weekly, bi-weekly and every 4 weeks.
* Fixed: Some country codes not displaying in the "Top Countries" overview widget/page.
* Fixed: Export filename contained a colon, which is not a valid character.
* Fixed: In some cases purging data in the optimization page would succeed but the UI would "re-activate".
* Updated: All charts now use jqPlot instead of HighCharts so we are now fully GPL compliant.
* Updated: "Top Referring Sites" on the overview page now only displays if there are entries to be displayed.
* Updated: "Latest Search Words" on the overview page now only displays if there are entries to be displayed.
* Updated: "Top Pages Visited" on the overview page now only displays if there are entries to be displayed.
* Updated: About on the overview page box.
* Updated: Settings page from css tabs to jQuery tabs.
* Updated: Settings system (which used individual WordPress settings for each option) to a new unified system (uses a single WordPress setting and stores it as an array)
* Updated: Optimization page from css tabs to jQuery tabs.
* Updated: Install/Upgrade code to share a single code base.
* Updated: Persian (fa_IR) language.
* Updated: Arabic (ar) language.
* Updated: rtl.css file for new version.
* Updated: Lots of code comments.
* Updated: Statistical report schedule list in settings is now dynamically generated.
* Updated: WP-Statistics screenshots.
* Removed: "Alternate map location" setting as it has been made redundant by the new overview display settings.
* Removed: "Chart type" setting as chart types are now hard coded to the appropriate type for the data.
* Removed: HighCharts javascript library.
* Removed: Unused function objectToArray().

= 6.1 =
* Release Date: June 29, 2014
* Added: Display of the current memory_limit setting from php.ini in the optimization page.
* Added: New index on visitor table for new installs to avoid duplicate entries being created.  A future update will add this index to existing installs but will need additional testing before it is implemented.
* Added: Seychelles flag.
* Updated: Support international number formats in statistics display.
* Updated: Description of WordPress.org plugin link in plugin list.
* Updated: Widget and shortcode now use the countonly option in wp_statistics_vistor() for better performance.
* Updated: Renamed plugin from "WordPress Statistics" to "WP-Statistics".
* Fixed: bug in new IP validation code and support for stripping off port numbers if they are passed through the headers.  Thanks Stephanos Io.
* Updated: Persian (fa_IR) language.

= 6.0 =
* Release Date: June 11, 2014
* Added: Page tracking support.  Includes new overview widget and detail page.  Also supports page hit count in the pages/post list and in the page/post editor.
* Added: Admin manual, online viewing as well as downloadable version.
* Added: Links for “Settings”, “WordPress Plugin Page” and “Rate” pages to the plugin list for WP-Statistics.
* Updated: General settings tab re-organization.
* Updated: Several typo's and other minor issues.
* Updated: Highcharts JS v3.0.9 to JS v4.0.1.
* Updated: Persian (fa_IR) language.
* Updated: Polish (pl_PL) language.
* Updated: Arabic (ar) language.
* Updated: Turkish (tr_TR) language.
* Removed: shortcode and functions reference from readme.txt, now in admin manual.

= 5.4 =
* Release Date: May 31, 2014
* Fixed: GeoIP dependency code to ignore safe mode check in PHP 5.4 or newer.
* Fixed: GeoIP dependency code to properly detect safe mode with PHP 5.3 or older.
* Fixed: Browser information not recorded if GeoIP was not enabled.
* Updated: get_IP code to better handle malformed IP addresses.
* Updated: Persian (fa_IR) language.
* Updated: Arabic (ar) language.
* Updated: Chinese (zh_CN) language.

= 5.3 =
* Release Date: April 17, 2014
* Added: New robot's to the robots list: BOT for JCE, Leikibot, LoadTimeBot, NerdyBot, niki-bot, PagesInventory, sees.co, SurveyBot, trendictionbot, Twitterbot, Wotbox, ZemlyaCrawl
* Added: Check for PHP's Safe Mode as the GeoIP code does not function with it enabled.
* Added: Option to disable administrative notices of inactive features.
* Added: Option to export column names as first line of export files.
* Added: Options to disable search engines from being collected/displayed.
* Updated: French (fr_FR) language translation.
* Fixed: Download of the GeoIP database could cause a fatal error message at the end of a page if it was triggered outside the admin area.

= 5.2 =
* Release Date: March 10, 2014
* Added: Additional checks for BC Math and cURL which are required for the GeoIP code.
* Updated: GeoIP database handling if it is missing or invalid.
* Updated: GeoIP database is now stored in uploads/wp-statistics directory so it does not get overwritten during upgrades.
* Fixed: Typo's in the shortcode codes (thanks 	g33kg0dd3ss).
* Updated: Polish (pl_PL) language.

= 5.1 =
* Release Date: March 3, 2014
* Fixes: Small bug in referral url.
* Fixes: Problem export table.
* Updated: Arabic (ar) language.

= 5.0 =
* Release Date: March 2, 2014
* Added: Show last visitor in Google Map.
* Added: Search visitor by IP in log pages.
* Added: Total line to charts with multiple values, like the search engine referrals.
* Added: Shortcodes. [By Greg Ross](http://profiles.wordpress.org/gregross)
* Added: Dashicons to log pages.
* Fixes: Small bugs.
* Fixes: More debug warnings.
* Fixes: User access function level code always returned manage_options no matter what it was actaully set to.
* Updated: Hungarian (hu_HU) language.
* Updated: Turkish (tr_TR) language.
* Removed: Parameter from `wp_statistics_lastpostdate()` function and return date type became dynamic.

= 4.8.1 =
* Release Date: February 4, 2014
* Fixes: Small bug in the `Current_Date`.
* Fixes: Small bug in the `exclusions.php` file.
* Updated: Polish (pl_PL) language.

= 4.8 =
* Release Date: February 4, 2014
* Added: Converting Gregorian date to Persian When enabled [wp-parsidate](http://wordpress.org/plugins/wp-parsidate/) plugin.
* Added: New feature, option to record the number and type of excluded hits to your site.
* Added: New exclusion types for login and admin pages.
* Fixes: GeoIP populate code now REALLY functions again.
* Updated: Arabic (ar) language.
* Updated: Polish (pl_PL) language.

= 4.7 =
* Release Date: February 2, 2014
* Added: Responsive Stats page for smaller-screen devices.
* Added: Dashicons icon for plugin page.
* Added: Tabs option in setting page.
* Added: Tabs category in optimization page.
* Fixes: More debug warnings.
* Fixes: GeoIP populate code now functions again.
* Updated: Some optimization of the statistics code.
* Updated: Search Words now reports results only for referrers with actual search queries.
* Updated: Highcharts JS v3.0.7 to JS v3.0.9.
* Updated: Brazil (pt_BR) language.

= 4.6.1 =
* Release Date: January 24, 2014
* Fixes: a Small bug in to get rid of one of the reported warnings from debug mode.

= 4.6 =
* Release Date: January 20, 2014
* Added: In the optimization page you can now empty all tables at once.
* Added: In the optimization page you can now purge statistics over a given number of days old.
* Added: Daily scheduled job to purge statistics over a given number of days old.
* Fixes: Bug in the robots code that on new installs failed to populate the defaults in the database.
* Fixes: All known warning messages when running in WordPress debug mode.
* Fixes: Incorrect description of co-efficient value in the setting page.
* Fixes: Top level links on the various stats pages now update highlight the current page in the admin menu instead of the overview page.
* Fixes: Install code now only executes on a true new installation instead of on each activation.
* Fixes: Bug in hits code when GeoIP was disabled, IP address would not be recorded.

= 4.5 =
* Release Date: January 18, 2014
* Added: Support for more search engines: DuckDuckGo, Baidu and Yandex.
* Added: Support for Google local sites like google.ca, google.fr, etc.
* Added: Anchor links in the optimization and settings page to the main sections.
* Added: Icon for Opera Next.
* Updated: Added new bot match strings: 'archive.org_bot', 'meanpathbot', 'moreover', 'spbot'.
* Updated: Replaced bot match string 'ezooms.bot' with 'ezooms'.
* Updated: Overview summary statistics layout.
* Fixes: Bug in widget code that didn't allow you to edit the settings after adding the widget to your site.

= 4.4 =
* Release Date: January 16, 2014
* Added: option to set the required capability level to view statistics in the admin interface.
* Added: option to set the required capability level to manage statistics in the admin interface.
* Fixes: 'See More' links on the overview page now update highlight the current page in the admin menu instead of the overview page.
* Added: Schedule downloads of the GeoIP database.
* Added: Auto populate missing GeoIP information after a download of the GeoIP database.
* Fixes: Unschedule of report event if reporting is disabled.

= 4.3.1 =
* Release Date: January 13, 2014
* Fixes: Critical bug that caused only a single visitor to be recorded.
* Added: Version information to the optimization page.
[Thanks Greg Ross](http://profiles.wordpress.org/gregross)

= 4.3 =
* Release Date: January 12, 2014
* Added: Definable robots list to exclude based upon the user agent string from the client.
* Added: IP address and subnet exclusion support.
* Added: Client IP and user agent information to the optimization page.
* Added: Support to exclude users from data collection based on their WordPress role.
* Fixes: A bug when the GeoIP code was disabled with optimization page.

= 4.2 =
* Release Date: December 31, 2013
* Added: Statistical menus.
* Fixes: Small bug in the geoip version.
* Language: Serbian (sr_RS) was updated.
* Language: German (de_DE) was updated.
* Language: French (fr_FR) was updated.

= 4.1 =
* Release Date: December 23, 2013
* Language: Arabic (ar) was updated
* Fixes: small bug in moved the GeoIP database.
* Updated: update to the spiders list.

= 4.0 =
* Release Date: December 21, 2013
* Added: GeoIP location support for visitors country.
* Added: Download option in settings for GeoIP database.
* Added: Populate location entries with unknown or missing location information to the optimization page.
* Added: Detect self referrals and disregard them like webcrawlers.
* Added: "All Browsers" and "Top Countries" pages.
* Added: "more" page to hit statistics chart, support for charts from 10 days to 1 year.
* Added: "more" page to search engine statistics chart, support for charts from 10 days to 1 year.
* Added: Option to store complete user agent string for debugging purposes.
* Added: Option to delete specific browser or platform types from the database in the optimization page.
* Updated: Browser detection now supports more browsers and includes platform and version information.
* Updated: List of webcrawlers to catch more bots.
* Updated: Statistics reporting options in settings no longer needs a page reload to hide/show the settings.
* Updated: Summary Statistcs now uses the WordPress set format for the time and date.
* Fixes: Webcrawler detection now works and is case insensitive.
* Fixes: Install code now correctly sets defaults.
* Fixes: Upgrade code now works correctly.  If you are running V3.2, your old data will be preserved, older versions will delete the tables and recreate them.
* Fixes: Ajax submissions on the optmiziation page (like the empty table function) should work in IE and other browsers that are sensitive to cross site attacks.
* Fixes: Replaced call to the dashboard code (to support the postbox widgets on the log screen) with the proper call to the postbox code as WordPress 3.8 beta 1 did not work with the old code.
* Updated: Highcharts JS 3.0.1 to JS 3.0.7 version.

= 3.2 =
* Release Date: August 7, 2013
* Added: Optimization plugin page.
* Added: Export data to excel, xml, csv and tsv files.
* Added: Delete table data.
* Added: Show memory usage in optimization page.
* Language: Polish (pl_PL) was updated.
* Language: updated.

= 3.1.4 =
* Release Date: July 18, 2013
* Added: Chart Type in the settings plugin.
* Added: Search Engine referrer chart in the view stats page.
* Added: Search Engine stats in Summary Statistics.
* Optimized: 'wp_statistics_searchengine()' and add second parameter in the function.
* Language: Chinese (China) was added.
* Language: Russian was updated.
* Language: updated.

= 3.1.3 =
* Release Date: June 9, 2013
* Optimized: View statistics.
* Added: Chinese (Taiwan) language.

= 3.1.2 =
* Release Date: June 4, 2013
* Added: Top referring sites with full details.
* Resolved: Loads the plugin's translated strings problem.
* Resolved: View the main site in top referring sites.
* Resolved: Empty referrer.
* Resolved: Empty search words.
* Update: Highcharts js 2.3.5 to v3.0.1.
* Language: Arabic was updated.
* Language: Hungarian was updated.
* Language: updated.

= 3.1.1 =
* Release Date: April 11, 2013
* Bug Fix: Security problem. (Thanks Mohammad Teimori) for report bug.
* Optimized: Statistics screen in resolution 1024x768.
* Language: Persian was updated.

= 3.1.0 =
* Release Date: April 3, 2013
* Bug Fix: Statistics Menu bar.
* Bug Fix: Referral link of the last visitors.
* Added: Latest all search words with full details.
* Added: Recent all visitors with full details.
* Optimized: View statistics.
* Language: updated.
* Language: Arabic was updated.
* Remove: IP Information in setting page.

= 3.0.2 =
* Release Date: February 5, 2013
* Added: Hungarian language.
* Added: Insert value in useronline table by Primary_Values function.
* Added: Opera browser in get_UserAgent function.
* Added: prefix wps_ in options.
* Added: Notices to enable or disable the plugin.
* Changed: Statistics class to WP_Statistics because Resemblance name.

= 3.0.1 =
* Release Date: February 3, 2013
* Bug Fix: Table plugin problem.

= 3.0 =
* Release Date: February 3, 2013
* Bug Fix: problem in calculating Statistics.
* Optimized: and speed up the process.
* Optimized: Overall reconstruction and coding plug with a new structure.
* Optimized: The use of object-oriented programming.
* Added: statistics screen to complete.
* Added: Chart Show.
* Added: Graph of Browsers.
* Added: Latest search words.
* Added: Specification (Country and county) Visitors.
* Added: Top referring sites.
* Added: Send stats to Email/[SMS](http://wordpress.org/extend/plugins/wp-sms/)

= 2.3.3 =
* Release Date: December 18, 2012
* Serbian language was solved.
* Server variables were optimized by m.emami.
* Turkish translation was complete.

= 2.3.2 =
* Release Date: October 24, 2012
* Added Indonesia language.
* Turkish language file corrected by MBOZ.

= 2.3.1 =
* Release Date: October 12, 2012
* Added Polish language.
* Added Support forum link in menu.
* Fix problem error in delete plugin.

= 2.3.0 =
* Release Date: Not released
* Added Serbian language.

= 2.2.9 =
* Release Date: September 20, 2012
* Added Bengali language.

= 2.2.8 =
* Release Date: July 27, 2012
* Added Russian language.
* Fix problem in count views.
* Added more filter for check spider.
* Optimize plugin.

= 2.2.7 =
* Release Date: May 20, 2012
* Fix problem in widget class.
* Redundancy in Arabic translation.
* Fix problem in [countposts] shortcode.
* Optimized Style Reports.

= 2.2.6 =
* Release Date: April 19, 2012
* Fix a small problem.

= 2.2.5 =
* Release Date: April 18, 2012
* The security problem was solved. Please be sure to update!
* Redundancy in French translation.
* Add CSS Class for the containing widget. (Thanks Luai Mohammed).
* Add daily or total search engines in setting page.
* Using wordpress jQuery in setting page.

= 2.2.4 =
* Release Date: March 12, 2012
* Added Turkish language.
* Added Italian language.
* Added German language.
* Arabic language was solved.
* Romanian language was solved.
* The words in setting page were complete. (Thanks Will Abbott) default.po file is Updated.
* The change of time from minutes to seconds to check users online.
* Ignoring search engine crawler.
* Added features premium version to free version.
* Added user online live.
* Added total visit live.
* Added Increased to visit.
* Added Reduced to visit.
* Added Coefficient statistics for each user.

= 2.2.3 =
* Release Date: February 3, 2012
* Optimized Counting.
* Added Arabic language.
* Draging problem was solved in Widgets
* css problem was solved in sidebar

= 2.2.2 =
* Release Date: January 11, 2012
* Solving show functions in setting page.
* Solving month visit in widget.
* Added Spanish language.

= 2.2.1 =
* Release Date: December 27, 2011
* Solving drap uploader problem in media-new.php.

= 2.2.0 =
* Release Date: December 26, 2011
* Added statistics to admin bar wordpress 3.3.
* Added Uninstall for remove data and table from database.
* Added all statistics item in widget and Their choice.
* Optimize show function code in setting page.
* Calling jQuery in wordpress admin for plugin.
* Remove the word "disabled" in the statistics When the plugin was disabled.
* Solving scroll problem in statistics page.

= 2.1.6 =
* Release Date: October 21, 2011
* Added Russian language.

= 2.1.5 =
* Release Date: October 29, 2011
* Added French language.
* Rounds a float Averages.

= 2.1.4 =
* Release Date: October 21, 2011
* Added Romanian language.

= 2.1.3 =
* Release Date: October 14, 2011
* Active plugin in setting page was solved.

= 2.1.2 =
* Release Date: October 12, 2011
* Added default language file.
* Added Portuguese language.

= 2.1.1 =
* Release Date: September 27, 2011
* Complete files

= 2.1 =
* Release Date: September 25, 2011
* Edit string

= 2.0 =
* Release Date: September 20, 2011
* Support from Database
* Added Setting Page
* Added decimals number
* Added Online user check time
* Added Database check time
* Added User Online
* Added Today Visit
* Added Yesterday Visit
* Added Week Visit
* Added Month Visit
* Added Years Visit
* Added Search Engine reffered
* Added Average Posts
* Added Average Comments
* Added Average Users
* Added Google Pagerank
* Added Alexa Pagerank
* Added wordpress shortcode

= 1.0 =
* Release Date: March 20, 2011
* Start plugin
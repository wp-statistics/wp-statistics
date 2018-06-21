=== WP Statistics ===
Contributors: mostafa.s1990, GregRoss, dedidata
Donate link: https://wp-statistics.com/donate/
Tags: analytics, wordpress analytics, stats, statistics, visit, visitors, hits, chart, browser, today, yesterday, week, month, year, total, post, page, sidebar, google, live visit, search word, agent, google analytics, webmasters, google webmasters, geoip, location
Requires at least: 3.0
Tested up to: 4.9
Stable tag: 12.3.6.1
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Complete WordPress Analytics and Statistics for your site!

== Description ==
A comprehensive plugin for your WordPress visitor statistics, come visit us at our [website](https://wp-statistics.com) for all the latest news and information.

Track Statistics for your WordPress site without depending on external services and uses arrogate data whenever possible to respect your users privacy.

On screen Statistics presented as graphs are easily viewed through the WordPress admin interface.

This product includes GeoLite2 data created by MaxMind, available from https://www.maxmind.com.

= Features =
* Online users, visits, visitors and page statistics
* Search Engines, see search queries and redirects from popular search engines like Google, Bing, DuckDuckGo, Yahoo, Yandex and Baidu
* Overview and detail pages for all kinds of data, including; browser versions, country stats, hits, exclusions, referrers, searches, search words and visitors
* GeoIP location by Country
* Support for hashing IP addresses in the database to protect your users privacy
* Interactive map of visitors location
* E-mail reports of statistics
* Set access level for view and manage roles based on WordPress roles
* Exclude users from statistics collection based on various criteria, including; user roles, common robots, IP subnets, page URL, login page, RSS pages, admin pages, Country, number of visits per day, hostname
* Record statistics on exclusions
* Automatic updates to the GeoIP database
* Automatically prune the databases of old data
* Export the data to XML, CSV or TSV files
* Widget to provide information to your users
* Shortcodes for many different types of data in both widgets and posts/pages
* Dashboard widgets for the admin area
* Comprehensive Admin Manual

= Translations =
WP Statistics has been translated in to many languages, for the current list and contributors, please visit the [translate page](https://translate.wordpress.org/projects/wp-plugins/wp-statistics).

Translations are done by people just like you, help make WP Statistics available to more people around the world and [do a translation](https://wp-statistics.com/translations/) today!

= Contributing and Reporting Bugs =
WP-Statistics is being developed on GitHub, If you’re interested in contributing to plugin, Please look at [Github page](https://github.com/wp-statistics/wp-statistics)

= Support =
We're sorry you're having problem with WP Statistics and we're happy to help out. Here are a few things to do before contacting us:

* Have you read the [FAQs](https://wordpress.org/plugins/wp-statistics/faq/)?
* Have you read the [documentation](http://wp-statistics.com/category/documentation)?
* Have you search the [support forum](https://wordpress.org/support/plugin/wp-statistics) for a similar issue?
* Have you search the Internet for any error messages you are receiving?
* Make sure you have access to your PHP error logs.

And a few things to double-check:

* How's your memory_limit in php.ini?
* Have you tried disabling any other plugins you may have installed?
* Have you tried using the default WordPress theme?
* Have you double checked the plugin settings?
* Do you have all the required PHP extensions installed?
* Are you getting a blank or incomplete page displayed in your browser?  Did you view the source for the page and check for any fatal errors?
* Have you checked your PHP and web server error logs?

Still not having any luck? Open a new thread on one of the support forums and we'll respond as soon as possible.

* [English Support Forum](https://wordpress.org/support/plugin/wp-statistics)

== Installation ==
1. Upload `wp-statistics` to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Make sure the Date and Time is set correctly in WordPress.
4. Go to the plugin settings page and configure as required (note this will also download the GeoIP database for the fist time).

== Frequently Asked Questions ==
= What do I do if the plugin does not work? =
Disable then enable the plugin.  You may also want to try removing and re-installing it as well.  If it is still not working, please open a new support thread on the [WordPress support forums](https://wordpress.org/support/plugin/wp-statistics).

= All visitors are being set to unknown for their location? =
Make sure you've downloaded the GeoIP database and the GeoIP code is enabled.

Also, if your running an internal test site with non-routable IP addresses (like 192.168.x.x or 172.28.x.x or 10.x.x.x), these addresses will come up as unknown always unless you have defined a location in the "Country code for private IP addresses" setting.

= GeoIP is enabled but no hits are being counted? =
The GeoIP code requires several things to function, PHP 5.3 or above, the cURL extension and PHP cannot be running in safe mode.  All of these conditions are checked for but there may be additional items required.  Check your PHP log files and see if there are any fatal errors listed.

= How much memory does PHP Statistics require? =
This depends on how many hits your site gets.  The data collection code is very light weight, however the reporting and statistics code can take a lot of memory to process.  The longer you collect data for the more memory you will need to process it.  At a bare minimum, a basic WordPress site with WP Statistics should have at least 32 meg of RAM available for a page load.  Sites with lots of plugins and high traffic should look at significantly increasing that (128 to 256 meg is not unreasonable).

= I've enabled IP subnet exclusions and now no visitors are recorded? =
Be very careful to set the subnet mask correctly on the subnet list, it is very easy to catch too much traffic.  Likewise if you are excluding a single IP address make sure to include a subnet mask of 32 or 255.255.255.255 otherwise you may not get the expected results.

= I'm not receiving e-mail reports? =
Make sure you have WordPress configured correctly for SMTP and also check your WP Cron is working correctly.  You can use [Cron View](https://wordpress.org/plugins/cron-view) to examine your WP Cron table and see if there are any issues.

= Does WP Statistics support multi-site? =
WP Statistics doesn't officially support multi-site however it does have limited functionally associated with it and should function without issue.  However no support is provided at this time.

Version 8.8 is the first release that should install, upgrade and remove correctly on mutli-site as well as have some very basic support for the network admin menu.  This should not be taken as an indication that WP Statistics fully supports multi-site, but only as a very preliminary first step.

= Does WP Statistics track the time of the hits? =
No.

= I'm using another statistics plugin/service and get different numbers for them, why? =

Pretty much every plugin/service is going to give you different results for visits and visitors, there are several reasons for this:

* Web crawler detection
* Detection method (javascript vs server side PHP)
* Centralized exclusions

Services that use centralized databases, like Google Analytics, for spam and robot detection have better detection than WP Statistics can.  The trade off of course is relaying on an external service.

= The search words and search engine referrals are zero or very low, what's wrong? =

Search Engine Referrals and Words are highly dependent on the search engine providing the information to us and that often is not the case.  Unfortunately there is nothing we can do about this, we report on everything we receive.

= Why did my visits suddenly jump way up today? =

There can be many reasons for this, but the most common reason is a botnet has decided to visit your site and we have been unable to filter it out.  You usually see your visits spike for a few days and then they give up.

= What’s the difference between Visits and Visitors? =

Visits is the number of page hits your site has received.

Visitors is the number of unique users that have visited your site.

Visits should always be greater than Visitors (though there are a few times when this won’t be true on very low usage sites due to how the exclusion code works).

The average number of pages a visitor views on your site is Visits/Visitors.

= My overview screen is blank, what's wrong? =

This is usually caused by a PHP fatal error, check the page source and PHP logs.

The most common fatal error is an out of memory error. Check the Statistics->Optimization page and see how much memory is currently assigned to PHP.

If it is a memory issue you have two choices:
 - Increase PHP's memory allocation
 - Delete some of your historical data.

See https://php.net/manual/en/ini.core.php#ini.memory-limit for information about PHP's memory limit.

To remove historical data you can use the Statistics->Optimization->Purging->Purge records older than.

= Not all referrals are showing up in the search words list, why? =

Unfortunate we're completely dependent on the search engine sending use the search parameters as part of the referrer header, which they do not always do.

= Does WP Statistics work with caching plugins? =

Probably not, most caching plugins don't execute the standard WordPress loop for a page it has already cached (by design of course) which means the WP Statistics code never runs for that page.

This means WP Statistics can't record the page hit or visitor information, which defeats the purpose of WP Statistics.

We do not recommend using a caching plugin along with WP Statistics.

= PHP 7 Support =

WP Statistics is PHP 7 compliant, however some versions of PHP 7 have bugs that can cause issues. One know issue is with PHP 7.0.4 causing memory exhaustion errors, newer versions of PHP 7 do not have this issue.

At this time (Jun 2018) WP Statistics seems to run fine with PHP 7.2.6, however you may experience issues that we haven't found yet. If you do, feel free to report it after you've confirmed it is not a problem with PHP.

= IPv6 Support =

WP Statistics supports IPv6 as of version 11.0, however PHP must be compiled with IPv6 support enabled, otherwise you may see warnings when a visitor from an IPv6 address hits your site.

You can check if IPv6 support is enabled in PHP by visiting the "Optimization->Resources/Information->Version Info->PHP IPv6 Enabled" section.

If IPv6 is not enabled, you may see an warning like:

	Warning: inet_pton() [function.inet-pton]: Unrecognized address 2003:0006:1507:5d71:6114:d8bd:80c2:1090

= GDPR Support =

The greatest advantage of WP Statistics is, that all the data is saved locally in WordPress.

This helps a lot while implementing the new GDPR restrictions because it’s not necessary to create a data processing contract with an external company!

Introduction of a popup with “Accept” and “Deny” before collection data and Hash IP addresses is a useful option on the WP-Statistics.

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

== Upgrade Notice ==
= 12.0.9 =
This is a security fix, please update immediately.

== Changelog ==
= 12.3.6.2 =
* Tested with PHP v7.2.4
* Added suggestion notice in the log pages.
* Added new option for enable/disable notices.

= 12.3.6.1 =
* Improvement i18n strings.
* Improvement GDPR, Supported for DNT-Header.
* Improvement GDPR, Added new option for delete visitor data with IP addresses.

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
* Fixed: Updated CSS definition for widgets to avoid overflow only for WP Statistics widgets instead of all active widgets to avoid conflicts with other plugins.

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

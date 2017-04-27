=== WP Statistics ===
Contributors: GregRoss, mostafa.s1990
Donate link: https://wp-statistics.com/donate/
Tags: statistics, stats, visit, visitors, chart, browser, blog, today, yesterday, week, month, year, total, post, page, sidebar, summary, hits, pagerank, google, alexa, live visit
Requires at least: 3.0
Tested up to: 4.7
Stable tag: 12.0.6
License: GPL3

Complete statistics for your WordPress site.

== Description ==
A comprehensive plugin for your WordPress visitor statistics, come visit us at our [website](https://wp-statistics.com) for all the latest news and information.

Track statistics for your WordPress site without depending on external services and uses arrogate data whenever possible to respect your users privacy.

On screen statistics presented as graphs are easily viewed through the WordPress admin interface.

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
WP Statistics has been translated in to many languages, for the current list and contributors, please visit the [translators](https://wp-statistics.com/translators/) page on [wp-statistics.com](https://wp-statistics.com/).

Translations are done by people just like you, help make WP Statistics available to more people around the world and [do a translation](https://wp-statistics.com/translations/) today!

= Support =
We're sorry you're having problem with WP Statistics and we're happy to help out.  Here are a few things to do before contacting us:

* Have you read the [FAQs](https://wordpress.org/plugins/wp-statistics/faq/)?
* Have you read the [manual](https://plugins.svn.wordpress.org/wp-statistics/trunk/manual/WP%20Statistics%20Admin%20Manual.html)?
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
* [Persian Support Forum](https://forum.wp-parsi.com/forum/17-%D9%85%D8%B4%DA%A9%D9%84%D8%A7%D8%AA-%D8%AF%DB%8C%DA%AF%D8%B1/)

== Installation ==
1. Upload `wp-statistics` to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Make sure the Date and Time is set correctly in WordPress.
4. Go to the plugin settings page and configure as required (note this will also download the GeoIP database for the fist time).

== Frequently Asked Questions ==
= Where's the Admin Manual? =
The admin manual is installed as part of the plugin, simply go to Statistics->Manual to view it.  At the top of the page will also be two icons that will allow you to download it in either ODT or HTML formats.

= What do I do if the plug does not work? =
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

= The GeoIP database isn't downloading and when I force a download through the settings page I get the following error: "Error downloading GeoIP database from: https://geolite.maxmind.com/download/geoip/database/GeoLite2-Country.mmdb.gz - Forbidden" =
This means that MaxMind has block the IP address of your webserver, this is often the case if it has been blacklisted in the past due to abuse.

You have two options:
- Contact MaxMind and have them unblock your IP address
- Manually download the database

To manually download the database and install it take the following steps:

- On another system (any PC will do) download the maxmind database from https://geolite.maxmind.com/download/geoip/database/GeoLite2-Country.mmdb.gz
- Decompress the database
- Connect to your web host and create a "wp-statistics" directory in your wordpress uploads folder (usually it is located in wp-content, so you would create a directory "wp-content/uploads/wp-statistics").
- Upload the GeoLite-Country.mmdb file to the folder you just created.

You can also ask MaxMind to unblock your host.  Note that automatic updates will not function until you can successfully download the database from your web server.

= I've activated the plugin but the menus don't show up and nothing happens? =

WP Statistics requires PHP 5.4, if it has detected an older version of PHP installed it will active cleanly in WordPress but disable all functionality, you will have to upgrade to PHP 5.4 or above for it to function.  WP Statistics will display an error at the top of your plugin list to let you know.

If there is no error message there may be something else wrong, your first thing to try is disabling your other plugins as they can sometimes cause conflicts.

If you still don't see the menus, go to the support forums and open a new thread and we'll try to help out.

= I'm using another statistics plugin/service and get different numbers for them, why? =

Pretty much every plugin/service is going to give you different results for visits and visitors, there are several reasons for this:

* Web crawler detection
* Detection method (javascript vs server side PHP)
* Centralized exclusions

Services that use centralized databases, like Google Analytics, for spam and robot detection have better detection than WP Statistics can.  The trade off of course is relaying on an external service.

= When I upgrade or install WP Statistics I get an error message like "Parse error: syntax error, unexpected T_STRING, expecting T_CONSTANT_ENCAPSED_STRING or '('" =

Since WP Statistics 8.0, PHP 5.3 or above has been required.  If you are using an older version of PHP it cannot understand the new syntax included in WP Statistics 8.0 and generates a parse error.

Your hosting provider should have a newer version of PHP available, sometimes you must activate it through your hosting control panel.

Since the last release of PHP 5.2 is over 5 years ago (Jan 2011) and is no longer supported or receiving security fixes, if your provider does not support a newer version you should probably be moving hosting providers.

If you have done an upgrade and you can no longer access your site due to the parse error you will have to manually delete the wp-statistics directory from your wordpress/wp-content/plugins directory, either through your hosting providers control panel or FTP.

Do not use older versions of WP Statistics as they have know security issues and will leave your site vulnerable to attack.

= I've decided to stay with WP Statistics 7.4 even though its a bad idea but now WordPress continuously reports there are updates available, how can I stop that? =

Don't, upgrade immediately to the latest version of WP Statistics.

= Something has gone horribly wrong and my site no longer loads, how can I disable the plugin without access to the admin area? =

You can manually disable plugins in WordPress by simply renaming the folder they are installed in.  Using FTP or your hosting providers file manager, go to your WordPress directory, from there go to wp-content/plugins and rename or delete the wp-statistics folder.

= I'm getting an error in my PHP log like: Fatal error: Call to undefined method Composer\Autoload\ClassLoader::set() =

We use several libraries and use a utility called Composer to manage the dependencies between them.  We try and keep our Composer library up to date but not all plugins do and sometimes we find conflicts with other plugins.  Try disabling your other plugins until the error goes away and then contact that plugin developer to update their Composer files.

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

= I get an error message like "PHP Fatal error: Function name must be a string in /../parse-user-agent.php" =

Do you have eAccelerator installed?  If so this is a known issue with eAccelerator and PHP's "anonymous" functions, which are used in the user agent parsing library.  As no new versions of eAccelerator have been released for over 6 years (since January 2010), you should look to replace it or disable it.

= I've installed WP Statistics for the first time on a site and when I go to the statistics pages I get an error saying like "The following plugin table(s) do not exist in the database" =

This is because something has gone wrong during the installation.

At the end of the message will be a list of tables that are missing, you can use the provided link to re-run the installation routine.  If that does not resolve the issue and the visitors table is the only table listed, you may want to check your MySQL version.  Some older versions of MySQL (in the 5.0.x series) have issues with complex compound indexes, which we use on the visitors table.  If this is the case, check with your hosting provider and see if they can upgrade to a newer version of MySQL.

If you still have issues open a new thread on the support forum and we'll try and resolve it for you.

= I've changed the permissions for WP Statistics access and now I've lost access to it myself, how to I fix it? =

If you have access to phpMyAdmin (or similar tool) you can query the wp_options table:

	SELECT * FROM wp_options WHERE option_name = 'wp_statistics';

Then edit the value, inside the string will be something like (note: "edit_plugins" will be whatever permission you selected):

	s:15:"read_capability";s:12:"edit_plugins";s:17:"manage_capability";s:12:"edit_plugins";

Replace it with:

	s:15:"read_capability";s:14:"manage_options";s:17:"manage_capability";s:14:"manage_options";

= I see error messages in my PHP log like "WordPress database error Duplicate entry 'YYYY-MM-DD' for key 'unique_date' for ..." =

This is caused by a race condition in the code, it's safe to ignore (it shouldn't be labeled as an error really, but that is part of WordPress that we can't control).

It happens when a new day starts and two visitors hit the site at nearly the same time for the first visit of the day. Both try and create a new row in the table to track the days visits, but only one of them success and the other throws this warning.

= PHP 7 Support =

WP Statistics is PHP 7 compliant, however some versions of PHP 7 have bugs that can cause issues.  One know issue is with PHP 7.0.4 causing memory exhaustion errors, newer versions of PHP 7 do not have this issue.

At this time (August 2016) WP Statistics seems to run fine with PHP 7.0.10, however you may experience issues that we haven't found yet.  If you do, feel free to report it after you've confirmed it is not a problem with PHP.

= PHP 7.1 Support =

WP Statistics has not yet been tested on PHP 7.1 and reports so far (As of February 2017) indicate there are issues.  As PHP 7.1 is still relatively new it is not recommended at this time.

= IPv6 Support =

WP Statistics supports IPv6 as of version 11.0, however PHP must be compiled with IPv6 support enabled, otherwise you may see warnings when a visitor from an IPv6 address hits your site.

You can check if IPv6 support is enabled in PHP by visiting the "Optimization->Resources/Information->Version Info->PHP IPv6 Enabled" section.

If IPv6 is not enabled, you may see an warning like:

	Warning: inet_pton() [function.inet-pton]: Unrecognized address 2003:0006:1507:5d71:6114:d8bd:80c2:1090

= When I upgrade or install WP Statistics 11.0 I get an error message like "Parse error: syntax error, unexpected T_USE, expecting T_FUNCTION in..." =

Since WP Statistics 11.0, PHP 5.4 or above has been required.  If you are using an older version of PHP it cannot understand the new syntax included in WP Statistics 11.0 and generates a parse error.

Your hosting provider should have a newer version of PHP available, sometimes you must activate it through your hosting control panel.

Since the last release of PHP 5.3 is over 2 years ago (Aug 2014) and is no longer supported or receiving security fixes, if your provider does not support a newer version you should probably be moving hosting providers.

If you have done an upgrade and you can no longer access your site due to the parse error you will have to manually delete the wp-statistics directory from your wordpress/wp-content/plugins directory, either through your hosting providers control panel or FTP.

You may also downgrade to WP Statistics 10.3 as a temporary measure, but no new fixes or features will be added to that version and you should move to a newer version of PHP as soon as possible.  You can download the 10.3 here: https://downloads.wordpress.org/plugin/wp-statistics.10.3.zip

== Screenshots ==
1. View stats page.
2. View latest search words.
3. View recent visitors page.
4. View top referrer site page.
5. Optimization page.
6. Settings page.
7. Widget page.
8. View Top Browsers page.
9. View latest Hits Statistics page.
10. View latest search engine referrers Statistics page.

== Upgrade Notice ==
= 12.0.5 =
This is a security fix, please update immediately.

== Changelog ==
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
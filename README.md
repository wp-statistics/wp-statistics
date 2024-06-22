[![Average time to resolve an issue](https://isitmaintained.com/badge/resolution/wp-statistics/wp-statistics.svg)](http://isitmaintained.com/project/wp-statistics/wp-statistics "Average time to resolve an issue")
[![Percentage of issues still open](https://isitmaintained.com/badge/open/wp-statistics/wp-statistics.svg)](http://isitmaintained.com/project/wp-statistics/wp-statistics "Percentage of issues still open")
[![WP compatibility](https://plugintests.com/plugins/wporg/wp-statistics/wp-badge.svg)](https://plugintests.com/plugins/wporg/wp-statistics/latest)
[![PHP compatibility](https://plugintests.com/plugins/wporg/wp-statistics/php-badge.svg)](https://plugintests.com/plugins/wporg/wp-statistics/latest)

# WP Statistics - The Most Popular Privacy-Friendly Analytics Plugin

## Requirements
- Requires at least: 5.0
- Tested up to: 6.5
- Stable tag: 14.8
- Requires PHP: 5.6

## License
- License: GPL-2.0+
- License URI: https://www.gnu.org/licenses/gpl-2.0.html

## Description
### WP Statistics: THE #1 WORDPRESS ANALYTICS PLUGIN
Discover GDPR-compliant analytics with [WP Statistics](https://wp-statistics.com/?utm_source=wporg&utm_medium=link&utm_campaign=website), the top choice for WordPress users seeking an alternative to Google Analytics. No external accounts, unlimited visitor tracking, and full data ownership—all stored directly in your WordPress database.

[Checkout Demo](https://wp-statistics.com/demo) | [View Screenshots](https://wordpress.org/plugins/wp-statistics#screenshots)

### GDPR Compliant (Data Privacy)
WP Statistics is GDPR, CCPA, PECR, and cookie compliance by default.

- We do not use cookies
- We do not store personally identifiable information (PII) by default
- 100% data ownership. Data is entirely created and stored on your server
- Enhance IP Hashing with Random Daily Salt Mechanism
- Features to export and delete data for GDPR
- Respect for User Privacy with Do Not Track (DNT)
- Privacy Audit Tool for compliance with privacy laws

Your site won't need to have a cookie popup since WP Statistics uses [cookie-less tracking](https://wp-statistics.com/resources/counting-unique-visitors-without-cookies/?utm_source=wporg&utm_medium=link&utm_campaign=doc).

For more information, see "[What we collect](https://wp-statistics.com/resources/what-we-collect/?utm_source=wporg&utm_medium=link&utm_campaign=doc)".

### Top Features
- Fully integrate with your WordPress and your content structure, with all reports in your WP dashboard
- Simple analytics dashboard
- Super easy to install. No coding or technical knowledge needed
- Advanced data privacy settings customizable to fit your needs, in compliance with diverse data protection laws
- Track URL parameters, including UTMs, for campaign analysis
- Manage large amounts of data on high-traffic websites with configurable settings
- Monitor live online user traffic in real-time
- Fully customized overview dashboard page
- Shows your most popular posts and pages
- Lists your top referral sources such as search engines
- Author Analytics: Measures author performance
- Geographic Reports: Location-based analytics, including countries, cities, European countries, US states, and regions within your country
- Devices Report: Detailed device-specific analytics covering browsers, operating systems, and device models
- Bypass Ad Blockers: Dynamically load the tracking script with a unique name and address to bypass ad blockers
- Integrate with WP Consent API: Ensures compatibility with consent plugins like Complianz and Cookiebot
- Coming Soon: Content and Category Analytics: Track performance based on your site’s content and categories
- Email reports with customizable content
- Customize role-based access to view analytics and modify settings
- Advanced Filtering & Exceptions: By user roles, IPs, countries, URLs, and more
- Premium Add-on: [Data Plus](https://wp-statistics.com/product/wp-statistics-data-plus?utm_source=wporg&utm_medium=link&utm_campaign=dp)
 - **Custom Post Type Tracking**: DataPlus extends WP Statistics' tracking to include all custom post types in addition to Posts and Pages.
 - **Custom Taxonomy Analytics**: In addition to monitoring default taxonomies like Categories and Tags, DataPlus also tracks custom taxonomies.
 - **Link Tracker**: Find out which outbound links your audience clicks on, giving you insights into their preferences and behaviors.
 - **Download Tracker**: Keep track of what's being downloaded, who's downloading it, and when.
 - **Individual Author Performance**: Detailed metrics on the performance of individual authors.
 - **Soon**: Detailed Analytics for Each Country: In-depth analytics for each country to enhance geographical reporting.
 - And more!

**Get the most out of your website analytics by using WP Statistics Premium Add-ons**
Upgrade your analytics toolkit with our range of premium add-ons, including [Data Plus](https://wp-statistics.com/product/wp-statistics-data-plus?utm_source=wporg&utm_medium=link&utm_campaign=dp), [Advanced Reporting](https://wp-statistics.com/product/wp-statistics-advanced-reporting/?utm_source=wporg&utm_medium=link&utm_campaign=adv-report), [Real-Time Stats](https://wp-statistics.com/product/wp-statistics-realtime-stats/?utm_source=wporg&utm_medium=link&utm_campaign=real-time), [Mini Chart](https://wp-statistics.com/product/wp-statistics-mini-chart/?utm_source=wporg&utm_medium=link&utm_campaign=mini-chart), and [more](https://wp-statistics.com/add-ons/?utm_source=wporg&utm_medium=link&utm_campaign=add-ons). Making informed decisions is easier with these powerful tools.

**Special Offer:** Purchase the [bundle pack](https://wp-statistics.com/product/add-ons-bundle/?utm_source=wporg&utm_medium=link&utm_campaign=bundle) and Enjoy Savings of up to 60%!

## Report Bugs
Having trouble with a bug? Please [create an issue](https://github.com/wp-statistics/wp-statistics/issues/new) on GitHub. Kindly note that [GitHub](https://github.com/wp-statistics/wp-statistics) is exclusively for bug reports; other inquiries will be closed.

## Installation
1. Upload `wp-statistics` to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Make sure the Date and Time are set correctly in WordPress.
4. Go to the plugin settings page and configure as required (note this will also include downloading the GeoIP database for the first time).

## WP-CLI Commands

### Batch Insert Using Bash Script

You can use a bash script to insert multiple records with random data. Place the script in the `tools` directory:

```sh
bash tools/dummy.sh {quantity}
```

=== WP-Statistics ===
Contributors: mostafa.s1990
Donate link: http://iran98.org/donate/
Tags: statistics, stats, visit, visitors, chart, browser, blog, today, yesterday, week, month, yearl, total, post, page, sidebar, summary, feedburner, hits, pagerank, google, alexa, live visit
Requires at least: 3.0
Tested up to: 3.6
Stable tag: 3.1.4

Complete statistics for your blog.

== Description ==
A perfect plugin for your blog visitors statistics.
With this plugin, you can get hit full blog. Visitors and visits your blog from today to 1 year before you can get!
Many features have been added to the new version of the plugin and the previous problems have been resolved.
Statistics report on the screen can also view statistics with graphs.

Features:

* User Online
* Today visit/visitors
* Yesterday visit/visitors
* Week Visit/visitors
* Month Visit/visitors
* Years Visit/visitors
* Total Visit/visitors
* Search Engine reffered (Google, Yahoo, Bing)
* Coefficient statistics for each user
* Total Posts
* Total Pages
* Total Comments
* Total Spams [Need installed akismet plugin](http://automattic.com/wordpress-plugins/)
* Total Users
* Last Post Date (English, Persian)
* Average Posts
* Average Comments
* Average Users
* Visitor Browser View as chart
* View search words
* View Recent Visitors (Country and provincial visitor)
* Send scheduling statistics by email/SMS 
* Support functions and Widgets
* The object-oriented programming
* Standard functions for development

Language Support:

* English
* Persian
* Portuguese [Thanks](http://www.musicalmente.info/)
* Romanian [Thanks Luke Tyler](http://www.nobelcom.com/)
* French Thanks Anice Gnampa. Redundancy translated by Nicolas Baudet
* Russian [Thanks Igor Dubilej](http://www.iflexion.com/)
* Spanish Thanks Jose
* Arabic [Thanks Hammad Shammari](http://www.facebook.com/aboHatim)
* Turkish [Thanks aidinMC](http://www.artadl.ir/) & [Manset27.com](http://www.manset27.com/)
* Italian [Thanks Tony Bellardi](http://www.tonybellardi.com/)
* German [Thanks Andreas Martin](http://www.andreasmartin.com/)
* Russian [Thanks Oleg](http://www.bestplugins.ru/)
* Bengali [Thanks Mehdi Akram](http://www.shamokaldarpon.com/)
* Serbian [Thanks Radovan Georgijevic](http://www.georgijevic.info/)
* Polish Thanks Tomasz Stulka
* Indonesian [Thanks Agit Amrullah](http://www.facebook.com/agitowblinkerz/)
* Hungarian [Thanks ZSIMI](http://www.zsimi.hu/)
* Chinese (Taiwan) [Thanks Toine Cheung](https://twitter.com/ToineCheung)
* Chinese (China) [Thanks Toine Cheung](https://twitter.com/ToineCheung)

[Percentage languages ​​translation](http://teamwork.wp-parsi.com/projects/wp-statistics/)
To complete the language deficits of [this section](http://teamwork.wp-parsi.com/projects/wp-statistics/) apply.
Support Forum in [WordPress support forum Persian](http://forum.wp-parsi.com/forum/17-%D9%85%D8%B4%DA%A9%D9%84%D8%A7%D8%AA-%D8%AF%DB%8C%DA%AF%D8%B1/)
[Donate to this plugin](http://iran98.org/donate/)

[Plugin Facebook page](https://www.facebook.com/pages/Wordpress-Statistics/546922341997898?ref=stream)

== Installation ==
1. Upload `wp-statistics` to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Set Date and Time in Wordpress.
4. Go to the plugin settings and plugins enabled.
3. To display stats, using this functions:

* User online: `<?php echo wp_statistics_useronline(); ?>`
* Today visitor: `<?php echo wp_statistics_visitor('today'); ?>`
* Today visit: `<?php echo wp_statistics_visit('today'); ?>`
* Yesterday visitor: `<?php echo wp_statistics_visitor('yesterday'); ?>`
* Yesterday visit: `<?php echo wp_statistics_visit('yesterday'); ?>`
* Week visitor: `<?php echo wp_statistics_visitor('week'); ?>`
* Week visit: `<?php echo wp_statistics_visit('week'); ?>`
* Month visitor: `<?php echo wp_statistics_visitor('month'); ?>`
* Mount visit: `<?php echo wp_statistics_visit('month'); ?>`
* Years visitor: `<?php echo wp_statistics_visitor('year'); ?>`
* Years visit: `<?php echo wp_statistics_visit('year'); ?>`
* Total visitor: `<?php echo wp_statistics_visitor('total'); ?>`
* Total visit: `<?php echo wp_statistics_visit('total'); ?>`
* Number of visitors of 40 days to today: `<?php echo wp_statistics_visitor('-45'); ?>`
* Number of visits of 40 days to today: `<?php echo wp_statistics_visit('-45'); ?>`
* Number of visitors 45 days ago: `<?php echo wp_statistics_visitor('-45', true); ?>`
* Number of visits 45 days ago: `<?php echo wp_statistics_visit('-45', true); ?>`
* All Search Engine reffered `<?php echo wp_statistics_searchengine(); ?>`
* Google Search Engine reffered `<?php echo wp_statistics_searchengine('google'); ?>`
* Yahoo Search Engine reffered `<?php echo wp_statistics_searchengine('yahoo'); ?>`
* Bing Search Engine reffered `<?php echo wp_statistics_searchengine('bing'); ?>`
* Google Search Engine reffered in today  `<?php echo wp_statistics_searchengine('google', 'today'); ?>`
* Google Search Engine reffered in yesterday  `<?php echo wp_statistics_searchengine('google', 'yesterday'); ?>`
* Google Search Engine reffered in 5 days ago `<?php echo wp_statistics_searchengine('google', '-5'); ?>`
* Total All Search Enginee reffered `<?php echo wp_statistics_searchengine('all', 'total'); ?>`
* Total posts `<?php echo wp_statistics_countposts(); ?>`
* Total pages `<?php echo wp_statistics_countpages(); ?>`
* Total comments `<?php echo wp_statistics_countcomment(); ?>`
* Total spams `<?php echo wp_statistics_countspam(); ?>`
* Total users `<?php echo wp_statistics_countusers(); ?>`
* Last post date `<?php echo wp_statistics_lastpostdate(); ?>`
* Last post date (Persian) `<?php echo wp_statistics_lastpostdate('farsi'); ?>`
* Average posts `<?php echo wp_statistics_average_post(); ?>`
* Average comments `<?php echo wp_statistics_average_comment(); ?>`
* Average users `<?php echo wp_statistics_average_registeruser(); ?>`

== Frequently Asked Questions ==
= How to update to version 3.0? =
Get Plugin updates via Automatic only.

= If the plug does not work? =
Disable / Enable the plugin.

== Screenshots ==
1. Screen shot (screenshot-1.png) in view stats page.
1. Screen shot (screenshot-2.png) in view latest search words.
1. Screen shot (screenshot-3.png) in view recent visitors page.
1. Screen shot (screenshot-4.png) in view top referrer site page.
1. Screen shot (screenshot-5.png) in settings page.
1. Screen shot (screenshot-6.png) in widget page.

== Upgrade Notice ==
= 3.1.4 =
* Added: Chart Type in the settings plugin.
* Added: Search Engine referrer chart in the view stats page.
* Added: Search Engine stats in Summary Statistics.
* Optimized: 'wp_statistics_searchengine()' and add second parameter in the function.
* Language: Chinese (China) was added.
* Language: Russian was updated.
* Language: updated.

= 3.1.3 =
* Optimized: View statistics.
* Added: Chinese (Taiwan) language.

= 3.1.2 =
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
* Bug Fix: Security problem. (Thanks Mohammad Teimori) for report bug.
* Optimized: Statistics screen in resolution 1024x768.
* Language: Persian was updated.

= 3.1.0 =
* Bug Fix: Statistics Menu bar.
* Bug Fix: Referral link of the last visitors.
* Added: Latest all search words with full details.
* Added: Recent all visitors with full details.
* Optimized: View statistics.
* Language: updated.
* Language: Arabic was updated.
* Remove: IP Information in setting page.

= 3.0.2 =
* Added: Hungarian language.
* Added: Insert value in useronline table by Primary_Values function.
* Added: Opera browser in get_UserAgent function.
* Added: prefix wps_ in options.
* Added: Notices to enable or disable the plugin.
* Changed: Statistics class to WP_Statistics because Resemblance name.

= 3.0.1 =
* Bug Fix: Table plugin problem.

= 3.0 =
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
* Serbian language was solved.
* Server variables were optimized by m.emami.
* Turkish translation was complete.

= 2.3.2 =
* Added Indonesia language.
* Turkish language file corrected by MBOZ.

= 2.3.1 =
* Added Polish language.
* Added Support forum link in menu.
* Fix problem error in delete plugin.

= 2.3.0 =
* Added Serbian language.

= 2.2.9 =
* Added Bengali language.

= 2.2.8 =
* Added Russian language.
* Fix problem in count views.
* Added more filter for check spider.
* Optimize plugin.

= 2.2.7 =
* Fix problem in widget class.
* Redundancy in Arabic translation.
* Fix problem in [countposts] shortcode.
* Optimized Style Reports.

= 2.2.6 =
* Fix a small problem.

= 2.2.5 =
* The security problem was solved. Please be sure to update!
* Redundancy in French translation.
* Add CSS Class for the containing widget. (Thanks Luai Mohammed).
* Add daily or total search engines in setting page.
* Using wordpress jQuery in setting page.

= 2.2.4 =
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
* Optimized Counting.
* Added Arabic language.
* Draging problem was solved in Widgets
* css problem was solved in sidebar

= 2.2.2 =
* Solving show functions in setting page.
* Solving month visit in widget.
* Added Spanish language.

= 2.2.1 =
* Solving drap uploader problem in media-new.php.

= 2.2.0 =
* Added statistics to admin bar wordpress 3.3.
* Added Uninstall for remove data and table from database.
* Added all statistics item in widget and Their choice.
* Optimize show function code in setting page.
* Calling jQuery in wordpress admin for plugin.
* Remove the word "disabled" in the statistics When the plugin was disabled.
* Solving scroll problem in statistics page.

= 2.1.6 =
* Added Russian language.

= 2.1.5 =
* Added French language.
* Rounds a float Averages.

= 2.1.4 =
* Added Romanian language.

= 2.1.3 =
* Active plugin in setting page was solved.

= 2.1.2 =
* Added default language file.
* Added Portuguese language.

= 2.1.1 =
* Complete files

= 2.1 =
* Edit string

= 2.0 =
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
* Start plugin

== Changelog ==
= 3.1.4 =
* Added: Chart Type in the settings plugin.
* Added: Search Engine referrer chart in the view stats page.
* Added: Search Engine stats in Summary Statistics.
* Optimized: 'wp_statistics_searchengine()' and add second parameter in the function.
* Language: Chinese (China) was added.
* Language: Russian was updated.
* Language: updated.

= 3.1.3 =
* Optimized: View statistics.
* Added: Chinese (Taiwan) language.

= 3.1.2 =
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
* Bug Fix: Security problem. (Thanks Mohammad Teimori) for report bug.
* Optimized: Statistics screen in resolution 1024x768.
* Language: Persian was updated.

= 3.1.0 =
* Bug Fix: Statistics Menu bar.
* Bug Fix: Referral link of the last visitors.
* Added: Latest all search words with full details.
* Added: Recent all visitors with full details.
* Optimized: View statistics.
* Language: updated.
* Language: Arabic was updated.
* Remove: IP Information in setting page.

= 3.0.2 =
* Added: Hungarian language.
* Added: Insert value in useronline table by Primary_Values function.
* Added: Opera browser in get_UserAgent function.
* Added: prefix wps_ in options.
* Added: Notices to enable or disable the plugin.
* Changed: Statistics class to WP_Statistics because Resemblance name.

= 3.0.1 =
* Bug Fix: Table plugin problem.

= 3.0 =
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
* Serbian language was solved.
* Server variables were optimized by m.emami.
* Turkish translation was complete.

= 2.3.2 =
* Added Indonesia language.
* Turkish language file corrected by MBOZ.

= 2.3.1 =
* Added Polish language.
* Added Support forum link in menu.
* Fix problem error in delete plugin.

= 2.3.0 =
* Added Serbian language.

= 2.2.9 =
* Added Bengali language.

= 2.2.8 =
* Added Russian language.
* Fix problem in count views.
* Added more filter for check spider.
* Optimize plugin.

= 2.2.7 =
* Fix problem in widget class.
* Redundancy in Arabic translation.
* Fix problem in [countposts] shortcode.
* Optimized Style Reports.

= 2.2.6 =
* Fix a small problem.

= 2.2.5 =
* The security problem was solved. Please be sure to update!
* Redundancy in French translation.
* Add CSS Class for the containing widget. (Thanks Luai Mohammed).
* Add daily or total search engines in setting page.
* Using wordpress jQuery in setting page.

= 2.2.4 =
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
* Optimized Counting.
* Added Arabic language.
* Draging problem was solved in Widgets
* css problem was solved in sidebar

= 2.2.2 =
* Solving show functions in setting page.
* Solving month visit in widget.
* Added Spanish language.

= 2.2.1 =
* Solving drap uploader problem in media-new.php.

= 2.2.0 =
* Added statistics to admin bar wordpress 3.3.
* Added Uninstall for remove data and table from database.
* Added all statistics item in widget and Their choice.
* Optimize show function code in setting page.
* Calling jQuery in wordpress admin for plugin.
* Remove the word "disabled" in the statistics When the plugin was disabled.
* Solving scroll problem in statistics page.

= 2.1.6 =
* Added Russian language.

= 2.1.5 =
* Added French language.
* Rounds a float Averages.

= 2.1.4 =
* Added Romanian language.

= 2.1.3 =
* Active plugin in setting page was solved.

= 2.1.2 =
* Added default language file.
* Added Portuguese language.

= 2.1.1 =
* Complete files

= 2.1 =
* Edit string

= 2.0 =
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
* Start plugin
=== WP-Statistics ===
Contributors: mostafa.s1990
Donate link: http://iran98.org/
Tags: statistics, stats, blog, today, yesterday, week, month, yearl, total, post, page, sidebar, summary, feedburner, hits, pagerank, google, alexa, live visit
Requires at least: 3.0
Tested up to: 3.4.2
Stable tag: 2.3.0

Summary statistics of blog.

== Description ==
a plugin for displaying Summary statistics of blog.

Features:

* User Online
* Today Visit
* Yesterday visit
* Week Visit
* Month Visit
* Years Visit
* Total Visit
* Search Engine reffered (Google, Yahoo, Bing)
* User Online Live
* Total Visit Live
* Increased to visit.
* Reduced to visit.
* Coefficient statistics for each user.
* Total Posts
* Total Pages
* Total Comments
* Total Spams [Need installed akismet plugin](http://automattic.com/wordpress-plugins/)
* Total Users
* Last Post Date (English, Persian)
* Average Posts
* Average Comments
* Average Users
* Total Feedburner Subscribe
* Google Pagerank
* Alexa Pagerank
* Use optimize from Database
* Show Useronline and last visitor in setting page (ip, time, agent, reffered)
* Supported wordpress shortcode for view function in post/page or widget(text)

Language Support:

* English
* Persian
* Portuguese [Thanks](http://www.musicalmente.info/)
* Romanian [Thanks Luke Tyler](http://www.nobelcom.com/)
* French Thanks Anice Gnampa. Redundancy translated by Nicolas Baudet
* Russian [Thanks Igor Dubilej](http://www.iflexion.com/)
* Spanish Thanks Jose
* Arabic [Thanks Hammad Shammari](http://www.facebook.com/aboHatim)
* Turkish [Thanks aidinMC](http://www.artadl.ir/)
* Italian [Thanks Tony Bellardi](http://www.tonybellardi.com/)
* German [Thanks Andreas Martin](http://www.andreasmartin.com/)
* Russian [Thanks Oleg](http://www.bestplugins.ru/)
* Bengali [Thanks Mehdi Akram](http://www.shamokaldarpon.com/)
* Serbian [Thanks Radovan Georgijevic](http://www.georgijevic.info)

Send email for Translation files: mst404[a]gmail[dot].com
for translate, please open langs/default.po by Poedit and translate strings.

== Installation ==
1. Upload `wp-statistics` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. To display stats, using this functions:

* User Online: `<?php echo wp_statistics_useronline(); ?>`
* Today Visit: `<?php echo wp_statistics_today(); ?>`
* Yesterday Visit: `<?php echo wp_statistics_yesterday(); ?>`
* Week Visit `<?php echo wp_statistics_week(); ?>`
* Month Visit `<?php echo wp_statistics_month(); ?>`
* Years Visit `<?php echo wp_statistics_year(); ?>`
* Total Visit `<?php echo wp_statistics_total(); ?>`
* All Search Engine reffered `<?php echo wp_statistics_searchengine(); ?>`
* Google Search Engine reffered `<?php echo wp_statistics_searchengine('google'); ?>`
* Yahoo Search Engine reffered `<?php echo wp_statistics_searchengine('yahoo'); ?>`
* Bing Search Engine reffered `<?php echo wp_statistics_searchengine('bing'); ?>`
* User Online Live (Premium Version) `<?php echo wp_statistics_useronline_live(); ?>`
* Total Visit Live (Premium Version) `<?php echo wp_statistics_total_live(); ?>`
* Total Posts `<?php echo wp_statistics_countposts(); ?>`
* Total Pages `<?php echo wp_statistics_countpages(); ?>`
* Total Comments `<?php echo wp_statistics_countcomment(); ?>`
* Total Spams `<?php echo wp_statistics_countspam(); ?>`
* Total Users `<?php echo wp_statistics_countusers(); ?>`
* Last Post Date `<?php echo wp_statistics_lastpostdate(); ?>`
* Last Post Date (Persian) `<?php echo wp_statistics_lastpostdate('farsi'); ?>`
* Average Posts `<?php echo wp_statistics_average_post(); ?>`
* Average Comments `<?php echo wp_statistics_average_comment(); ?>`
* Average Users `<?php echo wp_statistics_average_registeruser(); ?>`
* Total Feedburner Subscribe `<?php wp_statistics_countsubscrib('feedburneraddress'); ?>`
* Google Pagerank `<?php wp_statistics_google_page_rank(); ?>`
* Alexa Pagerank `<?php echo wp_statistics_alexaRank(); ?>`

or using this Shortcode in Posts pages or Widget:

* User Online: `[useronline]useronline[/useronline]`
* Today Visit: `[today]today[/today]`
* Yesterday Visit: `[yesterday]yesterday[/yesterday]`
* Week Visit `[week]week[/week]`
* Month Visit `[month]month[/month]`
* Years Visit `[year]year[/year]`
* Total Visit `[total]total[/total]`
* All Search Engine reffered `[searchengine]searchengine[/searchengine]`
* User Online Live (Premium Version) `[useronlinelive]useronlinelive[/useronlinelive]`
* Total Visit Live (Premium Version) `[totallive]totallive[/totallive]`
* Total Posts `[countposts]countposts[/countposts]`
* Total Pages `[countpages]countpages[/countpages]`
* Total Comments `[countcomments]countcomments[/countcomments]`
* Total Spams `[countspams]countspams[/countspams]`
* Total Users `[countusers]countusers[/countusers]`
* Last Post Date `[lastpostdate]lastpostdate[/lastpostdate]`
* Average Posts `[averagepost]averagepost[/averagepost]`
* Average Comments `[averagecomment]averagecomment[/averagecomment]`
* Average Users `[averageusers]averageusers[/averageusers]`
* Google Pagerank `[googlepagerank]googlepagerank[/googlepagerank]`
* Alexa Pagerank `[alexaRank]alexaRank[/alexaRank]`

== Frequently Asked Questions ==
a plugin for displaying Summary statistics of blog.

== Screenshots ==
1. Screen shot (screenshot-1.png) in WP-Statistics Setting Page
1. Screen shot (screenshot-2.png) in WP-Statistics Widget Page

== Upgrade Notice ==
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
= 2.3.0 =
* Added Serbian language

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
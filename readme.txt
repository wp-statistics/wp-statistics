=== WP-Statistics ===
Contributors: WP-Statistics
Donate link: http://iran98.org/
Tags: statistics, stats, blog, total, post, page, sidebar, widget, language, summary, feedburner, hits
Requires at least: 1.0
Tested up to: 3.1
Stable tag: 1.0

Summary statistics of blog.

== Description ==

A plugin for displaying Summary statistics of blog.
This plugin displays: Total Posts, Total Pages, Total Comments, Total Spams*, Last Post Date, Feedburner Subscribe And Total Blog Hits.

Language Support:
- English
- Persian (fa_IR) Mostafa Soufi

== Installation ==

1. Upload `wp-statistics.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to the themes > Sidebar And adding `Summary statistics` in the widget.
3. Or: The following functions to display in the theme:

Total Posts:		<?php statistics_countposts(); ?>
Total Pages:		<?php statistics_countpages(); ?>
Total Comments:		<?php statistics_countcomment(); ?>
Total Spams:		<?php statistics_countspam(); ?>
Total Users:		<?php statistics_countusers(); ?>
Last Post Date:		<?php statistics_lastpostdate(); ?>
Last Post Date (Persian):	<?php statistics_lastpostdate('farsi'); ?>
Total Feedburner Subscribe:	<?php statistics_countsubscrib('feedburnerurl'); ?> For Example: <?php statistics_countsubscrib('http://feeds2.feedburner.com/wpdelicious'); ?>
Total Blog Hits:		<?php statistics_totalhits(); ?>

== Frequently Asked Questions ==

= What do the plugin? =
Summary statistics of blog.

== Screenshots ==

1. Screen shot (screenshot-1.png) Show the theme.
2. Screen shot (screenshot-2.png) Show the sidebar.

== Upgrade Notice ==

= 1.0 =
* Adding Total Posts, Total Pages, Total Comments, Total Spams*, Last Post Date, Feedburner Subscribe And Total Blog Hits.
* Adding English, Persian Language.

== Changelog ==
= 1.0 =
* Adding Total Posts, Total Pages, Total Comments, Total Spams*, Last Post Date, Feedburner Subscribe And Total Blog Hits.
* Adding English, Persian Language.
wps_js.set_content = function(response, key) {
     if (response && response.response) {
        wps_js.metaBoxInner(key.replace(/_/g, '-')).html(response.response);
        wps_js.initDatePickerHandlers();
    }
};

wps_js.render_table_content = wps_js.set_content;

wps_js.render_wp_statistics_traffic_summary_widget = wps_js.render_table_content;
wps_js.render_wp_statistics_referring_widget = wps_js.render_table_content;
wps_js.render_wp_statistics_pages_widget = wps_js.render_table_content;
wps_js.render_wp_statistics_top_visitors_widget = wps_js.render_table_content;
wps_js.render_wp_statistics_recent_widget = wps_js.render_table_content;
wps_js.render_wp_statistics_useronline_widget = wps_js.render_table_content;
wps_js.render_wp_statistics_go_premium_widget = wps_js.render_table_content;
wps_js.render_wp_statistics_about_metabox = wps_js.render_table_content;
wps_js.render_wp_statistics_weekly_performance_widget = wps_js.render_table_content;
wps_js.render_wp_statistics_post_visitors_widget = wps_js.render_table_content;
wps_js.render_wp_statistics_source_categories_widget = wps_js.render_table_content;
wps_js.render_wp_statistics_search_queries_widget = wps_js.render_table_content;
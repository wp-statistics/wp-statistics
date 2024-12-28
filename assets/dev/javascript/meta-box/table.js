wps_js.set_content = function(response, key) {
    if (response && response.response) {
        wps_js.metaBoxInner(key).html(response.response);
        wps_js.initDatePickerHandlers();
    }
};

wps_js.render_table_content = wps_js.set_content;

wps_js.render_wp_statistics_top_countries_metabox = wps_js.render_table_content;
wps_js.render_wp_statistics_traffic_summary_metabox = wps_js.render_table_content;
wps_js.render_wp_statistics_top_referring_metabox = wps_js.render_table_content;
wps_js.render_wp_statistics_most_visited_pages_metabox = wps_js.render_table_content;
wps_js.render_wp_statistics_most_active_visitors_metabox = wps_js.render_table_content;
wps_js.render_wp_statistics_latest_visitor_breakdown_metabox = wps_js.render_table_content;
wps_js.render_wp_statistics_currently_online_metabox = wps_js.render_table_content;
wps_js.render_wp_statistics_go_premium_metabox = wps_js.render_table_content;
wps_js.render_wp_statistics_about_wps_metabox = wps_js.render_table_content;
wps_js.render_wp_statistics_weekly_performance_metabox = wps_js.render_table_content;
wps_js.render_wp_statistics_post_latest_visitors_metabox = wps_js.render_table_content;

wps_js.set_content = function(response, key) {
    if (response && response.response) {
        wps_js.metaBoxInner(key).html(response.response);
        wps_js.initDatePickerHandlers();
    }
};

wps_js.render_table_content = wps_js.set_content;

wps_js.render_top_countries = wps_js.render_table_content;
wps_js.render_traffic_summary = wps_js.render_table_content;
wps_js.render_top_referring = wps_js.render_table_content;
wps_js.render_most_visited_pages = wps_js.render_table_content;
wps_js.render_most_active_visitors = wps_js.render_table_content;
wps_js.render_latest_visitor_breakdown = wps_js.render_table_content;
wps_js.render_currently_online = wps_js.render_table_content;
wps_js.render_go_premium = wps_js.render_table_content;
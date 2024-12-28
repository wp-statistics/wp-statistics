wps_js.horizontal_bar_metabox = function(response, key, id, label, data, icon) {
    if (response && response.response) {
        wps_js.metaBoxInner(key).html(response.response.output);
        wps_js.horizontal_bar(id, label, data, icon);
        wps_js.initDatePickerHandlers();
    }
}

wps_js.render_horizontal_bar_data = function(response, key) {
    if (response.response?.data) {
        const args = response.response.data;
        wps_js.horizontal_bar_metabox(
            response,
            key,
            args['tag_id'],
            args['labels'],
            args['data'],
            args['icons']
        );
    }
};

// Alias functions for backward compatibility
wps_js.render_wp_statistics_browser_usage_metabox = function(response, key) {
    wps_js.render_horizontal_bar_data(response, key);
};

wps_js.render_wp_statistics_most_used_operating_systems_metabox = function(response, key) {
    wps_js.render_horizontal_bar_data(response, key);
};

wps_js.render_wp_statistics_device_usage_breakdown_metabox = function(response, key) {
    wps_js.render_horizontal_bar_data(response, key);
};

wps_js.render_wp_statistics_top_device_model_metabox = function(response, key) {
    wps_js.render_horizontal_bar_data(response, key);
};
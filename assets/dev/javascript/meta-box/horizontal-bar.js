wps_js.horizontal_bar_metabox = function(response, key, id, label, data, icon, percentages = []) {
     if (response && response.response) {
        wps_js.metaBoxInner(key).html(response.response.output);
        wps_js.horizontal_bar(id, label, data, icon, percentages);
        wps_js.initDatePickerHandlers();
    }
}

wps_js.render_horizontal_bar_data = function(response, keyName) {
    const key = keyName.replace(/_/g, '-');
    if (response.response?.data) {
        const args = response.response.data;
        wps_js.horizontal_bar_metabox(
            response,
            key,
            args['tag_id'],
            args['labels'],
            args['data'],
            args['icons'],
            args['percentages'] ?? []
        );
    }
};

// Alias functions for backward compatibility
wps_js.render_wp_statistics_browsers_widget = function(response, key) {
    wps_js.render_horizontal_bar_data(response, key);
};

wps_js.render_wp_statistics_platforms_widget = function(response, key) {
    wps_js.render_horizontal_bar_data(response, key);
};

wps_js.render_wp_statistics_devices_widget = function(response, key) {
    wps_js.render_horizontal_bar_data(response, key);
};

wps_js.render_wp_statistics_models_widget = function(response, key) {
    wps_js.render_horizontal_bar_data(response, key);
};
wps_js.render_wp_statistics_countries_widget = function(response, key) {
    wps_js.render_horizontal_bar_data(response, key);
};
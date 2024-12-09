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
        console.log(args)
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
wps_js.render_browser_usage = function(response, key) {
    wps_js.render_horizontal_bar_data(response, key);
};

wps_js.render_most_used_operating_systems = function(response, key) {
    wps_js.render_horizontal_bar_data(response, key);
};

wps_js.render_device_usage_breakdown = function(response, key) {
    wps_js.render_horizontal_bar_data(response, key);
};

wps_js.render_top_device_model = function(response, key) {
    wps_js.render_horizontal_bar_data(response, key);
};
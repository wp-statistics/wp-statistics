wps_js.render_traffic_summary = function (response, key ) {
    if (response && response.response) {
        wps_js.metaBoxInner(key).html(response.response);
        wps_js.initDatePickerHandlers()
    }
    wps_js.handelReloadButton(key);
    wps_js.handelMetaBoxFooter(key,response)
};
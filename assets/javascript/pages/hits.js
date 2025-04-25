if (wps_js.isset(wps_js.global, 'request_params', 'page') && wps_js.global.request_params.page === "hits") {

    // Create Params
    let params = {};

    // Check Days ago or Between
    if (wps_js.isset(wps_js.global, 'request_params', 'from') && wps_js.isset(wps_js.global, 'request_params', 'to')) {
        params = {'from': wps_js.global.request_params.from, 'to': wps_js.global.request_params.to};
    } else {
        params = {'ago': 30};
    }

    // Set PlaceHolder For Total
    jQuery( "span[id^='number-total-chart-']").html(wps_js.rectangle_placeholder('wps-text-placeholder'));

    // Run MetaBox
    wps_js.run_meta_box('hits', params, false);
}
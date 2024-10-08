if (wps_js.isset(wps_js.global, 'request_params', 'page') && wps_js.global.request_params.page === "searches") {

    // Create Params
    let params;

    // Check Days ago or Between
    if (wps_js.isset(wps_js.global, 'request_params', 'from') && wps_js.isset(wps_js.global, 'request_params', 'to')) {
        params = {'from': wps_js.global.request_params.from, 'to': wps_js.global.request_params.to};
    } else {
        params = {'from': wps_js.global.user_date_range.from, 'to': wps_js.global.user_date_range.to};
    }

    // Run MetaBox
    wps_js.run_meta_box('search', params, false);
}